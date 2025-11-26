<?php

use App\Http\Controllers\Admin\DataEntryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard-metrics', [DashboardController::class, 'metrics'])->name('dashboard.metrics');

    Route::get('/admin/data-entry', [DataEntryController::class, 'index'])->name('admin.data-entry');
    Route::post('/admin/programs', [DataEntryController::class, 'storeProgram'])->name('admin.programs.store');
    Route::delete('/admin/programs/{program}', [DataEntryController::class, 'destroyProgram'])->name('admin.programs.destroy');
    Route::post('/admin/participants', [DataEntryController::class, 'storeParticipant'])->name('admin.participants.store');
    Route::post('/admin/participants/import', [DataEntryController::class, 'importParticipants'])->name('admin.participants.import');
    Route::delete('/admin/participants/{participant}', [DataEntryController::class, 'destroyParticipant'])->name('admin.participants.destroy');
    Route::post('/admin/enrollments', [DataEntryController::class, 'storeEnrollment'])->name('admin.enrollments.store');
    Route::patch('/admin/enrollments/{enrollment}', [DataEntryController::class, 'updateParticipant'])->name('admin.enrollments.update');
    Route::post('/admin/assessments', [DataEntryController::class, 'storeAssessment'])->name('admin.assessments.store');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';
