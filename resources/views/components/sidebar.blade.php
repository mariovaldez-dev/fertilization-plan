<aside :class="sidebarOpen ? 'translate-x-0 lg:w-72 lg:opacity-100' : '-translate-x-full lg:w-0 lg:opacity-0 lg:border-r-0'" 
       class="fixed inset-y-0 left-0 z-50 -translate-x-full lg:w-0 lg:opacity-0 lg:border-r-0 bg-white dark:bg-[#131B20] border-r border-gray-100 dark:border-white/5 flex flex-col transition-all duration-300 ease-in-out lg:static shadow-xl lg:shadow-none overflow-hidden shrink-0">
    <div class="w-72 flex flex-col h-full">
        <!-- Brand -->
        <div class="h-20 flex items-center px-6 border-b border-gray-100 dark:border-white/5 shrink-0">
            <div class="flex items-center gap-3">
                <div class="bg-green-50 dark:bg-green-950/30 p-2.5 rounded-2xl">
                    <img src="{{ asset('favicon.png') }}" alt="Logo" class="h-6 w-6 object-contain" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase text-green-600 dark:text-green-400 tracking-[0.2em] leading-none mb-0.5">Grupo Impulsora</span>
                    <span class="text-sm font-extrabold text-gray-900 dark:text-white leading-none">Nutrición Vegetal</span>
                </div>
            </div>
            <!-- Close Button -->
            <button @click="toggleSidebar()" class="ml-auto text-gray-400 hover:text-gray-650 dark:text-gray-500 dark:hover:text-gray-300 lg:hidden">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar">
            <div class="px-4 text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 tracking-widest mb-2 mt-4 first:mt-0">Nutrición Agrícola</div>

            <a href="{{ route('seguimiento.index') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all {{ request()->routeIs('seguimiento.*') || request()->routeIs('plans.*') ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-seedling w-5 text-center text-base"></i>
                <span>Planes de Fertilización</span>
            </a>

            <a href="{{ route('plan.create') }}" wire:navigate class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-bold transition-all {{ request()->routeIs('plan.*') ? 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white' }}">
                <i class="fa-solid fa-wand-magic-sparkles w-5 text-center text-base"></i>
                <span>Crear Nuevo Plan</span>
            </a>
        </nav>
    </div>
</aside>
