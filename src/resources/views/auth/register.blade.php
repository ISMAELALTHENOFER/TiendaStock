<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-3">
            TiendaStock
        </h1>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Crear tu cuenta</h2>
        <p class="text-gray-600">Únete a nuestra plataforma de gestión de inventario</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre completo')" />
            <x-text-input
                id="name"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="Tu nombre"
                class="block mt-2 w-full" />
            @if ($errors->has('name'))
                @foreach ($errors->get('name') as $error)
                    <p class="text-sm text-red-600 mt-2">{{ $error }}</p>
                @endforeach
            @endif
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
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
                autocomplete="new-password"
                placeholder="••••••••"
                class="block mt-2 w-full" />
            @if ($errors->has('password'))
                @foreach ($errors->get('password') as $error)
                    <p class="text-sm text-red-600 mt-2">{{ $error }}</p>
                @endforeach
            @endif
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />
            <x-text-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="••••••••"
                class="block mt-2 w-full" />
            @if ($errors->has('password_confirmation'))
                @foreach ($errors->get('password_confirmation') as $error)
                    <p class="text-sm text-red-600 mt-2">{{ $error }}</p>
                @endforeach
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-6">
            <x-primary-button class="w-full justify-center py-3 text-base">
                {{ __('Registrarse') }}
            </x-primary-button>
        </div>

        <!-- Login Link -->
        <div class="text-center pt-4 border-t border-gray-100">
            <p class="text-sm text-gray-600 mt-4">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-700 font-semibold transition-colors duration-200">
                    Inicia sesión aquí
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
