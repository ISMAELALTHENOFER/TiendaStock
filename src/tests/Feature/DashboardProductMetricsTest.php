<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\View\Components\AppLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardProductMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_component_exposes_the_metrics_passed_to_it(): void
    {
        $metrics = ['productosActivos' => 3, 'productosAgotados' => 2];

        $this->assertSame($metrics, (new AppLayout(metrics: $metrics))->data()['metrics']);
        $this->assertSame(1, config('inventory.low_stock_threshold'));
    }

    public function test_react_receives_all_real_product_metrics_even_above_the_alert_list_limit(): void
    {
        config(['frontend.driver' => 'react', 'frontend.routes.dashboard' => 'react']);
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(10)->create(['categoria_id' => $categoria->id, 'cantidad' => 1, 'precio_venta' => 10.25]);
        Producto::factory()->create(['categoria_id' => $categoria->id, 'cantidad' => 2, 'precio_venta' => 7.50]);
        Producto::factory()->create(['categoria_id' => $categoria->id, 'cantidad' => 0, 'precio_venta' => 900]);
        $inactive = Producto::factory()->create(['categoria_id' => $categoria->id, 'cantidad' => 1, 'precio_venta' => 500]);
        // Bypass the stock lifecycle to exercise legacy inconsistent records.
        Producto::whereKey($inactive->id)->update(['activo' => false]);

        $response = $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertOk();
        preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
        $this->assertNotEmpty($matches[1] ?? null);
        $props = json_decode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals([
            'productosCount' => 13,
            'categoriasCount' => 1,
            'productosActivos' => 11,
            'productosAgotados' => 1,
            'productosBajoStock' => 10,
            'valorTotal' => 117.50,
        ], $props['metrics']);
    }

    public function test_empty_inventory_has_zero_metrics(): void
    {
        $response = $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertOk();
        $response->assertViewHas('productosCount', 0)->assertViewHas('productosActivos', 0)
            ->assertViewHas('productosAgotados', 0)->assertViewHas('productosBajoStock', 0)
            ->assertViewHas('valorTotal', 0);
    }

    public function test_blade_fallback_displays_real_product_metrics(): void
    {
        config(['frontend.driver' => 'blade']);
        Producto::factory()->create(['cantidad' => 1, 'precio_venta' => 12.50]);
        Producto::factory()->create(['cantidad' => 0]);

        $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertOk()
            ->assertDontSee('react-root')->assertSee('1 agotados · 1 con bajo stock')
            ->assertSee('Valor del inventario activo')->assertSee('$12.50');
    }

    public function test_react_dashboard_receives_categories_for_the_product_quick_action(): void
    {
        config(['frontend.driver' => 'react', 'frontend.routes.dashboard' => 'react']);
        $category = Categoria::factory()->create(['nombre' => 'Dashboard category']);
        $response = $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertOk();
        preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
        $props = json_decode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Dashboard category', $props['categorias'][0]['nombre']);

        $dashboard = file_get_contents(resource_path('js/react/dashboard.jsx'));
        $this->assertStringContainsString("label !== 'Nuevo Producto'", $dashboard);
        $this->assertStringContainsString('<ProductForm routes={routes} categorias={categorias} modal', $dashboard);
    }

    public function test_non_admins_cannot_access_either_driver(): void
    {
        foreach (['react', 'blade'] as $driver) {
            config(['frontend.driver' => $driver]);
            foreach (['Ventas', 'Control Stock'] as $role) {
                $this->actingAs(User::factory()->create(['role' => $role]))->get('/dashboard')->assertForbidden();
            }
        }
    }
}
