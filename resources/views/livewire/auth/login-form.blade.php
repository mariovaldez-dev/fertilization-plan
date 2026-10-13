<div class="login-background" x-data="{ showPassword: false }">
    <div class="w-full max-w-sm bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 space-y-6">
        <div class="text-center">
            <div class="flex justify-center items-center mb-4">
                <i class="fa-solid fa-leaf text-green-600 text-4xl"></i>
                <span class="text-2xl font-bold text-gray-800 ml-2">
                    Impulsora
                </span>
            </div>
            <h1 class="text-lg font-semibold text-gray-600">Plataforma</h1>
            <p class="text-gray-500">Plan de Fertilización</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-4">
            <div>
                <input
                    type="text"
                    placeholder="Usuario o Correo Electrónico"
                    wire:model="username"
                    class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-gray-800"
                    wire:loading.attr="disabled"
                />
                @error('username')
                    <p class="text-red-500 text-xs mt-1 text-left">{{ $message }}</p>
                @enderror
            </div>

            <div class="relative">
                <input
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Contraseña"
                    wire:model="password"
                    class="w-full px-4 py-3 pr-12 bg-gray-100 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-gray-800"
                    wire:loading.attr="disabled"
                />

                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition"
                    tabindex="-1"
                >
                    <i :class="showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'" class="text-lg"></i>
                </button>
                @error('password')
                    <p class="text-red-500 text-xs mt-1 text-left">{{ $message }}</p>
                @enderror
            </div>

            @error('auth_error')
                <p class="text-red-500 text-sm text-center">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition-colors duration-300 shadow-lg disabled:opacity-50 flex items-center justify-center gap-2"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="login">Iniciar Sesión</span>
                <span wire:loading wire:target="login" class="flex items-center gap-2">
                    <i class="fa-solid fa-spinner fa-spin"></i> Ingresando...
                </span>
            </button>
        </form>
    </div>
</div>