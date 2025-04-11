<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $id;
    protected $name;
    protected $email;
    protected $schedules;

    protected $fillable = ['name', 'email', 'token'];

    public static function validateToken($patientId, $token): ?self
    {
        return self::where('id', $patientId)->where('token', $token)->first();
    }
}
