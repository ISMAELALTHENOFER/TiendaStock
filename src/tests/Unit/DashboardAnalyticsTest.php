<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Services\DashboardAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_aggregates_completed_sales_by_day_and_category(): void
    {
        $categoria = Categoria::factory()->create(['nombre' => 'Remeras']);
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $venta = Venta::factory()->create(['created_at' => now()->subDays(2), 'total' => 150]);
        VentaItem::factory()->create([
            'venta_id' => $venta->id,
            'producto_id' => $producto->id,
            'cantidad' => 3,
            'precio_unitario' => 50,
            'subtotal' => 150,
        ]);

        $result = app(DashboardAnalytics::class)->forWindow(30);

        $this->assertSame(30, $result['window']['days']);
        $this->assertSame(150.0, $result['series']['sales_by_day'][0]['total']);
        $this->assertSame(1, $result['series']['sales_by_day'][0]['count']);
        $this->assertSame('Remeras', $result['series']['sales_by_category'][0]['category']);
        $this->assertSame(150.0, $result['series']['sales_by_category'][0]['total']);
        $this->assertSame(3, $result['series']['sales_by_category'][0]['count']);
    }

    public function test_excludes_cancelled_sales_and_returns_empty_series_without_matches(): void
    {
        $categoria = Categoria::factory()->create();
        $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);
        $venta = Venta::factory()->anulada()->create(['created_at' => now()]);
        VentaItem::factory()->create(['venta_id' => $venta->id, 'producto_id' => $producto->id]);

        $result = app(DashboardAnalytics::class)->forWindow(1);

        $this->assertSame([], $result['series']['sales_by_day']);
        $this->assertSame([], $result['series']['sales_by_category']);
    }
}
