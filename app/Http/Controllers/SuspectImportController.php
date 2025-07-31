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

class SuspectImportController extends Controller
{
    public function index(Request $request)
    {
        $caseId = $request->query('caseId');
        
        $cases = SuspectCase::where('is_closed', '0')->get();
        $suspects = Suspect::where('suspect_case_id', $caseId)->get();
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
        $file = $request->file('file');
        $caseId = $request->input('case');

        // Load the Excel file using PhpSpreadsheet
        $spreadsheet = IOFactory::load($file);
    
        // Check if there are any hidden sheets
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if ($sheet->getSheetState() === \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN) {
                // If a hidden sheet is found, return an error message to the user
                return redirect()->route('suspects.index')->with('errors', 'The Excel file contains hidden sheets. Please unhide them and try again.');
            }
        }

        // // Get the first worksheet (you can modify this for multiple sheets)
        // $sheet = $spreadsheet->getActiveSheet();
    
        // Get the specific worksheet (can be adjusted)
        $sheet = $spreadsheet->getSheet(0); // Define specific sheet    
    
        // Initialize a row counter
        $rowNumber = 0;
    
        // Initialize an array to store the data for batch insert
        $dataBatch = [];
    
        // Loop through each row in the sheet
        foreach ($sheet->getRowIterator() as $row) {
            $rowNumber++; // Increment row counter
    
            // Skip the first row (header row)
            if ($rowNumber < 14) {
                continue; // Skip header
            }
    
            // Get each cell in the row
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
    
            // Store the row data in an array
            $data = [];
            foreach ($cellIterator as $cell) {
                $data[] = $cell->getValue();
            }
    
            // Skip rows with invalid part_no or invoice_no
            if (empty($data[1])) continue;
    
            // Add the data to the batch
            $dataBatch[] = [
                'part_no' => (string)$data[1],
                'lot_no' => (string)$data[4],
                'box_id' => (string)$data[7],
                'container_no' => (string)$data[9],
                'invoice_no' => (string)$data[10],
                'quantity' => (string)$data[5],
                'is_scanned' => '0',
                'suspect_case_id' => (string)$caseId,
                'created_by' => session('npk'),
                'created_at' => date('Y-m-d H:i:s'),
            ];
    
            // If the batch size reaches 100, insert the data into the database and reset the batch
            if (count($dataBatch) >= 100) {
                DB::beginTransaction();
                try {
                    Suspect::insert($dataBatch);
                    DB::commit();
    
                    // Reset the batch for the next 1000 rows
                    $dataBatch = [];
                } catch (\Exception $e) {
                    DB::rollBack();
                    return redirect()->route('suspects.index')->with('errors', 'Failed to import data!' . $e->getMessage());
                }
            }
        }
    
        // After the loop, check if there's any remaining data to insert
        if (count($dataBatch) > 0) {
            DB::beginTransaction();
            try {
                Suspect::insert($dataBatch);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('suspects.index')->with('errors', 'Failed to import data!' . $e->getMessage());
            }
        }
    
        return redirect()->route('suspects.index', ['caseId' => $caseId])->with('success', 'Success to import data!');
    }

    public function manualAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'part_no' => 'required', 
        ]);

        if ($validator->fails()) {
            return redirect()->route('suspects.index')->with('errors', $validator->errors()->first());
        }

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

        // return redirect()->route('suspects.index')->with('success', 'Suspect Part added successfully!');
        return redirect()->route('suspects.index', ['caseId' => $request->input('suspect_case_id')])->with('success', 'Suspect Part added successfully!');
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
