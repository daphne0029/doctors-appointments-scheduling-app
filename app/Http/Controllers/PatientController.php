<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{

    /**
     * @OA\Post(
     *     path="/patients",
     *     summary="Create a new patient",
     *     description="Create a new patient after validating the provided name and email",
     *     tags={"Patients"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "email"},
     *             @OA\Property(property="name", type="string", description="Name of the patient", example="Ross Galler"),
     *             @OA\Property(property="email", type="string", format="email", description="Email of the patient", example="ross.galler@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Patient created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient created successfully"),
     *             @OA\Property(
     *                 property="patient",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ross Galler"),
     *                 @OA\Property(property="email", type="string", example="ross.galler@example.com"),
     *                 @OA\Property(property="token", type="string", example="e08bc6011a72"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-03T02:14:15.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-03T02:14:15.000000Z"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object", additionalProperties=@OA\Property(type="array", @OA\Items(type="string", example="The email has already been taken.")))
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|unique:patients,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $patient = Patient::create([
            'name'  => $request->name,
            'email' => $request->email,
            'token' => substr(md5(openssl_random_pseudo_bytes(20)), 20),
        ]);

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $patient,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/patients/{id}",
     *     summary="Get a specific patient by ID",
     *     description="Retrieve the details of a specific patient by their ID",
     *     tags={"Patients"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the patient",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Patient details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Ross Galler"),
     *             @OA\Property(property="email", type="string", example="ross.galler@example.com"),
     *             @OA\Property(property="token", type="string", example="e08bc6011a72089b82a2dbb2f62b4bfbdfb13148"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-03T02:14:15.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-03T02:14:15.000000Z"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        return response()->json($patient);
    }

    /**
     * @OA\Post(
     *     path="/patients/login",
     *     summary="Login a patient",
     *     description="Login a patient by their email. If the patient does not have a token, one will be generated.",
     *     tags={"Patients"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", description="Email of the patient", example="ross.galler@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful, patient details returned",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="patient",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Ross Galler"),
     *                 @OA\Property(property="email", type="string", example="ross.galler@example.com"),
     *                 @OA\Property(property="token", type="string", example="e08bc6011a72089b82a2dbb2f62b4bfbdfb13148")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Patient not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Patient not found")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:patients,email',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Patient not found',
            ], 404);
        }
    
        $patient = Patient::where('email', $request->email)->first();
    
        // If patient has no token, generate one
        if (!$patient->token) {
            $patient->token = substr(md5(openssl_random_pseudo_bytes(20)), 20);
            $patient->save();
        }
    
        return response()->json([
            'message' => 'Login successful',
            'patient' => [
                'id'    => $patient->id,
                'name'  => $patient->name,
                'email' => $patient->email,
                'token' => $patient->token,
            ],
        ]);
    }
}
