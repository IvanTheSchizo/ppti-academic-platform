<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\PeriodController;
use Illuminate\Support\Facades\Route;
use App\Models\Student;

Route::get('/student', function () {
    $students = Student::with('classGroup.batch')->get();

    return view('student', compact('students'));
})->middleware('auth')->name('student.index');

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

Route::get('/lecturer', function () {return view('lecturer'); });
Route::get('/lecturer/profile', function () { return view('lecturer-profile'); });
Route::get('/audit-log', function () { return view('audit-log'); });
Route::get('/course-records', function () { return view('course-records'); });

Route::apiResource('lecturers', LecturerController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('courses', CourseController::class);
Route::apiResource('batches', BatchController::class);
Route::apiResource('class-groups', ClassGroupController::class);
Route::apiResource('periods', PeriodController::class);