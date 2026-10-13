<!-- Skeleton Paso 3: Propuesta de Fertilización -->
<div class="space-y-6 animate-pulse">
    <!-- Header Skeleton -->
    <div class="border-b pb-3 flex justify-between items-center">
        <div class="h-6 bg-gray-200 dark:bg-white/10 rounded w-72"></div>
        <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-24"></div>
    </div>

    <!-- Cards Grid Skeleton (2 Columnas) -->
    <div class="grid gap-8 grid-cols-1 lg:grid-cols-2">
        @for($i = 0; $i < 4; $i++)
            <div class="bg-white dark:bg-[#1A2227] border-2 border-green-200/60 dark:border-white/10 rounded-3xl p-6 shadow-sm space-y-4">
                <!-- Título de la etapa y botón eliminar -->
                <div class="flex justify-between items-center pb-2 border-b border-gray-100 dark:border-white/5">
                    <div class="h-6 bg-green-100 dark:bg-green-500/20 rounded w-48"></div>
                    <div class="w-6 h-6 bg-red-100 dark:bg-red-500/20 rounded-full"></div>
                </div>

                <!-- Botón Agregar Producto -->
                <div class="h-9 bg-green-700/20 rounded-xl w-40"></div>

                <!-- Tarjeta de Producto Skeleton -->
                <div class="bg-green-50/50 dark:bg-white/5 border border-green-200/50 dark:border-white/10 rounded-2xl p-4 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-16"></div>
                            <div class="h-9 bg-white dark:bg-[#1A2227] rounded-xl border border-gray-200 dark:border-white/10"></div>
                        </div>
                        <div class="space-y-2">
                            <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-28"></div>
                            <div class="h-9 bg-white dark:bg-[#1A2227] rounded-xl border border-gray-200 dark:border-white/10"></div>
                        </div>
                    </div>
                </div>

                <!-- Controles Inferiores Skeleton -->
                <div class="border-t border-gray-100 dark:border-white/5 pt-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-32"></div>
                            <div class="h-10 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10"></div>
                        </div>
                        <div class="space-y-2">
                            <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-24"></div>
                            <div class="h-10 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10"></div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-24"></div>
                        <div class="h-10 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>
