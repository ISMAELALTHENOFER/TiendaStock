<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_includes_imagen(): void
    {
        $producto = new Producto;

        $this->assertContains('imagen', $producto->getFillable());
    }

    public function test_fillable_includes_activo(): void
    {
        $producto = new Producto;

        $this->assertContains('activo', $producto->getFillable());
    }

    public function test_factory_sin_talle_state_produces_null_talle(): void
    {
        $producto = Producto::factory()->sinTalle()->create();

        $this->assertNull($producto->fresh()->talle);
    }

    public function test_factory_sin_color_state_produces_null_color(): void
    {
        $producto = Producto::factory()->sinColor()->create();

        $this->assertNull($producto->fresh()->color);
    }

    public function test_factory_con_imagen_state_sets_imagen_path(): void
    {
        $producto = Producto::factory()->conImagen()->create();

        $this->assertNotNull($producto->fresh()->imagen);
        $this->assertStringStartsWith('productos/', $producto->fresh()->imagen);
    }

    public function test_factory_default_imagen_is_null(): void
    {
        $producto = Producto::factory()->create();

        $this->assertNull($producto->fresh()->imagen);
    }

    public function test_activo_scope_filters_only_active_products(): void
    {
        $active = Producto::factory()->create(['activo' => true]);
        $inactive = Producto::factory()->create(['activo' => false]);

        $found = Producto::where('activo', true)->pluck('id');

        $this->assertContains($active->id, $found);
        $this->assertNotContains($inactive->id, $found);
    }

    public function test_activo_can_be_toggled_to_false(): void
    {
        $producto = Producto::factory()->create(['activo' => true]);

        $producto->update(['activo' => false]);

        $this->assertFalse($producto->fresh()->activo);
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'activo' => false]);
    }

    public function test_producto_belongs_to_categoria(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

        $this->assertInstanceOf(Categoria::class, $producto->categoria);
        $this->assertEquals($categoria->id, $producto->categoria->id);
    }

    public function test_ganancia_accessor_returns_difference(): void
    {
        $producto = Producto::factory()->make([
            'precio_compra' => 10.00,
            'precio_venta' => 25.50,
        ]);

        $this->assertEquals(15.50, $producto->ganancia);
    }
}
