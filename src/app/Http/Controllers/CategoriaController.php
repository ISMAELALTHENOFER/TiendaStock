<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        return view('categorias.index', $this->categoryGridData($request));
    }

    public function create(Request $request)
    {
        if (! $this->usesReact($request)) {
            return view('categorias.create');
        }

        return view('categorias.index', $this->categoryGridData($request));
    }

    public function edit(Request $request, Categoria $categoria)
    {
        if (! $this->usesReact($request)) {
            return view('categorias.edit', compact('categoria'));
        }

        return view('categorias.index', $this->categoryGridData($request, $categoria));
    }

    private function categoryGridData(Request $request, ?Categoria $categoria = null): array
    {
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->paginate(12);
        $categoriaSummary = [
            'total' => Categoria::count(),
            'productos' => Producto::count(),
            'vacias' => Categoria::doesntHave('productos')->count(),
        ];
        $request->attributes->set('categoriaSummary', $categoriaSummary);

        return compact('categorias', 'categoriaSummary', 'categoria');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias',
            'descripcion' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($request) {
            $categoria = Categoria::create($request->all());

            ActivityRecorder::recordAfterCommit(
                $request->user(), 'category.created', 'Category created', "Category {$categoria->nombre} was created.", $categoria
            );

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría creada correctamente.');
        });
    }

    public function show(Categoria $categoria)
    {
        $categoria->load('productos');

        return view('categorias.show', compact('categoria'));
    }

    private function usesReact(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return config('frontend.driver', 'blade') === 'react'
            && (config("frontend.routes.{$routeName}") ?? 'react') === 'react';
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,'.$categoria->id,
            'descripcion' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($request, $categoria) {
            $categoria->update($request->all());

            ActivityRecorder::recordAfterCommit(
                $request->user(), 'category.updated', 'Category updated', "Category {$categoria->nombre} was updated.", $categoria
            );

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría actualizada correctamente.');
        });
    }

    /**
     * Inline category creation from the product create/edit form.
     *
     * Returns JSON `{id, nombre}` (201) on success or 422 on validation error
     * so the Alpine modal can push the new option into the <select> without
     * leaving the page. Reusing the categorias.store redirect would break AJAX.
     */
    public function storeInline(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
            'nombre.max' => 'El nombre no puede superar los 255 caracteres.',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $categoria = Categoria::create($validated);

            ActivityRecorder::recordAfterCommit(
                $request->user(), 'category.created', 'Category created', "Category {$categoria->nombre} was created.", $categoria
            );

            return response()->json([
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
            ], 201);
        });
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar una categoría que tiene productos.');
        }

        return DB::transaction(function () use ($categoria) {
            $categoria->delete();

            ActivityRecorder::recordAfterCommit(
                request()->user(), 'category.deleted', 'Category deleted', "Category {$categoria->nombre} was deleted.", $categoria
            );

            return redirect()->route('categorias.index')
                ->with('success', 'Categoría eliminada correctamente.');
        });
    }
}
