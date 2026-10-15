<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Separa las columnas DATETIME start_time / end_time en:
 *   start_date (DATE) + start_time (TIME)
 *   end_date   (DATE) + end_time   (TIME)
 *
 * Los datos existentes se conservan: primero se copian a columnas temporales,
 * después se eliminan las columnas viejas y por último se renombran las nuevas.
 *
 * IMPORTANTE: haz un respaldo de la tabla `reservations` antes de migrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Columnas temporales (nullable para poder llenarlas con los datos actuales).
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_clock')->nullable();
            $table->time('end_clock')->nullable();
        });

        // 2) Copiar los datos existentes.
        DB::statement('
            UPDATE reservations
            SET start_date  = DATE(start_time),
                start_clock = TIME(start_time),
                end_date    = DATE(end_time),
                end_clock   = TIME(end_time)
        ');

        // 3) Quitar las columnas DATETIME originales.
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });

        // 4) Renombrar las columnas de hora a su nombre definitivo.
        Schema::table('reservations', function (Blueprint $table) {
            $table->renameColumn('start_clock', 'start_time');
            $table->renameColumn('end_clock', 'end_time');
        });

        // 5) Ahora que tienen datos, se vuelven obligatorias.
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
            $table->time('start_time')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dateTime('start_at_tmp')->nullable();
            $table->dateTime('end_at_tmp')->nullable();
        });

        DB::statement('
            UPDATE reservations
            SET start_at_tmp = TIMESTAMP(start_date, start_time),
                end_at_tmp   = TIMESTAMP(end_date, end_time)
        ');

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'start_time', 'end_date', 'end_time']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->renameColumn('start_at_tmp', 'start_time');
            $table->renameColumn('end_at_tmp', 'end_time');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable(false)->change();
            $table->dateTime('end_time')->nullable(false)->change();
        });
    }
};