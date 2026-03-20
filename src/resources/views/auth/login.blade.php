<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-3">
            TiendaStock
        </h1>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Bienvenido de vuelta</h2>
        <p class="text-gray-600">Accede a tu plataforma de gestión de inventario</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg flex items-center gap-3">
        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="tu@email.com"
                class="block mt-2 w-full" />
            @if ($errors->has('email'))
                @foreach ($errors->get('email') as $error)
                    <p class="text-sm text-red-600 mt-2">{{ $error }}</p>
                @endforeach
            @endif
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="••••••••"
                class="block mt-2 w-full" />
            @if ($errors->has('password'))
                @foreach ($errors->get('password') as $error)
                    <p class="text-sm text-red-600 mt-2">{{ $error }}</p>
                @endforeach
            @endif
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-2">
            <label for="remember_me" class="flex items-center cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-purple-300 text-purple-600 shadow-sm focus:ring-purple-500 w-4 h-4 cursor-pointer transition-colors duration-200"
                    name="remember">
                <span class="ml-2 text-sm text-gray-600 select-none">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
            <a class="text-sm text-purple-600 hover:text-purple-700 font-medium transition-colors duration-200" href="{{ route('password.request') }}">
                ¿Olvidaste tu contraseña?
            </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-6">
            <x-primary-button class="w-full justify-center py-3 text-base">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
                Iniciar sesión
            </x-primary-button>
        </div>

        <!-- Sign Up Link -->
        <div class="text-center pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-600 mt-4">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" class="text-purple-600 hover:text-purple-700 font-semibold transition-colors duration-200">
                    Regístrate ahora
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
