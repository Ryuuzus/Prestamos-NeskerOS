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
 * 
 * @property int $id
 * @property string $name
 * @property string $serial_number
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property Reservation[] $reservations
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Device extends Model
{
    /**
     * Cantidad de registros por página por defecto en paginación.
     *
     * @var int
     */
    protected $perPage = 20;

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'serial_number', 'status'];

    /**
     * Relación con las reservaciones vinculadas a este dispositivo.
     *
     * @return HasMany
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'device_id', 'id');
    }
}