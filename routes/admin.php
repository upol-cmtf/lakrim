<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('', fn() => view('admin.login'))
        ->name('login');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', function () {
            dump('Admin dashboard');
            exit;
        });
    });

    // TODO other routes
});