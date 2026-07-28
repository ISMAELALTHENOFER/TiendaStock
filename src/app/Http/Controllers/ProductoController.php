<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        // El catálogo se carga vía fetch (ver data()) para sacarlo del HTML:
        // primero paint más rápido y cacheable por el navegador. Acá solo
        // pasamos las categorías (lista pequeña, necesaria de inmediato para
        // el select de filtros).
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.index', compact('categorias'));
    }

    /**
     * Devuelve el catálogo completo como JSON para filtrado client-side.
     * Es read-only y no depende del usuario => cacheable por el navegador,
     * así el segundo ingreso a /productos es instantáneo.
     */
    public function data()
    {
        $productos = Producto::with('categoria')->orderBy('nombre')->get();

        return response()->json($productos)
            ->header('Cache-Control', 'public, max-age=300');
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'cantidad' => 'required|integer|min:0',
            'talle' => 'required|string|max:20',
            'color' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:500',
        ]);

        Producto::create($request->all());

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto)
    {
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'cantidad' => 'required|integer|min:0',
            'talle' => 'required|string|max:20',
            'color' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:500',
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

    public function search(Request $request)
    {
        $productos = collect([]);

        if ($request->has('q') && strlen($request->q) >= 2) {
            $searchTerm = '%'.$request->q.'%';

            $productos = Producto::with('categoria')
                ->where(function ($query) use ($searchTerm) {
                    $query->where('nombre', 'like', $searchTerm)
                        ->orWhere('color', 'like', $searchTerm)
                        ->orWhere('talle', 'like', $searchTerm)
                        ->orWhereHas('categoria', function ($q) use ($searchTerm) {
                            $q->where('nombre', 'like', $searchTerm);
                        });
                })
                ->orderBy('nombre')
                ->limit(10)
                ->get();
        }

        return response()->json($productos);
    }
}
