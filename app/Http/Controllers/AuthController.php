<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\ResponseFormatter;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('pages.login');
    }

    // public function loginWeb(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'npk' => ['required'],
    //         'password' => ['required'],
    //     ]);

    //     // $validator = Validator::make($request->all(), [
    //     //     'npk' => 'required|string',
    //     //     'password' => 'required|string',
    //     // ]);

    //     // if ($validator->fails()) {
    //     //     return redirect('/login')->withErrors($validator)->withInput();
    //     // }

    //     // if (Auth::attempt(['npk' => $request->npk, 'password' => $request->password])) {
    //     if (Auth::attempt(['npk' => $credentials['npk'], 'password' => $credentials['password']])) {
    //         $request->session()->regenerate();
    //         // dd(session()->all());
    //         // if (Auth::check()) {
    //         //     return 'User is logged in!';
    //         // } else {
    //         //     return 'User is not logged in!';
    //         // }
 
    //         return redirect()->intended('dashboard');
    //     }
    //     // if (Auth::attempt($request->only('npk', 'password'))) {
    //     //     $request->session()->regenerate();
 
    //     //     return redirect()->intended('home');
    //     // }
 
    //     return back()->withErrors('NPK or Password incorrect!');
    // }

    // public function loginWeb(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'npk' => ['required', 'string'],
    //         'password' => ['required', 'string'],
    //     ]);

    //     // Try to retrieve the user by npk
    //     $user = User::where('npk', $credentials['npk'])->first();

    //     if ($user && Hash::check($credentials['password'], $user->password)) {
    //         Auth::login($user);
    //         $request->session()->regenerate();
    //         return redirect()->intended('home');
    //     }

    //     return back()->withErrors([
    //         'npk' => 'NPK or Password incorrect!',
    //     ]);
    // }

    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'npk' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Try to retrieve the user by npk
        $user = User::where('npk', $credentials['npk'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            session(['loggedin' => true]);
            session(['user_id' => $user->user_id]);
            session(['name' => $user->name]);
            session(['npk' => $user->npk]);

            return redirect()->route('suspects.index');
        }

        return back()->withErrors([
            'npk' => 'NPK or Password incorrect!',
        ]);
    }

    public function loginApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'npk' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            // return response()->json(['error' => $validator->errors()], 400);
            return ResponseFormatter::error(null, $validator->errors()->first(), 400);
        }

        $user = User::where('npk', $request->input('npk'))->first();

        if ($user && Hash::check($request->input('password'), $user->password))
        {
            $responseData = [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'npk' => $user->npk,
            ];

            return ResponseFormatter::success($responseData, 'Login success');
        } else {
            return ResponseFormatter::error(null, 'NPK or Password incorrect', 400);
        }
    }

    public function logoutWeb(Request $request)
    {
        // Clear all session data
        $request->session()->flush();

        // Optionally, regenerate the session ID for security purposes
        $request->session()->regenerate();

        // Redirect to the login page (or any other page)
        return redirect()->route('login'); // Or the route you want after logout
    }
}
