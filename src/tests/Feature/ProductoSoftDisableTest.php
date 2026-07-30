<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoSoftDisableTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->categoria = Categoria::factory()->create();
    }

    public function test_destroy_sets_activo_false_and_keeps_row(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('productos.destroy', $producto));

        $response->assertRedirect(route('productos.index'));
        $response->assertSessionHas('success', 'Producto desactivado correctamente.');

        // Row MUST still exist; only activo flipped.
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'activo' => false,
        ]);
        $this->assertDatabaseCount('productos', 1);
    }

    public function test_data_endpoint_excludes_inactive_products_by_default(): void
    {
        $active = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Producto Activo',
            'activo' => true,
        ]);
        $inactive = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Producto Inactivo',
            'activo' => false,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('productos.data'));

        $response->assertOk();
        $response->assertJsonFragment(['id' => $active->id]);
        $response->assertJsonMissing(['id' => $inactive->id]);
    }

    public function test_data_endpoint_excludes_zero_stock_active_products_by_default(): void
    {
        // Producto activo con stock 0 (estado edge: el saving event debería
        // forzarlo a inactivo, pero si por algún bypass quedó activo, el
        // inventario por defecto no debe mostrarlo).
        $inStock = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Con Stock',
            'cantidad' => 5,
            'activo' => true,
        ]);
        $zeroStock = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Sin Stock Activo',
            'cantidad' => 10,
            'activo' => true,
        ]);
        // Forzar stock 0 bypassando el saving event (simula bypass por
        // decrement u otro camino que no dispare el evento).
        \Illuminate\Support\Facades\DB::table('productos')
            ->where('id', $zeroStock->id)
            ->update(['cantidad' => 0]);

        $response = $this->actingAs($this->admin)->getJson(route('productos.data'));

        $response->assertOk();
        $response->assertJsonFragment(['id' => $inStock->id]);
        $response->assertJsonMissing(['nombre' => 'Sin Stock Activo']);
    }

    public function test_data_endpoint_with_inactivos_flag_shows_inactive_and_zero_stock(): void
    {
        $active = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Producto Activo',
            'activo' => true,
        ]);
        $inactive = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Producto Inactivo',
            'activo' => false,
        ]);
        // Producto con stock 0 y activo=true (bypass del saving event).
        // Debe aparecer en inactivos aunque activo sea true, porque no está
        // disponible para vender.
        $zeroStock = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'Producto Sin Stock',
            'cantidad' => 10,
            'activo' => true,
        ]);
        \Illuminate\Support\Facades\DB::table('productos')
            ->where('id', $zeroStock->id)
            ->update(['cantidad' => 0]);

        $response = $this->actingAs($this->admin)->getJson(route('productos.data', ['inactivos' => 1]));

        $response->assertOk();
        // Inactivos muestra: el desactivado + el de stock 0. No el activo con stock.
        $response->assertJsonFragment(['id' => $inactive->id, 'activo' => false]);
        $response->assertJsonFragment(['id' => $zeroStock->id]);
        $this->assertNotContains($active->id, array_column($response->json(), 'id'));
    }

    public function test_search_endpoint_excludes_inactive_products(): void
    {
        // Unique name so the search term matches only this product pair.
        $active = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'ZapatillaUnicaParaBusqueda',
            'activo' => true,
        ]);
        $inactive = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'ZapatillaUnicaParaBusqueda Inactiva',
            'activo' => false,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('productos.search', ['q' => 'ZapatillaUnicaParaBusqueda']));

        $response->assertOk();
        $response->assertJsonFragment(['id' => $active->id]);
        $response->assertJsonMissing(['id' => $inactive->id]);
    }

    public function test_search_returns_empty_for_disabled_only_match(): void
    {
        $inactive = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'SoloInactivoBusqueda',
            'activo' => false,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('productos.search', ['q' => 'SoloInactivoBusqueda']));

        $response->assertOk();
        $response->assertJsonMissing(['id' => $inactive->id]);
    }

    /**
     * The catalog JSON MUST NOT be cached long-term by the browser, otherwise
     * an edit → index roundtrip shows stale data until a manual reload.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
     */
    public function test_data_endpoint_does_not_send_long_cache_header(): void
    {
        $response = $this->actingAs($this->admin)->getJson(route('productos.data'));

        $response->assertOk();

        $cacheControl = $response->headers->get('Cache-Control');

        $this->assertNotNull($cacheControl, 'Cache-Control header must be present on the data endpoint.');
        $this->assertStringNotContainsString('max-age=300', $cacheControl, 'data() must not cache the catalog for 5 minutes.');
        $this->assertTrue(
            str_contains($cacheControl, 'no-store')
                || str_contains($cacheControl, 'no-cache')
                || str_contains($cacheControl, 'must-revalidate'),
            'data() must instruct the browser to never serve a stale catalog. Got: '.$cacheControl
        );
    }

    public function test_index_view_renders_desactivar_label(): void
    {
        $response = $this->actingAs($this->admin)->get(route('productos.index'));

        $response->assertOk();
        // The destroy action must be labeled "Desactivar", not "Eliminar".
        $response->assertSee('Desactivar');
    }
}
