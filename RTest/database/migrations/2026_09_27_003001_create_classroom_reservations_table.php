<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            
            // Relaciones (Llaves foráneas)
<<<<<<< HEAD
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained()->nullOnDelete();
=======
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('classroom_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('device_id')->nullable()->constrained()->onDelete('set null');
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
            
            // Fechas de la reservación
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            
            // Flujo de estados
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            
            // Campos para el administrador
            $table->text('rejection_reason')->nullable();
            $table->dateTime('actual_return_time')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};