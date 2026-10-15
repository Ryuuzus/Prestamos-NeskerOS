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
 * Si el recurso tiene reservaciones APROBADAS sin completar, su estado debe seguir siendo
 * "occupied". Así no se puede, por ejemplo, ponerlo en mantenimiento o en disponible a mano
 * mientras alguien lo tiene reservado. Al crear un recurso nuevo la regla no aplica.
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
        if (! $this->resource || ! $this->resource->exists || $value === 'occupied') {
            return;
        }

        $count = Reservation::query()
            ->approved()
            ->where($this->foreignKey, $this->resource->getKey())
            ->count();

        if ($count > 0) {
            $fail("No se puede cambiar el estado: tiene {$count} reservación(es) aprobada(s) sin completar. Márcalas como completadas primero.");
        }
    }
}