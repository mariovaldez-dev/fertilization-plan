<!-- Navigation Buttons Component -->
<div class="mt-10 flex flex-col sm:flex-row justify-between items-center gap-4 pt-4 border-t border-gray-100 dark:border-white/10">
    @if($isVersioning)
        <a
            href="{{ route('seguimiento.index') }}"
            class="bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 font-bold py-2.5 px-6 rounded-xl transition-colors w-full sm:w-auto text-center text-sm cursor-pointer inline-flex items-center justify-center gap-2"
        >
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Regresar al Seguimiento</span>
        </a>

        <div class="flex items-center gap-4 w-full sm:w-auto">
            <button
                type="button"
                wire:click="savePlan"
                wire:loading.attr="disabled"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-8 rounded-xl transition-colors w-full sm:w-auto text-sm shadow-md flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer min-w-[180px]"
            >
                <span wire:loading.remove wire:target="savePlan" class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Guardar Versión</span>
                </span>
                <span wire:loading wire:target="savePlan" class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Guardando Versión...</span>
                </span>
            </button>
        </div>
    @elseif($currentStep === 1)
        <button
            type="button"
            @click="showExitModal = true"
            class="bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 font-bold py-2.5 px-6 rounded-xl transition-colors w-full sm:w-auto text-center text-sm cursor-pointer"
        >
            Regresar al Dashboard
        </button>
    @else
        <button
            type="button"
            wire:click="previousStep"
            class="bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 font-bold py-2.5 px-6 rounded-xl transition-colors w-full sm:w-auto text-sm cursor-pointer"
        >
            Anterior
        </button>
    @endif

    @if(!$isVersioning)
        <div class="flex items-center gap-4 w-full sm:w-auto">
            @if($currentStep < 3)
                <button
                    type="button"
                    @click="
                        if ($wire.currentStep === 1 && typeof window._capturePlanWizardMap === 'function') {
                            try { await window._capturePlanWizardMap(); } catch(e){}
                        }
                        $wire.nextStep();
                    "
                    wire:loading.attr="disabled"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-8 rounded-xl transition-colors w-full sm:w-auto text-sm shadow-md cursor-pointer flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove wire:target="nextStep">Continuar</span>
                    <span wire:loading wire:target="nextStep">
                        <i class="fa-solid fa-spinner fa-spin"></i> Validando...
                    </span>
                </button>
            @else
                <button
                    type="button"
                    wire:click="savePlan"
                    wire:loading.attr="disabled"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-8 rounded-xl transition-colors w-full sm:w-auto text-sm shadow-md flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
                >
                    <span wire:loading.remove wire:target="savePlan">Guardar Plan</span>
                    <span wire:loading wire:target="savePlan">
                        <i class="fa-solid fa-spinner fa-spin"></i> Guardando...
                    </span>
                </button>
            @endif
        </div>
    @endif
</div>
