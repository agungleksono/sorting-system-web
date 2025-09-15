<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Section;
use App\Models\Authority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $sections = Section::all();
        $authorities = Authority::all();

        return view('pages.user', compact('users', 'sections', 'authorities'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npk' => 'required|unique:users,npk', 
                'user_name' => 'required|string|max:30', 
                'password' => 'required|string|confirmed', 
                'section' => 'required|string', 
                'authority' => 'required|string', 
            ]);
    
            if ($validator->fails()) {
                return redirect('/users/management')->withErrors($validator)->withInput();
            }
    
            $user = User::create([
                'name' => $request->input('user_name'),
                'npk' => $request->input('npk'),
                'password' => Hash::make($request->input('password')),
                'section' => $request->input('section'),
                'authority' => $request->input('authority'),
                'is_active' => '1',
                'added_by' => session('npk'),
                'added_at' => date('Y-m-d H:i:s'),
            ]);
    
            return redirect('/users/management')->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            Log::error('Error storing user: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return redirect('/users/management')->with('error', 'Failed to store user!');
        }
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
        try {
            $validator = Validator::make($request->all(), [
                'npk' => 'required|string|max:10',
                'user_name' => 'required|string|max:30',
                'status' => 'required',
                'section' => 'required',
                'authority' => 'required',
            ]);
    
            if ($validator->fails()) {
                return redirect('/users/management')->withErrors($validator)->withInput();
            }
            
            $updated = User::where('user_id', $user_id)
                       ->update([
                            'name' => $request->input('user_name'),
                            'npk' => $request->input('npk'),
                            'is_active' => $request->input('status'),
                            'section' => $request->input('section'),
                            'authority' => $request->input('authority'),
                            'updated_by' => session('npk'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
    
            if ($updated) {
                return redirect('/users/management')->with('success', 'User updated successfully!');
            } else {
                return redirect('/users/management')->with('error', 'Failed to update user!');
            }
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect('/users/management')->with('error', 'Failed to update user!');
        }
    }

    public function destroy($userId)
    {
        try {
            User::where('user_id', $userId)->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return redirect()->route('users.index')->with('errors', 'Failed to delete data!' . $e->getMessage());
        }
    }
}
