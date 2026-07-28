<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_all_links(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('Productos');
        $response->assertSee('Categorías');
        $response->assertSee('Usuarios');
        $response->assertSee('Nuevo Producto');
        $response->assertSee('Nuevo Usuario');
    }

    public function test_ventas_sees_only_dashboard(): void
    {
        $user = User::factory()->ventas()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertDontSee('Usuarios');
        $response->assertDontSee('Nuevo Producto');
        $response->assertDontSee('Nuevo Usuario');
    }

    public function test_control_stock_sees_productos_and_categorias_but_not_usuarios(): void
    {
        $user = User::factory()->controlStock()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('Nuevo Producto');
        $response->assertDontSee('Usuarios');
        $response->assertDontSee('Nuevo Usuario');
    }

    public function test_user_without_role_sees_setup_fallback(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('Bienvenido');
        $response->assertSee('configurada');
        $response->assertDontSee('Nuevo Producto');
    }
}
