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
        $types = config('appointment_types');

        // Return the patient's upcoming appointments
        $appointments = Appointment::where('patient_id', $patient->id)
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($appointment) use ($doctors, $types) {
                return [
                    'id' => $appointment->id,
                    'appointment_type' => $appointment->appointment_type,
                    'appointment_name' => $types[$appointment->appointment_type]['name'],
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

    public function destroy(Request $request, $patientId, $appointmentId)
    {
        $token = $request->bearerToken();

        if (!$token || !($patient = Patient::validateToken($patientId, $token))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Find the appointment
        $appointment = Appointment::where('id', $appointmentId)
                                ->where('patient_id', $patientId)
                                ->first();

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found.'], 404);
        }

        // Check if the appointment has already passed
        if ($appointment->end_time < Carbon::now()) {
            return response()->json(['error' => 'Cannot delete past appointments.'], 400);
        }

        // Delete the appointment
        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully.'], 204);
    }
}