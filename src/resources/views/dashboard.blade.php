<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">Visualiza el estado general de tu inventario</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Productos -->
            <div class="bg-white rounded-xl shadow-md border border-purple-100 p-6 hover:shadow-xl transition-all duration-300 hover:border-purple-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Productos</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $productosCount ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-purple-100 to-purple-50 rounded-lg">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Categorías -->
            <div class="bg-white rounded-xl shadow-md border border-pink-100 p-6 hover:shadow-xl transition-all duration-300 hover:border-pink-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Categorías</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $categoriasCount ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-pink-100 to-pink-50 rounded-lg">
                        <svg class="h-8 w-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Productos Activos -->
            <div class="bg-white rounded-xl shadow-md border border-green-100 p-6 hover:shadow-xl transition-all duration-300 hover:border-green-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Productos Activos</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $productosActivos ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-br from-green-100 to-green-50 rounded-lg">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Valor Total -->
            <div class="bg-gradient-to-br from-purple-600 to-pink-500 rounded-xl shadow-lg p-6 text-white hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-100 mb-1">Valor Total</p>
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

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-8">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="w-1 h-6 bg-gradient-to-b from-purple-600 to-pink-500 rounded mr-3"></div>
                    Actividad Reciente
                </h3>
            </div>
            <div class="space-y-4">
                <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-purple-50 to-transparent rounded-lg border-l-4 border-purple-500">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-gradient-to-r from-purple-600 to-purple-400 rounded-full flex items-center justify-center">
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
                <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-green-50 to-transparent rounded-lg border-l-4 border-green-500">
                    <div class="flex-shrink-0">
                        <div class="h-10 w-10 bg-gradient-to-r from-green-600 to-green-400 rounded-full flex items-center justify-center">
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
