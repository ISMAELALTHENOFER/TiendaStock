<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TiendaStock') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <style>
        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
</head>

    <body class="font-sans antialiased bg-canvas">
    @php
        $routeName = request()->route()?->getName();
        $routeDriver = config("frontend.routes.{$routeName}");
        $useReact = config('frontend.driver', 'blade') === 'react'
            && ($routeDriver ?? 'react') === 'react';
    @endphp

    @if ($useReact && in_array($routeName, ['dashboard', 'ventas.index', 'ventas.pos', 'ventas.show', 'productos.index', 'productos.create', 'productos.edit', 'categorias.index', 'admin.users.index', 'admin.users.create', 'admin.users.edit'], true))
        @include('react.app', ['props' => [
            'page' => $routeName,
            'user' => [
                'name' => Auth::user()->name,
                'username' => Auth::user()->username,
                'role' => Auth::user()->role,
            ],
            'metrics' => [
                'productosCount' => $productosCount ?? 0,
                'categoriasCount' => $categoriasCount ?? 0,
                'productosActivos' => $productosActivos ?? 0,
                'valorTotal' => $valorTotal ?? 0,
            ],
            'flash' => collect(['success', 'error', 'warning', 'info'])
                ->mapWithKeys(fn ($key) => [$key => session($key)])
                ->filter()
                ->all(),
            'routes' => [
                'dashboard' => route('dashboard'),
                'ventas' => route('ventas.index'),
                'ventasPos' => route('ventas.pos'),
                'productos' => route('productos.index'),
                'productosCreate' => route('productos.create'),
                'categorias' => route('categorias.index'),
                'categoriasCreate' => route('categorias.create'),
                'users' => route('admin.users.index'),
                'usersCreate' => route('admin.users.create'),
            ],
            'sales' => $ventas?->toArray(),
            'sale' => $venta?->toArray(),
            'categorias' => $categorias?->toArray(),
            'producto' => $producto?->toArray(),
            'users' => $users?->toArray(),
            'usuario' => $usuario?->toArray(),
            'query' => request()->only(['desde', 'hasta', 'estado']),
            'errors' => $errors->getBag('default')->toArray(),
        ]])
    @else
    <div x-data="{ sidebarOpen: false }" x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen)" class="min-h-dvh flex bg-canvas md:h-dvh md:overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="min-w-0 flex-1 flex flex-col">
            <!-- Top Navigation -->
            @include('layouts.topbar')

            <!-- Page Content -->
            <main class="min-w-0 flex-1 overflow-visible bg-canvas/60 p-4 sm:p-6 lg:p-8 md:overflow-y-auto" style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right)); padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                @isset($header)
                <div class="mb-8 min-w-0">
                    {{ $header }}
                </div>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </div>
    @endif

    @stack('scripts')

    @if (! $useReact)
        <x-confirm-dialog />
        <x-flash-toast />
    @endif

    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
</body>

</html>
