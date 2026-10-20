<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'LabCatalog' }} - Laboratorium Teknik Mesin Unwahas</title>

        <!-- Google Fonts: Plus Jakarta Sans, Inter, & Material Symbols -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#FAFAFA] text-slate-800 font-sans antialiased min-h-[100dvh] flex flex-col justify-between">
        <!-- Top App Bar -->
        <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
                <a href="{{ route('catalog.index') }}" class="group">
                    <x-application-logo />
                </a>

                <a href="{{ route('catalog.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 px-3.5 py-1.5 rounded-full border border-slate-200 hover:border-blue-200 hover:bg-blue-50/50 transition-all">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Katalog</span>
                </a>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col items-center justify-center px-4 sm:px-6 py-10 sm:py-14 w-full">
            <div class="w-full sm:max-w-md bg-white border border-slate-200/90 rounded-2xl shadow-sm p-6 sm:p-8 relative overflow-hidden">
                <!-- Top Accent Line -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-blue-600"></div>

                {{ $slot }}
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-5 text-center text-xs text-slate-400 border-t border-slate-200/70 bg-white/50">
            <p>&copy; {{ date('Y') }} Laboratorium Teknik Mesin &middot; Universitas Wahid Hasyim Semarang</p>
        </footer>
    </body>
</html>
