<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * React view contract for the Categorías slice.
 *
 * Categoría pages render only through the React host. The React grid
 * preserves the CRUD actions and uses the shared ConfirmDialog; the deletion
 * rule stays enforced server-side (SH-R3).
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

    public function test_categoria_detail_renders_react_with_pagination_and_search(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create(['nombre' => 'Temporada', 'descripcion' => 'Colección de temporada']);
        Producto::factory()->count(16)->create(['categoria_id' => $categoria->id, 'nombre' => 'Verano producto']);

        $response = $this->actingAs($admin)->get(route('categorias.show', ['categoria' => $categoria, 'buscar' => 'verano', 'per_page' => 15]));
        preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
        $props = json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true);
        $response->assertOk()->assertSee('id="react-root"', false);
        $this->assertSame('categorias.show', $props['page'] ?? null);
        $this->assertSame('Temporada', $props['categoria']['nombre'] ?? null);
        $this->assertSame(2, $props['productosPage']['last_page'] ?? null);
        $this->assertSame('verano', $props['query']['buscar'] ?? null);

        $staff = User::factory()->create(['role' => 'Control Stock']);
        $staffResponse = $this->actingAs($staff)->get(route('categorias.show', $categoria));
        preg_match('/data-props="([^"]*)"/', $staffResponse->getContent(), $staffMatches);
        $staffProps = json_decode(html_entity_decode($staffMatches[1] ?? '', ENT_QUOTES | ENT_HTML5), true);
        $this->assertFalse($staffProps['routes']['canViewProductCost'] ?? true);
        $this->assertArrayNotHasKey('precio_compra', $staffProps['productosPage']['data'][0] ?? []);

        $source = file_get_contents(base_path('resources/js/react/details.jsx'));
        $this->assertStringContainsString("params.set('page', String(page))", $source);
        $this->assertStringContainsString('action={routes.categoriaDetail}', $source);
        $this->assertStringContainsString("name=\"buscar\"", $source);
        $this->assertStringContainsString('canViewProductCost &&', $source);
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

    public function test_react_categoria_grid_keeps_crud_actions_and_replaces_native_confirm(): void
    {
        $source = file_get_contents(base_path('resources/js/react/categorias.jsx'));

        $this->assertStringContainsString('ConfirmDialog', $source, 'The grid must use the shared ConfirmDialog.');
        $this->assertStringNotContainsString('window.confirm', $source, 'The legacy native confirm() must be gone from the React grid.');

        foreach (['Nueva categoría', 'Ver', 'Editar', 'Eliminar'] as $contract) {
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
            "params.set('page'",
            "params.set('per_page'",
            "get('buscar')",
            'Aún no hay categorías',
            'Crear primera categoría',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        $this->assertStringContainsString("from './components/ui/Pagination.jsx'", $source);
        $this->assertStringContainsString('<Pagination', $source);
    }

    public function test_shared_react_pagination_supports_links_callbacks_and_page_sizes(): void
    {
        $source = file_get_contents(base_path('resources/js/react/components/ui/Pagination.jsx'));

        foreach (["[15, 30, 50]", 'hrefForPage', 'onPageChange', 'onPageSizeChange', 'aria-current', 'ariaLabel', 'disabled'] as $contract) {
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
            'saveResponse(response)',
            'routes.categorias',
            "defaultValue={categoria?.nombre ?? ''}",
            "defaultValue={categoria?.descripcion ?? ''}",
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
        $this->assertStringContainsString('required', $source);
        $this->assertStringContainsString('name="descripcion"', $source);
    }

    public function test_category_direct_routes_open_the_corresponding_modal(): void
    {
        $app = file_get_contents(base_path('resources/js/react/app.jsx'));
        $grid = file_get_contents(base_path('resources/js/react/categorias.jsx'));

        $this->assertStringContainsString("'categorias.create'", $app);
        $this->assertStringContainsString("'categorias.edit'", $app);
        $this->assertStringContainsString('initialModal', $grid);
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
        $created->assertOk()->assertJsonPath('saved', true)->assertJsonPath('redirect', route('categorias.index'));
        $categoria = Categoria::where('nombre', 'Accesorios')->firstOrFail();

        $this->put(route('categorias.update', $categoria), [
            'nombre' => 'Accesorios nuevos',
            'descripcion' => 'Descripción actualizada',
        ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('saved', true);
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
