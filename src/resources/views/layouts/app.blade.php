<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'TiendaStock') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar {
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            border: 2px solid #e2e8f0;
            padding: 12px;
            font-family: 'Figtree', sans-serif;
        }
        .flatpickr-monthSelect-month.selected {
            background: #d8a62a;
        }
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange,
        .flatpickr-day.selected.inRange {
            background: #d8a62a;
            border-color: #d8a62a;
        }
        .flatpickr-day.selected:hover,
        .flatpickr-day.startRange:hover,
        .flatpickr-day.endRange:hover {
            background: #b98213;
            border-color: #b98213;
        }
        .flatpickr-day.inRange {
            background: #fff4d6;
            border-color: #fff4d6;
            box-shadow: -5px 0 0 #fff4d6, 5px 0 0 #fff4d6;
        }
        .flatpickr-day.prevMonthDay:hover,
        .flatpickr-day.nextMonthDay:hover,
        .flatpickr-day:hover {
            background: #f7f4ee;
            border-color: #f7f4ee;
        }
        .flatpickr-months .flatpickr-month {
            border-radius: 12px;
        }
        .flatpickr-current-month .numInputWrapper span.arrowUp:after {
            border-bottom-color: #d8a62a;
        }
        .flatpickr-current-month .numInputWrapper span.arrowDown:after {
            border-top-color: #d8a62a;
        }
        .flatpickr-weekday {
            color: #64748b;
            font-weight: 600;
        }
        .flatpickr-day.today {
            border-color: #d8a62a;
        }
        .flatpickr-day.today:hover {
            background: #d8a62a;
            border-color: #d8a62a;
            color: #fff;
        }
        .flatpickr-monthDropdown-months {
            font-family: 'Figtree', sans-serif;
        }
        input.flatpickr-input {
            cursor: pointer !important;
        }
    </style>
    <style>
        body {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }
    </style>
</head>

    <body class="font-sans antialiased bg-stone-50">
    <div x-data="{ sidebarOpen: false }" x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen)" class="min-h-dvh flex bg-stone-50 md:h-dvh md:overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="min-w-0 flex-1 flex flex-col">
            <!-- Top Navigation -->
            @include('layouts.topbar')

            <!-- Page Content -->
            <main class="min-w-0 flex-1 overflow-visible bg-stone-50/60 p-4 sm:p-6 lg:p-8 md:overflow-y-auto" style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right)); padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                @isset($header)
                <div class="mb-8 min-w-0">
                    {{ $header }}
                </div>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')

    <x-confirm-dialog />
    <x-flash-toast />

    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
</body>

</html>
