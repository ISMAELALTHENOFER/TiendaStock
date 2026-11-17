<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_is_paginated_and_filters_run_on_the_full_active_set(): void
    {
        $category = Categoria::factory()->create();
        $otherCategory = Categoria::factory()->create();
        foreach (range(1, 16) as $index) {
            Producto::factory()->create([
                'nombre' => sprintf('Catalog item %02d', $index),
                'categoria_id' => $index === 16 ? $otherCategory->id : $category->id,
                'talle' => $index === 16 ? 'XL' : 'M',
                'color' => $index === 16 ? 'Blue' : 'Red',
                'cantidad' => 4,
                'precio_compra' => 3,
            ]);
        }

        $this->actingAs(User::factory()->admin()->create());
        $this->getJson(route('productos.data', ['per_page' => 15]))->assertOk()
            ->assertJsonCount(15, 'data')->assertJsonPath('total', 16)->assertJsonPath('last_page', 2)
            ->assertJsonPath('data.0.nombre', 'Catalog item 01')
            ->assertJsonPath('filters.talles', ['M', 'XL'])->assertJsonPath('filters.colores', ['Blue', 'Red']);
        $this->getJson(route('productos.data', ['per_page' => 15, 'page' => 2]))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.nombre', 'Catalog item 16');
        $this->getJson(route('productos.data', ['categoria_id' => $category->id, 'talle' => 'M', 'color' => 'Red', 'buscar' => 'Catalog item']))
            ->assertOk()->assertJsonPath('total', 15)->assertJsonCount(15, 'data');
        $this->getJson(route('productos.data', ['per_page' => 999]))->assertOk()->assertJsonPath('per_page', 15);
    }

    public function test_sellers_are_forbidden_and_control_stock_does_not_receive_cost_fields(): void
    {
        Producto::factory()->create(['nombre' => 'Safe catalog item', 'precio_compra' => 9, 'cantidad' => 3]);
        $this->actingAs(User::factory()->ventas()->create())->getJson(route('productos.data'))->assertForbidden();
        $this->actingAs(User::factory()->controlStock()->create())->getJson(route('productos.data'))
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.precio_compra')
            ->assertJsonMissingPath('data.0.ganancia');
    }
}
