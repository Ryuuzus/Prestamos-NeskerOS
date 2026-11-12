<?php

namespace Tests\Feature;

use App\Exports\BuildingExport;
use App\Exports\DeviceExport;
use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class InputAndSpreadsheetValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN, 'email_verified_at' => now()]));
    }

    public function test_searches_reject_long_or_array_values_without_flashing_the_input(): void
    {
        foreach (['buildings.index', 'classrooms.index', 'devices.index', 'admin.users.index', 'admin.reservations.index', 'reservations.history'] as $route) {
            foreach ([str_repeat('a', 101), ['unexpected']] as $search) {
                $this->get(route($route, ['search' => $search]))
                    ->assertRedirect(route($route))
                    ->assertSessionHasErrors('search', null, 'search')
                    ->assertSessionMissing('_old_input');
                $this->getJson(route($route, ['search' => $search]))->assertUnprocessable()->assertJsonValidationErrors('search');
            }
        }

        $this->actingAs(User::factory()->create(['role' => User::ROLE_USER, 'email_verified_at' => now()]))
            ->getJson(route('reservations.index', ['search' => str_repeat('a', 101)]))
            ->assertUnprocessable()->assertJsonValidationErrors('search');
    }

    public function test_search_zero_is_applied_and_the_limit_is_rendered(): void
    {
        Building::create(['name' => 'Edificio 0', 'floors' => 1, 'status' => 'available']);
        Building::create(['name' => 'Edificio Norte', 'floors' => 1, 'status' => 'available']);
        $this->get(route('buildings.index', ['search' => '0']))
            ->assertOk()->assertSee('Edificio 0')->assertDontSee('Edificio Norte')->assertSee('maxlength="100"', false);
        $this->get(route('buildings.index', ['search' => str_repeat('a', 100)]))->assertOk();
    }

    public function test_building_names_have_consistent_validation_and_normalization(): void
    {
        foreach (['<script>', '!!!', '   ', str_repeat('a', 101), ['not a string']] as $name) {
            $this->post(route('buildings.store'), ['name' => $name, 'floors' => 2, 'status' => 'available'])
                ->assertSessionHasErrors('name', null, 'createBuilding');
        }
        $this->assertDatabaseCount('buildings', 0);
        $this->post(route('buildings.store'), ['name' => '  Edificio   José-2.  ', 'floors' => 2, 'status' => 'available'])
            ->assertSessionHasNoErrors();
        $building = Building::firstOrFail();
        $this->assertSame('Edificio José-2.', $building->name);
        $this->post(route('buildings.store'), ['name' => 'Edificio  José-2.', 'floors' => 2, 'status' => 'available'])
            ->assertSessionHasErrors('name', null, 'createBuilding');
        $this->patch(route('buildings.update', $building), ['name' => $building->name, 'floors' => 2, 'status' => 'available'])
            ->assertSessionHasNoErrors();
    }

    public function test_floor_limits_and_existing_classrooms_prevent_invalid_building_changes(): void
    {
        foreach ([0, 101, 1.5] as $floors) {
            $this->post(route('buildings.store'), ['name' => 'Central', 'floors' => $floors, 'status' => 'available'])
                ->assertSessionHasErrors('floors', null, 'createBuilding');
        }
        $building = Building::create(['name' => 'Central', 'floors' => 3, 'status' => 'available']);
        Classroom::create(['buildings_id' => $building->id, 'classroom' => 'Aula 3', 'floor' => 3, 'status' => 'available']);
        $this->patch(route('buildings.update', $building), ['name' => 'Central', 'floors' => 2, 'status' => 'available'])
            ->assertSessionHasErrors('floors', null, 'editBuilding'.$building->id);
        $this->assertEquals(3, $building->fresh()->floors);
    }

    public function test_classroom_validation_rejects_symbols_arrays_and_out_of_range_floors(): void
    {
        $building = Building::create(['name' => 'Central', 'floors' => 2, 'status' => 'available']);
        $data = ['buildings_id' => $building->id, 'classroom' => 'Aula A-1', 'floor' => 1, 'status' => 'available'];
        foreach (['<a>', str_repeat('x', 101), ['invalid']] as $name) {
            $this->post(route('classrooms.store'), [...$data, 'classroom' => $name])->assertSessionHasErrors('classroom');
        }
        $this->post(route('classrooms.store'), [...$data, 'buildings_id' => ['invalid']])->assertSessionHasErrors('buildings_id');
        $this->post(route('classrooms.store'), [...$data, 'floor' => 3])->assertSessionHasErrors('floor');
        $this->post(route('classrooms.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('classrooms.store'), [...$data, 'floor' => 2])->assertSessionHasNoErrors();
        $this->post(route('classrooms.store'), $data)->assertSessionHasErrors('classroom');
        $this->assertDatabaseCount('classrooms', 2);
    }

    public function test_import_upload_errors_are_in_spanish_and_attached_to_the_right_catalog(): void
    {
        foreach (['buildings' => 'Edificios', 'classrooms' => 'Aulas', 'devices' => 'Dispositivos'] as $key => $label) {
            $this->post(route($key.'.import'))
                ->assertRedirect(route('reports.excel'))
                ->assertSessionHasErrors(['file' => 'Selecciona un archivo para importar.'], null, 'import'.$label);
            $this->followExcelRedirect()->assertOk()->assertSee('Selecciona un archivo para importar.');
        }
        $this->post(route('devices.import'), ['file' => UploadedFile::fake()->create('large.csv', 2049, 'text/csv')])
            ->assertSessionHasErrors('file', null, 'importDispositivos');
        $this->post(route('devices.import'), ['file' => UploadedFile::fake()->createWithContent('wrong.txt', "name,serial_number\nEquipo,SN-1")])
            ->assertSessionHasErrors('file', null, 'importDispositivos');
    }

    public function test_invalid_import_row_reports_its_location_and_rolls_back_the_entire_file(): void
    {
        $this->importCsv('buildings', "name,floors\nEdificio A,2\nEdificio B,1.5\n")
            ->assertSessionHasErrors('file', null, 'importEdificios');
        $this->assertDatabaseCount('buildings', 0);
        $this->followExcelRedirect()->assertOk()->assertSee('Fila 3')->assertSee('número entero')->assertDontSee('SQLSTATE');
    }

    public function test_duplicate_rows_are_skipped_and_new_invalid_names_are_rejected(): void
    {
        $this->importCsv('buildings', "name,floors\nEdificio A,2\nEdificio A,2\n")
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Edificios: 1 importado, 1 duplicado omitido.');
        $this->assertDatabaseCount('buildings', 1);
        $this->importCsv('buildings', "name,floors\n<script>,2\n")
            ->assertSessionHasErrors('file', null, 'importEdificios');
        $this->assertDatabaseCount('buildings', 1);
    }

    public function test_empty_or_wrong_header_files_are_not_reported_as_successful(): void
    {
        $this->importCsv('buildings', "name,floors\n")->assertSessionHasErrors('file', null, 'importEdificios')->assertSessionMissing('success');
        $this->importCsv('devices', "wrong,headers\nEquipo,SN-1\n")->assertSessionHasErrors('file', null, 'importDispositivos');
        $this->assertDatabaseCount('devices', 0);
    }

    public function test_device_import_preserves_quantity_and_rejects_retired_status(): void
    {
        $this->importCsv('devices', "name,serial_number,quantity,status\nProyector,SN-1,8,available\n")
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('devices', ['serial_number' => 'SN-1', 'quantity' => 8]);
        $this->importCsv('devices', "name,serial_number,quantity,status\nEquipo,SN-2,2,occupied\n")
            ->assertSessionHasErrors('file', null, 'importDispositivos');
        $this->assertDatabaseCount('devices', 1);
        $this->importCsv('devices', "name,serial_number\nEquipo,SN-3\n")->assertSessionHasNoErrors();
        $this->assertDatabaseHas('devices', ['serial_number' => 'SN-3', 'quantity' => 1]);
    }

    public function test_building_import_preserves_maintenance_status(): void
    {
        $this->importCsv('buildings', "name,floors,status\nEdificio Sur,2,maintenance\n")->assertSessionHasNoErrors();
        $this->assertDatabaseHas('buildings', ['name' => 'Edificio Sur', 'status' => 'maintenance']);
    }

    public function test_classroom_import_uses_building_and_floor_scoped_uniqueness(): void
    {
        $building = Building::create(['name' => 'Central', 'floors' => 2, 'status' => 'available']);
        $csv = "classroom,building_id,floor\nAula A,{$building->id},1\nAula A,{$building->id},2\n";
        $this->importCsv('classrooms', $csv)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('classrooms', 2);
        $this->importCsv('classrooms', "classroom,building_id,floor\nAula A,{$building->id},1\n")->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Aulas: 0 importados, 1 duplicado omitido.');
        $this->importCsv('classrooms', "classroom,building_name,floor\nAula B,Central,1\n")->assertSessionHasNoErrors();
        $this->assertDatabaseCount('classrooms', 3);
    }

    public function test_classroom_import_rejects_missing_buildings_invalid_floors_and_maintenance_conflicts(): void
    {
        $building = Building::create(['name' => 'Central', 'floors' => 2, 'status' => 'maintenance']);
        foreach ([
            "classroom,floor\nAula A,1\n",
            "classroom,building_name,floor\nAula A,Inexistente,1\n",
            "classroom,building_id,floor\nAula A,{$building->id},3\n",
            "classroom,building_id,floor,status\nAula A,{$building->id},1,available\n",
        ] as $csv) {
            $this->importCsv('classrooms', $csv)->assertSessionHasErrors('file', null, 'importAulas');
        }
        $this->assertDatabaseCount('classrooms', 0);
        $this->importCsv('classrooms', "classroom,building_id,floor\nAula A,{$building->id},1\n")->assertSessionHasNoErrors();
        $this->assertDatabaseHas('classrooms', ['classroom' => 'Aula A', 'status' => 'maintenance']);
    }

    public function test_exports_preserve_device_quantities_and_building_status(): void
    {
        $device = Device::create(['name' => 'Proyector', 'serial_number' => 'SN-1', 'quantity' => 8, 'status' => 'maintenance']);
        $export = new DeviceExport;
        $row = array_combine($export->headings(), $export->map($device));
        $this->assertSame(8, $row['quantity']);
        $this->get(route('devices.export'))->assertDownload('dispositivos.xlsx');
        $building = Building::create(['name' => 'Central', 'floors' => 2, 'status' => 'maintenance']);
        $export = new BuildingExport;
        $row = array_combine($export->headings(), $export->map($building));
        $this->assertSame('maintenance', $row['status']);
        $this->get(route('buildings.export'))->assertDownload('edificios.xlsx');
    }

    public function test_export_failures_show_a_catalog_specific_error_without_internal_details(): void
    {
        Excel::shouldReceive('download')->once()->andThrow(new \RuntimeException('Private technical detail'));
        $this->get(route('devices.export'))->assertRedirect(route('reports.excel'))
            ->assertSessionHasErrors('export', null, 'exportDispositivos');
        $this->followExcelRedirect()->assertOk()->assertSee('No se pudo exportar Dispositivos.')
            ->assertDontSee('Private technical detail');
    }

    public function test_reservation_instructions_have_their_own_page_and_links_for_all_roles(): void
    {
        foreach ([User::ROLE_USER, User::ROLE_MODERATOR, User::ROLE_ADMIN] as $role) {
            $user = User::factory()->create(['role' => $role, 'email_verified_at' => now()]);
            $this->actingAs($user);
            $this->get(route('reservations.guide'))->assertOk()
                ->assertSee('¿Cómo funcionan las reservaciones?')
                ->assertSee('días de anticipación')->assertSee(route('agenda.index'));
            $pages = $role === User::ROLE_USER
                ? ['agenda.index', 'reservations.index', 'reservations.create', 'receipts.index']
                : ['agenda.index', 'admin.reservations.index', 'reservations.history'];
            foreach ($pages as $page) {
                $this->get(route($page))->assertOk()
                    ->assertSee(route('reservations.guide'))->assertSee('Consulta la guía de reservaciones')
                    ->assertDontSee('¿Cómo funcionan las reservaciones?')
                    ->assertDontSee('Una solicitud pendiente todavía no garantiza la disponibilidad.');
            }
            $device = Device::create(['name' => 'Proyector', 'serial_number' => 'GUIDE-'.$role, 'quantity' => 1, 'status' => 'available']);
            $reservation = Reservation::create([
                'user_id' => $user->id, 'device_id' => $device->id, 'device_quantity' => 1,
                'start_date' => today()->addDays(10)->toDateString(), 'start_time' => '09:00',
                'end_date' => today()->addDays(10)->toDateString(), 'end_time' => '11:00',
                'status' => Reservation::STATUS_PENDING,
            ]);
            if ($role === User::ROLE_USER) {
                $this->get(route('reservations.edit', $reservation))->assertOk()
                    ->assertSee(route('reservations.guide'))->assertDontSee('¿Cómo funcionan las reservaciones?');
            }
            $reservation->update(['status' => Reservation::STATUS_APPROVED]);
            $this->get(route('pdf.ver', $reservation))->assertOk()
                ->assertSee(route('reservations.guide'))->assertDontSee('¿Cómo funcionan las reservaciones?');
        }
    }

    private function followExcelRedirect(): TestResponse
    {
        return $this->withCookie(session()->getName(), session()->getId())->get(route('reports.excel'));
    }

    private function importCsv(string $catalog, string $content): TestResponse
    {
        return $this->post(route($catalog.'.import'), [
            'file' => UploadedFile::fake()->createWithContent('catalog.csv', $content),
        ]);
    }
}
