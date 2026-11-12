<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\User;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class CatalogSpreadsheetWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN, 'email_verified_at' => now()]));
    }

    public function test_devices_skip_existing_and_repeated_serials_without_changing_inventory(): void
    {
        $existing = Device::create(['name' => 'Original', 'serial_number' => 'EXISTING', 'quantity' => 8, 'status' => 'maintenance']);

        $this->importCsv('devices', "name,serial_number,quantity,status\n, EXISTING ,-4,invalid\nNuevo,NEW,3,available\nCambio,NEW,99,maintenance\nOtro,OTHER,1,available\n")
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Dispositivos: 2 importados, 2 duplicados omitidos.');

        $this->assertDatabaseCount('devices', 3);
        $this->assertSame('Original', $existing->fresh()->name);
        $this->assertSame(8, $existing->fresh()->quantity);
        $this->assertSame('maintenance', $existing->fresh()->status);
        $this->assertDatabaseHas('devices', ['serial_number' => 'NEW', 'name' => 'Nuevo', 'quantity' => 3, 'status' => 'available']);
        $this->assertDatabaseHas('devices', ['serial_number' => 'OTHER']);
    }

    public function test_buildings_skip_normalized_duplicates_and_continue_after_them(): void
    {
        Building::create(['name' => 'Edificio Norte', 'floors' => 2, 'status' => 'maintenance']);

        $this->importCsv('buildings', "name,floors,status\n  Edificio   Norte  ,invalid,invalid\nEdificio Sur,3,available\n Edificio  Sur ,99,maintenance\nEdificio Este,1,available\n")
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Edificios: 2 importados, 2 duplicados omitidos.');

        $this->assertDatabaseCount('buildings', 3);
        $this->assertDatabaseHas('buildings', ['name' => 'Edificio Norte', 'floors' => 2, 'status' => 'maintenance']);
        $this->assertDatabaseHas('buildings', ['name' => 'Edificio Sur', 'floors' => 3, 'status' => 'available']);
        $this->assertDatabaseHas('buildings', ['name' => 'Edificio Este']);
    }

    public function test_classrooms_skip_duplicates_by_building_and_floor_with_id_or_name(): void
    {
        $first = Building::create(['name' => 'Norte', 'floors' => 2, 'status' => 'available']);
        $second = Building::create(['name' => 'Sur', 'floors' => 2, 'status' => 'available']);
        Classroom::create(['buildings_id' => $first->id, 'classroom' => 'Aula A', 'floor' => 1, 'status' => 'maintenance']);

        $this->importCsv('classrooms', "classroom,building_id,building_name,floor,status\n Aula  A ,, Norte ,1,invalid\nAula A,{$first->id},,2,available\nAula A,,Norte,2,maintenance\nAula A,{$second->id},Norte,1,available\n")
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Aulas: 2 importados, 2 duplicados omitidos.');

        $this->assertDatabaseCount('classrooms', 3);
        $this->assertDatabaseHas('classrooms', ['buildings_id' => $first->id, 'floor' => 1, 'status' => 'maintenance']);
        $this->assertDatabaseHas('classrooms', ['buildings_id' => $first->id, 'floor' => 2, 'status' => 'available']);
        $this->assertDatabaseHas('classrooms', ['buildings_id' => $second->id, 'floor' => 1, 'status' => 'available']);
    }

    public function test_reimporting_only_duplicates_succeeds_for_every_catalog(): void
    {
        $building = Building::create(['name' => 'Central', 'floors' => 2, 'status' => 'available']);
        $files = [
            ['devices', 'Dispositivos', "name,serial_number\nEquipo,SERIAL\n"],
            ['buildings', 'Edificios', "name,floors\nNorte,1\n"],
            ['classrooms', 'Aulas', "classroom,building_id,floor\nAula,{$building->id},1\n"],
        ];
        foreach ($files as [$catalog, $label, $csv]) {
            $this->importCsv($catalog, $csv)->assertSessionHasNoErrors()->assertSessionHas('success', $label.': 1 importado.');
            $this->importCsv($catalog, $csv)->assertSessionHasNoErrors()->assertSessionHas('success', $label.': 0 importados, 1 duplicado omitido.');
        }
        $this->assertDatabaseCount('devices', 1);
        $this->assertDatabaseCount('buildings', 2);
        $this->assertDatabaseCount('classrooms', 1);
    }

    public function test_invalid_new_rows_still_rollback_new_records_after_skipping_duplicates(): void
    {
        Building::create(['name' => 'Existente', 'floors' => 2, 'status' => 'available']);

        $this->importCsv('buildings', "name,floors\nExistente,invalid\nNuevo,1\nInválido,1.5\n")
            ->assertSessionHasErrors('file', null, 'importEdificios')
            ->assertSessionMissing('success');

        $this->assertDatabaseCount('buildings', 1);
        $this->assertDatabaseHas('buildings', ['name' => 'Existente', 'floors' => 2]);
        $this->withCookie(session()->getName(), session()->getId())->get(route('reports.excel'))
            ->assertSee('Fila 4')->assertSee('número entero');
    }

    public function test_downloads_are_real_spreadsheets_containing_only_import_headers(): void
    {
        Device::create(['name' => 'Dato privado', 'serial_number' => 'PRIVATE', 'quantity' => 1, 'status' => 'available']);
        $building = Building::create(['name' => 'Edificio privado', 'floors' => 2, 'status' => 'available']);
        Classroom::create(['buildings_id' => $building->id, 'classroom' => 'Aula privada', 'floor' => 1, 'status' => 'available']);

        $catalogs = [
            ['devices', 'dispositivos', ['name', 'serial_number', 'quantity', 'status']],
            ['buildings', 'edificios', ['name', 'floors', 'status']],
            ['classrooms', 'aulas', ['classroom', 'building_id', 'building_name', 'floor', 'status']],
        ];
        foreach ($catalogs as [$catalog, $filename, $headers]) {
            $response = $this->get(route('catalogs.template', $catalog))->assertOk()->assertDownload('plantilla-'.$filename.'.xlsx');
            $spreadsheet = IOFactory::load($response->baseResponse->getFile()->getPathname());
            $this->assertSame(1, $spreadsheet->getSheetCount());
            $this->assertSame([$headers], $spreadsheet->getActiveSheet()->toArray());
            $spreadsheet->disconnectWorksheets();
        }
        $this->get(route('catalogs.template', 'invalid'))->assertNotFound();
    }

    public function test_template_download_errors_are_specific_and_hide_internal_details(): void
    {
        Excel::shouldReceive('download')->once()->andThrow(new \RuntimeException('Private technical detail'));
        $this->get(route('catalogs.template', 'devices'))
            ->assertRedirect(route('reports.excel'))
            ->assertSessionHasErrors('template', null, 'templateDispositivos');
        $this->withCookie(session()->getName(), session()->getId())->get(route('reports.excel'))
            ->assertSee('No se pudo descargar la plantilla de Dispositivos.')
            ->assertDontSee('Private technical detail')->assertDontSee('No se pudieron importar');
    }

    public function test_templates_and_excel_guides_keep_admin_permissions(): void
    {
        foreach ([User::ROLE_USER, User::ROLE_MODERATOR] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'email_verified_at' => now()]));
            foreach (['devices', 'buildings', 'classrooms'] as $catalog) {
                $this->get(route('catalogs.template', $catalog))->assertForbidden();
            }
            $this->get(route('reports.excel.guide'))->assertForbidden();
            $this->get(route('help.account'))->assertOk();
            $this->get(route('help.catalogs'))->assertStatus($role === User::ROLE_MODERATOR ? 200 : 403);
        }
    }

    public function test_contextual_guides_open_new_tabs_and_keep_details_off_work_screens(): void
    {
        $pages = [
            ['reports.excel', route('reports.excel.guide')],
            ['buildings.index', route('help.catalogs').'#buildings'],
            ['classrooms.index', route('help.catalogs').'#classrooms'],
            ['devices.index', route('help.catalogs').'#devices'],
            ['admin.users.index', route('help.account').'#roles'],
            ['configuration', route('help.account')],
            ['agenda.index', route('reservations.guide')],
        ];
        foreach ($pages as [$page, $href]) {
            $html = $this->get(route($page))->assertOk()->getContent();
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            $link = $document->querySelector('a[href="'.$href.'"]');
            $this->assertNotNull($link, 'Missing guide on '.$page);
            $this->assertSame('_blank', $link->getAttribute('target'));
            $this->assertStringContainsString('noopener', $link->getAttribute('rel'));
        }
        $this->get(route('reports.excel'))->assertSee('Plantilla vacía')
            ->assertDontSee('serial_number')->assertDontSee('los duplicados producen un error');
        $this->get(route('reports.excel.guide'))->assertOk()->assertSee('serial_number')->assertSee('Duplicados y errores');
        $this->get(route('admin.users.index'))->assertDontSee('Permisos principales');
        $this->get(route('help.account'))->assertOk()->assertSee('Permisos por rol');
        $this->get(route('help.catalogs'))->assertOk()->assertSee('Devolver un aula significa regresar su llave.');
    }

    private function importCsv(string $catalog, string $content): TestResponse
    {
        return $this->post(route($catalog.'.import'), [
            'file' => UploadedFile::fake()->createWithContent('catalog.csv', $content),
        ]);
    }
}
