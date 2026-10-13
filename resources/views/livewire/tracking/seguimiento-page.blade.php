<div class="bg-gray-50 min-h-screen flex flex-col">
    <x-header />

    <main class="p-4 sm:p-6 md:p-8 flex flex-col items-center flex-1">
        <div class="w-full max-w-5xl bg-white p-6 rounded-2xl shadow-lg">
            <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">
                Seguimiento de Planes
            </h1>

            <!-- FILTROS -->
            <div class="flex flex-col gap-3 mb-4 md:flex-row md:items-center">
                @if(!empty($zonas))
                    <div class="relative w-full md:w-56"
                        x-data="{
                            open: false,
                            selected: @entangle('zona').live,
                            zonas: {{ json_encode($zonas) }},
                            get label() {
                                if (!this.selected) return 'Selecciona zona';
                                const z = this.zonas.find(item => (item.Code == this.selected || item.code == this.selected));
                                return z ? (z.Name || z.name) : this.selected;
                            }
                        }"
                        @click.outside="open = false"
                        :class="{ 'z-50': open, 'z-10': !open }"
                    >
                        <button
                            type="button"
                            @click="open = !open"
                            class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg flex items-center justify-between text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm transition-all text-left"
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
                            class="absolute z-[100] w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-2xl max-h-56 overflow-y-auto py-1 text-sm divide-y divide-gray-50"
                            style="display: none;"
                        >
                            <button
                                type="button"
                                @click="selected = ''; open = false;"
                                class="w-full text-left px-4 py-2 hover:bg-green-50 flex items-center justify-between text-gray-700 transition-colors"
                                :class="{ 'bg-green-50 text-green-700 font-bold': !selected }"
                            >
                                <span>Todas las Zonas</span>
                                <i x-show="!selected" class="fa-solid fa-check text-green-600 text-xs"></i>
                            </button>
                            @foreach($zonas as $z)
                                @php
                                    $zCode = $z['Code'] ?? $z['code'];
                                    $zName = $z['Name'] ?? $z['name'];
                                @endphp
                                <button
                                    type="button"
                                    @click="selected = '{{ addslashes($zCode) }}'; open = false;"
                                    class="w-full text-left px-4 py-2 hover:bg-green-50 flex items-center justify-between text-gray-700 transition-colors"
                                    :class="{ 'bg-green-50 text-green-700 font-bold': selected === '{{ addslashes($zCode) }}' }"
                                >
                                    <span class="truncate pr-2">{{ $zName }}</span>
                                    <i x-show="selected === '{{ addslashes($zCode) }}'" class="fa-solid fa-check text-green-600 text-xs shrink-0"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <input
                    type="text"
                    placeholder="Buscar cliente..."
                    class="border rounded px-3 py-2 w-full md:flex-1 border-gray-300 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none"
                    wire:model.live.debounce.300ms="texto"
                />
            </div>

            <!-- TABLA -->
            <div class="border rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100 text-gray-700 text-sm">
                        <tr>
                            <th class="p-3 text-left font-semibold">Código</th>
                            <th class="p-3 text-left font-semibold">Cliente</th>
                            <th class="p-3 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 text-sm">
                        @forelse($clientes as $cliente)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-3 text-gray-600">{{ $cliente['CardCode'] }}</td>
                                <td class="p-3 font-medium text-gray-800">{{ $cliente['CardName'] }}</td>
                                <td class="p-3 text-center">
                                    <button
                                        type="button"
                                        wire:click="toggleCliente('{{ $cliente['CardCode'] }}')"
                                        class="bg-green-600 text-white px-4 py-1.5 rounded hover:bg-green-700 transition-colors text-xs font-semibold"
                                    >
                                        Ver detalles
                                    </button>
                                </td>
                            </tr>

                            @if($expandedClient === $cliente['CardCode'])
                                <tr class="bg-gray-50">
                                    <td colspan="3" class="p-4">
                                        @if($loadingPlanes === $cliente['CardCode'])
                                            <p class="text-gray-500 text-center py-4 flex items-center justify-center gap-2">
                                                <i class="fa-solid fa-spinner fa-spin text-green-600"></i> Cargando planes…
                                            </p>
                                        @elseif(!empty($planesPorCliente[$cliente['CardCode']]))
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                @foreach($planesPorCliente[$cliente['CardCode']] as $plan)
                                                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm space-y-3">
                                                        <div class="flex justify-between items-start">
                                                            <div>
                                                                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full {{ $plan->status->name === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                                    {{ $plan->status->label() }}
                                                                </span>
                                                                <h4 class="text-base font-bold text-gray-800 mt-1">Plan #{{ $plan->planCode }}</h4>
                                                            </div>
                                                            <span class="text-xs text-gray-500">v{{ $plan->version }}</span>
                                                        </div>

                                                        <div class="text-xs text-gray-600 space-y-1">
                                                            <p><span class="font-semibold">Cultivo:</span> {{ $plan->currentCropName ?? 'N/A' }}</p>
                                                            <p><span class="font-semibold">Temporada:</span> {{ $plan->season }}</p>
                                                            <p><span class="font-semibold">Superficie:</span> {{ $plan->hectares }} Ha</p>
                                                            <p><span class="font-semibold">Asesor:</span> {{ $plan->advisorName ?: 'N/A' }}</p>
                                                        </div>

                                                        <div class="pt-2 border-t flex flex-wrap gap-2">
                                                            <a 
                                                                href="{{ route('tracking.edit', ['planCode' => $plan->planCode]) }}" 
                                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg text-xs font-bold transition-colors"
                                                            >
                                                                <i class="fa-solid fa-pen-to-square"></i> Seguimiento
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-center py-4">Este cliente no tiene planes activos.</p>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="3" class="p-6 text-center text-gray-500">
                                    @if($isLoading)
                                        <i class="fa-solid fa-spinner fa-spin text-green-600 text-lg"></i> Cargando clientes…
                                    @else
                                        No se encontraron clientes.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINADOR -->
            @if($totalPages > 1)
                <div class="flex justify-center gap-4 mt-6 items-center text-sm">
                    <button
                        wire:click="setPage({{ $page - 1 }})"
                        @if($page === 1) disabled @endif
                        class="px-4 py-1.5 bg-gray-200 text-gray-700 rounded disabled:opacity-50 font-medium hover:bg-gray-300 transition-colors"
                    >
                        Anterior
                    </button>

                    <span class="text-gray-600">
                        Página {{ $page }} de {{ $totalPages }}
                    </span>

                    <button
                        wire:click="setPage({{ $page + 1 }})"
                        @if($page === $totalPages) disabled @endif
                        class="px-4 py-1.5 bg-gray-200 text-gray-700 rounded disabled:opacity-50 font-medium hover:bg-gray-300 transition-colors"
                    >
                        Siguiente
                    </button>
                </div>
            @endif

            @if($errorMensaje)
                <p class="text-center mt-4 text-red-600 text-sm">{{ $errorMensaje }}</p>
            @endif

            <a
                href="{{ route('dashboard') }}"
                class="mt-8 inline-flex items-center gap-2 bg-gray-200 text-gray-700 font-bold px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors text-sm"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Regresar
            </a>
        </div>
    </main>
</div>
