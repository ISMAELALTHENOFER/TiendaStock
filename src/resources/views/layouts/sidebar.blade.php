<div class="relative">
    <!-- Sidebar for desktop -->
    <div class="hidden overflow-hidden transition-[width] duration-200 lg:sticky lg:top-0 lg:flex lg:h-dvh lg:flex-shrink-0" :class="sidebarCollapsed ? 'w-[4.5rem]' : 'w-72'">
        <div class="flex w-full flex-col">
            <div class="flex h-dvh flex-1 flex-col bg-sidebar">
            <div class="flex min-h-0 flex-1 flex-col">
                    <div class="flex min-h-16 flex-shrink-0 items-center justify-between border-b border-white/10 px-5" :class="sidebarCollapsed ? 'justify-center px-0' : ''">
                        <a href="{{ route('dashboard') }}" aria-label="TiendaStock, Dashboard" :class="sidebarCollapsed ? 'lg:sr-only' : ''" class="flex min-h-11 items-center gap-3 text-white">
                            <img src="{{ asset('images/tiendastock-mark.svg') }}" alt="" class="h-7 w-7 object-contain">
                            <span class="text-lg font-bold">TiendaStock</span>
                        </a>
                        <button type="button" @click="sidebarCollapsed = !sidebarCollapsed" :aria-label="sidebarCollapsed ? 'Expandir barra lateral' : 'Contraer barra lateral'" :aria-expanded="!sidebarCollapsed" aria-controls="desktop-sidebar-navigation" class="hidden h-11 w-11 shrink-0 place-items-center rounded-control text-white/75 transition-colors hover:bg-white/10 hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white lg:grid" title="Contraer o expandir barra lateral">
                            <svg x-show="!sidebarCollapsed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6" /></svg>
                            <svg x-show="sidebarCollapsed" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6" /></svg>
                        </button>
                    </div>
                    <nav id="desktop-sidebar-navigation" class="flex-1 space-y-6 overflow-y-auto px-3 py-6" :class="sidebarCollapsed ? 'lg:px-2' : ''">
                        <div><p class="px-3 text-xs font-semibold uppercase tracking-widest text-white/60" :class="sidebarCollapsed ? 'lg:sr-only' : ''">Workspace</p><div class="mt-2 space-y-1">
                        <a href="{{ route('dashboard') }}" aria-label="Dashboard" title="Dashboard" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="9" rx="1" /><rect x="14" y="3" width="7" height="5" rx="1" /><rect x="14" y="12" width="7" height="9" rx="1" /><rect x="3" y="16" width="7" height="5" rx="1" />
                            </svg>
                            <span :class="sidebarCollapsed ? 'lg:sr-only' : ''">Dashboard</span>
                        </a>
                        </div></div>
                        @if(in_array(Auth::user()->role, ['ADMIN', 'Ventas', 'Control Stock']))
                        <div><p class="px-3 text-xs font-semibold uppercase tracking-widest text-white/60" :class="sidebarCollapsed ? 'lg:sr-only' : ''">Operación</p><div class="mt-2 space-y-1">
                        @if(in_array(Auth::user()->role, ['ADMIN', 'Ventas']))
                        <a href="{{ route('ventas.index') }}" aria-label="Ventas" title="Ventas" aria-current="{{ request()->routeIs('ventas.*') ? 'page' : 'false' }}" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('ventas.*') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="8" cy="21" r="1" /><circle cx="19" cy="21" r="1" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.05 2.05h2l2.66 12.42a2 2 0 002 1.58h9.78a2 2 0 001.95-1.57l1.65-7.43H5.12" />
                            </svg>
                            <span :class="sidebarCollapsed ? 'lg:sr-only' : ''">Ventas</span>
                        </a>
                        @endif
                        @if(in_array(Auth::user()->role, ['ADMIN', 'Control Stock']))
                        <a href="{{ route('productos.index') }}" aria-label="Productos" title="Productos" aria-current="{{ request()->routeIs('productos.*') ? 'page' : 'false' }}" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('productos.*') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M2.97 12.92A2 2 0 002 14.63v3.24a2 2 0 00.97 1.71l3 1.8a2 2 0 002.06 0L12 19v-5.5l-5-3-4.03 2.42Z" /><path d="m7 16.5-4.74-2.85m4.74 2.85 5-3M7 16.5v5.17M12 13.5V19l3.97 2.38a2 2 0 002.06 0l3-1.8a2 2 0 00.97-1.71v-3.24a2 2 0 00-.97-1.71L17 10.5l-5 3Zm5 3-5-3m5 3 4.74-2.85M17 16.5v5.17M7.97 4.42A2 2 0 007 6.13v4.37l5 3 5-3V6.13a2 2 0 00-.97-1.71l-3-1.8a2 2 0 00-2.06 0l-3 1.8ZM12 8 7.26 5.15M12 8l4.74-2.85M12 13.5V8" />
                            </svg>
                            <span :class="sidebarCollapsed ? 'lg:sr-only' : ''">Productos</span>
                        </a>
                        <a href="{{ route('categorias.index') }}" aria-label="Categorías" title="Categorías" aria-current="{{ request()->routeIs('categorias.*') ? 'page' : 'false' }}" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('categorias.*') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2ZM8 10v4m4-4v2m4-2v6" />
                            </svg>
                            <span :class="sidebarCollapsed ? 'lg:sr-only' : ''">Categorías</span>
                        </a>
                        @endif
                        </div></div>@endif
                        @if(Auth::user()->isAdmin())
                        <div><p class="px-3 text-xs font-semibold uppercase tracking-widest text-white/60" :class="sidebarCollapsed ? 'lg:sr-only' : ''">Administración</p><div class="mt-2 space-y-1">
                        <a href="{{ route('admin.users.index') }}" aria-label="Usuarios" title="Usuarios" aria-current="{{ request()->routeIs('admin.*') ? 'page' : 'false' }}" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('admin.*') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M16 3.128a4 4 0 010 7.744M22 21v-2a4 4 0 00-3-3.87" /><circle cx="9" cy="7" r="4" />
                            </svg>
                            <span :class="sidebarCollapsed ? 'lg:sr-only' : ''">Usuarios</span>
                        </a>
                        </div></div>@endif
                    </nav>
                </div>
                <a href="{{ route('profile.edit') }}" title="Configuración de cuenta" aria-label="Configuración de cuenta para {{ Auth::user()->name }} ({{ '@' . Auth::user()->username }})" aria-current="{{ request()->routeIs('profile.edit') ? 'page' : 'false' }}" :class="sidebarCollapsed ? 'lg:justify-center lg:gap-1 lg:px-0' : ''" class="mt-auto border-t flex min-h-11 flex-shrink-0 items-center gap-3 rounded-control px-4 py-4 text-white transition-colors {{ request()->routeIs('profile.edit') ? 'bg-primary-container' : 'bg-white/10 hover:bg-white/15' }}">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-primary text-white" :class="sidebarCollapsed ? 'lg:h-8 lg:w-8' : ''" aria-hidden="true"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="5" /><path d="M20 21a8 8 0 0 0-16 0" /></svg></span>
                    <span class="min-w-0 flex-1" :class="sidebarCollapsed ? 'lg:sr-only' : ''"><span class="block truncate text-sm font-semibold">{{ Auth::user()->name }}</span><span class="block truncate text-xs text-white/60">{{ '@' . Auth::user()->username }}</span></span>
                    <svg class="h-4 w-4 shrink-0 text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.671 4.136a2.34 2.34 0 014.659 0 2.34 2.34 0 003.319 1.915 2.34 2.34 0 012.33 4.033 2.34 2.34 0 000 3.831 2.34 2.34 0 01-2.33 4.033 2.34 2.34 0 00-3.319 1.915 2.34 2.34 0 01-4.659 0 2.34 2.34 0 00-3.32-1.915 2.34 2.34 0 01-2.33-4.033 2.34 2.34 0 000-3.831A2.34 2.34 0 016.35 6.051a2.34 2.34 0 003.319-1.915" /><circle cx="12" cy="12" r="3" /></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile sidebar -->
    <div id="mobile-sidebar" x-show="sidebarOpen" x-init="$watch('sidebarOpen', open => {
        if (open) {
            $el.__returnFocus = document.activeElement;
            $nextTick(() => [...$el.querySelectorAll('button:not([disabled]), a, input:not([type=\'hidden\']), select, textarea, [tabindex]:not([tabindex=\'-1\'])')].find(element => element.getClientRects().length)?.focus());
        } else if ($el.__returnFocus?.isConnected) {
            $nextTick(() => $el.__returnFocus.focus());
            $el.__returnFocus = null;
        }
    })" @keydown.tab.prevent="(() => {
        const focusables = [...$el.querySelectorAll('button:not([disabled]), a, input:not([type=\'hidden\']), select, textarea, [tabindex]:not([tabindex=\'-1\'])')].filter(element => element.getClientRects().length);
        if (!focusables.length) return;
        const currentIndex = focusables.indexOf(document.activeElement);
        const nextIndex = $event.shiftKey
            ? (currentIndex <= 0 ? focusables.length - 1 : currentIndex - 1)
            : (currentIndex + 1) % focusables.length;
        focusables[nextIndex].focus();
    })()" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @keydown.escape.window="sidebarOpen = false" class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Navegación principal">
        <div class="absolute inset-0 bg-sidebar/75" @click="sidebarOpen = false" aria-hidden="true"></div>
        <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-200 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex h-full w-full max-w-xs flex-1 flex-col bg-sidebar">
            <div class="absolute top-2 right-2 z-10">
                <button type="button" x-show="sidebarOpen" @click="sidebarOpen = false" class="ml-1 flex items-center justify-center w-11 h-11 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                        <span class="sr-only">Cerrar navegación</span>
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
                    <div class="flex min-h-0 flex-1 flex-col overflow-y-auto" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <div class="flex min-h-16 flex-shrink-0 items-center gap-3 border-b border-white/10 px-5">
                    <img src="{{ asset('images/tiendastock-mark.svg') }}" alt="" class="h-7 w-7 object-contain">
                    <span class="text-lg font-bold text-white">TiendaStock</span>
                </div>
                <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-6">
                    <div><p class="px-3 text-xs font-semibold uppercase tracking-widest text-white/60">Workspace</p><div class="mt-2 space-y-1">
                    <a href="{{ route('dashboard') }}" aria-label="Dashboard" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="9" rx="1" /><rect x="14" y="3" width="7" height="5" rx="1" /><rect x="14" y="12" width="7" height="9" rx="1" /><rect x="3" y="16" width="7" height="5" rx="1" />
                        </svg>
                        Dashboard
                    </a>
                    </div></div>
                    @if(in_array(Auth::user()->role, ['ADMIN', 'Ventas', 'Control Stock']))
                    <div><p class="px-3 text-xs font-semibold uppercase tracking-widest text-white/60">Operación</p><div class="mt-2 space-y-1">
                    @if(in_array(Auth::user()->role, ['ADMIN', 'Ventas']))
                    <a href="{{ route('ventas.index') }}" aria-label="Ventas" aria-current="{{ request()->routeIs('ventas.*') ? 'page' : 'false' }}" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('ventas.*') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="8" cy="21" r="1" /><circle cx="19" cy="21" r="1" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.05 2.05h2l2.66 12.42a2 2 0 002 1.58h9.78a2 2 0 001.95-1.57l1.65-7.43H5.12" />
                        </svg>
                        Ventas
                    </a>
                    @endif
                    @if(in_array(Auth::user()->role, ['ADMIN', 'Control Stock']))
                    <a href="{{ route('productos.index') }}" aria-label="Productos" aria-current="{{ request()->routeIs('productos.*') ? 'page' : 'false' }}" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('productos.*') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M2.97 12.92A2 2 0 002 14.63v3.24a2 2 0 00.97 1.71l3 1.8a2 2 0 002.06 0L12 19v-5.5l-5-3-4.03 2.42Z" /><path d="m7 16.5-4.74-2.85m4.74 2.85 5-3M7 16.5v5.17M12 13.5V19l3.97 2.38a2 2 0 002.06 0l3-1.8a2 2 0 00.97-1.71v-3.24a2 2 0 00-.97-1.71L17 10.5l-5 3Zm5 3-5-3m5 3 4.74-2.85M17 16.5v5.17M7.97 4.42A2 2 0 007 6.13v4.37l5 3 5-3V6.13a2 2 0 00-.97-1.71l-3-1.8a2 2 0 00-2.06 0l-3 1.8ZM12 8 7.26 5.15M12 8l4.74-2.85M12 13.5V8" />
                        </svg>
                        Productos
                    </a>
                    <a href="{{ route('categorias.index') }}" aria-label="Categorías" aria-current="{{ request()->routeIs('categorias.*') ? 'page' : 'false' }}" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('categorias.*') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 20h16a2 2 0 002-2V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 007.93 3H4a2 2 0 00-2 2v13c0 1.1.9 2 2 2ZM8 10v4m4-4v2m4-2v6" />
                        </svg>
                        Categorías
                    </a>
                    @endif
                    </div></div>@endif
                    @if(Auth::user()->isAdmin())
                    <div><p class="px-3 text-xs font-semibold uppercase tracking-widest text-white/60">Administración</p><div class="mt-2 space-y-1">
                    <a href="{{ route('admin.users.index') }}" aria-label="Usuarios" aria-current="{{ request()->routeIs('admin.*') ? 'page' : 'false' }}" class="group flex min-h-11 items-center gap-3 rounded-control px-3 text-sm font-semibold {{ request()->routeIs('admin.*') ? 'bg-primary-container text-white' : 'text-white/75 hover:bg-white/10 hover:text-white' }} transition-colors">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M16 3.128a4 4 0 010 7.744M22 21v-2a4 4 0 00-3-3.87" /><circle cx="9" cy="7" r="4" />
                        </svg>
                        Usuarios
                    </a>
                    </div></div>@endif
                </nav>
            </div>
            <div class="mt-auto flex-shrink-0">
                <a href="{{ route('profile.edit') }}" @click="sidebarOpen = false" aria-current="{{ request()->routeIs('profile.edit') ? 'page' : 'false' }}" class="flex min-h-11 items-center gap-3 rounded-control border-t px-4 py-4 text-white transition-colors {{ request()->routeIs('profile.edit') ? 'bg-primary-container' : 'bg-white/10 hover:bg-white/15' }}" aria-label="Configuración de cuenta para {{ Auth::user()->name }} ({{ '@' . Auth::user()->username }})">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-primary text-white" aria-hidden="true"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="5" /><path d="M20 21a8 8 0 0 0-16 0" /></svg></span>
                    <span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold">{{ Auth::user()->name }}</span><span class="block truncate text-xs text-white/60">{{ '@' . Auth::user()->username }}</span></span>
                    <svg class="h-4 w-4 shrink-0 text-white/60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M9.671 4.136a2.34 2.34 0 014.659 0 2.34 2.34 0 003.319 1.915 2.34 2.34 0 012.33 4.033 2.34 2.34 0 000 3.831 2.34 2.34 0 01-2.33 4.033 2.34 2.34 0 00-3.319 1.915 2.34 2.34 0 01-4.659 0 2.34 2.34 0 00-3.32-1.915 2.34 2.34 0 01-2.33-4.033 2.34 2.34 0 000-3.831A2.34 2.34 0 016.35 6.051a2.34 2.34 0 003.319-1.915" /><circle cx="12" cy="12" r="3" /></svg>
                </a>
            </div>
        </div>
    </div>
</div>
