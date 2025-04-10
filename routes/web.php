<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web.auth')->group(function () {
    Route::get('/appointments', function () {
        return view('appointments');
    });
});

Route::get('/patients', function () {
    return view('patients');
});
