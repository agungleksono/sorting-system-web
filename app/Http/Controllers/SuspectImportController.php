<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Suspect;

class SuspectImportController extends Controller
{
    public function import(Request $request)
    {
        // Get the uploaded file
        $file = $request->file('file');

        // Load the Excel file using PhpSpreadsheet
        $spreadsheet = IOFactory::load($file);

        // Get the first worksheet (you can modify this for multiple sheets)
        $sheet = $spreadsheet->getActiveSheet();

        // Initialize a row counter
        $rowNumber = 0;

        // Loop through each row in the sheet
        foreach ($sheet->getRowIterator() as $row) {
            $rowNumber++; // Increment row counter

            // Skip the first row (header row)
            if ($rowNumber == 1) {
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

            Suspect::create([
                'part_no' => $data[0],
                'lot_no' => $data[1],
                'invoice_id' => $data[2],
                'quantity' => $data[3],
            ]);
        }
    }
}
