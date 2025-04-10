<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PatientAppointmentController;

Route::post('/patients/login', [PatientController::class, 'login']);

Route::get('/doctors', [DoctorController::class, 'index']);
Route::get('/doctors/{id}', [DoctorController::class, 'show']);

Route::post('/patients', [PatientController::class, 'store']);
Route::get('/patients/{patientId}', [PatientController::class, 'show']);


Route::middleware('auth')->group(function () {
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/available-appointments', [AppointmentController::class, 'availableAppointments']);
});

Route::middleware('auth.patient')->group(function () {
    Route::post('/patients/{patientId}/appointments', [PatientAppointmentController::class, 'store']);
    Route::get('/patients/{patientId}/appointments/upcomings', [PatientAppointmentController::class, 'index']);
    Route::delete('/patients/{id}/appointments/{appointmentId}', [PatientAppointmentController::class, 'destroy']);
});

