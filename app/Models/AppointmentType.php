<?php

namespace App\Models;

class AppointmentType extends Model
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
}
