<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
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
     * Get available appointments for the next 7 days (excluding today)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableAppointments()
    {
        // Get today's date & the next 7 days (excluding today)
        $today = now()->toDateString();
        $weekDates = collect(range(1, 7))->map(fn($i) => now()->addDays($i)->toDateString());

        $appointmentTypes = config('appointment_types');
        $doctors = config('doctors');
        $appointmentsConfig = config('appointments');

        // Define working hours (assuming all doctors have the same hours)
        $interval = (int) $appointmentsConfig['appointment_interval']; // minutes

        // Get booked appointments and group them by date + doctor ID
        $bookedAppointments = Appointment::whereBetween('start_time', [$weekDates->first(), $weekDates->last()])
            ->get()
            ->groupBy(fn($appt) => $appt->doctor_id . '-' . Carbon::parse($appt->start_time)->toDateString());

        $availableAppointments = [];

        foreach ($appointmentTypes as $typeKey => $typeData) {
            $availableAppointments[$typeKey] = [];
    
            foreach ($weekDates as $date) {
                $dayOfWeek = Carbon::parse($date)->format('l'); // e.g., "Wednesday"
    
                foreach ($doctors as $doctorId => $doctorData) {
                    $doctorSchedules = collect($doctorData['schedules'])->where('day_of_week', $dayOfWeek);
                    if ($doctorSchedules->isEmpty()) continue; // Skip if doctor doesn't work that day
    
                    $duration = (int) $typeData['duration_in_mins'];
                    $bookedForDoctorAndDate = $bookedAppointments[$doctorId . '-' . $date] ?? collect();
    
                    // Convert booked appointments into [start, end] timestamps
                    $bookedTimes = $bookedForDoctorAndDate->map(fn($appt) => [
                        'start' => Carbon::parse($appt->start_time),
                        'end' => Carbon::parse($appt->start_time)->
                            addMinutes(AppointmentType::getAppointmentDuration($appt->appointment_type))
                    ]);
    
                    $availableTimes = [];
    
                    // Loop through each working period on this day
                    foreach ($doctorSchedules as $schedule) {
                        $startTime = Carbon::parse("{$date} {$schedule['start_time']}");
                        $endTime = Carbon::parse("{$date} {$schedule['end_time']}");
    
                        while ($startTime->lt($endTime)) {
                            $slotStart = $startTime->copy();
                            $slotEnd = $slotStart->copy()->addMinutes($duration);
    
                            if ($slotEnd->gt($endTime)) break; // Ensure it fits within the working period
    
                            // Check against ALL booked appointments before adding
                            $isOverlapping = false;
                            foreach ($bookedTimes as $appt) {
                                if ($slotStart->lt($appt['end']) && $slotEnd->gt($appt['start'])) {
                                    $isOverlapping = true;
                                    break; // No need to check further
                                }
                            }
    
                            if (!$isOverlapping) {
                                $availableTimes[] = $slotStart->format('H:i');
                            }
    
                            $startTime->addMinutes($interval); // Move to the next slot
                        }
                    }
    
                    if (!empty($availableTimes)) {
                        $availableAppointments[$typeKey][] = [
                            'date' => $date,
                            'doctor' => $doctorData['name'],
                            'doctor_id' => $doctorId,
                            'available_start_time' => $availableTimes,
                        ];
                    }
                }
            }
        }

        return response()->json($availableAppointments);
    }
}
