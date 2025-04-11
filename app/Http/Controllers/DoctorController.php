<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Support\Facades\Validator;
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
    public function show(Request $request, $doctorId)
    {
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $schedules = [];
        if (!empty($request->input('schedule'))) {
            // request schedules
        }

        return response()->json([
            'id'        => $doctor->id,
            'name'      => $doctor->name,
            'email'     => $doctor->email,
            'schedules' => $schedules,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|unique:doctors,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $doctor = Doctor::create([
            'name'  => $request->name,
            'email' => $request->email,
            'token' => substr(md5(openssl_random_pseudo_bytes(20)), 20),
        ]);

        return response()->json([
            'message' => 'New doctor created successfully',
            'doctor' => $doctor,
        ], 201);
    }

    public function update(Request $request, $doctorId)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:doctors,email,' . $doctorId,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Find the doctor
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor not found',
            ], 404);
        }

        // Update the doctor's profile with the validated data
        $doctor->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'Doctor profile updated successfully',
            'doctor'  => $doctor,
        ]);
    }
}
