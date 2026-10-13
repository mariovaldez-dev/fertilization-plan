<div class="bg-gray-50 min-h-screen flex flex-col">
    <x-header />

    <main class="p-4 sm:p-6 md:p-8 flex flex-col items-center flex-1">
        <div class="w-full max-w-5xl bg-white p-6 sm:p-8 rounded-2xl shadow-lg space-y-6">
            
            <div class="flex items-center justify-between border-b pb-4">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-seedling text-green-600 text-2xl"></i>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Seguimiento de Plan #{{ $planCode }}</h1>
                        @if(!empty($plan['customerName']))
                            <p class="text-sm text-gray-500">{{ $plan['customerName'] }} | {{ $plan['currentCropName'] }} ({{ $plan['hectares'] }} Ha)</p>
                        @endif
                    </div>
                </div>

                <a 
                    href="{{ route('seguimiento.index') }}"
                    class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                >
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>

            @if($successMessage)
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <p class="text-sm font-semibold">{{ $successMessage }}</p>
                </div>
            @endif

            @if($errorMessage)
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-lg"></i>
                    <p class="text-sm font-semibold">{{ $errorMessage }}</p>
                </div>
            @endif

            <!-- Etapas List -->
            <div class="space-y-6">
                @foreach($etapas as $eIndex => $etapa)
                    <div class="border-2 border-green-200 rounded-2xl p-6 bg-white shadow-sm space-y-4">
                        <div class="flex justify-between items-center border-b pb-2">
                            <h3 class="text-lg font-bold text-green-700">{{ $etapa['nombreEtapa'] }}</h3>
                            <span class="text-xs font-semibold px-2.5 py-1 bg-green-100 text-green-800 rounded-full">
                                Etapa {{ $eIndex + 1 }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Custom Select Método de Aplicación -->
                            <div class="relative"
                                x-data="{
                                    open: false,
                                    selected: @entangle('etapas.' . $eIndex . '.idMetodo'),
                                    metodos: {{ json_encode($metodosAplicacion) }},
                                    get label() {
                                        if (!this.selected) return 'Seleccionar Método';
                                        const found = this.metodos.find(m => ((m.Code && m.Code == this.selected) || (m.Name && m.Name == this.selected)));
                                        return found ? found.Name : this.selected;
                                    }
                                }"
                                @click.outside="open = false"
                                :class="{ 'z-50': open, 'z-10': !open }"
                            >
                                <label class="block text-xs font-medium text-gray-600 mb-1">Método de Aplicación</label>
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="w-full p-2 bg-white border border-gray-300 rounded-lg text-sm flex items-center justify-between focus:ring-2 focus:ring-green-500 focus:outline-none transition-all text-left shadow-sm"
                                >
                                    <span x-text="label" class="truncate pr-2 font-medium text-gray-800"></span>
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
                                    class="absolute z-[100] w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-2xl max-h-56 overflow-y-auto py-1 text-sm divide-y divide-gray-50"
                                    style="display: none;"
                                >
                                    @foreach($metodosAplicacion as $m)
                                        @php
                                            $mCode = $m['Code'] ?? $m['Name'];
                                            $mName = $m['Name'];
                                        @endphp
                                        <button
                                            type="button"
                                            @click="selected = '{{ addslashes($mCode) }}'; open = false;"
                                            class="w-full text-left px-3 py-2 hover:bg-green-50 flex items-center justify-between text-xs text-gray-700 transition-colors"
                                            :class="{ 'bg-green-50 text-green-700 font-bold': selected === '{{ addslashes($mCode) }}' || selected === '{{ addslashes($mName) }}' }"
                                        >
                                            <span class="truncate pr-2">{{ $mName }}</span>
                                            <i x-show="selected === '{{ addslashes($mCode) }}' || selected === '{{ addslashes($mName) }}'" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Fecha de Aplicación</label>
                                <input 
                                    type="date" 
                                    wire:model="etapas.{{ $eIndex }}.fechaAplicacion" 
                                    class="w-full p-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Productos dentro de la etapa -->
                        <div class="space-y-3 pt-2">
                            <div class="flex justify-between items-center">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Productos Aplicados</h4>
                                <button
                                    type="button"
                                    wire:click="agregarProducto({{ $eIndex }})"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-green-600 hover:text-green-700 hover:bg-green-50 px-2.5 py-1 rounded-lg transition-colors"
                                >
                                    <i class="fa-solid fa-plus"></i> Agregar Producto
                                </button>
                            </div>

                            @foreach($etapa['productos'] as $pIndex => $prod)
                                <div class="relative border border-green-200 rounded-xl p-4 bg-green-50/50 space-y-3">
                                    <button
                                        type="button"
                                        wire:click="eliminarProducto({{ $eIndex }}, {{ $pIndex }})"
                                        class="absolute top-3 right-3 text-red-400 hover:text-red-600 p-1 rounded-full transition-colors"
                                        title="Eliminar producto"
                                    >
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>

                                    <!-- Selector Tipo: Impulsora vs Otros -->
                                    <div class="flex gap-4 pr-8">
                                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                            <input 
                                                type="radio" 
                                                wire:model.live="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.tipo" 
                                                value="impulsora" 
                                                class="text-green-600 focus:ring-green-500"
                                            />
                                            Impulsora
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                            <input 
                                                type="radio" 
                                                wire:model.live="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.tipo" 
                                                value="otros" 
                                                class="text-green-600 focus:ring-green-500"
                                            />
                                            Otros
                                        </label>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Producto *</label>
                                            @if(($prod['tipo'] ?? 'impulsora') === 'impulsora')
                                                <!-- Custom Select Producto con Búsqueda -->
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
                                                            if (!this.selected) return 'Seleccionar producto...';
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
                                                        class="w-full p-2 bg-white border border-gray-300 rounded-lg text-sm flex items-center justify-between focus:ring-2 focus:ring-green-500 focus:outline-none transition-all text-left shadow-sm"
                                                    >
                                                        <span x-text="label" :class="{ 'text-gray-400': !selected, 'text-gray-800 font-medium': selected }" class="truncate pr-2"></span>
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
                                                        class="absolute z-[100] left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-2xl max-h-56 overflow-hidden flex flex-col text-sm"
                                                        style="display: none;"
                                                    >
                                                        <div class="p-2 border-b border-gray-100 bg-gray-50/80">
                                                            <input
                                                                x-ref="searchInput"
                                                                type="text"
                                                                x-model="search"
                                                                placeholder="Buscar producto..."
                                                                class="w-full px-2.5 py-1 text-xs bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-800 placeholder-gray-400"
                                                            />
                                                        </div>
                                                        <div class="overflow-y-auto divide-y divide-gray-50 flex-1 max-h-44">
                                                            <template x-for="p in filteredProducts" :key="p.ItemCode || p.itemCode">
                                                                <button
                                                                    type="button"
                                                                    @click="selected = (p.ItemCode || p.itemCode); open = false; search = '';"
                                                                    class="w-full text-left px-3 py-2 hover:bg-green-50 flex items-center justify-between text-xs text-gray-700 transition-colors"
                                                                    :class="{ 'bg-green-50 text-green-700 font-bold': selected == (p.ItemCode || p.itemCode) }"
                                                                >
                                                                    <span x-text="p.ItemName || p.name" class="truncate pr-2"></span>
                                                                    <i x-show="selected == (p.ItemCode || p.itemCode)" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
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
                                                    wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.nombreProducto" 
                                                    placeholder="Nombre del producto..."
                                                    class="w-full p-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none"
                                                />
                                            @endif
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Dosis Aplicada *</label>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                wire:model="etapas.{{ $eIndex }}.productos.{{ $pIndex }}.dosisReal" 
                                                class="w-full p-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:outline-none"
                                            />
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center pt-6 border-t">
                <a 
                    href="{{ route('seguimiento.index') }}"
                    class="bg-gray-200 text-gray-700 font-bold py-2.5 px-6 rounded-lg hover:bg-gray-300 transition-colors text-sm"
                >
                    Regresar
                </a>

                <button
                    type="button"
                    wire:click="guardarSeguimiento"
                    wire:loading.attr="disabled"
                    class="bg-green-600 text-white font-bold py-2.5 px-8 rounded-lg hover:bg-green-700 transition-colors shadow-md disabled:opacity-50 flex items-center gap-2 text-sm"
                >
                    <span wire:loading.remove wire:target="guardarSeguimiento">Guardar Seguimiento</span>
                    <span wire:loading wire:target="guardarSeguimiento">
                        <i class="fa-solid fa-spinner fa-spin"></i> Guardando...
                    </span>
                </button>
            </div>
        </div>
    </main>
</div>
