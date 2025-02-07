<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

            return redirect('home');
        }

        return back()->withErrors([
            'npk' => 'NPK or Password incorrect!',
        ]);
    }
}
