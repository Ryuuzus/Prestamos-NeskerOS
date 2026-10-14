<?php

namespace App\Imports;

use App\Models\Building;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

<<<<<<< HEAD
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
=======
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
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        }
        if (isset($data['floors'])) {
            $data['floors'] = (int) $data['floors'];
        }

        return $data;
    }

    /**
<<<<<<< HEAD
     * Mapeo del modelo.
     */
    public function model(array $row): ?Model
    {
        $building_name = $row['building_name'] ?? $row['nombre'] ?? $row['name'] ?? null;
        $floors = $row['floors'] ?? $row['pisos'] ?? $row['piso'] ?? null;

        if (!$building_name || !$floors) {
=======
     * Mapea cada fila procesada del archivo a una nueva instancia del modelo Building.
     */
    public function model(array $row): ?Model
    {
        $name   = $row['name'] ?? null;
        $floors = $row['floors'] ?? null;

        if (!$name || !$floors) {
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
            return null;
        }

        return new Building([
<<<<<<< HEAD
            'building_name' => $building_name,
=======
            'name'   => $name,
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
            'floors' => $floors,
        ]);
    }

    /**
<<<<<<< HEAD
     * Reglas de validación.
=======
     * Define las reglas de validación aplicables a las filas de la hoja de cálculo.
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
     */
    public function rules(): array
    {
        return [
<<<<<<< HEAD
            '*.nombre' => ['nullable', 'string', 'max:100', 'unique:buildings,building_name'],
            '*.building_name'   => ['nullable', 'string', 'max:100', 'unique:buildings,building_name'],
            '*.pisos'  => ['nullable', 'integer', 'min:1'],
            '*.piso'   => ['nullable', 'integer', 'min:1'],
            '*.floors' => ['nullable', 'integer', 'min:1'],
=======
            '*.name'   => ['required', 'string', 'max:100', 'unique:buildings,name'],
            '*.floors' => ['required', 'integer', 'min:1'],
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        ];
    }
}