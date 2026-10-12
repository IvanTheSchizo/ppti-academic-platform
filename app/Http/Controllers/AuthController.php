<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('students.index'));
        }

        return back()
            ->withErrors([
                'username' => 'Invalid username or password.',
            ])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function editPassword()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => [
                'bail', 'required', 'string', 'min:8', 'different:current_password',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[\d\W_]/',
            ],
            'password_confirmation' => ['required', 'same:password'],
        ], [
            'current_password.current_password' => 'Current password is incorrect.',
            'password_confirmation.same' => 'Passwords do not match.',
            'password.different' => 'New password must be different from the current password.',
            'password.required' => 'Password does not meet requirements.',
            'password.min' => 'Password does not meet requirements.',
            'password.regex' => 'Password does not meet requirements.',
        ]);

        $request->user()->forceFill(['password' => Hash::make($data['password'])])->save();

        return redirect()->route('students.index')->with('success', 'Password updated successfully.');
    }
}