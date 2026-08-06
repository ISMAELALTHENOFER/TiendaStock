<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="page-title">
                    Usuarios del Sistema
                </h2>
                <p class="text-gray-600 text-sm mt-1">Gestiona todos los usuarios de la plataforma</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105 w-full sm:w-auto justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Usuario
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg shadow-md" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($users->count() > 0)
            <div class="bg-white rounded-xl shadow-md border border-sky-100 overflow-hidden">

                {{-- Desktop: tabla --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-sky-50 to-blush-50 border-b border-sky-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Rol</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($users as $user)
                            <tr class="hover:bg-sky-50/50 transition-colors duration-200">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $user->username }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($user->role === 'ADMIN')
                                    <span class="inline-flex items-center gap-1 bg-brand-100 text-brand-800 rounded-full px-3 py-1 text-xs font-bold">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        Administrador
                                    </span>
                                    @elseif($user->role === 'Ventas')
                                    <span class="inline-flex items-center gap-1 bg-sky-100 text-sky-700 rounded-full px-3 py-1 text-xs font-bold">Ventas</span>
                                    @elseif($user->role === 'Control Stock')
                                    <span class="inline-flex items-center gap-1 bg-brand-100 text-brand-800 rounded-full px-3 py-1 text-xs font-bold">Control Stock</span>
                                    @else
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 rounded-full px-3 py-1 text-xs font-bold">Sin rol</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        title="Editar usuario"
                                        class="inline-flex items-center gap-1 text-sky hover:text-sky-dark hover:bg-sky-50 p-2 rounded-lg transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Editar
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile: cards --}}
                <div class="md:hidden space-y-3 p-4">
                    @foreach($users as $user)
                    <div class="bg-white rounded-xl border border-sky-100 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500">@ {{ $user->username }}</p>
                            </div>
                            @if($user->role === 'ADMIN')
                            <span class="bg-brand-100 text-brand-800 rounded-full px-3 py-1 text-xs font-bold">Admin</span>
                            @elseif($user->role === 'Ventas')
                            <span class="bg-sky-100 text-sky-700 rounded-full px-3 py-1 text-xs font-bold">Ventas</span>
                            @elseif($user->role === 'Control Stock')
                            <span class="bg-brand-100 text-brand-800 rounded-full px-3 py-1 text-xs font-bold">Stock</span>
                            @else
                            <span class="bg-gray-100 text-gray-600 rounded-full px-3 py-1 text-xs font-bold">Sin rol</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-600 mb-3">
                            <span>{{ $user->email }}</span>
                        </div>
                        <a href="{{ route('admin.users.edit', $user) }}" class="block w-full text-center bg-sky-50 hover:bg-sky-100 text-sky-600 font-semibold py-3 rounded-lg transition-colors">
                            Editar usuario
                        </a>
                    </div>
                    @endforeach
                </div>

                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-md border border-sky-100 overflow-hidden">
                <div class="p-8 sm:p-12 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">No hay usuarios en el sistema</p>
                    <p class="text-gray-400 text-sm mt-2">Crea tu primer usuario para comenzar</p>
                    <a href="{{ route('admin.users.create') }}"
                        class="mt-6 inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crear Primer Usuario
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
