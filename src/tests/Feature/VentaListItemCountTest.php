<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaListItemCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_html_hosts_count_items_without_loading_them_and_json_keeps_items(): void
    {
        $seller = User::factory()->ventas()->create();
        $sale = Venta::factory()->create(['user_id' => $seller->id]);
        VentaItem::factory()->count(2)->create(['venta_id' => $sale->id]);
        $empty = Venta::factory()->create(['user_id' => $seller->id]);
        $other = Venta::factory()->create();
        VentaItem::factory()->create(['venta_id' => $other->id]);

        $this->actingAs($seller);
        foreach (['react', 'blade'] as $driver) {
            config(['frontend.driver' => $driver]);
            $response = $this->get('/ventas');
            $response->assertOk()->assertViewHas('ventas', function ($ventas) use ($sale, $empty) {
                $sales = $ventas->getCollection()->keyBy('id');

                return $sales->count() === 2
                    && $sales[$sale->id]->items_count === 2
                    && $sales[$empty->id]->items_count === 0
                    && ! $sales[$sale->id]->relationLoaded('items')
                    && ! $sales[$empty->id]->relationLoaded('items');
            });
            if ($driver === 'blade') {
                $response->assertSee('2 items')->assertSee('0 items');
            } else {
                $response->assertSee('&quot;items_count&quot;:2', false);
            }
        }

        $json = $this->getJson('/ventas')->assertOk()->assertJsonCount(2, 'data');
        $sales = collect($json->json('data'))->keyBy('id');
        $this->assertCount(2, $sales[$sale->id]['items']);
        $this->assertCount(0, $sales[$empty->id]['items']);
        $this->assertArrayNotHasKey('items_count', $sales[$sale->id]);
    }
}
