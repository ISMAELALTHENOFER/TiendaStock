<x-guest-layout :show-logo="false">
    <x-slot:brand>
        <div class="login-brand" aria-label="TiendaStock">
            <svg class="login-brand__icon" viewBox="0 0 40 40" fill="none" aria-hidden="true" focusable="false">
                <rect width="40" height="40" rx="12" fill="#006948" />
                <path d="M11 13h18v5H11zM13 18v11h14V18M18 23h4" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span>TiendaStock</span>
        </div>
    </x-slot:brand>

    <div class="login-heading">
        <h1>Bienvenido</h1>
        <p>Ingresa a TiendaStock para gestionar tu negocio.</p>
    </div>

    @if (session('status'))
        <div class="login-status" role="status">{{ session('status') }}</div>
    @endif

    @php($authenticationError = $errors->first('username') === __('auth.failed'))
    @if ($authenticationError)
        <div id="login-auth-error" class="login-alert" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 8v4m0 4h.01" /></svg>
            <span>{{ $errors->first('username') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="login-form" x-data="{ submitting: false, showPassword: false }" @submit="submitting = true">
        @csrf

        <div>
            <x-input-label for="username" :value="__('Usuario')" />
            <div class="login-input-wrap">
                <svg class="login-input-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.5-1.632z" /></svg>
                <x-text-input id="username" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" placeholder="Ingresa tu usuario" :aria-invalid="$errors->has('username') ? 'true' : 'false'" :aria-describedby="$authenticationError ? 'login-auth-error' : ($errors->has('username') ? 'username-error' : null)" class="mt-2 pl-11" />
            </div>
            @unless ($authenticationError)
                <x-input-error id="username-error" :messages="$errors->get('username')" />
            @endunless
        </div>

        <div>
            <x-input-label for="password" :value="__('Contraseña')" />
            <div class="login-input-wrap">
                <svg class="login-input-icon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-1.5 0h12a1.5 1.5 0 011.5 1.5v7.5A1.5 1.5 0 0118 21H6a1.5 1.5 0 01-1.5-1.5V12A1.5 1.5 0 016 10.5z" /></svg>
                <x-text-input id="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="Ingresa tu contraseña" :aria-invalid="$authenticationError || $errors->has('password') ? 'true' : 'false'" :aria-describedby="$errors->has('password') ? 'password-error' : ($authenticationError ? 'login-auth-error' : null)" class="mt-2 pl-11 pr-12" />
                <button type="button" class="login-password-toggle" x-on:click="showPassword = !showPassword" x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" x-bind:aria-pressed="showPassword.toString()">
                    <svg x-show="!showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 12a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                    <svg x-cloak x-show="showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587A2.25 2.25 0 0013.41 13.41M9.88 5.09A10.783 10.783 0 0112 4.875c6.25 0 9.75 7.125 9.75 7.125a17.108 17.108 0 01-3.168 4.223M6.228 6.228C3.74 7.91 2.25 12 2.25 12s3.5 6.75 9.75 6.75a10.83 10.83 0 004.347-.903" /></svg>
                </button>
            </div>
            <x-input-error id="password-error" :messages="$errors->get('password')" />
        </div>

        <div class="login-options">
            <label for="remember_me" class="flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="login-checkbox" name="remember">
                <span class="ml-2 text-sm text-gray-600 select-none">Recordarme</span>
            </label>

            @if (Route::has('password.request'))
                <a class="login-recovery-link" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
            @endif
        </div>

        <button type="submit" class="login-submit" x-bind:disabled="submitting" x-bind:aria-busy="submitting.toString()">
            <svg x-show="!submitting" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            <svg x-cloak x-show="submitting" class="login-spinner" aria-hidden="true" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".35" /><path d="M12 3a9 9 0 019 9" stroke="currentColor" stroke-width="3" stroke-linecap="round" /></svg>
            <span x-text="submitting ? 'Ingresando...' : 'Iniciar sesión'"></span>
        </button>
    </form>
</x-guest-layout>
