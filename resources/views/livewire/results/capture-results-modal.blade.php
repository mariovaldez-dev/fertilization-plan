<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="relative w-full max-w-xl bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl p-6 lg:p-8 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-green-600 dark:text-green-400">Resultados Agronómicos</span>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">Dictamen Final de Cosecha</h2>
                    </div>
                    <button type="button" wire:click="close" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Form Fields -->
                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-[#1A2227] space-y-1">
                        <span class="text-xs text-gray-400">Plan de Fertilización:</span>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $customerName }} ({{ $planCode }})</p>
                    </div>

                    <div>
                        <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Rendimiento Obtenido (Ton/Ha) *</label>
                        <input type="number" step="0.01" wire:model="finalYieldTonPerHa" placeholder="Ej: 14.5" class="w-full h-12 px-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1A2227] text-sm font-bold text-gray-900 dark:text-white focus:border-green-500 focus:outline-none">
                        @error('finalYieldTonPerHa') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Fecha de Cosecha / Corte *</label>
                        <input type="date" wire:model="harvestDate" class="w-full h-12 px-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1A2227] text-sm text-gray-900 dark:text-white focus:border-green-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">Conclusiones y Observaciones Agronómicas</label>
                        <textarea wire:model="agronomicConclusions" rows="4" placeholder="Observaciones técnicas de sanidad vegetal, calibre y respuesta nutricional..." class="w-full p-3 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:border-green-500 focus:outline-none"></textarea>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                    <button type="button" wire:click="close" class="px-5 py-2.5 rounded-2xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 font-bold text-xs hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        Cancelar
                    </button>
                    <button type="button" wire:click="saveResults" class="px-6 py-2.5 rounded-2xl bg-green-600 hover:bg-green-500 text-white font-bold text-xs shadow-md transition-all">
                        Guardar Dictamen
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
