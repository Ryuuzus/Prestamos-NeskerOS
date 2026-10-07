<?php

namespace App\Exports;

use App\Models\Classroom;
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
 * Clase de Exportación de Aulas (ClassroomExport).
 * 
 * Gestiona la generación y descarga en formato de hoja de cálculo Excel (.xlsx) del catálogo
 * de salones y aulas. Utiliza encabezados estandarizados en inglés para garantizar
 * compatibilidad directa e inmediata con la clase de importación (ClassroomImport).
 * ==================================================================================================
 */
class ClassroomExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    /**
     * Define la consulta Eloquent base para la exportación de registros, optimizando
     * la carga de la relación del edificio mediante Eager Loading (prevención de N+1).
     * 
     * @return Builder
     */
    public function query(): Builder    
    {
        return Classroom::query()->with('building');
    }

    /**
     * Mapea y transforma los campos de cada modelo de aula antes de insertarlo en la fila del archivo.
     * 
     * @param mixed $classroom
     * @return array
     */
    public function map($classroom): array
    {
        return [
            $classroom->id,
            $classroom->buildings_id,
            $classroom->classroom,
            $classroom->floor,
            $classroom->status,
            $classroom->created_at?->format('Y-m-d H:i:s') ?? '',
            $classroom->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    /**
     * Define los nombres de las columnas que aparecerán en la primera fila (encabezados).
     * Alineados exactamente con los nombres de atributos leídos en ClassroomImport.
     * 
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'building_id',
            'classroom',
            'floor',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Registra eventos para personalizar la hoja de cálculo tras su generación,
     * aplicando estilo en negrita a la fila de encabezados.
     * 
     * @return array
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