<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Dispositivo (Device).
 * 
 * Representa los equipos y dispositivos tecnológicos asignables.
 * Permite llevar el control de números de serie, estados de disponibilidad y solicitudes de reserva.
 * ==================================================================================================
 */
class Device extends Model
{
    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'serial_number',
        'status',
    ];

    /**
     * Relación con las reservaciones vinculadas a este dispositivo.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'device_id', 'id');
    }
}
