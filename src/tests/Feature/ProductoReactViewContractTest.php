<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coexistence contract for the migrated Productos slice (react driver).
 *
 * The product routes (index/create/edit) are React-owned by default but MUST
 * roll back to the legacy Blade surface via the per-route override or the
 * global FRONTEND_DRIVER=blade switch (SH-R1). Pure client-side behavior
 * (search, pagination, money mask, duplicate check) is guarded as a source
 * contract on productos.jsx, mirroring the VentaReactViewContractTest pattern.
 */
class ProductoReactViewContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_routes_render_the_react_host_when_enabled(): void
    {
        $source = file_get_contents(base_path('resources/views/layouts/app.blade.php'));

        $this->assertStringContainsString("'productos.index'", $source);
        $this->assertStringContainsString("'productos.create'", $source);
        $this->assertStringContainsString("'productos.edit'", $source);
        $this->assertStringContainsString("@include('react.app'", $source);
    }

    public function test_product_index_can_roll_back_to_the_legacy_blade_surface(): void
    {
        $admin = User::factory()->admin()->create();
        config(['frontend.routes.productos.index' => 'blade']);

        $response = $this->actingAs($admin)->get('/productos');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Inventario de Productos');
    }

    public function test_global_blade_driver_restores_the_product_surface(): void
    {
        $admin = User::factory()->admin()->create();
        config(['frontend.driver' => 'blade']);

        $response = $this->actingAs($admin)->get('/productos');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Inventario de Productos');
    }

    public function test_product_create_can_roll_back_to_the_legacy_blade_form(): void
    {
        $admin = User::factory()->admin()->create();
        config(['frontend.routes.productos.create' => 'blade']);

        $response = $this->actingAs($admin)->get('/productos/create');

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Nuevo Producto');
        $response->assertSee('Información del Producto');
    }

    public function test_product_edit_can_roll_back_to_the_legacy_blade_form(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        config(['frontend.routes.productos.edit' => 'blade']);

        $response = $this->actingAs($admin)->get(route('productos.edit', $producto));

        $response->assertOk()->assertDontSee('id="react-root"')->assertSee('Editar Producto');
    }

    public function test_react_product_index_preserves_catalog_search_and_filters(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        foreach ([
            '/productos/data',
            'inactivos',
            'Buscar por nombre, categoría, talle o color',
            'Limpiar filtros',
            'filtroCategoria',
            'filtroTalle',
            'filtroColor',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_product_index_preserves_fifteen_row_pagination_and_low_stock_badge(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        foreach (['POR_PAGINA = 15', 'Anterior', 'Siguiente', 'Página', 'cantidad <= 5', 'danger'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_product_mobile_cards_keep_complete_information(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        // Mobile cards must carry the same field set as the desktop table —
        // nothing hidden or clipped at narrow widths.
        foreach (['md:hidden', 'Talle', 'Color', 'P. Compra', 'P. Venta', 'Ganancia', 'Stock'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_product_actions_preserve_ver_editar_and_activate_toggle(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        foreach ([
            'Ver',
            'Editar',
            'Desactivar',
            'Activar',
            'Sí, desactivar',
            'Sí, activar',
            'Inactivo',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_product_form_preserves_money_inline_category_and_image_contracts(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        foreach ([
            '/categorias/inline',
            '/productos/check-duplicate',
            'multipart/form-data',
            'ProductImage',
            'Vista previa',
            'Crear categoría nueva',
            'inputMode="decimal"',
            'digitsLeft',
            'setSelectionRange',
            'Costo',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
        // The form must keep the "Costo" label vocabulary (never
        // "Precio de Compra").
        $this->assertStringNotContainsString('Precio de Compra', $source);
    }

    public function test_react_product_money_input_maps_typed_digits_to_full_amounts(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        // Product money inputs follow the legacy moneyInput FULL-AMOUNT mask:
        // typing "2500" must submit raw 2500 ($2.500,00) — never the POS
        // cents-mask conversion (25.00 / 100x error). The mask builds the raw
        // value straight from the typed digits (empty input maps to 0), and
        // the hidden sibling input submits that raw full amount to the backend.
        $this->assertStringContainsString('const rawValue = digits ? Number(digits) : 0;', $source);
        $this->assertStringNotContainsString('Number(digits) / 100', $source);
        $this->assertStringContainsString('<input type="hidden" name={name} value={raw} />', $source);
    }

    public function test_react_product_create_form_submits_to_the_store_endpoint(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        // The create form must POST to the store endpoint (`routes.productos`
        // → `/productos` POST), never to the GET form route `productosCreate`
        // (`/productos/create`) which would answer 405 for a POST (SH-R3).
        $this->assertStringContainsString(
            'const action = isEdit ? `${routes.productos}/${producto.id}` : routes.productos;',
            $source
        );
        $this->assertStringNotContainsString('routes.productosCreate;', $source);
    }
}
