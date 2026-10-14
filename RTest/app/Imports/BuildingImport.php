<?php

namespace App\Imports;

use App\Models\Building;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class BuildingImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Convierte valores numéricos a String o Integer antes de validar.
     */
    public function prepareForValidation($data, $index)
    {
        if (isset($data['nombre'])) {
            $data['nombre'] = (string) $data['nombre'];
        }
        if (isset($data['building_name'])) {
            $data['building_name'] = (string) $data['building_name'];
        }
        if (isset($data['pisos'])) {
            $data['pisos'] = (int) $data['pisos'];
        }
        if (isset($data['piso'])) {
            $data['piso'] = (int) $data['piso'];
        }
        if (isset($data['floors'])) {
            $data['floors'] = (int) $data['floors'];
        }

        return $data;
    }

    /**
     * Mapeo del modelo.
     */
    public function model(array $row): ?Model
    {
        $building_name = $row['building_name'] ?? $row['nombre'] ?? $row['name'] ?? null;
        $floors = $row['floors'] ?? $row['pisos'] ?? $row['piso'] ?? null;

        if (!$building_name || !$floors) {
            return null;
        }

        return new Building([
            'building_name' => $building_name,
            'floors' => $floors,
        ]);
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            '*.nombre' => ['nullable', 'string', 'max:100', 'unique:buildings,building_name'],
            '*.building_name'   => ['nullable', 'string', 'max:100', 'unique:buildings,building_name'],
            '*.pisos'  => ['nullable', 'integer', 'min:1'],
            '*.piso'   => ['nullable', 'integer', 'min:1'],
            '*.floors' => ['nullable', 'integer', 'min:1'],
        ];
    }
}