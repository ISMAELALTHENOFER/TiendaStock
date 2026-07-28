<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        if ($request->has('buscar') && strlen($request->buscar) >= 2) {
            $searchTerm = '%'.$request->buscar.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                    ->orWhere('color', 'like', $searchTerm)
                    ->orWhere('talle', 'like', $searchTerm)
                    ->orWhereHas('categoria', function ($q) use ($searchTerm) {
                        $q->where('nombre', 'like', $searchTerm);
                    });
            });
        }

        $productos = $query->orderBy('nombre')->paginate(10);
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias'));
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
