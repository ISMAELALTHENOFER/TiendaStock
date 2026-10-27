<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaListSearchPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_search_matches_sale_id_customer_and_processor(): void
    {
        $salesUser = User::factory()->ventas()->create(['name' => 'Sales Search User']);
        $otherUser = User::factory()->ventas()->create(['name' => 'Other Processor']);
        $byCustomer = Venta::factory()->create(['cliente_nombre' => 'Customer Search', 'user_id' => $otherUser->id]);
        $byProcessor = Venta::factory()->create(['cliente_nombre' => 'Another Customer', 'user_id' => $salesUser->id]);
        $byId = Venta::factory()->create(['cliente_nombre' => 'Third Customer', 'user_id' => $otherUser->id]);
        $this->actingAs($salesUser);

        foreach ([
            'Customer Search' => $byCustomer->id,
            'Sales Search User' => $byProcessor->id,
            (string) $byId->id => $byId->id,
        ] as $search => $expectedId) {
            $response = $this->get('/ventas?buscar='.urlencode($search));

            $response->assertOk()->assertViewHas('ventas', function ($ventas) use ($expectedId) {
                return $ventas->total() === 1 && $ventas->first()->id === $expectedId;
            });
        }
    }

    public function test_sales_page_size_defaults_to_15_and_accepts_only_15_30_or_50(): void
    {
        $user = User::factory()->ventas()->create();
        Venta::factory()->count(51)->create();
        $this->actingAs($user);

        foreach ([null => 15, '15' => 15, '30' => 30, '50' => 50, '100' => 15] as $perPage => $expected) {
            $url = $perPage === '' ? '/ventas' : '/ventas?per_page='.$perPage;
            $this->get($url)->assertOk()->assertViewHas('ventas', function ($ventas) use ($expected) {
                return $ventas->perPage() === $expected;
            });
        }
    }

    public function test_sales_search_and_all_existing_filters_are_retained_in_pagination_urls(): void
    {
        $user = User::factory()->ventas()->create();
        Venta::factory()->count(16)->create(['cliente_nombre' => 'Keep Me', 'estado' => 'completada']);
        $this->actingAs($user);

        $response = $this->get('/ventas?buscar=Keep%20Me&per_page=15&desde=2020-01-01&hasta=2030-01-01&estado=completada');

        $response->assertOk()->assertViewHas('ventas', function ($ventas) {
            $url = $ventas->url(2);

            return $ventas->total() === 16
                && str_contains($url, 'buscar=Keep%20Me')
                && str_contains($url, 'per_page=15')
                && str_contains($url, 'desde=2020-01-01')
                && str_contains($url, 'hasta=2030-01-01')
                && str_contains($url, 'estado=completada')
                && str_contains($url, 'page=2');
        });
    }
}
