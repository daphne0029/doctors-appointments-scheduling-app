<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    /**
     * Create a patient
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
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
     * Retrieve a paitent by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        return response()->json($patient);
    }

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
