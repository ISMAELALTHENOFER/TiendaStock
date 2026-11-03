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

    public function test_non_admin_sidebar_hides_dashboard_in_blade_and_react(): void
    {
        foreach (['Ventas', 'Control Stock', null, 'invalid'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $html = view('layouts.sidebar')->render();
            $this->assertStringNotContainsString('href="'.route('dashboard').'"', $html);
            $this->assertStringNotContainsString('aria-label="Dashboard"', $html);
            $this->assertStringContainsString('href="'.route('profile.edit').'"', $html);
        }

        $sidebar = file_get_contents(resource_path('js/react/layout/Sidebar.jsx'));
        $this->assertStringContainsString("icon: LayoutDashboard, roles: ['ADMIN']", $sidebar);
        $this->assertStringContainsString("user.role === 'ADMIN' ? routes.dashboard : '/profile'", $sidebar);
    }

    public function test_blade_driver_restores_the_legacy_dashboard(): void
    {
        config(['frontend.driver' => 'blade']);
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertDontSee('react-root')->assertDontSee('Actividad Reciente')
            ->assertDontSee('Producto "Ejemplo"')->assertDontSee('Categoría "Electrónicos"')
            ->assertSee('Total Productos')->assertSee('Acciones Rápidas')->assertSee('Nueva Venta (POS)');
    }

    public function test_dashboard_route_driver_restores_the_legacy_dashboard_without_global_rollback(): void
    {
        config([
            'frontend.driver' => 'react',
            'frontend.routes.dashboard' => 'blade',
        ]);
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertDontSee('react-root')->assertDontSee('Actividad Reciente')
            ->assertSee('Total Productos')->assertSee('Acciones Rápidas');
    }

    public function test_desktop_sidebar_is_an_independent_viewport_height_region(): void
    {
        $appShell = file_get_contents(resource_path('js/react/layout/AppShell.jsx'));
        $sidebar = file_get_contents(resource_path('js/react/layout/Sidebar.jsx'));

        $this->assertStringContainsString('<Sidebar user={user} routes={routes} activePage={activePage} open={open}', $appShell);
        $this->assertStringContainsString('lg:pl-72', $appShell);
        $this->assertStringContainsString('fixed inset-y-0', $sidebar);
        $this->assertStringContainsString('h-dvh min-h-screen', $sidebar);
        $this->assertStringContainsString('flex-1 space-y-6 overflow-y-auto', $sidebar);
        $this->assertStringContainsString('mt-auto border-t', $sidebar);
    }

    public function test_modal_restores_body_overflow_after_closing(): void
    {
        $modal = file_get_contents(resource_path('js/react/components/ui/Modal.jsx'));

        $this->assertStringContainsString('const previousOverflow = document.body.style.overflow', $modal);
        $this->assertStringContainsString("document.body.style.overflow = 'hidden'", $modal);
        $this->assertStringContainsString('document.body.style.overflow = previousOverflow', $modal);
    }
}
