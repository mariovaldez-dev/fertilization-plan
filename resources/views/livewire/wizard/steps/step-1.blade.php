<!-- PASO 1: DATOS GENERALES Y MAPA -->
<div class="space-y-6">
    <!-- Fila 1: Cliente, Asesor, Parcela -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-30">
        <!-- Cliente -->
        <div class="relative" x-data="{ open: true }" @click.outside="open = false" :class="{ 'z-50': open, 'z-20': !open }">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Cliente <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    type="text"
                    placeholder="Buscar Cliente (ej. Agricola, Juan...)"
                    wire:model.live.debounce.300ms="customerSearchText"
                    @keydown.enter.prevent="$wire.searchCustomers()"
                    @focus="open = true"
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border rounded-lg focus:outline-none {{ $errors->has('customerCode') ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 dark:border-white/10 focus:ring-2 focus:ring-green-500 focus:border-green-500' }} pr-10 text-sm text-gray-800 dark:text-white placeholder-gray-400"
                />
                <button
                    type="button" 
                    wire:click="searchCustomers"
                    class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-green-600 transition-colors focus:outline-none"
                    title="Buscar Cliente"
                >
                    <span wire:loading.remove wire:target="customerSearchText, searchCustomers">
                        <i class="fa-solid fa-magnifying-glass text-base"></i>
                    </span>
                    <span wire:loading wire:target="customerSearchText, searchCustomers">
                        <i class="fa-solid fa-spinner fa-spin text-green-600 text-base"></i>
                    </span>
                </button>
            </div>

            @if(!empty($customerSearchResults))
                <ul 
                    x-show="open" 
                    class="absolute z-[100] w-full bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 mt-1 rounded-lg shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5 text-sm"
                >
                    @foreach($customerSearchResults as $c)
                        <li 
                            wire:click="selectCustomer('{{ $c['CardCode'] }}', '{{ addslashes($c['CardName']) }}')"
                            @click="open = false"
                            class="px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 cursor-pointer text-gray-800 dark:text-gray-200 font-medium transition-colors"
                        >
                            {{ $c['CardName'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
            @error('customerCode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Asesor Técnico -->
        <div class="relative" x-data="{ open: true }" @click.outside="open = false" :class="{ 'z-50': open, 'z-20': !open }">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Asesor Técnico <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input
                    type="text"
                    placeholder="Buscar asesor..."
                    wire:model.live.debounce.300ms="advisorSearchText"
                    @keydown.enter.prevent="$wire.searchAdvisors()"
                    @focus="open = true"
                    class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border rounded-lg focus:outline-none {{ $errors->has('advisorId') ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 dark:border-white/10 focus:ring-2 focus:ring-green-500 focus:border-green-500' }} pr-10 text-sm text-gray-800 dark:text-white placeholder-gray-400"
                />
                <button
                    type="button" 
                    wire:click="searchAdvisors"
                    class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-400 hover:text-green-600 transition-colors focus:outline-none"
                    title="Buscar Asesor"
                >
                    <span wire:loading.remove wire:target="advisorSearchText, searchAdvisors">
                        <i class="fa-solid fa-magnifying-glass text-base"></i>
                    </span>
                    <span wire:loading wire:target="advisorSearchText, searchAdvisors">
                        <i class="fa-solid fa-spinner fa-spin text-green-600 text-base"></i>
                    </span>
                </button>
            </div>

            @if(!empty($advisorSearchResults))
                <ul 
                    x-show="open" 
                    class="absolute z-[100] w-full bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 mt-1 rounded-lg shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5 text-sm"
                >
                    @foreach($advisorSearchResults as $a)
                        <li 
                            wire:click="selectAdvisor('{{ $a['AdvisorId'] ?? $a['SlpCode'] ?? $a['Code'] ?? '' }}', '{{ addslashes($a['AdvisorName'] ?? $a['SlpName'] ?? $a['Name'] ?? '') }}', '{{ $a['Phone'] ?? '' }}')"
                            @click="open = false"
                            class="px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 cursor-pointer text-gray-800 dark:text-gray-200 font-medium transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-sm">{{ mb_strtoupper($a['AdvisorName'] ?? $a['SlpName'] ?? $a['Name'] ?? '', 'UTF-8') }}</p>
                                    @if(!empty($a['Email'] ?? ''))
                                        <p class="text-xs text-gray-400 dark:text-gray-400 font-normal">{{ $a['Email'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
            @error('advisorId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Parcela -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Parcela <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                placeholder="Nombre de la parcela"
                wire:model.live.debounce.300ms="parcela"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border rounded-lg focus:outline-none {{ $errors->has('parcela') ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 dark:border-white/10 focus:ring-2 focus:ring-green-500 focus:border-green-500' }} text-sm text-gray-800 dark:text-white placeholder-gray-400"
            />
            @error('parcela') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <!-- Fila 2: Superficie (ha), Tipo de suelo, Análisis de suelo -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-20">
        <!-- Superficie (ha) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Superficie (ha) <span class="text-red-500">*</span>
            </label>
            <input
                type="number"
                step="0.1"
                min="0.1"
                placeholder="Ej: 25.5"
                wire:model.live.debounce.300ms="hectares"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border rounded-lg focus:outline-none {{ $errors->has('hectares') ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 dark:border-white/10 focus:ring-2 focus:ring-green-500 focus:border-green-500' }} text-sm text-gray-800 dark:text-white placeholder-gray-400"
            />
            @error('hectares') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Tipo de suelo (Custom Select) -->
        <div class="relative" 
            x-data="{ 
                open: false, 
                selected: @entangle('soilType'),
                options: {{ json_encode($soilTypes) }},
                get label() {
                    if (!this.selected) return 'Seleccione...';
                    const found = this.options.find(o => o.Code == this.selected || o.Name == this.selected);
                    return found ? (found.Name || found.Code) : this.selected;
                }
            }" 
            @click.outside="open = false" 
            :class="{ 'z-50': open, 'z-20': !open }"
        >
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Tipo de suelo <span class="text-red-500">*</span>
            </label>
            <button
                type="button"
                @click="open = !open"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border rounded-lg flex items-center justify-between focus:outline-none {{ $errors->has('soilType') ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 dark:border-white/10 focus:ring-2 focus:ring-green-500 focus:border-green-500' }} text-sm transition-all text-left"
            >
                <span x-text="label" :class="{ 'text-gray-400': !selected, 'text-gray-800 dark:text-white font-medium': selected }"></span>
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
                class="absolute z-[100] w-full mt-1 bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl max-h-56 overflow-y-auto py-1 text-sm divide-y divide-gray-50 dark:divide-white/5"
                style="display: none;"
            >
                @foreach($soilTypes as $st)
                    @php
                        $val = $st['Code'] ?? $st['Name'];
                        $label = $st['Name'] ?? $st['Code'];
                    @endphp
                    <button
                        type="button"
                        @click="selected = '{{ addslashes((string)$val) }}'; open = false; $wire.set('soilType', '{{ addslashes((string)$val) }}');"
                        class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                        :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-bold': selected == '{{ addslashes((string)$val) }}' || selected == '{{ addslashes((string)($st['Name'] ?? '')) }}' }"
                    >
                        <span>{{ $label }}</span>
                        <i x-show="selected == '{{ addslashes((string)$val) }}' || selected == '{{ addslashes((string)($st['Name'] ?? '')) }}'" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
                    </button>
                @endforeach
            </div>
            @error('soilType') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <!-- Análisis de suelo (Custom Select) -->
        <div class="relative" x-data="{ open: false, selected: @entangle('analisisSuelo') }" @click.outside="open = false" :class="{ 'z-50': open, 'z-20': !open }">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Análisis de suelo <span class="text-red-500">*</span>
            </label>
            <button
                type="button"
                @click="open = !open"
                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-white/5 border rounded-lg flex items-center justify-between focus:outline-none {{ $errors->has('analisisSuelo') ? 'border-red-500 focus:ring-2 focus:ring-red-500' : 'border-gray-300 dark:border-white/10 focus:ring-2 focus:ring-green-500 focus:border-green-500' }} text-sm transition-all text-left"
            >
                <span x-text="selected ? selected : 'NO'" class="text-gray-800 dark:text-white font-medium"></span>
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
                class="absolute z-[100] w-full mt-1 bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl py-1 text-sm divide-y divide-gray-50 dark:divide-white/5"
                style="display: none;"
            >
                <button
                    type="button"
                    @click="selected = 'NO'; open = false; $wire.set('analisisSuelo', 'NO');"
                    class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                    :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-bold': selected === 'NO' }"
                >
                    <span>NO</span>
                    <i x-show="selected === 'NO'" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
                </button>
                <button
                    type="button"
                    @click="selected = 'SI'; open = false; $wire.set('analisisSuelo', 'SI');"
                    class="w-full text-left px-4 py-2.5 hover:bg-green-50 dark:hover:bg-white/5 flex items-center justify-between text-gray-700 dark:text-gray-200 transition-colors"
                    :class="{ 'bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 font-bold': selected === 'SI' }"
                >
                    <span>SI</span>
                    <i x-show="selected === 'SI'" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
                </button>
            </div>
            @error('analisisSuelo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <!-- Mapa Interactivo (Leaflet + Nominatim) -->
    <div 
        x-data="{
            map: null,
            marker: null,
            searchQuery: '',
            results: [],
            isSearching: false,
            debounceTimer: null,
            coordenadasDisplay: @js($coordenadasText) || 'Sin coordenadas marcadas',
            init() {
                this.$watch('$wire.currentStep', (step) => {
                    if (step === 1 && this.map) {
                        setTimeout(() => this.map.invalidateSize(), 150);
                    }
                });
            },
            getMarkerIcon() {
                return L.divIcon({
                    className: 'custom-leaflet-marker',
                    html: `<div style='display:flex;align-items:center;justify-content:center;width:34px;height:34px;background:#16a34a;color:#ffffff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #ffffff;box-shadow:0 3px 10px rgba(0,0,0,0.4);'><i class=\'fa-solid fa-location-dot\' style=\'transform:rotate(45deg);font-size:15px;\'></i></div>`,
                    iconSize: [34, 34],
                    iconAnchor: [17, 34],
                    popupAnchor: [0, -34]
                });
            },
            initMap() {
                if (typeof L === 'undefined') {
                    setTimeout(() => this.initMap(), 200);
                    return;
                }
                const container = document.getElementById('map-container');
                if (!container) {
                    setTimeout(() => this.initMap(), 200);
                    return;
                }
                
                if (window._planWizardMap) {
                    this.map = window._planWizardMap;
                    this.marker = window._planWizardMarker;
                    this.map.invalidateSize();
                    return;
                }

                if (container._leaflet_id) {
                    container._leaflet_id = null;
                }

                const currentLat = parseFloat(@js($latitude));
                const currentLng = parseFloat(@js($longitude));
                const hasValidCoords = !isNaN(currentLat) && !isNaN(currentLng);

                // Default center: Culiacán, Sinaloa (Grupo Impulsora Core)
                const centerLat = hasValidCoords ? currentLat : 24.8080;
                const centerLng = hasValidCoords ? currentLng : -107.3940;

                this.map = L.map('map-container', {
                    zoomControl: false
                }).setView([centerLat, centerLng], hasValidCoords ? 14 : 11);

                L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    maxZoom: 17,
                    attribution: '© OpenTopoMap, © OpenStreetMap',
                    crossOrigin: true
                }).addTo(this.map);

                if (hasValidCoords) {
                    this.marker = L.marker([currentLat, currentLng], { icon: this.getMarkerIcon() }).addTo(this.map);
                    this.coordenadasDisplay = 'Lat: ' + currentLat.toFixed(6) + ', Lng: ' + currentLng.toFixed(6);
                    setTimeout(() => this.captureMap(), 1000);
                } else {
                    this.marker = null;
                    this.coordenadasDisplay = 'Sin coordenadas marcadas';
                }

                window._planWizardMap = this.map;
                window._planWizardMarker = this.marker;
                window._capturePlanWizardMap = () => this.captureMap();

                this.map.on('click', (e) => {
                    this.updateMarker(e.latlng.lat, e.latlng.lng);
                });

                // Ensure map tiles render properly after DOM calculation
                setTimeout(() => {
                    if (this.map) this.map.invalidateSize();
                }, 300);
            },
            async captureMap() {
                const container = document.getElementById('map-container');
                if (!container || typeof html2canvas === 'undefined') return;
                try {
                    const canvas = await html2canvas(container, {
                        useCORS: true,
                        logging: false,
                        scale: 1.5,
                        allowTaint: false
                    });
                    const base64 = canvas.toDataURL('image/jpeg', 0.85);
                    if (base64 && base64.startsWith('data:image/')) {
                        $wire.setMapImage(base64);
                    }
                } catch(e) {
                    console.warn('Error al capturar mapa Leaflet:', e);
                }
            },
            updateMarker(lat, lng, shouldCenter = true) {
                const numLat = parseFloat(lat);
                const numLng = parseFloat(lng);
                if (isNaN(numLat) || isNaN(numLng)) return;

                const mapInstance = this.map || window._planWizardMap;
                let markerInstance = this.marker || window._planWizardMarker;

                if (!markerInstance && mapInstance) {
                    markerInstance = L.marker([numLat, numLng], { icon: this.getMarkerIcon() }).addTo(mapInstance);
                    this.marker = markerInstance;
                    window._planWizardMarker = markerInstance;
                } else if (markerInstance) {
                    markerInstance.setLatLng([numLat, numLng]);
                }

                // Si está muy alejado (zoom < 14), enfocar y centrar en la parcela seleccionada
                if (shouldCenter && mapInstance) {
                    const currentZoom = mapInstance.getZoom();
                    const targetZoom = currentZoom < 14 ? 14 : currentZoom;
                    mapInstance.setView([numLat, numLng], targetZoom, { animate: true });
                }

                this.coordenadasDisplay = 'Lat: ' + numLat.toFixed(6) + ', Lng: ' + numLng.toFixed(6);
                $wire.setCoordinates(numLat, numLng);

                clearTimeout(this.mapCaptureTimer);
                this.mapCaptureTimer = setTimeout(() => {
                    this.captureMap();
                }, 700);
            },
            handleSearchInput() {
                clearTimeout(this.debounceTimer);
                if (!this.searchQuery.trim() || this.searchQuery.length < 2) {
                    this.results = [];
                    return;
                }
                this.debounceTimer = setTimeout(() => {
                    this.searchPlaces();
                }, 350);
            },
            async searchPlaces() {
                if (!this.searchQuery.trim()) return;
                this.isSearching = true;
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}`);
                    this.results = await res.json();
                } catch (e) {
                    console.error('Error al buscar lugares', e);
                } finally {
                    this.isSearching = false;
                }
            },
            selectPlace(place) {
                const lat = parseFloat(place.lat);
                const lon = parseFloat(place.lon);
                if (!isNaN(lat) && !isNaN(lon)) {
                    const mapInstance = this.map || window._planWizardMap;
                    if (mapInstance) {
                        mapInstance.invalidateSize();
                        mapInstance.setView([lat, lon], 15, { animate: true });
                    }
                    this.updateMarker(lat, lon);
                }
                this.searchQuery = place.display_name;
                this.results = [];
            }
        }"
        x-init="$nextTick(() => initMap())"
        class="border {{ $errors->has('latitude') || $errors->has('longitude') ? 'border-red-500 ring-2 ring-red-500/20' : 'border-gray-200 dark:border-white/10' }} rounded-xl p-4 bg-gray-50/50 dark:bg-white/5 space-y-3 relative z-10 transition-colors"
    >
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
            <div>
                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <i class="fa-solid fa-map-location-dot {{ $errors->has('latitude') || $errors->has('longitude') ? 'text-red-500' : 'text-green-600' }}"></i> Ubicación Geográfica de la Parcela <span class="text-red-500">*</span>
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Haz clic en el mapa para marcar las coordenadas o busca la localidad</p>
            </div>
            
            <div class="text-xs font-mono {{ $errors->has('latitude') || $errors->has('longitude') ? 'bg-red-50 dark:bg-red-950/30 border-red-300 dark:border-red-800 text-red-700 dark:text-red-300' : 'bg-white dark:bg-[#1A2227] border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300' }} px-3 py-1.5 rounded-lg border flex items-center gap-1.5 shadow-sm transition-colors">
                <i class="fa-solid fa-location-crosshairs {{ $errors->has('latitude') || $errors->has('longitude') ? 'text-red-500' : 'text-green-600' }}"></i>
                <span x-text="coordenadasDisplay"></span>
            </div>
        </div>

        <!-- Buscador Nominatim en Mapa (Full Width) -->
        <div class="relative w-full h-80 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shadow-inner">
            <div class="absolute top-3 left-3 right-3 z-[400] flex items-center gap-2">
                <div class="relative flex-1">
                    <input
                        type="text"
                        x-ref="mapSearchInput"
                        x-model="searchQuery"
                        @input="handleSearchInput()"
                        @keydown.enter.prevent="searchPlaces()"
                        placeholder="Buscar ejido/rancho/calle…"
                        class="w-full h-10 text-sm pl-3.5 pr-9 rounded-lg shadow-sm bg-white/95 dark:bg-[#1A2227]/95 border border-gray-200 dark:border-white/10 focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-800 dark:text-white placeholder-gray-400"
                    />

                    <!-- Botón para limpiar búsqueda (X roja) -->
                    <button
                        x-show="searchQuery && searchQuery.trim().length > 0"
                        x-cloak
                        type="button"
                        @click.prevent.stop="searchQuery = ''; results = []; $refs.mapSearchInput?.focus()"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition-colors p-1 flex items-center justify-center cursor-pointer"
                        title="Limpiar búsqueda"
                    >
                        <i class="fa-solid fa-circle-xmark text-base"></i>
                    </button>

                    <div 
                        x-show="results.length > 0 || isSearching" 
                        x-cloak 
                        class="absolute mt-1 w-full bg-white dark:bg-[#1A2227] rounded-xl shadow-2xl border border-gray-200 dark:border-white/10 max-h-52 overflow-auto z-[500] divide-y divide-gray-100 dark:divide-white/5"
                    >
                        <template x-if="isSearching">
                            <div class="px-3 py-2 text-sm text-gray-500 flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-green-600"></i> Buscando...
                            </div>
                        </template>
                        <template x-for="item in results" :key="item.place_id">
                            <button
                                type="button"
                                @click.prevent.stop="selectPlace(item)"
                                @mousedown.prevent.stop="selectPlace(item)"
                                class="block w-full text-left px-3.5 py-2.5 text-xs hover:bg-green-50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-200 transition-colors cursor-pointer"
                                x-text="item.display_name"
                            ></button>
                        </template>
                    </div>
                </div>

                <button
                    type="button"
                    @click.prevent="searchPlaces()"
                    class="h-10 text-sm px-5 rounded-lg shadow-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-white font-medium transition-colors cursor-pointer border border-gray-200 dark:border-white/20 shrink-0 flex items-center justify-center"
                >
                    Buscar
                </button>
            </div>

            <!-- Canvas del Mapa con wire:ignore para evitar que Livewire destruya el canvas -->
            <div id="map-container" wire:ignore class="w-full h-full z-0"></div>
        </div>

        @if ($errors->has('latitude') || $errors->has('longitude'))
            <p class="text-xs text-red-600 dark:text-red-400 font-semibold flex items-center gap-1.5 mt-1">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ $errors->first('latitude') ?: $errors->first('longitude') }}</span>
            </p>
        @endif
    </div>
</div>
