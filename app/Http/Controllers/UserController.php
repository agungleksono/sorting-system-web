<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

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
            'user_name' => 'required|string|max:30', 
            'password' => 'required|string|confirmed', 
        ]);

        if ($validator->fails()) {
            return redirect('/users/management')->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->input('user_name'),
            'npk' => $request->input('npk'),
            'password' => Hash::make($request->input('password')),
            'is_active' => '1'
        ]);

        return redirect('/users/management')->with('success', 'User created successfully!');
    }

    public function edit($user_id)
    {
        // Find the user by user_id (assuming the primary key is user_id)
        $user = User::where('user_id', $user_id)->firstOrFail();
        
        // Return the user data as JSON for AJAX to populate the form
        return response()->json($user);
    }

    public function update(Request $request, $user_id)
    {
        // Validate the input data
        // $request->validate([
        //     'npk' => 'required|string|max:10',
        //     'user_name' => 'required|string|max:30',
        //     'status' => 'required',
        // ]);

        $validator = Validator::make($request->all(), [
            'npk' => 'required|string|max:10',
            'user_name' => 'required|string|max:30',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('/users/management')->withErrors($validator)->withInput();
        }

        // Find the user by user_id (assuming the primary key is user_id)
        // $user = User::where('user_id', $user_id)->firstOrFail();
        
        $updated = User::where('user_id', $user_id)
                   ->update([
                        'name' => $request->input('user_name'),
                        'npk' => $request->input('npk'),
                        'is_active' => $request->input('status')
                    ]);
        
        // Update the user details
        // $user->name = $request->input('user_name');
        // $user->npk = $request->input('npk');
        // $user->is_active = $request->input('status');
        // $user->save(); // Save the updated user data

        if ($updated) {
            return redirect('/users/management')->with('success', 'User updated successfully!');
        } else {
            return redirect('/users/management')->with('error', 'Failed to update user!');
        }

        // return redirect('/users/management')->with('success', 'User updated successfully!');
    }

    public function deactivate()
    {
        
    }
}
