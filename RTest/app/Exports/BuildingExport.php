<?php

namespace App\Exports;

use App\Models\Building;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Clase de Exportación de Edificios (BuildingExport).
 * 
 * Genera la exportación en Excel del catálogo de edificios formateando sus fechas y encabezados.
 * ==================================================================================================
 */
class BuildingExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    public function query(): Builder    
    {
        return Building::query();
    }

    public function map($building): array
    {
        return [
            $building->id,
            $building->name,
            $building->floors,
            $building->created_at?->format('Y-m-d H:i:s') ?? '',
            $building->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'floors',
            'created_at',
            'updated_at',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $columnRange = 'A1:' . Coordinate::stringFromColumnIndex(count($this->headings())) . '1';
                $event->sheet->getDelegate()->getStyle($columnRange)->getFont()->setBold(true);
            },
        ];
    }
}