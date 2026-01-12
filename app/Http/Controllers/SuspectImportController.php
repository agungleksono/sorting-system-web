<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Suspect;
use App\Models\SuspectCase;
use App\Models\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SuspectImportController extends Controller
{
    public function index(Request $request)
    {
        $caseId = $request->query('caseId');
        
        $cases = SuspectCase::where('is_closed', '0')->get();
        // $suspects = Suspect::where('suspect_case_id', $caseId)->get();
        $suspects = Suspect::join('suspect_cases', 'suspects.suspect_case_id', '=', 'suspect_cases.suspect_case_id')
                            ->select('suspects.*', 'suspect_cases.scan_type_id')
                            ->where('suspects.suspect_case_id', $caseId)
                            ->get();
        $scanProgress = DB::table('suspects')
                            ->select(
                                DB::raw("SUM(CASE WHEN is_scanned = '1' THEN 1 ELSE 0 END) AS current_progress"),
                                DB::raw('COUNT(suspect_case_id) AS max_progress')
                            )
                            ->where('suspect_case_id', $caseId)
                            ->first();

        return view('pages.home', compact('suspects', 'cases', 'scanProgress'));
    }

    public function import(Request $request)
    {
        // Step 1: Validate request input
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx',
            'case' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        $file = $request->file('file');
        $caseId = $request->input('case');
        $createdBy = session('npk', 'system'); // Default to 'system' if not found
        $batchSize = 100;
        $headerRowsToSkip = 13;

        // Step 2: Try to load Excel file
        try {
            $spreadsheet = IOFactory::load($file);
        } catch (\Exception $e) {
            Log::error('Excel load error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->route('suspects.index')->with('errors', 'Failed to read Excel file.');
        }

        // // Step 3: Check for hidden sheets
        // foreach ($spreadsheet->getAllSheets() as $sheet) {
        //     if ($sheet->getSheetState() === \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN) {
        //         return redirect()->route('suspects.index')->with('errors', 'The Excel file contains hidden sheets. Please unhide them and try again.');
        //     }
        // }

        // Step 4: Read from first sheet
        $sheet = $spreadsheet->getSheet(0); // Get the specific worksheet (can be adjusted)
        $rowNumber = 0; // Initialize a row counter
        $dataBatch = []; // Initialize an array to store the data for batch insert

        foreach ($sheet->getRowIterator() as $row) {
            $rowNumber++;

            // Skip the first row (header row)
            if ($rowNumber <= $headerRowsToSkip) {
                continue; // Skip header rows
            }

            // Get each cell in the row
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            // Store the row data in an array
            $data = [];
            foreach ($cellIterator as $cell) {
                $data[] = $cell->getValue();
            }

            // Skip rows if part_no empty (Required field: part_no (column index 1))
            if (empty($data[1])) continue;

            // Named variables for readability
            $partNo = (string)($data[1] ?? null);
            $lotNo = (string)($data[4] ?? null);
            $quantity = (string)($data[5] ?? null);
            $boxId = (string)($data[7] ?? null);
            $containerNo = (string)($data[9] ?? null);
            $invoiceNo = (string)($data[10] ?? null);

            $dataBatch[] = [
                'part_no' => $partNo,
                'lot_no' => $lotNo,
                'box_id' => $boxId,
                'container_no' => $containerNo,
                'invoice_no' => $invoiceNo,
                'quantity' => $quantity,
                'is_scanned' => '0',
                'suspect_case_id' => $caseId,
                'created_by' => $createdBy,
                'created_at' => Carbon::now(),
            ];

            // Insert batch when it reaches $batchSize
            if (count($dataBatch) >= $batchSize) {
                try {
                    DB::beginTransaction();
                    Suspect::insert($dataBatch);
                    DB::commit();
                    $dataBatch = []; // Reset batch
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Import batch insert failed: ' . $e->getMessage(), [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                    return redirect()->route('suspects.index')->with('errors', 'Failed to import data: ' . $e->getMessage());
                }
            }
        }

        // Final insert for remaining rows
        if (!empty($dataBatch)) {
            try {
                DB::beginTransaction();
                Suspect::insert($dataBatch);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Final batch insert failed: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->route('suspects.index')->with('errors', 'Failed to import data: ' . $e->getMessage());
            }
        }

        return redirect()->route('suspects.index', ['caseId' => $caseId])->with('success', 'Success to import data!');
    }

    // public function importOld(Request $request)
    // {
    //     $file = $request->file('file');
    //     $caseId = $request->input('case');

    //     // Load the Excel file using PhpSpreadsheet
    //     $spreadsheet = IOFactory::load($file);
    
    //     // Check if there are any hidden sheets
    //     foreach ($spreadsheet->getAllSheets() as $sheet) {
    //         if ($sheet->getSheetState() === \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN) {
    //             // If a hidden sheet is found, return an error message to the user
    //             return redirect()->route('suspects.index')->with('errors', 'The Excel file contains hidden sheets. Please unhide them and try again.');
    //         }
    //     }

    //     // // Get the first worksheet (you can modify this for multiple sheets)
    //     // $sheet = $spreadsheet->getActiveSheet();
    
    //     // Get the specific worksheet (can be adjusted)
    //     $sheet = $spreadsheet->getSheet(0); // Define specific sheet    
    
    //     // Initialize a row counter
    //     $rowNumber = 0;
    
    //     // Initialize an array to store the data for batch insert
    //     $dataBatch = [];
    
    //     // Loop through each row in the sheet
    //     foreach ($sheet->getRowIterator() as $row) {
    //         $rowNumber++; // Increment row counter
    
    //         // Skip the first row (header row)
    //         if ($rowNumber < 14) {
    //             continue; // Skip header
    //         }
    
    //         // Get each cell in the row
    //         $cellIterator = $row->getCellIterator();
    //         $cellIterator->setIterateOnlyExistingCells(false);
    
    //         // Store the row data in an array
    //         $data = [];
    //         foreach ($cellIterator as $cell) {
    //             $data[] = $cell->getValue();
    //         }
    
    //         // Skip rows with invalid part_no or invoice_no
    //         if (empty($data[1])) continue;
    
    //         // Add the data to the batch
    //         $dataBatch[] = [
    //             'part_no' => (string)$data[1],
    //             'lot_no' => (string)$data[4],
    //             'box_id' => isset($data[7]) ? (string)$data[7] : null,
    //             'container_no' => isset($data[9]) ? (string)$data[9] : null,
    //             'invoice_no' => isset($data[10]) ? (string)$data[10] : null,
    //             'quantity' => (string)$data[5],
    //             'is_scanned' => '0',
    //             'suspect_case_id' => (string)$caseId,
    //             'created_by' => session('npk'),
    //             'created_at' => date('Y-m-d H:i:s'),
    //         ];
    
    //         // If the batch size reaches 100, insert the data into the database and reset the batch
    //         if (count($dataBatch) >= 100) {
    //             try {
    //                 DB::beginTransaction();
    //                 Suspect::insert($dataBatch);
    //                 DB::commit();
    
    //                 // Reset the batch for the next 1000 rows
    //                 $dataBatch = [];
    //             } catch (\Exception $e) {
    //                 DB::rollBack();
    //                 return redirect()->route('suspects.index')->with('errors', 'Failed to import data!' . $e->getMessage());
    //             }
    //         }
    //     }
    
    //     // After the loop, check if there's any remaining data to insert
    //     if (count($dataBatch) > 0) {
    //         try {
    //             DB::beginTransaction();
    //             Suspect::insert($dataBatch);
    //             DB::commit();
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             return redirect()->route('suspects.index')->with('errors', 'Failed to import data!' . $e->getMessage());
    //         }
    //     }
    
    //     return redirect()->route('suspects.index', ['caseId' => $caseId])->with('success', 'Success to import data!');
    // }

    public function manualAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'part_no' => 'required', 
        ]);

        if ($validator->fails()) {
            return redirect()->route('suspects.index')->with('errors', $validator->errors()->first());
        }

        try {
            $suspect = Suspect::create([
                'part_no' => $request->input('part_no'),
                'lot_no' => $request->input('lot_no'),
                'box_id' => $request->input('box_id'),
                'invoice_no' => $request->input('invoice_no'),
                'container_no' => $request->input('container_no'),
                'quantity' => $request->input('quantity'),
                'suspect_case_id' => $request->input('suspect_case_id'),
                'is_scanned' => '0',
                'created_by' => session('npk'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect()->route('suspects.index', ['caseId' => $request->input('suspect_case_id')])->with('success', 'Suspect Part added successfully!');
        } catch (\Exception $e) {
            Log::error('Error added suspect: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return redirect()->route('suspects.index')->with('errors', 'Failed to add suspect.');
        }
    }

    public function downloadSample()
    {
        $filePath = public_path('file/sample.xlsx');

        if (File::exists($filePath)) {
            return response()->download($filePath);
        } else {
            abort(404, 'File not found');
        }
    }

    public function delete(Request $request)
    {
        return $request->input('type') == "QR" ? SuspectImportController::deleteQr($request) : SuspectImportController::deleteSuspect($request);
    }

    private function deleteSuspect(Request $request)
    {
        try {
            DB::beginTransaction();

            $selectedIds = $request->input('selected');
            if (!empty($selectedIds)) {
                foreach ($selectedIds as $selectedId) {
                    Suspect::where('suspect_id', $selectedId)->delete();
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('suspects.index', ['caseId' => $request->has('caseId') ? $request->input('caseId') : null])->with('errors', 'Failed to delete Suspect Item(s)!' . $e->getMessage());
        }
        
        return redirect()->route('suspects.index', ['caseId' => $request->has('caseId') ? $request->input('caseId') : null])->with('success', 'Suspect Item(s) deleted successfully!');
    }

    private function deleteQr(Request $request)
    {
        try {
            DB::beginTransaction();

            $selectedIds = $request->input('selected');
            if (!empty($selectedIds)) {
                foreach ($selectedIds as $selectedId) {
                    QrCode::where('suspect_id', $selectedId)->delete();
                    Suspect::where('suspect_id', $selectedId)->update(['is_scanned' => 0, 'scanned_by' => null, 'scanned_at' => null, 'progress_quantity' => null]);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('suspects.index', ['caseId' => $request->has('caseId') ? $request->input('caseId') : null])->with('errors', 'Failed to delete Scanned QR(s)!' . $e->getMessage());
        }
        
        return redirect()->route('suspects.index', ['caseId' => $request->has('caseId') ? $request->input('caseId') : null])->with('success', 'Scanned QR(s) deleted successfully!');
    }

}
