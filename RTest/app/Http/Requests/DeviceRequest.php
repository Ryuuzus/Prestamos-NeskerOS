<?php

namespace App\Http\Requests;

use App\Models\Device;
use App\Rules\DeviceQuantityCoversReservations;
use App\Rules\NoActiveReservations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Validación del formulario de Dispositivos (crear y editar).
 *
 * Reglas de negocio:
 *  - El número de serie NO puede repetirse entre dispositivos.
 *  - Al editar, el dispositivo puede conservar su propio número de serie.
 *  - La cantidad debe ser un entero mayor o igual a 1.
 * ==================================================================================================
 */
class DeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Limpia los textos antes de validar (quita espacios al inicio/final),
     * para que "SN-001" y " SN-001 " se detecten como el mismo serial.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'          => trim((string) $this->input('name')),
            'serial_number' => trim((string) $this->input('serial_number')),
        ]);
    }

    public function rules(): array
    {
        // Con binding implícito la ruta entrega el modelo; con ID plano entrega el valor.
        $routeParam = $this->route('device');
        $deviceId   = $routeParam instanceof Device ? $routeParam->getKey() : $routeParam;

        return [
            'name' => ['required', 'string', 'max:100'],

            'serial_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique((new Device())->getTable(), 'serial_number')->ignore($deviceId),
            ],

            // La cantidad no puede bajar de las unidades que las reservaciones aprobadas usan a la vez.
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:9999',
                new DeviceQuantityCoversReservations($routeParam instanceof Device ? $routeParam : null),
            ],

            'status' => [
                'required',
                Rule::in(['available', 'maintenance']),
                // No se puede poner en mantenimiento un dispositivo con reservaciones aprobadas sin completar.
                new NoActiveReservations($routeParam instanceof Device ? $routeParam : null, 'device_id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del dispositivo es obligatorio.',
            'name.string'   => 'El nombre del dispositivo debe ser texto.',
            'name.max'      => 'El nombre del dispositivo no puede superar los 100 caracteres.',

            'serial_number.required' => 'El número de serie es obligatorio.',
            'serial_number.string'   => 'El número de serie debe ser texto.',
            'serial_number.max'      => 'El número de serie no puede superar los 50 caracteres.',
            'serial_number.unique'   => 'Ya existe un dispositivo con ese número de serie.',

            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.integer'  => 'La cantidad debe ser un número entero.',
            'quantity.min'      => 'La cantidad debe ser al menos 1.',
            'quantity.max'      => 'La cantidad no puede ser mayor a 9999.',

            'status.required' => 'Debes seleccionar un estado.',
            'status.in'       => 'El estado seleccionado no es válido.',
        ];
    }
}
