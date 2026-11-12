<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropForeign(['device_id']);
            $table->foreign('classroom_id')->references('id')->on('classrooms')->restrictOnDelete();
            $table->foreign('device_id')->references('id')->on('devices')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['classroom_id']);
            $table->dropForeign(['device_id']);
            $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
            $table->foreign('device_id')->references('id')->on('devices')->nullOnDelete();
        });
    }
};
