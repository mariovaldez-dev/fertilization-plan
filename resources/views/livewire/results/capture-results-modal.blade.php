<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="relative w-full max-w-2xl bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl p-6 lg:p-8 space-y-6">
                @if(!$isSuccess)
                    <!-- Header -->
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">
                                    Folio #{{ $planCode }}
                                </span>
                                <span class="text-xs text-gray-400">| Captura de Resultados</span>
                            </div>
                            <h2 class="text-xl font-black text-gray-900 dark:text-white">Dictamen Final de Cosecha</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $customerName }}</p>
                        </div>
                        <button type="button" wire:click="close" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    @if($errorMessage)
                        <div class="p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 rounded-2xl text-xs font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>{{ $errorMessage }}</span>
                        </div>
                    @endif

                    <!-- Form Content (Scrollable if needed) -->
                    <div class="space-y-4 max-h-[65vh] overflow-y-auto pr-1">
                        <!-- Date & Yields -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Fecha Cosecha *</label>
                                <input type="date" wire:model="fechaCosecha" class="w-full h-11 px-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500">
                                @error('fechaCosecha') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Rendimiento (Ton/Ha) *</label>
                                <input type="number" step="0.1" max="99.9" wire:model.live.debounce.300ms="rendimiento" placeholder="Ej: 14.5" class="w-full h-11 px-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-black text-green-600 dark:text-green-400 focus:outline-none focus:border-green-500">
                                @error('rendimiento') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Rend. Tradicional *</label>
                                <input type="number" step="0.1" wire:model.live.debounce.300ms="rendimientoTradicional" placeholder="Ej: 11.0" class="w-full h-11 px-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500">
                                @error('rendimientoTradicional') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Dosage & Price -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Dosis (Ton/Ha) *</label>
                                <input type="number" step="0.01" wire:model="dosis" placeholder="Ej: 1.0" class="w-full h-11 px-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500">
                                @error('dosis') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Precio Cosecha ($/Ton) *</label>
                                <input type="number" step="0.01" wire:model.live.debounce.300ms="precioCosecha" placeholder="Ej: 5500.00" class="w-full h-11 px-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500">
                                @error('precioCosecha') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Costo Total ($/Ha) *</label>
                                <input type="number" step="0.01" wire:model.live.debounce.300ms="costoTotal" class="w-full h-11 px-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500">
                                @error('costoTotal') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Costo Tradicional & Coordenadas -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Costo Tradicional ($/Ha) *</label>
                                <input type="number" step="0.01" wire:model.live.debounce.300ms="costoTradicional" class="w-full h-11 px-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-bold text-gray-900 dark:text-white focus:outline-none focus:border-green-500">
                                @error('costoTradicional') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Coordenadas GPS (Lat, Lng)</label>
                                <input type="text" wire:model="coordenadas" class="w-full h-11 px-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none focus:border-green-500" placeholder="25.790466,-108.995888">
                            </div>
                        </div>

                        <!-- Real-time KPI Summary Box -->
                        <div class="p-4 rounded-2xl bg-green-50/70 dark:bg-green-500/10 border border-green-200/50 dark:border-green-500/20 grid grid-cols-3 gap-3 text-center">
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-green-700 dark:text-green-400">Utilidad Extra</span>
                                <span class="text-sm font-black text-green-800 dark:text-white">${{ number_format($utilidadExtra, 2) }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-green-700 dark:text-green-400">Inversión Extra</span>
                                <span class="text-sm font-black text-green-800 dark:text-white">${{ number_format($inversionExtra, 2) }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-green-700 dark:text-green-400">Retorno (ROI)</span>
                                <span class="text-sm font-black text-green-800 dark:text-white">{{ number_format($retornoInversion, 2) }}x</span>
                            </div>
                        </div>

                        <!-- Textareas -->
                        <div class="space-y-3">
                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Recomendaciones Técnicas *</label>
                                <textarea wire:model="recomendaciones" rows="2" maxlength="500" placeholder="Recomendaciones agronómicas..." class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none focus:border-green-500 resize-none"></textarea>
                                @error('recomendaciones') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Propuesta Técnica *</label>
                                <textarea wire:model="propuesta" rows="2" maxlength="500" placeholder="Detalle de la propuesta de fertilización aplicada..." class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none focus:border-green-500 resize-none"></textarea>
                                @error('propuesta') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Buenas Prácticas Agrícolas *</label>
                                <textarea wire:model="buenasPracticas" rows="2" maxlength="500" placeholder="Buenas prácticas implementadas durante el ciclo..." class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none focus:border-green-500 resize-none"></textarea>
                                @error('buenasPracticas') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                        <button type="button" wire:click="close" class="px-5 py-2.5 rounded-2xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 font-bold text-xs hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            Cancelar
                        </button>
                        <button type="button" wire:click="saveResults" wire:loading.attr="disabled" class="px-6 py-2.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                            <span wire:loading.remove wire:target="saveResults">Guardar Dictamen</span>
                            <span wire:loading wire:target="saveResults">
                                <i class="fa-solid fa-circle-notch fa-spin"></i> Guardando...
                            </span>
                        </button>
                    </div>
                @else
                    <!-- Success View with PDF and WhatsApp -->
                    <div class="text-center py-6 space-y-6">
                        <div class="w-16 h-16 rounded-full bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center mx-auto text-3xl shadow-inner">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>

                        <div>
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400">
                                Folio #{{ $planCode }}
                            </span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mt-2">¡Resultados Guardados con Éxito!</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                                Se ha registrado el dictamen agronómico para {{ $customerName }}. Puedes descargar el PDF o compartirlo vía WhatsApp.
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                            <button
                                type="button"
                                wire:click="downloadPdf"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-bold text-xs shadow-md hover:bg-green-600 dark:hover:bg-green-400 transition-colors"
                            >
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>Descargar Dictamen PDF</span>
                            </button>

                            <button
                                type="button"
                                wire:click="shareWhatsApp"
                                wire:loading.attr="disabled"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-colors"
                            >
                                <span wire:loading.remove wire:target="shareWhatsApp" class="flex items-center gap-2">
                                    <i class="fa-brands fa-whatsapp text-sm"></i>
                                    <span>Compartir por WhatsApp</span>
                                </span>
                                <span wire:loading wire:target="shareWhatsApp" class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                                    <span>Enviando...</span>
                                </span>
                            </button>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-white/5">
                            <button type="button" wire:click="close" class="text-xs font-bold text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                                Cerrar ventana
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
