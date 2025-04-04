<?php

use Illuminate\Support\Facades\Route;

Route::get('/appointments', function () {
    return view('appointments');
});

Route::get('/patients', function () {
    return view('patients');
});
