<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
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

    Route::middleware('role:ADMIN,Control Stock')->group(function () {
        Route::resource('/categorias', CategoriaController::class);
        Route::resource('/productos', ProductoController::class);
        Route::get('/productos/search', [ProductoController::class, 'search'])->name('productos.search');
    });

    Route::middleware(['role:ADMIN'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', AdminUserController::class)
                ->except(['show', 'destroy']);
        });
});

require __DIR__ . '/auth.php';
