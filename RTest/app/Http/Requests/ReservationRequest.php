<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Validación de reservaciones (crear y editar).
 *
 * Reglas:
 *  - Se debe elegir un aula, un dispositivo, o ambos.
 *  - Fecha y hora van separadas: start_date + start_time, end_date + end_time.
 *  - Anticipación mínima: el inicio debe ser al menos N días después de hoy
 *    (por defecto 7), lo que también impide reservar en el pasado.
 *  - Duración mínima (por defecto 24 horas). Los límites opcionales de anticipación máxima y de
 *    duración máxima salen de config/reservations.php.
 *  - No se puede reservar un aula o dispositivo en mantenimiento.
 *  - No se puede reservar un recurso que ya tenga una reservación APROBADA que se cruce
 *    con el horario elegido.
 * ==================================================================================================
 */
class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza las horas: "08:30:00" -> "08:30".
     */
    protected function prepareForValidation(): void
    {
        foreach (['start_time', 'end_time'] as $field) {
            $value = $this->input($field);

            if (is_string($value) && strlen($value) === 8) {
                $this->merge([$field => substr($value, 0, 5)]);
            }
        }

        // Sin dispositivo la cantidad no aplica (queda en 1).
        if (! $this->filled('device_id')) {
            $this->merge(['device_quantity' => 1]);
        }
    }

    public function rules(): array
    {
        return [
            'classroom_id' => [
                'nullable',
                'required_without:device_id',
                'integer',
                Rule::exists((new Classroom())->getTable(), 'id'),
            ],
            'device_id' => [
                'nullable',
                'required_without:classroom_id',
                'integer',
                Rule::exists((new Device())->getTable(), 'id'),
            ],

            // Unidades del dispositivo que se solicitan.
            'device_quantity' => ['nullable', 'required_with:device_id', 'integer', 'min:1', 'max:9999'],

            'start_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_date'   => ['required', 'date_format:Y-m-d'],
            'end_time'   => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * Validaciones que dependen de varios campos a la vez (horario completo y recursos).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $dateFields = ['start_date', 'start_time', 'end_date', 'end_time'];

            if ($validator->errors()->hasAny($dateFields)) {
                return;
            }

            $start = Carbon::createFromFormat('Y-m-d H:i', $this->input('start_date') . ' ' . $this->input('start_time'));
            $end   = Carbon::createFromFormat('Y-m-d H:i', $this->input('end_date') . ' ' . $this->input('end_time'));

            $now         = now();
            $minDays     = (int) config('reservations.min_days_in_advance', 7);
            $minHours    = (int) config('reservations.min_duration_hours', 24);
            $maxDays     = config('reservations.max_days_ahead');
            $maxDuration = config('reservations.max_duration_hours');
            $earliest    = $now->copy()->addDays($minDays);

            // Anticipación mínima (esto también impide reservar en el pasado).
            if ($start->lt($earliest)) {
                $validator->errors()->add(
                    'start_date',
                    "Las reservaciones deben hacerse con al menos {$minDays} días de anticipación: el inicio no puede ser antes del {$earliest->format('d/m/Y H:i')}."
                );
            }

            if ($end->lt($start->copy()->addHours($minHours))) {
                $validator->errors()->add('end_date', 'La reservación debe durar al menos ' . $this->durationLabel($minHours) . '.');
            }

            // Límites opcionales (null en la configuración = sin límite).
            if ($maxDays !== null && $start->gt($now->copy()->addDays((int) $maxDays))) {
                $validator->errors()->add('start_date', "Solo se puede reservar con un máximo de {$maxDays} días de anticipación.");
            }

            if ($maxDuration !== null && $end->gt($start->copy()->addHours((int) $maxDuration))) {
                $validator->errors()->add('end_date', 'La reservación no puede durar más de ' . $this->durationLabel((int) $maxDuration) . '.');
            }

            // Si el horario ya es inválido no tiene caso revisar recursos.
            if ($validator->errors()->hasAny($dateFields)) {
                return;
            }

            $this->validateResources($validator, $start, $end);
        });
    }

    /**
     * Revisa el aula y el dispositivo elegidos con AvailabilityService:
     *  - Aula: que no esté en mantenimiento y que no se cruce con una reservación aprobada.
     *  - Dispositivo: que no esté en mantenimiento y que queden unidades suficientes en ese horario.
     */
    private function validateResources(Validator $validator, Carbon $start, Carbon $end): void
    {
        $routeParam = $this->route('reservation');
        $ignoreId   = $routeParam instanceof Reservation ? $routeParam->getKey() : $routeParam;
        $ignoreId   = $ignoreId ? (int) $ignoreId : null;

        $availability = app(AvailabilityService::class);

        // ---- Aula ----
        $classroomId = $this->input('classroom_id');

        if ($classroomId && ! $validator->errors()->has('classroom_id') && ($classroom = Classroom::find($classroomId))) {
            if ($classroom->status === 'maintenance') {
                $validator->errors()->add('classroom_id', "El aula «{$classroom->classroom}» está en mantenimiento y no se puede reservar.");
            } elseif ($conflict = $availability->classroomConflict($classroom, $start, $end, $ignoreId)) {
                $validator->errors()->add(
                    'classroom_id',
                    "El aula «{$classroom->classroom}» ya tiene una reservación aprobada del "
                    . $conflict->start_at->format('d/m/Y H:i') . ' al ' . $conflict->end_at->format('d/m/Y H:i')
                    . ' que se cruza con el horario elegido.'
                );
            }
        }

        // ---- Dispositivo ----
        $deviceId = $this->input('device_id');

        if (
            $deviceId
            && ! $validator->errors()->has('device_id')
            && ! $validator->errors()->has('device_quantity')
            && ($device = Device::find($deviceId))
        ) {
            $requested = (int) $this->input('device_quantity', 1);

            if ($device->status === 'maintenance') {
                $validator->errors()->add('device_id', "El dispositivo «{$device->name}» está en mantenimiento y no se puede reservar.");
            } else {
                $available = $availability->deviceAvailableUnits($device, $start, $end, $ignoreId);

                if ($requested > $available) {
                    $validator->errors()->add(
                        'device_quantity',
                        $available === 0
                            ? "El dispositivo «{$device->name}» no tiene unidades disponibles en ese horario."
                            : "Solo hay {$available} unidad(es) disponible(s) de «{$device->name}» en ese horario y solicitaste {$requested}."
                    );
                }
            }
        }
    }

    private function durationLabel(int $hours): string
    {
        return $hours % 24 === 0
            ? ($hours / 24) . ' día(s)'
            : "{$hours} hora(s)";
    }

    public function messages(): array
    {
        return [
            'classroom_id.required_without' => 'Debes elegir un aula o un dispositivo.',
            'classroom_id.exists'           => 'El aula seleccionada no existe.',
            'classroom_id.integer'          => 'El aula seleccionada no es válida.',

            'device_id.required_without' => 'Debes elegir un aula o un dispositivo.',
            'device_id.exists'           => 'El dispositivo seleccionado no existe.',
            'device_id.integer'          => 'El dispositivo seleccionado no es válido.',

            'device_quantity.required_with' => 'Indica cuántas unidades del dispositivo necesitas.',
            'device_quantity.integer'       => 'La cantidad de unidades debe ser un número entero.',
            'device_quantity.min'           => 'Debes pedir al menos 1 unidad.',
            'device_quantity.max'           => 'La cantidad de unidades no puede ser mayor a 9999.',

            'start_date.required'    => 'La fecha de inicio es obligatoria.',
            'start_date.date_format' => 'La fecha de inicio no es válida.',
            'start_time.required'    => 'La hora de inicio es obligatoria.',
            'start_time.date_format' => 'La hora de inicio no es válida.',

            'end_date.required'    => 'La fecha de entrega es obligatoria.',
            'end_date.date_format' => 'La fecha de entrega no es válida.',
            'end_time.required'    => 'La hora de entrega es obligatoria.',
            'end_time.date_format' => 'La hora de entrega no es válida.',
        ];
    }
}
