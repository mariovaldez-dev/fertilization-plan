<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="relative w-full max-w-4xl bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl p-6 lg:p-8 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/5 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">Dictamen Técnico Comparativo (IMP-07)</span>
                        <h2 class="text-xl font-black text-gray-900 dark:text-white">Reporte de Resultados — {{ $planCode }}</h2>
                    </div>
                    <button type="button" wire:click="close" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- PDF Preview Frame & WhatsApp Send Form -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 rounded-2xl overflow-hidden border border-gray-200 dark:border-white/10 h-[480px] bg-gray-100 dark:bg-black/20">
                        @if($pdfBase64)
                            <iframe src="data:application/pdf;base64,{{ $pdfBase64 }}" class="w-full h-full"></iframe>
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                                Generando reporte de resultados y comparativa dual...
                            </div>
                        @endif
                    </div>

                    <!-- WhatsApp Section (IMP-03) -->
                    <div class="p-5 rounded-2xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-500/10 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2 text-blue-800 dark:text-blue-300">
                                <i class="fa-brands fa-whatsapp text-xl text-green-600 dark:text-green-400"></i>
                                <h3 class="text-sm font-bold">Enviar Dictamen por WhatsApp</h3>
                            </div>
                            <p class="text-xs text-blue-700 dark:text-blue-400">Envía el dictamen comparativo comercial vs testigo al productor.</p>

                            <div>
                                <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-600 dark:text-gray-400 mb-1">Teléfono (10 dígitos) *</label>
                                <input type="text" wire:model="recipientPhone" placeholder="Ej: 6671234567" class="w-full h-11 px-3 rounded-xl border border-blue-200 dark:border-blue-500/20 bg-white dark:bg-[#1A2227] text-xs font-bold text-gray-900 dark:text-white focus:outline-none">
                                @error('recipientPhone') <span class="text-red-500 text-[11px] mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[11px] uppercase tracking-widest text-gray-600 dark:text-gray-400 mb-1">Mensaje Personalizado (Opcional)</label>
                                <textarea wire:model="customMessage" rows="3" placeholder="Mensaje de acompañamiento..." class="w-full p-2.5 rounded-xl border border-blue-200 dark:border-blue-500/20 bg-white dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none"></textarea>
                            </div>
                        </div>

                        <div class="space-y-2 pt-3 border-t border-blue-200/40 dark:border-blue-500/10">
                            <button type="button" wire:click="sendViaWhatsApp" wire:loading.attr="disabled" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                <span wire:loading.remove wire:target="sendViaWhatsApp">Enviar Dictamen</span>
                                <span wire:loading wire:target="sendViaWhatsApp">Enviando WhatsApp...</span>
                            </button>

                            @if($pdfBase64)
                                <a href="data:application/pdf;base64,{{ $pdfBase64 }}" download="Dictamen_Resultados_{{ $planCode }}.pdf" class="w-full py-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 font-bold text-xs text-center block hover:bg-white dark:hover:bg-white/5 transition-colors">
                                    <i class="fa-solid fa-download mr-1"></i> Descargar PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
