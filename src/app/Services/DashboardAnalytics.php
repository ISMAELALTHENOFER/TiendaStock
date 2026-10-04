<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class DashboardAnalytics
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function forWindow(int $days): array
    {
        $to = now()->endOfDay();
        $from = now()->subDays($days - 1)->startOfDay();
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $salesByDay = Venta::query()
            ->where('estado', 'completada')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row): array => [
                'date' => $row->date,
                'total' => (float) $row->total,
                'count' => (int) $row->count,
            ])->values()->all();

        $salesByCategory = DB::table('venta_items')
            ->join('ventas', 'ventas.id', '=', 'venta_items.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_items.producto_id')
            ->join('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.created_at', [$from, $to])
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
            ->whereBetween('ventas.created_at', [$from, $to])
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
            ->whereBetween('created_at', [$start, $to])
            ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(*) as count')
            ->first();

        $todaySales = $salesSummary($today);
        $monthSales = $salesSummary($monthStart);

        return [
            'window' => [
                'days' => $days,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
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
