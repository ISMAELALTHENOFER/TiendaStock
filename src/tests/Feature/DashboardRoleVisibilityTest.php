<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_accessible_category_comparison_bars(): void
    {
        $react = file_get_contents(resource_path('js/react/dashboard.jsx'));

        $this->assertStringContainsString('<CategoryChart data={data.series.sales_by_category} />', $react);
        $this->assertStringContainsString('category.count} unidades', $react);
        $this->assertStringContainsString('Math.max(0, category.count) / max * 100', $react);
        $this->assertStringContainsString('aria-hidden="true"', $react);
        $this->assertStringContainsString('text-[12px]', $react);
        $this->assertStringContainsString('minWidth: Math.max(data.length * 54, 320)', $react);
        $this->assertStringContainsString('[overflow-wrap:anywhere]', $react);
    }

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

    public function test_non_admin_sidebar_hides_dashboard(): void
    {
        $sidebar = file_get_contents(resource_path('js/react/layout/Sidebar.jsx'));
        $this->assertStringContainsString("icon: LayoutDashboard, roles: ['ADMIN']", $sidebar);
        $this->assertStringContainsString("user.role === 'ADMIN' ? routes.dashboard : routes.profilePage", $sidebar);
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
