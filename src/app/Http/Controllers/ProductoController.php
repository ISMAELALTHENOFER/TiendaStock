<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('categoria')->orderBy('nombre')->paginate(10);
        return view('app', [
            'pageName' => 'Productos/Index',
            'pageProps' => [
                'productos' => $productos->toArray(),
            ]
        ]);
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();
        return view('app', [
            'pageName' => 'Productos/Create',
            'pageProps' => [
                'categorias' => $categorias->toArray(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255',
            'categoria_id'  => 'required|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',
            'cantidad'      => 'required|integer|min:0',
            'talle'         => 'nullable|string|max:20',
            'color'         => 'nullable|string|max:50',
            'descripcion'   => 'nullable|string|max:500',
        ]);

        Producto::create($request->all());

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto)
    {
        $producto->load('categoria');
        return view('app', [
            'pageName' => 'Productos/Show',
            'pageProps' => [
                'producto' => $producto->toArray(),
            ]
        ]);
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('nombre')->get();
        return view('app', [
            'pageName' => 'Productos/Edit',
            'pageProps' => [
                'producto' => $producto->toArray(),
                'categorias' => $categorias->toArray(),
            ]
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre'        => 'required|string|max:255',
            'categoria_id'  => 'required|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',
            'cantidad'      => 'required|integer|min:0',
            'talle'         => 'nullable|string|max:20',
            'color'         => 'nullable|string|max:50',
            'descripcion'   => 'nullable|string|max:500',
        ]);

        $producto->update($request->all());

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
