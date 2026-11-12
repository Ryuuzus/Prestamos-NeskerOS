<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\BuildingStatusService;
use App\Services\MaintenanceService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ReservationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $staff;

    private Classroom $room;

    private Device $device;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2030, 11, 9)->setTime(9, 0));
        $this->owner = User::factory()->create(['role' => 'user']);
        $this->staff = User::factory()->create();
        $this->staff->forceFill(['role' => 'admin'])->save();
        $building = Building::create(['name' => 'Edificio Norte', 'floors' => 2, 'status' => 'available']);
        $this->room = Classroom::create(['buildings_id' => $building->id, 'classroom' => 'Aula 1', 'floor' => 1, 'status' => 'available']);
        $this->device = Device::create(['name' => 'Tableta', 'serial_number' => 'TAB-1', 'quantity' => 4, 'status' => 'available']);
        $this->actingAs($this->staff);
    }

    private function reservation(array $data = []): Reservation
    {
        return Reservation::create(array_replace([
            'user_id' => $this->owner->id, 'classroom_id' => $this->room->id,
            'device_id' => $this->device->id, 'device_quantity' => 4,
            'start_date' => today(), 'start_time' => '10:00:00',
            'end_date' => today(), 'end_time' => '12:00:00', 'status' => 'approved',
        ], $data));
    }

    private function move(Reservation $r, string $action, array $data = []): TestResponse
    {
        return $this->from(route('reservations.operations', $r))
            ->patch(route('reservations.operations.update', $r), ['action' => $action] + $data);
    }

    public function test_approval_reserves_time_without_issuing_resources(): void
    {
        $r = $this->reservation(['status' => 'pending']);
        $this->patch(route('reservations.approve', $r))->assertSessionHas('success');
        $this->assertSame('approved', $r->fresh()->status);
        $this->assertNull($r->fresh()->key_issued_at);
        $this->assertSame(0, app(AvailabilityService::class)->deviceUnitsInUseNow($this->device));
        $this->assertDatabaseHas('reservation_events', ['reservation_id' => $r->id, 'action' => 'approved', 'actor_id' => $this->staff->id]);
        $this->get(route('admin.reservations.index'))->assertSee('Gestionar préstamo')->assertDontSee('>Complete<', false);
    }

    public function test_future_handover_and_returns_without_delivery_are_rejected(): void
    {
        $r = $this->reservation();
        foreach (['issue_key', 'issue_devices', 'return_key'] as $action) {
            $this->move($r, $action)->assertSessionHas('error');
        }
        $this->move($r, 'return_devices', ['quantity' => 1])->assertSessionHas('error');
        $this->patch(route('reservations.complete', $r))->assertRedirect(route('reservations.operations', $r));
        $this->assertNull($r->fresh()->actual_return_time);
        $this->assertSame(0, $r->events()->count());
    }

    public function test_combined_loan_returns_key_and_partial_device_quantities_independently(): void
    {
        $r = $this->reservation();
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_key')->assertSessionHas('success');
        $this->move($r, 'issue_devices')->assertSessionHas('success');
        $this->move($r, 'return_key')->assertSessionHas('success');
        $this->assertSame('approved', $r->fresh()->status);
        $this->assertNull(app(AvailabilityService::class)->classroomConflict($this->room, now(), now()->addHour()));
        $this->move($r, 'return_devices', ['quantity' => 2])->assertSessionHas('success');
        $this->assertSame(2, $this->device->fresh()->available_units_now);
        $this->assertSame(2, app(AvailabilityService::class)->deviceAvailableUnits($this->device, now(), now()->addHour()));
        $this->move($r, 'return_devices', ['quantity' => 3])->assertSessionHas('error');
        $this->assertSame(2, $r->fresh()->devices_returned);
        $this->travelTo(today()->setTime(12, 30));
        $this->move($r, 'return_devices', ['quantity' => 2])->assertSessionHas('success');
        $this->assertSame('completed', $r->fresh()->status);
        $this->assertTrue($r->fresh()->actual_return_time->equalTo(now()));
        $this->assertSame(4, $this->device->fresh()->available_units_now);
        $this->move($r, 'return_key')->assertSessionHas('error');
        $this->assertSame(6, $r->events()->count());
    }

    public function test_repeated_issue_and_repeated_return_do_not_change_inventory(): void
    {
        $r = $this->reservation(['classroom_id' => null]);
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_devices')->assertSessionHas('success');
        $this->move($r, 'issue_devices')->assertSessionHas('error');
        $this->move($r, 'return_devices', ['quantity' => 4])->assertSessionHas('success');
        $this->move($r, 'return_devices', ['quantity' => 1])->assertSessionHas('error');
        $this->assertSame(4, $r->fresh()->devices_returned);
    }

    public function test_overdue_physical_custody_blocks_next_handover_but_not_future_planning(): void
    {
        $old = $this->reservation();
        $this->travelTo(today()->setTime(10, 0));
        $this->move($old, 'issue_key')->assertSessionHas('success');
        $this->move($old, 'issue_devices')->assertSessionHas('success');
        $next = $this->reservation(['start_time' => '12:00:00', 'end_time' => '14:00:00']);
        $future = now()->addDays(10);
        $this->assertSame(4, app(AvailabilityService::class)->deviceAvailableUnits($this->device, $future, $future->copy()->addHours(2)));
        $this->travelTo(today()->setTime(12, 0));
        $this->move($next, 'issue_key')->assertSessionHas('error');
        $this->move($next, 'issue_devices')->assertSessionHas('error');
        $this->move($old, 'return_key')->assertSessionHas('success');
        $this->move($old, 'return_devices', ['quantity' => 4])->assertSessionHas('success');
        $this->move($next, 'issue_key')->assertSessionHas('success');
        $this->move($next, 'issue_devices')->assertSessionHas('success');
    }

    public function test_owner_can_cancel_approved_uncollected_reservation_without_deleting_history(): void
    {
        $r = $this->reservation();
        $this->actingAs($this->owner)->delete(route('reservations.destroy', $r), ['reason' => 'Se suspendió la actividad.'])->assertSessionHas('success');
        $this->assertSame('cancelled', $r->fresh()->status);
        $this->assertDatabaseHas('reservation_events', ['reservation_id' => $r->id, 'actor_id' => $this->owner->id, 'action' => 'cancelled']);
        $this->get(route('reservations.index'))->assertSee('Se suspendió la actividad.')->assertSee('Cancelada');
        $this->actingAs($this->staff)->get(route('reservations.history', ['status' => 'cancelled']))->assertSee('Se suspendió la actividad.');
    }

    public function test_owner_cannot_cancel_a_delivered_loan_or_another_users_reservation(): void
    {
        $r = $this->reservation();
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_key')->assertSessionHas('success');
        $this->actingAs($this->owner)->delete(route('reservations.destroy', $r), ['reason' => 'Cambio de planes'])->assertSessionHas('error');
        $stranger = User::factory()->create(['role' => 'user']);
        $this->actingAs($stranger)->delete(route('reservations.destroy', $r), ['reason' => 'Cambio de planes'])->assertForbidden();
        $this->assertSame('approved', $r->fresh()->status);
    }

    public function test_only_staff_can_record_movements_and_frontend_exposes_return_limits(): void
    {
        $r = $this->reservation();
        $this->actingAs($this->owner)->get(route('reservations.operations', $r))->assertForbidden();
        $this->move($r, 'issue_key')->assertForbidden();
        $this->actingAs($this->staff);
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_devices')->assertSessionHas('success');
        $this->get(route('reservations.operations', $r))->assertOk()->assertSee('Registrar devolución de equipos')->assertSee('max="4"', false)->assertSee('maxlength="500"', false);
        $this->move($r, 'return_devices', ['quantity' => 1.5])->assertSessionHasErrors('quantity');
        $this->move($r, 'release', ['reason' => str_repeat('a', 501)])->assertSessionHasErrors('reason');
    }

    public function test_scheduler_expires_pending_and_closes_no_shows_once_without_returning_loans(): void
    {
        $pending = $this->reservation(['status' => 'pending']);
        $noShow = $this->reservation(['classroom_id' => null]);
        $loan = $this->reservation(['device_id' => null]);
        $this->travelTo(today()->setTime(10, 0));
        $this->move($loan, 'issue_key')->assertSessionHas('success');
        $this->artisan('reservations:activate')->assertSuccessful();
        $this->assertSame('expired', $pending->fresh()->status);
        $this->assertSame('approved', $noShow->fresh()->status);
        $this->travelTo(today()->setTime(12, 0));
        $this->artisan('reservations:activate')->assertSuccessful();
        $this->assertSame('no_show', $noShow->fresh()->status);
        $this->assertSame('approved', $loan->fresh()->status);
        $this->assertNull($loan->fresh()->key_returned_at);
        $eventCount = DB::table('reservation_events')->count();
        $this->artisan('reservations:activate')->assertSuccessful();
        $this->assertSame($eventCount, DB::table('reservation_events')->count());
    }

    public function test_uncollected_part_can_be_released_without_closing_outstanding_key(): void
    {
        $r = $this->reservation();
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_key')->assertSessionHas('success');
        $this->move($r, 'release', ['reason' => 'No requiere equipos'])->assertSessionHas('success');
        $this->assertNotNull($r->fresh()->devices_released_at);
        $this->assertSame('approved', $r->fresh()->status);
        $this->move($r, 'return_key')->assertSessionHas('success');
        $this->assertSame('completed', $r->fresh()->status);
    }

    public function test_scheduler_releases_unused_part_after_other_part_was_returned(): void
    {
        $r = $this->reservation();
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_key')->assertSessionHas('success');
        $this->move($r, 'return_key')->assertSessionHas('success');
        $this->travelTo(today()->setTime(12, 0));
        $this->artisan('reservations:activate')->assertSuccessful();
        $this->assertSame('completed', $r->fresh()->status);
        $this->assertSame('10:00', $r->fresh()->actual_return_time->format('H:i'));
    }

    public function test_legacy_custody_requires_explicit_confirmation_before_return_or_expiration(): void
    {
        $r = $this->reservation(['device_id' => null, 'key_custody_unverified' => true]);
        $this->travelTo(today()->setTime(13, 0));
        $this->artisan('reservations:activate')->assertSuccessful();
        $this->assertSame('approved', $r->fresh()->status);
        $this->move($r, 'return_key')->assertSessionHas('error');
        $this->get(route('reservations.operations', $r))->assertOk()->assertSee('Verificar entrega anterior');
        $this->move($r, 'verify_key', ['custody' => 'issued', 'occurred_at' => today()->setTime(10, 15)->format('Y-m-d\\TH:i'), 'reason' => 'Confirmado con la persona responsable.'])->assertSessionHas('success');
        $this->assertSame('10:15', $r->fresh()->key_issued_at->format('H:i'));
        $this->move($r, 'return_key')->assertSessionHas('success');
        $this->assertSame('completed', $r->fresh()->status);
    }

    public function test_legacy_uncollected_resources_do_not_receive_fabricated_handover_dates(): void
    {
        $r = $this->reservation(['key_custody_unverified' => true, 'devices_custody_unverified' => true]);
        $this->travelTo(today()->setTime(13, 0));
        foreach (['verify_key', 'verify_devices'] as $action) {
            $this->move($r, $action, ['custody' => 'not_issued', 'reason' => 'Se confirmó que no se recogió.'])->assertSessionHas('success');
        }
        $this->artisan('reservations:activate')->assertSuccessful();
        $this->assertSame('no_show', $r->fresh()->status);
        $this->assertNull($r->fresh()->key_issued_at);
        $this->assertNull($r->fresh()->devices_issued_at);
        $this->assertNull($r->fresh()->actual_return_time);
    }

    public function test_multiple_old_records_can_be_reconciled_without_declaring_a_real_delivery_uncollected(): void
    {
        $old = $this->reservation(['device_id' => null, 'key_custody_unverified' => true]);
        $other = $this->reservation(['device_id' => null, 'key_custody_unverified' => true, 'start_time' => '12:00', 'end_time' => '14:00']);
        $this->travelTo(today()->setTime(13, 0));
        $this->move($old, 'verify_key', ['custody' => 'issued', 'occurred_at' => today()->setTime(10, 0)->format('Y-m-d\\TH:i'), 'reason' => 'Entrega anterior confirmada.'])->assertSessionHas('success');
        $this->move($old, 'return_key')->assertSessionHas('success');
        $this->assertSame('completed', $old->fresh()->status);
        $this->assertTrue($other->fresh()->key_custody_unverified);
        $this->assertTrue(Reservation::keyOutstanding()->where('classroom_id', $this->room->id)->exists());
    }

    public function test_emergency_maintenance_cancels_future_bookings_but_keeps_issued_key_outstanding(): void
    {
        $loan = $this->reservation(['device_id' => null]);
        $future = $this->reservation(['start_date' => today()->addDays(10), 'end_date' => today()->addDays(10)]);
        $this->travelTo(today()->setTime(10, 0));
        $this->move($loan, 'issue_key')->assertSessionHas('success');
        $this->post(route('maintenance.store', ['type' => 'classroom', 'id' => $this->room->id]), ['reason' => 'Fuga de agua'])->assertSessionHas('success');
        $this->assertSame('maintenance', $this->room->fresh()->status);
        $this->assertSame('cancelled', $future->fresh()->status);
        $this->assertSame('approved', $loan->fresh()->status);
        $this->assertNull($loan->fresh()->key_returned_at);
        $this->move($loan, 'return_key')->assertSessionHas('success');
        $this->assertSame('completed', $loan->fresh()->status);
    }

    public function test_device_fault_releases_uncollected_equipment_without_returning_the_key(): void
    {
        $r = $this->reservation();
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_key')->assertSessionHas('success');
        app(MaintenanceService::class)->report($this->device, 'Falla eléctrica');
        $this->assertSame('approved', $r->fresh()->status);
        $this->assertNotNull($r->fresh()->devices_released_at);
        $this->assertNull($r->fresh()->key_returned_at);
        $this->move($r, 'issue_devices')->assertSessionHas('error');
    }

    public function test_reopening_building_preserves_individual_classroom_maintenance(): void
    {
        $broken = Classroom::create(['buildings_id' => $this->room->buildings_id, 'classroom' => 'Aula 2', 'floor' => 1, 'status' => 'maintenance']);
        $building = $this->room->building;
        app(MaintenanceService::class)->report($building, 'Reparación general');
        app(BuildingStatusService::class)->update($building, ['status' => 'available']);
        $this->assertSame('available', $this->room->fresh()->status);
        $this->assertSame('maintenance', $broken->fresh()->status);
    }

    public function test_maintenance_requires_administrator_and_reason(): void
    {
        $url = route('maintenance.store', ['type' => 'device', 'id' => $this->device->id]);
        $this->post($url, [])->assertSessionHasErrors('reason');
        $this->actingAs($this->owner)->post($url, ['reason' => 'Falla'])->assertForbidden();
        $this->assertSame('available', $this->device->fresh()->status);
    }

    public function test_existing_time_rules_remain_shared_for_classrooms_and_devices(): void
    {
        $this->assertSame(7, config('reservations.min_days_in_advance'));
        $this->assertSame(2, config('reservations.min_duration_hours'));
        $this->assertSame(48, config('reservations.max_duration_hours'));
    }

    public function test_conflicting_approvals_are_rejected_but_touching_intervals_are_allowed(): void
    {
        $this->reservation();
        $conflict = $this->reservation(['status' => 'pending', 'device_id' => null]);
        $this->patch(route('reservations.approve', $conflict))->assertSessionHas('error');
        $touching = $this->reservation(['status' => 'pending', 'start_time' => '12:00', 'end_time' => '14:00']);
        $this->patch(route('reservations.approve', $touching))->assertSessionHas('success');
        $equipment = $this->reservation(['status' => 'pending', 'classroom_id' => null, 'device_quantity' => 1]);
        $this->patch(route('reservations.approve', $equipment))->assertSessionHas('error');
        $this->assertSame('pending', $equipment->fresh()->status);
    }

    public function test_the_agenda_distinguishes_uncollected_resources_from_physical_loans(): void
    {
        $r = $this->reservation();
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_key')->assertSessionHas('success');
        $events = $this->get(route('agenda.index', ['view' => 'day', 'date' => today()->toDateString()]))->assertOk()->viewData('eventsByDay');
        $today = collect($events[today()->toDateString()]);
        $this->assertSame('active', $today->firstWhere('kind', 'classroom')['phase']);
        $this->assertSame('upcoming', $today->firstWhere('kind', 'device')['phase']);
        $this->move($r, 'return_key')->assertSessionHas('success');
        $events = $this->get(route('agenda.index', ['view' => 'day', 'date' => today()->toDateString()]))->viewData('eventsByDay');
        $this->assertSame('completed', collect($events[today()->toDateString()])->firstWhere('kind', 'classroom')['phase']);
    }

    public function test_catalog_counters_and_history_survive_partial_returns_and_delete_requests(): void
    {
        $r = $this->reservation();
        $this->travelTo(today()->setTime(10, 0));
        $this->move($r, 'issue_devices')->assertSessionHas('success');
        $this->move($r, 'return_devices', ['quantity' => 1])->assertSessionHas('success');
        $this->get(route('devices.index'))->assertOk()->assertSee('1 / 4')->assertSee('Reportar avería');
        $this->get(route('classrooms.index'))->assertOk()->assertSee('Reportar avería');
        $this->get(route('buildings.index'))->assertOk()->assertSee('Reportar avería');
        $this->delete(route('devices.destroy', $this->device))->assertSessionHas('error');
        $this->delete(route('classrooms.destroy', $this->room))->assertSessionHas('error');
        $this->delete(route('buildings.destroy', $this->room->building))->assertSessionHas('error');
        $this->assertDatabaseHas('reservations', ['id' => $r->id, 'classroom_id' => $this->room->id, 'device_id' => $this->device->id]);
        $this->patch(route('devices.update', $this->device), ['name' => 'Tableta', 'serial_number' => 'TAB-1', 'quantity' => 2, 'status' => 'available'])->assertSessionHasErrors('quantity');
        $this->assertSame(4, (int) $this->device->fresh()->quantity);
    }

    public function test_resource_foreign_keys_prevent_losing_the_custody_record(): void
    {
        $this->reservation();
        $this->expectException(QueryException::class);
        DB::table('classrooms')->where('id', $this->room->id)->delete();
    }

    public function test_migration_flags_old_approvals_without_inventing_deliveries(): void
    {
        $migration = require database_path('migrations/2026_11_09_004630_add_custody_tracking_to_reservations.php');
        $migration->down();
        $old = $this->reservation(['start_time' => '08:00', 'end_time' => '10:00']);
        $future = $this->reservation();
        $closed = $this->reservation(['status' => 'completed', 'actual_return_time' => '2030-11-09 15:00:00']);
        $migration->up();
        $this->assertTrue($old->fresh()->key_custody_unverified);
        $this->assertTrue($old->fresh()->devices_custody_unverified);
        $this->assertNull($old->fresh()->key_issued_at);
        $this->assertNull($old->fresh()->devices_issued_at);
        $this->assertFalse($future->fresh()->key_custody_unverified);
        $this->assertFalse($closed->fresh()->key_custody_unverified);
        $this->assertSame('completed', $closed->fresh()->status);
        $this->assertSame('09:00', $closed->fresh()->actual_return_time->format('H:i'));
    }
}
