<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardProductMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_react_receives_all_real_product_metrics_even_above_the_alert_list_limit(): void
    {
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
        $response->assertViewHas('metrics.productosCount', 0)->assertViewHas('metrics.productosActivos', 0)
            ->assertViewHas('metrics.productosAgotados', 0)->assertViewHas('metrics.productosBajoStock', 0)
            ->assertViewHas('metrics.valorTotal', 0);
        $this->assertSame(1, config('inventory.low_stock_threshold'));
    }

    public function test_react_dashboard_receives_categories_for_the_product_quick_action(): void
    {
        $category = Categoria::factory()->create(['nombre' => 'Dashboard category']);
        $response = $this->actingAs(User::factory()->admin()->create())->get('/dashboard')->assertOk();
        preg_match('/data-props="([^"]*)"/', $response->getContent(), $matches);
        $props = json_decode(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Dashboard category', $props['categorias'][0]['nombre']);

        $dashboard = file_get_contents(resource_path('js/react/dashboard.jsx'));
        $this->assertStringContainsString("label !== 'Nuevo Producto'", $dashboard);
        $this->assertStringContainsString('<ProductForm routes={routes} categorias={categorias} modal', $dashboard);
    }

    public function test_non_admins_cannot_access_the_dashboard(): void
    {
        foreach (['Ventas', 'Control Stock'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get('/dashboard')->assertForbidden();
        }
    }
}
