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

    public function test_product_detail_uses_react_by_default_and_keeps_blade_fallback_and_admin_cost_visibility(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->create(['role' => 'Control Stock']);
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id, 'precio_compra' => 20, 'precio_venta' => 40, 'imagen' => 'database/test']);

        $response = $this->actingAs($admin)->get(route('productos.show', $producto));
        preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
        $props = json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true);
        $response->assertOk()->assertSee('id="react-root"', false);
        $this->assertSame('productos.show', $props['page'] ?? null);
        $this->assertSame(true, $props['routes']['canViewProductCost'] ?? null);
        $this->assertStringStartsWith(route('productos.imagen', $producto), $props['routes']['productoImage'] ?? '');

        $this->actingAs($staff)->get(route('productos.show', $producto))
            ->assertOk()->assertSee('id="react-root"', false)
            ->assertDontSee('"precio_compra"', false);
        config(['frontend.routes.productos.show' => 'blade']);
        $this->actingAs($admin)->get(route('productos.show', $producto))
            ->assertOk()->assertDontSee('id="react-root"', false)->assertSee('Detalles del producto');
    }

    public function test_react_product_index_preserves_catalog_search_and_filters(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        foreach ([
            'routes.productosData',
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

    public function test_react_product_index_uses_server_pagination_and_shared_stock_threshold(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        foreach (['pageSize, setPageSize] = useState([15, 30, 50].includes', 'page: String(paginaActual)', 'catalogo?.data', 'onPageChange={setPaginaActual}', 'stockTone(producto.cantidad, lowStockThreshold)', 'danger'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }

        $this->assertStringContainsString("from './components/ui/Pagination.jsx'", $source);
        $this->assertStringContainsString('<Pagination', $source);
    }

    public function test_product_blade_fallback_keeps_search_and_all_supported_page_sizes(): void
    {
        $source = file_get_contents(base_path('resources/views/productos/index.blade.php'));

        foreach (['x-model="busqueda"', 'value="15"', 'value="30"', 'value="50"', 'per_page', 'paginaSiguiente()'] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
    }

    public function test_react_product_mobile_cards_keep_complete_information(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        // Mobile cards must carry the same field set as the desktop table —
        // nothing hidden or clipped at narrow widths.
        foreach (['xl:hidden', 'Talle', 'Color', 'P. Compra', 'P. Venta', 'Ganancia', 'Stock'] as $contract) {
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

    public function test_layout_exposes_installable_pwa_metadata_using_existing_icons(): void
    {
        $layout = file_get_contents(base_path('resources/views/layouts/app.blade.php'));
        $manifest = file_get_contents(public_path('manifest.webmanifest'));
        $data = json_decode($manifest, true, flags: JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('rel="manifest"', $layout);
        $this->assertStringContainsString('name="theme-color" content="#006948"', $layout);
        $this->assertStringContainsString('rel="apple-touch-icon"', $layout);
        $this->assertSame('standalone', $data['display']);
        $this->assertFileExists(public_path(ltrim($data['icons'][0]['src'], '/')));
    }

    public function test_react_page_modules_are_lazy_loaded_with_shared_loading_state(): void
    {
        $app = file_get_contents(base_path('resources/js/react/app.jsx'));

        $this->assertStringContainsString('lazy(() => import(', $app);
        $this->assertStringContainsString('<Suspense fallback={<LoadingState', $app);
        $this->assertStringContainsString("JSON_HEX_TAG", file_get_contents(base_path('resources/views/react/app.blade.php')));
    }

    public function test_react_product_form_preserves_money_inline_category_and_image_contracts(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));
        $money = file_get_contents(base_path('resources/js/react/lib/productMoneyInput.js'));

        foreach ([
            'routes.categoriasInline',
            'routes.productoDuplicateCheck',
            'multipart/form-data',
            'ProductImage',
            'Vista previa',
            'Crear categoría nueva',
            'inputMode="decimal"',
            'setSelectionRange',
            'Costo',
            'file:cursor-pointer',
            'error={errors?.imagen?.[0]}',
            'role="alert"',
            'saveResponse(response)',
            'value={categoryId}',
            'onChange={(event) => setCategoryId(event.target.value)}',
            'const closeInline = useCallback(() => setInlineOpen(false), []);',
            'onClose={closeInline}',
        ] as $contract) {
            $this->assertStringContainsString($contract, $source);
        }
        $this->assertStringContainsString('digitsLeft', $money);
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
        $this->assertStringContainsString('productMoneyInput(element.value, element.selectionStart', $source);
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

    public function test_modal_save_refreshes_catalog_only_after_validated_success(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        $this->assertStringContainsString('saveResponse(response)', $source);
        $this->assertStringNotContainsString('if (response.redirected)', $source);
    }

    public function test_product_catalog_opens_create_and_prefilled_edit_modals(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));
        $modal = file_get_contents(base_path('resources/js/react/components/ui/Modal.jsx'));

        $this->assertStringContainsString('setCrearAbierto(true)', $source);
        $this->assertStringContainsString('setEditingProducto(producto)', $source);
        $this->assertStringContainsString('producto={productoEditando}', $source);
        $this->assertStringContainsString('onSaved={async () =>', $source);
        $this->assertStringContainsString('size="product"', $source);
        $this->assertStringContainsString('max-w-[620px]', $modal);
        $this->assertStringContainsString('backdrop-blur-sm', $modal);
        $this->assertStringContainsString('event.key === \'Escape\'', $modal);
    }

    public function test_product_catalog_quick_view_reuses_modal_without_full_page_link(): void
    {
        $source = file_get_contents(base_path('resources/js/react/productos.jsx'));

        $this->assertStringContainsString('setProductoViendo(producto)', $source);
        $this->assertStringContainsString('open title="Detalles del producto"', $source);
        $this->assertStringNotContainsString('Ver página completa', $source);
    }
}
