<header class="bg-white dark:bg-[#131b20] border-b border-gray-200 dark:border-white/5 px-4 sm:px-6 py-3.5 flex justify-between items-center transition-colors duration-200 shadow-sm" x-data="{ 
    isDropdownOpen: false,
    isDark: document.documentElement.classList.contains('dark'),
    toggleTheme() {
        this.isDark = !this.isDark;
        if (this.isDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    }
}">
    <!-- Left: Brand link to /dashboard -->
    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 group">
        <div class="w-9 h-9 rounded-xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center border border-green-100 dark:border-green-500/20 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-leaf text-green-600 dark:text-green-400 text-lg"></i>
        </div>
        <div>
            <span class="text-lg font-black text-gray-800 dark:text-white tracking-tight">Impulsora</span>
            <span class="text-[10px] block font-bold text-green-600 dark:text-green-400 uppercase tracking-widest -mt-1">Nutrición Vegetal</span>
        </div>
    </a>

    <!-- Right: Dark Mode Toggle + User Profile + Dropdown -->
    <div class="flex items-center space-x-3">
        <!-- Dark Mode Toggle Button -->
        <button
            type="button"
            @click="toggleTheme()"
            class="h-9 w-9 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-600 dark:text-amber-400 flex items-center justify-center transition-all cursor-pointer border border-gray-200 dark:border-white/10 shadow-sm"
            title="Cambiar modo claro/oscuro"
            aria-label="Toggle dark mode"
        >
            <template x-if="!isDark">
                <i class="fa-solid fa-moon text-sm"></i>
            </template>
            <template x-if="isDark">
                <i class="fa-solid fa-sun text-sm"></i>
            </template>
        </button>

        <!-- User Info -->
        @php
            $userDisplayName = Auth::user()->name ?? session('user.name') ?? 'Usuario';
            if (str_contains($userDisplayName, '@')) {
                $cleanPrefix = explode('@', $userDisplayName)[0];
                $words = preg_split('/[._-]/', $cleanPrefix);
                $userDisplayName = ucwords(implode(' ', array_filter($words ?: [$cleanPrefix])));
            }
            $userDisplayName = mb_strtoupper($userDisplayName, 'UTF-8');
        @endphp
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 hidden sm:inline-block">
            {{ $userDisplayName }}
        </span>
        
        <!-- User Avatar & Dropdown -->
        <div class="relative inline-block" @click.outside="isDropdownOpen = false">
            <button
                type="button"
                @click="isDropdownOpen = !isDropdownOpen"
                class="h-9 w-9 rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 flex items-center justify-center text-green-700 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-500/20 transition-colors cursor-pointer"
                title="Menú de usuario"
            >
                <i class="fa-solid fa-user text-sm"></i>
            </button>

            <div 
                x-show="isDropdownOpen" 
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-2 w-52 bg-white dark:bg-[#1A2227] rounded-2xl shadow-xl border border-gray-100 dark:border-white/10 py-1.5 z-[200]"
            >
                <div class="px-4 py-2 border-b border-gray-100 dark:border-white/5 sm:hidden">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Conectado como</p>
                    <p class="text-sm font-bold text-gray-800 dark:text-white truncate">
                        {{ $userDisplayName }}
                    </p>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button
                        type="submit"
                        class="w-full text-left px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                    >
                        <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
