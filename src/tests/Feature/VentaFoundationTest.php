<?php

namespace Tests\Feature;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VentaFoundationTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // T0.1 — Migration: ventas table
    // ──────────────────────────────────────────────

    public function test_ventas_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('ventas'));

        $columns = Schema::getColumnListing('ventas');
        $expected = [
            'id', 'user_id', 'cliente_nombre', 'subtotal', 'descuento',
            'impuesto', 'total', 'pago_con', 'cambio', 'metodo_pago',
            'tipo_entrega', 'estado', 'created_at', 'updated_at',
        ];

        foreach ($expected as $column) {
            $this->assertContains($column, $columns, "Column '{$column}' missing from ventas table.");
        }
    }

    public function test_ventas_table_has_foreign_key_to_users(): void
    {
        $this->assertTrue(Schema::hasColumn('ventas', 'user_id'));

        // Verify FK exists by checking the column has an index
        $foreignKeys = Schema::getForeignKeys('ventas');
        $this->assertNotEmpty($foreignKeys, 'ventas table should have foreign keys.');
    }

    // ──────────────────────────────────────────────
    // T0.2 — Migration: venta_items table
    // ──────────────────────────────────────────────

    public function test_venta_items_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('venta_items'));

        $columns = Schema::getColumnListing('venta_items');
        $expected = [
            'id', 'venta_id', 'producto_id', 'cantidad',
            'precio_unitario', 'subtotal', 'created_at', 'updated_at',
        ];

        foreach ($expected as $column) {
            $this->assertContains($column, $columns, "Column '{$column}' missing from venta_items table.");
        }
    }

    public function test_venta_items_table_has_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasColumn('venta_items', 'venta_id'));
        $this->assertTrue(Schema::hasColumn('venta_items', 'producto_id'));

        $foreignKeys = Schema::getForeignKeys('venta_items');
        $this->assertCount(2, $foreignKeys, 'venta_items table should have 2 foreign keys.');
    }

    // ──────────────────────────────────────────────
    // T1.1 — Venta model
    // ──────────────────────────────────────────────

    public function test_venta_model_can_be_created(): void
    {
        $user = User::factory()->ventas()->create();
        $venta = Venta::create([
            'user_id' => $user->id,
            'cliente_nombre' => 'Juan Pérez',
            'subtotal' => 100.00,
            'descuento' => 0,
            'impuesto' => 0,
            'total' => 100.00,
            'pago_con' => 100.00,
            'cambio' => 0,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => Venta::TIPO_ENTREGA_LOCAL,
            'estado' => 'completada',
        ]);

        $this->assertInstanceOf(Venta::class, $venta);
        $this->assertDatabaseHas('ventas', [
            'id' => $venta->id,
            'user_id' => $user->id,
            'cliente_nombre' => 'Juan Pérez',
            'total' => 100.00,
        ]);
    }

    public function test_venta_model_has_correct_table(): void
    {
        $venta = new Venta;
        $this->assertEquals('ventas', $venta->getTable());
    }

    public function test_venta_model_casts_monetary_fields_to_decimal(): void
    {
        $venta = new Venta;
        $casts = $venta->getCasts();

        $monetaryFields = ['subtotal', 'descuento', 'impuesto', 'total', 'pago_con', 'cambio'];
        foreach ($monetaryFields as $field) {
            $this->assertArrayHasKey($field, $casts, "Cast not defined for '{$field}'");
            $this->assertStringContainsString('decimal', $casts[$field]);
        }
    }

    public function test_venta_belongs_to_user(): void
    {
        $user = User::factory()->ventas()->create();
        $venta = Venta::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $venta->user);
        $this->assertEquals($user->id, $venta->user->id);
    }

    public function test_venta_has_many_items(): void
    {
        $venta = Venta::factory()->create();

        $item1 = VentaItem::factory()->create(['venta_id' => $venta->id]);
        $item2 = VentaItem::factory()->create(['venta_id' => $venta->id]);

        $this->assertCount(2, $venta->items);
        $this->assertInstanceOf(VentaItem::class, $venta->items->first());
    }

    public function test_venta_is_completada_returns_true_when_estado_is_completada(): void
    {
        $venta = Venta::factory()->create(['estado' => 'completada']);
        $this->assertTrue($venta->isCompletada());
        $this->assertFalse($venta->isAnulada());
    }

    public function test_venta_is_anulada_returns_true_when_estado_is_anulada(): void
    {
        $venta = Venta::factory()->anulada()->create();
        $this->assertTrue($venta->isAnulada());
        $this->assertFalse($venta->isCompletada());
    }

    // ──────────────────────────────────────────────
    // T1.2 — VentaItem model
    // ──────────────────────────────────────────────

    public function test_venta_item_model_can_be_created(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $venta = Venta::factory()->create();
        $item = VentaItem::create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario' => 15.00,
            'subtotal' => 30.00,
        ]);

        $this->assertInstanceOf(VentaItem::class, $item);
        $this->assertDatabaseHas('venta_items', [
            'id' => $item->id,
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);
    }

    public function test_venta_item_model_has_correct_table(): void
    {
        $item = new VentaItem;
        $this->assertEquals('venta_items', $item->getTable());
    }

    public function test_venta_item_casts_monetary_fields_to_decimal(): void
    {
        $item = new VentaItem;
        $casts = $item->getCasts();

        $this->assertArrayHasKey('precio_unitario', $casts);
        $this->assertArrayHasKey('subtotal', $casts);
        $this->assertStringContainsString('decimal', $casts['precio_unitario']);
        $this->assertStringContainsString('decimal', $casts['subtotal']);
    }

    public function test_venta_item_belongs_to_venta(): void
    {
        $venta = Venta::factory()->create();
        $item = VentaItem::factory()->create(['venta_id' => $venta->id]);

        $this->assertInstanceOf(Venta::class, $item->venta);
        $this->assertEquals($venta->id, $item->venta->id);
    }

    public function test_venta_item_belongs_to_producto(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $item = VentaItem::factory()->create(['producto_id' => $producto->id]);

        $this->assertInstanceOf(Producto::class, $item->producto);
        $this->assertEquals($producto->id, $item->producto->id);
    }

    // ──────────────────────────────────────────────
    // T1.3 — Producto ventaItems relationship
    // ──────────────────────────────────────────────

    public function test_producto_has_many_venta_items(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $venta = Venta::factory()->create();

        $item1 = VentaItem::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
        ]);
        $item2 = VentaItem::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
        ]);

        $this->assertCount(2, $producto->ventaItems);
        $this->assertInstanceOf(VentaItem::class, $producto->ventaItems->first());
    }

    // ──────────────────────────────────────────────
    // T2.1 — VentaFactory
    // ──────────────────────────────────────────────

    public function test_venta_factory_creates_valid_venta(): void
    {
        $venta = Venta::factory()->create();

        $this->assertInstanceOf(Venta::class, $venta);
        $this->assertNotNull($venta->user_id);
        $this->assertNotNull($venta->subtotal);
        $this->assertNotNull($venta->total);
        $this->assertNotNull($venta->pago_con);
        $this->assertNotNull($venta->metodo_pago);
        $this->assertEquals('completada', $venta->estado);
    }

    public function test_venta_factory_anulada_state(): void
    {
        $venta = Venta::factory()->anulada()->create();

        $this->assertEquals('anulada', $venta->estado);
    }

    // ──────────────────────────────────────────────
    // T2.2 — VentaItemFactory + ProductoFactory
    // ──────────────────────────────────────────────

    public function test_venta_item_factory_creates_valid_item(): void
    {
        $item = VentaItem::factory()->create();

        $this->assertInstanceOf(VentaItem::class, $item);
        $this->assertNotNull($item->venta_id);
        $this->assertNotNull($item->producto_id);
        $this->assertNotNull($item->cantidad);
        $this->assertNotNull($item->precio_unitario);
        $this->assertNotNull($item->subtotal);
    }

    public function test_venta_item_factory_calculates_subtotal(): void
    {
        $item = VentaItem::factory()->create([
            'cantidad' => 3,
            'precio_unitario' => 25.50,
        ]);

        $this->assertEquals(76.50, $item->subtotal);
    }

    public function test_producto_factory_creates_valid_producto(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->assertInstanceOf(Producto::class, $producto);
        $this->assertNotNull($producto->nombre);
        $this->assertNotNull($producto->precio_venta);
        $this->assertNotNull($producto->cantidad);
        $this->assertTrue($producto->activo);
    }

    // ──────────────────────────────────────────────
    // T3.1 — StoreVentaRequest
    // ──────────────────────────────────────────────

    public function test_store_venta_request_authorize_returns_true(): void
    {
        $request = new StoreVentaRequest;

        $this->assertTrue($request->authorize());
    }

    public function test_store_venta_request_has_required_validation_rules(): void
    {
        $request = new StoreVentaRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('items', $rules);
        $this->assertArrayHasKey('items.*.producto_id', $rules);
        $this->assertArrayHasKey('items.*.cantidad', $rules);
        $this->assertArrayHasKey('pago_con', $rules);
        $this->assertArrayHasKey('metodo_pago', $rules);
        $this->assertArrayHasKey('tipo_entrega', $rules);
        $this->assertArrayHasKey('cliente_nombre', $rules);
    }

    public function test_store_venta_request_has_spanish_error_messages(): void
    {
        $request = new StoreVentaRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('items.required', $messages);
        $this->assertStringContainsString('producto', $messages['items.required']);
        $this->assertArrayHasKey('metodo_pago.in', $messages);
        $this->assertStringContainsString('efectivo', $messages['metodo_pago.in']);
        $this->assertArrayHasKey('tipo_entrega.required', $messages);
        $this->assertArrayHasKey('tipo_entrega.in', $messages);
    }

    public function test_store_venta_request_validates_items_required(): void
    {
        $user = User::factory()->ventas()->create();
        $routeUrl = '/_test/ventas/validate';

        Route::post($routeUrl, function (StoreVentaRequest $request) {
            return response()->json(['ok' => true]);
        })->middleware('web');

        $response = $this->actingAs($user)->postJson($routeUrl, [
            'items' => [],
            'pago_con' => 100,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
            'total' => 100,
            'subtotal' => 100,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    }

    public function test_store_venta_request_validates_metodo_pago_values(): void
    {
        $user = User::factory()->ventas()->create();
        $routeUrl = '/_test/ventas/validate-pago';

        Route::post($routeUrl, function (StoreVentaRequest $request) {
            return response()->json(['ok' => true]);
        })->middleware('web');

        $producto = Producto::factory()->create([
            'precio_venta' => 100.00,
            'cantidad' => 10,
        ]);

        $response = $this->actingAs($user)->postJson($routeUrl, [
            'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
            'subtotal' => 100,
            'total' => 100,
            'pago_con' => 100,
            'metodo_pago' => 'cripto',
            'tipo_entrega' => 'local',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['metodo_pago']);
    }

    public function test_store_venta_request_validates_pago_con_gte_total(): void
    {
        $user = User::factory()->ventas()->create();
        $routeUrl = '/_test/ventas/validate-pago-gte';

        Route::post($routeUrl, function (StoreVentaRequest $request) {
            return response()->json(['ok' => true]);
        })->middleware('web');

        $producto = Producto::factory()->create([
            'precio_venta' => 100.00,
            'cantidad' => 10,
        ]);

        $response = $this->actingAs($user)->postJson($routeUrl, [
            'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
            'subtotal' => 100,
            'total' => 100,
            'pago_con' => 50,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pago_con']);
    }

    public function test_store_venta_request_validates_stock_sufficiency(): void
    {
        $user = User::factory()->ventas()->create();
        $routeUrl = '/_test/ventas/validate-stock';

        Route::post($routeUrl, function (StoreVentaRequest $request) {
            return response()->json(['ok' => true]);
        })->middleware('web');

        $producto = Producto::factory()->create([
            'precio_venta' => 100.00,
            'cantidad' => 2,
        ]);

        $response = $this->actingAs($user)->postJson($routeUrl, [
            'items' => [['producto_id' => $producto->id, 'cantidad' => 5]],
            'subtotal' => 500,
            'total' => 500,
            'pago_con' => 500,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.cantidad']);
    }

    public function test_store_venta_request_passes_with_valid_data(): void
    {
        $user = User::factory()->ventas()->create();
        $routeUrl = '/_test/ventas/validate-valid';

        Route::post($routeUrl, function (StoreVentaRequest $request) {
            return response()->json(['ok' => true]);
        })->middleware('web');

        $producto = Producto::factory()->create([
            'precio_venta' => 50.00,
            'cantidad' => 10,
        ]);

        $response = $this->actingAs($user)->postJson($routeUrl, [
            'items' => [['producto_id' => $producto->id, 'cantidad' => 2]],
            'subtotal' => 100,
            'total' => 100,
            'pago_con' => 100,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'local',
            'cliente_nombre' => 'Cliente Test',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
    }

    // ──────────────────────────────────────────────
    // T3.2 — StoreVentaRequest: tipo_entrega validation
    // ──────────────────────────────────────────────

    public function test_store_venta_request_requires_tipo_entrega(): void
    {
        $user = User::factory()->ventas()->create();
        $routeUrl = '/_test/ventas/validate-tipo-entrega-required';

        Route::post($routeUrl, function (StoreVentaRequest $request) {
            return response()->json(['ok' => true]);
        })->middleware('web');

        $producto = Producto::factory()->create([
            'precio_venta' => 50.00,
            'cantidad' => 10,
        ]);

        $response = $this->actingAs($user)->postJson($routeUrl, [
            'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
            'subtotal' => 50,
            'total' => 50,
            'pago_con' => 50,
            'metodo_pago' => 'efectivo',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tipo_entrega']);
    }

    public function test_store_venta_request_rejects_invalid_tipo_entrega(): void
    {
        $user = User::factory()->ventas()->create();
        $routeUrl = '/_test/ventas/validate-tipo-entrega-invalid';

        Route::post($routeUrl, function (StoreVentaRequest $request) {
            return response()->json(['ok' => true]);
        })->middleware('web');

        $producto = Producto::factory()->create([
            'precio_venta' => 50.00,
            'cantidad' => 10,
        ]);

        $response = $this->actingAs($user)->postJson($routeUrl, [
            'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
            'subtotal' => 50,
            'total' => 50,
            'pago_con' => 50,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'domicilio',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tipo_entrega']);
    }

    public function test_store_venta_request_accepts_uber_tipo_entrega(): void
    {
        $user = User::factory()->ventas()->create();
        $routeUrl = '/_test/ventas/validate-tipo-entrega-uber';

        Route::post($routeUrl, function (StoreVentaRequest $request) {
            return response()->json(['ok' => true]);
        })->middleware('web');

        $producto = Producto::factory()->create([
            'precio_venta' => 50.00,
            'cantidad' => 10,
        ]);

        $response = $this->actingAs($user)->postJson($routeUrl, [
            'items' => [['producto_id' => $producto->id, 'cantidad' => 1]],
            'subtotal' => 50,
            'total' => 50,
            'pago_con' => 50,
            'metodo_pago' => 'efectivo',
            'tipo_entrega' => 'uber',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
    }
}
