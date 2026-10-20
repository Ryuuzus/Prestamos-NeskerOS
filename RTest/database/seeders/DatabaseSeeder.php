<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Datos de demostración de NeskerOS. No modifica ni elimina datos existentes.
 * Los seeders individuales prohíben su ejecución fuera de local/testing.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InitialUsersSeeder::class,
            DemoCatalogSeeder::class,
            DemoReservationsSeeder::class,
        ]);
    }
}
