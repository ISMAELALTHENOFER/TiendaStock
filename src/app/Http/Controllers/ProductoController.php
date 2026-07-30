<?php

namespace App\Http\Controllers;

use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     *
     * El navegador NO debe cachear la respuesta: tras editar/desactivar/reactivar
     * un producto y volver al índice, la lista debe reflejar el cambio de
     * inmediato. Un Cache-Control: public, max-age=300 servía datos stalidados
     * por 5 minutos; degradamos a no-store para que cada navegación vuelva a
     * pedir el catálogo fresco (el costo es despreciable: un SELECT con JOIN).
     *
     * Default: solo productos activos (el POS y el buscador operan sobre el
     * inventario disponible). El flag ?inactivos=1 incluye los desactivados
     * para la vista administrativa.
     */
    public function data(Request $request)
    {
        $query = Producto::with('categoria')->orderBy('nombre');

        // Las dos vistas son complementarias y exhaustivas:
        //   Default (inventario disponible):  activo=true AND cantidad > 0
        //   ?inactivos=1 (no disponibles):     activo=false OR cantidad = 0
        // Así ningún producto queda invisible en ambos listados.
        if ($request->boolean('inactivos')) {
            $query->where(function ($q) {
                $q->where('activo', false)->orWhere('cantidad', 0);
            });
        } else {
            $query->where('activo', true)->where('cantidad', '>', 0);
        }

        $productos = $query->get();

        return response()->json($productos)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.create', compact('categorias'));
    }

    public function store(StoreProductoRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            // Stores under productos/ on the public disk; returns a generated
            // relative path (never the client filename — path-traversal safe).
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        Producto::create($data);

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

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $data = $request->validated();

        if ($request->hasFile('imagen')) {
            // Reemplaza la imagen anterior si existía.
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $data['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        // Sin archivo nuevo => NO se toca `imagen`; se preserva el path existente.

        $producto->update($data);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Soft-disable: marca activo=false en lugar de borrar el registro.
     * El producto permanece en la base de datos (preserva histórico de ventas)
     * y deja de aparecer en POS/búsqueda por defecto.
     */
    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto desactivado correctamente.');
    }

    /**
     * Re-activation: invierte el soft-disable marcando activo=true.
     * Es el espejo de destroy(): el registro vuelve al inventario disponible
     * sin necesidad de re-cargarlo.
     *
     * Refuses to activate a 0-stock product: the model `saving` rule would
     * silently force activo back to false anyway, but we surface a clear
     * user-facing error instead of a silent no-op.
     */
    public function activate(Producto $producto)
    {
        if ((int) $producto->cantidad === 0) {
            return redirect()->route('productos.index')
                ->with('error', 'No se puede activar un producto sin stock.');
        }

        $producto->update(['activo' => true]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto activado correctamente.');
    }

    /**
     * Duplicate detection for the create flow.
     *
     * The create form fires this on the nombre input's @blur event: if a
     * product with the exact same name already exists (active OR
     * soft-disabled — a duplicate could be a 0-stock inactive row), the user
     * is prompted to edit the existing record rather than re-create it.
     *
     * Match is EXACT and CASE-INSENSITIVE (not LIKE/fuzzy): the user said
     * "ya existe" — they mean the same product name. Fuzzy matching would
     * produce false positives and frustrate legitimate variant creation.
     *
     * Returns 200 with `{ exists: bool, id?, nombre? }`. The 200 status is
     * intentional on both branches — `exists:false` is a normal outcome,
     * not an error condition.
     */
    public function checkDuplicate(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'min:2'],
        ]);

        $producto = Producto::whereRaw('LOWER(nombre) = ?', [strtolower($validated['nombre'])])->first();

        if ($producto) {
            return response()->json([
                'exists' => true,
                'id' => $producto->id,
                'nombre' => $producto->nombre,
            ]);
        }

        return response()->json(['exists' => false]);
    }

    public function search(Request $request)
    {
        $productos = collect([]);

        if ($request->has('q') && strlen($request->q) >= 2) {
            $searchTerm = '%'.$request->q.'%';

            $productos = Producto::with('categoria')
                ->where('activo', true)
                ->where('cantidad', '>', 0)
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
