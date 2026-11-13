@props(['showLogo' => true])
@php
    $reactAuth = request()->routeIs('login')
        ? config('frontend.driver', 'blade') === 'react' && config('frontend.routes.login', 'react') === 'react'
        : request()->routeIs('password.confirm') && config('frontend.driver', 'blade') === 'react' && config('frontend.routes.password.confirm', 'react') === 'react';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if (request()->routeIs('login')) data-theme-system @endif>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TiendaStock') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @include('layouts.theme')
    <!-- Scripts -->
    @vite('resources/css/app.css')
    @if (! $reactAuth)
        @vite('resources/js/app.js')
    @endif
</head>

<body class="font-sans text-gray-900 antialiased">
    <div @class(['login-react-host' => $reactAuth && request()->routeIs('login'), 'min-h-dvh flex flex-col justify-center items-center px-3 py-6 sm:px-6' => ! ($reactAuth && request()->routeIs('login')), 'login-page' => isset($brand), 'bg-stone-50' => ! isset($brand) && ! ($reactAuth && request()->routeIs('login'))]) @if (! ($reactAuth && request()->routeIs('login'))) style="padding-top: max(1.5rem, env(safe-area-inset-top)); padding-right: max(0.75rem, env(safe-area-inset-right)); padding-bottom: max(1.5rem, env(safe-area-inset-bottom)); padding-left: max(0.75rem, env(safe-area-inset-left));" @endif>
        @if ($reactAuth && request()->routeIs('login'))
        <button type="button" data-theme-toggle aria-pressed="false" data-login-theme-toggle aria-label="Activar modo oscuro" class="theme-toggle login-react__theme">
            <svg class="login-react__theme-moon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z" /></svg>
            <svg class="login-react__theme-sun" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4" /><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" /></svg>
        </button>
        @else
        <button type="button" data-theme-toggle aria-pressed="false" class="theme-toggle mb-4">Modo oscuro</button>
        @endif
        @if ($reactAuth)
            {{ $slot }}
        @else
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
        @endif
    </div>
</body>

</html>
