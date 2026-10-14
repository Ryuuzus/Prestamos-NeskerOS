<?php

namespace App\Imports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

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
        if (isset($data['name'])) {
            $data['name'] = (string) $data['name'];
        }

        return $data;
    }

    /**
     * Mapeo del modelo.
     */
    public function model(array $row): ?Model
    {
        $name         = $row['nombre'] ?? $row['name'] ?? null;
        $serialNumber = $row['numero_de_serie'] ?? $row['serial_number'] ?? null;
        $status       = strtolower($row['estado'] ?? $row['status'] ?? 'available');

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
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            '*.nombre'          => ['nullable', 'string', 'max:100'],
            '*.name'            => ['nullable', 'string', 'max:100'],
            '*.numero_de_serie' => ['nullable', 'string', 'max:50', 'unique:devices,serial_number'],
            '*.serial_number'   => ['nullable', 'string', 'max:50', 'unique:devices,serial_number'],
            '*.estado'          => ['nullable', 'in:available,maintenance,occupied'],
            '*.status'          => ['nullable', 'in:available,maintenance,occupied'],
        ];
    }
}