<?php

namespace App\Imports;

use App\Models\Device;
use App\Rules\CatalogRules;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DeviceImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    public int $importedRows = 0;

    public int $duplicateRows = 0;

    public function isEmptyWhen(array $row): bool
    {
        $serial = trim((string) ($row['serial_number'] ?? ''));
        if ($serial !== '' && mb_strlen($serial) <= 50 && Device::where('serial_number', $serial)->exists()) {
            $this->duplicateRows++;

            return true;
        }

        return false;
    }

    public function prepareForValidation(array $data, int $index): array
    {
        foreach (['name', 'serial_number'] as $field) {
            $data[$field] = isset($data[$field]) ? trim((string) $data[$field]) : null;
        }
        $data['quantity'] = $data['quantity'] ?? 1;
        $data['status'] = $data['status'] ?? 'available';

        return $data;
    }

    public function model(array $row): ?Model
    {
        $this->importedRows++;

        return new Device([
            'name' => $row['name'],
            'serial_number' => $row['serial_number'],
            'quantity' => $row['quantity'],
            'status' => $row['status'],
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'string', 'max:100'],
            'serial_number' => ['bail', 'required', 'string', 'max:50', 'unique:devices,serial_number'],
            'quantity' => ['bail', 'required', 'integer', 'min:1', 'max:9999'],
            'status' => ['required', 'in:available,maintenance'],
        ];
    }

    public function customValidationMessages(): array
    {
        return CatalogRules::importMessages(array_keys($this->rules()));
    }
}
