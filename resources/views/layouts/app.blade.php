<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'Plan de Fertilización') }} - Grupo Impulsora</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 & Lucide Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        green: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }
        
        .login-background {
            background-image: url('https://images.unsplash.com/photo-1560493676-04071c5f467b?q=80&w=1974&auto=format&fit=crop');
            height: 100%;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        /* Custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* SweetAlert2 Institutional Styling */
        .swal2-popup {
            font-family: 'Inter', sans-serif !important;
            border-radius: 1.5rem !important;
        }
        .dark .swal2-popup {
            background-color: #131b20 !important;
            color: #f3f4f6 !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
        }
        .dark .swal2-title {
            color: #ffffff !important;
        }
        .dark .swal2-html-container {
            color: #94a3b8 !important;
        }
        .swal2-confirm {
            background-color: #16a34a !important;
            border-radius: 0.875rem !important;
            font-weight: 700 !important;
            padding: 0.65rem 1.5rem !important;
            box-shadow: 0 4px 14px 0 rgba(22, 163, 74, 0.35) !important;
        }
        .swal2-confirm:hover {
            background-color: #15803d !important;
        }
        .swal2-cancel {
            border-radius: 0.875rem !important;
            font-weight: 600 !important;
            padding: 0.65rem 1.5rem !important;
        }
    </style>
    
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800 min-h-full flex flex-col">

    {{ $slot }}

    @livewireScripts

    <script>
        document.addEventListener('livewire:init', () => {
            const isDark = () => document.documentElement.classList.contains('dark');
            const getSwalTheme = () => ({
                background: isDark() ? '#131b20' : '#ffffff',
                color: isDark() ? '#f3f4f6' : '#1f2937',
            });

            // 1. Toasts reactivos para notify
            Livewire.on('notify', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                const type = data.type || 'info';
                const message = data.message || data.text || '';
                const theme = getSwalTheme();

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: type,
                    title: message,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: theme.background,
                    color: theme.color,
                    customClass: {
                        popup: 'rounded-2xl border border-gray-100 dark:border-white/10 shadow-xl'
                    }
                });
            });

            // 2. Diálogos Modales (Error, Warning, Success, Info)
            Livewire.on('swal:modal', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                const theme = getSwalTheme();

                Swal.fire({
                    icon: data.type || 'info',
                    title: data.title || '',
                    text: data.text || data.message || '',
                    html: data.html || undefined,
                    confirmButtonText: data.confirmButtonText || 'Entendido',
                    confirmButtonColor: data.type === 'error' ? '#ef4444' : '#16a34a',
                    background: theme.background,
                    color: theme.color,
                    customClass: {
                        popup: 'rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl'
                    }
                });
            });

            // 3. Diálogo de Éxito con Redirección
            Livewire.on('swal:success-redirect', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                const theme = getSwalTheme();

                Swal.fire({
                    icon: 'success',
                    title: data.title || '¡Operación Exitosa!',
                    text: data.text || data.message || '',
                    confirmButtonText: data.confirmButtonText || 'Aceptar',
                    confirmButtonColor: '#16a34a',
                    timer: data.timer || 4000,
                    timerProgressBar: true,
                    background: theme.background,
                    color: theme.color,
                    customClass: {
                        popup: 'rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl'
                    }
                }).then(() => {
                    if (data.redirectUrl) {
                        window.location.href = data.redirectUrl;
                    }
                });
            });

            // 4. Diálogo de Confirmación
            Livewire.on('swal:confirm', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                const theme = getSwalTheme();

                Swal.fire({
                    icon: data.type || 'warning',
                    title: data.title || '¿Estás seguro?',
                    text: data.text || '',
                    showCancelButton: true,
                    confirmButtonText: data.confirmButtonText || 'Confirmar',
                    cancelButtonText: data.cancelButtonText || 'Cancelar',
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#ef4444',
                    background: theme.background,
                    color: theme.color,
                    customClass: {
                        popup: 'rounded-3xl border border-gray-100 dark:border-white/10 shadow-2xl'
                    }
                }).then((result) => {
                    if (result.isConfirmed && data.action) {
                        Livewire.dispatch(data.action, data.params || {});
                    }
                });
            });
        });
    </script>
</body>
</html>
