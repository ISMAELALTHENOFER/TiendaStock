<?php

namespace App\Http\Controllers;

use App\Http\Requests\Producto\StoreProductoRequest;
use App\Http\Requests\Producto\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoImagen;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

    public function data(Request $request)
    {
        $base = Producto::query();
        $this->applyInventoryScope($base, $request);

        $query = (clone $base)->with('categoria')->orderBy('nombre')->orderBy('id');
        $tokens = preg_split('/\s+/', trim($request->string('buscar')->toString()), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($tokens) && mb_strlen(implode('', $tokens)) >= 2) {
            foreach ($tokens as $token) {
                $like = '%'.$token.'%';
                $query->where(function ($matches) use ($like) {
                    $matches->where('nombre', 'like', $like)
                        ->orWhere('talle', 'like', $like)
                        ->orWhere('color', 'like', $like)
                        ->orWhereHas('categoria', fn ($category) => $category->where('nombre', 'like', $like));
                });
            }
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->integer('categoria_id'));
        }
        foreach (['talle', 'color'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->query($field));
            }
        }

        $perPage = in_array((string) $request->query('per_page'), ['15', '30', '50'], true)
            ? (int) $request->query('per_page')
            : 15;
        $productos = $query->paginate($perPage)->withQueryString();
        $imageHashes = ProductoImagen::whereIn('producto_id', $productos->getCollection()->pluck('id'))
            ->pluck('sha1', 'producto_id');
        $productos->getCollection()->each(function (Producto $producto) use ($request, $imageHashes): void {
            if ($request->user()?->role !== 'ADMIN') {
                $producto->makeHidden(['precio_compra', 'ganancia']);
            }
            if ($producto->imagen) {
                $url = route('productos.imagen', $producto);
                $producto->setAttribute('imagen', isset($imageHashes[$producto->id]) ? $url.'?v='.$imageHashes[$producto->id] : $url);
            }
        });

        $options = (clone $base)->select('talle', 'color')->distinct()->get();

        return response()->json($productos->toArray() + ['filters' => [
            'talles' => $options->pluck('talle')->filter()->unique()->sort()->values(),
            'colores' => $options->pluck('color')->filter()->unique()->sort()->values(),
        ]])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    private function applyInventoryScope($query, Request $request): void
    {
        if ($request->boolean('inactivos')) {
            $query->where(fn ($products) => $products->where('activo', false)->orWhere('cantidad', 0));
        } else {
            $query->where('activo', true)->where('cantidad', '>', 0);
        }
    }

    public function create()
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.create', compact('categorias'));
    }

    public function store(StoreProductoRequest $request)
    {
        $data = $request->validated();
        if ($request->user()?->role !== 'ADMIN') {
            $data['precio_compra'] = 0;
        }

        return DB::transaction(function () use ($data, $request) {
            $file = $request->file('imagen');
            unset($data['imagen']);
            $producto = Producto::create($data);
            if ($file) {
                $producto->update(['imagen' => 'database/'.$producto->id]);
                $this->persistImage($producto, $file->get());
            }

            ActivityRecorder::recordAfterCommit(
                $request->user(), 'product.created', 'Product created', "Product {$producto->nombre} was created.", $producto
            );

            if ($request->expectsJson()) {
                return response()->json(['saved' => true, 'redirect' => route('productos.index')]);
            }

            return redirect()->route('productos.index')
                ->with('success', 'Producto creado correctamente.');
        });
    }

    public function show(Producto $producto)
    {
        $producto->load(['imagenRecord:producto_id,sha1']);
        if (request()->user()?->role !== 'ADMIN') {
            $producto->makeHidden(['precio_compra', 'ganancia']);
        }
        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto)
    {
        $producto->load(['imagenRecord:producto_id,sha1']);
        if (request()->user()?->role !== 'ADMIN') {
            $producto->makeHidden(['precio_compra', 'ganancia']);
        }
        $categorias = Categoria::orderBy('nombre')->get();

        return view('productos.edit', compact('producto', 'categorias'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        $data = $request->validated();
        if ($request->user()?->role !== 'ADMIN') {
            unset($data['precio_compra']);
        }
        $stockOriginal = (int) $data['stock_original'];
        unset($data['stock_original']);

        $response = DB::transaction(function () use ($data, $request, $producto, $stockOriginal) {
            $producto = Producto::whereKey($producto->id)->lockForUpdate()->firstOrFail();
            if ((int) $producto->cantidad !== $stockOriginal) {
                throw ValidationException::withMessages(['stock_original' => 'El stock cambió desde que abriste el formulario. Revisa el inventario antes de guardar.']);
            }
            $file = $request->file('imagen');
            unset($data['imagen']);
            $producto->update($data);
            if ($file) {
                $producto->update(['imagen' => 'database/'.$producto->id]);
                $this->persistImage($producto, $file->get());
            }

            ActivityRecorder::recordAfterCommit(
                $request->user(), 'product.updated', 'Product updated', "Product {$producto->nombre} was updated.", $producto
            );

            if ($request->expectsJson()) {
                return response()->json(['saved' => true, 'redirect' => route('productos.index')]);
            }

            return redirect()->route('productos.index')
                ->with('success', 'Producto actualizado correctamente.');
        });

        return $response;
    }

    public function image(Producto $producto, Request $request)
    {
        $image = ProductoImagen::where('producto_id', $producto->id)->first();
        if ($image) {
            $response = response($image->bytes)->header('Content-Type', $image->mime)
                ->header('ETag', '"'.$image->sha1.'"')
                ->header('Cache-Control', 'private, max-age=31536000, immutable');
            $response->isNotModified($request);

            return $response;
        }

        if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            return Storage::disk('public')->response($producto->imagen)
                ->header('Cache-Control', 'private, max-age=3600');
        }

        abort(404);
    }

    private function persistImage(Producto $producto, string $bytes): void
    {
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        $dimensions = function_exists('getimagesizefromstring') ? @getimagesizefromstring($bytes) : false;
        ProductoImagen::updateOrCreate(['producto_id' => $producto->id], [
            'bytes' => $bytes,
            'mime' => $mime,
            'width' => $dimensions ? $dimensions[0] : null,
            'height' => $dimensions ? $dimensions[1] : null,
            'sha1' => sha1($bytes),
        ]);
    }

    /**
     * Soft-disable: marca activo=false en lugar de borrar el registro.
     * El producto permanece en la base de datos (preserva histórico de ventas)
     * y deja de aparecer en POS/búsqueda por defecto.
     */
    public function destroy(Request $request, Producto $producto)
    {
        $data = $request->validate(['stock_original' => ['required', 'integer', 'min:0']]);
        return DB::transaction(function () use ($producto, $data) {
            $producto = Producto::whereKey($producto->id)->lockForUpdate()->firstOrFail();
            if ((int) $producto->cantidad !== (int) $data['stock_original']) {
                if (request()->expectsJson()) {
                    return response()->json(['message' => 'El stock cambió. Actualiza el inventario antes de desactivar.'], 409);
                }
                return redirect()->route('productos.index')->with('error', 'El stock cambió. Actualiza el inventario antes de desactivar.');
            }
            $producto->update(['cantidad' => 0]);

            ActivityRecorder::recordAfterCommit(
                request()->user(), 'product.deactivated', 'Product deactivated', "Product {$producto->nombre} was deactivated.", $producto
            );

            return redirect()->route('productos.index')
                ->with('success', 'Producto desactivado correctamente.');
        });
    }

    public function activate(Request $request, Producto $producto)
    {
        $data = $request->validate(['cantidad' => ['required', 'integer', 'min:1', 'max:2147483647']]);

        return DB::transaction(function () use ($producto, $data) {
            $producto = Producto::whereKey($producto->id)->lockForUpdate()->firstOrFail();
            if ($producto->activo || $producto->cantidad > 0) {
                if (request()->expectsJson()) {
                    return response()->json(['message' => 'El producto ya tiene stock. Actualiza el inventario antes de continuar.'], 409);
                }
                return redirect()->route('productos.index')
                    ->with('error', 'El producto ya tiene stock. Actualiza el inventario antes de continuar.');
            }
            $producto->update(['cantidad' => $data['cantidad']]);

            ActivityRecorder::recordAfterCommit(
                request()->user(), 'product.activated', 'Product activated', "Product {$producto->nombre} was activated.", $producto
            );

            if (request()->expectsJson()) {
                return response()->json(['saved' => true]);
            }

            return redirect()->route('productos.index')
                ->with('success', 'Producto activado correctamente.');
        });
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

            $productos = Producto::select('id', 'nombre', 'categoria_id', 'precio_venta', 'cantidad', 'color', 'talle')
                ->with('categoria:id,nombre')
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
