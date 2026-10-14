<?php

namespace App\Exports;

use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DeviceExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    /**
     * Consulta base para la exportación.
     */
    public function query(): Builder    
    {
        return Device::query();
    }

    /**
     * Mapeo individual por fila.
     */
    public function map($device): array
    {
        return [
            $device->id,
            $device->name,
            $device->serial_number,
            $device->status,
            $device->created_at ? $device->created_at->format('Y-m-d H:i:s') : '',
            $device->updated_at ? $device->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Número de Serie',
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