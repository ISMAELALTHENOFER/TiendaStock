<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryReadAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_can_search_public_catalog_fields_but_not_read_management_data(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create([
            'nombre' => 'Zapatillas', 'categoria_id' => $categoria->id,
            'precio_compra' => 999, 'cantidad' => 3,
        ]);
        $sales = User::factory()->ventas()->create();

        $this->actingAs($sales)->getJson(route('productos.search', ['q' => 'Zapatillas']))
            ->assertOk()->assertJsonPath('0.id', $producto->id)
            ->assertJsonPath('0.categoria.nombre', $categoria->nombre)
            ->assertJsonMissingPath('0.precio_compra')
            ->assertJsonMissingPath('0.imagen');
        $this->getJson(route('productos.data'))->assertForbidden();
        $this->get(route('productos.index'))->assertForbidden();
        $this->postJson(route('categorias.store'), ['nombre' => 'Denied'])->assertForbidden();
        $this->assertDatabaseMissing('categorias', ['nombre' => 'Denied']);
    }

    public function test_cost_is_visible_only_to_admin_while_inventory_managers_keep_access(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->create(['nombre' => 'Zapatillas', 'categoria_id' => $categoria->id, 'precio_compra' => 999]);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->getJson(route('productos.data'))
                ->assertOk()->assertJsonPath('data.0.precio_compra', '999.00');
        $controlStock = User::factory()->controlStock()->create();
        $this->actingAs($controlStock)->getJson(route('productos.data'))
            ->assertOk()->assertJsonMissingPath('data.0.precio_compra')->assertJsonMissingPath('data.0.ganancia');
        $this->get(route('categorias.index'))->assertOk();
    }

    public function test_guest_cannot_read_catalog_or_save(): void
    {
        $this->getJson(route('productos.search', ['q' => 'Zap']))->assertUnauthorized();
        $this->getJson(route('productos.data'))->assertUnauthorized();
        $this->postJson(route('productos.store'), [])->assertUnauthorized();
    }
}
