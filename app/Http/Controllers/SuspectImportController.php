<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Suspect;
use Illuminate\Support\Facades\DB;

class SuspectImportController extends Controller
{
    public function index()
    {
        $suspects = Suspect::where('is_scanned', false)->get();
        return view('pages.home', compact('suspects'));
    }

    public function import(Request $request)
    {
        // Get the uploaded file
        $file = $request->file('file');

        // Load the Excel file using PhpSpreadsheet
        $spreadsheet = IOFactory::load($file);

        // Get the first worksheet (you can modify this for multiple sheets)
        // $sheet = $spreadsheet->getActiveSheet();

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

            if (is_null($data[1]) || $data[1] == '') continue;

            $dataBatch[] = [
                'part_no' => $data[1],
                'lot_no' => $data[4],
                'invoice_no' => $data[9],
                'container_no' => $data[10],
                'is_scanned' => '0',
            ];

            // Suspect::create([
            //     'part_no' => $data[0],
            //     'lot_no' => $data[1],
            //     'invoice_id' => $data[2],
            //     'quantity' => $data[3],
            // ]);
        }
        // dd($dataBatch);
        // return view('pages.home', compact('suspects'));

        DB::beginTransaction();
        try {
            Suspect::insert($dataBatch); // Bulk insert remaining rows
            DB::commit();

            return redirect('home')->with('success', 'Success to import data!');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error or handle it as needed
            // throw $e;
            return redirect('home')->with('errors', 'Failed to import data!' . $e->getMessage());
        }
    }
}
