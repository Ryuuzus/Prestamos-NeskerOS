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

class ClassroomExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    /**
     * Consulta base para la exportación.
     */
    public function query(): Builder    
    {
        return Classroom::query();
    }

    /**
     * Mapeo individual por fila.
     */
    public function map($classroom): array
    {
        return [
            $classroom->id,
            $classroom->building->name,
            $classroom->classroom,
            $classroom->floor,
            $classroom->status,
            $classroom->created_at ? $classroom->created_at->format('Y-m-d H:i:s') : '',
            $classroom->updated_at ? $classroom->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Edificio',
            'Aula',
            'Piso',
            'Estado',
            'Fecha de Creación',
            'Fecha de Actualización',
        ];
    }

    /**
     * Estilos para la hoja de Excel.
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