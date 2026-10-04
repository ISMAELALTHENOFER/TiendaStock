@props(['showLogo' => true])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TiendaStock') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-dvh flex flex-col justify-center items-center px-3 py-6 sm:px-6 {{ isset($brand) ? 'login-page' : 'bg-stone-50' }}" style="padding-top: max(1.5rem, env(safe-area-inset-top)); padding-right: max(0.75rem, env(safe-area-inset-right)); padding-bottom: max(1.5rem, env(safe-area-inset-bottom)); padding-left: max(0.75rem, env(safe-area-inset-left));">
        <div class="w-full max-w-md {{ isset($brand) ? 'login-frame' : '' }}">
            @if ($showLogo && ! isset($brand))
                <div class="flex justify-center mb-6">
                    <x-application-logo class="w-14 h-14 text-brand-600" />
                </div>
            @endif

            {{ $brand ?? '' }}

            <!-- Card -->
            <div class="surface-panel overflow-hidden {{ isset($brand) ? 'login-card' : '' }}">
                <div class="px-5 py-6 sm:px-8 sm:py-8 {{ isset($brand) ? 'login-card__content' : '' }}">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-600 {{ isset($brand) ? 'login-footer' : '' }}">
                @if (isset($brand))
                    <p>&copy; {{ date('Y') }} TiendaStock <span aria-hidden="true">&bull;</span> Todos los derechos reservados <span aria-hidden="true">&bull;</span> Términos Privacidad</p>
                @else
                    <p>&copy; {{ date('Y') }} TiendaStock</p>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
