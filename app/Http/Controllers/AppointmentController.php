<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Create an appointment
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
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

    /**
     * Retrieve all appointments
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $appointments = Appointment::query();

        // Filter by appointment type (if provided)
        if ($request->has('appointment_type')) {
            $appointments->where('appointment_type', $request->input('appointment_type'));
        }

        // Filter by doctor (if provided)
        if ($request->has('doctor_id')) {
            $appointments->where('doctor_id', $request->input('doctor_id'));
        }

        // Optionally, you can add sorting by a column (e.g., start time)
        if ($request->has('sort_by')) {
            $appointments->orderBy($request->input('sort_by'), $request->input('sort_direction', 'asc'));
        }

        // Get the results (can be paginated)
        $appointments = $appointments->paginate(10);

        return response()->json([
            'appointments' => $appointments,
        ]);
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
