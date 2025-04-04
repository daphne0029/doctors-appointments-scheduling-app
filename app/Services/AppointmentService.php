<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentService
{
    private int $interval;

    public function __construct()
    {
        $this->interval = (int) config('appointments.appointment_interval');
    }

    /**
     * Get available appointment slots for the next few days.
     *
     * @param int $next_number_of_days
     * @return array
     */
    public function getAvailableAppointments(int $next_number_of_days): array
    {
        $weekDates = $this->getWeekDates($next_number_of_days);
        $appointmentTypes = config('appointment_types');
        $doctors = config('doctors');

        $bookedAppointments = $this->getBookedAppointments($weekDates);
        $availableAppointments = [];

        foreach ($appointmentTypes as $typeKey => $typeData) {
            $availableAppointments[$typeKey] = $this->getAvailableSlotsForType($typeData, $weekDates, $doctors, $bookedAppointments);
        }

        return $availableAppointments;
    }

    /**
     * Get an array of dates for the next 7 days (excluding today).
     *
     * @param int $numberOfDays
     * @return \Illuminate\Support\Collection
     */
    private function getWeekDates(int $numberOfDays): Collection
    {
        return collect(range(1, $numberOfDays))->map(fn($i) => now()->addDays($i)->toDateString());
    }

    /**
     * Retrieve booked appointments for the upcoming week and group them by doctor and date.
     *
     * @param \Illuminate\Support\Collection $weekDates
     * @return \Illuminate\Support\Collection
     */
    private function getBookedAppointments(Collection $weekDates): Collection
    {
        return Appointment::whereBetween('start_time', [$weekDates->first(), $weekDates->last()])
            ->get()
            ->groupBy(fn($appt) => $appt->doctor_id . '-' . Carbon::parse($appt->start_time)->toDateString());
    }

    /**
     * Get available appointment slots for a specific appointment type.
     *
     * @param array $typeData The appointment type configuration.
     * @param \Illuminate\Support\Collection $weekDates The dates to check availability for.
     * @param array $doctors The list of doctors and their schedules.
     * @param \Illuminate\Support\Collection $bookedAppointments The list of already booked appointments.
     * @return array
     */
    private function getAvailableSlotsForType(
        array $typeData, 
        Collection $weekDates, 
        array $doctors, 
        Collection $bookedAppointments
      ): array
    {
        $availableSlots = [];
        $duration = (int) $typeData['duration_in_mins'];

        foreach ($weekDates as $date) {
            $dayOfWeek = Carbon::parse($date)->format('l');

            foreach ($doctors as $doctorId => $doctorData) {
                $doctorSchedules = collect($doctorData['schedules'])->where('day_of_week', $dayOfWeek);
                if ($doctorSchedules->isEmpty()) continue;

                $bookedTimes = $this->getBookedTimesForDoctor($bookedAppointments, $doctorId, $date);
                $availableTimes = $this->getAvailableTimes($doctorSchedules, $date, $duration, $bookedTimes);

                if (!empty($availableTimes)) {
                    $availableSlots[] = [
                        'date' => $date,
                        'doctor' => $doctorData['name'],
                        'doctor_id' => $doctorId,
                        'available_start_time' => $availableTimes,
                    ];
                }
            }
        }

        return $availableSlots;
    }

    /**
     * Get booked appointment times for a specific doctor and date.
     *
     * @param \Illuminate\Support\Collection $bookedAppointments
     * @param int $doctorId
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    private function getBookedTimesForDoctor(Collection $bookedAppointments, int $doctorId, string $date): Collection
    {
        return ($bookedAppointments[$doctorId . '-' . $date] ?? collect())
            ->map(fn($appt) => [
                'start' => Carbon::parse($appt->start_time),
                'end' => Carbon::parse($appt->start_time)
                    ->addMinutes(AppointmentType::getAppointmentDuration($appt->appointment_type))
            ]);
    }

    /**
     * Get available appointment time slots for a doctor on a specific day.
     *
     * @param \Illuminate\Support\Collection $doctorSchedules The doctor's working schedules for the given day.
     * @param string $date The date being checked.
     * @param int $duration The duration of the appointment type in minutes.
     * @param \Illuminate\Support\Collection $bookedTimes The booked appointments for the doctor.
     * @return array
     */
    private function getAvailableTimes(
        Collection $doctorSchedules, 
        string $date, 
        int $duration,
        Collection $bookedTimes
      ): array
    {
        $availableTimes = [];

        foreach ($doctorSchedules as $schedule) {
            $startTime = Carbon::parse("{$date} {$schedule['start_time']}");
            $endTime = Carbon::parse("{$date} {$schedule['end_time']}");

            while ($startTime->lt($endTime)) {
                $slotStart = $startTime->copy();
                $slotEnd = $slotStart->copy()->addMinutes($duration);

                if ($slotEnd->gt($endTime)) break;

                if (!$this->isOverlapping($slotStart, $slotEnd, $bookedTimes)) {
                    $availableTimes[] = $slotStart->format('H:i');
                }

                $startTime->addMinutes($this->interval);
            }
        }

        return $availableTimes;
    }

    /**
     * Check if a given time slot overlaps with booked appointments.
     *
     * @param \Carbon\Carbon $slotStart The start time of the slot.
     * @param \Carbon\Carbon $slotEnd The end time of the slot.
     * @param \Illuminate\Support\Collection $bookedTimes The booked appointment times.
     * @return bool
     */
    private function isOverlapping(Carbon $slotStart, Carbon $slotEnd, Collection $bookedTimes): bool
    {
        foreach ($bookedTimes as $appt) {
            if ($slotStart->lt($appt['end']) && $slotEnd->gt($appt['start'])) {
                return true;
            }
        }
        return false;
    }
}
