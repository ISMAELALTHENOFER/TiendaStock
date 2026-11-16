<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaItem;
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
            ->assertSee('canViewProductCost&quot;:true', false)->assertSee('precio_compra&quot;:', false);
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
        // Control Stock enters the cost on create (user decision 2026-11-16) but never sees it afterwards.
        $this->assertDatabaseHas('productos', ['nombre' => 'New item', 'precio_compra' => 700]);
        $this->get(route('productos.show', Producto::where('nombre', 'New item')->first()))->assertOk()
            ->assertViewHas('producto', fn (Producto $p) => ! array_key_exists('precio_compra', $p->toArray()));
    }

    public function test_cost_is_hidden_from_any_product_serialization_by_default(): void
    {
        $product = Producto::factory()->create(['precio_compra' => 4321.98]);
        $item = VentaItem::factory()->create(['producto_id' => $product->id]);

        $this->assertArrayNotHasKey('precio_compra', $product->toArray());
        $this->assertArrayNotHasKey('precio_compra', Producto::find($product->id)->toArray());
        $this->assertArrayNotHasKey('precio_compra', $item->load('producto')->toArray()['producto']);
        $this->assertStringNotContainsString('4321.98', response()->json(Producto::all())->getContent());
        $this->assertArrayNotHasKey('ganancia', $product->toArray());
    }

    public function test_admin_product_detail_includes_unit_profit_and_control_stock_does_not(): void
    {
        $product = Producto::factory()->create(['precio_compra' => 75, 'precio_venta' => 100, 'cantidad' => 3]);

        $this->actingAs(User::factory()->admin()->create())->get("/productos/{$product->id}")->assertOk()
            ->assertViewHas('producto', fn (Producto $p) => (float) ($p->toArray()['ganancia'] ?? -1) === 25.0);
        $this->actingAs(User::factory()->controlStock()->create())->get("/productos/{$product->id}")->assertOk()
            ->assertViewHas('producto', fn (Producto $p) => ! array_key_exists('ganancia', $p->toArray()) && ! array_key_exists('precio_compra', $p->toArray()));
    }

    public function test_admin_sees_cost_in_category_and_sale_detail_and_ventas_does_not(): void
    {
        $category = Categoria::factory()->create();
        $product = Producto::factory()->create(['categoria_id' => $category->id, 'precio_compra' => 4321.98]);
        $seller = User::factory()->ventas()->create();
        $venta = Venta::factory()->create(['user_id' => $seller->id]);
        VentaItem::factory()->create(['venta_id' => $venta->id, 'producto_id' => $product->id]);

        $this->actingAs(User::factory()->admin()->create());
        $this->get(route('categorias.show', $category))->assertOk()->assertSee('4321.98', false);
        $this->get(route('ventas.show', $venta))->assertOk()->assertSee('4321.98', false);
        $this->getJson(route('productos.data'))->assertOk()->assertJsonPath('data.0.precio_compra', '4321.98');
        $this->actingAs($seller)->get(route('ventas.show', $venta))->assertOk()->assertDontSee('4321.98', false);
        $this->actingAs(User::factory()->controlStock()->create())->get(route('categorias.show', $category))
            ->assertOk()->assertDontSee('4321.98', false);
    }
}
