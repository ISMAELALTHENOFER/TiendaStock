<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Zero-stock invariants (user rule):
 *   "los productos que tienen 0 no debe permitirse vender ni tampoco
 *    deberian estar activo justamente porque no cuentan con stock"
 *
 * Defense in depth:
 *   1. Model `saving` event forces activo=false whenever cantidad===0
 *      (single chokepoint for create/update and any future code path).
 *   2. ProductoController::activate() refuses to activate a 0-stock
 *      product with a user-facing error (no silent no-op).
 *   3. search() (POS autocomplete) adds `cantidad > 0` so a 0-stock row
 *      never surfaces in POS regardless of its activo flag.
 *   4. data() (admin catalog) keeps showing by activo + ?inactivos; admins
 *      MUST see 0-stock rows to manage them.
 *   5. StoreVentaRequest already rejects a sale line whose requested
 *      cantidad exceeds the product's stock; a 0-stock line is rejected
 *      even if it somehow reaches the store endpoint.
 */
class ProductoZeroStockTest extends TestCase
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

    public function test_product_created_with_zero_stock_is_inactive(): void
    {
        $response = $this->actingAs($this->admin)->post(route('productos.store'), [
            'nombre' => 'Producto Sin Stock',
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10,
            'precio_venta' => 20,
            'cantidad' => 0,
        ]);

        $response->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Producto Sin Stock',
            'cantidad' => 0,
            'activo' => false,
        ]);
    }

    public function test_product_updated_to_zero_stock_becomes_inactive(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'cantidad' => 5,
            'activo' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('productos.update', $producto), [
            'nombre' => $producto->nombre,
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10,
            'precio_venta' => 20,
            'cantidad' => 0,
        ]);

        $response->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'cantidad' => 0,
            'activo' => false,
        ]);
    }

    public function test_activating_zero_stock_product_refused(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'cantidad' => 0,
            'activo' => true, // saving event forces this to false on create
        ]);

        // Sanity: the model rule already made it inactive.
        $this->assertSame(false, $producto->fresh()->activo);

        $response = $this->actingAs($this->admin)
            ->from(route('productos.index'))
            ->patch(route('productos.activate', $producto));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'No se puede activar un producto sin stock.');

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'activo' => false,
            'cantidad' => 0,
        ]);
    }

    public function test_activating_positive_stock_product_succeeds(): void
    {
        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'cantidad' => 1,
            'activo' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('productos.activate', $producto));

        $response->assertRedirect(route('productos.index'));
        $response->assertSessionHas('success', 'Producto activado correctamente.');

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'activo' => true,
        ]);
    }

    public function test_search_excludes_zero_stock_products(): void
    {
        // Active, in-stock row that should surface.
        $visible = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'nombre' => 'ZeroStockBusqueda Visible',
            'cantidad' => 5,
            'activo' => true,
        ]);

        // A 0-stock row that is STILL flagged activo=true (e.g. stock was
        // depleted by a sale while activo was not synced, or a legacy row).
        // Inserted via a raw query to bypass the model `saving` event so we
        // can assert the search() `cantidad > 0` guard independently of the
        // activo-based filter.
        DB::table('productos')->insert([
            'nombre' => 'ZeroStockBusqueda Sin Stock',
            'descripcion' => null,
            'categoria_id' => $this->categoria->id,
            'precio_compra' => 10,
            'precio_venta' => 20,
            'cantidad' => 0,
            'talle' => null,
            'color' => null,
            'imagen' => null,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('productos.search', ['q' => 'ZeroStockBusqueda']));

        $response->assertOk();
        $response->assertJsonFragment(['id' => $visible->id]);
        $response->assertJsonMissing(['nombre' => 'ZeroStockBusqueda Sin Stock']);
    }

    public function test_sale_depleting_stock_deactivates_product(): void
    {
        $ventas = User::factory()->ventas()->create();

        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'precio_venta' => 25.00,
            'cantidad' => 3, // Stock exacto que se agotará con la venta.
            'activo' => true,
        ]);

        $response = $this->actingAs($ventas)->post('/ventas', [
            'items' => [
                ['producto_id' => $producto->id, 'cantidad' => 3],
            ],
            'subtotal' => 75.00,
            'total' => 75.00,
            'pago_con' => 75.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertRedirect(route('ventas.show', 1));
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'cantidad' => 0,
            'activo' => false, // saving event fires on save() → sync activo.
        ]);
    }

    public function test_pos_sale_with_zero_stock_product_rejected(): void
    {
        $ventas = User::factory()->ventas()->create();

        $producto = Producto::factory()->create([
            'categoria_id' => $this->categoria->id,
            'precio_venta' => 25.00,
            'cantidad' => 0,
            'activo' => true, // saving event forces false on create
        ]);

        $response = $this->actingAs($ventas)->postJson('/ventas', [
            'items' => [
                ['producto_id' => $producto->id, 'cantidad' => 1],
            ],
            'subtotal' => 25.00,
            'total' => 25.00,
            'pago_con' => 25.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'cantidad' => 0,
        ]);
    }
}
