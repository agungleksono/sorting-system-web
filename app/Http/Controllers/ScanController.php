<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suspect;
use App\Models\PrintQueue;
use App\Models\QrCode;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseFormatter;

class ScanController extends Controller
{
    public function index()
    {
        
        return view('pages.scan');
    }

    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'part_no' => 'required', 
            'qr_code' => 'required',
            // 'scanned_by' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        $partNo = $request->input('part_no');
        $qrCode = substr($request->input('qr_code'), 0, 1) === "\000026" ? substr($request->input('qr_code'), 1) : $request->input('qr_code');
        // $scannedBy = $request->input('scanned_by');

        if ($this->isQrScanned($qrCode)) {
            return ResponseFormatter::error(null, 'QR sudah di scan.', 422);
        }
        
        $suspects = Suspect::where('part_no', $partNo)
                            ->select('suspect_id', 'part_no', 'lot_no')
                            ->get();

        // Extracting the 'lot_no' values into an array
        // $lotNoArr = $suspects->pluck('lot_no')->toArray();

        $foundedLot = null;
        $judgment = 'ok';
        $isSuspectFound = false;
        $suspectId = null;

        // foreach ($suspects as $suspect) {
        //     if (strpos($qrCode, $suspect->lot_no) !== false) {
        //         $foundedLot = $suspect->lot_no;
        //         $judgment = 'ng';
        //         $isSuspectFound = true;
        //         break;
        //     }
        // }

        foreach ($suspects as $suspect) {
            if (strpos($qrCode, $suspect->lot_no) !== false) {
                $foundedLot = $suspect->lot_no;
                $judgment = 'ng';
                $isSuspectFound = true;
                $suspectId = $suspect->suspect_id;
                break;
            }
        }

        $responseData = [
            'is_suspect' => $isSuspectFound,
        ];

        if ($isSuspectFound) {
            Suspect::where('suspect_id', $suspectId)->update([
                'is_scanned' => '1',
                'scanned_at' => date('Y-m-d H:i:s'),
            ]);

            
        }

        QrCode::create([
            'qr_id' => date('dmyHis'),
            'qr_content' => $qrCode,
            'judgment' => $judgment,
            'part_no' => $isSuspectFound ? $partNo : '',
            'lot_no' => $foundedLot,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        PrintQueue::create([
            'part_no' => $isSuspectFound ? $partNo : '',
            'lot_no' => $foundedLot,
            'invoice_no' => '',
            'judgment' => $judgment,
            'status' => 'pending',
        ]);

        return ResponseFormatter::success($responseData, 'Scanning success');
    }

    public function checkPrintQueue()
    {
        // $printQueue = false;
        $queue = PrintQueue::all();

        // if (count($queue) >= 1)
        // {
        //     $printQueue = true;
        // }

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
}
