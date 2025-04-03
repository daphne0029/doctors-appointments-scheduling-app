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
        $appointmentsConfig = config('appointments');
        $interval = (int) $appointmentsConfig['appointment_interval']; // minutes
        $doctors = config('doctors'); // Get doctor schedules from config

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
                    // Find this doctor's schedule for the given day
                    $doctorSchedule = collect($doctorData['schedules'])->firstWhere('day_of_week', $dayOfWeek);

                    if (!$doctorSchedule) continue; // Skip if doctor doesn't work that day
    
                    $startTime = Carbon::parse("{$date} {$doctorSchedule['start_time']}");
                    $endTime = Carbon::parse("{$date} {$doctorSchedule['end_time']}");
                    $duration = (int) $typeData['duration_in_mins'];
    
                    $availableTimes = [];
                    $bookedForDoctorAndDate = $bookedAppointments[$doctorId . '-' . $date] ?? collect();
    
                    // Convert booked appointments into [start, end] timestamps
                    $bookedTimes = $bookedForDoctorAndDate->map(fn($appt) => [
                        'start' => Carbon::parse($appt->start_time),
                        'end' => Carbon::parse($appt->start_time)->
                          addMinutes(AppointmentType::getAppointmentDuration($appt->appointment_type)),
                    ]);
                    // print $bookedTimes;
    
                    while ($startTime->lt($endTime)) {
                        $slotStart = $startTime->copy();
                        $slotEnd = $slotStart->copy()->addMinutes($duration);
    
                        if ($slotEnd->gt($endTime)) break; // Ensure it fits in working hours
    
                        // print $slotStart->format('H:i');
                        // Check against ALL booked appointments before adding
                        $isOverlapping = false;
                        foreach ($bookedTimes as $appt) {
                            if ($slotStart->lt($appt['end']) && $slotEnd->gt($appt['start'])) {
                                $isOverlapping = true;
                                break; // No need to check further
                            }
                        }
                        // var_dump($isOverlapping);
    
                        if (!$isOverlapping) {
                            $availableTimes[] = $slotStart->format('H:i');
                        }
    
                        $startTime->addMinutes($interval); // Move to next slot
                    }
    
                    if (!empty($availableTimes)) {
                        $availableAppointments[$typeKey][] = [
                            'date' => $date,
                            'doctor' => $doctorData['name'],
                            'available_start_time' => $availableTimes,
                        ];
                    }
                }
            }
        }

        return response()->json($availableAppointments);
    }
}
