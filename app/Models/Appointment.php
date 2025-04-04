<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_type',
        'start_time',
        'end_time'
    ];

    /**
     * Check if the requested appointment overlaps with an existing one.
     * 
     * @param int $doctorId
     * @param Carbon $startTime
     * @param Carbon $endTime
     * @return bool
     */
    public static function isOverlapping($doctorId, Carbon $startTime, Carbon $endTime)
    {
        // Fetch all appointments for the doctor on the same date
        $appointments = self::where('doctor_id', $doctorId)
                            ->whereDate('start_time', $startTime->toDateString())  // Get the same date
                            ->get();

        foreach ($appointments as $appointment) {
            // Check if the new appointment time overlaps with an existing one
            if ($startTime->lt($appointment->end_time) && $endTime->gt($appointment->start_time)) {
                return true;
            }
        }

        return false;
    }
}
