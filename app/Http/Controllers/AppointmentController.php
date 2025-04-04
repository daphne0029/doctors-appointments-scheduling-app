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
     * Retrieve all appointments
     *
     * @return \Illuminate\Http\JsonResponse
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
        $appointments = $appointments->paginate(10);

        return response()->json([
            'appointments' => $appointments,
        ]);
    }

    /**
     * Get available appointment slots for the next few days.
     *
     * @return \Illuminate\Http\JsonResponse
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
