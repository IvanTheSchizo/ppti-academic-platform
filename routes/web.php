<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {return view('welcome');});
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');


Route::post('/login', [AuthController::class, 'processLogin']);

Route::get('/student', function () {return view('student'); });
Route::get('/student/profile', function () { return view('student-profile'); });
Route::get('/lecturer', function () {return view('lecturer'); });
Route::get('/lecturer/profile', function () { return view('lecturer-profile'); });
Route::get('/audit-log', function () { return view('audit-log'); });
Route::get('/course-records', function () { return view('course-records'); });