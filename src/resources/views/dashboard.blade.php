<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">Visualiza el estado general de tu inventario</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Productos -->
            <div class="bg-white rounded-xl shadow-sm border border-sky-100 p-4 sm:p-6 hover:shadow-md hover:border-sky-200 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Productos</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $productosCount ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-brand-50 rounded-lg">
                        <svg class="h-8 w-8 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Categorías -->
            <div class="bg-white rounded-xl shadow-sm border border-blush-100 p-4 sm:p-6 hover:shadow-md hover:border-blush-200 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Categorías</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $categoriasCount ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-blush-50 rounded-lg">
                        <svg class="h-8 w-8 text-blush-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Productos Activos -->
            <div class="bg-white rounded-xl shadow-sm border border-sky-100 p-4 sm:p-6 hover:shadow-md hover:border-sky-200 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Productos Activos</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $productosActivos ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-sky-50 rounded-lg">
                        <svg class="h-8 w-8 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Valor Total -->
            <div class="bg-gradient-to-br from-brand-300 to-sky-300 rounded-xl shadow-md p-4 sm:p-6 text-white hover:shadow-lg transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80 mb-1">Valor Total</p>
                        <p class="text-3xl font-bold text-white">${{ number_format($valorTotal ?? 0, 2) }}</p>
                    </div>
                    <div class="p-3 bg-white/20 rounded-lg backdrop-blur-sm">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Null-role fallback -->
        @if(!Auth::user()->role)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-8">
            <div class="mb-4">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="w-1 h-6 bg-brand-300 rounded mr-3"></div>
                    Bienvenido a TiendaStock
                </h3>
            </div>
            <div class="p-6 bg-sky-50 rounded-lg border border-sky-200">
                <p class="text-gray-700">
                    Tu cuenta está siendo configurada. Contactá al administrador del sistema para que te asigne un rol y puedas acceder a las funcionalidades disponibles.
                </p>
            </div>
        </div>
        @endif

        <!-- Role-Specific Quick Actions -->
        @if(Auth::user()->role)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="w-1 h-6 bg-brand-300 rounded mr-3"></div>
                    Acciones Rápidas
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @if(in_array(Auth::user()->role, ['ADMIN', 'Ventas']))
                <a href="{{ route('ventas.pos') }}" class="flex items-center gap-3 p-4 bg-sky-50/30 rounded-lg border-l-4 border-green-500 hover:shadow-md transition-all duration-200">
                    <div class="h-10 w-10 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Nueva Venta (POS)</p>
                        <p class="text-xs text-gray-500">Abrir el punto de venta</p>
                    </div>
                </a>
                @endif
                @if(in_array(Auth::user()->role, ['ADMIN', 'Control Stock']))
                <a href="{{ route('productos.create') }}" class="flex items-center gap-3 p-4 bg-sky-50/30 rounded-lg border-l-4 border-brand-300 hover:shadow-md transition-all duration-200">
                    <div class="h-10 w-10 bg-brand-300 rounded-full flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Nuevo Producto</p>
                        <p class="text-xs text-gray-500">Agregar un producto al inventario</p>
                    </div>
                </a>
                <a href="{{ route('categorias.create') }}" class="flex items-center gap-3 p-4 bg-sky-50/30 rounded-lg border-l-4 border-sky-400 hover:shadow-md transition-all duration-200">
                    <div class="h-10 w-10 bg-sky-500 rounded-full flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Nueva Categoría</p>
                        <p class="text-xs text-gray-500">Crear una categoría nueva</p>
                    </div>
                </a>
                @endif
                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 p-4 bg-sky-50/30 rounded-lg border-l-4 border-green-500 hover:shadow-md transition-all duration-200">
                    <div class="h-10 w-10 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Nuevo Usuario</p>
                        <p class="text-xs text-gray-500">Crear un usuario del sistema</p>
                    </div>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="w-1 h-6 bg-brand-300 rounded mr-3"></div>
                    Actividad Reciente
                </h3>
            </div>
            <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 bg-sky-50/30 rounded-lg border-l-4 border-brand-300">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-brand-300 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Nuevo producto agregado</p>
                        <p class="text-sm text-gray-500">Producto "Ejemplo" fue creado hace 2 horas</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-4 bg-sky-50/30 rounded-lg border-l-4 border-sky-400">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-sky-500 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">Categoría actualizada</p>
                        <p class="text-sm text-gray-500">Categoría "Electrónicos" fue modificada</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
