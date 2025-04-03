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

        // Get appointment durations from config
        $appointmentTypes = config('appointment_types');

        // Define working hours (assuming all doctors have the same hours)
        $workStart = '09:00';
        $workEnd = '17:00';
        $appointmentsConfig = config('appointments');
        $interval = (int) $appointmentsConfig['appointment_interval']; // minutes

        // Get booked appointments within the week
        $bookedAppointments = Appointment::whereBetween('start_time', [$weekDates->first(), $weekDates->last()])
            ->get()
            ->groupBy(fn($appt) => Carbon::parse($appt->start_time)->toDateString());

        $availableAppointments = [];

        foreach ($appointmentTypes as $typeKey => $typeData) {
            $availableAppointments[$typeKey] = [];

            foreach ($weekDates as $date) {
                $availableTimes = [];

                // Get booked appointments for this specific date
                $bookedForDate = $bookedAppointments[$date] ?? collect();

                // Convert booked appointments into [start, end] timestamps
                $bookedTimes = $bookedForDate->map(fn($appt) => [
                    'start' => Carbon::parse($appt->start_time),
                    'end' => Carbon::parse($appt->start_time)->
                      addMinutes(AppointmentType::getAppointmentDuration($appt->appointment_type))
                ]);

                // Generate time slots for the day
                $start = Carbon::parse("{$date} {$workStart}");
                $end = Carbon::parse("{$date} {$workEnd}");
                $duration = (int) $typeData['duration_in_mins'];

                while ($start->lt($end)) {
                    $slotStart = $start->copy();
                    $slotEnd = $slotStart->copy()->addMinutes($duration);
    
                    // Ensure the slot fits within working hours
                    if ($slotEnd->gt($end)) break;
    
                    // Check if this slot overlaps with any existing appointment for the date
                    $isOverlapping = $bookedTimes->contains(fn($appt) =>
                        $slotStart->lt($appt['end']) && $slotEnd->gt($appt['start'])
                    );

                    if (!$isOverlapping) {
                        $availableTimes[] = $slotStart->format('H:i');
                    }

                    // Move to next 10-minute slot
                    $start->addMinutes($interval); 
                }

                if (!empty($availableTimes)) {
                    $availableAppointments[$typeKey][] = [
                        'date' => $date,
                        'available_start_time' => $availableTimes,
                    ];
                }
            }
        }

        return response()->json($availableAppointments);
    }
}
