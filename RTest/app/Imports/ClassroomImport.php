<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\Building;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ClassroomImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    /**
     * Converteert numerieke waarden naar het juiste type vóór de validatie.
     */
    public function prepareForValidation($data, $index)
    {
        if (isset($data['aula'])) {
            $data['aula'] = (string) $data['aula'];
        }
        if (isset($data['classroom'])) {
            $data['classroom'] = (string) $data['classroom'];
        }
        if (isset($data['piso'])) {
            $data['piso'] = (int) $data['piso'];
        }
        if (isset($data['floor'])) {
            $data['floor'] = (int) $data['floor'];
        }
        if (isset($data['edificio_id'])) {
            $data['buildings_id'] = (int) $data['edificio_id'];
        }
        if (isset($data['building_id'])) {
            $data['buildings_id'] = (int) $data['building_id'];
        }

        return $data;
    }

    /**
     * Model toewijzing.
     */
    public function model(array $row): ?Model
    {
        $classroom   = $row['aula'] ?? $row['classroom'] ?? null;
        $floor       = $row['piso'] ?? $row['floor'] ?? null;
        $status      = strtolower($row['estado'] ?? $row['status'] ?? 'available');
        
        // Zoek het gebouw ID op basis van ID of naam
        $buildingsId = $row['buildings_id'] ?? $row['building_id'] ?? $row['edificio_id'] ?? null;
        
        if (!$buildingsId && isset($row['edificio'])) {
            $building = Building::where('name', $row['edificio'])->first();
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
     * Validatieregels.
     */
    public function rules(): array
    {
        return [
            '*.classroom'    => ['nullable', 'string', 'max:100', 'unique:classrooms,classroom'],
            '*.aula'         => ['nullable', 'string', 'max:100', 'unique:classrooms,classroom'],
            '*.buildings_id' => ['nullable', 'integer', 'exists:buildings,id'],
            '*.building_id'  => ['nullable', 'integer', 'exists:buildings,id'],
            '*.edificio_id'  => ['nullable', 'integer', 'exists:buildings,id'],
            '*.floor'        => ['nullable', 'integer', 'min:1'],
            '*.piso'         => ['nullable', 'integer', 'min:1'],
            '*.status'       => ['nullable', 'in:available,maintenance,occupied'],
            '*.estado'       => ['nullable', 'in:available,maintenance,occupied'],
        ];
    }
}