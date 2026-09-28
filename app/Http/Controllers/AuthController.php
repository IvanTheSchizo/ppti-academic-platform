<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function processLogin(Request $request)
    {
        // Bypass sementara biar langsung masuk ke desain Student List
        return redirect('/students');
    }
}