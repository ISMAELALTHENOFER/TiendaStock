<x-app-layout>
    <div class="py-4 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-8">
            <header class="border-b border-border pb-4">
                <nav aria-label="Breadcrumb" class="mb-2 text-xs text-muted">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li>Cuenta</li>
                        <li aria-hidden="true">&gt;</li>
                        <li aria-current="page" class="font-semibold text-primary">Configuración personal</li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Mi perfil</h1>
                <p class="mt-1 max-w-2xl text-sm text-muted">
                    Administre sus credenciales corporativas, preferencias de seguridad y parámetros de identidad dentro del sistema de inventario TiendaStock.
                </p>
            </header>

            <div class="w-full lg:w-2/3 space-y-6">
                <div class="surface-panel p-4 sm:p-6 lg:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="surface-panel p-4 sm:p-6 lg:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="surface-panel border border-red-200 p-4 sm:p-6 lg:p-8">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
