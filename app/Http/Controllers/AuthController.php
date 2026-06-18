<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // show login form
    public function showLoginForm()
    {
        // if already logged in, redirect to dashboard
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }
        return view('auth.login');
    }

    // process login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email','password');

        // try to authenticate
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    // show register form (optional)
    public function showRegisterForm()
    {
        abort(404);
    }

    // register user (optional)
    public function register(Request $request)
    {
        abort(404);
    }

    // logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
