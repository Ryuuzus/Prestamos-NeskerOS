<?php

namespace App\Http\Requests;

use App\Models\Building;
use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Validación del formulario de Aulas (crear y editar).
 *
 * Reglas de negocio:
 *  - El nombre del aula NO puede repetirse dentro del mismo edificio Y el mismo piso.
 *  - El mismo nombre SÍ puede existir en otro piso o en otro edificio.
 *  - El piso debe existir dentro del número de pisos del edificio seleccionado.
 *  - Si el edificio está en mantenimiento, el aula solo puede estar en mantenimiento.
 * ==================================================================================================
 */
class ClassroomRequest extends FormRequest
{
    /** Edificio seleccionado en el formulario (se consulta una sola vez). */
    private ?Building $selectedBuilding = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Limpia el nombre antes de validar (quita espacios al inicio/final y espacios dobles),
     * para que "Casado" y " Casado  " se detecten como el mismo nombre.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('classroom')) {
            $this->merge([
                'classroom' => preg_replace('/\s+/', ' ', trim((string) $this->input('classroom'))),
            ]);
        }
    }

    /**
     * Edificio elegido en el formulario, o null si no existe.
     */
    private function building(): ?Building
    {
        return $this->selectedBuilding ??= Building::find($this->input('buildings_id'));
    }

    public function rules(): array
    {
        // Con binding implícito la ruta entrega el modelo; con ID plano entrega el valor.
        $routeParam  = $this->route('classroom');
        $classroomId = $routeParam instanceof Classroom ? $routeParam->getKey() : $routeParam;

        return [
            'buildings_id' => [
                'required',
                'integer',
                Rule::exists((new Building())->getTable(), 'id'),
            ],

            'classroom' => [
                'required',
                'string',
                'max:100',
                // Único solo dentro de la combinación edificio + piso (ignora el aula que se está editando).
                Rule::unique((new Classroom())->getTable(), 'classroom')
                    ->where(fn ($query) => $query
                        ->where('buildings_id', $this->input('buildings_id'))
                        ->where('floor', $this->input('floor')))
                    ->ignore($classroomId),
            ],

            'floor' => [
                'required',
                'integer',
                'min:1',
                // El piso no puede superar los pisos que tiene el edificio.
                function (string $attribute, mixed $value, \Closure $fail) {
                    $building = $this->building();

                    if ($building && (int) $value > (int) $building->floors) {
                        $fail("El edificio seleccionado solo tiene {$building->floors} piso(s).");
                    }
                },
            ],

            'status' => [
                'required',
                Rule::in(['available', 'maintenance', 'occupied']),
                // Un edificio en mantenimiento mantiene todas sus aulas en mantenimiento.
                function (string $attribute, mixed $value, \Closure $fail) {
                    $building = $this->building();

                    if ($building && $building->isUnderMaintenance() && $value !== 'maintenance') {
                        $fail("El edificio «{$building->name}» está en mantenimiento: sus aulas solo pueden estar en mantenimiento.");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'buildings_id.required' => 'Debes seleccionar un edificio.',
            'buildings_id.integer'  => 'El edificio seleccionado no es válido.',
            'buildings_id.exists'   => 'El edificio seleccionado no existe.',

            'classroom.required' => 'El nombre del aula es obligatorio.',
            'classroom.string'   => 'El nombre del aula debe ser texto.',
            'classroom.max'      => 'El nombre del aula no puede superar los 100 caracteres.',
            'classroom.unique'   => 'Ya existe un aula con ese nombre en este piso del edificio seleccionado.',

            'floor.required' => 'Debes seleccionar un piso.',
            'floor.integer'  => 'El piso seleccionado no es válido.',
            'floor.min'      => 'El piso debe ser mayor o igual a 1.',

            'status.required' => 'Debes seleccionar un estado.',
            'status.in'       => 'El estado seleccionado no es válido.',
        ];
    }
}