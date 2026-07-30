<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VentaController;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'productosCount' => Producto::count(),
        'categoriasCount' => Categoria::count(),
        'productosActivos' => Producto::where('activo', true)->count(),
        'valorTotal' => Producto::sum('precio_venta'),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/productos/search', [ProductoController::class, 'search'])->name('productos.search');
    Route::get('/productos/data', [ProductoController::class, 'data'])->name('productos.data');

    Route::middleware('role:ADMIN,Control Stock')->group(function () {
        Route::resource('/categorias', CategoriaController::class);
        Route::post('/categorias/inline', [CategoriaController::class, 'storeInline'])->name('categorias.inline');
        // Duplicate detection on the create flow: advises the user to edit an
        // existing product (active OR soft-disabled) instead of re-creating it.
        // Declared BEFORE the resource so the static path wins over the
        // `productos/{producto}` show wildcard.
        Route::get('/productos/check-duplicate', [ProductoController::class, 'checkDuplicate'])->name('productos.check-duplicate');
        Route::resource('/productos', ProductoController::class);
        // Reactivación: re-enable a soft-disabled product without re-creating it.
        Route::patch('/productos/{producto}/activate', [ProductoController::class, 'activate'])->name('productos.activate');
    });

    Route::middleware(['role:ADMIN'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('users', AdminUserController::class)
                ->except(['show', 'destroy']);
        });

    Route::middleware('role:ADMIN,Ventas')->group(function () {
        Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
        Route::get('/ventas/pos', [VentaController::class, 'create'])->name('ventas.pos');
        Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
        Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
        Route::post('/ventas/{venta}/cancel', [VentaController::class, 'cancel'])->name('ventas.cancel');
    });
});

require __DIR__.'/auth.php';
