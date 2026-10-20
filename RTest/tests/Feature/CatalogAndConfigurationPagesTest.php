<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class CatalogAndConfigurationPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_configuration_page_displays_the_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana Ejemplo',
            'email' => 'ana@example.test',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('configuration'))
            ->assertOk()
            ->assertSee('Ajustes de cuenta')
            ->assertSee('Ana Ejemplo')
            ->assertSee('ana@example.test')
            ->assertSee('Usuario');
    }

    public function test_building_details_display_its_classrooms(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MODERATOR,
            'email_verified_at' => now(),
        ]);
        $building = Building::create([
            'name' => 'Edificio Central',
            'floors' => 3,
            'status' => Building::STATUS_AVAILABLE,
        ]);
        Classroom::create([
            'buildings_id' => $building->id,
            'classroom' => 'Aula Norte',
            'floor' => 2,
            'status' => 'available',
        ]);

        $this->actingAs($user)
            ->get(route('buildings.show', $building))
            ->assertOk()
            ->assertSee('Edificio Central')
            ->assertSee('Aula Norte')
            ->assertSee('2');
    }

    public function test_pagination_uses_bootstrap_five_markup(): void
    {
        $paginator = new LengthAwarePaginator(
            range(1, 10),
            20,
            10,
            1,
            ['path' => '/test'],
        );

        $html = $paginator->links()->render();

        $this->assertStringContainsString('class="pagination"', $html);
        $this->assertStringContainsString('class="page-link"', $html);
        $this->assertStringNotContainsString('w-5 h-5', $html);
    }
}
