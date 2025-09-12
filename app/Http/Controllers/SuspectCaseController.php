<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SuspectCase;
use App\Models\ScanParameter;
use App\Models\ScanType;
use App\Models\Suspect;
use App\Models\QrCode;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuspectCaseController extends Controller
{
    public function indexApi()
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error fetching suspect cases: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return ResponseFormatter::error(null, 'Internal Server Error', 500);
        }
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

    public function show($suspectCaseId)
    {
        try {
            $case = SuspectCase::where('suspect_case_id', $suspectCaseId)->first();
    
            if (!$case) {
                return ResponseFormatter::error(null, 'Case not found', 404);
            }
    
            return ResponseFormatter::success($case, 'Get case data success!'); 
        } catch (\Exception $e) {
            Log::error('Error fetching suspect case by suspect Id: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return ResponseFormatter::error(null, 'Internal Server Error', 500);
        }
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
        try {
            // 1. Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'scan_type' => 'required|string',
                'scan_parameter' => 'required|string',
            ]);
    
            if ($validator->fails()) {
                return ResponseFormatter::error(null, $validator->errors()->first(), 400);
            }
            
            // 2. Generate suspect_case_id
            $newCaseId = $this->generateCaseId();
            
            // 3. Create new suspect case
            $suspectCase = SuspectCase::create([
                'suspect_case_id' => $newCaseId,
                'title' => $request->input('title'),
                'scan_parameter_code' => $request->input('scan_parameter'),
                'scan_type_id' => $request->input('scan_type'),
                'is_closed' => '0',
                'created_by' => $request->input('user_id') ?? null,
                'created_at' => now(),
            ]);
    
            return ResponseFormatter::success($suspectCase, 'New Case created successfully!');
        } catch (\Exception $e) {
            Log::error('Error storing suspect case: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return ResponseFormatter::error(null, 'Internal Server Error', 500);
        }
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
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'scan_type' => 'required',
                'scan_parameter' => 'required',
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
                'scan_parameter_code' => $request->input('scan_parameter'),
                'scan_type_id' => $request->input('scan_type'),
                'is_closed' => $request->input('is_closed'),
                'updated_by' => $request->input('user_id'),
                'updated_at' => now(),
            ]);
        
            return ResponseFormatter::success($case, 'Case updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updateing suspect case: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return ResponseFormatter::error(null, 'Internal Server Error', 500);
        }
    }


    public function destroy($suspectCaseId)
    {
        DB::beginTransaction();

        try {
            $deleteQr = QrCode::where('suspect_case_id', $suspectCaseId)->delete();
            $deletedCase = SuspectCase::where('suspect_case_id', $suspectCaseId)->delete();
            $deletedSuspects = Suspect::where('suspect_case_id', $suspectCaseId)->delete();

            DB::commit();

            if (!$deletedCase) {
                return ResponseFormatter::error(null, 'Case not found or already deleted.', 404);
            }
    
            return ResponseFormatter::success(null, 'Case deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting suspect case: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return ResponseFormatter::error(null, 'Internal Server Error', 500);
        }
    }

    public function generateCaseId()
    {
        $caseId = 'CASE0001';
        $latestCase = SuspectCase::orderBy('created_at', 'desc')->value('suspect_case_id');

        if ($latestCase) {
            $latestSequence = (int) substr($latestCase, -4);
            $newSequence = $latestSequence + 1;
            $caseId = 'CASE' . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
        }

        return $caseId;
    }
}
