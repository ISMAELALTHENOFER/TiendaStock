<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Venta;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DashboardAnalytics
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function forWindow(int $days): array
    {
        $localToday = CarbonImmutable::now('America/Argentina/Buenos_Aires')->startOfDay();
        $localFrom = $localToday->subDays($days - 1)->startOfDay();
        $from = $localFrom->utc();
        $to = $localToday->addDay()->startOfDay()->utc();
        $today = $localToday->utc();
        $monthStart = $localToday->startOfMonth()->startOfDay()->utc();

        $salesByDay = [];
        $sales = DB::table('ventas')
            ->where('estado', 'completada')
            ->where('created_at', '>=', $from)
            ->where('created_at', '<', $to)
            ->select(['created_at', 'total'])->cursor();
        foreach ($sales as $sale) {
            $date = CarbonImmutable::parse($sale->created_at, 'UTC')->setTimezone('America/Argentina/Buenos_Aires')->toDateString();
            $salesByDay[$date] ??= ['date' => $date, 'total' => 0.0, 'count' => 0];
            $salesByDay[$date]['total'] += (float) $sale->total;
            $salesByDay[$date]['count']++;
        }
        ksort($salesByDay);
        $salesByDay = array_values($salesByDay);

        $salesByCategory = DB::table('venta_items')
            ->join('ventas', 'ventas.id', '=', 'venta_items.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_items.producto_id')
            ->join('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->where('ventas.estado', 'completada')
            ->where('ventas.created_at', '>=', $from)
            ->where('ventas.created_at', '<', $to)
            ->selectRaw('categorias.nombre as category, SUM(venta_items.subtotal) as total, SUM(venta_items.cantidad) as count')
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderBy('categorias.nombre')
            ->get()
            ->map(fn ($row): array => [
                'category' => $row->category,
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ])->values()->all();

        $topProducts = DB::table('venta_items')
            ->join('ventas', 'ventas.id', '=', 'venta_items.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_items.producto_id')
            ->where('ventas.estado', 'completada')
            ->where('ventas.created_at', '>=', $from)
            ->where('ventas.created_at', '<', $to)
            ->selectRaw('productos.id, productos.nombre, SUM(venta_items.cantidad) as count, SUM(venta_items.subtotal) as total')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('count')
            ->orderBy('productos.nombre')
            ->limit(5)
            ->get()
            ->map(fn ($row): array => [
                'id' => $row->id,
                'name' => $row->nombre,
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ])->values()->all();

        $lowStock = Producto::query()
            ->where('cantidad', '<=', self::LOW_STOCK_THRESHOLD)
            ->orderBy('cantidad')
            ->orderBy('nombre')
            ->limit(8)
            ->get(['id', 'nombre', 'cantidad'])
            ->map(fn (Producto $product): array => [
                'id' => $product->id,
                'name' => $product->nombre,
                'quantity' => (int) $product->cantidad,
                'status' => (int) $product->cantidad === 0 ? 'out_of_stock' : 'low_stock',
            ])->values()->all();

        $salesSummary = fn ($start) => Venta::query()
            ->where('estado', 'completada')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $to)
            ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(*) as count')
            ->first();

        $todaySales = $salesSummary($today);
        $monthSales = $salesSummary($monthStart);

        return [
            'window' => [
                'days' => $days,
                'from' => $localFrom->toDateString(),
                'to' => $localToday->toDateString(),
            ],
            'series' => [
                'sales_by_day' => $salesByDay,
                'sales_by_category' => $salesByCategory,
            ],
            'summary' => [
                'sales_today' => ['total' => (float) $todaySales->total, 'count' => (int) $todaySales->count],
                'sales_month' => ['total' => (float) $monthSales->total, 'count' => (int) $monthSales->count],
            ],
            'top_products' => $topProducts,
            'low_stock' => $lowStock,
        ];
    }
}
