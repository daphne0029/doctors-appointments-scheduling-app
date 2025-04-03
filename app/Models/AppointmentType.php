<?php

namespace App\Models;

class AppointmentType
{
    /**
     * Retrieve all doctors from the config.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        return collect(config('appointment_type'));
    }

    /**
     * Find an appointment type by ID.
     *
     * @param  int|string  $id
     * @return array|null
     */
    public static function find(int $id): array|null
    {
        $types = config('appointment_type');

        return $types[$id] ?? null;
    }

    // Helper function to get appointment duration
    public static function getAppointmentDuration($type)
    {
        $appointmentTypes = config('appointment_type');

        return $appointmentTypes[$type]['duration_in_mins'] ?? 30;
    }
}
