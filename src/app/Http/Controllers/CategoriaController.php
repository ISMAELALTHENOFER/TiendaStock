<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->paginate(12);
        return view('app', [
            'pageName' => 'Categorias/Index',
            'pageProps' => [
                'categorias' => $categorias->toArray(),
            ]
        ]);
    }

    public function create()
    {
        return view('app', [
            'pageName' => 'Categorias/Create',
            'pageProps' => []
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:categorias',
            'descripcion' => 'nullable|string|max:500',
        ]);

        Categoria::create($request->all());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function show(Categoria $categoria)
    {
        $categoria->load('productos');
        return view('app', [
            'pageName' => 'Categorias/Show',
            'pageProps' => [
                'categoria' => $categoria->toArray(),
            ]
        ]);
    }

    public function edit(Categoria $categoria)
    {
        return view('app', [
            'pageName' => 'Categorias/Edit',
            'pageProps' => [
                'categoria' => $categoria->toArray(),
            ]
        ]);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string|max:500',
        ]);

        $categoria->update($request->all());

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return redirect()->route('categorias.index')
                ->with('error', 'No se puede eliminar una categoría que tiene productos.');
        }

        $categoria->delete();
        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
