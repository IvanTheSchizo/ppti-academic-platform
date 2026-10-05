<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

if (app()->isLocal()) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
}

Route::middleware('auth')->group(function () {
    Route::view('/students', 'students.index')->name('students.index');
    Route::view('/lecturers', 'lecturers.index')->name('lecturers.index');
    Route::view('/course-records', 'course-records.index')->name('course-records.index');
    Route::view('/audit-logs', 'audit-logs.index')->name('audit-logs.index');
    Route::view('/courses', 'courses.index')->name('courses.index');
    Route::view('/classes', 'classes.index')->name('classes.index');
    Route::view('/grades', 'grades.index')->name('grades.index');
});