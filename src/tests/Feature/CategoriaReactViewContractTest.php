<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coexistence contract for the migrated Categorías slice (react driver).
 *
 * categorias.index is React-owned by default but MUST roll back to the legacy
 * Blade grid via the per-route override or the global FRONTEND_DRIVER=blade
 * switch (SH-R1). The React grid preserves the CRUD actions and replaces the
 * legacy native confirm() with the shared ConfirmDialog; the deletion rule
 * stays enforced server-side (SH-R3).
 */
class CategoriaReactViewContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_categoria_index_route_renders_the_react_host_when_enabled(): void
    {
        $source = file_get_contents(base_path('resources/views/layouts/app.blade.php'));

        $this->assertStringContainsString("'categorias.index'", $source);
        $this->assertStringContainsString("@include('react.app'", $source);
    }

    public function test_categoria_index_can_roll_back_to_the_legacy_blade_grid(): void
    {
        $admin = User::factory()->admin()->create();
        config(['frontend.routes.categorias.index' => 'blade']);

        $response = $this->actingAs($admin)->get('/categorias');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Categorías de Productos');
    }

    public function test_global_blade_driver_restores_the_categoria_grid(): void
    {
        $admin = User::factory()->admin()->create();
        config(['frontend.driver' => 'blade']);

        $response = $this->actingAs($admin)->get('/categorias');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Categorías de Productos');
    }

    public function test_react_categoria_grid_keeps_crud_actions_and_replaces_native_confirm(): void
    {
        $source = file_get_contents(base_path('resources/js/react/categorias.jsx'));

        $this->assertStringContainsString('ConfirmDialog', $source, 'The grid must use the shared ConfirmDialog.');
        $this->assertStringNotContainsString('window.confirm', $source, 'The legacy native confirm() must be gone from the React grid.');

        foreach (['Nueva Categoría', 'Ver', 'Editar', 'Eliminar'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_categoria_grid_preserves_product_count_and_server_pagination(): void
    {
        $source = file_get_contents(base_path('resources/js/react/categorias.jsx'));

        foreach ([
            'productos_count',
            'last_page',
            'current_page',
            '?page=',
            'No hay categorías creadas aún',
            'Crear Primera Categoría',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }
}
