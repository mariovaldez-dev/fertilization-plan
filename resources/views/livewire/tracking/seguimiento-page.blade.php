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
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                                @foreach($planesPorCliente[$cliente['CardCode']] as $plan)
                                                    <div class="bg-white dark:bg-[#131b20] p-5 rounded-2xl border-2 border-green-200 dark:border-green-500/20 shadow-sm hover:shadow-md transition-all space-y-4 relative flex flex-col justify-between">
                                                        <div>
                                                            <!-- Header con Título y Acciones -->
                                                            <div class="flex justify-between items-start mb-2">
                                                                <div>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $plan->status->name === 'ACTIVE' ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-300' }}">
                                                                            {{ $plan->status->label() }}
                                                                        </span>
                                                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">v{{ $plan->version }}</span>
                                                                    </div>
                                                                    <h4 class="text-base font-extrabold text-green-700 dark:text-green-400 mt-1">
                                                                        {{ $plan->customerName ?: "Plan #{$plan->planCode}" }}
                                                                    </h4>
                                                                    <p class="text-xs text-gray-400">ID: {{ $plan->planCode }}</p>
                                                                </div>

                                                                <!-- Botones Lápiz (Menú) y Papelera -->
                                                                <div class="flex items-center gap-2" x-data="{ menuOpen: false }">
                                                                    <div class="relative">
                                                                        <button
                                                                            type="button"
                                                                            @click="menuOpen = !menuOpen"
                                                                            class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors cursor-pointer"
                                                                            title="Opciones del plan"
                                                                        >
                                                                            <i class="fa-solid fa-pen text-sm"></i>
                                                                        </button>

                                                                        <!-- Dropdown Opciones -->
                                                                        <div
                                                                            x-show="menuOpen"
                                                                            @click.outside="menuOpen = false"
                                                                            x-cloak
                                                                            class="absolute right-0 top-8 z-30 w-52 bg-white dark:bg-[#1A2227] rounded-xl shadow-xl border border-gray-100 dark:border-white/10 py-1 text-xs divide-y divide-gray-100 dark:divide-white/5"
                                                                            style="display: none;"
                                                                        >
                                                                            <a
                                                                                href="{{ route('plan.version', ['planCode' => $plan->planCode]) }}"
                                                                                class="flex items-center gap-2 px-4 py-2.5 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5 transition-colors font-medium"
                                                                            >
                                                                                <i class="fa-solid fa-code-branch text-green-600"></i>
                                                                                <span>Versionar plan</span>
                                                                            </a>
                                                                            <a
                                                                                href="{{ route('tracking.edit', ['planCode' => $plan->planCode]) }}"
                                                                                class="flex items-center gap-2 px-4 py-2.5 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5 transition-colors font-medium"
                                                                            >
                                                                                <i class="fa-solid fa-clipboard-list text-blue-600"></i>
                                                                                <span>Registro de seguimiento</span>
                                                                            </a>
                                                                            <button
                                                                                type="button"
                                                                                @click="menuOpen = false; $dispatch('open-capture-results', { planCode: '{{ $plan->planCode }}' })"
                                                                                class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5 transition-colors font-medium cursor-pointer"
                                                                            >
                                                                                <i class="fa-solid fa-chart-line text-amber-600"></i>
                                                                                <span>Capturar resultados</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>

                                                                    <button
                                                                        type="button"
                                                                        wire:click="openCancelModal('{{ $plan->planCode }}', '{{ addslashes($cliente['CardName']) }}')"
                                                                        class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors cursor-pointer"
                                                                        title="Cerrar / Cancelar plan"
                                                                    >
                                                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <hr class="border-green-100 dark:border-white/5 my-3" />

                                                            <!-- Datos Clave del Cultivo -->
                                                            <div class="space-y-1.5 text-xs">
                                                                <div class="flex justify-between">
                                                                    <span class="font-semibold text-gray-500 dark:text-gray-400">Cultivo:</span>
                                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $plan->currentCropName ?? 'Sin cultivo' }}</span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="font-semibold text-gray-500 dark:text-gray-400">Superficie:</span>
                                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $plan->hectares }} ha</span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="font-semibold text-gray-500 dark:text-gray-400">Fecha siembra:</span>
                                                                    <span class="font-bold text-gray-800 dark:text-gray-200">
                                                                        {{ $plan->estimatedSowingDate ? \Carbon\Carbon::parse($plan->estimatedSowingDate)->format('d/m/Y') : 'N/A' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Botón Principal: Descargar / Compartir Propuesta -->
                                                        <div class="pt-3 border-t border-gray-100 dark:border-white/5">
                                                            <button
                                                                type="button"
                                                                wire:click="$dispatch('open-proposal-pdf', { planCode: '{{ $plan->planCode }}', version: {{ $plan->version }}, customerName: '{{ addslashes($cliente['CardName']) }}', phone: '{{ $cliente['Cellular'] ?? '' }}' })"
                                                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 transition-all shadow-md shadow-green-600/20 text-xs cursor-pointer hover:-translate-y-0.5 active:translate-y-0"
                                                            >
                                                                <i class="fa-solid fa-download text-xs"></i>
                                                                <span>Descargar / Compartir</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-gray-500 dark:text-gray-400 text-center py-4">Este cliente no tiene planes activos.</p>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="3" class="p-6 text-center text-gray-500 dark:text-gray-400">
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
                        class="px-4 py-1.5 bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded disabled:opacity-50 font-medium hover:bg-gray-300 dark:hover:bg-white/15 transition-colors cursor-pointer"
                    >
                        Anterior
                    </button>

                    <span class="text-gray-600 dark:text-gray-400">
                        Página {{ $page }} de {{ $totalPages }}
                    </span>

                    <button
                        wire:click="setPage({{ $page + 1 }})"
                        @if($page === $totalPages) disabled @endif
                        class="px-4 py-1.5 bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded disabled:opacity-50 font-medium hover:bg-gray-300 dark:hover:bg-white/15 transition-colors cursor-pointer"
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
                class="mt-8 inline-flex items-center gap-2 bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-bold px-6 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-white/15 transition-colors text-sm"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Regresar
            </a>
        </div>
    </main>

    <!-- Modal Cancelación de Plan (Fiel a React ModalCancelacion) -->
    @if($isCancelModalOpen)
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
            <div class="relative bg-white dark:bg-[#131b20] w-full max-w-md p-6 sm:p-8 rounded-3xl shadow-2xl border border-gray-100 dark:border-white/10 text-center space-y-4" @click.outside="$wire.closeCancelModal()">
                <div class="w-14 h-14 bg-yellow-100 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-400 rounded-full flex items-center justify-center mx-auto text-2xl border border-yellow-500/30">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

                <div class="space-y-1">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">
                        ¿Seguro de cerrar el plan de fertilización?
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Plan: <span class="font-bold font-mono">{{ $cancellingPlanCode }}</span> — {{ $cancellingCustomerName }}
                    </p>
                </div>

                <div class="text-left space-y-1" x-data="{ comment: @entangle('cancellationComment') }">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                        Comentarios *
                    </label>
                    <textarea
                        wire:model="cancellationComment"
                        rows="4"
                        maxlength="245"
                        placeholder="Escribir motivo del cierre..."
                        class="w-full p-3 text-xs bg-gray-50 dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-xl text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-green-500"
                    ></textarea>
                    <div class="flex justify-between items-center text-[10px] text-gray-400">
                        @error('cancellationComment')
                            <span class="text-red-500 font-bold">{{ $message }}</span>
                        @else
                            <span></span>
                        @enderror
                        <span x-text="(comment ? comment.length : 0) + ' / 245'"></span>
                    </div>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="confirmCancelPlan"
                        wire:loading.attr="disabled"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-xl transition-all text-xs flex items-center justify-center gap-2 cursor-pointer shadow-md shadow-green-600/20"
                    >
                        <span wire:loading.remove wire:target="confirmCancelPlan">Sí</span>
                        <span wire:loading wire:target="confirmCancelPlan">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </span>
                    </button>
                    <button
                        type="button"
                        wire:click="closeCancelModal"
                        class="flex-1 bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20 text-gray-800 dark:text-white font-bold py-2.5 px-4 rounded-xl transition-all text-xs cursor-pointer"
                    >
                        No
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Inclusión de Modales Globales -->
    <livewire:reports.proposal-pdf-modal />
    <livewire:reports.results-pdf-modal />
    <livewire:results.capture-results-modal />
</div>
