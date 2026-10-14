<?php

namespace App\Imports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

<<<<<<< HEAD
class DeviceImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Convierte valores numéricos a String antes de validar.
     */
    public function prepareForValidation($data, $index)
    {
        if (isset($data['numero_de_serie'])) {
            $data['numero_de_serie'] = (string) $data['numero_de_serie'];
        }
        if (isset($data['serial_number'])) {
            $data['serial_number'] = (string) $data['serial_number'];
        }
        if (isset($data['nombre'])) {
            $data['nombre'] = (string) $data['nombre'];
        }
=======
/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Clase de Importación de Dispositivos (DeviceImport).
 * 
 * Encargada del procesamiento e importación en lote de equipos y dispositivos tecnológicos.
 * Normaliza los números de serie a cadenas de texto, valida la unicidad de los seriales y crea los registros.
 * ==================================================================================================
 */
class DeviceImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Convierte y castea las celdas numéricas a formato String antes de ejecutar validaciones.
     */
    public function prepareForValidation($data, $index)
    {
        if (isset($data['serial_number'])) {
            $data['serial_number'] = (string) $data['serial_number'];
        }
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        if (isset($data['name'])) {
            $data['name'] = (string) $data['name'];
        }

        return $data;
    }

    /**
<<<<<<< HEAD
     * Mapeo del modelo.
     */
    public function model(array $row): ?Model
    {
        $name         = $row['nombre'] ?? $row['name'] ?? null;
        $serialNumber = $row['numero_de_serie'] ?? $row['serial_number'] ?? null;
        $status       = strtolower($row['estado'] ?? $row['status'] ?? 'available');
=======
     * Mapea y crea un nuevo objeto Device con los valores validados de la fila.
     */
    public function model(array $row): ?Model
    {
        $name         = $row['name'] ?? null;
        $serialNumber = $row['serial_number'] ?? null;
        $status       = strtolower($row['status'] ?? 'available');
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2

        if (!$name || !$serialNumber) {
            return null;
        }

        return new Device([
            'name'          => $name,
            'serial_number' => $serialNumber,
            'status'        => $status,
        ]);
    }

    /**
<<<<<<< HEAD
     * Reglas de validación.
=======
     * Establece las reglas de validación para asegurar la calidad de la información del inventario.
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
     */
    public function rules(): array
    {
        return [
<<<<<<< HEAD
            '*.nombre'          => ['nullable', 'string', 'max:100'],
            '*.name'            => ['nullable', 'string', 'max:100'],
            '*.numero_de_serie' => ['nullable', 'string', 'max:50', 'unique:devices,serial_number'],
            '*.serial_number'   => ['nullable', 'string', 'max:50', 'unique:devices,serial_number'],
            '*.estado'          => ['nullable', 'in:available,maintenance,occupied'],
            '*.status'          => ['nullable', 'in:available,maintenance,occupied'],
=======
            '*.name'          => ['required', 'string', 'max:100'],
            '*.serial_number' => ['required', 'string', 'max:50', 'unique:devices,serial_number'],
            '*.status'        => ['nullable', 'in:available,maintenance,occupied'],
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
        ];
    }
}