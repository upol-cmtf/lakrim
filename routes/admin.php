<?php

use Illuminate\Support\Facades\Route;

Route::prefix('')->group(function () {
    Route::get('', function () {
        dump('Admin login');
        exit;
    });

    // TODO admin middleware to authenticate
});