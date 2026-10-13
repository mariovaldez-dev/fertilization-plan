<!-- Stepper UI Component -->
<div class="overflow-x-auto pb-2">
    <div class="flex items-center min-w-max">
        @foreach(['Datos Generales', 'Cultivos', 'Propuesta'] as $index => $title)
            <div class="flex items-center flex-shrink-0 p-1">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 ease-out {{ $currentStep >= $index + 1 ? 'bg-green-600 text-white shadow-sm scale-105' : 'bg-gray-200 dark:bg-white/10 text-gray-500 dark:text-gray-400' }}">
                    {{ $index + 1 }}
                </div>
                <p class="ml-2 mr-4 font-semibold whitespace-nowrap text-sm transition-colors duration-300 ease-out {{ $currentStep >= $index + 1 ? 'text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                    {{ $title }}
                </p>
            </div>
            @if($index < 2)
                <div class="flex-auto w-12 md:w-24 border-t-2 mx-2 md:mx-4 transition-colors duration-300 ease-out {{ $currentStep > $index + 1 ? 'border-green-600' : 'border-gray-200 dark:border-white/10' }}"></div>
            @endif
        @endforeach
    </div>
</div>
