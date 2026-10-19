<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Dispositivo (Device).
 *
 * Representa los equipos y dispositivos tecnológicos asignables. Cada registro tiene `quantity`
 * unidades. El estado guardado solo distingue "available" y "maintenance"; las unidades que están
 * en uso se calculan con las reservaciones aprobadas (ver App\Services\AvailabilityService).
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
        'quantity',
        'status',
    ];

    /**
     * Relación con las reservaciones vinculadas a este dispositivo.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'device_id', 'id');
    }

    /**
     * Unidades disponibles ahora mismo: cantidad total menos las unidades en uso
     * (reservaciones aprobadas que ya iniciaron y no se han completado).
     *
     * Si el controlador precargó `units_in_use` con withSum, se usa ese valor y no se hace otra
     * consulta por cada dispositivo del listado.
     */
    protected function availableUnitsNow(): Attribute
    {
        return Attribute::get(function () {
            $inUse = array_key_exists('units_in_use', $this->attributes)
                ? (int) $this->attributes['units_in_use']
                : (int) $this->reservations()->approved()->started()->sum('device_quantity');

            return max(0, (int) $this->quantity - $inUse);
        });
    }
}
