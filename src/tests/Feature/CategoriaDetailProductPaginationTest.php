<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaDetailProductPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_category_products_search_matches_name_color_size_and_description(): void
    {
        $categoria = Categoria::factory()->create();
        $matches = [
            Producto::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Abrigo Norte']),
            Producto::factory()->create(['categoria_id' => $categoria->id, 'color' => 'Azul especial']),
            Producto::factory()->create(['categoria_id' => $categoria->id, 'talle' => 'Talle especial']),
            Producto::factory()->create(['categoria_id' => $categoria->id, 'descripcion' => 'Tejido especial']),
        ];
        Producto::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Pantalón']);

        foreach (['Abrigo Norte', 'Azul especial', 'Talle especial', 'Tejido especial'] as $term) {
            $this->get(route('categorias.show', ['categoria' => $categoria, 'buscar' => $term]))
                ->assertOk()
                ->assertViewHas('productos', fn ($productos) => $productos->total() === 1);
        }

        $this->assertCount(4, $matches);
    }

    public function test_category_products_default_to_15_and_preserve_total_product_count(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(18)->create(['categoria_id' => $categoria->id]);

        $response = $this->get(route('categorias.show', $categoria))
            ->assertOk()
            ->assertViewHas('productos', fn ($productos) => $productos->perPage() === 15 && $productos->total() === 18);
        $props = $this->reactProps($response);
        $this->assertSame(18, $props['categoria']['productos_count']);
        $this->assertSame(18, $props['productosPage']['total']);
    }

    public function test_category_product_pagination_preserves_search_and_page_size(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(32)->create([
            'categoria_id' => $categoria->id,
            'descripcion' => 'Colección verano',
        ]);

        $response = $this->get(route('categorias.show', ['categoria' => $categoria, 'buscar' => 'verano', 'per_page' => 15]))->assertOk();

        $this->assertSame(
            route('categorias.show', ['categoria' => $categoria, 'buscar' => 'verano', 'per_page' => 15, 'page' => 2]),
            $this->reactProps($response)['productosPage']['next_page_url']
        );
    }

    public function test_category_products_accept_only_supported_page_sizes(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(55)->create(['categoria_id' => $categoria->id]);

        foreach ([[null, 15], [30, 30], [50, 50], [100, 15], ['15invalid', 15]] as [$requested, $expected]) {
            $query = $requested === null ? [] : ['per_page' => $requested];
            $this->get(route('categorias.show', ['categoria' => $categoria] + $query))
                ->assertOk()->assertViewHas('productos', fn ($productos) => $productos->perPage() === $expected);
        }
    }

    private function reactProps(\Illuminate\Testing\TestResponse $response): array
    {
        preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);

        return json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true, flags: JSON_THROW_ON_ERROR);
    }
}
