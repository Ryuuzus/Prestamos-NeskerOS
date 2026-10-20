<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use Database\Seeders\Concerns\OnlyForDevelopment;
use Illuminate\Database\Seeder;

/**
 * Catálogos de prueba con prefijo DEMO, sin modificar catálogos reales existentes.
 * Incluye recursos disponibles, cantidades y recursos en mantenimiento.
 */
class DemoCatalogSeeder extends Seeder
{
    use OnlyForDevelopment;

    public const BUILDINGS = [
        ['name' => 'DEMO - Edificio Norte', 'floors' => 3, 'status' => 'available'],
        ['name' => 'DEMO - Biblioteca', 'floors' => 2, 'status' => 'available'],
        ['name' => 'DEMO - Laboratorios', 'floors' => 2, 'status' => 'maintenance'],
    ];

    // [edificio, nombre de aula, piso, estado]
    public const CLASSROOMS = [
        ['DEMO - Edificio Norte', 'DEMO A-101', 1, 'available'],
        ['DEMO - Edificio Norte', 'DEMO A-102', 1, 'available'],
        ['DEMO - Edificio Norte', 'DEMO A-201', 2, 'available'],
        ['DEMO - Biblioteca', 'DEMO Sala de Lectura', 1, 'available'],
        ['DEMO - Biblioteca', 'DEMO Sala de Capacitación', 2, 'available'],
        ['DEMO - Laboratorios', 'DEMO Laboratorio en mantenimiento', 1, 'maintenance'],
    ];

    public const DEVICES = [
        ['name' => 'DEMO Laptop', 'serial_number' => 'NES-DEMO-LAP-001', 'quantity' => 10, 'status' => 'available'],
        ['name' => 'DEMO Proyector', 'serial_number' => 'NES-DEMO-PRO-001', 'quantity' => 3, 'status' => 'available'],
        ['name' => 'DEMO Tablet', 'serial_number' => 'NES-DEMO-TAB-001', 'quantity' => 12, 'status' => 'available'],
        ['name' => 'DEMO Bocinas', 'serial_number' => 'NES-DEMO-BOC-001', 'quantity' => 2, 'status' => 'available'],
        ['name' => 'DEMO Cámara en mantenimiento', 'serial_number' => 'NES-DEMO-CAM-001', 'quantity' => 1, 'status' => 'maintenance'],
    ];

    public function run(): void
    {
        $this->assertDevelopmentEnvironment();

        foreach (self::BUILDINGS as $record) {
            Building::query()->firstOrCreate(['name' => $record['name']], [
                'floors' => $record['floors'],
                'status' => $record['status'],
            ]);
        }

        foreach (self::CLASSROOMS as [$buildingName, $name, $floor, $status]) {
            $building = Building::query()->where('name', $buildingName)->firstOrFail();
            Classroom::query()->firstOrCreate([
                'buildings_id' => $building->id,
                'classroom' => $name,
                'floor' => $floor,
            ], ['status' => $status]);
        }

        foreach (self::DEVICES as $record) {
            Device::query()->firstOrCreate(['serial_number' => $record['serial_number']], [
                'name' => $record['name'],
                'quantity' => $record['quantity'],
                'status' => $record['status'],
            ]);
        }

        $this->command?->info('Catálogos demo listos: 3 edificios, 6 aulas y 5 tipos de dispositivos.');
    }
}
