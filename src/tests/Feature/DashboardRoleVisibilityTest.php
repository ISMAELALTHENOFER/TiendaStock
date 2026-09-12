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

        $response->assertSee('react-root')->assertSee('Nueva Venta');
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

        $response->assertSee('react-root')->assertSee('Nueva Venta');
        $response->assertDontSee('Usuarios');
        $response->assertDontSee('Nuevo Producto');
        $response->assertDontSee('Nuevo Usuario');
    }

    public function test_control_stock_sees_productos_and_categorias_but_not_usuarios(): void
    {
        $user = User::factory()->controlStock()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('react-root')->assertSee('Nuevo Producto');
        $response->assertSee('Nuevo Producto');
        $response->assertDontSee('Usuarios');
        $response->assertDontSee('Nuevo Usuario');
    }

    public function test_user_without_role_sees_setup_fallback(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('react-root')->assertSee('Bienvenido');
        $response->assertSee('Bienvenido');
        $response->assertSee('configurada');
        $response->assertDontSee('Nuevo Producto');
    }

    public function test_blade_driver_restores_the_legacy_dashboard(): void
    {
        config(['frontend.driver' => 'blade']);
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertDontSee('react-root')->assertSee('Actividad Reciente');
    }

    public function test_dashboard_route_driver_restores_the_legacy_dashboard_without_global_rollback(): void
    {
        config([
            'frontend.driver' => 'react',
            'frontend.routes.dashboard' => 'blade',
        ]);
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertDontSee('react-root')->assertSee('Actividad Reciente');
    }

    public function test_tablet_sidebar_rail_binds_to_app_shell_open_state(): void
    {
        $appShell = file_get_contents(resource_path('js/react/layout/AppShell.jsx'));
        $sidebar = file_get_contents(resource_path('js/react/layout/Sidebar.jsx'));

        $this->assertStringContainsString('<Sidebar user={user} routes={routes} open={open}', $appShell);
        $this->assertStringContainsString("open ? 'md:w-72' : 'md:w-20'", $sidebar);
        $this->assertStringContainsString("open ? 'md:not-sr-only' : 'md:sr-only'", $sidebar);
    }

    public function test_modal_restores_body_overflow_after_closing(): void
    {
        $modal = file_get_contents(resource_path('js/react/components/ui/Modal.jsx'));

        $this->assertStringContainsString('const previousOverflow = document.body.style.overflow', $modal);
        $this->assertStringContainsString("document.body.style.overflow = 'hidden'", $modal);
        $this->assertStringContainsString('document.body.style.overflow = previousOverflow', $modal);
    }
}
