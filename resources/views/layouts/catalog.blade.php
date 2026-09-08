<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'LabCatalog' }} - Laboratorium Teknik Mesin Unwahas</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col antialiased" x-data="{ mobileMenuOpen: false }">
    <!-- TopNavBar -->
    <nav class="bg-surface border-b border-outline-variant shadow-sm fixed top-0 w-full z-50">
        <div class="flex justify-between items-center h-16 px-gutter-md max-w-container-max mx-auto">
            <div class="flex items-center gap-8">
                <!-- Brand -->
                <a href="{{ route('catalog.index') }}" class="font-headline-sm text-headline-sm text-primary font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">science</span>
                    LabCatalog
                </a>

                <!-- Nav links (Desktop) -->
                <div class="hidden md:flex gap-6 items-center">
                    <a href="{{ route('catalog.index') }}" 
                       class="{{ request()->routeIs('catalog.*') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary transition-colors duration-200' }} font-body-md text-body-md">
                        Katalog
                    </a>

                    @auth
                        <a href="{{ route('loans.history') }}" 
                           class="{{ request()->routeIs('loans.history') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary transition-colors duration-200' }} font-body-md text-body-md">
                            Riwayat Pengajuan
                        </a>
                        <a href="{{ route('loans.create') }}" 
                           class="{{ request()->routeIs('loans.create') ? 'text-primary font-bold border-b-2 border-primary pb-1' : 'text-on-surface-variant hover:text-primary transition-colors duration-200' }} font-body-md text-body-md">
                            Ajukan Peminjaman
                        </a>
                    @endauth

                    <a href="#panduan" class="text-on-surface-variant hover:text-primary transition-colors duration-200 font-body-md text-body-md">
                        Panduan
                    </a>
                    <a href="#kontak" class="text-on-surface-variant hover:text-primary transition-colors duration-200 font-body-md text-body-md">
                        Kontak
                    </a>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="hidden md:flex items-center gap-4">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="bg-primary-container text-on-primary-container font-label-md text-label-md px-4 py-2 rounded-lg shadow-sm hover:opacity-90 transition-opacity flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">dashboard</span>
                            Dashboard Admin
                        </a>
                    @else
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="font-label-md text-label-md text-on-surface font-medium leading-none">{{ auth()->user()->name }}</p>
                                <p class="font-caption-xs text-caption-xs text-on-surface-variant">{{ auth()->user()->identity_number ?? 'Mahasiswa' }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 text-on-surface-variant hover:text-error hover:bg-surface-container rounded-lg transition-colors" title="Keluar">
                                    <span class="material-symbols-outlined text-xl">logout</span>
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors shadow-sm">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-dim transition-colors border border-outline-variant">
                        Daftar
                    </a>
                @endauth
            </div>

            <!-- Mobile Menu Toggle Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-on-surface-variant p-2 rounded-md hover:bg-surface-container">
                <span class="material-symbols-outlined" x-text="mobileMenuOpen ? 'close' : 'menu'">menu</span>
            </button>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition 
             class="md:hidden bg-surface border-b border-outline-variant px-4 py-4 space-y-3"
             style="display: none;">
            <a href="{{ route('catalog.index') }}" class="block font-label-md text-label-md text-on-surface py-2">Katalog Alat</a>
            @auth
                <a href="{{ route('loans.history') }}" class="block font-label-md text-label-md text-on-surface py-2">Riwayat Pengajuan</a>
                <a href="{{ route('loans.create') }}" class="block font-label-md text-label-md text-on-surface py-2">Ajukan Peminjaman</a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="block font-label-md text-label-md text-primary font-bold py-2">Dashboard Admin</a>
                @endif
                <div class="pt-3 border-t border-outline-variant flex items-center justify-between">
                    <div>
                        <p class="font-label-md text-label-md text-on-surface">{{ auth()->user()->name }}</p>
                        <p class="font-caption-xs text-caption-xs text-on-surface-variant">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-error font-label-md text-label-md">Keluar</button>
                    </form>
                </div>
            @else
                <div class="pt-3 border-t border-outline-variant flex gap-2">
                    <a href="{{ route('login') }}" class="flex-1 text-center bg-primary text-on-primary font-label-md text-label-md py-2 rounded-lg">Masuk</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center bg-surface-container-high text-on-surface font-label-md text-label-md py-2 rounded-lg border border-outline-variant">Daftar</a>
                </div>
            @endauth
        </div>
    </nav>

    <!-- Main Content Canvas -->
    <main class="flex-grow pt-16">
        <!-- Flash Alerts -->
        @if (session('success'))
            <div class="max-w-container-max mx-auto px-gutter-md mt-6">
                <div class="bg-status-success-bg border border-status-success-text/30 text-status-success-text px-4 py-3 rounded-lg shadow-sm flex items-center justify-between" role="alert">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-status-success-text">check_circle</span>
                        <span class="font-label-md text-label-md">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-status-success-text hover:opacity-70">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-container-max mx-auto px-gutter-md mt-6">
                <div class="bg-error-container border border-error/30 text-on-error-container px-4 py-3 rounded-lg shadow-sm flex items-center justify-between" role="alert">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">error</span>
                        <span class="font-label-md text-label-md">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-on-error-container hover:opacity-70">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer Component -->
    <footer class="bg-sidebar-dark w-full mt-auto">
        <div class="flex flex-col md:flex-row justify-between items-center py-8 px-gutter-md max-w-container-max mx-auto gap-4">
            <!-- Brand / Copyright -->
            <div class="flex flex-col items-center md:items-start gap-2">
                <span class="font-headline-sm text-headline-sm text-primary-fixed font-bold">LabCatalog Unwahas</span>
                <span class="font-caption-xs text-caption-xs text-surface-variant opacity-70">© 2026 Laboratorium Proses Produksi Teknik Mesin Universitas Wahid Hasyim</span>
            </div>
            <!-- Footer Links -->
            <div class="flex items-center gap-6">
                <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Kebijakan Privasi</a>
                <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Syarat &amp; Ketentuan</a>
                <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Bantuan</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
