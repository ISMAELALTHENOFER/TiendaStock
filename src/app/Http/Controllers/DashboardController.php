<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'productosCount' => Producto::count(),
            'categoriasCount' => Categoria::count(),
            'productosActivos' => Producto::where('activo', true)->count(),
            'productosAgotados' => Producto::where('cantidad', 0)->count(),
            'productosBajoStock' => Producto::where('activo', true)->where('cantidad', 1)->count(),
            'valorTotal' => Producto::where('activo', true)->selectRaw('COALESCE(SUM(precio_venta * cantidad), 0) as total')->value('total'),
        ]);
    }
}
