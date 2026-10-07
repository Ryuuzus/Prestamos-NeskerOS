<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\Building;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Clase de Importación de Aulas (ClassroomImport).
 * 
 * Administra el procesamiento e importación masiva de aulas y salones desde hojas Excel/CSV.
 * Convierte tipos de datos previa validación, resuelve la relación con el edificio por ID o por nombre,
 * y aplica validaciones para la correcta asignación de espacios.
 * ==================================================================================================
 */
class ClassroomImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Convierte los valores numéricos y cadenas al tipo adecuado antes de proceder a la validación.
     */
    public function prepareForValidation($data, $index)
    {
        if (isset($data['classroom'])) {
            $data['classroom'] = (string) $data['classroom'];
        }
        if (isset($data['floor'])) {
            $data['floor'] = (int) $data['floor'];
        }
        if (isset($data['building_id'])) {
            $data['building_id'] = (int) $data['building_id'];
        }

        return $data;
    }

    /**
     * Mapea los campos de cada fila al modelo Classroom, buscando la referencia del edificio por ID o nombre.
     */
    public function model(array $row): ?Model
    {
        $classroom   = $row['classroom'] ?? null;
        $floor       = $row['floor'] ?? null;
        $status      = strtolower($row['status'] ?? 'available');
        $buildingsId = $row['building_id'] ?? null;

        // Búsqueda por nombre de edificio como alternativa si no se envió building_id
        if (!$buildingsId && isset($row['building_name'])) {
            $building = Building::where('name', $row['building_name'])->first();
            $buildingsId = $building ? $building->id : null;
        }

        if (!$classroom || !$buildingsId) {
            return null;
        }

        return new Classroom([
            'buildings_id' => $buildingsId,
            'classroom'    => $classroom,
            'floor'        => $floor,
            'status'       => $status,
        ]);
    }

    /**
     * Define las reglas de validación requeridas para la importación de aulas.
     */
    public function rules(): array
    {
        return [
            '*.classroom'   => ['required', 'string', 'max:100', 'unique:classrooms,classroom'],
            '*.building_id' => ['nullable', 'integer', 'exists:buildings,id'],
            '*.floor'       => ['nullable', 'integer', 'min:1'],
            '*.status'      => ['nullable', 'in:available,maintenance,occupied'],
        ];
    }
}