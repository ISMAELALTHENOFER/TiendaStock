<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Models\User;
use App\Models\VentaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoForceDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_deletes_inactive_product_without_sales(): void
    {
        $producto = Producto::factory()->create(['activo' => false, 'cantidad' => 0]);
        ProductoImagen::create(['producto_id' => $producto->id, 'bytes' => 'x', 'mime' => 'image/png', 'sha1' => sha1('x')]);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('productos.force-destroy', $producto))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Producto eliminado correctamente.');

        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
        $this->assertDatabaseMissing('producto_imagenes', ['producto_id' => $producto->id]);
    }

    public function test_non_admin_cannot_delete_product(): void
    {
        $producto = Producto::factory()->create(['activo' => false, 'cantidad' => 0]);

        $this->actingAs(User::factory()->controlStock()->create())
            ->delete(route('productos.force-destroy', $producto))
            ->assertForbidden();

        $this->assertDatabaseHas('productos', ['id' => $producto->id]);
    }

    public function test_active_product_cannot_be_deleted(): void
    {
        $producto = Producto::factory()->create(['activo' => true, 'cantidad' => 5]);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('productos.force-destroy', $producto))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('error', 'Solo se pueden eliminar productos inactivos.');

        $this->assertDatabaseHas('productos', ['id' => $producto->id]);
    }

    public function test_product_with_sales_cannot_be_deleted(): void
    {
        $producto = Producto::factory()->create(['activo' => false, 'cantidad' => 0]);
        VentaItem::factory()->create(['producto_id' => $producto->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('productos.force-destroy', $producto))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('error', 'No se puede eliminar un producto con ventas registradas.');

        $this->assertDatabaseHas('productos', ['id' => $producto->id]);
    }
}
