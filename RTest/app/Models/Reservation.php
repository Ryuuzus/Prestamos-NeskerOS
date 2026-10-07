<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'actual_return_time',
    ];

    /**
     * Define las conversiones de tipo para los atributos especificados.
     * Convierte automáticamente los campos de fecha/hora en objetos Carbon.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time'          => 'datetime',
            'end_time'            => 'datetime',
            'actual_return_time'  => 'datetime',
        ];
    }

    /**
     * Relación con el usuario solicitante de la reservación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el aula reservada (si aplica).
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relación con el dispositivo reservado (si aplica).
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
