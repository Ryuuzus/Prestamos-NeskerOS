<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
            $table->dateTime('key_issued_at')->nullable();
            $table->dateTime('key_returned_at')->nullable();
            $table->dateTime('key_released_at')->nullable();
            $table->dateTime('devices_issued_at')->nullable();
            $table->unsignedInteger('devices_returned')->default(0);
            $table->dateTime('devices_returned_at')->nullable();
            $table->dateTime('devices_released_at')->nullable();
            $table->boolean('key_custody_unverified')->default(false);
            $table->boolean('devices_custody_unverified')->default(false);
            $table->dateTime('closed_at')->nullable();
            $table->string('closure_reason', 500)->nullable();
        });
        Schema::table('classrooms', function (Blueprint $table) {
            $table->boolean('maintenance_from_building')->default(false);
        });
        Schema::create('reservation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->unsignedInteger('quantity')->nullable();
            $table->string('reason', 500)->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
        });

        // Earlier approvals did not record handovers; staff must verify their custody.
        $now = now();
        $legacy = DB::table('reservations')->where('status', 'approved')
            ->where(fn ($q) => $q->where('start_date', '<', $now->toDateString())
                ->orWhere(fn ($q) => $q->where('start_date', $now->toDateString())->where('start_time', '<=', $now->format('H:i:s'))));
        (clone $legacy)->whereNotNull('classroom_id')->update(['key_custody_unverified' => true]);
        (clone $legacy)->whereNotNull('device_id')->update(['devices_custody_unverified' => true]);
    }

    public function down(): void
    {
        if (DB::table('reservation_events')->exists() || DB::table('reservations')->whereIn('status', ['cancelled', 'expired', 'no_show'])->exists()) {
            throw new RuntimeException('No se puede revertir con movimientos registrados; conserva el historial y restaura un respaldo si fuera necesario.');
        }
        Schema::dropIfExists('reservation_events');
        Schema::table('classrooms', fn (Blueprint $table) => $table->dropColumn('maintenance_from_building'));
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['key_issued_at', 'key_returned_at', 'key_released_at', 'devices_issued_at', 'devices_returned', 'devices_returned_at', 'devices_released_at', 'key_custody_unverified', 'devices_custody_unverified', 'closed_at', 'closure_reason']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending')->change();
        });
    }
};
