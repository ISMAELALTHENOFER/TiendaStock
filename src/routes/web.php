<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('app', [
            'pageName' => 'Dashboard',
            'pageProps' => [
                'productosCount' => \App\Models\Producto::count(),
                'categoriasCount' => \App\Models\Categoria::count(),
                'productosActivos' => \App\Models\Producto::where('activo', true)->count(),
                'valorTotal' => \App\Models\Producto::sum('precio_venta'),
            ]
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Categorías
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::get('/categorias/create', [CategoriaController::class, 'create']);
    Route::post('/categorias', [CategoriaController::class, 'store']);
    Route::get('/categorias/{categoria}', [CategoriaController::class, 'show']);
    Route::get('/categorias/{categoria}/edit', [CategoriaController::class, 'edit']);
    Route::put('/categorias/{categoria}', [CategoriaController::class, 'update']);
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy']);
    
    // Productos
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/create', [ProductoController::class, 'create']);
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::get('/productos/{producto}', [ProductoController::class, 'show']);
    Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit']);
    Route::put('/productos/{producto}', [ProductoController::class, 'update']);
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy']);
});

require __DIR__ . '/auth.php';
