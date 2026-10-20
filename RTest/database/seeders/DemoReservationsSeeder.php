<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Concerns\OnlyForDevelopment;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Escenarios para Dashboard, Moderación, Historial, Recibos, Agenda y disponibilidad.
 *
 * Las reservas se generan con las mismas columnas que ReservationRequest y respetan
 * el máximo de 48 horas y los horarios 07:00-21:00 del config compartido.
 * Los estados se insertan directamente PARA CREAR CASOS DE PRUEBA históricos.
 * No se llama a approve() porque el servicio solo aprueba solicitudes pendientes.
 */
class DemoReservationsSeeder extends Seeder
{
    use OnlyForDevelopment;

    public function run(): void
    {
        $this->assertDevelopmentEnvironment();

        $users = [
            1 => User::query()->where('email', 'usuario@usuario.com')->firstOrFail(),
            2 => User::query()->where('email', 'usuario2@usuario.com')->firstOrFail(),
            3 => User::query()->where('email', 'usuario3@usuario.com')->firstOrFail(),
        ];

        $north = Building::query()->where('name', 'DEMO - Edificio Norte')->firstOrFail();
        $library = Building::query()->where('name', 'DEMO - Biblioteca')->firstOrFail();
        $classrooms = [
            'A101' => $this->getClassroom($north, 'DEMO A-101'),
            'A102' => $this->getClassroom($north, 'DEMO A-102'),
            'A201' => $this->getClassroom($north, 'DEMO A-201'),
            'B101' => $this->getClassroom($library, 'DEMO Sala de Lectura'),
            'B201' => $this->getClassroom($library, 'DEMO Sala de Capacitación'),
        ];
        $devices = [
            'laptop' => Device::query()->where('serial_number', 'NES-DEMO-LAP-001')->firstOrFail(),
            'projector' => Device::query()->where('serial_number', 'NES-DEMO-PRO-001')->firstOrFail(),
            'tablet' => Device::query()->where('serial_number', 'NES-DEMO-TAB-001')->firstOrFail(),
            'speakers' => Device::query()->where('serial_number', 'NES-DEMO-BOC-001')->firstOrFail(),
        ];

        // El lunes de dentro de dos semanas: por lo menos 7 días en el futuro,
        // independientemente de la hora o el día en que se ejecute el seeder.
        $futureMonday = Carbon::today()->startOfWeek(Carbon::MONDAY)->addWeeks(2);
        $today = Carbon::today();
        $at = static fn (Carbon $day, int $hour, int $minute = 0): Carbon =>
            $day->copy()->setTime($hour, $minute);

        // DISPONIBILIDAD: tres préstamos simultáneos de 3+4+3 laptops = 10/10.
        // Aulas diferentes, sin colisión. Útiles para validar la disponibilidad por cantidad.
        $this->createFixture($users[1], $classrooms['A101'], $devices['laptop'], 3,
            $at($futureMonday->copy()->addDay(), 10), $at($futureMonday->copy()->addDay(), 14), Reservation::STATUS_APPROVED);
        $this->createFixture($users[2], $classrooms['A102'], $devices['laptop'], 4,
            $at($futureMonday->copy()->addDay(), 11), $at($futureMonday->copy()->addDay(), 13), Reservation::STATUS_APPROVED);
        $this->createFixture($users[3], $classrooms['A201'], $devices['laptop'], 3,
            $at($futureMonday->copy()->addDay(), 11), $at($futureMonday->copy()->addDay(), 13), Reservation::STATUS_APPROVED);

        // PRÓXIMAS: aula + dispositivo, solo aula y solo dispositivo.
        $this->createFixture($users[3], $classrooms['A201'], $devices['projector'], 1,
            $at($futureMonday->copy()->addDays(2), 8), $at($futureMonday->copy()->addDays(2), 12), Reservation::STATUS_APPROVED);
        $this->createFixture($users[1], $classrooms['B101'], $devices['tablet'], 5,
            $at($futureMonday->copy()->addDays(3), 9), $at($futureMonday->copy()->addDays(3), 15), Reservation::STATUS_APPROVED);
        $this->createFixture($users[2], $classrooms['B201'], null, 1,
            $at($futureMonday->copy()->addDays(4), 14), $at($futureMonday->copy()->addDays(4), 18), Reservation::STATUS_APPROVED);
        $this->createFixture($users[3], null, $devices['speakers'], 1,
            $at($futureMonday->copy()->addDays(4), 9), $at($futureMonday->copy()->addDays(4), 13), Reservation::STATUS_APPROVED);

        // EN CURSO: inician ayer y terminan mañana; 42 horas (máximo permitido: 48).
        $this->createFixture($users[1], $classrooms['B201'], $devices['laptop'], 2,
            $at($today->copy()->subDay(), 15), $at($today->copy()->addDay(), 9), Reservation::STATUS_APPROVED);
        $this->createFixture($users[3], null, $devices['tablet'], 3,
            $at($today->copy()->subDay(), 15), $at($today->copy()->addDay(), 9), Reservation::STATUS_APPROVED);

        // DEVOLUCIÓN ATRASADA: aprobada, finalizó hace días y aún no fue completada.
        $this->createFixture($users[2], $classrooms['B101'], null, 1,
            $at($today->copy()->subDays(4), 10), $at($today->copy()->subDays(3), 10), Reservation::STATUS_APPROVED);

        // COMPLETADAS: una puntual y dos con distinto recurso / fecha de devolución.
        $this->createFixture($users[1], $classrooms['A102'], null, 1,
            $at($today->copy()->subDays(5), 8), $at($today->copy()->subDays(5), 13), Reservation::STATUS_COMPLETED,
            null, $at($today->copy()->subDays(5), 12, 30));
        $this->createFixture($users[2], $classrooms['A201'], $devices['tablet'], 2,
            $at($today->copy()->subDays(6), 9), $at($today->copy()->subDays(6), 13), Reservation::STATUS_COMPLETED,
            null, $at($today->copy()->subDays(6), 15));
        $this->createFixture($users[3], null, $devices['laptop'], 1,
            $at($today->copy()->subDays(8), 9), $at($today->copy()->subDays(8), 13), Reservation::STATUS_COMPLETED,
            null, $at($today->copy()->subDays(8), 12));

        // RECHAZADAS: motivo visible en historial del personal y en el listado del usuario.
        $this->createFixture($users[1], null, $devices['projector'], 1,
            $at($futureMonday, 9), $at($futureMonday, 12), Reservation::STATUS_REJECTED,
            'DEMO: solicitud duplicada; favor de corregir la selección.');
        $this->createFixture($users[2], $classrooms['A101'], null, 1,
            $at($futureMonday->copy()->addDays(3), 8), $at($futureMonday->copy()->addDays(3), 10), Reservation::STATUS_REJECTED,
            'DEMO: espacio no autorizado para este evento.');

        // PENDIENTES: moderador/admin pueden aprobarlas o rechazarlas.
        $this->createFixture($users[1], $classrooms['A201'], null, 1,
            $at($futureMonday, 10), $at($futureMonday, 12), Reservation::STATUS_PENDING);
        $this->createFixture($users[2], null, $devices['tablet'], 1,
            $at($futureMonday, 8), $at($futureMonday, 10), Reservation::STATUS_PENDING);
        $this->createFixture($users[3], null, $devices['projector'], 1,
            $at($futureMonday->copy()->addDay(), 14), $at($futureMonday->copy()->addDay(), 16), Reservation::STATUS_PENDING);
        $this->createFixture($users[2], $classrooms['A201'], null, 1,
            $at($futureMonday->copy()->addDays(2), 14), $at($futureMonday->copy()->addDays(2), 18), Reservation::STATUS_PENDING);
        $this->createFixture($users[1], $classrooms['B101'], null, 1,
            $at($futureMonday->copy()->addDays(4), 8), $at($futureMonday->copy()->addDays(4), 10), Reservation::STATUS_PENDING);
        $this->createFixture($users[2], null, $devices['speakers'], 1,
            $at($futureMonday->copy()->addDays(3), 10), $at($futureMonday->copy()->addDays(3), 13), Reservation::STATUS_PENDING);

        // PRUEBA NEGATIVA 1: pendiente que intenta usar A-101 en un horario ya aprobado.
        // En el flujo real podría ocurrir porque otra solicitud fue aprobada después de crearla.
        // Al pulsar 'Approve', ReservationService debe rechazarla por cruce de aula.
        $this->createFixture($users[3], $classrooms['A101'], $devices['projector'], 1,
            $at($futureMonday->copy()->addDay(), 11), $at($futureMonday->copy()->addDay(), 13), Reservation::STATUS_PENDING);

        // PRUEBA NEGATIVA 2: pendiente con recursos que pasaron a mantenimiento.
        // Al pulsar 'Approve', ReservationService debe mostrar la regla de mantenimiento.
        $maintenanceBuilding = Building::query()->where('name', 'DEMO - Laboratorios')->firstOrFail();
        $maintenanceRoom = $this->getClassroom($maintenanceBuilding, 'DEMO Laboratorio en mantenimiento');
        $maintenanceCamera = Device::query()->where('serial_number', 'NES-DEMO-CAM-001')->firstOrFail();
        $this->createFixture($users[1], $maintenanceRoom, $maintenanceCamera, 1,
            $at($futureMonday->copy()->addDays(2), 10), $at($futureMonday->copy()->addDays(2), 14), Reservation::STATUS_PENDING);

        $this->command?->info('Escenarios demo listos: pendientes, aprobadas, en curso, retrasadas, completadas y rechazadas.');
    }

    private function getClassroom(Building $building, string $name): Classroom
    {
        return Classroom::query()
            ->where('buildings_id', $building->id)
            ->where('classroom', $name)
            ->firstOrFail();
    }

    private function createFixture(
        User $user,
        ?Classroom $classroom,
        ?Device $device,
        int $quantity,
        Carbon $start,
        Carbon $end,
        string $status,
        ?string $rejectionReason = null,
        ?Carbon $actualReturn = null,
    ): void {
        if ($end->lte($start) || $start->diffInHours($end) > 48) {
            throw new RuntimeException('Intervalo de reservación DEMO inválido.');
        }
        if (! $classroom && ! $device) {
            throw new RuntimeException('Las reservaciones DEMO requieren aula o dispositivo.');
        }
        if ($device && $quantity > $device->quantity) {
            throw new RuntimeException("Cantidad DEMO mayor a disponibilidad total de {$device->name}.");
        }

        // Cada fixture usa una combinación única de usuario + aula + dispositivo.
        // Al repetir el seeder se conserva el estado actual, incluso si un moderador
        // ya aprobó o completó la solicitud. No se reponen ni duplican solicitudes.
        if (Reservation::query()
            ->where('user_id', $user->id)
            ->where('classroom_id', $classroom?->id)
            ->where('device_id', $device?->id)
            ->exists()) {
            return;
        }

        Reservation::query()->create([
            'user_id' => $user->id,
            'classroom_id' => $classroom?->id,
            'device_id' => $device?->id,
            'device_quantity' => $device ? $quantity : 1,
            'start_date' => $start->toDateString(),
            'start_time' => $start->format('H:i:s'),
            'end_date' => $end->toDateString(),
            'end_time' => $end->format('H:i:s'),
            'status' => $status,
            'rejection_reason' => $rejectionReason,
            'actual_return_time' => $actualReturn,
        ]);
    }
}
