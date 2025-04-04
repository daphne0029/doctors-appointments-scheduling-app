<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentType;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    private AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * @OA\Get(
     *     path="/appointments",
     *     summary="Get all appointments. For MVP veriosn, fetch 50 appointment.",
     *     tags={"Appointments"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of appointments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="patient_id", type="integer", example=1),
     *                 @OA\Property(property="doctor_id", type="integer", example=1),
     *                 @OA\Property(property="appointment_type", type="string", example="new_patient"),
     *                 @OA\Property(property="start_time", type="string", format="date-time", example="2025-04-01 09:00:00"),
     *                 @OA\Property(property="end_time", type="string", format="date-time", example="2025-04-01 10:00:00"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-03T02:14:15.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-03T02:14:15.000000Z"),
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $appointments = Appointment::query();

        // Filter by appointment type (if provided)
        if ($request->has('appointment_type')) {
            $appointments->where('appointment_type', $request->input('appointment_type'));
        }

        // Filter by doctor (if provided)
        if ($request->has('doctor_id')) {
            $appointments->where('doctor_id', $request->input('doctor_id'));
        }

        // Optionally, you can add sorting by a column (e.g., start time)
        if ($request->has('sort_by')) {
            $appointments->orderBy($request->input('sort_by'), $request->input('sort_direction', 'asc'));
        }

        // Get the results (can be paginated)
        $appointments = $appointments->paginate(50);

        return response()->json([
            'appointments' => $appointments,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/appointments/available",
     *     summary="Get available appointment times",
     *     tags={"Appointments"},
     *     description="Retrieve available appointment times for new patients, consultations, and follow-ups",
     *     @OA\Parameter(
     *         name="next_number_of_days",
     *         in="query",
     *         description="Number of days in the future to retrieve available appointment slots",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=7
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="new_patient",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date", type="string", example="2025-04-04"),
     *                     @OA\Property(property="doctor", type="string", example="Hermione Granger"),
     *                     @OA\Property(property="doctor_id", type="integer", example=2),
     *                     @OA\Property(
     *                         property="available_start_time",
     *                         type="array",
     *                         @OA\Items(
     *                             type="string",
     *                             example="12:00"
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="consultation",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date", type="string", example="2025-04-04"),
     *                     @OA\Property(property="doctor", type="string", example="Ron Weasley"),
     *                     @OA\Property(property="doctor_id", type="integer", example=3),
     *                     @OA\Property(
     *                         property="available_start_time",
     *                         type="array",
     *                         @OA\Items(
     *                             type="string",
     *                             example="09:00"
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="follow_up",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="date", type="string", example="2025-04-05"),
     *                     @OA\Property(property="doctor", type="string", example="Draco Malfoy"),
     *                     @OA\Property(property="doctor_id", type="integer", example=4),
     *                     @OA\Property(
     *                         property="available_start_time",
     *                         type="array",
     *                         @OA\Items(
     *                             type="string",
     *                             example="13:00"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function availableAppointments(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'next_number_of_days'  => 'int|max:14',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $numberOfDays = $request->input('next_number_of_days') ?? (int) config('appointments.default_number_of_days');
        $availableAppointments = $this->appointmentService->getAvailableAppointments($numberOfDays);

        return response()->json($availableAppointments);
    }
}
