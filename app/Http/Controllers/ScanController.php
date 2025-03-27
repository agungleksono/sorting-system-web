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
        date_default_timezone_set('Asia/Jakarta');
        
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required',
            'scan_parameter' => 'required',
            'scanned_by' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        $partNo = $request->input('part_no');
        $scanParameter = $request->input('scan_parameter');
        $qrCode = $request->input('qr_code');
        $scannedBy = $request->input('scanned_by');

        if ($this->isQrScanned($qrCode)) {
            return ResponseFormatter::error(null, 'QR sudah di scan.', 422);
        }
        
        $suspects = Suspect::where('is_scanned', '0')
                            ->select('suspect_id', 'part_no', 'lot_no', 'box_id', 'invoice_no')
                            ->get();

        // Extracting the 'lot_no' values into an array
        // $lotNoArr = $suspects->pluck('lot_no')->toArray();

        $listPart = null;

        if ($scanParameter == 'PART_NO') $listPart = $suspects->pluck('part_no')->toArray();
        elseif ($scanParameter == 'LOT_NO') $listPart = $suspects->pluck('lot_no')->toArray();
        elseif ($scanParameter == 'INVOICE_NO') $listPart = $suspects->pluck('invoice_no')->toArray();
        elseif ($scanParameter == 'BOX_NO') $listPart = $suspects->pluck('box_id')->toArray();

        $foundedLot = null;
        $judgment = 'ok';
        $isSuspectFound = false;
        $suspectId = null;
        
        $foundedData = [];

        // foreach ($suspects as $suspect) {
        //     if (strpos($qrCode, $suspect->lot_no) !== false) {
        //         $foundedLot = $suspect->lot_no;
        //         $judgment = 'ng';
        //         $isSuspectFound = true;
        //         break;
        //     }
        // }

        foreach ($listPart as $index => $value) {
            if (strpos($qrCode, $value) !== false) {
                // $foundedLot = $part;
                $judgment = 'ng';
                $isSuspectFound = true;
                // $suspectId = $suspect->suspect_id;

                $foundedData = [
                    'suspect_id' => $suspects[$index]->suspect_id,
                    'part_no' => $suspects[$index]->part_no,
                    'lot_no' => $suspects[$index]->lot_no,
                    'box_id' => $suspects[$index]->box_id,
                    'invoice_no' => $suspects[$index]->invoice_no,
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
                'judgment' => $judgment,
                'part_no' => $isSuspectFound ? $foundedData['part_no'] : null,
                'lot_no' => $isSuspectFound ? $foundedData['lot_no'] : null,
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $scannedBy,
            ]);
    
            PrintQueue::create([
                'part_no' => $isSuspectFound ? $foundedData['part_no'] : null,
                'lot_no' => $isSuspectFound ? $foundedData['lot_no'] : null,
                'invoice_no' => $isSuspectFound ? $foundedData['invoice_no'] : null,
                'judgment' => $judgment,
                'status' => 'pending',
            ]);

            DB::commit();
            return ResponseFormatter::success($responseData, 'Scanning success');
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    // public function scan(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'part_no' => 'required', 
    //         'qr_code' => 'required',
    //         // 'scanned_by' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return ResponseFormatter::error(null, $validator->errors()->first(), 400);
    //     }

    //     $partNo = $request->input('part_no');
    //     $qrCode = substr($request->input('qr_code'), 0, 1) === "\000026" ? substr($request->input('qr_code'), 1) : $request->input('qr_code');
    //     // $scannedBy = $request->input('scanned_by');

    //     if ($this->isQrScanned($qrCode)) {
    //         return ResponseFormatter::error(null, 'QR sudah di scan.', 422);
    //     }
        
    //     $suspects = Suspect::where('part_no', $partNo)
    //                         ->select('suspect_id', 'part_no', 'lot_no')
    //                         ->get();

    //     // Extracting the 'lot_no' values into an array
    //     // $lotNoArr = $suspects->pluck('lot_no')->toArray();

    //     $foundedLot = null;
    //     $judgment = 'ok';
    //     $isSuspectFound = false;
    //     $suspectId = null;

    //     // foreach ($suspects as $suspect) {
    //     //     if (strpos($qrCode, $suspect->lot_no) !== false) {
    //     //         $foundedLot = $suspect->lot_no;
    //     //         $judgment = 'ng';
    //     //         $isSuspectFound = true;
    //     //         break;
    //     //     }
    //     // }

    //     foreach ($suspects as $suspect) {
    //         if (strpos($qrCode, $suspect->lot_no) !== false) {
    //             $foundedLot = $suspect->lot_no;
    //             $judgment = 'ng';
    //             $isSuspectFound = true;
    //             $suspectId = $suspect->suspect_id;
    //             break;
    //         }
    //     }

    //     $responseData = [
    //         'is_suspect' => $isSuspectFound,
    //     ];

    //     if ($isSuspectFound) {
    //         Suspect::where('suspect_id', $suspectId)->update([
    //             'is_scanned' => '1',
    //             'scanned_at' => date('Y-m-d H:i:s'),
    //         ]);

            
    //     }

    //     QrCode::create([
    //         'qr_id' => date('dmyHis'),
    //         'qr_content' => $qrCode,
    //         'judgment' => $judgment,
    //         'part_no' => $isSuspectFound ? $partNo : '',
    //         'lot_no' => $foundedLot,
    //         'created_at' => date('Y-m-d H:i:s'),
    //     ]);

    //     PrintQueue::create([
    //         'part_no' => $isSuspectFound ? $partNo : '',
    //         'lot_no' => $foundedLot,
    //         'invoice_no' => '',
    //         'judgment' => $judgment,
    //         'status' => 'pending',
    //     ]);

    //     return ResponseFormatter::success($responseData, 'Scanning success');
    // }

    // need to check this function is used or not
    public function checkPrintQueue()
    {
        $queue = PrintQueue::all();

        $printQueue = count($queue) >= 1 ? true : false;

        return ResponseFormatter::success(['printQueue' => $printQueue], 'Ok');
    }

    public function getListPartNo()
    {
        $partNo = Suspect::where('is_scanned', '0')
                        ->distinct()
                        ->pluck('part_no');

        return ResponseFormatter::success(['part_no' => $partNo], 'Ok');
    }

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
        // $query = "SELECT TOP 1 * FROM suspects s1 WHERE s1.lot_no = '41260058' AND (s1.progress IS NOT NULL OR NOT EXISTS (SELECT 1 FROM suspects s2 WHERE s2.lot_no = '41260058' AND s2.progress IS NOT NULL));";

        $validator = Validator::make($request->all(), [
            'qr_code' => 'required',
            'scan_parameter' => 'required',
            'suspect_case_id' => 'required',
            'scanned_by' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        // $partNo = $request->input('part_no');
        $scanParameter = $request->input('scan_parameter');
        $qrCode = $request->input('qr_code');
        $scannedBy = $request->input('scanned_by');
        $suspectCaseId = $request->input('suspect_case_id');

        if ($this->isQrScanned($qrCode)) {
            return ResponseFormatter::error(null, 'QR sudah di scan.', 422);
        }

        $suspects = Suspect::select('suspect_id', 'part_no', 'lot_no', 'box_id', 'invoice_no')
                            ->where([
                                'is_scanned' => '0',
                                'suspect_case_id' => $suspectCaseId,
                            ])
                            // ->where('is_scanned', '0')
                            ->get();

        

        // $listPart = null;

        if ($scanParameter == 'PART_NO') {
            $whereClause = 'part_no';
            // $listPart = $suspects->pluck('part_no')->toArray();
        } elseif ($scanParameter == 'LOT_NO') {
            $whereClause = 'lot_no';
            // $listPart = $suspects->pluck('lot_no')->toArray();
        } elseif ($scanParameter == 'INVOICE_NO') {
            $whereClause = 'invoice_no';
            // $listPart = $suspects->pluck('invoice_no')->toArray();
        } elseif ($scanParameter == 'BOX_NO') {
            $whereClause = 'box_id';
            // $listPart = $suspects->pluck('box_id')->toArray();
        }

        $listPart = $suspects->pluck($whereClause)->toArray();

        $foundedLot = null;
        $judgment = 'ok';
        $isSuspectFound = false;
        $foundedData = [];
        // return response()->json(strpos("2jwe 23j41260090llndk", "41260090") !== false);

        foreach ($listPart as $index => $value) {
            if (strpos($qrCode, $value) !== false) {
                $judgment = 'ng';
                $isSuspectFound = true;

                $foundedData = [
                    'suspect_id' => $suspects[$index]->suspect_id,
                    'part_no' => $suspects[$index]->part_no,
                    'lot_no' => $suspects[$index]->lot_no,
                    'box_id' => $suspects[$index]->box_id,
                    'invoice_no' => $suspects[$index]->invoice_no,
                    'search_value' => $value,
                ];

                break;
            }
        }


    //     $result = Suspect::where('lot_no', '41260058')
    // ->where(function($query) {
    //     $query->whereNotNull('progress')
    //           ->orWhereNotExists(function($subQuery) {
    //               $subQuery->select(DB::raw('1'))
    //                        ->from('suspects as s2')
    //                        ->whereRaw('s2.lot_no = suspects.lot_no')
    //                        ->whereNotNull('s2.progress');
    //           });
    // })
    // ->limit(1)
    // ->first();

    // $result = DB::table('suspects as s1')
    // ->select('*')
    // ->where('s1.lot_no', '41260058')
    // ->where(function($query) {
    //     $query->whereNotNull('s1.progress')
    //           ->orWhereNotExists(function($subQuery) {
    //               $subQuery->select(DB::raw('1'))
    //                        ->from('suspects as s2')
    //                        ->whereRaw('s2.lot_no = s1.lot_no')
    //                        ->whereNotNull('s2.progress');
    //           });
    // })
    // ->limit(1)  // Equivalent to TOP 1 in SQL
    // ->first(); // Get the first result

        $responseData = [
            'is_suspect' => $isSuspectFound,
            'part_no' => $isSuspectFound ? $foundedData['part_no'] : '-',
            'search_value' => $isSuspectFound ? $foundedData['search_value'] : '-',
        ];
    }
}
