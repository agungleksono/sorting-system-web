<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('pages.user', compact('users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'npk' => 'required|unique:users,npk', 
            'name' => 'required|string|max:30', 
            'password' => 'required|string|confirmed', 
        ]);

        if ($validator->fails()) {
            return redirect('/users/management')->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->input('name'),
            'npk' => $request->input('npk'),
            'password' => $request->input('password'),
        ]);

        return redirect('/users/management')->with('success', 'User created successfully!');
    }

    public function update()
    {
        
    }

    public function deactivate()
    {

    }
}
