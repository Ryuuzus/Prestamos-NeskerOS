<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La ocupación de aulas y dispositivos ya no se guarda: se calcula con las reservaciones aprobadas.
 * El estado guardado solo distingue "available" y "maintenance".
 *
 * IMPORTANTE: haz un respaldo de las tablas `classrooms` y `devices` antes de migrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Lo que estaba "occupied" pasa a "available".
        DB::table('classrooms')->where('status', 'occupied')->update(['status' => 'available']);
        DB::table('devices')->where('status', 'occupied')->update(['status' => 'available']);

        // 2) El ENUM solo conserva available / maintenance.
        Schema::table('classrooms', function (Blueprint $table) {
            $table->enum('status', ['available', 'maintenance'])->default('available')->change();
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->enum('status', ['available', 'maintenance'])->default('available')->change();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available')->change();
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available')->change();
        });
    }
};
