<?php

namespace App\Rules;

use App\Models\Reservation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Regla de validación para el campo "status" de un Aula o un Dispositivo.
 *
 *  - No se puede poner en "maintenance" si tiene reservaciones aprobadas sin completar
 *    (aunque todavía no hayan empezado).
 *  - Si está en uso ahora mismo (reservación aprobada que ya inició y no se ha completado),
 *    debe seguir en "occupied" hasta que la reservación se complete.
 *
 * Al crear un recurso nuevo la regla no aplica.
 * ==================================================================================================
 */
class NoActiveReservations implements ValidationRule
{
    /**
     * @param  Model|null  $resource    Aula o dispositivo que se edita (null al crear).
     * @param  string      $foreignKey  'classroom_id' o 'device_id'.
     */
    public function __construct(
        private ?Model $resource,
        private string $foreignKey,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->resource || ! $this->resource->exists) {
            return;
        }

        $approved = fn () => Reservation::query()
            ->approved()
            ->where($this->foreignKey, $this->resource->getKey());

        if ($value === 'maintenance' && ($count = $approved()->count()) > 0) {
            $fail("No se puede poner en mantenimiento: tiene {$count} reservación(es) aprobada(s) sin completar.");

            return;
        }

        if ($value !== 'occupied' && $this->resource->status === 'occupied' && $approved()->started()->exists()) {
            $fail('No se puede cambiar el estado: está en uso por una reservación aprobada. Márcala como completada primero.');
        }
    }
}
