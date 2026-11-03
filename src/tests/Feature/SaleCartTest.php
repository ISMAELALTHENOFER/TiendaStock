<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_preserves_the_server_price_and_cannot_be_replayed(): void
    {
        $user = User::factory()->ventas()->create();
        $product = Producto::factory()->create(['precio_venta' => '25.00', 'cantidad' => 10, 'activo' => true]);
        $this->actingAs($user);

        $cart = $this->postJson('/ventas/cart', ['producto_id' => $product->id, 'precio_venta' => '25.00'])
            ->assertSuccessful()->json('id');
        $product->update(['precio_venta' => '30.00']);
        $payload = [
            'cart_id' => $cart,
            'items' => [['producto_id' => $product->id, 'cantidad' => 2, 'precio_unitario' => '0.01']],
            'subtotal' => '0.02', 'total' => '0.02', 'descuento' => '0.00', 'pago_con' => '50.00',
            'metodo_pago' => 'efectivo', 'tipo_entrega' => 'local',
        ];

        $this->postJson('/ventas', $payload)->assertSuccessful();
        $this->assertDatabaseHas('ventas', ['user_id' => $user->id, 'subtotal' => 50, 'total' => 50, 'cambio' => 0]);
        $this->assertDatabaseHas('venta_items', ['producto_id' => $product->id, 'precio_unitario' => 25, 'subtotal' => 50]);
        $this->postJson('/ventas', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 1);
        $this->assertSame(8, $product->fresh()->cantidad);
    }

    public function test_other_user_cannot_checkout_or_modify_a_cart(): void
    {
        $owner = User::factory()->ventas()->create();
        $other = User::factory()->ventas()->create();
        $product = Producto::factory()->create(['precio_venta' => '25.00', 'cantidad' => 10]);
        $cart = $this->actingAs($owner)->postJson('/ventas/cart', ['producto_id' => $product->id, 'precio_venta' => '25.00'])->json('id');
        $this->actingAs($other)->postJson('/ventas/cart', ['cart_id' => $cart, 'producto_id' => $product->id, 'precio_venta' => '25.00'])->assertUnprocessable();
        $this->postJson('/ventas', $this->payload($cart, $product))->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(10, $product->fresh()->cantidad);
        $this->actingAs($owner)->postJson('/ventas', $this->payload($cart, $product))->assertOk();
    }

    public function test_discount_limit_and_fractional_cents_are_enforced_without_consuming_cart(): void
    {
        $product = Producto::factory()->create(['precio_venta' => '25.00', 'cantidad' => 10]);
        $this->actingAs(User::factory()->ventas()->create());
        $cart = $this->postJson('/ventas/cart', ['producto_id' => $product->id, 'precio_venta' => '25.00'])->json('id');
        $payload = $this->payload($cart, $product);
        foreach ([['descuento' => '12.51'], ['descuento' => '0.001'], ['pago_con' => '25.001']] as $invalid) {
            $this->postJson('/ventas', array_replace($payload, $invalid))->assertUnprocessable();
            $this->assertDatabaseCount('ventas', 0);
            $this->assertSame(10, $product->fresh()->cantidad);
        }
        $this->postJson('/ventas', array_replace($payload, ['descuento' => '12.50', 'pago_con' => '13.00']))->assertOk();
        $this->assertDatabaseHas('ventas', ['subtotal' => 25, 'descuento' => 12.5, 'total' => 12.5, 'cambio' => 0.5]);
    }

    public function test_inactive_and_cumulative_stock_failures_roll_back_and_leave_cart_usable(): void
    {
        $product = Producto::factory()->create(['precio_venta' => '25.00', 'cantidad' => 10]);
        $this->actingAs(User::factory()->ventas()->create());
        $cart = $this->postJson('/ventas/cart', ['producto_id' => $product->id, 'precio_venta' => '25.00'])->json('id');
        $payload = $this->payload($cart, $product);
        $product->update(['cantidad' => 0]);
        $this->postJson('/ventas', $payload)->assertUnprocessable();
        $product->update(['cantidad' => 10]);
        $payload['items'] = [
            ['producto_id' => $product->id, 'cantidad' => 6],
            ['producto_id' => $product->id, 'cantidad' => 6],
        ];
        $this->postJson('/ventas', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_items', 0);
        $this->assertSame(10, $product->fresh()->cantidad);
        $this->postJson('/ventas', $this->payload($cart, $product))->assertOk();
    }

    public function test_cart_rejects_forged_or_stale_displayed_price_and_unauthorized_roles(): void
    {
        $product = Producto::factory()->create(['precio_venta' => '25.00', 'cantidad' => 10]);
        $this->actingAs(User::factory()->controlStock()->create())->postJson('/ventas/cart', ['producto_id' => $product->id, 'precio_venta' => '25.00'])->assertForbidden();
        $this->actingAs(User::factory()->ventas()->create())->postJson('/ventas/cart', ['producto_id' => $product->id, 'precio_venta' => '0.01'])->assertUnprocessable();
        $this->assertDatabaseCount('sale_carts', 0);
        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(10, $product->fresh()->cantidad);
    }

    public function test_multi_product_failure_preserves_all_stock_and_quote(): void
    {
        $first = Producto::factory()->create(['precio_venta' => '10.01', 'cantidad' => 5]);
        $second = Producto::factory()->create(['precio_venta' => '2.02', 'cantidad' => 3]);
        $this->actingAs(User::factory()->ventas()->create());
        $cart = $this->postJson('/ventas/cart', ['producto_id' => $first->id, 'precio_venta' => '10.01'])->json('id');
        $this->postJson('/ventas/cart', ['cart_id' => $cart, 'producto_id' => $second->id, 'precio_venta' => '2.02'])->assertOk();
        $payload = $this->payload($cart, $first);
        $payload['items'] = [['producto_id' => $first->id, 'cantidad' => 2], ['producto_id' => $second->id, 'cantidad' => 4]];
        $payload['pago_con'] = '30.00';
        $this->postJson('/ventas', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('venta_items', 0);
        $this->assertSame(5, $first->fresh()->cantidad);
        $this->assertSame(3, $second->fresh()->cantidad);
        $payload['items'][1]['cantidad'] = 3;
        $this->postJson('/ventas', $payload)->assertOk();
        $this->assertDatabaseHas('ventas', ['subtotal' => '26.08', 'total' => '26.08', 'cambio' => '3.92']);
    }

    public function test_removal_rejects_checkout_and_readding_captures_only_the_new_price(): void
    {
        $owner = User::factory()->ventas()->create();
        $first = Producto::factory()->create(['precio_venta' => '10.00', 'cantidad' => 5]);
        $second = Producto::factory()->create(['precio_venta' => '20.00', 'cantidad' => 5]);
        $this->actingAs($owner);
        $cart = $this->postJson('/ventas/cart', ['producto_id' => $first->id, 'precio_venta' => '10.00'])->assertOk()->json('id');
        $this->postJson('/ventas/cart', ['cart_id' => $cart, 'producto_id' => $second->id, 'precio_venta' => '20.00'])->assertOk();
        $this->deleteJson("/ventas/cart/$cart/items/{$first->id}")->assertOk();
        $this->postJson('/ventas', $this->payload($cart, $first))->assertUnprocessable()->assertJsonValidationErrors('items.0.producto_id');
        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(5, $first->fresh()->cantidad);
        $this->assertSame(5, $second->fresh()->cantidad);
        $first->update(['precio_venta' => '15.00']);
        $second->update(['precio_venta' => '30.00']);
        $this->postJson('/ventas/cart', ['cart_id' => $cart, 'producto_id' => $first->id, 'precio_venta' => '15.00'])->assertOk()->assertJsonPath('precio_venta', '15.00');
        $payload = $this->payload($cart, $first);
        $payload['items'][] = ['producto_id' => $second->id, 'cantidad' => 1];
        $payload['pago_con'] = '35.00';
        $this->postJson('/ventas', $payload)->assertOk();
        $this->assertDatabaseHas('ventas', ['user_id' => $owner->id, 'total' => 35]);
        $this->assertDatabaseHas('venta_items', ['producto_id' => $first->id, 'precio_unitario' => 15]);
        $this->assertDatabaseHas('venta_items', ['producto_id' => $second->id, 'precio_unitario' => 20]);
        $this->assertSame(4, $first->fresh()->cantidad);
        $this->assertSame(4, $second->fresh()->cantidad);
        $this->deleteJson("/ventas/cart/$cart/items/{$first->id}")->assertUnprocessable();
    }

    public function test_only_owner_with_sales_role_can_remove_or_clear_cart_items(): void
    {
        $owner = User::factory()->ventas()->create();
        $other = User::factory()->ventas()->create();
        $product = Producto::factory()->create(['precio_venta' => '25.00', 'cantidad' => 5]);
        $cart = $this->actingAs($owner)->postJson('/ventas/cart', ['producto_id' => $product->id, 'precio_venta' => '25.00'])->json('id');
        $this->actingAs($other)->deleteJson("/ventas/cart/$cart/items/{$product->id}")->assertUnprocessable();
        $this->deleteJson("/ventas/cart/$cart/items")->assertUnprocessable();
        $this->actingAs(User::factory()->controlStock()->create())->deleteJson("/ventas/cart/$cart/items/{$product->id}")->assertForbidden();
        $this->actingAs($owner)->postJson('/ventas', $this->payload($cart, $product))->assertOk();
        $this->assertDatabaseCount('ventas', 1);
        $this->assertSame(4, $product->fresh()->cantidad);
    }

    public function test_clearing_cart_invalidates_all_its_quoted_lines(): void
    {
        $product = Producto::factory()->create(['precio_venta' => '25.00', 'cantidad' => 5]);
        $this->actingAs(User::factory()->ventas()->create());
        $cart = $this->postJson('/ventas/cart', ['producto_id' => $product->id, 'precio_venta' => '25.00'])->json('id');
        $this->deleteJson("/ventas/cart/$cart/items")->assertOk();
        $this->postJson('/ventas', $this->payload($cart, $product))->assertUnprocessable();
        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame(5, $product->fresh()->cantidad);
    }

    private function payload(int $cart, Producto $product): array
    {
        return ['cart_id' => $cart, 'items' => [['producto_id' => $product->id, 'cantidad' => 1]],
            'pago_con' => '25.00', 'metodo_pago' => 'efectivo', 'tipo_entrega' => 'local'];
    }
}
