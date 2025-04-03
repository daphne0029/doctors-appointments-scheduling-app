<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PatientAppointmentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/patients/login', [PatientController::class, 'login']);

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);

Route::post('/patients', [PatientController::class, 'store']);
Route::get('/patients/{id}', [PatientController::class, 'show']);

Route::get('/appointments', [AppointmentController::class, 'index']);

Route::post('/patients/{id}/appointments', [PatientAppointmentController::class, 'store']);
Route::get('/patients/{id}/appointments/upcomings', [PatientAppointmentController::class, 'index']);
Route::delete('/patients/{id}/appointments/{appointmentId}', [PatientAppointmentController::class, 'destroy']);
