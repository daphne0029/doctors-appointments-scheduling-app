<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Patient;

class ValidatePatientToken
{
    public function handle(Request $request, Closure $next)
    {
        $patientId = $request->route('patientId');
        $token = $request->bearerToken();

        $patient = $token ? Patient::validateToken($patientId, $token) : null;

        if (!$patient) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Pass patient to request so controller can access it
        $request->attributes->set('patient', $patient);

        return $next($request);
    }
}
