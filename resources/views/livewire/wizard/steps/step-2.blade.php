<!-- PASO 2: CULTIVOS ANTERIOR Y ACTUAL -->
<div class="space-y-6">
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b border-gray-200 dark:border-white/10 pb-2">
        Cultivo anterior
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-30">
        <!-- Cultivo Anterior (Custom Select) -->
        <div class="relative" 
            x-data="{ 
                open: false, 
                selected: @entangle('previousCrop'),
                options: {{ json_encode($cropTypes) }},
                get label() {
                    if (!this.selected) return 'Seleccionar Cultivo...';
                    const found = this.options.find(o => o.Code == this.selected || o.Name == this.selected || (o.code && o.code == this.selected) || (o.name && o.name == this.selected));
                    return found ? (found.Name || found.name || found.Code || found.code) : this.selected;
                }
            }" 
            @click.outside="open = false" 
            :class="{ 'z-50': open, 'z-20': !open }"
        >
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Cultivo Anterior <span class="text-red-500">*</span>
            </label>
            <button
                type="button"
                @click="open = !open"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border rounded-lg flex items-center justify-between focus:outline-none {{ $errors->has('previousCrop') ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 dark:border-white/10 focus:ring-2 focus:ring-green-500 focus:border-green-500' }} text-sm transition-all text-left"
            >
                <span x-text="label" :class="{ 'text-gray-400': !selected, 'text-gray-800 dark:text-white font-medium': selected }"></span>
                <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute z-[100] w-full mt-1 bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl max-h-56 overflow-y-auto py-1 text-sm divide-y divide-gray-50 dark:divide-white/5"
                style="display: none;"
            >
                @foreach($cropTypes as $ct)
                    @php
                        $val = $ct['Code'] ?? $ct['code'] ?? $ct['Name'] ?? $ct['name'];
                        $label = $ct['Name'] ?? $ct['name'] ?? $ct['Code'] ?? $ct['code'];
                    @endphp
                    <button
                        type="button"
                        @click="selected = '{{ addslashes($val) }}'; open = false; $wire.set('previousCrop', '{{ addslashes($val) }}');"
                        class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                        :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-bold': selected === '{{ addslashes($val) }}' || selected === '{{ addslashes($label) }}' }"
                    >
                        <span>{{ $label }}</span>
                        <i x-show="selected === '{{ addslashes($val) }}' || selected === '{{ addslashes($label) }}'" class="fa-solid fa-check text-green-600 text-xs"></i>
                    </button>
                @endforeach
            </div>
            @error('previousCrop') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div wire:key="container-previous-crop-yield">
            <label for="previousCropYield" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Rendimiento real (ton/ha) <span class="text-red-500">*</span>
            </label>
            <input 
                type="number" 
                id="previousCropYield"
                name="previousCropYield"
                wire:key="input-previous-crop-yield"
                step="0.01" 
                min="0.01"
                placeholder="Ej: 12.5"
                wire:model.live.debounce.300ms="previousCropYield" 
                autocomplete="off"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border {{ $errors->has('previousCropYield') ? 'border-red-500' : 'border-gray-300 dark:border-white/10' }} rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm text-gray-800 dark:text-white"
            />
            @error('previousCropYield') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div wire:key="container-previous-crop-max-yield">
            <label for="previousCropMaxYield" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Rendimiento Máx. Ref. (ton/ha) <span class="text-red-500">*</span>
            </label>
            <input 
                type="number" 
                id="previousCropMaxYield"
                name="previousCropMaxYield"
                wire:key="input-previous-crop-max-yield"
                step="0.01" 
                min="0.01"
                placeholder="Ej: 15.0"
                wire:model.live.debounce.300ms="previousCropMaxYield" 
                autocomplete="off"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border {{ $errors->has('previousCropMaxYield') ? 'border-red-500' : 'border-gray-300 dark:border-white/10' }} rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm text-gray-800 dark:text-white"
            />
            @error('previousCropMaxYield') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 border-b border-gray-200 dark:border-white/10 pb-2 pt-4">
        Cultivo actual
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
        <!-- Cultivo Actual (Custom Select) -->
        <div class="relative" 
            x-data="{ 
                open: false, 
                selected: @entangle('currentCropCode').live,
                customerCrops: {{ json_encode($customerCrops) }},
                allCrops: {{ json_encode($cropTypes) }},
                get label() {
                    if (!this.selected) return 'Seleccionar Cultivo Actual...';
                    const cc = this.customerCrops.find(c => ((c.cultivoId && c.cultivoId == this.selected) || (c.code && c.code == this.selected)));
                    if (cc) return cc.nombreCultivo || cc.name;
                    const ac = this.allCrops.find(c => ((c.Code && c.Code == this.selected) || (c.code && c.code == this.selected) || (c.Name && c.Name == this.selected) || (c.name && c.name == this.selected)));
                    return ac ? (ac.Name || ac.name) : this.selected;
                }
            }" 
            @click.outside="open = false" 
            :class="{ 'z-50': open, 'z-10': !open }"
        >
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Cultivo actual <span class="text-red-500">*</span>
            </label>
            <button
                type="button"
                @click="open = !open"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border rounded-lg flex items-center justify-between focus:outline-none {{ $errors->has('currentCropCode') ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 dark:border-white/10 focus:ring-2 focus:ring-green-500 focus:border-green-500' }} text-sm transition-all text-left"
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
                class="absolute z-[100] w-full mt-1 bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl max-h-64 overflow-y-auto py-1 text-sm divide-y divide-gray-100 dark:divide-white/5"
                style="display: none;"
            >
                @if(!empty($customerCrops))
                    <div class="px-3 py-1.5 bg-green-50/70 dark:bg-green-500/10 text-green-800 dark:text-green-400 font-bold text-xs uppercase tracking-wider">
                        Cultivos del Cliente
                    </div>
                    @foreach($customerCrops as $cc)
                        @php
                            $cVal = $cc['cultivoId'] ?? $cc['code'];
                            $cName = $cc['nombreCultivo'] ?? $cc['name'];
                        @endphp
                        <button
                            type="button"
                            @click="selected = '{{ addslashes($cVal) }}'; open = false; $wire.set('currentCropCode', '{{ addslashes($cVal) }}');"
                            class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                            :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-bold': selected === '{{ addslashes($cVal) }}' }"
                        >
                            <span class="truncate">{{ $cName }}</span>
                            <i x-show="selected === '{{ addslashes($cVal) }}'" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
                        </button>
                    @endforeach
                @endif

                <div class="px-3 py-1.5 bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400 font-bold text-xs uppercase tracking-wider">
                    Todos los Cultivos
                </div>
                @foreach($cropTypes as $ct)
                    @php
                        $cVal = $ct['Code'] ?? $ct['code'];
                        $cName = $ct['Name'] ?? $ct['name'];
                    @endphp
                    <button
                        type="button"
                        @click="selected = '{{ addslashes($cVal) }}'; open = false; $wire.set('currentCropCode', '{{ addslashes($cVal) }}');"
                        class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                        :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-bold': selected === '{{ addslashes($cVal) }}' }"
                    >
                        <span class="truncate">{{ $cName }}</span>
                        <i x-show="selected === '{{ addslashes($cVal) }}'" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
                    </button>
                @endforeach
            </div>
            @error('currentCropCode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Fecha de Siembra -->
        <div wire:key="container-estimated-sowing-date">
            <label for="estimatedSowingDate" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Fecha de siembra <span class="text-red-500">*</span>
            </label>
            <input 
                type="date" 
                id="estimatedSowingDate"
                name="estimatedSowingDate"
                wire:key="input-estimated-sowing-date"
                wire:model.live="estimatedSowingDate" 
                autocomplete="off"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border {{ $errors->has('estimatedSowingDate') ? 'border-red-500' : 'border-gray-300 dark:border-white/10' }} rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm text-gray-800 dark:text-white"
            />
            @error('estimatedSowingDate') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Variedad de Semilla -->
        <div wire:key="container-seed-variety">
            <label for="seedVariety" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Variedad de semilla <span class="text-red-500">*</span>
            </label>
            <input 
                type="text" 
                id="seedVariety"
                name="seedVariety"
                wire:key="input-seed-variety"
                placeholder="Ej: DK-4018, Pioneer 3260..."
                wire:model.live.debounce.300ms="seedVariety" 
                autocomplete="off"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border {{ $errors->has('seedVariety') ? 'border-red-500' : 'border-gray-300 dark:border-white/10' }} rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm text-gray-800 dark:text-white placeholder-gray-400"
            />
            @error('seedVariety') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Meta de Rendimiento -->
        <div wire:key="container-target-yield">
            <label for="targetYield" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Objetivo de producción (ton/ha) <span class="text-red-500">*</span>
            </label>
            <input 
                type="number" 
                id="targetYield"
                name="targetYield"
                wire:key="input-target-yield"
                step="0.01" 
                min="0.01"
                placeholder="Ej: 14.0"
                wire:model.live.debounce.300ms="targetYield" 
                autocomplete="off"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border {{ $errors->has('targetYield') ? 'border-red-500' : 'border-gray-300 dark:border-white/10' }} rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm text-gray-800 dark:text-white"
            />
            @error('targetYield') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
