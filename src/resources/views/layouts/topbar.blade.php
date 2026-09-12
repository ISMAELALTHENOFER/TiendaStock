<div class="bg-surface border-b border-border" style="padding-top: env(safe-area-inset-top);">
    <div class="px-4 sm:px-6 lg:px-8" style="padding-left: max(1rem, env(safe-area-inset-left)); padding-right: max(1rem, env(safe-area-inset-right));">
        <div class="flex items-center justify-between min-h-[4rem] py-2">
            <div class="flex">
                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button type="button" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-controls="mobile-sidebar" class="inline-flex items-center justify-center w-11 h-11 rounded-md text-gray-500 hover:text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-600 transition-colors duration-200">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- User menu -->
            <div class="flex items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" aria-label="Abrir menú de usuario" class="flex items-center min-h-11 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-600 transition-all duration-200">
                            <div class="h-9 w-9 rounded-full bg-slate-900 text-white flex items-center justify-center shadow-sm">
                                <span class="text-sm font-medium text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <span class="ml-2 text-gray-700 text-sm font-medium hidden sm:inline">{{ Auth::user()->name }}</span>
                            <svg class="ml-1 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</div>
