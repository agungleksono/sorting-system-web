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
        
        $bearerToken = env('BEARER_TOKEN');

        return view('pages.cases.index', compact('cases', 'bearerToken'));
    }

    public function create()
    {
        $scanParameters = ScanParameter::all();
        $scanTypes = ScanType::all();
        $bearerToken = env('BEARER_TOKEN');

        return view('pages.cases.create', compact('scanParameters', 'scanTypes', 'bearerToken'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'scanType' => 'required|string',
            'scanParameter' => 'required|string',
            'qrLength' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        if (!empty($request->input('qrContent')) && ($request->input('qrLength') != strlen($request->input('qrContent')))) {
            return ResponseFormatter::error(null, 'Panjang karakter QR tidak sesuai dengan hasil scan', 400);
        }

        $latestCase = SuspectCase::orderBy('created_at', 'desc')->value('suspect_case_id');

        if ($latestCase) {
            $number = (int) substr($latestCase, -4);
            $newNumber = $number + 1;
            $newCaseId = 'CASE' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } else {
            $newCaseId = 'CASE0001';
        }

        $suspectCase = SuspectCase::create([
            'suspect_case_id' => $newCaseId,
            'title' => $request->input('title'),
            'scan_parameter_code' => $request->input('scanParameter'),
            'scan_type_id' => $request->input('scanType'),
            'qr_length' => $request->input('qrLength'),
            'is_closed' => '0',
            'created_by' => $request->input('user_id') ?? null, // Or use session('npk') if needed
            'created_at' => now(),
        ]);

        return ResponseFormatter::success($suspectCase, 'New Case created successfully!');
    }

    public function edit($suspectCaseId)
    {
        $suspectCase = SuspectCase::where('suspect_case_id', $suspectCaseId)->first();
        $scanParameters = ScanParameter::all();
        $scanTypes = ScanType::all();
        $bearerToken = env('BEARER_TOKEN');

        return view('pages.cases.edit', compact('suspectCase', 'scanParameters', 'scanTypes', 'bearerToken'));
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
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        $case = SuspectCase::find($suspectCaseId);

        if (!$case) {
            return ResponseFormatter::error(null, 'Case not found', 404);
        }
    
        $case->update([
            'title' => $request->input('title'),
            'scan_parameter_code' => $request->input('scanParameter'),
            'scan_type_id' => $request->input('scanType'),
            'qr_length' => $request->input('qrLength'),
            'is_closed' => $request->input('is_closed'),
            'updated_by' => $request->input('user_id'),
            'updated_at' => now(),
        ]);
    
        return ResponseFormatter::success($case, 'Case updated successfully!');
    }


    public function destroy($suspectCaseId)
    {
        DB::beginTransaction();

        try {
            $deletedCase = SuspectCase::where('suspect_case_id', $suspectCaseId)->delete();
            $deletedSuspects = Suspect::where('suspect_case_id', $suspectCaseId)->delete();

            DB::commit();

            if (!$deletedCase) {
                return ResponseFormatter::error(null, 'Case not found or already deleted.', 404);
            }
    
            return ResponseFormatter::success(null, 'Case deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseFormatter::error(null, 'Failed to delete case. ' . $e->getMessage(), 500);
        }
    }
}
