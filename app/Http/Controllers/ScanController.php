<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suspect;
use App\Models\PrintQueue;
use App\Models\QrCode;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;

class ScanController extends Controller
{
    public function index()
    {
        
        return view('pages.scan');
    }

    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required',
            'scan_parameter' => 'required',
            'suspect_case_id' => 'required',
            'scanned_by' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        $scanParameter = $request->input('scan_parameter');
        $qrCode = $request->input('qr_code');
        $suspectCaseId = $request->input('suspect_case_id');
        $scannedBy = $request->input('scanned_by');

        if ($this->isQrScanned($qrCode)) {
            return ResponseFormatter::error(null, 'QR sudah di scan.', 422);
        }
        
        $suspects = Suspect::select('suspect_id', 'part_no', 'lot_no', 'box_id', 'invoice_no')
                            ->where([
                                'is_scanned' => '0',
                                'suspect_case_id' => $suspectCaseId,
                            ])
                            ->get();

        $listPart = null;

        if ($scanParameter == 'PART_NO') $listPart = $suspects->pluck('part_no')->toArray();
        elseif ($scanParameter == 'LOT_NO') $listPart = $suspects->pluck('lot_no')->toArray();
        elseif ($scanParameter == 'INVOICE_NO') $listPart = $suspects->pluck('invoice_no')->toArray();
        elseif ($scanParameter == 'BOX_NO') $listPart = $suspects->pluck('box_id')->toArray();

        $isSuspectFound = false;
        $foundedData = [];

        foreach ($listPart as $index => $value) {
            if (strpos($qrCode, $value) !== false) {
                $isSuspectFound = true;

                $foundedData = [
                    'suspect_id' => $suspects[$index]->suspect_id,
                    'part_no' => $suspects[$index]->part_no,
                    'lot_no' => $suspects[$index]->lot_no,
                    'box_id' => $suspects[$index]->box_id,
                    'invoice_no' => $suspects[$index]->invoice_no,
                    'box_id' => $suspects[$index]->box_id,
                    'search_value' => $value,
                ];

                break;
            }
        }

        $responseData = [
            'is_suspect' => $isSuspectFound,
            'part_no' => $isSuspectFound ? $foundedData['part_no'] : '-',
            'search_value' => $isSuspectFound ? $foundedData['search_value'] : '-',
        ];

        DB::beginTransaction();
        try {
            if ($isSuspectFound) {
                Suspect::where('suspect_id', $foundedData['suspect_id'])->update([
                    'is_scanned' => '1',
                    'scanned_at' => date('Y-m-d H:i:s'),
                    'scanned_by' => $scannedBy,
                ]);
            }
    
            QrCode::create([
                'qr_id' => date('dmyHis'),
                'qr_content' => $qrCode,
                'judgment' => $isSuspectFound ? 'ng' : 'ok',
                'part_no' => $isSuspectFound ? $foundedData['part_no'] : null,
                'lot_no' => $isSuspectFound ? $foundedData['lot_no'] : null,
                'invoice_no' => $isSuspectFound ? $foundedData['invoice_no'] : null,
                'box_id' => $isSuspectFound ? $foundedData['box_id'] : null,
                'scan_parameter' => $isSuspectFound ? $scanParameter : null,
                'suspect_case_id' => $suspectCaseId,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $scannedBy,
                'suspect_id' => $isSuspectFound ? $foundedData['suspect_id'] : null,
            ]);
    
            PrintQueue::create([
                'part_no' => $isSuspectFound ? $foundedData['part_no'] : null,
                'lot_no' => $isSuspectFound ? $foundedData['lot_no'] : null,
                'invoice_no' => $isSuspectFound ? $foundedData['invoice_no'] : null,
                'box_id' => $isSuspectFound ? $foundedData['box_id'] : null,
                'scan_parameter' => $isSuspectFound ? $scanParameter : null,
                'judgment' => $isSuspectFound ? 'ng' : 'ok',
                'status' => 'pending',
            ]);

            DB::commit();
            return ResponseFormatter::success($responseData, 'Scanning success');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed.', 400);
        }
    }

    

    // need to check this function is used or not
    public function checkPrintQueue()
    {
        $queue = PrintQueue::all();

        $printQueue = count($queue) >= 1 ? true : false;

        return ResponseFormatter::success(['printQueue' => $printQueue], 'Ok');
    }

    // public function getListPartNo()
    // {
    //     $partNo = Suspect::where('is_scanned', '0')
    //                     ->distinct()
    //                     ->pluck('part_no');

    //     return ResponseFormatter::success(['part_no' => $partNo], 'Ok');
    // }

    public function isQrScanned($qrContent)
    {
        $qrCode = QrCode::where('qr_content', $qrContent)->first();

        // Return true if QR code is found, otherwise false
        return $qrCode !== null;
    }

    public function countScanProgress($suspectCaseId)
    {
        $result = DB::select(
            DB::raw('
                SELECT 
                    COUNT(CASE WHEN is_scanned = 1 THEN 1 END) AS current_progress,
                    COUNT(*) AS max_progress
                FROM suspects 
                WHERE suspect_case_id = :suspectCaseId
            '),
            ['suspectCaseId' => $suspectCaseId] // Bind the suspect_case_id parameter
        );
        
        $response = [
            'current_progress' => $result[0]->current_progress,
            'max_progress' => $result[0]->max_progress,
        ];

        return ResponseFormatter::success($response, 'Success');
    }


    public function scanMultiBox(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required',
            'scan_parameter' => 'required',
            'suspect_case_id' => 'required',
            'scanned_by' => 'required',
            'progress_quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        $scanParameter = $request->input('scan_parameter');
        $qrCode = $request->input('qr_code');
        $suspectCaseId = $request->input('suspect_case_id');
        $scannedBy = $request->input('scanned_by');
        $progressQuantity = $request->input('progress_quantity');

        if ($this->isQrScanned($qrCode)) {
            return ResponseFormatter::error(null, 'QR sudah di scan.', 422);
        }

        $suspects = Suspect::select('suspect_id', 'part_no', 'lot_no', 'box_id', 'invoice_no')
                            ->where('suspect_case_id', $suspectCaseId)
                            ->where(function($query) {
                                $query->where('is_scanned', '0')
                                      ->orWhereRaw('CAST(progress_quantity AS INT) < CAST(quantity AS INT)');
                            })
                            ->get();

        $mapScanParameter = [
            'PART_NO' => 'part_no',
            'LOT_NO' => 'lot_no',
            'INVOICE_NO' => 'invoice_no',
            'BOX_NO' => 'box_id'
        ];

        // Check if the scanParameter exists in the map
        if (!isset($mapScanParameter[$scanParameter])) {
            return ResponseFormatter::error(null, 'Scan Parameter tidak sesuai.', 400);
        }

        $whereClause = $mapScanParameter[$scanParameter];
        $listPart = $suspects->pluck($whereClause)->toArray();

        $isSuspectFound = false;
        $foundedData = [];

        foreach ($listPart as $index => $value) {
            if (strpos($qrCode, $value) !== false) {
                $isSuspectFound = true;
                $foundedData = [
                    'suspect_id' => $suspects[$index]->suspect_id,
                    'part_no' => $suspects[$index]->part_no,
                    'lot_no' => $suspects[$index]->lot_no,
                    'box_id' => $suspects[$index]->box_id,
                    'invoice_no' => $suspects[$index]->invoice_no,
                    'box_id' => $suspects[$index]->box_id,
                    'search_value' => $value,
                ];
                break;
            }
        }
        
        $result = null;
        if ($isSuspectFound) {
            $result = DB::table('suspects')
                        ->where($whereClause, $foundedData['search_value'])
                        ->orderByRaw('CASE 
                                        WHEN progress_quantity IS NOT NULL AND CAST(progress_quantity AS INT) < CAST(quantity AS INT) THEN 1 
                                        WHEN progress_quantity IS NULL THEN 2 
                                        ELSE 3 
                                     END')
                        ->limit(1)
                        ->first();
        }

        $responseData = [
            'is_suspect' => $isSuspectFound,
            'part_no' => $isSuspectFound ? $foundedData['part_no'] : '-',
            'search_value' => $isSuspectFound ? $foundedData['search_value'] : '-',
        ];

        DB::beginTransaction();
        try {
            if ($isSuspectFound) {
                if (empty($result->progress_quantity)) {
                    Suspect::where('suspect_id', $result->suspect_id)->update([
                        'is_scanned' => '1',
                        'scanned_at' => date('Y-m-d H:i:s'),
                        'scanned_by' => $scannedBy,
                        'progress_quantity' => $progressQuantity,
                    ]);
                } else {
                    Suspect::where('suspect_id', $result->suspect_id)->update([
                        'is_scanned' => '1',
                        'scanned_at' => date('Y-m-d H:i:s'),
                        'scanned_by' => $scannedBy,
                        'progress_quantity' => $result->progress_quantity + $progressQuantity,
                    ]);
                }
            }

            QrCode::create([
                'qr_id' => date('dmyHis'),
                'qr_content' => $qrCode,
                'judgment' => $isSuspectFound ? 'ng' : 'ok',
                'part_no' => $isSuspectFound ? $foundedData['part_no'] : null,
                'lot_no' => $isSuspectFound ? $foundedData['lot_no'] : null,
                'invoice_no' => $isSuspectFound ? $foundedData['invoice_no'] : null,
                'box_id' => $isSuspectFound ? $foundedData['box_id'] : null,
                'scan_parameter' => $isSuspectFound ? $scanParameter : null,
                'suspect_case_id' => $suspectCaseId,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $scannedBy,
            ]);
    
            PrintQueue::create([
                'part_no' => $isSuspectFound ? $foundedData['part_no'] : null,
                'lot_no' => $isSuspectFound ? $foundedData['lot_no'] : null,
                'invoice_no' => $isSuspectFound ? $foundedData['invoice_no'] : null,
                'box_id' => $isSuspectFound ? $foundedData['box_id'] : null,
                'scan_parameter' => $isSuspectFound ? $scanParameter : null,
                'judgment' => $isSuspectFound ? 'ng' : 'ok',
                'status' => 'pending',
            ]);

            DB::commit();
            return ResponseFormatter::success($responseData, 'Scanning success');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed.', 400);
        }
    }

    public function reprint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required',
            'suspect_case_id' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        $qrCode = $request->input('qr_code');
        $suspectCaseId = $request->input('suspect_case_id');

        $qrDb = QrCode::where([
                    'suspect_case_id' => $suspectCaseId,
                    'qr_content' => $qrCode,
                ])->first();

        if (!$qrDb) {
            return ResponseFormatter::error(null, 'QR Code not found.', 404);
        }

        $scanParameterToColumn = [
            'PART_NO' => 'part_no',
            'LOT_NO' => 'lot_no',
            'INVOICE_NO' => 'invoice_no',
            'BOX_NO' => 'box_no',
        ];
        $dbColumn = $scanParameterToColumn[$qrDb->scan_parameter] ?? null;

        $responseData = [
            'is_suspect' => $qrDb->judgment == 'ng' ? true : false,
            'part_no' => $qrDb->part_no,
            'search_value' => $qrDb->$dbColumn
        ];

        try {
            PrintQueue::create([
                'part_no' => $qrDb->part_no,
                'lot_no' => $qrDb->lot_no,
                'invoice_no' => $qrDb->invoice_no,
                'judgment' => $qrDb->judgment,
                'box_id' => $qrDb->box_id,
                'scan_parameter' => $qrDb->scan_parameter,
                'status' => 'pending',
            ]);

            return ResponseFormatter::success($responseData, 'Reprint success');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(null, 'Failed Reprint.', 400);
        }
    }
}
