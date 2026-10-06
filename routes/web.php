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

// ====================
// Authentication
// ====================

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');


// ====================
// Development
// ====================

if (app()->isLocal()) {
    Route::view('/styleguide', 'styleguide')
        ->name('styleguide');
}


// ====================
// Authenticated Pages
// ====================

Route::middleware('auth')->group(function () {

    Route::get('/students', [StudentController::class, 'index'])
        ->name('students.index');
        
    Route::get('/students/export', [StudentController::class, 'export'])
        ->name('students.export');

    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    Route::view('/students', 'students.index')
        ->name('students.index');

    Route::view('/lecturers', 'lecturers.index')
        ->name('lecturers.index');

    Route::view('/course-records', 'course-records.index')
        ->name('course-records.index');

    Route::view('/audit-logs', 'audit-logs.index')
        ->name('audit-logs.index');

    Route::view('/courses', 'courses.index')
        ->name('courses.index');

    Route::view('/classes', 'classes.index')
        ->name('classes.index');

    Route::view('/grades', 'grades.index')
        ->name('grades.index');

    Route::view('/lecturer', 'lecturer');
    Route::view('/lecturer/profile', 'lecturer-profile');
    Route::view('/audit-log', 'audit-log');
    Route::view('/course-records', 'course-records');
});


// ====================
// API Resource Routes
// ====================

Route::apiResource('lecturers', LecturerController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('courses', CourseController::class);
Route::apiResource('batches', BatchController::class);
Route::apiResource('class-groups', ClassGroupController::class);
Route::apiResource('periods', PeriodController::class);