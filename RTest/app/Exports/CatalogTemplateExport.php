<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CatalogTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function __construct(private string $catalog) {}

    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return match ($this->catalog) {
            'devices' => ['name', 'serial_number', 'quantity', 'status'],
            'buildings' => ['name', 'floors', 'status'],
            'classrooms' => ['classroom', 'building_id', 'building_name', 'floor', 'status'],
        };
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
