<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationAgendaTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    public function test_the_agenda_is_accessible_for_each_role(): void
    {
        foreach (['user', 'moderator', 'admin'] as $role) {
            $this->actingAs($this->account($role))
                ->get(route('agenda.index'))
                ->assertOk()
                ->assertSee('Agenda de préstamos');
        }
    }

    public function test_the_agenda_requires_authentication(): void
    {
        $this->get(route('agenda.index'))->assertRedirect(route('login'));
    }

    public function test_it_lists_approved_resources_but_not_pending_resources(): void
    {
        $user = $this->account('user');
        $building = Building::create(['name' => 'Edificio Centro', 'floors' => 2, 'status' => 'available']);
        $classroom = Classroom::create([
            'buildings_id' => $building->id, 'classroom' => 'Sala Norte',
            'floor' => 1, 'status' => 'available',
        ]);
        $device = Device::create([
            'name' => 'Proyector Azul', 'serial_number' => 'TEST-A-300',
            'quantity' => 5, 'status' => 'available',
        ]);
        $other = Device::create([
            'name' => 'Pantalla Pendiente', 'serial_number' => 'TEST-P-301',
            'quantity' => 2, 'status' => 'available',
        ]);
        $date = today()->addDays(10)->toDateString();

        Reservation::create([
            'user_id' => $user->id, 'classroom_id' => $classroom->id,
            'device_id' => $device->id, 'device_quantity' => 2,
            'start_date' => $date, 'start_time' => '09:00',
            'end_date' => $date, 'end_time' => '11:00',
            'status' => Reservation::STATUS_APPROVED,
        ]);
        Reservation::create([
            'user_id' => $user->id, 'device_id' => $other->id, 'device_quantity' => 1,
            'start_date' => $date, 'start_time' => '12:00',
            'end_date' => $date, 'end_time' => '14:00',
            'status' => Reservation::STATUS_PENDING,
        ]);

        $this->actingAs($user)->get(route('agenda.index', [
            'view' => 'day', 'date' => $date,
        ]))->assertOk()
            ->assertSee('Sala Norte')
            ->assertSee('Proyector Azul')
            ->assertSee('09:00 – 11:00')
            ->assertDontSee('Pantalla Pendiente');

        $this->actingAs($user)->get(route('agenda.index', [
            'view' => 'day', 'date' => $date, 'type' => 'device',
        ]))->assertSee('Proyector Azul')->assertDontSee('Sala Norte');
    }

    public function test_multi_day_loan_appears_on_both_days(): void
    {
        $user = $this->account('user');
        $device = Device::create([
            'name' => 'Tablet Escolar', 'serial_number' => 'TEST-X-400',
            'quantity' => 2, 'status' => 'available',
        ]);
        $date = today()->addDays(10);
        Reservation::create([
            'user_id' => $user->id, 'device_id' => $device->id,
            'device_quantity' => 1,
            'start_date' => $date->toDateString(), 'start_time' => '10:00',
            'end_date' => $date->copy()->addDay()->toDateString(), 'end_time' => '08:00',
            'status' => Reservation::STATUS_APPROVED,
        ]);

        $this->actingAs($user)->get(route('agenda.index', [
            'view' => 'day', 'date' => $date->toDateString(),
        ]))->assertOk()->assertSee('Tablet Escolar')->assertSee('10:00 – 24:00');

        $this->actingAs($user)->get(route('agenda.index', [
            'view' => 'day', 'date' => $date->copy()->addDay()->toDateString(),
        ]))->assertOk()->assertSee('Tablet Escolar')->assertSee('00:00 – 08:00');
    }
}
