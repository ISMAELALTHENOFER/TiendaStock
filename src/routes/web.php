<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'productosCount' => \App\Models\Producto::count(),
        'categoriasCount' => \App\Models\Categoria::count(),
        'productosActivos' => \App\Models\Producto::where('activo', true)->count(),
        'valorTotal' => \App\Models\Producto::sum('precio_venta'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('/categorias', CategoriaController::class);
    Route::resource('/productos', ProductoController::class);
});

require __DIR__ . '/auth.php';
