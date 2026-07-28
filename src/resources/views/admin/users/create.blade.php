<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-3xl text-gray-900 leading-tight">
                    Nuevo Usuario
                </h2>
                <p class="text-gray-600 text-sm mt-1">Crea un nuevo usuario en la plataforma</p>
            </div>
            <a href="{{ route('admin.users.index') }}"
                class="text-gray-600 hover:text-gray-900 font-medium">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-sky-400 to-sky-500 px-6 py-8">
                    <h3 class="text-white font-bold text-lg">Información del Usuario</h3>
                    <p class="text-white/80 text-sm mt-1">Completa los detalles para crear un nuevo usuario</p>
                </div>

                <form action="{{ route('admin.users.store') }}" method="POST" class="p-8">
                    @csrf

                    <div class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-900 mb-3">
                                Nombre completo
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                placeholder="Ej: Juan Pérez"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('name') border-red-500 @enderror">
                            @error('name')
                            <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-bold text-gray-900 mb-3">
                                Nombre de usuario
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}"
                                placeholder="Ej: juanperez"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('username') border-red-500 @enderror">
                            @error('username')
                            <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-900 mb-3">
                                Correo electrónico
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="Ej: juan@example.com"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('email') border-red-500 @enderror">
                            @error('email')
                            <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-bold text-gray-900 mb-3">
                                Contraseña
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="password" name="password"
                                placeholder="••••••••"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('password') border-red-500 @enderror">
                            @error('password')
                            <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-bold text-gray-900 mb-3">
                                Confirmar contraseña
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                placeholder="••••••••"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200">
                        </div>

                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-bold text-gray-900 mb-3">
                                Rol de usuario
                                <span class="text-red-500">*</span>
                            </label>
                            <select id="role" name="role"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-300 focus:border-brand-300 transition-all duration-200 @error('role') border-red-500 @enderror">
                                <option value="Control Stock" {{ old('role', 'Control Stock') === 'Control Stock' ? 'selected' : '' }}>Control Stock</option>
                                <option value="Ventas" {{ old('role') === 'Ventas' ? 'selected' : '' }}>Ventas</option>
                                <option value="ADMIN" {{ old('role') === 'ADMIN' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @error('role')
                            <p class="text-red-500 text-sm font-medium mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex gap-4 mt-8">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-brand-300 to-brand-dark hover:from-brand-dark hover:to-brand-300 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-md">
                            Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
