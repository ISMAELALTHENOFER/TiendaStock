<section>
    <header>
        <h2 class="text-xl font-bold text-ink">
            Información del perfil
        </h2>

        <p class="mt-1 text-sm text-muted">
            Actualice sus datos personales y su dirección de correo electrónico.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nombre" class="!text-xs !text-ink" />
            <div class="login-input-wrap">
                <svg class="login-input-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.5-1.632z" /></svg>
                <x-text-input id="name" name="name" type="text" class="mt-1 block h-11 w-full bg-surface-muted pl-11 pr-4 text-sm text-ink" :value="old('name', $user->name)" :aria-invalid="$errors->has('name') ? 'true' : 'false'" :aria-describedby="$errors->has('name') ? 'name-error' : null" required autofocus autocomplete="name" />
            </div>
            <x-input-error id="name-error" class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" value="Nombre de usuario" class="!text-xs !text-ink" />
            <div class="login-input-wrap">
                <svg class="login-input-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9" /><circle cx="12" cy="12" r="3.5" /><path stroke-linecap="round" d="M16 8v5a2 2 0 004 0" /></svg>
                <x-text-input id="username" name="username" type="text" class="mt-1 block h-11 w-full bg-surface-muted pl-11 pr-4 text-sm text-ink" :value="old('username', $user->username)" :aria-invalid="$errors->has('username') ? 'true' : 'false'" :aria-describedby="$errors->has('username') ? 'username-error' : null" required autocomplete="username" />
            </div>
            <x-input-error id="username-error" class="mt-2" :messages="$errors->get('username')" />
        </div>

        <div>
            <x-input-label for="email" value="Correo electrónico" class="!text-xs !text-ink" />
            <div class="login-input-wrap">
                <svg class="login-input-icon" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8 6 8-6" /></svg>
                <x-text-input id="email" name="email" type="email" class="mt-1 block h-11 w-full bg-surface-muted pl-11 pr-4 text-sm text-ink" :value="old('email', $user->email)" :aria-invalid="$errors->has('email') ? 'true' : 'false'" :aria-describedby="$errors->has('email') ? 'email-error' : null" required autocomplete="username" />
            </div>
            <x-input-error id="email-error" class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-ink">
                        Su dirección de correo electrónico no está verificada.

                        <button type="submit" form="send-verification" class="inline-flex min-h-11 items-center rounded-md text-sm text-primary underline hover:text-primary-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                            Reenviar correo de verificación
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-primary">
                            Se envió un nuevo enlace de verificación a su correo electrónico.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button>Guardar</x-primary-button>

            @if (session('status') === 'profile-updated')
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
