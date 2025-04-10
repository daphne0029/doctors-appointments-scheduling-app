<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Models\Patient;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PatientAppointmentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/patients/{patientId}/appointments/upcomings",
     *     summary="Get patient's upcoming appointments",
     *     description="Fetch the upcoming appointments for a specific patient by patient ID. The patient's token must be valid.",
     *     tags={"Patient Appointments"},
     *     @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         description="The ID of the patient",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="Authorization", type="string", description="Bearer token for authentication", example="Bearer token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of upcoming appointments for the patient",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="appointments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="appointment_type", type="string", example="follow_up"),
     *                     @OA\Property(property="appointment_name", type="string", example="Follow-up Consultation"),
     *                     @OA\Property(property="start_time", type="string", format="date-time", example="2025-04-03T09:00:00"),
     *                     @OA\Property(property="end_time", type="string", format="date-time", example="2025-04-03T09:30:00"),
     *                     @OA\Property(property="doctor_id", type="integer", example=1),
     *                     @OA\Property(property="doctor_name", type="string", example="Dr. Harry Potter")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized, invalid or missing token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function index(Request $request, $patientId)
    {
        $patient = $request->get('patient');
    
        $doctors = config('doctors');
        $types = config('appointment_types');

        // Return the patient's upcoming appointments
        $appointments = Appointment::where('patient_id', $patient->id)
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($appointment) use ($doctors, $types) {
                return [
                    'id' => $appointment->id,
                    'appointment_type' => $appointment->appointment_type,
                    'appointment_name' => $types[$appointment->appointment_type]['name'],
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'doctor_id' => $appointment->doctor_id,
                    'doctor_name' => $doctors[$appointment->doctor_id]['name'] ?? 'Unknown Doctor',
                ];
            });

        return response()->json(['appointments' => $appointments]);
    }

    /**
     * @OA\Post(
     *     path="/patients/{patientId}/appointments",
     *     summary="Create a new appointment for a patient",
     *     description="This endpoint allows the creation of a new appointment for the specified patient. The patient's token must be valid.",
     *     tags={"Patient Appointments"},
     *     @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         description="The ID of the patient",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="patient_id", type="integer", description="The ID of the patient", example=1),
     *             @OA\Property(property="doctor_id", type="integer", description="The ID of the doctor for the appointment", example=1),
     *             @OA\Property(property="appointment_type", type="string", description="The type of appointment", example="follow_up"),
     *             @OA\Property(property="start_time", type="string", format="date-time", description="The start time of the appointment", example="2025-04-03T09:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Appointment created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Appointment created successfully"),
     *             @OA\Property(
     *                 property="appointment",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="patient_id", type="integer", example=1),
     *                 @OA\Property(property="doctor_id", type="integer", example=1),
     *                 @OA\Property(property="appointment_type", type="string", example="follow_up"),
     *                 @OA\Property(property="start_time", type="string", format="date-time", example="2025-04-03T09:00:00"),
     *                 @OA\Property(property="end_time", type="string", format="date-time", example="2025-04-03T09:30:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to invalid data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized, invalid or missing token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $patientId)
    {
        $patient = $request->get('patient');

        $appointmentTypes = array_keys(config('appointment_types'));
        $validator = Validator::make($request->all(),[
            'patient_id'         => 'required|exists:patients,id',
            'doctor_id'          => 'required|integer',
            'appointment_type'   => 'required|string|in:' . implode(',', $appointmentTypes),
            'start_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $service = new AppointmentService();
    
        $result = $service->createAppointment($request->all());
    
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status'] ?? 400);
        }
    
        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $result['appointment'],
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/patients/{patientId}/appointments/{appointmentId}",
     *     summary="Delete an appointment for a patient",
     *     description="Deletes an upcoming appointment for a patient if it exists and hasn’t already passed. Requires a valid Bearer token.",
     *     tags={"Patient Appointments"},
     *     @OA\Parameter(
     *         name="patientId",
     *         in="path",
     *         required=true,
     *         description="The ID of the patient",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="appointmentId",
     *         in="path",
     *         required=true,
     *         description="The ID of the appointment to delete",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Appointment deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Appointment deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete past appointments",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Cannot delete past appointments.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Appointment not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Appointment not found.")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, $patientId, $appointmentId)
    {
        $patient = $request->get('patient');

        // Find the appointment
        $appointment = Appointment::where('id', $appointmentId)
                                ->where('patient_id', $patientId)
                                ->first();

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found.'], 404);
        }

        // Check if the appointment has already passed
        if ($appointment->end_time < Carbon::now()) {
            return response()->json(['error' => 'Cannot delete past appointments.'], 400);
        }

        // Delete the appointment
        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully.'], 204);
    }
}