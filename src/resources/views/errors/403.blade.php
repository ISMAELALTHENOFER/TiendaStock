{{-- Static error template: Laravel renders it for every 403 (role gates, ownership checks). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme-system>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Acceso restringido · {{ config('app.name', 'TiendaStock') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @include('layouts.theme')
    @vite('resources/css/app.css')
</head>
<body class="bg-canvas font-sans antialiased">
    <main class="flex min-h-dvh items-center justify-center px-4 py-12">
        <div class="w-full max-w-md text-center">
            <img src="/images/tiendastock-mark.svg" alt="" class="mx-auto h-12 w-12">
            <p class="mt-8 text-sm font-semibold tracking-widest text-primary dark:text-primary-300">ERROR 403</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-ink">Acceso restringido</h1>
            <p class="mt-3 text-base text-muted">Tu usuario no tiene permiso para acceder a esta sección. Si lo necesitas, pide acceso a un administrador.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                @auth
                    <a href="{{ auth()->user()->defaultDestination() }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Ir a mi inicio</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-primary px-5 text-sm font-semibold text-white hover:bg-primary-700">Iniciar sesión</a>
                @endauth
                <button type="button" onclick="history.back()" class="inline-flex min-h-11 items-center justify-center rounded-control border border-border px-5 text-sm font-semibold text-ink hover:bg-surface-muted">Volver</button>
            </div>
        </div>
    </main>
</body>
</html>
