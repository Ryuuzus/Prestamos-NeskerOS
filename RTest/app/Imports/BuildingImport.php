<?php

namespace App\Imports;

use App\Models\Building;
use App\Rules\CatalogRules;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BuildingImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    public int $importedRows = 0;

    public int $duplicateRows = 0;

    public function isEmptyWhen(array $row): bool
    {
        $name = CatalogRules::normalize((string) ($row['name'] ?? ''));
        if ($name !== '' && mb_strlen($name) <= CatalogRules::NAME_MAX && Building::where('name', $name)->exists()) {
            $this->duplicateRows++;

            return true;
        }

        return false;
    }

    public function prepareForValidation(array $data, int $index): array
    {
        $data['name'] = CatalogRules::normalize(isset($data['name']) ? (string) $data['name'] : null);
        $data['status'] = $data['status'] ?? 'available';

        return $data;
    }

    public function model(array $row): ?Model
    {
        $this->importedRows++;

        return new Building([
            'name' => $row['name'],
            'floors' => $row['floors'],
            'status' => $row['status'],
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [...CatalogRules::name(), 'unique:buildings,name'],
            'floors' => ['bail', 'required', 'integer', 'min:1', 'max:'.CatalogRules::FLOORS_MAX],
            'status' => ['required', 'in:available,maintenance'],
        ];
    }

    public function customValidationMessages(): array
    {
        return CatalogRules::importMessages(array_keys($this->rules()));
    }
}
