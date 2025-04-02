<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Retrieve all doctors.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Could use pagination one day, if needed
        // right now we only have 4 doctors

        $doctors = Doctor::all()->map(fn($doctor) => $doctor->toArray());

        return response()->json($doctors);
    }

    /**
     * Retrieve a doctor by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json([
            'id'        => $doctor->getId(),
            'name'      => $doctor->getName(),
            'email'     => $doctor->getEmail(),
            'schedules' => $doctor->getSchedules(),
        ]);
    }
}
