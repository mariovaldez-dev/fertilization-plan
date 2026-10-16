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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-gray-100 dark:border-white/5">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20">
                        @if($isVersioning)
                            <i class="fa-solid fa-code-branch text-xl"></i>
                        @else
                            <i class="fa-solid fa-flask-vial text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                                @if($isVersioning)
                                    Versionar Plan (Base: V{{ $baseVersion }})
                                @else
                                    Generar Plan de Fertilización
                                @endif
                            </h1>
                            @if($isVersioning)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                                    Nueva Versión: V{{ $newVersion }} • Plan #{{ $basePlanCode }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-0.5">
                            @if($isVersioning)
                                Ajusta los productos, dosis y aplicaciones por etapa fenológica para esta versión.
                            @else
                                Configuración agronómica por etapas fenológicas y georreferenciación
                            @endif
                        </p>
                    </div>
                </div>
                @if($isVersioning)
                    <div class="self-start sm:self-center">
                        <span class="inline-flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 px-3 py-1 rounded-full">
                            <i class="fa-solid fa-lock text-[10px] text-gray-400"></i>
                            <span>Datos del lote inmutables</span>
                        </span>
                    </div>
                @endif
            </div>

            @if($isVersioning)
                @if(!$isLoaded)
                    <!-- Skeleton del Resumen del Lote mientras carga la información -->
                    <div class="bg-gray-50/70 dark:bg-white/[0.02] border border-gray-200/70 dark:border-white/5 rounded-2xl p-4 sm:p-5 animate-pulse">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                            @for($i = 0; $i < 4; $i++)
                                <div class="space-y-2">
                                    <div class="h-2.5 bg-gray-200 dark:bg-white/10 rounded w-16"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-28"></div>
                                    <div class="h-3 bg-gray-100 dark:bg-white/5 rounded w-20"></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @else
                    <!-- Resumen Compacto y Limpio del Lote (Inmutable) -->
                    <div class="bg-gray-50/70 dark:bg-white/[0.02] border border-gray-200/70 dark:border-white/5 rounded-2xl p-4 sm:p-5">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                            
                            <!-- Col 1: Productor -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 block">
                                    Productor
                                </span>
                                <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight truncate" title="{{ $customerName }}">
                                    {{ $customerName ?: 'Cliente no especificado' }}
                                </div>
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500 block">
                                    {{ $customerCode ?: 'S/C' }}
                                </span>
                            </div>

                            <!-- Col 2: Lote & Superficie -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 block">
                                    Lote / Parcela
                                </span>
                                <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight truncate" title="{{ $parcela }}">
                                    {{ $parcela ?: 'Parcela 1' }}
                                </div>
                                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <span>{{ $hectares }} Ha</span>
                                </div>
                            </div>

                            <!-- Col 3: Cultivo & Suelo -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 block">
                                    Cultivo & Suelo
                                </span>
                                <div class="text-sm font-bold text-gray-900 dark:text-white leading-tight flex items-center gap-2">
                                <span class="truncate">{{ $currentCropName ?: 'Cultivo' }}</span>
                                    @if(strtoupper((string)$analisisSuelo) === 'SI')
                                        <span class="inline-flex items-center text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20" title="Cuenta con análisis de suelo">
                                            Análisis ✓
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    <span>{{ $this->soilTypeName }}</span>
                                    @if(!empty($seedVariety) && !in_array(strtolower($seedVariety), ['sin variedad', 'n/d']))
                                        <span>• {{ $seedVariety }}</span>
                                    @endif
                                    @if(!empty($targetYield))
                                        <span>• {{ $targetYield }} Ton/Ha</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Col 4: Asesor Técnico -->
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 block">
                                    Asesor Técnico
                                </span>
                                <div class="text-xs font-bold text-gray-900 dark:text-white leading-tight truncate" title="{{ $advisorName }}">
                                    {{ $advisorName ?: 'Asesor Comercial' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    @if(!empty($advisorPhone))
                                        <a class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-phone text-[9px] text-green-600"></i>{{ $advisorPhone }}
                                        </a>
                                    @elseif(!empty($zone) && $zone !== 'N/D')
                                        <span>Zona {{ $zone }}</span>
                                    @elseif(!empty($territory) && $territory !== 'N/D')
                                        <span>{{ $territory }}</span>
                                    @else
                                        <span>Grupo Impulsora</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                @endif
            @else
                <!-- Stepper UI -->
                @include('livewire.wizard.steps.stepper')
            @endif

            <!-- ========================================== -->
            <!-- CONTENIDO MODULAR POR PASOS CON SKELETONS  -->
            <!-- ========================================== -->
            <div class="relative min-h-[360px]">
                @if(!$isLoaded)
                    <!-- Skeleton de Carga Inicial Asíncrona (UI First) -->
                    @if($isVersioning)
                        @include('livewire.wizard.skeletons.step-3-skeleton')
                    @else
                        @include('livewire.wizard.skeletons.step-1-skeleton')
                    @endif
                @else
                    <div class="space-y-6">
                        @if($isVersioning)
                            <div class="step-transition">
                                @include('livewire.wizard.steps.step-3')
                            </div>
                        @else
                            <div x-show="$wire.currentStep === 1" class="step-transition">
                                @include('livewire.wizard.steps.step-1')
                            </div>
                            <div x-show="$wire.currentStep === 2" x-cloak class="step-transition" style="display: none;">
                                @include('livewire.wizard.steps.step-2')
                            </div>
                            <div x-show="$wire.currentStep === 3" x-cloak class="step-transition" style="display: none;">
                                @include('livewire.wizard.steps.step-3')
                            </div>
                        @endif
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
            <div class="fixed inset-0 bg-black/60 dark:bg-black/80"></div>

            <!-- Panel del Modal Bento Style (Compacto max-w-sm como en React) -->
            <div class="relative w-full max-w-sm bg-white dark:bg-[#131b20] rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-white/10 text-center space-y-4">
                
                <!-- Botón Cerrar (X) arriba a la derecha que redirige al Dashboard/Seguimiento como en React -->
                <button 
                    type="button"
                    wire:click="closeSuccessModal" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors p-1 cursor-pointer"
                    title="{{ $isVersioning ? 'Cerrar y volver al Seguimiento' : 'Cerrar y volver al Dashboard' }}"
                >
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>

                <!-- Icono Check Verde -->
                <div class="mx-auto bg-green-100 dark:bg-green-500/20 rounded-full h-16 w-16 flex items-center justify-center border border-green-500/30">
                    <i class="fa-solid fa-check text-green-600 dark:text-green-400 text-2xl"></i>
                </div>

                <div class="space-y-1">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                        @if($isVersioning)
                            ¡Versión V{{ $newVersion }} creada exitosamente!
                        @else
                            El plan ha sido guardado exitosamente
                        @endif
                    </h2>
                </div>

                @if($savedPlanCode)
                    <div class="p-2.5 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl flex items-center justify-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-green-700 dark:text-green-400">
                            {{ $isVersioning ? 'Plan Versionado:' : 'Folio del Plan:' }}
                        </span>
                        <span class="text-sm font-black text-green-800 dark:text-green-300 font-mono">{{ $savedPlanCode }}</span>
                        @if($isVersioning)
                            <span class="ml-1 px-2 py-0.5 bg-green-600 text-white text-xs font-bold rounded-md">V{{ $newVersion }}</span>
                        @endif
                    </div>
                @endif

                <!-- Notificaciones de WhatsApp -->
                @if($whatsAppSuccess)
                    <div class="p-3 bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 rounded-xl text-xs font-bold flex items-center gap-2 text-left">
                        <i class="fa-solid fa-circle-check text-base shrink-0"></i>
                        <span>{{ $whatsAppSuccess }}</span>
                    </div>
                @endif

                @if($whatsAppError)
                    <div class="p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 rounded-xl text-xs font-bold flex items-center gap-2 text-left">
                        <i class="fa-solid fa-triangle-exclamation text-base shrink-0"></i>
                        <span>{{ $whatsAppError }}</span>
                    </div>
                @endif

                <!-- Banner Advertencia Falta Celular Registrado -->
                @if($this->isCustomerPhoneMissing)
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs rounded-xl flex items-center gap-2.5 text-left border border-amber-200 dark:border-amber-800/40 shadow-xs">
                        <i class="fa-solid fa-triangle-exclamation flex-shrink-0 text-base text-amber-600 dark:text-amber-400"></i>
                        <div class="leading-snug">
                            <span class="font-bold block">Cliente sin celular registrado</span>
                            <span class="text-[11px] opacity-90">El cliente no cuenta con número celular en SAP. No es posible compartir por WhatsApp.</span>
                        </div>
                    </div>
                @endif

                <!-- Dos Botones Horizontales: Compartir y Descargar -->
                <div class="flex justify-center gap-3 pt-2">
                    <!-- Botón Compartir (WhatsApp al Cliente) -->
                    <button
                        type="button"
                        wire:click="shareViaWhatsApp"
                        wire:loading.attr="disabled"
                        wire:target="shareViaWhatsApp"
                        @if($this->isCustomerPhoneMissing) disabled title="El cliente no cuenta con celular registrado en SAP" @endif
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-lg shadow-green-600/30 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer disabled:bg-gray-400 disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none text-sm"
                    >
                        <span wire:loading.remove wire:target="shareViaWhatsApp" class="flex items-center gap-2">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                            <span>Compartir</span>
                        </span>
                        <span wire:loading wire:target="shareViaWhatsApp" class="flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin text-base"></i>
                            <span>Enviando...</span>
                        </span>
                    </button>

                    <!-- Botón Descargar PDF -->
                    <button
                        type="button"
                        wire:click="downloadProposalPdf"
                        wire:loading.attr="disabled"
                        wire:target="downloadProposalPdf"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-white/10 dark:hover:bg-white/20 text-gray-800 dark:text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer disabled:opacity-50 text-sm"
                    >
                        <span wire:loading.remove wire:target="downloadProposalPdf" class="flex items-center gap-2">
                            <i class="fa-solid fa-download text-base"></i>
                            <span>Descargar</span>
                        </span>
                        <span wire:loading wire:target="downloadProposalPdf" class="flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin text-base"></i>
                            <span>Generando...</span>
                        </span>
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
