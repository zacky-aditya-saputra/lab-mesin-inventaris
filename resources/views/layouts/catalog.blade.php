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
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col antialiased" x-data="{ mobileMenuOpen: false, userMenuOpen: false, logoutOpen: false }">
    <!-- TopNavBar (Glassmorphism) -->
    @php
        $authUser = auth()->user();
        $roleLabel = 'Mahasiswa';
        if ($authUser) {
            if ($authUser->isKepalaLab()) {
                $roleLabel = 'Kepala Lab';
            } elseif ($authUser->isLaboran()) {
                $roleLabel = 'Laboran '.str_replace('Laboratorium', 'Lab', $authUser->laboratory?->name ?? '');
            }
        }
    @endphp
    <nav class="bg-white/85 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-xs">
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

                    <a href="#panduan" class="text-on-surface-variant hover:text-primary transition-colors duration-200 font-body-md text-body-md">
                        Panduan
                    </a>
                    <a href="#kontak" class="text-on-surface-variant hover:text-primary transition-colors duration-200 font-body-md text-body-md">
                        Kontak
                    </a>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="hidden md:flex items-center gap-3">
                @auth
                    <x-notification-bell />

                    <!-- User Avatar Dropdown Menu -->
                    <div class="relative" @click.outside="userMenuOpen = false">
                        <button type="button" @click="userMenuOpen = !userMenuOpen"
                                class="flex items-center gap-2.5 pl-1.5 pr-2.5 py-1.5 rounded-full border border-slate-200/90 bg-white/70 hover:bg-slate-50 hover:border-slate-300 shadow-xs transition-all duration-150 cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-b from-blue-600 to-blue-700 text-white flex items-center justify-center font-bold text-xs shadow-sm ring-1 ring-inset ring-white/20 flex-shrink-0">
                                {{ strtoupper(substr($authUser->name, 0, 1)) }}
                            </div>
                            <div class="text-left hidden lg:block max-w-[150px]">
                                <p class="text-sm font-semibold text-slate-800 leading-tight truncate">{{ $authUser->name }}</p>
                                <p class="text-[11px] font-medium text-blue-700/80 leading-tight truncate">{{ $roleLabel }}</p>
                            </div>
                            <span class="material-symbols-outlined text-lg text-slate-400 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''">expand_more</span>
                        </button>

                        <!-- Dropdown Card -->
                        <div x-show="userMenuOpen" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute right-0 mt-2.5 w-72 rounded-xl border border-slate-200/90 bg-white shadow-xl shadow-slate-900/10 overflow-hidden origin-top-right">
                            <!-- Identity Card -->
                            <div class="px-4 py-3.5 border-b border-slate-100 bg-slate-50/70">
                                <p class="text-sm font-bold text-slate-900 truncate">{{ $authUser->name }}</p>
                                <p class="text-xs text-slate-500 truncate mb-2">{{ $authUser->email }}</p>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">
                                    <span class="material-symbols-outlined text-[13px]" style="font-variation-settings: 'FILL' 1;">verified</span>
                                    {{ $roleLabel }}
                                </span>
                            </div>

                            <!-- Role-based Menu Items -->
                            <div class="py-1.5 px-1.5">
                                @if ($authUser->isLaboran() || $authUser->isKepalaLab())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-700 transition-colors">
                                        <span class="material-symbols-outlined text-lg text-slate-400">dashboard</span>
                                        Kelola Lab (Dashboard)
                                    </a>
                                    <a href="{{ route('catalog.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-700 transition-colors">
                                        <span class="material-symbols-outlined text-lg text-slate-400">menu_book</span>
                                        Katalog Publik
                                    </a>
                                @else
                                    <a href="{{ route('loans.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-700 transition-colors">
                                        <span class="material-symbols-outlined text-lg text-slate-400">add_circle</span>
                                        Ajukan Peminjaman
                                    </a>
                                    <a href="{{ route('loans.history') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-700 transition-colors">
                                        <span class="material-symbols-outlined text-lg text-slate-400">history</span>
                                        Riwayat Pengajuan Tiket
                                    </a>
                                @endif
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-700 transition-colors">
                                    <span class="material-symbols-outlined text-lg text-slate-400">manage_accounts</span>
                                    Pengaturan Profil Saya
                                </a>
                            </div>

                            <!-- Divider + Logout -->
                            <div class="border-t border-slate-100 py-1.5 px-1.5">
                                <button type="button" @click="userMenuOpen = false; logoutOpen = true"
                                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-lg">logout</span>
                                    Keluar / Logout
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white text-sm font-medium tracking-tight shadow-sm hover:shadow ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium tracking-tight border border-slate-300/80 hover:border-slate-400 shadow-xs active:bg-slate-100 active:scale-[0.98] transition-all duration-150 cursor-pointer">
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
            <a href="#panduan" class="block font-label-md text-label-md text-on-surface py-2">Panduan</a>
            <a href="#kontak" class="block font-label-md text-label-md text-on-surface py-2">Kontak</a>
            @auth
                @if ($authUser->isLaboran() || $authUser->isKepalaLab())
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-label-md text-label-md text-primary font-bold py-2">
                        <span class="material-symbols-outlined text-lg">dashboard</span>
                        Kelola Lab (Dashboard)
                    </a>
                @else
                    <a href="{{ route('loans.create') }}" class="block font-label-md text-label-md text-on-surface py-2">Ajukan Peminjaman</a>
                    <a href="{{ route('loans.history') }}" class="block font-label-md text-label-md text-on-surface py-2">Riwayat Pengajuan Tiket</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="block font-label-md text-label-md text-on-surface py-2">Pengaturan Profil Saya</a>
                <div class="pt-3 border-t border-outline-variant flex items-center justify-between">
                    <div>
                        <p class="font-label-md text-label-md text-on-surface font-semibold">{{ $authUser->name }}</p>
                        <p class="font-caption-xs text-caption-xs text-on-surface-variant">{{ $roleLabel }}</p>
                    </div>
                    <button type="button" @click="mobileMenuOpen = false; logoutOpen = true" class="text-error font-label-md text-label-md cursor-pointer">Keluar</button>
                </div>
            @else
                <div class="pt-3 border-t border-outline-variant flex gap-2">
                    <a href="{{ route('login') }}" class="flex-1 text-center bg-primary text-on-primary font-label-md text-label-md py-2 rounded-lg">Masuk</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center bg-surface-container-high text-on-surface font-label-md text-label-md py-2 rounded-lg border border-outline-variant">Daftar</a>
                </div>
            @endauth
        </div>
    </nav>

    <!-- Logout Confirmation Modal -->
    <div x-show="logoutOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[70] flex items-center justify-center p-4"
         style="display: none;">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="logoutOpen = false"></div>
        <div class="relative bg-white w-full max-w-sm rounded-2xl border border-slate-200/80 shadow-2xl p-6 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-rose-50 border border-rose-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl text-rose-600">logout</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 tracking-tight">Keluar dari Sistem?</h3>
            <p class="text-sm text-slate-500 mt-1.5">Apakah Anda yakin ingin mengakhiri sesi Anda saat ini?</p>
            <div class="mt-5 flex gap-3">
                <button type="button" @click="logoutOpen = false"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium tracking-tight border border-slate-300/80 hover:border-slate-400 shadow-xs active:bg-slate-100 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                    Batal
                </button>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-gradient-to-b from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white text-sm font-medium tracking-tight shadow-sm hover:shadow ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                        Ya, Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content Canvas -->
    <main class="flex-grow">
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
