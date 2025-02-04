<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SuspectsImport;
use App\Models\Suspect;

class SuspectController extends Controller
{
    public function importSuspects()
    {
        Excel::import(new SuspectsImport, $request->file('file'));
        echo('tes');
    }
}
