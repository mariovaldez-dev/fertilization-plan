<div>
    @if($isOpen)
        <div class="fixed inset-0 bg-black/60 dark:bg-black/80 flex items-center justify-center z-50 p-4" x-cloak @click.self="$wire.close()">
            <div
                class="bg-white dark:bg-[#131B20] rounded-2xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 relative border border-gray-100 dark:border-white/10"
                @click.stop
            >
                <button
                    type="button"
                    wire:click="close"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors cursor-pointer"
                    title="Cerrar"
                >
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>

                <div class="mx-auto bg-green-100 dark:bg-green-500/20 rounded-full h-16 w-16 flex items-center justify-center border border-green-500/20">
                    <i class="fa-solid fa-file-lines text-green-600 dark:text-green-400 text-2xl"></i>
                </div>

                <div class="space-y-1">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Acciones del Plan {{ $planCode }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Versión: {{ $version }}
                    </p>
                </div>

                {{-- Banner Error --}}
                @if($errorMessage)
                    <div class="p-3 bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-300 text-xs rounded-xl flex items-center gap-2 text-left border border-red-200 dark:border-red-800/40">
                        <i class="fa-solid fa-circle-exclamation flex-shrink-0 text-sm"></i>
                        <span>{{ $errorMessage }}</span>
                    </div>
                @endif

                {{-- Banner Status Éxito --}}
                @if($statusMessage && !$errorMessage)
                    <div class="p-3 bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-300 text-xs rounded-xl flex items-center gap-2 text-left border border-green-200 dark:border-green-800/40">
                        <i class="fa-solid fa-circle-check flex-shrink-0 text-sm"></i>
                        <span>{{ $statusMessage }}</span>
                    </div>
                @endif

                {{-- Banners de Carga Reactiva Inmediata --}}
                <div wire:loading wire:target="download" class="p-3 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 text-xs rounded-xl flex items-center gap-2 text-left border border-blue-200 dark:border-blue-800/40 animate-pulse">
                    <i class="fa-solid fa-spinner fa-spin flex-shrink-0 text-sm"></i>
                    <span>Preparando documento... Obteniendo PDF del plan para descargar.</span>
                </div>

                <div wire:loading wire:target="share" class="p-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-xs rounded-xl flex items-center gap-2 text-left border border-emerald-200 dark:border-emerald-800/40 animate-pulse">
                    <i class="fa-solid fa-spinner fa-spin flex-shrink-0 text-sm"></i>
                    <span>Enviando por WhatsApp... Conectando con el servicio de mensajería.</span>
                </div>

                {{-- Advertencia Falta Celular Registrado --}}
                @if($isPhoneMissing || $this->isPhoneMissing)
                    <div wire:loading.remove wire:target="share, download" class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs rounded-xl flex items-center gap-2.5 text-left border border-amber-200 dark:border-amber-800/40 shadow-xs">
                        <i class="fa-solid fa-triangle-exclamation flex-shrink-0 text-base text-amber-600 dark:text-amber-400"></i>
                        <div class="leading-snug">
                            <span class="font-bold block">Cliente sin celular registrado</span>
                            <span class="text-[11px] opacity-90">El cliente no cuenta con número celular en SAP. No es posible compartir por WhatsApp.</span>
                        </div>
                    </div>
                @endif

                {{-- Botones de Acción --}}
                <div class="flex justify-center gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="share"
                        wire:loading.attr="disabled"
                        wire:target="download, share"
                        @if($isPhoneMissing || $this->isPhoneMissing) disabled title="El cliente no cuenta con celular registrado en SAP" @endif
                        class="flex-1 bg-green-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-green-700 transition-colors flex items-center justify-center gap-2 text-xs shadow-md shadow-green-600/20 disabled:bg-gray-400 disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="share" class="inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-share-nodes text-sm"></i>
                            <span>Compartir</span>
                        </span>
                        <span wire:loading wire:target="share" class="inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                            <span>Enviando...</span>
                        </span>
                    </button>

                    <button
                        type="button"
                        wire:click="download"
                        wire:loading.attr="disabled"
                        wire:target="download, share"
                        class="flex-1 bg-gray-200 text-gray-800 dark:bg-white/10 dark:text-gray-200 font-bold py-3 px-4 rounded-xl hover:bg-gray-300 dark:hover:bg-white/15 transition-colors flex items-center justify-center gap-2 text-xs disabled:bg-gray-400 disabled:opacity-80 disabled:cursor-not-allowed cursor-pointer"
                    >
                        <span wire:loading.remove wire:target="download" class="inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-download text-sm"></i>
                            <span>Descargar</span>
                        </span>
                        <span wire:loading wire:target="download" class="inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                            <span>Descargando...</span>
                        </span>
                    </button>
                </div>

                {{-- Botón Cerrar --}}
                <button
                    type="button"
                    wire:click="close"
                    wire:loading.attr="disabled"
                    wire:target="download, share"
                    class="w-full text-xs font-semibold text-gray-600 dark:text-gray-400 mt-2 hover:text-black dark:hover:text-white transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    Cerrar
                </button>
            </div>
        </div>
    @endif
</div>
