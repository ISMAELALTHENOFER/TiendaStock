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

    public function test_inventory_managers_keep_complete_catalog_and_modification_access(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->create(['nombre' => 'Zapatillas', 'categoria_id' => $categoria->id, 'precio_compra' => 999]);

        foreach ([User::factory()->admin()->create(), User::factory()->controlStock()->create()] as $manager) {
            $this->actingAs($manager)->getJson(route('productos.data'))
                ->assertOk()->assertJsonPath('0.precio_compra', 999);
            $this->get(route('categorias.index'))->assertOk();
        }
    }

    public function test_guest_cannot_read_catalog_or_save(): void
    {
        $this->getJson(route('productos.search', ['q' => 'Zap']))->assertUnauthorized();
        $this->getJson(route('productos.data'))->assertUnauthorized();
        $this->postJson(route('productos.store'), [])->assertUnauthorized();
    }
}
