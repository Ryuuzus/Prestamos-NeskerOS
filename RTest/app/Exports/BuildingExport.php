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
    /**
     * Define la consulta Eloquent base para la exportación de los registros de edificios.
     * 
     * @return Builder
     */
    public function query(): Builder    
    {
        return Building::query();
    }

    /**
     * Mapea y transforma los atributos de cada objeto Building a la estructura de columnas requerida en el Excel.
     * 
     * @param mixed $building Instancia del modelo Building.
     * @return array
     */
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

    /**
     * Define los nombres de las columnas que aparecerán en la primera fila (encabezados).
     * Alineados exactamente con las propiedades validadas en BuildingImport.
     * 
     * @return array
     */
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

    /**
     * Registra eventos para personalizar la hoja de cálculo tras su generación,
     * aplicando estilo en negrita a la fila de encabezados.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $headingCount = count($this->headings());
                $columnRange = 'A1:' . Coordinate::stringFromColumnIndex($headingCount) . '1';
                $event->sheet->getDelegate()->getStyle($columnRange)->getFont()->setBold(true);
            },
        ];
    }
}