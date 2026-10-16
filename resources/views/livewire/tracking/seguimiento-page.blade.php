<div class="bg-gray-50 dark:bg-[#0D1418] min-h-screen flex flex-col transition-colors">
    <x-header />

    <main class="px-3.5 py-4 sm:p-6 md:p-8 flex flex-col items-center flex-1">
        <div class="w-full max-w-5xl bg-white dark:bg-[#131B20] p-4 sm:p-6 md:p-8 rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 dark:border-white/10 transition-colors">
            <h1 class="text-xl sm:text-2xl font-black text-center mb-4 sm:mb-6 text-gray-800 dark:text-white tracking-tight">
                Seguimiento de Planes
            </h1>

            <!-- BUSCADOR -->
            <div class="relative w-full mb-5">
                <div class="relative flex items-center">
                    <div class="absolute left-3.5 text-gray-400 dark:text-gray-500 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input
                        type="text"
                        placeholder="Buscar cliente por código o nombre..."
                        class="w-full h-11 pl-10 pr-10 bg-white dark:bg-[#1A2227] border border-gray-300 dark:border-white/10 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent shadow-sm transition-all"
                        wire:model.live.debounce.400ms="texto"
                    />
                    <div class="absolute right-3.5 text-green-600 dark:text-green-400" wire:loading wire:target="texto, setPage">
                        <i class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                    </div>
                </div>
            </div>

            <!-- VISTA DE ESCRITORIO (Tabla Institucional MD+) -->
            <div class="hidden md:block border border-gray-200 dark:border-white/10 rounded-2xl overflow-hidden shadow-sm relative bg-white dark:bg-[#131B20]">
                <table class="w-full table-fixed border-collapse" style="table-layout: fixed; width: 100%;">
                    <colgroup>
                        <col style="width: 140px;" />
                        <col />
                        <col style="width: 160px;" />
                    </colgroup>
                    <thead class="bg-gray-50 dark:bg-white/[0.03] text-gray-600 dark:text-gray-300 text-xs uppercase tracking-wider font-bold border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th class="p-3.5 text-left font-bold" style="width: 140px;">Código</th>
                            <th class="p-3.5 text-left font-bold">Cliente</th>
                            <th class="p-3.5 text-center font-bold" style="width: 160px;">Acciones</th>
                        </tr>
                    </thead>

                    {{-- Skeleton Tbody Escritorio --}}
                    <tbody
                        wire:key="tracking-skeleton-desktop"
                        wire:loading.class.remove="hidden"
                        wire:target="texto, setPage"
                        class="hidden table-skeleton-group divide-y divide-gray-100 dark:divide-white/5 text-sm"
                    >
                        @php
                            $skeletonWidths = [56, 74, 48, 62, 44, 66];
                        @endphp
                        @for($i = 0; $i < 6; $i++)
                            <tr wire:key="skeleton-desktop-row-{{ $i }}" class="animate-pulse border-b border-gray-100 dark:border-white/5">
                                <td class="p-3.5" style="width: 140px;">
                                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-md" style="width: 76px;"></div>
                                </td>
                                <td class="p-3.5">
                                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-md" style="width: {{ $skeletonWidths[$i] }}%; max-width: 420px;"></div>
                                </td>
                                <td class="p-3.5 text-center" style="width: 160px;">
                                    <div class="h-8 bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg mx-auto" style="width: 96px;"></div>
                                </td>
                            </tr>
                        @endfor
                    </tbody>

                    {{-- Data Tbody Escritorio --}}
                    <tbody
                        wire:key="tracking-data-desktop"
                        wire:loading.class="hidden"
                        wire:target="texto, setPage"
                        class="divide-y divide-gray-100 dark:divide-white/5 text-sm"
                    >
                        @forelse($clientes as $cliente)
                            <tr
                                wire:key="desktop-cliente-row-{{ $cliente['CardCode'] }}"
                                class="hover:bg-gray-50/80 dark:hover:bg-white/[0.03] transition-colors"
                            >
                                <td class="p-3.5 text-gray-500 dark:text-gray-400 font-mono text-xs font-bold truncate" style="width: 140px;">
                                    {{ $cliente['CardCode'] }}
                                </td>
                                <td class="p-3.5 font-bold text-gray-800 dark:text-gray-100 truncate">
                                    {{ trim((string) preg_replace('/^\s*'.preg_quote($cliente['CardCode'], '/').'\s*[-–—:]\s*/i', '', $cliente['CardName'])) }}
                                    @if(!empty($cliente['Zone']))
                                        <span class="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400 shrink-0">
                                            {{ $cliente['Zone'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-center" style="width: 160px;">
                                    <button
                                        type="button"
                                        wire:click="toggleCliente('{{ $cliente['CardCode'] }}')"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-lg transition-all text-xs font-bold shadow-sm shadow-green-600/20 cursor-pointer active:scale-95 mx-auto block"
                                        style="width: 96px;"
                                    >
                                        {{ $expandedClient === $cliente['CardCode'] ? 'Ocultar' : 'Ver planes' }}
                                    </button>
                                </td>
                            </tr>

                            @if($expandedClient === $cliente['CardCode'])
                                <tr wire:key="desktop-expanded-{{ $cliente['CardCode'] }}" class="bg-gray-50/70 dark:bg-white/[0.02]">
                                    <td colspan="3" class="p-5">
                                        @if($loadingPlanes === $cliente['CardCode'])
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 animate-pulse py-2">
                                                @for($s = 0; $s < 3; $s++)
                                                    <div wire:key="desktop-sk-plan-{{ $cliente['CardCode'] }}-{{ $s }}" class="bg-white dark:bg-[#131b20] p-5 rounded-2xl border-2 border-green-200/60 dark:border-green-500/20 shadow-sm space-y-4">
                                                        <div class="space-y-3">
                                                            <div class="h-4 bg-green-600/20 dark:bg-green-500/20 rounded-full w-16"></div>
                                                            <div class="h-5 bg-green-700/20 dark:bg-green-400/20 rounded-md w-40"></div>
                                                            <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-16"></div>
                                                        </div>
                                                        <div class="pt-3 border-t border-gray-100 dark:border-white/5">
                                                            <div class="h-10 bg-green-600/20 dark:bg-green-500/20 rounded-xl w-full"></div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        @elseif(!empty($planesPorCliente[$cliente['CardCode']]))
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                                @foreach($planesPorCliente[$cliente['CardCode']] as $plan)
                                                    <div class="bg-white dark:bg-[#131b20] p-5 rounded-2xl border-2 border-green-200 dark:border-green-500/20 shadow-sm hover:shadow-md transition-all space-y-4 relative flex flex-col justify-between">
                                                        <div>
                                                            <div class="flex justify-between items-start mb-2">
                                                                <div>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ ($plan['status'] ?? '') === 'ACTIVE' || ($plan['status'] ?? '') === 'Activo' ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-300' }}">
                                                                            {{ $plan['statusLabel'] ?? ($plan['status'] ?? 'Activo') }}
                                                                        </span>
                                                                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">v{{ $plan['version'] }}</span>
                                                                    </div>
                                                                    <h4 class="text-base font-extrabold text-green-700 dark:text-green-400 mt-1">
                                                                        {{ $plan['customerName'] ?: "Plan #{$plan['planCode']}" }}
                                                                    </h4>
                                                                    <p class="text-xs text-gray-400">ID: {{ $plan['planCode'] }}</p>
                                                                </div>

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
                                                                        <div
                                                                            x-show="menuOpen"
                                                                            @click.outside="menuOpen = false"
                                                                            x-cloak
                                                                            class="absolute right-0 top-8 z-30 w-52 bg-white dark:bg-[#1A2227] rounded-xl shadow-xl border border-gray-100 dark:border-white/10 py-1 text-xs divide-y divide-gray-100 dark:divide-white/5"
                                                                            style="display: none;"
                                                                        >
                                                                            <a href="{{ route('plan.version', ['planCode' => $plan['planCode'], 'phone' => $cliente['Cellular'] ?? '']) }}" class="flex items-center gap-2 px-4 py-2.5 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5 transition-colors font-medium">
                                                                                <i class="fa-solid fa-code-branch text-green-600"></i>
                                                                                <span>Versionar plan</span>
                                                                            </a>
                                                                            <a href="{{ route('tracking.edit', ['planCode' => $plan['planCode'], 'cultivoId' => $plan['currentCropCode'] ?? null]) }}" class="flex items-center gap-2 px-4 py-2.5 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5 transition-colors font-medium">
                                                                                <i class="fa-solid fa-clipboard-list text-blue-600"></i>
                                                                                <span>Registro de seguimiento</span>
                                                                            </a>
                                                                            <button
                                                                                type="button"
                                                                                @click="menuOpen = false; $dispatch('open-capture-results', { planCode: '{{ $plan['planCode'] }}', phone: '{{ addslashes($cliente['Cellular'] ?? '') }}' })"
                                                                                class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5 transition-colors font-medium cursor-pointer"
                                                                            >
                                                                                <i class="fa-solid fa-chart-line text-amber-600"></i>
                                                                                <span>Capturar resultados</span>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <button
                                                                        type="button"
                                                                        wire:click="openCancelModal('{{ $plan['planCode'] }}', '{{ addslashes($cliente['CardName']) }}')"
                                                                        class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors cursor-pointer"
                                                                        title="Cerrar / Cancelar plan"
                                                                    >
                                                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <hr class="border-green-100 dark:border-white/5 my-3" />

                                                            <div class="space-y-1.5 text-xs">
                                                                <div class="flex justify-between">
                                                                    <span class="font-semibold text-gray-500 dark:text-gray-400">Cultivo:</span>
                                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $plan['currentCropName'] ?? 'Sin cultivo' }}</span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="font-semibold text-gray-500 dark:text-gray-400">Superficie:</span>
                                                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $plan['hectares'] }} ha</span>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <span class="font-semibold text-gray-500 dark:text-gray-400">Fecha siembra:</span>
                                                                    <span class="font-bold text-gray-800 dark:text-gray-200">
                                                                        {{ !empty($plan['estimatedSowingDate']) ? \Carbon\Carbon::parse($plan['estimatedSowingDate'])->format('d/m/Y') : 'N/A' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="pt-3 border-t border-gray-100 dark:border-white/5">
                                                            <button
                                                                type="button"
                                                                wire:click="$dispatch('open-proposal-pdf', { planCode: '{{ $plan['planCode'] }}', version: {{ $plan['version'] }}, customerName: '{{ addslashes($cliente['CardName']) }}', phone: '{{ $cliente['Cellular'] ?? '' }}', customerCode: '{{ $cliente['CardCode'] }}' })"
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
                                    No se encontraron clientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- VISTA MÓVIL: BENTO CARDS CON ELEVACIÓN Y SKELETON 1:1 (< MD) -->
            <div class="block md:hidden space-y-3.5">
                {{-- Skeleton Mobile: Bento Cards 1:1 --}}
                <div
                    wire:key="mobile-skeleton-container"
                    wire:loading.class.remove="hidden"
                    wire:target="texto, setPage"
                    class="hidden space-y-3.5"
                >
                    @for($i = 0; $i < 4; $i++)
                        <div wire:key="mobile-skeleton-card-{{ $i }}" class="bg-white dark:bg-[#131B20] border border-gray-200/80 dark:border-white/10 rounded-2xl p-4 shadow-sm space-y-3 animate-pulse">
                            <!-- Header skeleton: Avatar + Code pill -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-gray-200 dark:bg-white/10 shrink-0"></div>
                                    <div class="h-6 w-20 bg-gray-200 dark:bg-white/10 rounded-lg"></div>
                                </div>
                                <div class="h-3 w-12 bg-gray-200 dark:bg-white/10 rounded"></div>
                            </div>

                            <!-- Customer Name skeleton (natural line) -->
                            <div class="py-1">
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-md {{ $i % 2 === 0 ? 'w-4/5' : 'w-2/3' }}"></div>
                            </div>

                            <!-- Action Button skeleton -->
                            <div class="h-10 w-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl flex items-center justify-between px-4">
                                <div class="h-3.5 w-36 bg-gray-200 dark:bg-white/10 rounded"></div>
                                <div class="h-3.5 w-3.5 bg-gray-200 dark:bg-white/10 rounded-full"></div>
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Real Data Mobile: Bento Cards --}}
                <div
                    wire:key="mobile-data-container"
                    wire:loading.class="hidden"
                    wire:target="texto, setPage"
                    class="space-y-3.5"
                >
                    @forelse($clientes as $cliente)
                        <div
                            wire:key="mobile-cliente-card-{{ $cliente['CardCode'] }}"
                            class="bg-white dark:bg-[#131B20] border border-gray-200/90 dark:border-white/10 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all space-y-3"
                        >
                            <!-- Top: Avatar + Code badge + Tag -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 border border-green-200/60 dark:border-green-500/20 flex items-center justify-center font-bold text-xs shrink-0">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-xs font-mono font-bold">
                                        {{ $cliente['CardCode'] }}
                                    </span>
                                    @if(!empty($cliente['Zone']))
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20">
                                            <i class="fa-solid fa-location-dot text-[9px] mr-1"></i>{{ $cliente['Zone'] }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Cliente</span>
                            </div>

                            <!-- Client Name: Full typography, never truncated into unreadable dots -->
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white leading-snug break-words">
                                {{ trim((string) preg_replace('/^\s*'.preg_quote($cliente['CardCode'], '/').'\s*[-–—:]\s*/i', '', $cliente['CardName'])) }}
                            </h3>

                            <!-- Full width ergonomic button -->
                            <button
                                type="button"
                                wire:click="toggleCliente('{{ $cliente['CardCode'] }}')"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold transition-all flex items-center justify-between cursor-pointer active:scale-[0.98] {{ $expandedClient === $cliente['CardCode'] ? 'bg-green-600 text-white shadow-sm shadow-green-600/30' : 'bg-green-50 hover:bg-green-100 dark:bg-green-500/10 dark:hover:bg-green-500/20 text-green-700 dark:text-green-400 border border-green-200/60 dark:border-green-500/20' }}"
                            >
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid {{ $expandedClient === $cliente['CardCode'] ? 'fa-folder-open' : 'fa-folder' }} text-xs"></i>
                                    <span>{{ $expandedClient === $cliente['CardCode'] ? 'Ocultar planes' : 'Ver planes de fertilización' }}</span>
                                </span>
                                <i class="fa-solid {{ $expandedClient === $cliente['CardCode'] ? 'fa-chevron-up' : 'fa-chevron-down' }} text-xs"></i>
                            </button>

                            <!-- Expanded Section: Plans inside card -->
                            @if($expandedClient === $cliente['CardCode'])
                                <div wire:key="mobile-expanded-{{ $cliente['CardCode'] }}" class="pt-3 border-t border-gray-100 dark:border-white/5 space-y-3">
                                    @if($loadingPlanes === $cliente['CardCode'])
                                        <div class="space-y-3 animate-pulse py-1">
                                            @for($s = 0; $s < 2; $s++)
                                                <div class="bg-gray-50 dark:bg-white/[0.03] p-3.5 rounded-xl border border-gray-100 dark:border-white/5 space-y-2.5">
                                                    <div class="flex justify-between items-center">
                                                        <div class="h-4 bg-green-600/20 dark:bg-green-500/20 rounded-full w-16"></div>
                                                        <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-8"></div>
                                                    </div>
                                                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-36"></div>
                                                    <div class="h-9 bg-green-600/20 dark:bg-green-500/20 rounded-xl w-full"></div>
                                                </div>
                                            @endfor
                                        </div>
                                    @elseif(!empty($planesPorCliente[$cliente['CardCode']]))
                                        <div class="space-y-3">
                                            @foreach($planesPorCliente[$cliente['CardCode']] as $plan)
                                                <div class="bg-gray-50/80 dark:bg-white/[0.03] p-3.5 rounded-xl border border-green-200/60 dark:border-green-500/20 space-y-3">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ ($plan['status'] ?? '') === 'ACTIVE' || ($plan['status'] ?? '') === 'Activo' ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-300' }}">
                                                                    {{ $plan['statusLabel'] ?? ($plan['status'] ?? 'Activo') }}
                                                                </span>
                                                                <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">v{{ $plan['version'] }}</span>
                                                            </div>
                                                            <h4 class="text-sm font-extrabold text-green-700 dark:text-green-400 mt-1">
                                                                {{ $plan['customerName'] ?: "Plan #{$plan['planCode']}" }}
                                                            </h4>
                                                            <p class="text-[11px] text-gray-400 font-mono">ID: {{ $plan['planCode'] }}</p>
                                                        </div>

                                                        <div class="flex items-center gap-1.5" x-data="{ menuOpen: false }">
                                                            <div class="relative">
                                                                <button
                                                                    type="button"
                                                                    @click="menuOpen = !menuOpen"
                                                                    class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-200/50 dark:hover:bg-white/5 transition-colors cursor-pointer"
                                                                    title="Opciones del plan"
                                                                >
                                                                    <i class="fa-solid fa-pen text-xs"></i>
                                                                </button>
                                                                <div
                                                                    x-show="menuOpen"
                                                                    @click.outside="menuOpen = false"
                                                                    x-cloak
                                                                    class="absolute right-0 top-7 z-30 w-48 bg-white dark:bg-[#1A2227] rounded-xl shadow-xl border border-gray-100 dark:border-white/10 py-1 text-xs divide-y divide-gray-100 dark:divide-white/5"
                                                                >
                                                                    <a href="{{ route('plan.version', ['planCode' => $plan['planCode'], 'phone' => $cliente['Cellular'] ?? '']) }}" class="flex items-center gap-2 px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5">
                                                                        <i class="fa-solid fa-code-branch text-green-600"></i>
                                                                        <span>Versionar plan</span>
                                                                    </a>
                                                                    <a href="{{ route('tracking.edit', ['planCode' => $plan['planCode'], 'cultivoId' => $plan['currentCropCode'] ?? null]) }}" class="flex items-center gap-2 px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5">
                                                                        <i class="fa-solid fa-clipboard-list text-blue-600"></i>
                                                                        <span>Registro de seguimiento</span>
                                                                    </a>
                                                                    <button
                                                                        type="button"
                                                                        @click="menuOpen = false; $dispatch('open-capture-results', { planCode: '{{ $plan['planCode'] }}', phone: '{{ addslashes($cliente['Cellular'] ?? '') }}' })"
                                                                        class="w-full text-left flex items-center gap-2 px-3 py-2 text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-white/5"
                                                                    >
                                                                        <i class="fa-solid fa-chart-line text-amber-600"></i>
                                                                        <span>Capturar resultados</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <button
                                                                type="button"
                                                                wire:click="openCancelModal('{{ $plan['planCode'] }}', '{{ addslashes($cliente['CardName']) }}')"
                                                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-lg hover:bg-gray-200/50 dark:hover:bg-white/5 transition-colors cursor-pointer"
                                                                title="Cerrar / Cancelar plan"
                                                            >
                                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-3 gap-2 text-[11px] pt-1">
                                                        <div class="bg-white dark:bg-[#131b20] p-2 rounded-lg border border-gray-100 dark:border-white/5">
                                                            <span class="text-gray-400 block text-[9px] font-bold uppercase">Cultivo</span>
                                                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $plan['currentCropName'] ?? 'N/D' }}</span>
                                                        </div>
                                                        <div class="bg-white dark:bg-[#131b20] p-2 rounded-lg border border-gray-100 dark:border-white/5">
                                                            <span class="text-gray-400 block text-[9px] font-bold uppercase">Superficie</span>
                                                            <span class="font-bold text-gray-800 dark:text-gray-200 block">{{ $plan['hectares'] }} ha</span>
                                                        </div>
                                                        <div class="bg-white dark:bg-[#131b20] p-2 rounded-lg border border-gray-100 dark:border-white/5">
                                                            <span class="text-gray-400 block text-[9px] font-bold uppercase">Siembra</span>
                                                            <span class="font-bold text-gray-800 dark:text-gray-200 block truncate">
                                                                {{ !empty($plan['estimatedSowingDate']) ? \Carbon\Carbon::parse($plan['estimatedSowingDate'])->format('d/m/y') : 'N/D' }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        wire:click="$dispatch('open-proposal-pdf', { planCode: '{{ $plan['planCode'] }}', version: {{ $plan['version'] }}, customerName: '{{ addslashes($cliente['CardName']) }}', phone: '{{ $cliente['Cellular'] ?? '' }}', customerCode: '{{ $cliente['CardCode'] }}' })"
                                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg flex items-center justify-center gap-1.5 transition-all shadow-sm shadow-green-600/20 text-xs cursor-pointer active:scale-95"
                                                    >
                                                        <i class="fa-solid fa-download text-xs"></i>
                                                        <span>Descargar / Compartir</span>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-gray-500 dark:text-gray-400 text-center py-3 text-xs">Este cliente no tiene planes activos.</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-2xl p-8 text-center text-gray-500 dark:text-gray-400">
                            No se encontraron clientes.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- PAGINADOR -->
            @if($totalPages > 1)
                <div class="flex justify-between sm:justify-center items-center gap-2 sm:gap-4 mt-5 sm:mt-6 text-xs sm:text-sm">
                    <button
                        wire:click="setPage({{ $page - 1 }})"
                        @if($page === 1) disabled @endif
                        class="px-3.5 sm:px-4 py-2 sm:py-1.5 bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl sm:rounded disabled:opacity-40 font-bold transition-colors cursor-pointer flex items-center gap-1.5"
                    >
                        <i class="fa-solid fa-chevron-left text-[11px]"></i>
                        <span>Anterior</span>
                    </button>

                    <span class="text-gray-600 dark:text-gray-400 font-medium px-1 text-center">
                        Página {{ $page }} de {{ $totalPages }}
                    </span>

                    <button
                        wire:click="setPage({{ $page + 1 }})"
                        @if($page === $totalPages) disabled @endif
                        class="px-3.5 sm:px-4 py-2 sm:py-1.5 bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300 rounded-xl sm:rounded disabled:opacity-40 font-bold transition-colors cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Siguiente</span>
                        <i class="fa-solid fa-chevron-right text-[11px]"></i>
                    </button>
                </div>
            @endif

            @if($errorMensaje)
                <p class="text-center mt-4 text-red-600 text-sm font-semibold">{{ $errorMensaje }}</p>
            @endif

            <div class="mt-6 sm:mt-8 flex justify-start">
                <a
                    href="{{ route('dashboard') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-gray-300 font-bold px-6 py-2.5 sm:py-2 rounded-xl sm:rounded-lg hover:bg-gray-300 dark:hover:bg-white/15 transition-colors text-sm"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Regresar</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Modal Cancelación de Plan (Fiel a React ModalCancelacion) -->
    @if($isCancelModalOpen)
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 z-50 flex items-center justify-center p-4" x-cloak>
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
