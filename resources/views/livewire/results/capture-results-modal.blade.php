<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4" x-cloak x-init="$wire.loadResultsData()">
            <div class="relative w-full max-w-4xl bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl p-6 lg:p-8 space-y-6 animate-fade-in">
                @if(!$isSuccess)
                    @if($isLoading)
                        <!-- ================= SKELETON LOADER STATE ================= -->
                        <!-- Skeleton Header -->
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-4 animate-pulse">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <div class="h-5 w-24 bg-gray-200 dark:bg-white/10 rounded-full"></div>
                                    <div class="h-4 w-44 bg-gray-100 dark:bg-white/5 rounded-md"></div>
                                </div>
                                <div class="h-6 w-64 bg-gray-200 dark:bg-white/10 rounded-xl"></div>
                                <div class="h-4 w-48 bg-gray-100 dark:bg-white/5 rounded-lg"></div>
                            </div>
                            <div class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-white/5"></div>
                        </div>

                        <!-- Skeleton Body Sections -->
                        <div class="space-y-6 max-h-[68vh] overflow-y-auto pr-1 animate-pulse">
                            <!-- Section 1 Skeleton: Cosecha Actual -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-green-500/40"></div>
                                    <div class="h-3.5 w-52 bg-gray-200 dark:bg-white/10 rounded"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @for($i = 0; $i < 3; $i++)
                                        <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5 space-y-2">
                                            <div class="h-2.5 w-24 bg-gray-200 dark:bg-white/10 rounded"></div>
                                            <div class="h-10 w-full bg-white dark:bg-[#131B20] rounded-xl border border-gray-100 dark:border-white/5"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Section 2 Skeleton: Referencia Tradicional -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-amber-500/40"></div>
                                    <div class="h-3.5 w-56 bg-gray-200 dark:bg-white/10 rounded"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    @for($i = 0; $i < 4; $i++)
                                        <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5 space-y-2">
                                            <div class="h-2.5 w-24 bg-gray-200 dark:bg-white/10 rounded"></div>
                                            <div class="h-10 w-full bg-white dark:bg-[#131B20] rounded-xl border border-gray-100 dark:border-white/5"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Section 3 Skeleton: Bento Cards de Rentabilidad -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-blue-500/40"></div>
                                        <div class="h-3.5 w-64 bg-gray-200 dark:bg-white/10 rounded"></div>
                                    </div>
                                    <div class="h-2.5 w-32 bg-gray-100 dark:bg-white/5 rounded"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @for($i = 0; $i < 3; $i++)
                                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#1A2227] border border-gray-200/70 dark:border-white/5 space-y-2">
                                            <div class="flex justify-between items-center">
                                                <div class="h-2.5 w-20 bg-gray-200 dark:bg-white/10 rounded"></div>
                                                <div class="h-4 w-4 bg-gray-200 dark:bg-white/10 rounded-full"></div>
                                            </div>
                                            <div class="h-8 w-28 bg-gray-200 dark:bg-white/10 rounded-xl"></div>
                                            <div class="h-2.5 w-36 bg-gray-100 dark:bg-white/5 rounded"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Section 4 Skeleton: Dictamen y Recomendaciones -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500/40"></div>
                                    <div class="h-3.5 w-48 bg-gray-200 dark:bg-white/10 rounded"></div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @for($i = 0; $i < 3; $i++)
                                        <div class="space-y-2">
                                            <div class="h-2.5 w-32 bg-gray-200 dark:bg-white/10 rounded"></div>
                                            <div class="h-24 w-full bg-gray-50 dark:bg-[#1A2227] rounded-2xl border border-gray-200/70 dark:border-white/5"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <!-- Section 5 Skeleton: Coordenadas GPS y Mapa -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-red-500/40"></div>
                                    <div class="h-3.5 w-56 bg-gray-200 dark:bg-white/10 rounded"></div>
                                </div>
                                <div class="h-11 w-full bg-gray-50 dark:bg-[#1A2227] rounded-2xl border border-gray-200/70 dark:border-white/5"></div>
                                <div class="h-64 sm:h-72 w-full bg-gray-100 dark:bg-white/5 rounded-2xl border border-gray-200/70 dark:border-white/5 flex items-center justify-center">
                                    <div class="space-y-2 text-center">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-white/10 mx-auto flex items-center justify-center text-gray-400">
                                            <i class="fa-solid fa-map-location-dot text-lg"></i>
                                        </div>
                                        <div class="h-3 w-36 bg-gray-200 dark:bg-white/10 rounded mx-auto"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Skeleton Footer Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5 animate-pulse">
                            <div class="h-10 w-24 bg-gray-200 dark:bg-white/10 rounded-2xl"></div>
                            <div class="h-10 w-36 bg-gray-200 dark:bg-white/10 rounded-2xl"></div>
                        </div>
                    @else
                        <!-- ================= ACTUAL INTERACTIVE FORM ================= -->
                        <!-- Header -->
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">
                                        Folio #{{ $planCode }}
                                    </span>
                                    @if($version > 1)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                                            V{{ $version }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-400">| Captura de Resultados & Rentabilidad</span>
                                </div>
                                <h2 class="text-xl font-black text-gray-900 dark:text-white">Dictamen Final de Cosecha</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center gap-2">
                                    <i class="fa-solid fa-user text-green-600 dark:text-green-400"></i>
                                    <span>{{ $customerName }}</span>
                                    @if($customerPhone)
                                        <span class="text-gray-300 dark:text-white/20">•</span>
                                        <span class="text-gray-400">{{ $customerPhone }}</span>
                                    @endif
                                </p>
                            </div>
                            <button type="button" wire:click="close" class="p-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-2xl transition-colors">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        @if($errorMessage)
                            <div class="p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl text-xs font-semibold flex items-center gap-3">
                                <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
                                <span>{{ $errorMessage }}</span>
                            </div>
                        @endif

                        <!-- Form Content Scrollable -->
                        <div class="space-y-6 max-h-[68vh] overflow-y-auto pr-1">
                            <!-- Section 1: Cosecha Actual -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        1. Datos de Cosecha Actual (Manejo Impulsora)
                                    </h3>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5">
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Fecha Cosecha *
                                        </label>
                                        <input
                                            type="date"
                                            wire:model="fechaCosecha"
                                            class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500"
                                        >
                                        @error('fechaCosecha') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5">
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Rendimiento Obtenido (Ton/Ha) *
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="number"
                                                step="0.1"
                                                max="99.9"
                                                wire:model.live.debounce.300ms="rendimiento"
                                                placeholder="Ej: 14.5"
                                                class="w-full h-10 px-3 pr-16 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-sm font-black text-green-600 dark:text-green-400 focus:outline-none focus:border-green-500"
                                            >
                                            <span class="absolute right-3 top-2.5 text-[10px] font-bold text-gray-400">Ton/Ha</span>
                                        </div>
                                        @error('rendimiento') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5">
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Costo Total del Plan ($/Ha) *
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2.5 text-xs font-bold text-gray-400">$</span>
                                            <input
                                                type="number"
                                                step="0.01"
                                                wire:model.live.debounce.300ms="costoTotal"
                                                class="w-full h-10 pl-7 pr-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500"
                                            >
                                        </div>
                                        @error('costoTotal') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Referencia Tradicional -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        2. Referencia del Manejo Tradicional / Testigo
                                    </h3>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5">
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Costo Tradicional ($/Ha) *
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2.5 text-xs font-bold text-gray-400">$</span>
                                            <input
                                                type="number"
                                                step="0.01"
                                                wire:model.live.debounce.300ms="costoTradicional"
                                                class="w-full h-10 pl-7 pr-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500"
                                            >
                                        </div>
                                        @error('costoTradicional') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5">
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Rend. Tradicional (Ton/Ha) *
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="number"
                                                step="0.1"
                                                wire:model.live.debounce.300ms="rendimientoTradicional"
                                                placeholder="Ej: 11.0"
                                                class="w-full h-10 px-3 pr-16 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500"
                                            >
                                            <span class="absolute right-3 top-2.5 text-[10px] font-bold text-gray-400">Ton/Ha</span>
                                        </div>
                                        @error('rendimientoTradicional') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5">
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Dosis /Ha (Ton/Ha) *
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            wire:model="dosis"
                                            placeholder="Ej: 1.0"
                                            class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500"
                                        >
                                        @error('dosis') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="bg-gray-50 dark:bg-[#1A2227] p-3 rounded-2xl border border-gray-200/70 dark:border-white/5">
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Precio Cosecha ($/Ton) *
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2.5 text-xs font-bold text-gray-400">$</span>
                                            <input
                                                type="number"
                                                step="0.01"
                                                wire:model.live.debounce.300ms="precioCosecha"
                                                placeholder="5500.00"
                                                class="w-full h-10 pl-7 pr-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500"
                                            >
                                        </div>
                                        @error('precioCosecha') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Bento KPIs en Tiempo Real -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                            3. Rentabilidad Comparativa Calculada en Tiempo Real
                                        </h3>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-semibold">Cálculo dinámico automatizado</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <!-- Card 1: Utilidad Extra -->
                                    <div class="p-4 rounded-2xl border {{ $utilidadExtra >= 0 ? 'bg-green-500/10 border-green-500/20 text-green-700 dark:text-green-300' : 'bg-red-500/10 border-red-500/20 text-red-700 dark:text-red-300' }} space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-black uppercase tracking-wider">Utilidad Extra ($)</span>
                                            <i class="fa-solid {{ $utilidadExtra >= 0 ? 'fa-arrow-trend-up text-green-600 dark:text-green-400' : 'fa-arrow-trend-down text-red-600 dark:text-red-400' }} text-sm"></i>
                                        </div>
                                        <div class="text-2xl font-black {{ $utilidadExtra >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $utilidadExtra >= 0 ? '+' : '' }}${{ number_format($utilidadExtra, 2) }}
                                        </div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                            Diferencial de rendimiento: {{ number_format(($rendimiento ?? 0) - ($rendimientoTradicional ?? 0), 1) }} Ton/Ha
                                        </p>
                                    </div>

                                    <!-- Card 2: Inversión Extra -->
                                    <div class="p-4 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-700 dark:text-blue-300 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-black uppercase tracking-wider">Inversión Extra ($)</span>
                                            <i class="fa-solid fa-coins text-blue-600 dark:text-blue-400 text-sm"></i>
                                        </div>
                                        <div class="text-2xl font-black text-blue-600 dark:text-blue-400">
                                            ${{ number_format($inversionExtra, 2) }}
                                        </div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                            Costo Total - Costo Tradicional
                                        </p>
                                    </div>

                                    <!-- Card 3: Retorno de Inversión (ROI) -->
                                    <div class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-700 dark:text-purple-300 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-black uppercase tracking-wider">Retorno de Inversión (ROI)</span>
                                            <i class="fa-solid fa-chart-line text-purple-600 dark:text-purple-400 text-sm"></i>
                                        </div>
                                        <div class="text-2xl font-black text-purple-600 dark:text-purple-400">
                                            {{ number_format($retornoInversion, 2) }}x
                                        </div>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                            Por cada $1.00 extra invertido
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Recomendaciones Técnicas -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <h3 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        4. Dictamen y Recomendaciones Agronómicas
                                    </h3>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Recomendaciones Técnicas *
                                        </label>
                                        <textarea
                                            wire:model="recomendaciones"
                                            rows="3"
                                            maxlength="500"
                                            placeholder="Manejo agronómico y monitoreo sugerido..."
                                            class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none focus:border-green-500 resize-none"
                                        ></textarea>
                                        @error('recomendaciones') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Propuesta Siguiente Ciclo *
                                        </label>
                                        <textarea
                                            wire:model="propuesta"
                                            rows="3"
                                            maxlength="500"
                                            placeholder="Propuesta técnica recomendada..."
                                            class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none focus:border-green-500 resize-none"
                                        ></textarea>
                                        @error('propuesta') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">
                                            Buenas Prácticas Agrícolas *
                                        </label>
                                        <textarea
                                            wire:model="buenasPracticas"
                                            rows="3"
                                            maxlength="500"
                                            placeholder="Calibración, riego y prácticas de campo..."
                                            class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none focus:border-green-500 resize-none"
                                        ></textarea>
                                        @error('buenasPracticas') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Section 5: Ubicación Geográfica y Mapa de Parcela -->
                            <div class="space-y-3"
                                x-data="{
                                    map: null,
                                    marker: null,
                                    searchQuery: '',
                                    results: [],
                                    isSearching: false,
                                    debounceTimer: null,
                                    coordenadasDisplay: @entangle('coordenadas'),
                                    init() {
                                        this.initMap();
                                        this.$watch('coordenadasDisplay', (val) => {
                                            if (!val || !val.includes(',')) return;
                                            const parts = val.split(',');
                                            const pLat = parseFloat(parts[0]);
                                            const pLng = parseFloat(parts[1]);
                                            if (!isNaN(pLat) && !isNaN(pLng) && this.map) {
                                                if (this.marker) {
                                                    const cur = this.marker.getLatLng();
                                                    if (Math.abs(cur.lat - pLat) > 0.00001 || Math.abs(cur.lng - pLng) > 0.00001) {
                                                        this.marker.setLatLng([pLat, pLng]);
                                                        this.map.setView([pLat, pLng], Math.max(this.map.getZoom(), 14));
                                                    }
                                                } else {
                                                    this.marker = L.marker([pLat, pLng], { draggable: true, icon: this.getMarkerIcon() }).addTo(this.map);
                                                    this.bindMarkerEvents(this.marker);
                                                    this.map.setView([pLat, pLng], Math.max(this.map.getZoom(), 14));
                                                }
                                            }
                                        });
                                    },
                                    getMarkerIcon() {
                                        return L.divIcon({
                                            className: 'custom-results-marker',
                                            html: `<div style='display:flex;align-items:center;justify-content:center;width:34px;height:34px;background:#ef4444;color:#ffffff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #ffffff;box-shadow:0 3px 10px rgba(0,0,0,0.35);cursor:grab;'><i class=\'fa-solid fa-location-dot\' style=\'transform:rotate(45deg);font-size:15px;\'></i></div>`,
                                            iconSize: [34, 34],
                                            iconAnchor: [17, 34],
                                            popupAnchor: [0, -34]
                                        });
                                    },
                                    bindMarkerEvents(markerInstance) {
                                        markerInstance.on('dragend', (e) => {
                                            const pos = e.target.getLatLng();
                                            this.updateMarker(pos.lat, pos.lng, false);
                                        });
                                    },
                                    initMap() {
                                        if (typeof L === 'undefined') {
                                            setTimeout(() => this.initMap(), 150);
                                            return;
                                        }
                                        const container = document.getElementById('results-map-container');
                                        if (!container) {
                                            setTimeout(() => this.initMap(), 150);
                                            return;
                                        }

                                        if (this.map) {
                                            try {
                                                this.map.remove();
                                            } catch(e) {}
                                            this.map = null;
                                        }

                                        if (container._leaflet_id) {
                                            delete container._leaflet_id;
                                        }

                                        let lat = 24.8080;
                                        let lng = -107.3940;
                                        let hasCoords = false;

                                        if (this.coordenadasDisplay && this.coordenadasDisplay.includes(',')) {
                                            const parts = this.coordenadasDisplay.split(',');
                                            const pLat = parseFloat(parts[0]);
                                            const pLng = parseFloat(parts[1]);
                                            if (!isNaN(pLat) && !isNaN(pLng)) {
                                                lat = pLat;
                                                lng = pLng;
                                                hasCoords = true;
                                            }
                                        }

                                        this.map = L.map('results-map-container', {
                                            zoomControl: true,
                                            attributionControl: false
                                        }).setView([lat, lng], hasCoords ? 14 : 11);

                                        // Capa base OpenStreetMap de alta confiabilidad
                                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            maxZoom: 19,
                                            attribution: '© OpenStreetMap',
                                            crossOrigin: true
                                        }).addTo(this.map);

                                        if (hasCoords) {
                                            this.marker = L.marker([lat, lng], { draggable: true, icon: this.getMarkerIcon() }).addTo(this.map);
                                            this.bindMarkerEvents(this.marker);
                                        }

                                        this.map.on('click', (e) => {
                                            this.updateMarker(e.latlng.lat, e.latlng.lng, false);
                                        });

                                        // Ajustar dimensiones en varios intervalos para evitar cuadros grises dentro del modal con scroll
                                        [100, 300, 600, 1000].forEach(delay => {
                                            setTimeout(() => {
                                                if (this.map) this.map.invalidateSize();
                                            }, delay);
                                        });
                                    },
                                    updateMarker(lat, lng, shouldCenter = true) {
                                        const numLat = parseFloat(lat);
                                        const numLng = parseFloat(lng);
                                        if (isNaN(numLat) || isNaN(numLng)) return;

                                        if (!this.marker && this.map) {
                                            this.marker = L.marker([numLat, numLng], { draggable: true, icon: this.getMarkerIcon() }).addTo(this.map);
                                            this.bindMarkerEvents(this.marker);
                                        } else if (this.marker) {
                                            this.marker.setLatLng([numLat, numLng]);
                                        }

                                        if (shouldCenter && this.map) {
                                            this.map.setView([numLat, numLng], Math.max(this.map.getZoom(), 14), { animate: true });
                                        }

                                        const str = numLat.toFixed(6) + ',' + numLng.toFixed(6);
                                        this.coordenadasDisplay = str;
                                        $wire.setCoordinates(numLat, numLng);
                                    },
                                    searchPlaces() {
                                        if (!this.searchQuery || this.searchQuery.length < 3) {
                                            this.results = [];
                                            return;
                                        }
                                        this.isSearching = true;
                                        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(this.searchQuery + ' Sinaloa Mexico') + '&limit=5')
                                            .then(r => r.json())
                                            .then(data => {
                                                this.results = data;
                                                this.isSearching = false;
                                            })
                                            .catch(() => {
                                                this.isSearching = false;
                                            });
                                    },
                                    onSearchInput() {
                                        clearTimeout(this.debounceTimer);
                                        this.debounceTimer = setTimeout(() => {
                                            this.searchPlaces();
                                        }, 400);
                                    },
                                    selectPlace(place) {
                                        const lat = parseFloat(place.lat);
                                        const lon = parseFloat(place.lon);
                                        this.updateMarker(lat, lon, true);
                                        this.searchQuery = place.display_name.split(',')[0];
                                        this.results = [];
                                    }
                                }"
                                @mouseenter="if (map) map.invalidateSize()"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <h3 class="text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                            5. Ubicación Geográfica de la Parcela / Cosecha
                                        </h3>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-medium">
                                        <i class="fa-solid fa-hand-pointer text-green-600 dark:text-green-400 mr-1"></i>
                                        Haz clic en el mapa o arrastra el marcador para precisar la cosecha
                                    </span>
                                </div>

                                <!-- Buscador y Coordenadas GPS en Bento Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                    <!-- Buscador de Localidad / Parcela -->
                                    <div class="sm:col-span-7 relative">
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                                            </div>
                                            <input
                                                type="text"
                                                x-model="searchQuery"
                                                @input="onSearchInput()"
                                                @keydown.enter.prevent="searchPlaces()"
                                                placeholder="Buscar ejido, rancho o municipio (ej. Costa Rica, Navolato)..."
                                                class="w-full h-10 pl-9 pr-8 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs text-gray-900 dark:text-white focus:outline-none focus:border-green-500"
                                            >
                                            <button 
                                                x-show="searchQuery.length > 0" 
                                                type="button"
                                                @click="searchQuery = ''; results = []"
                                                class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs"
                                            >
                                                <i class="fa-solid fa-circle-xmark"></i>
                                            </button>
                                        </div>

                                        <!-- Dropdown de Resultados Nominatim -->
                                        <div 
                                            x-show="results.length > 0" 
                                            @click.away="results = []"
                                            class="absolute left-0 right-0 top-11 z-30 bg-white dark:bg-[#1A2227] rounded-xl border border-gray-200 dark:border-white/10 shadow-xl overflow-hidden max-h-48 overflow-y-auto"
                                        >
                                            <template x-for="item in results" :key="item.place_id">
                                                <button
                                                    type="button"
                                                    @click="selectPlace(item)"
                                                    class="w-full text-left px-3 py-2 text-xs hover:bg-green-50 dark:hover:bg-white/5 border-b border-gray-50 dark:border-white/5 last:border-0 text-gray-700 dark:text-gray-200 transition-colors flex items-center gap-2"
                                                >
                                                    <i class="fa-solid fa-location-dot text-red-500 text-[10px] shrink-0"></i>
                                                    <span class="truncate" x-text="item.display_name"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Coordenadas GPS Input Directo -->
                                    <div class="sm:col-span-5 relative">
                                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                            <i class="fa-solid fa-location-crosshairs text-xs"></i>
                                        </div>
                                        <input
                                            type="text"
                                            wire:model.live.debounce.400ms="coordenadas"
                                            class="w-full h-10 pl-9 pr-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-mono text-gray-900 dark:text-white focus:outline-none focus:border-green-500"
                                            placeholder="25.790466,-108.995888"
                                        >
                                        @error('coordenadas') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Leaflet Map Container con wire:ignore -->
                                <div wire:ignore class="h-64 sm:h-72 w-full rounded-2xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-inner relative bg-gray-100 dark:bg-[#1A2227]">
                                    <div id="results-map-container" class="w-full h-full z-0"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                            <button
                                type="button"
                                wire:click="close"
                                class="px-5 py-2.5 rounded-2xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 font-bold text-xs hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                wire:click="saveResults"
                                wire:loading.attr="disabled"
                                class="px-6 py-2.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2"
                            >
                                <span wire:loading.remove wire:target="saveResults">Guardar Dictamen</span>
                                <span wire:loading wire:target="saveResults" class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                                    <span>Guardando y Generando PDF...</span>
                                </span>
                            </button>
                        </div>
                    @endif
                @else
                    <!-- Success View with PDF and WhatsApp -->
                    <div class="text-center py-6 space-y-6">
                        <div class="w-16 h-16 rounded-full bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center mx-auto text-3xl shadow-inner">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">
                                Folio #{{ $planCode }} @if($version > 1) — V{{ $version }} @endif
                            </span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mt-2">¡Resultados Guardados con Éxito!</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                                Se ha registrado el dictamen agronómico para <strong class="text-gray-700 dark:text-gray-200">{{ $customerName }}</strong>. Puedes descargar el reporte institucional o compartirlo vía WhatsApp.
                            </p>
                        </div>

                        @if($this->isCustomerPhoneMissing)
                            <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs rounded-xl flex items-center gap-2.5 text-left border border-amber-200 dark:border-amber-800/40 shadow-xs max-w-sm mx-auto">
                                <i class="fa-solid fa-triangle-exclamation flex-shrink-0 text-base text-amber-600 dark:text-amber-400"></i>
                                <div class="leading-snug">
                                    <span class="font-bold block">Cliente sin celular registrado</span>
                                    <span class="text-[11px] opacity-90">El cliente no cuenta con número celular en SAP. No es posible compartir por WhatsApp.</span>
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                            <button
                                type="button"
                                wire:click="downloadPdf"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold text-xs shadow-md hover:bg-green-600 dark:hover:bg-green-400 transition-colors"
                            >
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>Descargar Dictamen PDF</span>
                            </button>

                            <button
                                type="button"
                                wire:click="shareWhatsApp"
                                wire:loading.attr="disabled"
                                @if($this->isCustomerPhoneMissing) disabled title="El cliente no cuenta con celular registrado en SAP" @endif
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-colors disabled:bg-gray-400 disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none"
                            >
                                <span wire:loading.remove wire:target="shareWhatsApp" class="flex items-center gap-2">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                    <span>Compartir por WhatsApp</span>
                                </span>
                                <span wire:loading wire:target="shareWhatsApp" class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                                    <span>Enviando...</span>
                                </span>
                            </button>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-white/5">
                            <button type="button" wire:click="close" class="text-xs font-bold text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                                Cerrar ventana
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
