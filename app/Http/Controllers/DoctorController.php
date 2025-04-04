<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/doctors",
     *     summary="Get all doctors with their schedules",
     *     description="Retrieve a list of all doctors with their schedules in the system",
     *     tags={"Doctors"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Harry Potter"),
     *                 @OA\Property(property="email", type="string", example="harry.potter@example.com"),
     *                 @OA\Property(
     *                     property="schedules",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="day_of_week", type="string", example="Sunday"),
     *                         @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
     *                         @OA\Property(property="end_time", type="string", format="time", example="12:00:00")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        // Could use pagination one day, if needed
        // right now we only have 4 doctors

        $doctors = Doctor::all()->map(fn($doctor) => $doctor->toArray());

        return response()->json($doctors);
    }

    /**
     * @OA\Get(
     *     path="/doctors/{id}",
     *     summary="Get a specific doctor by ID",
     *     description="Retrieve a specific doctor's details, including their name, email, and schedules",
     *     tags={"Doctors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the doctor",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with doctor details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Harry Potter"),
     *             @OA\Property(property="email", type="string", example="harry.potter@example.com"),
     *             @OA\Property(
     *                 property="schedules",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="day_of_week", type="string", example="Sunday"),
     *                     @OA\Property(property="start_time", type="string", format="time", example="09:00:00"),
     *                     @OA\Property(property="end_time", type="string", format="time", example="12:00:00")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Doctor not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Doctor not found")
     *         )
     *     )
     * )
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
