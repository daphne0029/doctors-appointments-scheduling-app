<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Patient;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PatientAppointmentController extends Controller
{
    public function index(Request $request, $patientId)
    {
        $token = $request->bearerToken();

        if (!$token || !($patient = Patient::validateToken($patientId, $token))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $doctors = config('doctors');

        // Return the patient's upcoming appointments
        $appointments = Appointment::where('patient_id', $patient->id)
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($appointment) use ($doctors) {
                return [
                    'id' => $appointment->id,
                    'appointment_type' => $appointment->appointment_type,
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'doctor_id' => $appointment->doctor_id,
                    'doctor_name' => $doctors[$appointment->doctor_id]['name'] ?? 'Unknown Doctor',
                ];
            });

        return response()->json(['appointments' => $appointments]);
    }

    /**
     * Create an appointment
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $patientId)
    {
        $token = $request->bearerToken();
    
        if (!$token || !($patient = Patient::validateToken($patientId, $token))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $appointmentTypes = array_keys(config('appointment_types'));
        $validator = Validator::make($request->all(),[
            'patient_id'         => 'required|exists:patients,id',
            'doctor_id'          => 'required|integer',
            'appointment_type'   => 'required|string|in:' . implode(',', $appointmentTypes),
            'start_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $service = new AppointmentService();
    
        $result = $service->createAppointment($request->all());
    
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 400);
        }
    
        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $result['appointment'],
        ], 201);
    }
}