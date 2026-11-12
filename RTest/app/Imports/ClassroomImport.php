<?php

namespace App\Imports;

use App\Models\Building;
use App\Models\Classroom;
use App\Rules\CatalogRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClassroomImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    public int $importedRows = 0;

    public int $duplicateRows = 0;

    public function isEmptyWhen(array $row): bool
    {
        $name = CatalogRules::normalize((string) ($row['classroom'] ?? ''));
        $floor = filter_var($row['floor'] ?? null, FILTER_VALIDATE_INT);
        if ($name === '' || mb_strlen($name) > CatalogRules::NAME_MAX || $floor === false) {
            return false;
        }

        $building = $this->resolveBuilding($row);
        if ($building && Classroom::where('buildings_id', $building->id)->where('floor', $floor)->where('classroom', $name)->exists()) {
            $this->duplicateRows++;

            return true;
        }

        return false;
    }

    private function resolveBuilding(array $data): ?Building
    {
        $id = $data['building_id'] ?? null;
        if ($id !== null && $id !== '') {
            return filter_var($id, FILTER_VALIDATE_INT) !== false ? Building::find($id) : null;
        }

        $name = CatalogRules::normalize((string) ($data['building_name'] ?? ''));

        return $name !== '' ? Building::where('name', $name)->first() : null;
    }

    private ?Building $building = null;

    private mixed $floor = null;

    public function prepareForValidation(array $data, int $index): array
    {
        $data['classroom'] = CatalogRules::normalize(isset($data['classroom']) ? (string) $data['classroom'] : null);
        $this->building = $this->resolveBuilding($data);
        $data['building_id'] = $this->building?->id;

        $this->floor = $data['floor'] ?? null;
        $data['status'] = $data['status'] ?? ($this->building?->status === 'maintenance' ? 'maintenance' : 'available');

        return $data;
    }

    public function model(array $row): ?Model
    {
        $this->importedRows++;

        return new Classroom([
            'buildings_id' => $row['building_id'],
            'classroom' => $row['classroom'],
            'floor' => $row['floor'],
            'status' => $row['status'],
        ]);
    }

    public function rules(): array
    {
        return [
            'classroom' => [...CatalogRules::name(),
                Rule::unique('classrooms', 'classroom')
                    ->where('buildings_id', $this->building?->id)
                    ->where('floor', $this->floor),
            ],
            'building_id' => ['bail', 'required', 'integer', 'exists:buildings,id'],
            'floor' => ['bail', 'required', 'integer', 'min:1', 'max:'.min(CatalogRules::FLOORS_MAX, $this->building?->floors ?? CatalogRules::FLOORS_MAX)],
            'status' => ['required', Rule::in($this->building?->isUnderMaintenance() ? ['maintenance'] : ['available', 'maintenance'])],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            ...CatalogRules::importMessages(array_keys($this->rules())),
            'classroom.unique' => 'Ya existe un aula con ese nombre en este piso del edificio.',
            'building_id.required' => 'Indica un building_id existente o un building_name que coincida con un edificio.',
            'floor.max' => 'El piso no puede superar los :max pisos permitidos para el edificio.',
            'status.in' => $this->building?->isUnderMaintenance()
                ? 'El edificio está en mantenimiento; el aula también debe estar en mantenimiento.'
                : 'El estado debe ser available o maintenance.',
        ];
    }
}
