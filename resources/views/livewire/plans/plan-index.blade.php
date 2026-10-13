<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-[#131B20] p-6 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Nutrición Vegetal</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">Planes de Fertilización</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Administración, seguimiento fenológico y dictámenes técnicos para productores</p>
        </div>

        <div class="flex items-center gap-3">
            @if($cardCode)
                <a href="{{ route('seguimiento.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/15 text-gray-700 dark:text-gray-200 font-bold text-xs transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Volver a Clientes</span>
                </a>
            @endif
            <a href="{{ route('wizard.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-sm shadow-lg shadow-green-600/20 transition-all duration-200">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Crear Nuevo Plan</span>
            </a>
        </div>
    </div>

    <!-- Filters Section (IMP-05 Cascading Filters & Mandatory Season) -->
    <div class="bg-white dark:bg-[#131B20] p-6 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search Query -->
            <div>
                <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Búsqueda Rápida</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cliente, asesor o folio..." class="w-full h-12 pl-10 pr-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-sm text-gray-900 dark:text-white focus:border-green-500 focus:outline-none">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Zona (Custom Select) -->
            <div class="relative"
                x-data="{
                    open: false,
                    selected: @entangle('selectedZone').live,
                    zones: {{ json_encode($zones) }},
                    get label() {
                        if (!this.selected) return 'Todas las Zonas';
                        const z = this.zones.find(item => (item.Code == this.selected || item.code == this.selected));
                        return z ? (z.Name || z.name) : this.selected;
                    }
                }"
                @click.outside="open = false"
                :class="{ 'z-50': open, 'z-10': !open }"
            >
                <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Zona</label>
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full h-12 px-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-sm text-gray-900 dark:text-white flex items-center justify-between focus:border-green-500 focus:outline-none transition-all text-left shadow-sm"
                >
                    <span x-text="label" class="truncate pr-2 font-medium"></span>
                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }"></i>
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute z-[100] w-full mt-1.5 bg-white dark:bg-[#1A2227] border border-gray-100 dark:border-white/10 rounded-2xl shadow-2xl max-h-60 overflow-y-auto py-1.5 text-sm divide-y divide-gray-50 dark:divide-white/5"
                    style="display: none;"
                >
                    <button
                        type="button"
                        @click="selected = ''; open = false;"
                        class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                        :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold': !selected }"
                    >
                        <span>Todas las Zonas</span>
                        <i x-show="!selected" class="fa-solid fa-check text-green-600 dark:text-green-400 text-xs"></i>
                    </button>
                    <template x-for="z in zones" :key="z.Code || z.code">
                        <button
                            type="button"
                            @click="selected = (z.Code || z.code); open = false;"
                            class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                            :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold': selected == (z.Code || z.code) }"
                        >
                            <span x-text="z.Name || z.name" class="truncate pr-2"></span>
                            <i x-show="selected == (z.Code || z.code)" class="fa-solid fa-check text-green-600 dark:text-green-400 text-xs shrink-0"></i>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Territorio (Custom Select) -->
            <div class="relative"
                x-data="{
                    open: false,
                    selected: @entangle('selectedTerritory').live,
                    territories: {{ json_encode($territories) }},
                    zone: @entangle('selectedZone').live,
                    get label() {
                        if (!this.zone) return 'Selecciona una zona primero';
                        if (!this.selected) return 'Todos los Territorios';
                        const t = this.territories.find(item => (item.Code == this.selected || item.code == this.selected));
                        return t ? (t.Name || t.name) : this.selected;
                    }
                }"
                @click.outside="open = false"
                :class="{ 'z-50': open, 'z-10': !open }"
            >
                <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Territorio</label>
                <button
                    type="button"
                    :disabled="!zone"
                    @click="if(zone) open = !open"
                    class="w-full h-12 px-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-sm text-gray-900 dark:text-white flex items-center justify-between focus:border-green-500 focus:outline-none transition-all text-left shadow-sm"
                    :class="{ 'opacity-50 cursor-not-allowed': !zone, 'cursor-pointer': zone }"
                >
                    <span x-text="label" class="truncate pr-2 font-medium" :class="{ 'text-gray-400 dark:text-gray-500': !zone }"></span>
                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }"></i>
                </button>

                <div
                    x-show="open && zone"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute z-[100] w-full mt-1.5 bg-white dark:bg-[#1A2227] border border-gray-100 dark:border-white/10 rounded-2xl shadow-2xl max-h-60 overflow-y-auto py-1.5 text-sm divide-y divide-gray-50 dark:divide-white/5"
                    style="display: none;"
                >
                    <button
                        type="button"
                        @click="selected = ''; open = false;"
                        class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                        :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold': !selected }"
                    >
                        <span>Todos los Territorios</span>
                        <i x-show="!selected" class="fa-solid fa-check text-green-600 dark:text-green-400 text-xs"></i>
                    </button>
                    @foreach($territories as $t)
                        @php
                            $tCode = $t['Code'] ?? $t['code'];
                            $tName = $t['Name'] ?? $t['name'];
                        @endphp
                        <button
                            type="button"
                            @click="selected = '{{ addslashes($tCode) }}'; open = false;"
                            class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                            :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold': selected === '{{ addslashes($tCode) }}' }"
                        >
                            <span class="truncate pr-2">{{ $tName }}</span>
                            <i x-show="selected === '{{ addslashes($tCode) }}'" class="fa-solid fa-check text-green-600 dark:text-green-400 text-xs shrink-0"></i>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Temporada (Ciclo) (Custom Select) -->
            <div class="relative"
                x-data="{
                    open: false,
                    selected: @entangle('selectedSeason').live,
                    seasons: {{ json_encode($seasons) }},
                    get label() {
                        return this.seasons[this.selected] || this.selected || 'Seleccionar Ciclo';
                    }
                }"
                @click.outside="open = false"
                :class="{ 'z-50': open, 'z-10': !open }"
            >
                <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Temporada (Ciclo) *</label>
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full h-12 px-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-sm font-bold text-green-600 dark:text-green-400 flex items-center justify-between focus:border-green-500 focus:outline-none transition-all text-left shadow-sm"
                >
                    <span x-text="label" class="truncate pr-2"></span>
                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }"></i>
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute z-[100] w-full mt-1.5 bg-white dark:bg-[#1A2227] border border-gray-100 dark:border-white/10 rounded-2xl shadow-2xl max-h-60 overflow-y-auto py-1.5 text-sm divide-y divide-gray-50 dark:divide-white/5"
                    style="display: none;"
                >
                    @foreach($seasons as $key => $lbl)
                        <button
                            type="button"
                            @click="selected = '{{ addslashes($key) }}'; open = false;"
                            class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                            :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold': selected === '{{ addslashes($key) }}' }"
                        >
                            <span>{{ $lbl }}</span>
                            <i x-show="selected === '{{ addslashes($key) }}'" class="fa-solid fa-check text-green-600 dark:text-green-400 text-xs shrink-0"></i>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Filter toggles -->
        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-white/5">
            <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-600 dark:text-gray-300">
                <input type="checkbox" wire:model.live="onlyClosed" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                <span>Ver únicamente planes cerrados (Solo Lectura)</span>
            </label>

            <span class="text-xs text-gray-400 font-medium">Mostrando <strong>{{ count($plans) }}</strong> planes encontrados</span>
        </div>
    </div>

    <!-- Plans Cards Grid -->
    @if(empty($plans))
        <div class="bg-white dark:bg-[#131B20] p-12 rounded-3xl border border-gray-100 dark:border-white/5 text-center space-y-4 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mx-auto text-gray-400 text-2xl">
                <i class="fa-solid fa-seedling"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">No se encontraron planes de fertilización</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">No hay registros con los filtros seleccionados o para la temporada activa.</p>
            </div>
            <a href="{{ route('wizard.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold text-xs hover:bg-green-100 transition-colors">
                <i class="fa-solid fa-plus"></i>
                <span>Crear Primer Plan</span>
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($plans as $p)
                @php
                    $isReadOnly = $p->isReadOnly();
                @endphp
                <div class="group relative rounded-3xl border transition-all duration-300 p-6 flex flex-col justify-between {{ $isReadOnly ? 'bg-gray-50 dark:bg-[#10161A] border-gray-200 dark:border-white/5 opacity-85' : 'bg-white dark:bg-[#131B20] border-gray-100 dark:border-white/5 hover:border-green-500/30 hover:shadow-xl hover:shadow-green-900/5' }}">
                    <div>
                        <!-- Header / Status Badge -->
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <span class="text-xs font-black uppercase tracking-wider text-green-600 dark:text-green-400">{{ $p->planCode }}</span>
                                <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-black bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300">v{{ $p->version }}</span>
                            </div>

                            @if($isReadOnly)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    <i class="fa-solid fa-lock text-[10px]"></i>
                                    <span>Cerrado</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    <span>{{ $p->status->label() }}</span>
                                </span>
                            @endif
                        </div>

                        <!-- Customer & Crop -->
                        <h3 class="text-lg font-black text-gray-900 dark:text-white leading-snug group-hover:text-green-600 transition-colors mb-1">{{ $p->customerName }}</h3>
                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <i class="fa-solid fa-wheat-awn text-green-500"></i>
                            <span class="font-bold">{{ $p->currentCropName ?? 'Cultivo No Definido' }}</span>
                            <span>•</span>
                            <span>{{ $p->hectares }} Ha</span>
                        </div>

                        <!-- Advisor & Metadata -->
                        <div class="space-y-2 py-3 border-y border-gray-100 dark:border-white/5 text-xs text-gray-600 dark:text-gray-300">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Asesor Técnico:</span>
                                <span class="font-bold">{{ $p->advisorName ?: 'Sin Asignar' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Zona / Territorio:</span>
                                <span class="font-medium">{{ $p->zone }} - {{ $p->territory }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">Tipo de Suelo:</span>
                                <span class="font-medium">{{ $p->soilType }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Bottom Bar -->
                    <div class="pt-4 mt-2 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <!-- Proposal PDF Modal Button -->
                            <button wire:click="$dispatch('open-proposal-pdf', { planCode: '{{ $p->planCode }}', version: {{ $p->version }}, customerName: '{{ addslashes($p->customerName) }}' })" class="p-2.5 rounded-xl text-gray-500 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-500/10 transition-colors" title="Ver Propuesta PDF">
                                <i class="fa-solid fa-file-pdf text-sm"></i>
                            </button>

                            <!-- Results Dictamen Modal Button -->
                            <button wire:click="$dispatch('open-results-pdf', { planCode: '{{ $p->planCode }}', customerName: '{{ addslashes($p->customerName) }}' })" class="p-2.5 rounded-xl text-gray-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors" title="Ver Dictamen de Resultados">
                                <i class="fa-solid fa-square-poll-vertical text-sm"></i>
                            </button>

                            <!-- Capture Results Modal Button -->
                            @if(!$isReadOnly)
                                <a href="{{ route('plan.version', $p->planCode) }}" class="p-2.5 rounded-xl text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition-colors" title="Versionar Plan (Crear V{{ $p->version + 1 }})">
                                    <i class="fa-solid fa-code-branch text-sm"></i>
                                </a>

                                <button wire:click="$dispatch('open-capture-results', { planCode: '{{ $p->planCode }}' })" class="p-2.5 rounded-xl text-gray-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors" title="Capturar Resultados Finales">
                                    <i class="fa-solid fa-clipboard-check text-sm"></i>
                                </button>

                                <button wire:click="openCancelModal('{{ $p->planCode }}', '{{ addslashes($p->customerName) }}')" class="p-2.5 rounded-xl text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors" title="Cerrar / Cancelar Plan">
                                    <i class="fa-solid fa-ban text-sm"></i>
                                </button>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if(!$isReadOnly)
                                <a href="{{ route('tracking.edit', $p->planCode) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold text-xs hover:bg-green-600 dark:hover:bg-green-400 transition-colors shadow-sm">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Seguimiento</span>
                                </a>
                            @else
                                <span class="text-xs font-bold text-gray-400 italic">Solo Lectura</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Cancel / Close Plan Modal -->
    @if($isCancelModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="relative w-full max-w-md bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl p-6 lg:p-8 space-y-6">
                <div class="text-center space-y-2">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">¿Seguro de cerrar / cancelar este plan?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Plan: <strong>{{ $cancellingCustomerName }} ({{ $cancellingPlanCode }})</strong></p>
                </div>

                <div class="space-y-1.5">
                    <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400">Motivo o Comentarios (Obligatorio) *</label>
                    <textarea wire:model="cancellationComment" rows="4" maxlength="245" placeholder="Escribe el motivo del cierre o cancelación..." class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none"></textarea>
                    <div class="flex justify-between items-center text-[10px] text-gray-400">
                        @error('cancellationComment') <span class="text-red-500 font-medium">{{ $message }}</span> @else <span></span> @enderror
                        <span>Máximo 245 caracteres</span>
                    </div>
                </div>

                <div class="flex items-center justify-center gap-3 pt-2">
                    <button type="button" wire:click="closeCancelModal" class="px-5 py-2.5 rounded-2xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 font-bold text-xs hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        Regresar
                    </button>
                    <button type="button" wire:click="confirmCancelPlan" wire:loading.attr="disabled" class="px-6 py-2.5 rounded-2xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs shadow-md transition-all">
                        <span wire:loading.remove wire:target="confirmCancelPlan">Confirmar Cierre</span>
                        <span wire:loading wire:target="confirmCancelPlan">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modals for PDF & Results -->
    <livewire:reports.proposal-pdf-modal />
    <livewire:reports.results-pdf-modal />
    <livewire:results.capture-results-modal />
</div>
