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
 * No se puede poner en "maintenance" un recurso que tenga reservaciones APROBADAS sin completar
 * (en curso, con devolución pendiente o futuras). Al crear un recurso nuevo la regla no aplica.
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
        if (! $this->resource || ! $this->resource->exists || $value !== 'maintenance') {
            return;
        }

        $count = Reservation::query()
            ->approved()
            ->where($this->foreignKey, $this->resource->getKey())
            ->count();

        if ($count > 0) {
            $fail("No se puede poner en mantenimiento: tiene {$count} reservación(es) aprobada(s) sin completar.");
        }
    }
}
