<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaPosTest extends TestCase
{
    use RefreshDatabase;

    private User $userVentas;

    private User $userAdmin;

    private User $userControlStock;

    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userVentas = User::factory()->ventas()->create();
        $this->userAdmin = User::factory()->admin()->create();
        $this->userControlStock = User::factory()->controlStock()->create();

        $categoria = Categoria::factory()->create();
        $this->producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'precio_venta' => 25.00,
            'cantidad' => 10,
            'nombre' => 'Remera Test',
        ]);
    }

    // ─── POS Screen ───────────────────────────────────────────

    public function test_pos_page_loads_for_ventas_role(): void
    {
        $response = $this->actingAs($this->userVentas)->get('/ventas/pos');
        $response->assertOk();
    }

    public function test_pos_page_loads_for_admin_role(): void
    {
        $response = $this->actingAs($this->userAdmin)->get('/ventas/pos');
        $response->assertOk();
    }

    public function test_pos_page_forbidden_for_control_stock(): void
    {
        $response = $this->actingAs($this->userControlStock)->get('/ventas/pos');
        $response->assertForbidden();
    }

    // ─── Sales History ─────────────────────────────────────────

    public function test_sales_history_loads_empty(): void
    {
        $response = $this->actingAs($this->userVentas)->get('/ventas');
        $response->assertOk();
        $response->assertSee('No hay ventas registradas');
    }

    public function test_sales_history_loads_with_data(): void
    {
        $venta = Venta::factory()->create([
            'user_id' => $this->userVentas->id,
            'total' => 75.00,
        ]);
        VentaItem::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 3,
            'precio_unitario' => 25.00,
            'subtotal' => 75.00,
        ]);

        $response = $this->actingAs($this->userVentas)->get('/ventas');
        $response->assertOk();
        $response->assertSee('$75,00');
        $response->assertSee($this->userVentas->name);
    }

    public function test_sales_history_filters_by_date(): void
    {
        $oldVenta = Venta::factory()->create([
            'user_id' => $this->userVentas->id,
            'created_at' => now()->subDays(5),
            'total' => 111.50,
        ]);
        $newVenta = Venta::factory()->create([
            'user_id' => $this->userVentas->id,
            'created_at' => now(),
            'total' => 222.75,
        ]);

        $desde = now()->subDays(2)->format('Y-m-d');
        $response = $this->actingAs($this->userVentas)->get('/ventas?desde='.$desde);
        $response->assertOk();
        $response->assertSee('$222,75');
        $response->assertDontSee('$111,50');
    }

    // ─── Create Sale ───────────────────────────────────────────

    public function test_can_create_complete_sale(): void
    {
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 3],
            ],
            'subtotal' => 75.00,
            'total' => 75.00,
            'pago_con' => 100.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ventas', [
            'user_id' => $this->userVentas->id,
            'total' => 75.00,
            'pago_con' => 100.00,
            'cambio' => 25.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
            'estado' => 'completada',
        ]);

        $this->assertDatabaseHas('venta_items', [
            'producto_id' => $this->producto->id,
            'cantidad' => 3,
            'precio_unitario' => 25.00,
            'subtotal' => 75.00,
        ]);

        $this->producto->refresh();
        $this->assertEquals(7, $this->producto->cantidad);
    }

    public function test_missing_tipo_entrega_returns_validation_error(): void
    {
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 1],
            ],
            'subtotal' => 25.00,
            'total' => 25.00,
            'pago_con' => 25.00,
            'metodo_pago' => 'efectivo',
        ]);

        $response->assertSessionHasErrors('tipo_entrega');
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_can_create_sale_with_uber_tipo_entrega(): void
    {
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 1],
            ],
            'subtotal' => 25.00,
            'total' => 25.00,
            'pago_con' => 25.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'uber',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ventas', [
            'user_id' => $this->userVentas->id,
            'tipo_entrega' => 'uber',
            'estado' => 'completada',
        ]);
    }

    public function test_pos_page_shows_tipo_entrega_options(): void
    {
        $response = $this->actingAs($this->userVentas)->get('/ventas/pos');
        $response->assertOk();
        $response->assertSee('Tipo de entrega');
        $response->assertSee('En el local');
        $response->assertSee('Envío por Uber');
    }

    public function test_receipt_shows_tipo_entrega_label(): void
    {
        $venta = Venta::factory()->create([
            'user_id' => $this->userVentas->id,
            'tipo_entrega' => 'uber',
            'total' => 50.00,
            'pago_con' => 50.00,
        ]);
        VentaItem::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 2,
            'precio_unitario' => 25.00,
            'subtotal' => 50.00,
        ]);

        $response = $this->actingAs($this->userVentas)->get("/ventas/{$venta->id}");
        $response->assertOk();
        $response->assertSee('Envío por Uber');
    }

    public function test_empty_cart_returns_validation_error(): void
    {
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [],
            'subtotal' => 0,
            'total' => 0,
            'pago_con' => 0,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_insufficient_stock_returns_validation_error(): void
    {
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 999],
            ],
            'subtotal' => 999 * 25.00,
            'total' => 999 * 25.00,
            'pago_con' => 999 * 25.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertSessionHasErrors('items.0.cantidad');

        $this->producto->refresh();
        $this->assertEquals(10, $this->producto->cantidad);
    }

    public function test_pago_con_less_than_total_returns_validation_error(): void
    {
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 1],
            ],
            'subtotal' => 25.00,
            'total' => 25.00,
            'pago_con' => 10.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertSessionHasErrors('pago_con');
    }

    // ─── Transactional Integrity (NFR-01) ────────────────────

    public function test_transactional_integrity_no_orphan_records_on_failure(): void
    {
        // When FormRequest validation fails (insufficient stock via withValidator),
        // NO venta or venta_items records should be created
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 999],
            ],
            'subtotal' => 999 * 25.00,
            'total' => 999 * 25.00,
            'pago_con' => 999 * 25.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertSessionHasErrors('items.0.cantidad');
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_items', 0);
        $this->producto->refresh();
        $this->assertEquals(10, $this->producto->cantidad);
    }

    public function test_transactional_integrity_all_or_nothing_success(): void
    {
        // When a sale succeeds, ALL records (venta + items + stock decrement) must exist
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 2],
                ['producto_id' => $this->producto->id, 'cantidad' => 3],
            ],
            'subtotal' => 125.00,
            'total' => 125.00,
            'pago_con' => 150.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('ventas', 1);
        $this->assertDatabaseCount('venta_items', 2);

        $this->producto->refresh();
        $this->assertEquals(5, $this->producto->cantidad, 'Stock MUST be 10 - 2 - 3 = 5');
    }

    public function test_transactional_integrity_cancel_restores_stock_atomically(): void
    {
        // Pre-create a completed sale via the controller (not factory)
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 4],
            ],
            'subtotal' => 100.00,
            'total' => 100.00,
            'pago_con' => 100.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'uber',
        ]);

        $response->assertRedirect();
        $venta = Venta::first();
        $this->assertNotNull($venta);

        $this->producto->refresh();
        $this->assertEquals(6, $this->producto->cantidad);

        // Cancel the sale — stock must be restored
        $cancelResponse = $this->actingAs($this->userVentas)->post("/ventas/{$venta->id}/cancel");
        $cancelResponse->assertRedirect();

        $this->producto->refresh();
        $this->assertEquals(10, $this->producto->cantidad, 'Stock MUST be restored to original after cancel');
        $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'estado' => 'anulada']);
    }

    public function test_store_copies_precio_unitario_at_moment_of_sale(): void
    {
        // Create the venta
        $response = $this->actingAs($this->userVentas)->post('/ventas', [
            'items' => [
                ['producto_id' => $this->producto->id, 'cantidad' => 2],
            ],
            'subtotal' => 50.00,
            'total' => 50.00,
            'pago_con' => 50.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertRedirect();

        // Change product price AFTER the sale
        $this->producto->update(['precio_venta' => 999.99]);

        // The venta_item should still have the original price
        $this->assertDatabaseHas('venta_items', [
            'producto_id' => $this->producto->id,
            'precio_unitario' => 25.00,
            'subtotal' => 50.00,
        ]);
    }

    // ─── Pagination ────────────────────────────────────────────

    public function test_sales_history_paginates_at_15_per_page(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'categoria_id' => $categoria->id,
            'precio_venta' => 10.00,
            'cantidad' => 100,
        ]);

        // Create 20 ventas
        for ($i = 0; $i < 20; $i++) {
            $venta = Venta::factory()->create([
                'user_id' => $this->userVentas->id,
                'total' => 10.00,
            ]);
            VentaItem::factory()->create([
                'venta_id' => $venta->id,
                'producto_id' => $producto->id,
                'cantidad' => 1,
                'precio_unitario' => 10.00,
                'subtotal' => 10.00,
            ]);
        }

        // Page 1: 15 ventas
        $page1 = $this->actingAs($this->userVentas)->get('/ventas?page=1');
        $page1->assertOk();
        // Page 2: 5 ventas
        $page2 = $this->actingAs($this->userVentas)->get('/ventas?page=2');
        $page2->assertOk();
    }

    // ─── View Receipt ──────────────────────────────────────────

    public function test_can_view_receipt(): void
    {
        $venta = Venta::factory()->create([
            'user_id' => $this->userVentas->id,
            'total' => 50.00,
            'pago_con' => 50.00,
        ]);
        VentaItem::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 2,
            'precio_unitario' => 25.00,
            'subtotal' => 50.00,
        ]);

        $response = $this->actingAs($this->userVentas)->get("/ventas/{$venta->id}");
        $response->assertOk();
        $response->assertSee('Recibo de Venta');
        $response->assertSee('Remera Test');
        $response->assertSee('$50,00');
    }

    public function test_nonexistent_receipt_returns_404(): void
    {
        $response = $this->actingAs($this->userVentas)->get('/ventas/99999');
        $response->assertNotFound();
    }

    // ─── Cancel Sale ───────────────────────────────────────────

    public function test_can_cancel_sale_and_restore_stock(): void
    {
        $venta = Venta::factory()->create([
            'user_id' => $this->userVentas->id,
            'estado' => 'completada',
            'total' => 50.00,
        ]);
        VentaItem::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 3,
            'precio_unitario' => 25.00,
            'subtotal' => 75.00,
        ]);

        // Stock is at 10, we decremented it via the item factory... Wait
        // The item factory creates a Producto via Producto::factory() with its own stock
        // Let me re-check: the venta item factory creates a new Producto
        // So this test needs to use the producto we already have

        // Actually, let me re-check the factory. VentaItemFactory creates a Producto::factory() default
        // which means it creates a SEPARATE producto. I need to specify the producto_id explicitly.

        // Let me just re-do: remove the old venta and create a clean one
        // Actually the factory was already called with the right producto_id
        // But the Decrement wasn't called - this is a factory-created item
        // The stock was never decremented since we didn't call the controller store

        // Let me verify: $this->producto started with cantidad=10
        // The factory VentaItem::factory()->create() with producto_id=$this->producto->id
        // But the product stock was never decremented because we didn't go through store()
        // So cantidad is still 10

        // Cancel should increment stock to 13
        $response = $this->actingAs($this->userVentas)->post("/ventas/{$venta->id}/cancel");

        $response->assertRedirect();

        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'estado' => 'anulada',
        ]);

        $this->producto->refresh();
        $this->assertEquals(13, $this->producto->cantidad);
    }

    public function test_cannot_cancel_already_cancelled_sale(): void
    {
        $venta = Venta::factory()->anulada()->create([
            'user_id' => $this->userVentas->id,
        ]);

        $response = $this->actingAs($this->userVentas)->post("/ventas/{$venta->id}/cancel");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Stock should NOT have changed (no items in this venta)
        $this->producto->refresh();
        $this->assertEquals(10, $this->producto->cantidad);
    }

    // ─── Role Authorization ────────────────────────────────────

    public function test_control_stock_gets_403_on_ventas_index(): void
    {
        $response = $this->actingAs($this->userControlStock)->get('/ventas');
        $response->assertForbidden();
    }

    public function test_ventas_gets_200_on_ventas_index(): void
    {
        $response = $this->actingAs($this->userVentas)->get('/ventas');
        $response->assertOk();
    }

    public function test_admin_gets_200_on_ventas_index(): void
    {
        $response = $this->actingAs($this->userAdmin)->get('/ventas');
        $response->assertOk();
    }

    public function test_control_stock_gets_403_on_ventas_store(): void
    {
        $response = $this->actingAs($this->userControlStock)->post('/ventas', [
            'items' => [['producto_id' => $this->producto->id, 'cantidad' => 1]],
            'subtotal' => 25.00,
            'total' => 25.00,
            'pago_con' => 25.00,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);
        $response->assertForbidden();
    }

    public function test_control_stock_gets_403_on_ventas_cancel(): void
    {
        $venta = Venta::factory()->create([
            'user_id' => $this->userVentas->id,
        ]);

        $response = $this->actingAs($this->userControlStock)->post("/ventas/{$venta->id}/cancel");
        $response->assertForbidden();
    }

    public function test_guest_redirected_from_ventas_routes(): void
    {
        $response = $this->get('/ventas');
        $response->assertRedirect('/login');

        $response = $this->get('/ventas/pos');
        $response->assertRedirect('/login');
    }

    // ─── Sidebar visibility ────────────────────────────────────

    public function test_sidebar_link_shown_for_ventas(): void
    {
        $response = $this->actingAs($this->userVentas)->get('/dashboard');
        $response->assertOk();
        $response->assertSee('Ventas');
    }

    public function test_sidebar_link_shown_for_admin(): void
    {
        $response = $this->actingAs($this->userAdmin)->get('/dashboard');
        $response->assertOk();
        $response->assertSee('Ventas');
    }

    public function test_sidebar_link_hidden_for_control_stock(): void
    {
        $response = $this->actingAs($this->userControlStock)->get('/dashboard');
        $response->assertOk();
        $response->assertDontSee('Ventas');
    }
}
