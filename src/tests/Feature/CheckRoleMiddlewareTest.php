<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckRoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_routes(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    public function test_ventas_user_gets_403_from_admin_routes(): void
    {
        $user = User::factory()->ventas()->create();

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_control_stock_user_gets_403_from_admin_routes(): void
    {
        $user = User::factory()->controlStock()->create();

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_forbidden_module_shows_branded_page_linking_to_the_users_own_module(): void
    {
        $ventas = User::factory()->ventas()->create();
        $this->actingAs($ventas)->get('/productos')->assertForbidden()
            ->assertSee('Acceso restringido')
            ->assertSee('href="/ventas/pos"', false);

        $stock = User::factory()->controlStock()->create();
        $this->actingAs($stock)->get('/ventas')->assertForbidden()
            ->assertSee('Acceso restringido')
            ->assertSee('href="/productos"', false);
    }

    public function test_admin_user_can_access_admin_routes(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertOk();
    }

    public function test_admin_user_can_access_admin_create(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/admin/users/create');

        $response->assertOk();
    }

    public function test_ventas_user_cannot_access_admin_create(): void
    {
        $user = User::factory()->ventas()->create();

        $response = $this->actingAs($user)->get('/admin/users/create');

        $response->assertForbidden();
    }

    public function test_ventas_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->ventas()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_ventas_user_cannot_access_productos(): void
    {
        $user = User::factory()->ventas()->create();

        $response = $this->actingAs($user)->get('/productos');

        $response->assertForbidden();
    }

    public function test_control_stock_user_can_access_productos(): void
    {
        $user = User::factory()->controlStock()->create();

        $response = $this->actingAs($user)->get('/productos');

        $response->assertOk();
    }
}
