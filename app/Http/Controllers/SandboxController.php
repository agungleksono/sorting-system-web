<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SandboxController extends Controller
{
    public function test()
    {
        $users = User::all();
        dd($users);
    }
}
