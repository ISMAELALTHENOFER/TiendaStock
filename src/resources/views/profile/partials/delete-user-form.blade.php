<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-ink">
            Eliminar cuenta
        </h2>

        <p class="mt-1 text-sm text-muted">
            Al eliminar su cuenta, todos sus datos y recursos se borrarán de forma permanente. Descargue antes la información que desee conservar.
        </p>
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
