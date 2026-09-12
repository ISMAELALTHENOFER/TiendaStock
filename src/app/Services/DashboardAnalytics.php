<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class DashboardAnalytics
{
    public function forWindow(int $days): array
    {
        $to = now()->endOfDay();
        $from = now()->subDays($days - 1)->startOfDay();

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
        ];
    }
}
