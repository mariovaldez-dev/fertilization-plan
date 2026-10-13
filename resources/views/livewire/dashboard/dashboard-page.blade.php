<div class="bg-gray-50 min-h-screen flex flex-col">
    <x-header />

    <main class="p-4 sm:p-6 md:p-8 flex-1">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                <!-- Generar Plan de Fertilización -->
                <a 
                    href="{{ route('plan.create') }}" 
                    class="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center justify-center text-center space-y-4 group cursor-pointer"
                >
                    <div class="h-16 w-16 flex items-center justify-center">
                        <i class="fa-solid fa-leaf text-5xl text-green-600 group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-700">Generar Plan de Fertilización</h2>
                </a>

                <!-- Seguimiento, aplicación y captura de resultados -->
                <a 
                    href="{{ route('seguimiento.index') }}" 
                    class="bg-white p-8 rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center justify-center text-center space-y-4 group cursor-pointer"
                >
                    <div class="h-16 w-16 flex items-center justify-center">
                        <i class="fa-solid fa-tractor text-5xl text-green-600 group-hover:scale-110 transition-transform"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-700">Seguimiento, aplicación y captura de resultados</h2>
                </a>
            </div>
        </div>
    </main>
</div>
