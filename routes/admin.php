<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('web')->name('admin.')->group(function () {
    Route::get('', function () {
        if (auth()->check()) {
            return to_route('admin.dashboard');
        }

        return view('admin.login');
    })->name('login');

    Route::post('', [Admin\LoginController::class, 'login']);

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get(
            '/export-completed-questionnaires-by-student-id',
            [Admin\ExportCompletedQuestionnairesByStudentIdController::class, 'index'],
        )
            ->name('export.completed-questionnaires-by-student-id');

        Route::post('/logout', [Admin\LoginController::class, 'logout'])
            ->name('logout');
    });

    // TODO other routes
});