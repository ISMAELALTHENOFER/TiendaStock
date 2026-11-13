@php
    $routeName = request()->route()?->getName();
    foreach (['ventas', 'venta', 'categorias', 'categoria', 'producto', 'users', 'usuario', 'metrics'] as $pageVariable) {
        $$pageVariable ??= null;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TiendaStock') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#006948">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-tiendastock.png') }}">

    @include('layouts.theme')
    <!-- Scripts -->
    @vite('resources/css/app.css')

    <style>
        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
</head>

    <body class="font-sans antialiased bg-canvas">
        @include('react.app', ['props' => [
            'page' => $routeName,
            'status' => session('status'),
            'userProfile' => Auth::user()->only(['name', 'username', 'email']),
            'user' => [
                'name' => Auth::user()->name,
                'username' => Auth::user()->username,
                'role' => Auth::user()->role,
            ],
            'metrics' => $metrics ?? [],
            'lowStockThreshold' => config('inventory.low_stock_threshold', 1),
            'flash' => collect(['success', 'error', 'warning', 'info'])
                ->mapWithKeys(fn ($key) => [$key => session($key)])
                ->filter()
                ->all(),
            'routes' => [
                'profile' => route('profile.update'),
                'profilePage' => route('profile.edit'),
                'passwordUpdate' => route('password.update'),
                'logout' => route('logout'),
                'currentUserId' => Auth::id(),
                'canViewProductCost' => Auth::user()->role === 'ADMIN',
                'dashboard' => route('dashboard'),
                'dashboardAnalytics' => route('dashboard.analytics'),
                'ventas' => route('ventas.index'),
                'ventasPos' => route('ventas.pos'),
                'productos' => route('productos.index'),
                'productosData' => route('productos.data'),
                'productosSearch' => route('productos.search'),
                'productoDuplicateCheck' => route('productos.check-duplicate'),
                'productosCreate' => route('productos.create'),
                'productoEdit' => isset($producto) ? route('productos.edit', $producto) : null,
                'productoImage' => isset($producto) && $producto->imagen ? route('productos.imagen', $producto).($producto->imagenRecord ? '?v='.$producto->imagenRecord->sha1 : '') : null,
                'categorias' => route('categorias.index'),
                'categoriaEdit' => isset($categoria) ? route('categorias.edit', $categoria) : null,
                'categoriaDetail' => isset($categoria) ? route('categorias.show', $categoria) : null,
                'categoriasCreate' => route('categorias.create'),
                'categoriasInline' => route('categorias.inline'),
                'users' => route('admin.users.index'),
                'usersCreate' => route('admin.users.create'),
                'usersActivate' => route('admin.users.activate', ['user' => '__USER__']),
                'usersDeactivate' => route('admin.users.deactivate', ['user' => '__USER__']),
            ],
            'sales' => $ventas?->toArray(),
            'sale' => $venta?->toArray(),
            'categorias' => request()->routeIs('categorias.index', 'categorias.create', 'categorias.edit')
                ? $categorias->toArray() + ['summary' => request()->attributes->get('categoriaSummary', [])]
                : $categorias?->toArray(),
            'categoria' => request()->routeIs('categorias.show')
                ? request()->attributes->get('categoriaDetail')?->toArray()
                : (isset($categoria) ? $categoria->toArray() : null),
            'producto' => $producto?->toArray(),
            'productosPage' => request()->routeIs('categorias.show') ? request()->attributes->get('categoriaProductsPage')?->toArray() : null,
            'users' => $users?->toArray(),
            'usuario' => $usuario?->toArray(),
            'query' => request()->only(['desde', 'hasta', 'estado', 'buscar', 'per_page', 'inactivos']),
            'errors' => $errors->getBag('default')->toArray(),
            'passwordErrors' => $errors->getBag('updatePassword')->toArray(),
            'old' => old(),
        ]])

</body>

</html>
