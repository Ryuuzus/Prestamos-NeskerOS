<?php

namespace App\Imports;

use App\Models\Building;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Clase de Importación de Edificios (BuildingImport).
 * 
 * Gestiona el procesamiento e importación masiva de edificios desde archivos Excel/CSV.
 * Convierte tipos de datos previa validación, crea instancias del modelo Building y aplica
 * validaciones para garantizar la integridad de la base de datos.
 * ==================================================================================================
 */
class BuildingImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Prepara y convierte los valores de entrada a sus tipos correspondientes antes de validar.
     */
    public function prepareForValidation($data, $index)
    {
        if (isset($data['name'])) {
            $data['name'] = (string) $data['name'];
        }
        if (isset($data['floors'])) {
            $data['floors'] = (int) $data['floors'];
        }

        return $data;
    }

    /**
     * Mapea cada fila procesada del archivo a una nueva instancia del modelo Building.
     */
    public function model(array $row): ?Model
    {
        $name   = $row['name'] ?? null;
        $floors = $row['floors'] ?? null;

        if (!$name || !$floors) {
            return null;
        }

        return new Building([
            'name'   => $name,
            'floors' => $floors,
        ]);
    }

    /**
     * Define las reglas de validación aplicables a las filas de la hoja de cálculo.
     */
    public function rules(): array
    {
        return [
            '*.name'   => ['required', 'string', 'max:100', 'unique:buildings,name'],
            '*.floors' => ['required', 'integer', 'min:1'],
        ];
    }
}