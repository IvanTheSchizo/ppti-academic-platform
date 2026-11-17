<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseRecordController;
use App\Http\Controllers\InputDataController;
use App\Http\Controllers\InputDataImportController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ProfilePhotoController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('students.index')
        : redirect()->route('login');
})->name('home');

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
    Route::put('/account/display-name', [AuthController::class, 'updateDisplayName'])->name('account.display-name.update');
    Route::get('/account/photo', [ProfilePhotoController::class, 'showAdmin'])->name('account.photo');
    Route::post('/account/photo', [ProfilePhotoController::class, 'updateAdmin'])->name('account.photo.update');
    Route::delete('/account/photo', [ProfilePhotoController::class, 'destroyAdmin'])->name('account.photo.destroy');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/export', [StudentController::class, 'export'])->name('students.export');
    Route::post('/students/photos/preview', [ProfilePhotoController::class, 'previewBulk'])->defaults('type', 'students')->name('students.photos.preview');
    Route::post('/students/photos/commit', [ProfilePhotoController::class, 'commitBulk'])->defaults('type', 'students')->name('students.photos.commit');
    Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.profile');
    Route::get('/students/{student}/export', [StudentController::class, 'exportProfile'])->name('students.profile.export');
    Route::get('/students/{student}/photo', [ProfilePhotoController::class, 'showStudent'])->name('students.photo');
    Route::post('/students/{student}/photo', [ProfilePhotoController::class, 'updateStudent'])->name('students.photo.update');
    Route::delete('/students/{student}/photo', [ProfilePhotoController::class, 'destroyStudent'])->name('students.photo.destroy');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');

    Route::get('/lecturers/export', [LecturerController::class, 'export'])->name('lecturers.export');
    Route::post('/lecturers/photos/preview', [ProfilePhotoController::class, 'previewBulk'])->defaults('type', 'lecturers')->name('lecturers.photos.preview');
    Route::post('/lecturers/photos/commit', [ProfilePhotoController::class, 'commitBulk'])->defaults('type', 'lecturers')->name('lecturers.photos.commit');
    Route::get('/lecturers', [LecturerController::class, 'index'])->name('lecturers.index');
    Route::get('/lecturers/{lecturer}/export', [LecturerController::class, 'exportProfile'])->name('lecturers.profile.export');
    Route::get('/lecturers/{lecturer}/photo', [ProfilePhotoController::class, 'showLecturer'])->name('lecturers.photo');
    Route::post('/lecturers/{lecturer}/photo', [ProfilePhotoController::class, 'updateLecturer'])->name('lecturers.photo.update');
    Route::delete('/lecturers/{lecturer}/photo', [ProfilePhotoController::class, 'destroyLecturer'])->name('lecturers.photo.destroy');
    Route::put('/lecturers/{lecturer}', [LecturerController::class, 'update'])->name('lecturers.update');
    Route::get('/lecturers/{lecturer}', [LecturerController::class, 'show'])->name('lecturers.profile');

    Route::view('/courses', 'courses.index')->name('courses.index');

    Route::get('/course-records/export', [CourseRecordController::class, 'export'])->name('course-records.export');
    Route::get('/course-records', [CourseRecordController::class, 'index'])->name('course-records.index');
    Route::get('/course-records/{courseRecord}', [CourseRecordController::class, 'show'])->name('course-records.show');
    Route::put('/course-records/{courseRecord}', [CourseRecordController::class, 'update'])->name('course-records.update');
    Route::post('/course-records/{courseRecord}/students', [CourseRecordController::class, 'addStudent'])->name('course-records.students.store');
    Route::delete('/course-records/{courseRecord}/students/bulk', [CourseRecordController::class, 'removeStudents'])->name('course-records.students.bulk-destroy');
    Route::delete('/course-records/{courseRecord}/students/{student}', [CourseRecordController::class, 'removeStudent'])->name('course-records.students.destroy');
    Route::get('/course-records/{courseRecord}/export', [CourseRecordController::class, 'exportRecord'])->name('course-records.show.export');

    Route::view('/classes', 'classes.index')->name('classes.index');
    Route::view('/grades', 'grades.index')->name('grades.index');

    Route::prefix('input-data')->name('input-data.')->controller(InputDataController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/students/search', 'searchStudents')->name('students.search');
        Route::get('/course-records/{courseRecord}/students', 'recordStudents')->name('record-students');
        Route::get('/{tab}', 'show')->whereIn('tab', InputDataController::TABS)->name('show');
        Route::post('/{tab}', 'store')->whereIn('tab', InputDataController::TABS)->name('store');
    });

    Route::prefix('input-data/{tab}/import')->name('input-data.import.')->controller(InputDataImportController::class)
        ->whereIn('tab', InputDataController::IMPORT_TABS)->group(function () {
            Route::get('/template', 'template')->name('template');
            Route::post('/preview', 'preview')->name('preview');
            Route::post('/commit', 'commit')->name('commit');
        });

    Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // ====================
    // JSON API (used by the pages)
    // ====================

    Route::prefix('api')->name('api.')->group(function () {
        Route::apiResource('students', StudentController::class)->except(['store', 'update']);
        Route::apiResource('lecturers', LecturerController::class)->except('store');
        Route::apiResource('courses', CourseController::class);
        Route::apiResource('course-records', CourseRecordController::class);
        Route::apiResource('batches', BatchController::class);
        Route::apiResource('class-groups', ClassGroupController::class);
        Route::apiResource('periods', PeriodController::class);
    });
});
