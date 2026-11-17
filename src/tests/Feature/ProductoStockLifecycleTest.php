<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoStockLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_rule_is_explicit_not_a_hidden_save_hook(): void
    {
        $producto = Producto::factory()->create(['cantidad' => 5]);
        $producto->update(['cantidad' => 0]);
        $this->assertTrue($producto->fresh()->activo, 'A plain save must not silently rewrite activo.');

        $this->assertFalse($producto->syncActivoWithStock()->activo);
        $producto->cantidad = 3;
        $this->assertTrue($producto->syncActivoWithStock()->activo);
    }

    public function test_store_and_update_endpoints_derive_activo_from_stock(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $categoria = Categoria::factory()->create();
        $base = ['categoria_id' => $categoria->id, 'precio_compra' => 10, 'precio_venta' => 20];

        $this->post(route('productos.store'), $base + ['nombre' => 'Sin stock', 'cantidad' => 0])->assertRedirect();
        $this->post(route('productos.store'), $base + ['nombre' => 'Con stock', 'cantidad' => 3])->assertRedirect();
        $this->assertDatabaseHas('productos', ['nombre' => 'Sin stock', 'activo' => false]);
        $this->assertDatabaseHas('productos', ['nombre' => 'Con stock', 'activo' => true]);

        $producto = Producto::where('nombre', 'Con stock')->first();
        $this->put(route('productos.update', $producto), $base + ['nombre' => 'Con stock', 'cantidad' => 0, 'stock_original' => 3])->assertRedirect();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'cantidad' => 0, 'activo' => false]);
        $this->put(route('productos.update', $producto), $base + ['nombre' => 'Con stock', 'cantidad' => 2, 'stock_original' => 0])->assertRedirect();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'cantidad' => 2, 'activo' => true]);
    }

    public function test_activate_endpoint_reactivates_only_zero_stock_products(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $producto = Producto::factory()->create(['cantidad' => 0]);
        $this->assertFalse($producto->fresh()->activo);

        $this->patchJson(route('productos.activate', $producto), ['cantidad' => 4])->assertOk();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'cantidad' => 4, 'activo' => true]);
        $this->patchJson(route('productos.activate', $producto), ['cantidad' => 4])->assertStatus(409);
    }

    public function test_manual_deactivation_does_not_create_a_sale(): void
    {
        $producto = Producto::factory()->create(['cantidad' => 5]);
        $this->actingAs(User::factory()->admin()->create())->delete(route('productos.destroy', $producto), ['stock_original' => 5])->assertRedirect();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'cantidad' => 0, 'activo' => false]);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_stale_edit_and_deactivation_are_rejected_without_losing_stock_or_input(): void
    {
        $producto = Producto::factory()->create(['cantidad' => 5]);
        $this->actingAs(User::factory()->admin()->create());
        $producto->update(['cantidad' => 4]);
        $payload = [
            'nombre' => 'Nombre editado', 'categoria_id' => $producto->categoria_id,
            'precio_compra' => 10, 'precio_venta' => 20, 'cantidad' => 8, 'stock_original' => 5,
        ];
        $this->from(route('productos.edit', $producto))->put(route('productos.update', $producto), $payload)
            ->assertRedirect(route('productos.edit', $producto))->assertSessionHasErrors('stock_original')
            ->assertSessionHasInput('nombre', 'Nombre editado')->assertSessionHasInput('cantidad', 8)
            ->assertSessionHasInput('stock_original', 5);
        $this->putJson(route('productos.update', $producto), $payload)->assertUnprocessable()->assertJsonValidationErrors('stock_original');
        $this->deleteJson(route('productos.destroy', $producto), ['stock_original' => 5])->assertStatus(409);
        $this->deleteJson(route('productos.destroy', $producto))->assertUnprocessable();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'nombre' => $producto->nombre, 'cantidad' => 4, 'activo' => true]);
        $this->assertDatabaseCount('ventas', 0);
    }

    public function test_migration_activates_positive_stock_without_changing_quantity_or_reverting(): void
    {
        $producto = Producto::factory()->create(['cantidad' => 7, 'activo' => false]);
        $migration = require database_path('migrations/2026_11_03_000001_activate_positive_stock_products.php');
        $migration->up();
        $migration->down();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'cantidad' => 7, 'activo' => true]);
    }
}
