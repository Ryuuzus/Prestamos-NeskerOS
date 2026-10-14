<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'classroom_id',
        'device_id',
        'start_time',
        'end_time',
        'status',
        'rejection_reason',
        'actual_return_time'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el aula
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Relación con el dispositivo
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}