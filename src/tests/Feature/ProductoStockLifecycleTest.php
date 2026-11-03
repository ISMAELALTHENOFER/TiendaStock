<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductoStockLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_quantity_controls_status_on_create_and_update(): void
    {
        $producto = Producto::factory()->create(['cantidad' => 2, 'activo' => false]);
        $this->assertTrue($producto->fresh()->activo);
        $producto->update(['cantidad' => 0, 'activo' => true]);
        $this->assertFalse($producto->fresh()->activo);
        $producto->update(['cantidad' => 1]);
        $this->assertTrue($producto->fresh()->activo);
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
        $producto = Producto::factory()->create(['cantidad' => 7]);
        DB::table('productos')->where('id', $producto->id)->update(['activo' => false]);
        $migration = require database_path('migrations/2026_11_03_000001_activate_positive_stock_products.php');
        $migration->up();
        $migration->down();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'cantidad' => 7, 'activo' => true]);
    }
}
