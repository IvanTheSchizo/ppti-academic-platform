<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseRecordController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// ====================
// Authentication
// ====================

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ====================
// Development
// ====================

if (app()->isLocal()) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
}

Route::middleware('auth')->group(function () {

    // ====================
    // Pages
    // ====================
    
    Route::get('/password', [AuthController::class, 'editPassword'])->name('password.edit');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/export', [StudentController::class, 'export'])->name('students.export');

    Route::get('/lecturers/export', [LecturerController::class, 'export'])->name('lecturers.export');
    Route::get('/lecturers', [LecturerController::class, 'index'])->name('lecturers.index');
    Route::get('/lecturers/{lecturer}', [LecturerController::class, 'show'])->name('lecturers.profile');

    Route::view('/courses', 'courses.index')->name('courses.index');

    Route::get('/course-records/export', [CourseRecordController::class, 'export'])->name('course-records.export');
    Route::get('/course-records', [CourseRecordController::class, 'index'])->name('course-records.index');

    Route::view('/classes', 'classes.index')->name('classes.index');
    Route::view('/grades', 'grades.index')->name('grades.index');

    Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // ====================
    // JSON API (used by the pages)
    // ====================

    Route::prefix('api')->name('api.')->group(function () {
        Route::apiResource('students', StudentController::class);
        Route::apiResource('lecturers', LecturerController::class);
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('course-records', CourseRecordController::class);
        Route::apiResource('batches', BatchController::class);
        Route::apiResource('class-groups', ClassGroupController::class);
        Route::apiResource('periods', PeriodController::class);
    });
});
