<!-- PASO 3: PROPUESTA DE FERTILIZACIÓN -->
<div class="space-y-6">
    <div class="border-b border-gray-200 dark:border-white/10 pb-3 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                Propuesta de Aplicación de Productos
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Define las etapas fenológicas, productos recomendados, dosis y fechas estimadas de aplicación.
            </p>
        </div>
        <button
            type="button"
            wire:click="addStage"
            class="inline-flex items-center gap-2 bg-[#16a34a] hover:bg-[#15803d] text-white font-bold px-4 py-2 rounded-xl text-sm transition-colors shadow-sm cursor-pointer shrink-0"
        >
            <i class="fa-solid fa-plus text-sm"></i> Agregar Etapa
        </button>
    </div>

    @error('stages')
        <div class="p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-2xl flex items-center gap-3 text-red-600 dark:text-red-400 text-sm font-medium">
            <i class="fa-solid fa-circle-exclamation text-base shrink-0"></i>
            <span>{{ $message }}</span>
        </div>
    @enderror

    @if(empty($stages))
        <div class="text-center py-12 px-4 bg-gray-50/50 dark:bg-white/5 border-2 border-dashed border-gray-200 dark:border-white/10 rounded-3xl">
            <div class="w-16 h-16 mx-auto mb-4 bg-green-50 dark:bg-green-500/10 rounded-2xl flex items-center justify-center text-green-600 dark:text-green-400 text-2xl">
                <i class="fa-solid fa-seedling"></i>
            </div>
            <h4 class="text-base font-bold text-gray-800 dark:text-white mb-1">No hay etapas fenológicas configuradas</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-5">
                Selecciona un cultivo en el paso 2 para cargar la recomendación o agrega una etapa manualmente.
            </p>
            <button
                type="button"
                wire:click="addStage"
                class="inline-flex items-center gap-2 bg-[#16a34a] hover:bg-[#15803d] text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-colors shadow-sm cursor-pointer"
            >
                <i class="fa-solid fa-plus text-sm"></i> Agregar Primera Etapa
            </button>
        </div>
    @else
        <div class="grid gap-8 grid-cols-1 lg:grid-cols-2">
            @foreach($stages as $sIndex => $stage)
                @php
                    $stageTitle = $stage['stageName'] ?? $stage['nombreEtapa'] ?? ('Etapa ' . ($sIndex + 1));
                    $hasEmptyProductsError = empty($stage['products']) && $errors->has("stages.{$sIndex}.products");
                    $hasSpecificProductErrors = collect($errors->keys())->contains(fn($key) => str_starts_with($key, "stages.{$sIndex}.products."));
                    $hasStageProductsError = $hasEmptyProductsError || $hasSpecificProductErrors;
                @endphp
                <div wire:key="stage-card-{{ $sIndex }}" class="bg-white dark:bg-[#1A2227] border-2 {{ $hasStageProductsError ? 'border-red-400 dark:border-red-500/50' : 'border-green-300/90 dark:border-white/10' }} rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow space-y-4">
                    <!-- Encabezado de la Etapa -->
                    <div class="flex justify-between items-center pb-2 border-b border-gray-100 dark:border-white/5">
                        <h3 class="text-xl font-bold text-green-800 dark:text-green-400">
                            {{ $stageTitle }}
                        </h3>
                        <button
                            type="button"
                            wire:click="removeStage({{ $sIndex }})"
                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-colors p-1"
                            title="Eliminar etapa"
                        >
                            <i class="fa-regular fa-trash-can text-lg"></i>
                        </button>
                    </div>

                    <!-- Error si no hay productos en la etapa -->
                    @if(empty($stage['products']))
                        @error("stages.{$sIndex}.products")
                            <div class="p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl text-red-600 dark:text-red-400 text-xs flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation shrink-0"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    @endif

                    <!-- Listado de Productos -->
                    @if(!empty($stage['products']))
                        <div class="space-y-3 pt-2">
                            @foreach($stage['products'] as $pIndex => $prod)
                                <div wire:key="stage-{{ $sIndex }}-prod-{{ $pIndex }}" class="bg-green-50/70 dark:bg-white/5 border border-green-200/80 dark:border-white/10 rounded-2xl p-4 relative space-y-3" style="z-index: {{ 50 - $pIndex }};">
                                    <button
                                        type="button"
                                        wire:click="removeProductFromStage({{ $sIndex }}, {{ $pIndex }})"
                                        class="absolute top-3 right-3 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-1"
                                        title="Eliminar producto"
                                    >
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pr-6">
                                        <!-- Custom Select Producto con Búsqueda -->
                                        <div class="relative" 
                                            x-data="{ 
                                                open: false, 
                                                search: '',
                                                selected: @js($prod['itemCode'] ?? $prod['ItemCode'] ?? ''),
                                                products: {{ json_encode($availableProducts) }},
                                                get filteredProducts() {
                                                    if (!this.search.trim()) return this.products;
                                                    const q = this.search.toLowerCase();
                                                    return this.products.filter(p => 
                                                        ((p.ItemName || p.itemName || p.name || '') + ' ' + (p.ItemCode || p.itemCode || '')).toLowerCase().includes(q)
                                                    );
                                                },
                                                get label() {
                                                    if (!this.selected) return 'Seleccionar Producto...';
                                                    const found = this.products.find(p => ((p.ItemCode && p.ItemCode == this.selected) || (p.itemCode && p.itemCode == this.selected)));
                                                    return found ? (found.ItemName || found.itemName || found.name) : this.selected;
                                                },
                                                selectProduct(code, name) {
                                                    this.selected = code;
                                                    this.open = false;
                                                    this.search = '';
                                                    $wire.set('stages.{{ $sIndex }}.products.{{ $pIndex }}.itemCode', code);
                                                    $wire.set('stages.{{ $sIndex }}.products.{{ $pIndex }}.itemName', name);
                                                }
                                            }" 
                                            @click.outside="open = false" 
                                            :class="{ 'z-50': open, 'z-10': !open }"
                                        >
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Producto <span class="text-red-500">*</span>
                                            </label>
                                            <button
                                                type="button"
                                                @click="open = !open; if(open) $nextTick(() => $refs.searchInput?.focus())"
                                                class="w-full px-3 py-2 bg-white dark:bg-[#1A2227] border rounded-xl flex items-center justify-between focus:outline-none focus:ring-2 text-sm transition-all text-left shadow-sm {{ $errors->has("stages.{$sIndex}.products.{$pIndex}.itemCode") ? 'border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-white/10 focus:ring-green-500' }}"
                                            >
                                                <span x-text="label" :class="{ 'text-gray-400': !selected, 'text-gray-800 dark:text-white font-medium': selected }" class="truncate pr-2"></span>
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
                                                class="absolute z-[100] left-0 right-0 mt-1 bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl max-h-56 overflow-hidden flex flex-col text-sm"
                                                style="display: none;"
                                            >
                                                <div class="p-2 border-b border-gray-100 dark:border-white/5 bg-gray-50/80 dark:bg-white/5">
                                                    <input
                                                        x-ref="searchInput"
                                                        type="text"
                                                        x-model="search"
                                                        placeholder="Buscar producto..."
                                                        class="w-full px-3 py-1.5 text-xs bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-800 dark:text-white placeholder-gray-400"
                                                    />
                                                </div>
                                                <div class="overflow-y-auto divide-y divide-gray-50 dark:divide-white/5 flex-1 max-h-44">
                                                    <template x-for="p in filteredProducts" :key="p.ItemCode || p.itemCode">
                                                        <button
                                                            type="button"
                                                            @click="selectProduct(p.ItemCode || p.itemCode, p.ItemName || p.itemName || p.name)"
                                                            class="w-full text-left px-3 py-2 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-xs text-gray-700 dark:text-gray-200 transition-colors"
                                                            :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-bold': selected == (p.ItemCode || p.itemCode) }"
                                                        >
                                                            <span x-text="p.ItemName || p.itemName || p.name" class="truncate pr-2"></span>
                                                            <i x-show="selected == (p.ItemCode || p.itemCode)" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
                                                        </button>
                                                    </template>
                                                    <div x-show="filteredProducts.length === 0" class="px-3 py-3 text-center text-xs text-gray-400">
                                                         No se encontraron productos
                                                    </div>
                                                </div>
                                            </div>
                                            @error("stages.{$sIndex}.products.{$pIndex}.itemCode")
                                                <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Dosis (Kg/Ha o L/Ha) <span class="text-red-500">*</span>
                                            </label>
                                            <input 
                                                type="number" 
                                                step="0.0001" 
                                                min="0.0001"
                                                placeholder="0.00"
                                                wire:model.live.debounce.400ms="stages.{{ $sIndex }}.products.{{ $pIndex }}.dosePerHectare" 
                                                class="w-full px-3 py-2 bg-white dark:bg-[#1A2227] border rounded-xl text-sm text-gray-800 dark:text-white focus:ring-2 focus:outline-none shadow-sm {{ $errors->has("stages.{$sIndex}.products.{$pIndex}.dosePerHectare") ? 'border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-white/10 focus:ring-green-500' }}"
                                            />
                                            @error("stages.{$sIndex}.products.{$pIndex}.dosePerHectare")
                                                <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Botón Agregar Producto (debajo de la lista de productos) -->
                    <div>
                        <button 
                            type="button" 
                            wire:click="addProductToStage({{ $sIndex }})"
                            class="inline-flex items-center gap-2 bg-[#15803d] hover:bg-[#166534] text-white font-bold px-4 py-2 rounded-xl text-sm transition-colors shadow-sm cursor-pointer"
                        >
                            <i class="fa-solid fa-circle-plus text-base"></i> Agregar producto
                        </button>
                    </div>

                    <!-- Controles Inferiores (Fecha, Aplicación, Comentarios) -->
                    <div class="border-t border-gray-200/80 dark:border-white/10 pt-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Fecha de aplicación -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Fecha de aplicación <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="date" 
                                    wire:model.live="stages.{{ $sIndex }}.applicationDate" 
                                    class="w-full px-4 py-2.5 bg-white dark:bg-[#1A2227] border rounded-xl focus:ring-2 focus:outline-none text-sm text-gray-800 dark:text-white shadow-sm {{ $errors->has("stages.{$sIndex}.applicationDate") ? 'border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-white/10 focus:ring-green-500' }}"
                                />
                                @error("stages.{$sIndex}.applicationDate")
                                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Custom Select Aplicación -->
                            <div class="relative" 
                                x-data="{ 
                                    open: false, 
                                    selected: @js($stage['applicationMethod'] ?? 'Tradicional'),
                                    options: [
                                        { value: 'Tradicional', label: 'Tradicional' },
                                        { value: 'Fertirriego', label: 'Fertirriego' },
                                        { value: 'Foliar', label: 'Foliar' },
                                        { value: 'Al Suelo / Granulado', label: 'Al Suelo / Granulado' },
                                        { value: 'Drench', label: 'Drench' }
                                    ],
                                    get label() {
                                        const found = this.options.find(o => o.value == this.selected);
                                        return found ? found.label : (this.selected || 'Tradicional');
                                    },
                                    selectMethod(val) {
                                        this.selected = val;
                                        this.open = false;
                                        $wire.set('stages.{{ $sIndex }}.applicationMethod', val);
                                    }
                                }" 
                                @click.outside="open = false" 
                                :class="{ 'z-50': open, 'z-10': !open }"
                            >
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Aplicación <span class="text-red-500">*</span>
                                </label>
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="w-full px-4 py-2.5 bg-white dark:bg-[#1A2227] border rounded-xl flex items-center justify-between focus:outline-none focus:ring-2 text-sm text-gray-800 dark:text-white transition-all text-left shadow-sm {{ $errors->has("stages.{$sIndex}.applicationMethod") ? 'border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-white/10 focus:ring-green-500' }}"
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
                                    class="absolute z-[100] left-0 right-0 mt-1 bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl max-h-56 overflow-y-auto py-1 text-sm divide-y divide-gray-50 dark:divide-white/5"
                                    style="display: none;"
                                >
                                    <template x-for="opt in options" :key="opt.value">
                                        <button
                                            type="button"
                                            @click="selectMethod(opt.value)"
                                            class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-sm text-gray-700 dark:text-gray-200 transition-colors"
                                            :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-bold': selected === opt.value }"
                                        >
                                            <span x-text="opt.label"></span>
                                            <i x-show="selected === opt.value" class="fa-solid fa-check text-green-600 text-xs"></i>
                                        </button>
                                    </template>
                                </div>
                                @error("stages.{$sIndex}.applicationMethod")
                                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Comentarios -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Comentarios
                            </label>
                            <div x-data="{ count: {{ strlen((string)($stage['observations'] ?? '')) }} }">
                                <input 
                                    type="text" 
                                    maxlength="253" 
                                    wire:model="stages.{{ $sIndex }}.observations"
                                    @input="count = $el.value.length"
                                    placeholder="Escribir observaciones o notas..." 
                                    class="w-full px-4 py-2.5 bg-white dark:bg-[#1A2227] border rounded-xl focus:ring-2 focus:outline-none text-sm text-gray-800 dark:text-white placeholder-gray-400 {{ $errors->has("stages.{$sIndex}.observations") ? 'border-red-500 focus:ring-red-500' : 'border-gray-200 dark:border-white/10 focus:ring-green-500' }}"
                                />
                                <div class="text-right text-xs text-gray-400 mt-1">
                                    <span x-text="count">0</span> / 253
                                </div>
                                @error("stages.{$sIndex}.observations")
                                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
