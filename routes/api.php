<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\ClassSubjectController;
use App\Http\Controllers\Api\ScheduleWizardController;

Route::prefix('v1')->group(function () {

    // Otentikasi
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // Tahun Ajaran
    Route::prefix('academic-years')->group(function () {
        Route::get('/', [AcademicYearController::class, 'index']);
        Route::post('/', [AcademicYearController::class, 'store']);
        Route::get('/{academicYear}', [AcademicYearController::class, 'show']);
        Route::put('/{academicYear}', [AcademicYearController::class, 'update']);
        Route::delete('/{academicYear}', [AcademicYearController::class, 'destroy']);
        Route::patch('/{academicYear}/set-active', [AcademicYearController::class, 'setActive']);
    });

    // Jenjang / Tingkat
    Route::prefix('grades')->group(function () {
        Route::get('/', [GradeController::class, 'index']);
        Route::post('/', [GradeController::class, 'store']);
        Route::get('/{grade}', [GradeController::class, 'show']);
        Route::put('/{grade}', [GradeController::class, 'update']);
        Route::delete('/{grade}', [GradeController::class, 'destroy']);
    });

    // Jurusan
    Route::prefix('majors')->group(function () {
        Route::get('/', [MajorController::class, 'index']);
        Route::post('/', [MajorController::class, 'store']);
        Route::get('/{major}', [MajorController::class, 'show']);
        Route::put('/{major}', [MajorController::class, 'update']);
        Route::delete('/{major}', [MajorController::class, 'destroy']);
    });

    // Data Guru
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::post('/', [TeacherController::class, 'store']);
        Route::get('/{teacher}', [TeacherController::class, 'show']);
        Route::put('/{teacher}', [TeacherController::class, 'update']);
        Route::delete('/{teacher}', [TeacherController::class, 'destroy']);
    });

    // Mata Pelajaran
    Route::prefix('subjects')->group(function () {
        Route::get('/', [SubjectController::class, 'index']);
        Route::post('/', [SubjectController::class, 'store']);
        Route::get('/{subject}', [SubjectController::class, 'show']);
        Route::put('/{subject}', [SubjectController::class, 'update']);
        Route::delete('/{subject}', [SubjectController::class, 'destroy']);
    });

    // Slot Waktu / Jam Pelajaran
    Route::prefix('time-slots')->group(function () {
        Route::get('/', [TimeSlotController::class, 'index']);
        Route::post('/', [TimeSlotController::class, 'store']);
        Route::get('/{timeSlot}', [TimeSlotController::class, 'show']);
        Route::put('/{timeSlot}', [TimeSlotController::class, 'update']);
        Route::delete('/{timeSlot}', [TimeSlotController::class, 'destroy']);
    });

    // Manajemen Kelas
    Route::prefix('classes')->group(function () {
        Route::get('/', [ClassController::class, 'index']);
        Route::post('/', [ClassController::class, 'store']);
        Route::get('/{class}', [ClassController::class, 'show']);
        Route::put('/{class}', [ClassController::class, 'update']);
        Route::delete('/{class}', [ClassController::class, 'destroy']);

        // Plotting Mapel per Kelas
        Route::prefix('{class}/subjects')->group(function () {
            Route::get('/', [ClassSubjectController::class, 'getByClass']);
            Route::post('/', [ClassSubjectController::class, 'store']);
        });
    });

    // Plotting Mapel & Guru
    Route::prefix('class-subjects')->group(function () {
        Route::put('/{classSubject}', [ClassSubjectController::class, 'update']);
        Route::delete('/{classSubject}', [ClassSubjectController::class, 'destroy']);
    });

    // Wizard Auto-Generate & Editor Jadwal
    Route::prefix('wizard')->group(function () {
        Route::get('/matrix/{class}', [ScheduleWizardController::class, 'getMatrix']);
        Route::get('/balance/{class}', [ScheduleWizardController::class, 'getBalance']);
        Route::post('/generate', [ScheduleWizardController::class, 'generate']);
        Route::post('/lock-slot', [ScheduleWizardController::class, 'lockSlot']);
        Route::post('/manual-assign', [ScheduleWizardController::class, 'manualAssign']);
        Route::post('/publish', [ScheduleWizardController::class, 'publish']);
    });

});