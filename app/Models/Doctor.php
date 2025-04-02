<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Doctor
{
    protected $id;
    protected $name;
    protected $email;
    protected $schedules;

    /**
     * Constructor to initialize a doctor from config data.
     *
     * @param  int    $id   The doctor's unique ID.
     * @param  array  $data The doctor's data from config.
     */
    public function __construct($id, array $data)
    {
        $this->id        = $id;
        $this->name      = $data['name'] ?? '';
        $this->email     = $data['email'] ?? '';
        $this->schedules = $data['schedules'] ?? [];
    }

    /**
     * Retrieve all doctors as a collection of DoctorConfig objects.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all(): Collection
    {
        $doctors = config('doctors'); // loads the config/doctors.php file

        return collect($doctors)->map(function ($data, $id) {
            return new self($id, $data);
        })->values();
    }

    /**
     * Find a doctor by ID.
     *
     * @param  int|string  $id
     * @return self|null
     */
    public static function find(int $id)
    {
        $doctors = config('doctors');
        if (isset($doctors[$id])) {
            return new self($id, $doctors[$id]);
        }
        return null;
    }

    /**
     * Getters
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get the doctor's schedules.
     *
     * @return array
     */
    public function getSchedules(): array
    {
        return $this->schedules;
    }

    // Could turn this into a transformer
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'schedules' => $this->schedules,
        ];
    }
}
