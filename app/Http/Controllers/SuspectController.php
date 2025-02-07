<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SuspectsImport;
use App\Models\Suspect;
use Illuminate\Support\Facades\DB;

class SuspectController extends Controller
{
    public function importSuspects()
    {
        Excel::import(new SuspectsImport, $request->file('file'));
        echo('tes');
    }

    public function dataScanned(Request $request)
    {
        if ($request->input('submit')) {   
            // $suspects = Suspect::where([
            //     'is_scanned' => '1',
            // ])->whereBetween('scanned_at', [
            //     $request->input('start_date'),
            //     $request->input('end_date'),
            // ])->get();
            // $suspects = Suspect::where([
            //     'is_scanned' => '1',
            //     'scanned_at >=' => $request->input('start_date'),
            //     'scanned_at <=' => $request->input('start_date'),
            // ])->get();
            
            $suspects = Suspect::where('is_scanned', '1')
                                ->whereDate('scanned_at', '>=', $request->input('start_date'))
                                ->whereDate('scanned_at', '<=', $request->input('end_date'))
                                ->get();
            // $suspects = DB::table('suspects')
            //                 ->whereRaw('is_scanned = 1')
            //                 ->whereRaw('scanned_at >= ?', [$request->input('start_date')])
            //                 ->whereRaw('scanned_at <= ?', [$request->input('end_date')])
            //                 ->get();

            // $query = Suspect::where('is_scanned', '1');

            // if ($request->filled('start_date') && !$request->filled('end_date')) {
            //     // Only start_date is provided, filter with >= start_date
            //     $query->whereDate('scanned_at', '>=', $request->input('start_date'));
            // }

            // if (!$request->filled('start_date') && $request->filled('end_date')) {
            //     // Only end_date is provided, filter with <= end_date
            //     $query->whereDate('scanned_at', '<=', $request->input('end_date'));
            // }

            // if ($request->filled('start_date') && $request->filled('end_date')) {
            //     // Both start_date and end_date are provided, use whereBetween
            //     $query->whereBetween('scanned_at', [
            //         $request->input('start_date') . ' 00:00:00', // Start of the day
            //         $request->input('end_date') . ' 23:59:59',   // End of the day
            //     ]);
            // }

            // $suspects = $query->get();
            dd($suspects);
        }
        else {
            $suspects = Suspect::where('is_scanned', '1')
                                ->get();

            $suspects = collect();
        }
        // return;
        // dd($request->input('submitt'));
        // if (empty($request->part_no) && empty($request->lot_no) && empty($request->container_no) && empty($request->invoice_no) && empty($request->start_date) && empty($request->end_date)) {
        //     // If all fields are empty, return an empty result
        //     $suspects = collect(); // Returns an empty collection
        //     return view('pages.scan', compact('suspects'));
        // }

        // $query = Suspect::query();

        // Check if each field is present and apply the necessary filtering
        
        // // Filter by part_no if it's provided
        // if ($request->has('part_no') && $request->input('part_no') !== '') {
        //     $query->where('part_no', 'like', '%' . $request->input('part_no') . '%');
        // }

        // // Filter by lot_no if it's provided
        // if ($request->has('lot_no') && $request->input('lot_no') !== '') {
        //     $query->where('lot_no', 'like', '%' . $request->input('lot_no') . '%');
        // }

        // // Filter by container_no if it's provided
        // if ($request->has('container_no') && $request->input('container_no') !== '') {
        //     $query->where('container_no', 'like', '%' . $request->input('container_no') . '%');
        // }

        // // Filter by invoice_no if it's provided
        // if ($request->has('invoice_no') && $request->input('invoice_no') !== '') {
        //     $query->where('invoice_no', 'like', '%' . $request->input('invoice_no') . '%');
        // }
        // dd($request->input('start_date'));
        
        // // Filter by start_date if it's provided
        // if ($request->has('start_date') && $request->input('start_date') !== '') {
        //     $query->where('created_at', '>=', $request->input('start_date'));
        // }

        // // Filter by end_date if it's provided
        // if ($request->has('end_date') && $request->input('end_date') !== '') {
        //     $query->where('created_at', '<=', $request->input('end_date'));
        // }

        // // Get the filtered results
        // $suspects = $query->get();

        // $suspects = collect();
        return view('pages.scan', compact('suspects'));
    }
}
