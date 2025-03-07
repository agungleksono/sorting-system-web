<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SuspectCase;
use App\Models\ScanParameter;
use App\Models\ScanType;
use App\Models\Suspect;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;

class SuspectCaseController extends Controller
{
    public function indexApi()
    {
        // $cases = SuspectCase::all();
        $cases = DB::table('suspect_cases as a')
                ->join('suspects as b', 'a.suspect_case_id', '=', 'b.suspect_case_id')
                ->select(
                    'a.suspect_case_id',
                    'a.title',
                    'a.scan_parameter_code',
                    'a.scan_type_id',
                    'a.qr_length',
                    'a.is_closed',
                    DB::raw("SUM(CASE WHEN b.is_scanned = '1' THEN 1 ELSE 0 END) AS current_progress"),
                    DB::raw('COUNT(b.suspect_case_id) AS max_progress')
                )
                ->where('a.is_closed', '=', '0')
                ->groupBy('a.suspect_case_id', 'a.title', 'a.scan_parameter_code', 'a.scan_type_id', 'a.qr_length', 'a.is_closed')
                ->get();

        return ResponseFormatter::success($cases, 'Success get cases');
    }

    public function index()
    {
        $cases = DB::table('suspect_cases as a')
                ->leftJoin('suspects as b', 'a.suspect_case_id', '=', 'b.suspect_case_id')
                ->select(
                    'a.suspect_case_id',
                    'a.title',
                    'a.scan_parameter_code',
                    'a.scan_type_id',
                    'a.qr_length',
                    'a.is_closed',
                    'a.created_by',
                    'a.created_at',
                    DB::raw("SUM(CASE WHEN b.is_scanned = '1' THEN 1 ELSE 0 END) AS current_progress"),
                    DB::raw('COUNT(b.suspect_case_id) AS max_progress')
                )
                // ->where('a.is_closed', '=', '0')
                ->groupBy('a.suspect_case_id', 'a.title', 'a.scan_parameter_code', 'a.scan_type_id', 'a.qr_length', 'a.is_closed', 'a.created_by', 'a.created_at')
                ->get();

        return view('pages.cases.index', compact('cases'));
    }

    public function create()
    {
        $scanParameters = ScanParameter::all();
        $scanTypes = ScanType::all();
        return view('pages.cases.create', compact('scanParameters', 'scanTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'scanType' => 'required',
            'scanParameter' => 'required',
            'qrLength' => 'required',
            // 'stringStartIndex' => 'required',
            // 'stringLength' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('/cases/create')->withErrors($validator)->withInput();
        }

        $countSuspectCase = SuspectCase::count();
        $suspectCaseId = 'CASE' . str_pad($countSuspectCase + 1, 4, "0", STR_PAD_LEFT);

        $suspectCase = SuspectCase::create([
            'suspect_case_id' => $suspectCaseId,
            'title' => $request->input('title'),
            'scan_parameter_id' => $request->input('scanParameter'),
            'scan_type_id' => $request->input('scanType'),
            'qr_length' => $request->input('qrLength'),
            // 'string_start_index' => $request->input('stringStartIndex'),
            // 'string_length' => $request->input('stringLength'),
            'is_closed' => '0',
            'created_by' => session('npk'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect('/cases/create')->with('success', 'New Case created successfully!');
    }

    public function edit($suspectCaseId)
    {
        $suspectCase = SuspectCase::where('suspect_case_id', $suspectCaseId)->first();
        $scanParameters = ScanParameter::all();
        $scanTypes = ScanType::all();

        return view('pages.cases.edit', compact('suspectCase', 'scanParameters', 'scanTypes'));
    }

    public function update(Request $request, $suspectCaseId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'scanType' => 'required',
            'scanParameter' => 'required',
            'qrLength' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('/cases/create')->withErrors($validator)->withInput();
        }

        $update = SuspectCase::where('suspect_case_id', $suspectCaseId)
                            ->update([
                                'title' => $request->input('title'),
                                'scan_parameter_code' => $request->input('scanParameter'),
                                'scan_type_id' => $request->input('scanType'),
                                'qr_length' => $request->input('qrLength'),
                                'is_closed' => $request->input('caseStatus'),
                            ]);

        return redirect()->route('cases.edit', $suspectCaseId)->with('success', 'Case updated successfully!');
    }

    public function destroy($suspectCaseId)
    {
        DB::beginTransaction();
        try {
            SuspectCase::where('suspect_case_id', $suspectCaseId)->delete();
            Suspect::where('suspect_case_id', $suspectCaseId)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cases.index')->with('errors', 'Failed to delete data!' . $e->getMessage());
        }

        return redirect()->route('cases.index')->with('success', 'Case deleted successfully!');
    }

    public function store2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tittle' => 'required', 
            'scan_parameter' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('/users/management')->withErrors($validator)->withInput();
        }

        // $request->validate([
        //     'tittle' => 'required',
        //     'scan_parameter' => 'required',
        // ]);
        
        // // If validation fails, it will automatically redirect back with errors and input.

        $countSuspectCase = SuspectCase::count();
        $suspectCaseId = 'CASE' . str_pad($countSuspectCase, 4, "0", STR_PAD_LEFT);

        $suspectCase = SuspectCase::create([
            'suspect_case_id' => $suspectCaseId,
            'title' => $request->input('tittle'),
            'scan_parameter_id' => '',
            'qr_length' => '',
            'string_start_index' => '',
            'string_length' => '',
            'is_closed' => '0',
            'created_by' => session('npk'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect('/users/management')->with('success', 'Sorting Case created successfully!');
    }
}
