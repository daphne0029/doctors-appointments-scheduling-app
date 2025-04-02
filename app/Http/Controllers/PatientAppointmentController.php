<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
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
    
        // Return the patient's appointments
        $appointments = Appointment::where('patient_id', $patientId)->get();
    
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

        // Load the appointment types from the config
        $appointmentTypes = array_keys(config('appointment_types'));

        $validator = Validator::make($request->all(),[
            'patient_id'         => 'required|exists:patients,id',
            'doctor_id'          => 'required|integer',
            'appointment_type'   => 'required|string|in:' . implode(',', $appointmentTypes),
            'start_time' => 'required|date',
            'end_time'   => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Get doctor schedule from config
        $doctorSchedule = config('doctors.' . $request->doctor_id . '.schedules');
        if (!$doctorSchedule) {
          return response()->json(['error' => 'No doctor found. Please check the doctor ID.'], 400);
        }

        $appointmentStartTime = Carbon::parse($request->start_time);
        $appointmentEndTime = Carbon::parse($request->end_time);

        // Check if doctor is available
        $isAvailable = $this->isDoctorAvailable($doctorSchedule, $appointmentStartTime, $appointmentEndTime);
        if (!$isAvailable) {
            return response()->json(['error' => 'Doctor is not available at the selected time.'], 400);
        }

        // Check for overlaps
        if (Appointment::isOverlapping($request->doctor_id, $appointmentStartTime, $appointmentEndTime)) {
            return response()->json([
                'error' => 'The appointment time overlaps with an existing appointment.'
            ], 400);  // 400 Bad Request
        }

        $appointment = Appointment::create($request->only([
          'patient_id', 'doctor_id', 'appointment_type', 'start_time', 'end_time'
        ]));

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment,
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

    /**
     * Check if the doctor is available at the requested appointment time
     * @param array $schedule
     * @param Carbon $startTime
     * @param Carbon $endTime
     */
    private function isDoctorAvailable($schedule, Carbon $startTime, Carbon $endTime)
    {
        foreach ($schedule as $workingHours) {
            $workingDay = $workingHours['day_of_week'];
            
            // Check if the requested time matches the doctor's working schedule
            if ($startTime->is($workingDay)) {
                $selectedDate = $startTime->toDateString();
                $workingStart = Carbon::parse("$selectedDate {$workingHours['start_time']}");
                $workingEnd = Carbon::parse("$selectedDate {$workingHours['end_time']}");
                
                // If the requested time is within the working hours
                if ($startTime->between($workingStart, $workingEnd) && $endTime->between($workingStart, $workingEnd)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}