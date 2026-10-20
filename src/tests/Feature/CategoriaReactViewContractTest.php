<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
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

    public function test_categoria_create_and_edit_routes_reuse_the_react_grid_modal_when_enabled(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();

        foreach ([
            ['/categorias/create', 'categorias.create'],
            ["/categorias/{$categoria->id}/edit", 'categorias.edit'],
        ] as [$url, $page]) {
            $response = $this->actingAs($admin)->get($url);
            preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
            $props = json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true);

            $response->assertOk()->assertSee('id="react-root"', false);
            $this->assertSame($page, $props['page'] ?? null);
            $this->assertNotEmpty($props['categorias']['data'] ?? []);
        }
    }

    public function test_categoria_create_and_edit_routes_keep_their_blade_fallbacks(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        config(['frontend.routes.categorias.create' => 'blade', 'frontend.routes.categorias.edit' => 'blade']);

        $this->actingAs($admin)->get('/categorias/create')
            ->assertOk()->assertDontSee('id="react-root"')->assertSee('Información de la Categoría');
        $this->actingAs($admin)->get("/categorias/{$categoria->id}/edit")
            ->assertOk()->assertDontSee('id="react-root"')->assertSee('Modificar Categoría');
    }

    public function test_global_blade_driver_keeps_create_and_edit_fallbacks_operational(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        config(['frontend.driver' => 'blade']);

        $this->actingAs($admin)->get('/categorias/create')
            ->assertOk()->assertDontSee('id="react-root"')->assertSee('Información de la Categoría');
        $this->actingAs($admin)->get("/categorias/{$categoria->id}/edit")
            ->assertOk()->assertDontSee('id="react-root"')->assertSee('Modificar Categoría');
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

    public function test_blade_categoria_grid_uses_styled_confirmation_for_delete(): void
    {
        $source = file_get_contents(base_path('resources/views/categorias/index.blade.php'));
        $layout = file_get_contents(base_path('resources/views/layouts/app.blade.php'));

        $this->assertStringContainsString('<x-confirm-dialog />', $layout);
        $this->assertStringContainsString('confirmDialogShow(', $source);
        $this->assertStringNotContainsString('confirm(', $source, 'The Blade category delete must not use native confirm().');
        $this->assertStringContainsString("route('categorias.destroy', \$categoria)", $source);
        $this->assertStringContainsString('@method(\'DELETE\')', $source);
        $this->assertStringContainsString('Eliminar', $source);
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

    public function test_react_categoria_grid_disables_unavailable_actions_semantically(): void
    {
        $source = file_get_contents(base_path('resources/js/react/categorias.jsx'));

        $this->assertStringContainsString('categoria.productos_count > 0', $source);
        $this->assertStringContainsString('<button type="button" disabled', $source);
        $this->assertStringContainsString('No se puede eliminar mientras tenga productos', $source);
    }

    public function test_react_category_host_receives_global_summary_values(): void
    {
        $admin = User::factory()->admin()->create();
        $categorias = Categoria::factory()->count(13)->create();
        Producto::factory()->count(2)->create(['categoria_id' => $categorias->last()->id]);

        $response = $this->actingAs($admin)->get('/categorias');
        preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
        $props = json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true);

        $response->assertOk();
        $this->assertSame([
            'total' => 13,
            'productos' => 2,
            'vacias' => 12,
        ], $props['categorias']['summary'] ?? null);
    }

    public function test_category_create_and_edit_use_modal_crud_with_inline_validation(): void
    {
        $source = file_get_contents(base_path('resources/js/react/categorias.jsx'));

        foreach ([
            'setModalCategoria(true)',
            'setModalCategoria(categoria)',
            '<Modal',
            'role="alert"',
            "headers = { Accept: 'application/json' }",
            'response.redirected',
            'routes.categorias',
            "defaultValue={categoria?.nombre ?? ''}",
            "defaultValue={categoria?.descripcion ?? ''}",
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
        $this->assertStringContainsString('required', $source);
        $this->assertStringContainsString('name="descripcion"', $source);
    }

    public function test_category_direct_routes_open_the_corresponding_modal_and_show_uses_system_controls(): void
    {
        $app = file_get_contents(base_path('resources/js/react/app.jsx'));
        $grid = file_get_contents(base_path('resources/js/react/categorias.jsx'));
        $show = file_get_contents(base_path('resources/views/categorias/show.blade.php'));

        $this->assertStringContainsString("'categorias.create'", $app);
        $this->assertStringContainsString("'categorias.edit'", $app);
        $this->assertStringContainsString('initialModal', $grid);
        $this->assertStringContainsString('bg-surface', $show);
        $this->assertStringContainsString('border-border', $show);
        $this->assertStringContainsString("route('productos.show', \$producto)", $show);
    }

    public function test_react_flash_toast_preserves_priority_and_type(): void
    {
        $app = file_get_contents(base_path('resources/js/react/app.jsx'));

        $this->assertStringContainsString("props.flash?.success ? 'success'", $app);
        $this->assertStringContainsString("props.flash?.error ? 'error'", $app);
        $this->assertStringContainsString('type={flashType}', $app);
    }

    public function test_react_flash_toast_consumes_each_type_with_distinct_semantic_styles(): void
    {
        $toast = file_get_contents(base_path('resources/js/react/components/ui/Toast.jsx'));

        $this->assertStringContainsString('Toast({ type = \'info\'', $toast);

        foreach ([
            'success' => 'bg-green-50 border-green-200 text-green-800',
            'error' => 'bg-red-50 border-red-200 text-red-800',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'info' => 'bg-sky-50 border-sky-200 text-sky-700',
        ] as $type => $styles) {
            $this->assertStringContainsString("$type: {", $toast);
            $this->assertStringContainsString($styles, $toast);
        }
    }

    public function test_category_create_and_edit_persist_and_return_json_validation_errors(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $created = $this->post(route('categorias.store'), [
            'nombre' => 'Accesorios',
            'descripcion' => 'Complementos',
        ], ['Accept' => 'application/json']);
        $created->assertRedirect(route('categorias.index'));
        $categoria = Categoria::where('nombre', 'Accesorios')->firstOrFail();

        $this->put(route('categorias.update', $categoria), [
            'nombre' => 'Accesorios nuevos',
            'descripcion' => 'Descripción actualizada',
        ], ['Accept' => 'application/json'])->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'nombre' => 'Accesorios nuevos',
            'descripcion' => 'Descripción actualizada',
        ]);

        $this->post(route('categorias.store'), ['nombre' => ''], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);

        $otra = Categoria::factory()->create(['nombre' => 'Ropa']);
        $this->put(route('categorias.update', $categoria), ['nombre' => 'Ropa'], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre']);
        $this->assertDatabaseHas('categorias', ['id' => $otra->id, 'nombre' => 'Ropa']);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($admin)->delete(route('categorias.destroy', $categoria))
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHas('error', 'No se puede eliminar una categoría que tiene productos.');
        $this->assertDatabaseHas('categorias', ['id' => $categoria->id]);
    }
}
