<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega a las reservaciones la cantidad de unidades del dispositivo que se solicitan.
 * Las reservaciones existentes quedan con device_quantity = 1.
 * Para reservaciones solo de aula la columna se ignora.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedInteger('device_quantity')->default(1)->after('device_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('device_quantity');
        });
    }
};
