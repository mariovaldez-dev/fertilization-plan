<div class="space-y-6">
    <!-- Header with Amber Accent for Testigo -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-amber-500/10 dark:bg-amber-950/20 p-6 rounded-3xl border border-amber-200 dark:border-amber-500/20 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('seguimiento.index') }}" class="text-xs font-bold text-amber-700 dark:text-amber-400 hover:underline">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Volver al listado
                </a>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-amber-900 dark:text-amber-300">Seguimiento de Lote Testigo (Control)</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-200 dark:bg-amber-900/50 text-amber-900 dark:text-amber-200">{{ $plan['planCode'] ?? $planCode }}</span>
            </div>
            <p class="text-sm text-amber-800 dark:text-amber-400">Registro independiente del manejo tradicional del productor para comparativa agronómica</p>
        </div>

        <div class="flex items-center gap-3">
            @if(!$isReadOnly)
                <button wire:click="saveTestigoTracking" wire:loading.attr="disabled" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm shadow-lg shadow-amber-600/20 transition-all">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span wire:loading.remove wire:target="saveTestigoTracking">Guardar Testigo</span>
                    <span wire:loading wire:target="saveTestigoTracking">Guardando...</span>
                </button>
            @else
                <span class="px-4 py-2 rounded-2xl bg-gray-100 dark:bg-white/10 text-gray-500 font-bold text-xs flex items-center gap-2">
                    <i class="fa-solid fa-lock"></i> Modo Solo Lectura
                </span>
            @endif
        </div>
    </div>

    <!-- Testigo Stages List -->
    <div class="space-y-6">
        @foreach($stages as $sIdx => $stg)
            <div class="bg-white dark:bg-[#131B20] p-6 lg:p-8 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-white/5 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-amber-500 text-white font-black text-xs flex items-center justify-center">{{ $sIdx + 1 }}</span>
                        <h2 class="text-lg font-black text-gray-900 dark:text-white">{{ $stg['stageName'] ?? "Etapa {$sIdx}" }}</h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-gray-400">Fecha Aplicación:</label>
                            <input type="date" wire:model="stages.{{ $sIdx }}.applicationDate" class="h-9 px-3 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white focus:outline-none" {{ $isReadOnly ? 'disabled' : '' }}>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-flask"></i>
                            <span>Productos Tradicionales Aplicados en Parcela Testigo</span>
                        </h3>
                        @if(!$isReadOnly)
                            <button type="button" wire:click="addProduct({{ $sIdx }})" class="text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                                + Agregar Producto
                            </button>
                        @endif
                    </div>

                    @if(!empty($stg['otherProducts']))
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="text-gray-400 border-b border-gray-100 dark:border-white/5 pb-2">
                                        <th class="py-2">Producto / Fórmula</th>
                                        <th class="py-2">Dosis / Ha</th>
                                        <th class="py-2">Unidad</th>
                                        <th class="py-2">Precio Unit. ($)</th>
                                        <th class="py-2">Método</th>
                                        @if(!$isReadOnly)<th class="py-2 text-right">Acción</th>@endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                    @foreach($stg['otherProducts'] as $pIdx => $prod)
                                        <tr>
                                            <td class="py-2.5 pr-2">
                                                <input type="text" wire:model="stages.{{ $sIdx }}.otherProducts.{{ $pIdx }}.itemName" class="w-full h-9 px-3 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-900 dark:text-white" {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            <td class="py-2.5 pr-2 w-28">
                                                <input type="number" step="0.01" wire:model="stages.{{ $sIdx }}.otherProducts.{{ $pIdx }}.dosePerHectare" class="w-full h-9 px-2 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-bold text-gray-900 dark:text-white" {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            <td class="py-2.5 pr-2 w-24">
                                                <input type="text" wire:model="stages.{{ $sIdx }}.otherProducts.{{ $pIdx }}.unitOfMeasure" class="w-full h-9 px-2 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs text-gray-700 dark:text-gray-300" {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            <td class="py-2.5 pr-2 w-32">
                                                <input type="number" step="0.01" wire:model="stages.{{ $sIdx }}.otherProducts.{{ $pIdx }}.unitPrice" class="w-full h-9 px-2 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs font-bold text-amber-600 dark:text-amber-400" {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            <td class="py-2.5 pr-2 w-36">
                                                <input type="text" wire:model="stages.{{ $sIdx }}.otherProducts.{{ $pIdx }}.applicationMethod" class="w-full h-9 px-2 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#1A2227] text-xs" {{ $isReadOnly ? 'disabled' : '' }}>
                                            </td>
                                            @if(!$isReadOnly)
                                                <td class="py-2.5 text-right">
                                                    <button type="button" wire:click="removeProduct({{ $sIdx }}, {{ $pIdx }})" class="p-1.5 text-gray-400 hover:text-red-500">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
