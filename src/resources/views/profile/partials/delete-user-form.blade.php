<section class="space-y-6">
    <header class="flex items-start gap-3">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-red-100 text-red-700" aria-hidden="true">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 2.8 19a1.4 1.4 0 001.2 2.1h16a1.4 1.4 0 001.2-2.1L12 3z" /><path stroke-linecap="round" d="M12 9v4m0 3h.01" /></svg>
        </span>
        <div>
        <h2 class="text-xl font-bold text-ink">
            Eliminar cuenta
        </h2>
        <p class="mt-1 text-xs font-medium text-red-700">Zona de peligro</p>
        <p class="mt-3 text-sm leading-relaxed text-muted">
            Al eliminar su cuenta, todos sus datos y recursos se borrarán de forma permanente.
        </p>
        </div>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar cuenta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

             <h2 class="text-lg font-medium text-ink">
                 ¿Está seguro de que desea eliminar su cuenta?
            </h2>

             <p class="mt-1 text-sm text-muted">
                 Esta acción eliminará permanentemente todos los datos y recursos de su cuenta. Introduzca su contraseña para confirmar.
            </p>

            <div class="mt-6">
                 <x-input-label for="password" value="Contraseña" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                     class="mt-1 block w-full sm:w-3/4"
                     placeholder="Contraseña"
                     autocomplete="current-password"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <x-secondary-button class="w-full sm:w-auto" x-on:click="$dispatch('close')">
                     Cancelar
                </x-secondary-button>

                 <x-danger-button class="w-full sm:w-auto">
                     Eliminar cuenta
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
