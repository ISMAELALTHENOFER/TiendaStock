<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_sellers_only_list_their_own_sales_in_html_and_json(): void
    {
        $seller = User::factory()->ventas()->create();
        $own = Venta::factory()->create(['user_id' => $seller->id, 'cliente_nombre' => 'Own Customer']);
        $other = Venta::factory()->create(['user_id' => User::factory()->ventas()->create()->id, 'cliente_nombre' => 'Foreign Customer']);

        $this->actingAs($seller)->get('/ventas')->assertOk()->assertSee('Own Customer')->assertDontSee('Foreign Customer');
        $this->getJson('/ventas')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $own->id)->assertJsonPath('total', 1);

        foreach ([$other->id, 'Foreign Customer', $other->user->name] as $search) {
            $url = '/ventas?buscar='.urlencode((string) $search);
            $this->get($url)->assertOk()->assertViewHas('ventas', fn ($ventas) => $ventas->total() === 0);
            $this->getJson($url)->assertOk()->assertJsonCount(0, 'data');
        }
        $this->getJson('/ventas?buscar=Own+Customer')->assertOk()->assertJsonPath('data.0.id', $own->id);
    }

    public function test_admin_can_list_and_search_all_sales(): void
    {
        $sales = Venta::factory()->count(2)->create();
        $this->actingAs(User::factory()->admin()->create())->getJson('/ventas')
            ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('total', 2);
        $this->getJson('/ventas?buscar='.$sales[0]->id)->assertOk()
            ->assertJsonFragment(['id' => $sales[0]->id]);
    }

    public function test_sale_detail_is_restricted_for_html_and_json_requests(): void
    {
        $seller = User::factory()->ventas()->create();
        $own = Venta::factory()->create(['user_id' => $seller->id]);
        $other = Venta::factory()->create(['user_id' => User::factory()->ventas()->create()->id]);

        $this->actingAs($seller)->get('/ventas/'.$own->id)->assertOk();
        $this->getJson('/ventas/'.$own->id)->assertOk();
        $this->get('/ventas/'.$other->id)->assertForbidden();
        $this->getJson('/ventas/'.$other->id)->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get('/ventas/'.$other->id)->assertOk();
        $this->getJson('/ventas/'.$other->id)->assertOk();
    }

    public function test_guests_and_stock_users_cannot_access_sales(): void
    {
        $sale = Venta::factory()->create();
        foreach (['/ventas', '/ventas/'.$sale->id] as $url) {
            $this->get($url)->assertRedirect('/login');
            $this->getJson($url)->assertUnauthorized();
        }
        $this->actingAs(User::factory()->controlStock()->create());
        foreach (['/ventas', '/ventas/'.$sale->id] as $url) {
            $this->get($url)->assertForbidden();
            $this->getJson($url)->assertForbidden();
        }
    }
}
