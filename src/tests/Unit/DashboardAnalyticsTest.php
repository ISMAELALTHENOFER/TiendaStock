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

    public function test_trailing_periods_use_local_half_open_boundaries(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-06-30 12:00:00', 'UTC'));
        foreach (['2026-06-01 02:59:59', '2026-06-01 03:00:00', '2026-06-24 02:59:59', '2026-06-24 03:00:00', '2026-07-01 02:59:59', '2026-07-01 03:00:00'] as $date) {
            Venta::factory()->create(['created_at' => $date, 'total' => 1]);
        }
        $analytics = app(DashboardAnalytics::class);
        $this->assertSame(2, array_sum(array_column($analytics->forWindow(7)['series']['sales_by_day'], 'count')));
        $this->assertSame(4, array_sum(array_column($analytics->forWindow(30)['series']['sales_by_day'], 'count')));
    }

    public function test_local_calendar_boundaries_and_historical_dst_grouping(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2008-10-20 12:00:00', 'UTC'));
        foreach ([
            ['2008-10-19 02:59:59', 10],
            ['2008-10-19 03:00:00', 20],
            ['2008-10-20 01:59:59', 30],
            ['2008-10-20 02:00:00', 40],
        ] as [$date, $total]) {
            Venta::factory()->create(['created_at' => $date, 'total' => $total]);
        }

        $result = app(DashboardAnalytics::class)->forWindow(1);
        $this->assertSame('2008-10-20', $result['window']['from']);
        $this->assertSame([['date' => '2008-10-20', 'total' => 40.0, 'count' => 1]], $result['series']['sales_by_day']);
        $result = app(DashboardAnalytics::class)->forWindow(2);
        $this->assertSame([['date' => '2008-10-19', 'total' => 50.0, 'count' => 2], ['date' => '2008-10-20', 'total' => 40.0, 'count' => 1]], $result['series']['sales_by_day']);
    }

    public function test_dashboard_uses_business_timezone_while_timestamps_remain_utc(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-03 01:00:00', 'UTC'));
        $sale = Venta::factory()->create(['created_at' => '2026-11-03 00:30:00', 'total' => 25]);

        $result = app(DashboardAnalytics::class)->forWindow(1);

        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('America/Argentina/Buenos_Aires', config('app.business_timezone'));
        $this->assertSame('2026-11-02', $result['series']['sales_by_day'][0]['date']);
        $this->assertSame(1, $result['summary']['sales_today']['count']);
        $this->assertSame('2026-11-03 00:30:00', $sale->getRawOriginal('created_at'));
    }

    public function test_category_uses_current_product_category(): void
    {
        $old = Categoria::factory()->create(['nombre' => 'Anterior']);
        $current = Categoria::factory()->create(['nombre' => 'Actual']);
        $product = Producto::factory()->create(['categoria_id' => $old->id]);
        $sale = Venta::factory()->create();
        VentaItem::factory()->create(['venta_id' => $sale->id, 'producto_id' => $product->id, 'cantidad' => 4]);
        $product->update(['categoria_id' => $current->id]);
        $category = app(DashboardAnalytics::class)->forWindow(7)['series']['sales_by_category'];
        $this->assertSame('Actual', $category[0]['category']);
        $this->assertSame(4, $category[0]['count']);
    }

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

    public function test_returns_real_sales_summaries_top_products_and_stock_alerts(): void
    {
        $categoria = Categoria::factory()->create();
        $lowStock = Producto::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Body T1', 'cantidad' => 1]);
        Producto::factory()->create(['categoria_id' => $categoria->id, 'nombre' => 'Calza T10', 'cantidad' => 0]);
        Producto::factory()->create(['categoria_id' => $categoria->id, 'cantidad' => 2]);
        $venta = Venta::factory()->create(['created_at' => now(), 'total' => 120]);
        VentaItem::factory()->create(['venta_id' => $venta->id, 'producto_id' => $lowStock->id, 'cantidad' => 3, 'subtotal' => 120]);

        $result = app(DashboardAnalytics::class)->forWindow(7);

        $this->assertSame(['total' => 120.0, 'count' => 1], $result['summary']['sales_today']);
        $this->assertSame(['total' => 120.0, 'count' => 1], $result['summary']['sales_month']);
        $this->assertSame('Body T1', $result['top_products'][0]['name']);
        $this->assertSame(3, $result['top_products'][0]['count']);
        $this->assertSame('out_of_stock', $result['low_stock'][0]['status']);
        $this->assertSame('low_stock', $result['low_stock'][1]['status']);
        $this->assertCount(2, $result['low_stock']);
    }
}
