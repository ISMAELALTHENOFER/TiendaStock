<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCostVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_control_stock_cannot_view_cost_in_product_or_category_pages(): void
    {
        $category = Categoria::factory()->create();
        $product = Producto::factory()->create(['categoria_id' => $category->id, 'precio_compra' => 75]);
        $user = User::factory()->controlStock()->create();

        $this->actingAs($user)->get(route('productos.show', $product))->assertOk()
            ->assertDontSee('Costo')->assertDontSee('Ganancia por Unidad');
        $this->get(route('categorias.show', $category))->assertOk()
            ->assertDontSee('Precio de Compra')->assertDontSee('P. Compra');
        $this->get(route('productos.create'))->assertOk()->assertDontSee('name="precio_compra"');
        $this->get(route('productos.edit', $product))->assertOk()->assertDontSee('name="precio_compra"')
            ->assertDontSee('precio_compra', false);
    }

    public function test_admin_keeps_cost_visibility_and_control_stock_cannot_forge_cost_updates(): void
    {
        $category = Categoria::factory()->create();
        $product = Producto::factory()->create(['categoria_id' => $category->id, 'precio_compra' => 75]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('productos.show', $product))->assertOk()
            ->assertSee('Costo')->assertSee('Ganancia por Unidad');
        $this->get(route('productos.edit', $product))->assertOk()->assertSee('canViewProductCost&quot;:true', false);
        $this->actingAs(User::factory()->controlStock()->create())->put(route('productos.update', $product), [
            'nombre' => $product->nombre,
            'categoria_id' => $category->id,
            'precio_compra' => 1,
            'precio_venta' => $product->precio_venta,
            'cantidad' => $product->cantidad,
            'stock_original' => $product->cantidad,
        ])->assertRedirect(route('productos.index'));

        $this->assertDatabaseHas('productos', ['id' => $product->id, 'precio_compra' => 75]);

        $adminProduct = Producto::factory()->create(['categoria_id' => $category->id]);
        $this->actingAs($admin)->put(route('productos.update', $adminProduct), [
            'nombre' => $adminProduct->nombre,
            'categoria_id' => $category->id,
            'precio_compra' => 125,
            'precio_venta' => $adminProduct->precio_venta,
            'cantidad' => $adminProduct->cantidad,
            'stock_original' => $adminProduct->cantidad,
        ])->assertRedirect(route('productos.index'));
        $this->assertDatabaseHas('productos', ['id' => $adminProduct->id, 'precio_compra' => 125]);

        $created = $this->actingAs(User::factory()->controlStock()->create())->post(route('productos.store'), [
            'nombre' => 'New item', 'categoria_id' => $category->id,
            'precio_compra' => 700, 'precio_venta' => 900, 'cantidad' => 2,
        ]);
        $created->assertRedirect(route('productos.index'));
        $this->assertDatabaseHas('productos', ['nombre' => 'New item', 'precio_compra' => 0]);
    }
}
