<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Reservación (Reservation).
 * 
 * Gestiona el registro centralizado de reservaciones de recursos (aulas o dispositivos).
 * Almacena información de tiempos, estados del trámite, motivos de rechazo y tiempos de devolución.
 * ==================================================================================================
 */
class Reservation extends Model
{
    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
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

    /**
     * Relación con el usuario solicitante de la reservación.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el aula reservada (si aplica).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relación con el dispositivo reservado (si aplica).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}