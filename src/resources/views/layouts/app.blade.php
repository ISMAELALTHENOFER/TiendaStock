<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            background: #a855f7;
        }
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange,
        .flatpickr-day.selected.inRange {
            background: #a855f7;
            border-color: #a855f7;
        }
        .flatpickr-day.selected:hover,
        .flatpickr-day.startRange:hover,
        .flatpickr-day.endRange:hover {
            background: #9333ea;
            border-color: #9333ea;
        }
        .flatpickr-day.inRange {
            background: #f3e8ff;
            border-color: #f3e8ff;
            box-shadow: -5px 0 0 #f3e8ff, 5px 0 0 #f3e8ff;
        }
        .flatpickr-day.prevMonthDay:hover,
        .flatpickr-day.nextMonthDay:hover,
        .flatpickr-day:hover {
            background: #f5f3ff;
            border-color: #f5f3ff;
        }
        .flatpickr-months .flatpickr-month {
            border-radius: 12px;
        }
        .flatpickr-current-month .numInputWrapper span.arrowUp:after {
            border-bottom-color: #a855f7;
        }
        .flatpickr-current-month .numInputWrapper span.arrowDown:after {
            border-top-color: #a855f7;
        }
        .flatpickr-weekday {
            color: #64748b;
            font-weight: 600;
        }
        .flatpickr-day.today {
            border-color: #a855f7;
        }
        .flatpickr-day.today:hover {
            background: #a855f7;
            border-color: #a855f7;
            color: #fff;
        }
        .flatpickr-monthDropdown-months {
            font-family: 'Figtree', sans-serif;
        }
        input.flatpickr-input {
            cursor: pointer !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-slate-50">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-gradient-to-br from-slate-50 to-sky-50">
        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            @include('layouts.topbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50/50 p-6">
                @isset($header)
                <div class="mb-8">
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
