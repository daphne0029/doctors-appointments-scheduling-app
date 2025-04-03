<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hello', function () {
    return "Hello, World!";
});

Route::get('/appointments', function () {
    return view('appointments');
});

Route::get('/patients', function () {
    return view('patients');
});
