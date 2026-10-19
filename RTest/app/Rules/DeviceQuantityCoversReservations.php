<?php

namespace App\Rules;

use App\Models\Device;
use App\Services\AvailabilityService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Regla de validación para el campo "quantity" de un Dispositivo.
 *
 * La cantidad no puede bajar por debajo del máximo de unidades que las reservaciones aprobadas sin
 * completar usan a la vez; si no, habría unidades prometidas que ya no existen.
 * Al crear un dispositivo nuevo la regla no aplica.
 * ==================================================================================================
 */
class DeviceQuantityCoversReservations implements ValidationRule
{
    public function __construct(private ?Device $device)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->device || ! $this->device->exists) {
            return;
        }

        $peak = app(AvailabilityService::class)->devicePeakCommitted($this->device);

        if ((int) $value < $peak) {
            $fail("La cantidad no puede ser menor a {$peak}: hay reservaciones aprobadas sin completar que usan hasta {$peak} unidad(es) a la vez.");
        }
    }
}
