<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaSearchPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['frontend.driver' => 'blade']);
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_category_search_matches_name_and_description(): void
    {
        $nameMatch = Categoria::factory()->create(['nombre' => 'Abrigos', 'descripcion' => 'Invierno']);
        $descriptionMatch = Categoria::factory()->create(['nombre' => 'Accesorios', 'descripcion' => 'Abrigos tejidos']);
        Categoria::factory()->create(['nombre' => 'Calzado', 'descripcion' => 'Zapatos']);

        $response = $this->get(route('categorias.index', ['buscar' => 'Abrigos']));

        $response->assertOk()->assertViewHas('categorias', function ($categorias) use ($nameMatch, $descriptionMatch) {
            return $categorias->total() === 2
                && $categorias->getCollection()->pluck('id')->all() === [$nameMatch->id, $descriptionMatch->id];
        });
    }

    public function test_category_list_defaults_to_15_and_only_accepts_supported_page_sizes(): void
    {
        Categoria::factory()->count(55)->create();

        $default = $this->get(route('categorias.index'));
        $default->assertOk()->assertViewHas('categorias', fn ($categorias) => $categorias->perPage() === 15);

        foreach ([30, 50] as $size) {
            $this->get(route('categorias.index', ['per_page' => $size]))
                ->assertOk()->assertViewHas('categorias', fn ($categorias) => $categorias->perPage() === $size);
        }

        foreach (['10', '100', '15invalid', 'invalid'] as $size) {
            $this->get(route('categorias.index', ['per_page' => $size]))
                ->assertOk()->assertViewHas('categorias', fn ($categorias) => $categorias->perPage() === 15);
        }
    }

    public function test_category_search_and_page_size_are_preserved_in_pagination_links(): void
    {
        Categoria::factory()->count(35)->create(['descripcion' => 'Ropa de verano']);

        $this->get(route('categorias.index', ['buscar' => 'verano', 'per_page' => 15]))
            ->assertOk()
            ->assertSee(route('categorias.index', ['buscar' => 'verano', 'per_page' => 15, 'page' => 2]));
    }
}
