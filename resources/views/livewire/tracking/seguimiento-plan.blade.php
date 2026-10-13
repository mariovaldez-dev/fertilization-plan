<div class="space-y-6 pb-28">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#131B20] p-6 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-pen-to-square"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">
                        Folio #{{ $planCode }}
                    </span>
                    <span class="text-xs text-gray-400">| Edición de Seguimiento</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white">
                    {{ $nombrePlan ?: 'Plan de Fertilización' }}
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                    {{ $nombreCultivo ?: ($plan['currentCropName'] ?? 'Cultivo') }} @if(!empty($plan['hectares'])) • {{ $plan['hectares'] }} Ha @endif
                </p>
            </div>
        </div>

        <div>
            <a href="{{ route('seguimiento.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/15 text-gray-700 dark:text-gray-200 font-bold text-xs transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Volver a Clientes</span>
            </a>
        </div>
    </div>

    <!-- Error message banner -->
    @if($errorMessage)
        <div class="p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-lg shrink-0"></i>
            <p class="text-xs sm:text-sm font-semibold">{{ $errorMessage }}</p>
        </div>
    @endif

    <!-- Stages Grid -->
    @if(empty($etapas) && !$isLoading)
        <div class="bg-white dark:bg-[#131B20] p-12 rounded-3xl border border-gray-100 dark:border-white/5 text-center space-y-3">
            <i class="fa-solid fa-leaf text-gray-400 text-3xl"></i>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">No hay etapas para mostrar</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Por favor revisa la configuración del plan o vuelve a intentar.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($etapas as $eIndex => $etapa)
                <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 hover:border-green-500/30 rounded-3xl p-6 shadow-sm flex flex-col justify-between transition-all">
                    <div class="space-y-4">
                        <!-- Stage Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">
                                    {{ $etapa['nombreEtapa'] }}
                                </h3>
                            </div>
                            <button
                                type="button"
                                wire:click="eliminarEtapa({{ $eIndex }})"
                                class="text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 p-2 rounded-xl transition-colors"
                                title="Eliminar etapa"
                            >
                                <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                        </div>

                        <!-- Product List -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Productos de la Etapa</span>
                                <span class="text-[11px] font-bold text-gray-400">{{ count($etapa['productos'] ?? []) }} registrados</span>
                            </div>

                            @foreach($etapa['productos'] ?? [] as $pIndex => $prod)
                                @php
                                    $pTipo = $prod['tipo'] ?? 'impulsora';
                                    $isImpulsora = $pTipo === 'impulsora';
                                @endphp
                                <div class="relative p-4 rounded-2xl bg-gray-50 dark:bg-[#1A2227] border border-gray-200/60 dark:border-white/5 space-y-3">
                                    <!-- Delete Product Button -->
                                    <button
                                        type="button"
                                        wire:click="eliminarProducto({{ $eIndex }}, {{ $pIndex }})"
                                        class="absolute top-3 right-3 text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition-colors"
                                        title="Eliminar producto"
                                    >
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>

                                    <!-- Impulsora vs Otros Toggle Radio -->
                                    <div class="flex items-center gap-3 pr-8">
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-bold {{ $isImpulsora ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            <input
                                                type="radio"
                                                wire:model.live="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.tipo"
                                                value="impulsora"
                                                class="text-green-600 focus:ring-green-500"
                                            />
                                            <span>Impulsora</span>
                                        </label>

                                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs font-bold {{ !$isImpulsora ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            <input
                                                type="radio"
                                                wire:model.live="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.tipo"
                                                value="otros"
                                                class="text-green-600 focus:ring-green-500"
                                            />
                                            <span>Otros</span>
                                        </label>
                                    </div>

                                    <!-- Product Selector / Input -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Producto *</label>
                                        @if($isImpulsora)
                                            <!-- Custom Select con Buscador -->
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
                                                        if (!this.selected) return 'Seleccionar producto Impulsora...';
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
                                                    <span x-text="label" :class="{ 'text-gray-400': !selected, 'text-gray-900 dark:text-white font-bold': selected }" class="truncate pr-2"></span>
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
                                                            placeholder="Buscar producto..."
                                                            class="w-full px-3 py-1.5 text-xs bg-white dark:bg-[#131B20] border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:border-green-500 text-gray-900 dark:text-white placeholder-gray-400"
                                                        />
                                                    </div>
                                                    <div class="overflow-y-auto divide-y divide-gray-50 dark:divide-white/5 flex-1 max-h-40">
                                                        <template x-for="p in filteredProducts" :key="p.ItemCode || p.itemCode">
                                                            <button
                                                                type="button"
                                                                @click="selected = (p.ItemCode || p.itemCode); open = false; search = '';"
                                                                class="w-full text-left px-3 py-2 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-xs text-gray-700 dark:text-gray-300 transition-colors"
                                                                :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold': selected == (p.ItemCode || p.itemCode) }"
                                                            >
                                                                <span x-text="p.ItemName || p.name" class="truncate pr-2"></span>
                                                                <i x-show="selected == (p.ItemCode || p.itemCode)" class="fa-solid fa-check text-green-600 dark:text-green-400 text-xs shrink-0"></i>
                                                            </button>
                                                        </template>
                                                        <div x-show="filteredProducts.length === 0" class="px-3 py-3 text-center text-xs text-gray-400">
                                                            No se encontraron productos
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <input
                                                type="text"
                                                maxlength="99"
                                                wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.nombreProducto"
                                                placeholder="Nombre del producto..."
                                                class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs text-gray-900 dark:text-white focus:border-green-500 focus:outline-none"
                                            />
                                        @endif
                                    </div>

                                    <!-- Grid Dosis & Precio & Proveedor -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Dosis (Ton/Ha) *</label>
                                            <input
                                                type="number"
                                                step="0.0001"
                                                wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.dosis"
                                                placeholder="0.00"
                                                class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#131B20] text-xs font-bold text-gray-900 dark:text-white focus:border-green-500 focus:outline-none"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Precio ($)</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                max="99999.99"
                                                wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.precio"
                                                placeholder="0.00"
                                                @if($isImpulsora) disabled @endif
                                                class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 text-xs text-gray-900 dark:text-white focus:border-green-500 focus:outline-none {{ $isImpulsora ? 'bg-gray-100 dark:bg-white/5 text-gray-400 cursor-not-allowed' : 'bg-white dark:bg-[#131B20]' }}"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Proveedor</label>
                                            <input
                                                type="text"
                                                maxlength="250"
                                                wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.Proveedor"
                                                placeholder="Proveedor..."
                                                @if($isImpulsora) disabled @endif
                                                class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 text-xs text-gray-900 dark:text-white focus:border-green-500 focus:outline-none {{ $isImpulsora ? 'bg-gray-100 dark:bg-white/5 text-gray-400 cursor-not-allowed' : 'bg-white dark:bg-[#131B20]' }}"
                                            />
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <button
                                type="button"
                                wire:click="agregarProducto({{ $eIndex }})"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 font-bold text-xs hover:bg-green-100 transition-colors"
                            >
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Agregar Producto</span>
                            </button>
                        </div>

                        <hr class="border-gray-100 dark:border-white/5" />

                        <!-- Stage General Inputs (Fecha, Método, Comentario) -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Fecha de Aplicación *</label>
                                    <input
                                        type="date"
                                        wire:model="etapas.{{ $eIndex }}.fechaAplicacion"
                                        class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:border-green-500 focus:outline-none"
                                    />
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Método de Aplicación *</label>
                                    <select
                                        wire:model="etapas.{{ $eIndex }}.idAplicacion"
                                        class="w-full h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:border-green-500 focus:outline-none"
                                    >
                                        <option value="">Seleccionar método...</option>
                                        @foreach($metodosAplicacion as $m)
                                            <option value="{{ $m['Code'] ?? $m['code'] }}">{{ $m['Name'] ?? $m['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Comentarios / Observaciones</label>
                                <textarea
                                    wire:model="etapas.{{ $eIndex }}.comentario"
                                    rows="2"
                                    maxlength="253"
                                    placeholder="Observaciones de la aplicación en esta etapa..."
                                    class="w-full p-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:border-green-500 focus:outline-none resize-none"
                                ></textarea>
                                <div class="text-right text-[10px] text-gray-400">
                                    {{ strlen((string) ($etapa['comentario'] ?? '')) }} / 253
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Fixed Bottom Action Bar -->
    @if(!empty($etapas))
        <footer class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-[#131B20]/95 backdrop-blur-md border-t border-gray-100 dark:border-white/10 p-4 z-40 shadow-2xl">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                <a
                    href="{{ route('seguimiento.index') }}"
                    class="px-6 py-3 rounded-2xl bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-200 font-bold text-xs hover:bg-gray-200 dark:hover:bg-white/15 transition-colors flex items-center gap-2"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Regresar</span>
                </a>

                <button
                    type="button"
                    wire:click="guardarSeguimiento"
                    wire:loading.attr="disabled"
                    class="px-8 py-3 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-xs shadow-lg shadow-green-600/25 transition-all flex items-center gap-2 min-w-[200px] justify-center disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="guardarSeguimiento" class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Guardar Seguimiento</span>
                    </span>
                    <span wire:loading wire:target="guardarSeguimiento" class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Guardando...</span>
                    </span>
                </button>
            </div>
        </footer>
    @endif

    <!-- Success Modal -->
    @if($showSuccessModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="relative w-full max-w-sm bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl p-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center mx-auto text-2xl shadow-inner">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">¡Éxito!</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $successMessage }}</p>
                </div>
                <div class="pt-2">
                    <a
                        href="{{ route('seguimiento.index') }}"
                        class="block w-full py-3 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-xs shadow-lg shadow-green-600/25 transition-all text-center"
                    >
                        Aceptar
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
