<section>
    <header>
        <h2 class="text-xl font-bold text-ink">
            Cambiar contraseña
        </h2>

        <p class="mt-1 text-sm text-muted">
            Use una contraseña larga y difícil de adivinar para proteger su cuenta.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Contraseña actual" class="!text-xs !text-ink" />
            <div class="login-input-wrap" x-data="{ showPassword: false }">
                <svg class="login-input-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="15" r="4" /><path stroke-linecap="round" stroke-linejoin="round" d="m11 12 7-7 3 3-2 2 2 2-3 3-2-2-3 3" /></svg>
                <x-text-input id="update_password_current_password" name="current_password" x-bind:type="showPassword ? 'text' : 'password'" class="mt-1 block h-11 w-full bg-surface-muted pl-11 pr-12 text-sm text-ink" autocomplete="current-password" required :aria-invalid="$errors->updatePassword->has('current_password') ? 'true' : 'false'" :aria-describedby="$errors->updatePassword->has('current_password') ? 'current-password-error' : null" placeholder="Escriba su contraseña actual" />
                <button type="button" class="login-password-toggle" x-on:click="showPassword = !showPassword" x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" x-bind:aria-pressed="showPassword.toString()">
                    <svg x-show="!showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 12a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                    <svg x-cloak x-show="showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587A2.25 2.25 0 0013.41 13.41M9.88 5.09A10.783 10.783 0 0112 4.875c6.25 0 9.75 7.125 9.75 7.125a17.108 17.108 0 01-3.168 4.223M6.228 6.228C3.74 7.91 2.25 12 2.25 12s3.5 6.75 9.75 6.75a10.83 10.83 0 004.347-.903" /></svg>
                </button>
            </div>
            <x-input-error id="current-password-error" :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nueva contraseña" class="!text-xs !text-ink" />
            <div class="login-input-wrap" x-data="{ showPassword: false }">
                <svg class="login-input-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-1.5 0h12a1.5 1.5 0 011.5 1.5v7.5A1.5 1.5 0 0118 21H6a1.5 1.5 0 01-1.5-1.5V12A1.5 1.5 0 016 10.5z" /></svg>
                <x-text-input id="update_password_password" name="password" x-bind:type="showPassword ? 'text' : 'password'" class="mt-1 block h-11 w-full bg-surface-muted pl-11 pr-12 text-sm text-ink" autocomplete="new-password" required :aria-invalid="$errors->updatePassword->has('password') ? 'true' : 'false'" :aria-describedby="$errors->updatePassword->has('password') ? 'new-password-error' : null" placeholder="Nueva contraseña" />
                <button type="button" class="login-password-toggle" x-on:click="showPassword = !showPassword" x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" x-bind:aria-pressed="showPassword.toString()">
                    <svg x-show="!showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 12a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                    <svg x-cloak x-show="showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587A2.25 2.25 0 0013.41 13.41M9.88 5.09A10.783 10.783 0 0112 4.875c6.25 0 9.75 7.125 9.75 7.125a17.108 17.108 0 01-3.168 4.223M6.228 6.228C3.74 7.91 2.25 12 2.25 12s3.5 6.75 9.75 6.75a10.83 10.83 0 004.347-.903" /></svg>
                </button>
            </div>
            <x-input-error id="new-password-error" :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar contraseña" class="!text-xs !text-ink" />
            <div class="login-input-wrap" x-data="{ showPassword: false }">
                <svg class="login-input-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-1.5 0h12a1.5 1.5 0 011.5 1.5v7.5A1.5 1.5 0 0118 21H6a1.5 1.5 0 01-1.5-1.5V12A1.5 1.5 0 016 10.5z" /><path stroke-linecap="round" d="M12 13v2m0 2h.01" /></svg>
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" x-bind:type="showPassword ? 'text' : 'password'" class="mt-1 block h-11 w-full bg-surface-muted pl-11 pr-12 text-sm text-ink" autocomplete="new-password" required :aria-invalid="$errors->updatePassword->has('password_confirmation') ? 'true' : 'false'" :aria-describedby="$errors->updatePassword->has('password_confirmation') ? 'password-confirmation-error' : null" placeholder="Repita la nueva contraseña" />
                <button type="button" class="login-password-toggle" x-on:click="showPassword = !showPassword" x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" x-bind:aria-pressed="showPassword.toString()">
                    <svg x-show="!showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-6.75 9.75-6.75S21.75 12 21.75 12 18.25 18.75 12 18.75 2.25 12 2.25 12z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 12a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                    <svg x-cloak x-show="showPassword" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587A2.25 2.25 0 0013.41 13.41M9.88 5.09A10.783 10.783 0 0112 4.875c6.25 0 9.75 7.125 9.75 7.125a17.108 17.108 0 01-3.168 4.223M6.228 6.228C3.74 7.91 2.25 12 2.25 12s3.5 6.75 9.75 6.75a10.83 10.83 0 004.347-.903" /></svg>
                </button>
            </div>
            <x-input-error id="password-confirmation-error" :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button>Guardar</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-muted"
                >Guardado.</p>
            @endif
        </div>
    </form>
</section>
