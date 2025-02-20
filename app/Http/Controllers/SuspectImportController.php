<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Suspect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ChunkReadFilter;

class SuspectImportController extends Controller
{
    public function index()
    {
        $suspects = Suspect::where('is_scanned', false)->get();
        return view('pages.home', compact('suspects'));
    }

    // public function import(Request $request)
    // {
    //     // Get the uploaded file
    //     $file = $request->file('file');

    //     // Load the Excel file using PhpSpreadsheet
    //     $spreadsheet = IOFactory::load($file);

    //     // Check if there are any hidden sheets
    //     foreach ($spreadsheet->getAllSheets() as $sheet) {
    //         if ($sheet->getSheetState() === \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN) {
    //             // If a hidden sheet is found, return an error message to the user
    //             return redirect()->route('suspects.index')->with('errors', 'The Excel file contains hidden sheets. Please unhide them and try again.');
    //         }
    //     }

    //     // Get the first worksheet (you can modify this for multiple sheets)
    //     // $sheet = $spreadsheet->getActiveSheet();

    //     $sheet = $spreadsheet->getSheet(6); // Define specific sheet

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
    //         // if (is_null($data[1]) || $data[1] == '') continue;

    //         $dataBatch[] = [
    //             'part_no' => $data[1],
    //             'lot_no' => (string)$data[4],
    //             'invoice_no' => $data[9],
    //             'container_no' => $data[10],
    //             'is_scanned' => '0',
    //             'created_by' => session('npk'),
    //             'created_at' => date('Y-m-d H:i:s'),
    //         ];
    //     }

    //     DB::beginTransaction();
    //     try {
    //         Suspect::insert($dataBatch);
    //         DB::commit();

    //         return redirect()->route('suspects.index')->with('success', 'Success to import data!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         // Log the error or handle it as needed
    //         // throw $e;
    //         return redirect()->route('suspects.index')->with('errors', 'Failed to import data!' . $e->getMessage());
    //         // return redirect()->route('suspects.index')->with('errors', 'Failed to import data!');
    //     }
    // }

    // public function import(Request $request)
    // {
    //     $file = $request->file('file');

    //     // Load the Excel file using PhpSpreadsheet
    //     $spreadsheet = IOFactory::load($file);
    //     $sheet = $spreadsheet->getSheet(6); // Define the specific sheet

    //     // Get the total row count (including row 14)
    //     $rowCount = $sheet->getHighestRow();
    //     $chunkSize = 100; // Process 1000 rows at a time

    //     // Start processing from row 14 onward
    //     for ($startRow = 14; $startRow <= $rowCount; $startRow += $chunkSize) {
    //         $endRow = min($startRow + $chunkSize - 1, $rowCount); // Set the end row for this chunk

    //         // Create a chunk read filter for this range of rows (starting from row 14)
    //         $chunkFilter = new ChunkReadFilter($startRow, $endRow);
    //         $reader = IOFactory::createReaderForFile($file);
    //         $reader->setReadFilter($chunkFilter);

    //         // Load only the chunk into memory
    //         $chunkSpreadsheet = $reader->load($file);
    //         $chunkSheet = $chunkSpreadsheet->getSheet(6); // Define the specific sheet

    //         // Initialize an array to store data for batch insert
    //         $dataBatch = [];

    //         // Process each row in this chunk (starting from row 14)
    //         for ($rowNumber = $startRow; $rowNumber <= $endRow; $rowNumber++) {
    //             $row = $chunkSheet->getRowIterator($rowNumber)->current();
    //             $cellIterator = $row->getCellIterator();
    //             $cellIterator->setIterateOnlyExistingCells(false);

    //             $data = [];
    //             foreach ($cellIterator as $cell) {
    //                 $data[] = $cell->getValue();
    //             }

    //             // Skip invalid rows (you can apply validation here)
    //             if (empty($data[1]) || empty($data[9])) {
    //                 continue;
    //             }

    //             $dataBatch[] = [
    //                 'part_no' => $data[1],
    //                 'lot_no' => (string)$data[4],
    //                 'invoice_no' => $data[9],
    //                 'container_no' => $data[10],
    //                 'is_scanned' => '0',
    //                 'created_by' => session('npk'),
    //                 'created_at' => now(),
    //             ];
    //         }

    //         // Insert the chunk into the database (batch insert)
    //         if (!empty($dataBatch)) {
    //             DB::beginTransaction();
    //             try {
    //                 Suspect::insert($dataBatch);
    //                 DB::commit();
    //             } catch (\Exception $e) {
    //                 DB::rollBack();
    //                 Log::error('Import Error: ' . $e->getMessage());
    //                 return redirect()->route('suspects.index')->with('errors', 'Failed to import data! Please check the log for more details.');
    //             }
    //         }
    //     }

    //     return redirect()->route('suspects.index')->with('success', 'Success to import data!');
    // }

    public function import(Request $request)
    {
        $file = $request->file('file');

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
        $sheet = $spreadsheet->getSheet(6); // Define specific sheet
    
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
                'part_no' => $data[1],
                'lot_no' => (string)$data[4],
                'invoice_no' => $data[9],
                'container_no' => $data[10],
                'is_scanned' => '0',
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
    
        return redirect()->route('suspects.index')->with('success', 'Success to import data!');
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
            'invoice_no' => $request->input('invoice_no'),
            'container_no' => $request->input('container_no'),
            'is_scanned' => '0',
            'created_by' => session('npk'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->route('suspects.index')->with('success', 'Suspect Part added successfully!');
    }
}
