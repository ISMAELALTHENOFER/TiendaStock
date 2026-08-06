<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TiendaStock') }} | Gestión de inventario</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page font-sans antialiased">
    <div class="landing-frame">
        <header class="landing-header">
            <div class="landing-container landing-header__inner">
                <a class="landing-brand" href="{{ url('/') }}" aria-label="TiendaStock, inicio">
                    <x-application-logo class="landing-brand__logo" aria-hidden="true" focusable="false" />
                    <span>TiendaStock</span>
                </a>
                <nav class="landing-nav" aria-label="Navegación principal">
                    <a class="landing-nav__link" href="#funciones">Funciones</a>
                    <a class="landing-action landing-action--compact" href="{{ route('login') }}">Iniciar sesión</a>
                </nav>
            </div>
        </header>
        {{ $slot }}
        <footer class="landing-footer">
            <div class="landing-container landing-footer__inner">
                <p>&copy; {{ date('Y') }} TiendaStock. Gestión simple para tiendas que crecen.</p>
            </div>
        </footer>
    </div>
</body>
</html>
