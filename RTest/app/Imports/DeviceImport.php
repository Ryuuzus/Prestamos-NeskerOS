<?php

namespace App\Imports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

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
        if (isset($data['name'])) {
            $data['name'] = (string) $data['name'];
        }

        return $data;
    }

    /**
     * Mapea y crea un nuevo objeto Device con los valores validados de la fila.
     */
    public function model(array $row): ?Model
    {
        $name         = $row['name'] ?? null;
        $serialNumber = $row['serial_number'] ?? null;
        $status       = strtolower($row['status'] ?? 'available');

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
     * Establece las reglas de validación para asegurar la calidad de la información del inventario.
     */
    public function rules(): array
    {
        return [
            '*.name'          => ['required', 'string', 'max:100'],
            '*.serial_number' => ['required', 'string', 'max:50', 'unique:devices,serial_number'],
            '*.status'        => ['nullable', 'in:available,maintenance,occupied'],
        ];
    }
}