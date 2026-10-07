
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

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Clase de Exportación de Dispositivos (DeviceExport).
 * 
 * Gestiona la generación y descarga en formato de hoja de cálculo Excel (.xlsx) del inventario
 * de dispositivos y equipos tecnológicos. Utiliza encabezados estandarizados en inglés para
 * garantizar compatibilidad directa e inmediata con la clase de importación (DeviceImport).
 * ==================================================================================================
 */
class DeviceExport implements FromQuery, WithHeadings, WithMapping, WithEvents
{
    /**
     * Define la consulta Eloquent base para la exportación de los registros de dispositivos.
     * 
     * @return Builder
     */
    public function query(): Builder    
    {
        return Device::query();
    }

    /**
     * Mapea y transforma los atributos de cada objeto Device a la estructura de columnas requerida en el Excel.
     * 
     * @param mixed $device Instancia del modelo Device.
     * @return array
     */
    public function map($device): array
    {
        return [
            $device->id,
            $device->name,
            $device->serial_number,
            $device->status,
            $device->created_at?->format('Y-m-d H:i:s') ?? '',
            $device->updated_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    /**
     * Define los nombres de las columnas que aparecerán en la primera fila (encabezados).
     * Alineados exactamente con las propiedades validadas en DeviceImport.
     * 
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'name',
            'serial_number',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Registra eventos para personalizar la hoja de cálculo tras su generación,
     * aplicando estilo en negrita a la fila de encabezados de forma dinámica.
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