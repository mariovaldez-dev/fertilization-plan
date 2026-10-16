<div class="bg-gray-50 dark:bg-[#0D1418] min-h-screen flex flex-col transition-colors duration-200" wire:init="cargarDetallePlan">
    <x-header />

    <main class="space-y-6 pb-32 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 w-full flex-1">
        <!-- Error message banner -->
        @if($errorMessage)
            <div class="p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl flex items-center justify-between gap-3 shadow-xs animate-fade-in">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-lg shrink-0"></i>
                    <p class="text-xs sm:text-sm font-bold">{{ $errorMessage }}</p>
                </div>
                <button type="button" wire:click="$set('errorMessage', '')" class="text-red-400 hover:text-red-600 p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if($isLoading)
            <!-- ================= BENTO HEADER SKELETON ================= -->
            <div class="bg-white dark:bg-[#131B20] p-6 lg:p-8 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm space-y-6 animate-pulse">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-28 bg-green-500/15 dark:bg-green-500/20 rounded-full"></div>
                            <div class="h-3 w-3 bg-gray-200 dark:bg-white/10 rounded-full"></div>
                            <div class="h-4 w-44 bg-gray-200 dark:bg-white/10 rounded-md"></div>
                        </div>
                        <div class="h-8 w-80 bg-gray-200 dark:bg-white/10 rounded-2xl"></div>
                        <div class="flex items-center gap-2.5 pt-1">
                            <div class="h-7 w-28 bg-gray-100 dark:bg-white/5 rounded-xl"></div>
                            <div class="h-7 w-32 bg-gray-100 dark:bg-white/5 rounded-xl"></div>
                            <div class="h-7 w-28 bg-gray-100 dark:bg-white/5 rounded-xl"></div>
                        </div>
                    </div>
                    <div class="h-10 w-36 bg-gray-100 dark:bg-white/5 rounded-2xl"></div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                    <div class="h-16 bg-gray-50/80 dark:bg-[#1A2227] rounded-2xl"></div>
                    <div class="h-16 bg-gray-50/80 dark:bg-[#1A2227] rounded-2xl"></div>
                    <div class="col-span-2 sm:col-span-1 h-16 bg-gray-50/80 dark:bg-[#1A2227] rounded-2xl"></div>
                </div>
            </div>

            <!-- ================= BENTO STAGES SKELETON ================= -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-pulse">
                @for($s = 0; $s < 4; $s++)
                    <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-green-500/20"></div>
                                <div class="h-5 w-44 bg-gray-200 dark:bg-white/10 rounded-lg"></div>
                            </div>
                            <div class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-white/5"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="h-10 bg-gray-50 dark:bg-[#1A2227] rounded-2xl"></div>
                            <div class="h-10 bg-gray-50 dark:bg-[#1A2227] rounded-2xl"></div>
                        </div>
                        <div class="space-y-3">
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#1A2227] space-y-3">
                                <div class="h-7 w-40 bg-gray-200 dark:bg-white/10 rounded-xl"></div>
                                <div class="h-10 w-full bg-white dark:bg-[#131B20] rounded-xl"></div>
                                <div class="grid grid-cols-3 gap-2 pt-1">
                                    <div class="h-9 bg-gray-200 dark:bg-white/10 rounded-xl"></div>
                                    <div class="h-9 bg-gray-200 dark:bg-white/10 rounded-xl"></div>
                                    <div class="h-9 bg-gray-200 dark:bg-white/10 rounded-xl"></div>
                                </div>
                            </div>
                        </div>
                        <div class="h-14 bg-gray-50 dark:bg-[#1A2227] rounded-2xl"></div>
                    </div>
                @endfor
            </div>
        @else
            <!-- ================= BENTO HEADER REAL ================= -->
            <div class="bg-white dark:bg-[#131B20] p-6 lg:p-8 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Left: Plan details & badges -->
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">
                                <i class="fa-solid fa-leaf text-[10px]"></i>
                                Folio #{{ $planCode }}
                            </span>
                            <span class="text-xs text-gray-400 font-semibold">•</span>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                Bitácora de Aplicaciones y Seguimiento
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                            {{ $nombrePlan ?: 'Plan de Fertilización' }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 pt-1">
                            @if(!empty($nombreCultivo) || !empty($plan['currentCropName']))
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 font-bold">
                                    <i class="fa-solid fa-wheat-awn text-amber-500"></i>
                                    {{ $nombreCultivo ?: ($plan['currentCropName'] ?? 'Cultivo') }}
                                </span>
                            @endif

                            @if(!empty($plan['hectares']))
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 font-bold">
                                    <i class="fa-solid fa-ruler-combined text-blue-500"></i>
                                    {{ $plan['hectares'] }} Hectáreas
                                </span>
                            @endif

                            @if(!empty($plan['season']))
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 font-bold">
                                    <i class="fa-solid fa-calendar-days text-purple-500"></i>
                                    Ciclo {{ $plan['season'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Return button -->
                    <div class="shrink-0">
                        <a
                            href="{{ route('seguimiento.index') }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/15 text-gray-700 dark:text-gray-200 font-bold text-xs transition-all shadow-xs"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Volver a Clientes</span>
                        </a>
                    </div>
                </div>

                <!-- Bento Summary Metric Pills -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                    <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-[#1A2227] border border-gray-100 dark:border-white/5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Etapas del Plan</span>
                            <span class="text-base font-black text-gray-900 dark:text-white">{{ $this->totalStagesCount }}</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-gray-50/80 dark:bg-[#1A2227] border border-gray-100 dark:border-white/5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Productos</span>
                            <span class="text-base font-black text-gray-900 dark:text-white">{{ $this->totalProductsCount }}</span>
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1 p-3.5 rounded-2xl bg-gray-50/80 dark:bg-[#1A2227] border border-gray-100 dark:border-white/5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <div>
                            <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Estado</span>
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">En Seguimiento</span>
                        </div>
                    </div>
                </div>
            </div>

            @if(empty($etapas))
                <div class="bg-white dark:bg-[#131B20] p-16 rounded-3xl border border-gray-100 dark:border-white/5 text-center space-y-4 shadow-sm">
                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-white/5 text-gray-400 flex items-center justify-center mx-auto text-2xl">
                        <i class="fa-solid fa-leaf"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">No hay etapas registradas para este plan</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                            No se encontraron recomendaciones previas ni etapas guardadas. Regresa a la lista o verifica la configuración del plan.
                        </p>
                    </div>
                    <a
                        href="{{ route('seguimiento.index') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-xs shadow-md transition-all"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Regresar a Clientes</span>
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($etapas as $eIndex => $etapa)
                <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 hover:border-green-500/30 rounded-3xl p-6 shadow-sm flex flex-col justify-between transition-all space-y-5">
                    <div class="space-y-5">
                        <!-- Stage Header Bento Bar -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-green-500/10 text-green-600 dark:text-green-400 font-mono font-black text-xs flex items-center justify-center border border-green-500/20">
                                    {{ sprintf('%02d', $eIndex + 1) }}
                                </span>
                                <div>
                                    <h3 class="text-base font-black text-gray-900 dark:text-white tracking-tight">
                                        {{ $etapa['nombreEtapa'] }}
                                    </h3>
                                    <span class="text-[11px] font-bold text-gray-400">
                                        {{ count($etapa['productos'] ?? []) }} {{ count($etapa['productos'] ?? []) === 1 ? 'producto' : 'productos' }} asignados
                                    </span>
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="eliminarEtapa({{ $eIndex }})"
                                class="w-8 h-8 rounded-xl text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 flex items-center justify-center transition-colors"
                                title="Eliminar etapa del seguimiento"
                            >
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>

                        <!-- General Stage Configuration (Fecha & Método) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-2xl bg-gray-50/70 dark:bg-[#1A2227] border border-gray-100 dark:border-white/5">
                            <!-- Fecha de Aplicación -->
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    Fecha de Aplicación *
                                </label>
                                <div class="relative">
                                    <input
                                        type="date"
                                        wire:model="etapas.{{ $eIndex }}.fechaAplicacion"
                                        class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-semibold text-gray-900 dark:text-white focus:border-green-500 focus:outline-none transition-colors"
                                    />
                                </div>
                            </div>

                            <!-- Método de Aplicación -->
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    Método de Aplicación *
                                </label>
                                <div class="relative">
                                    <select
                                        wire:model="etapas.{{ $eIndex }}.idAplicacion"
                                        class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-semibold text-gray-900 dark:text-white focus:border-green-500 focus:outline-none transition-colors"
                                    >
                                        <option value="">Seleccionar método...</option>
                                        @foreach($metodosAplicacion as $m)
                                            <option value="{{ $m['Code'] ?? $m['code'] }}">{{ $m['Name'] ?? $m['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Product List Sub-Cards -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    Productos Aplicados en Campo
                                </span>
                                <span class="text-[10px] font-medium text-gray-400">
                                    Impulsora / Terceros
                                </span>
                            </div>

                            @foreach($etapa['productos'] ?? [] as $pIndex => $prod)
                                @php
                                    $pTipo = $prod['tipo'] ?? 'impulsora';
                                    $isImpulsora = $pTipo === 'impulsora';
                                @endphp
                                <div class="relative p-4 rounded-2xl bg-gray-50/70 dark:bg-[#1A2227] border border-gray-200/60 dark:border-white/5 space-y-3 transition-all hover:border-gray-300 dark:hover:border-white/10">
                                    <!-- Delete Product Button -->
                                    <button
                                        type="button"
                                        wire:click="eliminarProducto({{ $eIndex }}, {{ $pIndex }})"
                                        class="absolute top-3.5 right-3.5 w-6 h-6 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 flex items-center justify-center transition-colors"
                                        title="Eliminar producto"
                                    >
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>

                                    <!-- Apple-style Segmented Capsule Toggle (Impulsora vs Otros) -->
                                    <div class="pr-8">
                                        <div class="inline-flex p-1 bg-gray-200/70 dark:bg-[#131B20] rounded-xl border border-gray-200 dark:border-white/5">
                                            <button
                                                type="button"
                                                wire:click="$set('etapas.{{ $eIndex }}.productos.{{ $pIndex }}.tipo', 'impulsora')"
                                                class="px-3 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ $isImpulsora ? 'bg-white dark:bg-green-600 text-green-700 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
                                            >
                                                <i class="fa-solid fa-certificate text-[10px]"></i>
                                                <span>Grupo Impulsora</span>
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="$set('etapas.{{ $eIndex }}.productos.{{ $pIndex }}.tipo', 'otros')"
                                                class="px-3 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ !$isImpulsora ? 'bg-white dark:bg-amber-600 text-amber-700 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
                                            >
                                                <i class="fa-solid fa-tag text-[10px]"></i>
                                                <span>Otros Proveedores</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Product Name / Select -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">
                                            Producto {{ $isImpulsora ? 'Oficial SAP *' : 'Comercial / Externo *' }}
                                        </label>

                                        @if($isImpulsora)
                                            <!-- Custom Select with Search -->
                                            <div class="relative"
                                                x-data="{
                                                    open: false,
                                                    search: '',
                                                    selected: @entangle('etapas.' . $eIndex . '.productos.' . $pIndex . '.idProducto'),
                                                    products: {{ json_encode($productosList) }},
                                                    get filteredProducts() {
                                                        if (!this.search.trim()) return this.products;
                                                        const q = this.search.toLowerCase();
                                                        return this.products.filter(p => 
                                                            ((p.ItemName || p.name || '') + ' ' + (p.ItemCode || p.itemCode || '')).toLowerCase().includes(q)
                                                        );
                                                    },
                                                    get label() {
                                                        if (!this.selected) return 'Seleccionar fertilizante Impulsora...';
                                                        const found = this.products.find(p => ((p.ItemCode && p.ItemCode == this.selected) || (p.itemCode && p.itemCode == this.selected)));
                                                        return found ? (found.ItemName || found.name) : this.selected;
                                                    }
                                                }"
                                                @click.outside="open = false"
                                                :class="{ 'z-50': open, 'z-10': !open }"
                                            >
                                                <button
                                                    type="button"
                                                    @click="open = !open; if(open) $nextTick(() => $refs.searchInput?.focus())"
                                                    class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs flex items-center justify-between text-gray-900 dark:text-white focus:border-green-500 focus:outline-none transition-all text-left shadow-sm"
                                                >
                                                    <span x-text="label" :class="{ 'text-gray-400 font-normal': !selected, 'text-gray-900 dark:text-white font-bold': selected }" class="truncate pr-2"></span>
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
                                                    class="absolute z-[100] left-0 right-0 mt-1 bg-white dark:bg-[#1A2227] border border-gray-100 dark:border-white/10 rounded-2xl shadow-2xl max-h-56 overflow-hidden flex flex-col text-xs"
                                                    style="display: none;"
                                                >
                                                    <div class="p-2 border-b border-gray-100 dark:border-white/5 bg-gray-50/80 dark:bg-black/20">
                                                        <input
                                                            x-ref="searchInput"
                                                            type="text"
                                                            x-model="search"
                                                            placeholder="Buscar fertilizante..."
                                                            class="w-full px-3 py-1.5 text-xs bg-white dark:bg-[#131B20] border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:border-green-500 text-gray-900 dark:text-white placeholder-gray-400"
                                                        />
                                                    </div>
                                                    <div class="overflow-y-auto divide-y divide-gray-50 dark:divide-white/5 flex-1 max-h-40">
                                                        <template x-for="p in filteredProducts" :key="p.ItemCode || p.itemCode">
                                                            <button
                                                                type="button"
                                                                @click="selected = (p.ItemCode || p.itemCode); $wire.seleccionarProductoImpulsora({{ $eIndex }}, {{ $pIndex }}, p.ItemCode || p.itemCode); open = false; search = '';"
                                                                class="w-full text-left px-3 py-2 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-xs text-gray-700 dark:text-gray-300 transition-colors"
                                                                :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold': selected == (p.ItemCode || p.itemCode) }"
                                                            >
                                                                <span x-text="p.ItemName || p.name" class="truncate pr-2"></span>
                                                                <i x-show="selected == (p.ItemCode || p.itemCode)" class="fa-solid fa-check text-green-600 dark:text-green-400 text-xs shrink-0"></i>
                                                            </button>
                                                        </template>
                                                        <div x-show="filteredProducts.length === 0" class="px-3 py-3 text-center text-xs text-gray-400">
                                                            No se encontraron fertilizantes
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <input
                                                type="text"
                                                maxlength="99"
                                                wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.nombreProducto"
                                                placeholder="Ej: Fertilizante Foliar 20-20-20..."
                                                class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-semibold text-gray-900 dark:text-white focus:border-green-500 focus:outline-none"
                                            />
                                        @endif
                                    </div>

                                    <!-- Grid Dosis & Precio & Proveedor -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <!-- Dosis -->
                                        <div class="space-y-1">
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                                Dosis Aplicada *
                                            </label>
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    step="0.0001"
                                                    min="0.0001"
                                                    wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.dosis"
                                                    placeholder="0.00"
                                                    class="w-full h-10 px-3 pr-14 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-mono font-bold text-gray-900 dark:text-white focus:border-green-500 focus:outline-none"
                                                />
                                                <span class="absolute right-3 top-2.5 text-[10px] font-bold text-gray-400">Ton/Ha</span>
                                            </div>
                                        </div>

                                        <!-- Precio Unitario -->
                                        <div class="space-y-1">
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                                Precio Unitario ($)
                                            </label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-2.5 text-xs font-bold text-gray-400">$</span>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="99999.99"
                                                    wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.precio"
                                                    placeholder="0.00"
                                                    @if($isImpulsora) disabled @endif
                                                    class="w-full h-10 pl-7 pr-3 rounded-xl border border-gray-200 dark:border-white/10 text-xs font-mono font-bold text-gray-900 dark:text-white focus:border-green-500 focus:outline-none {{ $isImpulsora ? 'bg-gray-100 dark:bg-white/5 text-gray-400 cursor-not-allowed border-dashed' : 'bg-white dark:bg-[#131B20]' }}"
                                                />
                                            </div>
                                        </div>

                                        <!-- Proveedor -->
                                        <div class="space-y-1">
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                                Proveedor
                                            </label>
                                            <input
                                                type="text"
                                                maxlength="250"
                                                wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.Proveedor"
                                                placeholder="Proveedor..."
                                                @if($isImpulsora) disabled @endif
                                                class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 text-xs font-semibold text-gray-900 dark:text-white focus:border-green-500 focus:outline-none {{ $isImpulsora ? 'bg-gray-100 dark:bg-white/5 text-gray-400 cursor-not-allowed border-dashed' : 'bg-white dark:bg-[#131B20]' }}"
                                            />
                                        </div>
                                    </div>

                                    @if($isImpulsora)
                                        <div class="flex items-center gap-2 pt-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">
                                            <i class="fa-solid fa-circle-check text-xs"></i>
                                            <span>Proveedor institucional: Grupo Impulsora (Tarifa oficial SAP)</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <!-- Add Product Button -->
                            <button
                                type="button"
                                wire:click="agregarProducto({{ $eIndex }})"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-green-50 hover:bg-green-100 dark:bg-green-500/10 dark:hover:bg-green-500/15 text-green-700 dark:text-green-300 font-bold text-xs transition-colors border border-green-500/20"
                            >
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Agregar Producto a la Etapa</span>
                            </button>
                        </div>

                        <!-- Stage Observations / Comments -->
                        <div class="space-y-1.5 pt-2 border-t border-gray-100 dark:border-white/5">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                Observaciones / Notas Agronómicas de Campo
                            </label>
                            <textarea
                                wire:model="etapas.{{ $eIndex }}.comentario"
                                rows="2"
                                maxlength="253"
                                placeholder="Condiciones del cultivo, humedad, observaciones técnicas..."
                                class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50/70 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:border-green-500 focus:outline-none resize-none transition-colors"
                            ></textarea>
                            <div class="text-right text-[10px] text-gray-400">
                                {{ strlen((string) ($etapa['comentario'] ?? '')) }} / 253 caracteres
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endif
</main>

    <!-- ================= FIXED BOTTOM ACTION BAR ================= -->
    @if(!empty($etapas))
        <footer class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-[#131B20]/95 backdrop-blur-md border-t border-gray-200/80 dark:border-white/10 p-4 z-40 shadow-2xl">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-500/10 text-green-600 dark:text-green-400 font-bold">
                        <i class="fa-solid fa-clipboard-list"></i>
                        {{ $this->totalStagesCount }} Etapas
                    </span>
                    <span>•</span>
                    <span class="font-medium">{{ $this->totalProductsCount }} productos listos para sincronizar</span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a
                        href="{{ route('seguimiento.index') }}"
                        class="px-5 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-200 font-bold text-xs hover:bg-gray-200 dark:hover:bg-white/15 transition-colors flex items-center justify-center gap-2"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Cancelar</span>
                    </a>

                    <button
                        type="button"
                        wire:click="guardarSeguimiento"
                        wire:loading.attr="disabled"
                        class="flex-1 sm:flex-none px-8 py-3 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-xs shadow-lg shadow-green-600/25 transition-all flex items-center justify-center gap-2 min-w-[220px] disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="guardarSeguimiento" class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Guardar Registro de Seguimiento</span>
                        </span>
                        <span wire:loading wire:target="guardarSeguimiento" class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                            <span>Guardando en Servidor...</span>
                        </span>
                    </button>
                </div>
            </div>
        </footer>
    @endif

    <!-- ================= SUCCESS MODAL ================= -->
    @if($showSuccessModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4 backdrop-blur-xs" x-cloak>
            <div class="relative w-full max-w-sm bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl p-6 text-center space-y-5 animate-fade-in">
                <div class="w-16 h-16 rounded-full bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center mx-auto text-3xl shadow-inner">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div class="space-y-1">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">
                        Folio #{{ $planCode }}
                    </span>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white pt-1">¡Seguimiento Guardado!</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                        {{ $successMessage ?: 'La bitácora de aplicaciones de campo se ha sincronizado exitosamente.' }}
                    </p>
                </div>

                <div class="pt-2">
                    <a
                        href="{{ route('seguimiento.index') }}"
                        class="block w-full py-3.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-xs shadow-lg shadow-green-600/25 transition-all text-center"
                    >
                        Volver a Clientes
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
