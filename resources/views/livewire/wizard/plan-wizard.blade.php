<div x-data="{ showExitModal: false }" class="bg-gray-50 dark:bg-[#0d1418] min-h-screen flex flex-col transition-colors duration-200" wire:init="loadInitialData">
    <style>
        @keyframes stepFadeIn {
            0% {
                opacity: 0;
                transform: translateY(12px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .step-transition {
            animation: stepFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <x-header />

    <main class="p-4 sm:p-6 md:p-8 flex-1">
        <div class="max-w-5xl mx-auto bg-white dark:bg-[#131b20] p-6 sm:p-8 rounded-3xl shadow-xl border border-gray-100 dark:border-white/5 space-y-8">
            
            <!-- Encabezado Principal -->
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-2xl bg-green-50 dark:bg-green-500/10 flex items-center justify-center mr-4 border border-green-100 dark:border-green-500/20 shrink-0">
                    <i class="fa-solid fa-flask-vial text-green-600 dark:text-green-400 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        Generar Plan de Fertilización
                    </h1>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                        Configuración agronómica por etapas fenológicas y georreferenciación
                    </p>
                </div>
            </div>

            <!-- Stepper UI -->
            @include('livewire.wizard.steps.stepper')

            <!-- ========================================== -->
            <!-- CONTENIDO MODULAR POR PASOS CON SKELETONS  -->
            <!-- ========================================== -->
            <div class="relative min-h-[360px]">
                @if(!$isLoaded)
                    <!-- Skeleton de Carga Inicial Asíncrona (UI First) -->
                    @include('livewire.wizard.skeletons.step-1-skeleton')
                @else
                    <div class="space-y-6">
                        <div x-show="$wire.currentStep === 1" class="step-transition">
                            @include('livewire.wizard.steps.step-1')
                        </div>
                        <div x-show="$wire.currentStep === 2" x-cloak class="step-transition" style="display: none;">
                            @include('livewire.wizard.steps.step-2')
                        </div>
                        <div x-show="$wire.currentStep === 3" x-cloak class="step-transition" style="display: none;">
                            @include('livewire.wizard.steps.step-3')
                        </div>
                    </div>
                @endif
            </div>

            <!-- Botones de Navegación -->
            @if($isLoaded)
                @include('livewire.wizard.steps.navigation')
            @else
                <!-- Skeleton de Navegación -->
                <div class="mt-10 flex justify-between items-center pt-4 border-t border-gray-100 dark:border-white/10 animate-pulse">
                    <div class="h-10 bg-gray-200 dark:bg-white/10 rounded-xl w-44"></div>
                    <div class="h-10 bg-gray-200 dark:bg-white/10 rounded-xl w-32"></div>
                </div>
            @endif

        </div>
    </main>

    <!-- ============================================== -->
    <!-- MODAL CUSTOM TAILWIND: ÉXITO AL GUARDAR PLAN   -->
    <!-- ============================================== -->
    @if($showSuccessModal)
        <div
            x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
        >
            <!-- Backdrop con Blur -->
            <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/75"></div>

            <!-- Panel del Modal Bento Style -->
            <div class="relative w-full max-w-lg bg-white dark:bg-[#131b20] rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-white/10 text-center space-y-6">
                <!-- Icono Animado con Anillo de Pulso Verde -->
                <div class="relative mx-auto w-20 h-20 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-green-500/20 animate-ping"></div>
                    <div class="relative w-20 h-20 rounded-full bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400 flex items-center justify-center border-2 border-green-500/30 shadow-inner">
                        <i class="fa-solid fa-check text-3xl"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        ¡Plan Registrado con Éxito!
                    </h3>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300 leading-relaxed">
                        {{ $successMessage }}
                    </p>
                </div>

                @if($savedPlanCode)
                    <div class="p-3.5 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-2xl flex items-center justify-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-green-700 dark:text-green-400">Folio del Plan:</span>
                        <span class="text-sm font-black text-green-800 dark:text-green-300 font-mono">{{ $savedPlanCode }}</span>
                    </div>
                @endif

                <!-- Campo de Envío por WhatsApp -->
                <div class="p-3.5 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/5 text-left space-y-1.5">
                    <label class="block text-xs font-bold text-gray-600 dark:text-gray-400">
                        Número de WhatsApp (opcional):
                    </label>
                    <div class="flex items-center gap-2">
                        <span class="text-emerald-500 text-sm shrink-0"><i class="fa-brands fa-whatsapp"></i></span>
                        <input 
                            type="text" 
                            wire:model="advisorPhone" 
                            placeholder="Celular para WhatsApp (10 dígitos)" 
                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#1A2227] border border-gray-200 dark:border-white/10 rounded-xl text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono"
                        />
                    </div>
                </div>

                <!-- Notificaciones de WhatsApp -->
                @if($whatsAppSuccess)
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-2xl text-xs font-bold flex items-center gap-2 text-left">
                        <i class="fa-solid fa-circle-check text-base shrink-0"></i>
                        <span>{{ $whatsAppSuccess }}</span>
                    </div>
                @endif

                @if($whatsAppError)
                    <div class="p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-2xl text-xs font-bold flex items-center gap-2 text-left">
                        <i class="fa-solid fa-triangle-exclamation text-base shrink-0"></i>
                        <span>{{ $whatsAppError }}</span>
                    </div>
                @endif

                <!-- Acciones Principales: Descargar PDF y WhatsApp -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <!-- Botón Descargar PDF -->
                    <button
                        type="button"
                        wire:click="downloadProposalPdf"
                        wire:loading.attr="disabled"
                        wire:target="downloadProposalPdf"
                        class="w-full px-4 py-3 bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900 rounded-2xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:-translate-y-0.5 active:translate-y-0 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="downloadProposalPdf" class="flex items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-red-500 text-base"></i>
                            <span>Descargar PDF</span>
                        </span>
                        <span wire:loading wire:target="downloadProposalPdf" class="flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            <span>Generando...</span>
                        </span>
                    </button>

                    <!-- Botón Enviar WhatsApp -->
                    <button
                        type="button"
                        wire:click="shareViaWhatsApp"
                        wire:loading.attr="disabled"
                        wire:target="shareViaWhatsApp"
                        class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-sm transition-all duration-200 shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="shareViaWhatsApp" class="flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                            <span>Enviar WhatsApp</span>
                        </span>
                        <span wire:loading wire:target="shareViaWhatsApp" class="flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            <span>Enviando...</span>
                        </span>
                    </button>
                </div>

                <!-- Acciones de Navegación -->
                <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center border-t border-gray-100 dark:border-white/5">
                    <a
                        href="{{ route('dashboard') }}"
                        class="w-full sm:w-1/2 px-5 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/15 text-gray-700 dark:text-gray-200 rounded-2xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 text-center"
                    >
                        <i class="fa-solid fa-house text-xs"></i>
                        <span>Ir al Dashboard</span>
                    </a>
                    <button
                        type="button"
                        wire:click="closeSuccessModal"
                        class="w-full sm:w-1/2 px-5 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-white/20 dark:hover:bg-white/25 text-gray-800 dark:text-white rounded-2xl font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                    >
                        <span>Ver Planes del Cliente</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ============================================== -->
    <!-- MODAL CUSTOM TAILWIND: ERROR AL GUARDAR PLAN   -->
    <!-- ============================================== -->
    @if($showErrorModal)
        <div
            x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
        >
            <!-- Backdrop con Blur -->
            <div
                wire:click="closeErrorModal"
                class="fixed inset-0 bg-gray-900/60 dark:bg-black/75 cursor-pointer"
            ></div>

            <!-- Panel del Modal Bento Style -->
            <div class="relative w-full max-w-lg bg-white dark:bg-[#131b20] rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-white/10 text-center space-y-6">
                <!-- Icono Animado con Anillo de Pulso Rojo -->
                <div class="relative mx-auto w-20 h-20 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-full bg-red-500/20 animate-ping"></div>
                    <div class="relative w-20 h-20 rounded-full bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 flex items-center justify-center border-2 border-red-500/30 shadow-inner">
                        <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        No se pudo guardar el plan
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Ocurrió un inconveniente durante el registro
                    </p>
                </div>

                <div class="p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 rounded-2xl text-left">
                    <p class="text-xs font-bold text-red-700 dark:text-red-300 leading-relaxed break-words">
                        {{ $errorMessage }}
                    </p>
                </div>

                <div class="pt-2 flex justify-center">
                    <button
                        type="button"
                        wire:click="closeErrorModal"
                        class="w-full px-6 py-3.5 bg-gray-900 dark:bg-white/10 hover:bg-gray-800 dark:hover:bg-white/20 text-white rounded-2xl font-black text-sm transition-all duration-200 shadow-md flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0"
                    >
                        <span>Entendido y Corregir</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- MODAL CUSTOM: CONFIRMAR SALIDA AL DASHBOARD (REACT EQUIV) -->
    <!-- ======================================================== -->
    <div
        x-show="showExitModal"
        x-cloak
        @keydown.escape.window="showExitModal = false"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
    >
        <!-- Backdrop -->
        <div 
            @click="showExitModal = false"
            class="fixed inset-0 bg-gray-900/60 dark:bg-black/75 cursor-pointer"
        ></div>

        <!-- Panel del Modal Bento Style -->
        <div 
            @click.outside="showExitModal = false"
            class="relative w-full max-w-md bg-white dark:bg-[#131b20] rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-white/10 text-center space-y-6"
        >
            <!-- Icono con Anillo de Alerta Ámbar -->
            <div class="relative mx-auto w-16 h-16 flex items-center justify-center">
                <div class="w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center border-2 border-amber-500/30 shadow-inner">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
            </div>

            <div class="space-y-2">
                <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                    ¿Salir al Dashboard?
                </h3>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300 leading-relaxed">
                    ¿Está seguro de salir? Los cambios no guardados se perderán.
                </p>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center">
                <button
                    type="button"
                    @click="showExitModal = false"
                    class="w-full sm:w-1/2 px-5 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/15 text-gray-700 dark:text-gray-200 rounded-2xl font-bold text-sm transition-all duration-200 cursor-pointer"
                >
                    Continuar editando
                </button>
                <a
                    href="{{ route('dashboard') }}"
                    class="w-full sm:w-1/2 px-5 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl font-black text-sm transition-all duration-200 shadow-lg shadow-amber-600/30 flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 text-center"
                >
                    <span>Sí, salir</span>
                </a>
            </div>
        </div>
    </div>
</div>
