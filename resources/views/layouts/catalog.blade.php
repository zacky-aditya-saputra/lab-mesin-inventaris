<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'LabCatalog' }} - Laboratorium Teknik Mesin Unwahas</title>

    <!-- Google Fonts: Plus Jakarta Sans, Inter, JetBrains Mono & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html {
            scroll-behavior: smooth;
        }

        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.995);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .animate-fade-in-up-delay-1 {
            animation: fadeInUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) 0.08s both;
        }
        .animate-fade-in-up-delay-2 {
            animation: fadeInUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) 0.16s both;
        }

        /* Scroll reveal utility - buttery smooth Apple-style easing */
        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(16px);
            transition: opacity 0.85s cubic-bezier(0.22, 1, 0.36, 1), transform 0.85s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: opacity, transform;
        }
        .reveal-on-scroll.is-revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Staggered delay helpers for grid items */
        .stagger-1 { transition-delay: 0.06s !important; }
        .stagger-2 { transition-delay: 0.13s !important; }
        .stagger-3 { transition-delay: 0.20s !important; }
        .stagger-4 { transition-delay: 0.27s !important; }

        /* Tool card hover elevation */
        .tool-card-hover {
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
        }
        .tool-card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.08), 0 8px 12px -6px rgba(15, 23, 42, 0.04);
        }

        /* Custom subtle scrollbar for sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
            transition: background 0.3s ease;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sticky sidebar independent scroll */
        .catalog-sidebar-scroll {
            max-height: calc(100vh - 8rem) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            overscroll-behavior: contain;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('ui', {
                search: '',
                filterSheetOpen: false,
                sopModalOpen: false,
                contactModalOpen: false,
                activeSection: window.location.hash || '#katalog',
                setActiveSection(hash) {
                    this.activeSection = hash;
                    const targetId = hash.replace('#', '');
                    const targetEl = document.getElementById(targetId);
                    if (targetEl) {
                        targetEl.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Scroll reveal observer with smooth threshold and rootMargin
            const revealElements = document.querySelectorAll('.reveal-on-scroll');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-revealed');
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

                revealElements.forEach(el => observer.observe(el));
            } else {
                revealElements.forEach(el => el.classList.add('is-revealed'));
            }

            // Scrollspy observer for header nav active states (throttled with rAF for 60/120fps smoothness)
            const updateScrollspy = () => {
                if (!window.location.pathname.endsWith('/catalog') && window.location.pathname !== '/') {
                    return;
                }
                const sections = [
                    { id: 'kontak', hash: '#kontak' },
                    { id: 'panduan', hash: '#panduan' },
                    { id: 'katalog', hash: '#katalog' }
                ];
                const scrollPos = window.scrollY + 200;
                for (const sec of sections) {
                    const el = document.getElementById(sec.id);
                    if (el) {
                        const top = el.offsetTop;
                        const height = el.offsetHeight;
                        if (scrollPos >= top && scrollPos < top + height) {
                            if (window.Alpine && Alpine.store('ui')) {
                                Alpine.store('ui').activeSection = sec.hash;
                            }
                            return;
                        }
                    }
                }
                if (window.scrollY < 300) {
                    if (window.Alpine && Alpine.store('ui')) {
                        Alpine.store('ui').activeSection = '#katalog';
                    }
                }
            };

            let scrollTicking = false;
            window.addEventListener('scroll', () => {
                if (!scrollTicking) {
                    window.requestAnimationFrame(() => {
                        updateScrollspy();
                        scrollTicking = false;
                    });
                    scrollTicking = true;
                }
            }, { passive: true });

            window.addEventListener('hashchange', () => {
                if (window.Alpine && Alpine.store('ui')) {
                    Alpine.store('ui').activeSection = window.location.hash || '#katalog';
                }
            });
            setTimeout(updateScrollspy, 200);
        });
    </script>
    @stack('styles')
</head>
<body class="bg-[#FAFAFA] text-slate-800 font-sans antialiased min-h-[100dvh] flex flex-col pb-16 md:pb-0" x-data="{ mobileMenuOpen: false, userMenuOpen: false, logoutOpen: false }">
    @php
        $authUser = auth()->user();
        $roleLabel = $authUser?->roleLabel() ?? 'Pengunjung';
        $onCatalogIndex = request()->routeIs('catalog.index');
    @endphp

    <!-- Top App Bar (Sesuai Referensi Desain dengan Blue Navigation Capsule) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs">
        <!-- Desktop Navbar -->
        <div class="hidden md:block">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <!-- SISI KIRI: Brand & Logo Identitas Lab Mesin -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('catalog.index') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-xs group-hover:bg-blue-700 transition-colors">
                            <span class="material-symbols-outlined text-2xl font-bold">precision_manufacturing</span>
                        </div>
                        <div class="flex flex-col justify-center">
                            <span class="text-lg font-black text-slate-900 tracking-tight leading-tight block">LabCatalog</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block mt-0.5 leading-none">TEKNIK MESIN UNWAHAS</span>
                        </div>
                    </a>
                </div>

                <!-- SISI TENGAH: Menu Navigasi Centered (Kontainer Persegi Panjang Biru Terang Flush / Bottom-Aligned) -->
                <div class="flex-1 flex justify-center self-end">
                    <nav class="self-end -mb-px relative z-10 flex items-center gap-2 lg:gap-3 bg-blue-600 rounded-t-2xl rounded-b-none px-4 lg:px-6 py-2.5 text-white shadow-xs">
                        <a href="{{ route('catalog.index') }}#katalog"
                           @click="$store.ui.setActiveSection('#katalog')"
                           :class="($store.ui.activeSection === '#katalog' || !$store.ui.activeSection) && {{ $onCatalogIndex ? 'true' : 'false' }}
                               ? 'text-white font-bold bg-white/20 shadow-2xs'
                               : 'text-white/80 hover:text-white hover:bg-white/10'"
                           class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200">
                            Katalog
                        </a>

                        @auth
                            @if ($authUser->isKlien())
                                <a href="{{ route('loans.history') }}"
                                   class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200 {{ request()->routeIs('loans.history*') ? 'text-white font-bold bg-white/20 shadow-2xs' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                    Riwayat
                                </a>

                                <a href="{{ route('loans.create') }}"
                                   class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200 {{ request()->routeIs('loans.create*') ? 'text-white font-bold bg-white/20 shadow-2xs' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                    Pengajuan
                                </a>

                                <a href="{{ route('activity-logs.index') }}"
                                   class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200 {{ request()->routeIs('activity-logs.*') ? 'text-white font-bold bg-white/20 shadow-2xs' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                    Logbook Kegiatan
                                </a>
                            @else
                                <a href="{{ route('admin.loans.index') }}"
                                   class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.loans.*') ? 'text-white font-bold bg-white/20 shadow-2xs' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                    Riwayat
                                </a>

                                <a href="{{ route('admin.loans.index') }}"
                                   class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.loans.review*') ? 'text-white font-bold bg-white/20 shadow-2xs' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                    Pengajuan
                                </a>

                                <a href="{{ route('admin.activity-logs.index') }}"
                                   class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.activity-logs.*') ? 'text-white font-bold bg-white/20 shadow-2xs' : 'text-white/80 hover:text-white hover:bg-white/10' }}">
                                    Logbook Kegiatan
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="text-xs lg:text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200">
                                Riwayat
                            </a>
                            <a href="{{ route('login') }}" class="text-xs lg:text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200">
                                Pengajuan
                            </a>
                            <a href="{{ route('login') }}" class="text-xs lg:text-sm font-semibold text-white/80 hover:text-white hover:bg-white/10 px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200">
                                Logbook Kegiatan
                            </a>
                        @endauth

                        <a href="{{ route('catalog.index') }}#panduan"
                           @click="$store.ui.setActiveSection('#panduan')"
                           :class="$store.ui.activeSection === '#panduan' && {{ $onCatalogIndex ? 'true' : 'false' }}
                               ? 'text-white font-bold bg-white/20 shadow-2xs'
                               : 'text-white/80 hover:text-white hover:bg-white/10'"
                           class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200">
                            Panduan
                        </a>
                        <a href="{{ route('catalog.index') }}#kontak"
                           @click="$store.ui.setActiveSection('#kontak')"
                           :class="$store.ui.activeSection === '#kontak' && {{ $onCatalogIndex ? 'true' : 'false' }}
                               ? 'text-white font-bold bg-white/20 shadow-2xs'
                               : 'text-white/80 hover:text-white hover:bg-white/10'"
                           class="text-xs lg:text-sm font-semibold px-3 lg:px-3.5 py-1.5 rounded-lg transition-all duration-200">
                            Info Kontak
                        </a>
                    </nav>
                </div>

                <!-- SISI KANAN: Notifikasi & Akun Pengguna -->
                <div class="flex items-center gap-3 shrink-0 my-auto">
                    @auth
                        <x-notification-bell />

                        <!-- User Profile Pill Container (Sesuai Gambar) -->
                        <div class="relative" @click.outside="userMenuOpen = false">
                            <button type="button" @click="userMenuOpen = !userMenuOpen"
                                    class="flex cursor-pointer items-center gap-2.5 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3.5 shadow-xs hover:bg-slate-50 transition-all">
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs shrink-0">
                                    {{ strtoupper(substr($authUser->name, 0, 1)) }}
                                </div>
                                <div class="hidden max-w-[140px] text-left lg:block">
                                    <p class="truncate text-xs font-bold leading-tight text-slate-800">{{ $authUser->name }}</p>
                                    <p class="truncate text-[10px] font-semibold leading-tight text-blue-600">{{ $roleLabel }}</p>
                                </div>
                                <span class="material-symbols-outlined text-base text-slate-400 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''">expand_more</span>
                            </button>

                            <div x-show="userMenuOpen" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="absolute right-0 mt-2.5 w-72 origin-top-right overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-900/10 z-50">
                                <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3.5">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $authUser->name }}</p>
                                    <p class="mb-2 truncate text-xs text-slate-500">{{ $authUser->email }}</p>
                                    <span class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                        <span class="material-symbols-outlined filled text-[13px]">verified</span>
                                        {{ $roleLabel }}
                                    </span>
                                </div>

                                <div class="px-1.5 py-1.5">
                                    @if ($authUser->isLaboran() || $authUser->isKepalaLab())
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                            <span class="material-symbols-outlined text-lg text-slate-400">dashboard</span>
                                            Kelola Lab (Dashboard)
                                        </a>
                                        <a href="{{ route('catalog.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                            <span class="material-symbols-outlined text-lg text-slate-400">menu_book</span>
                                            Katalog Publik
                                        </a>
                                    @else
                                        <a href="{{ route('loans.create') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                            <span class="material-symbols-outlined text-lg text-slate-400">add_circle</span>
                                            Ajukan Peminjaman
                                        </a>
                                        <a href="{{ route('loans.history') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                            <span class="material-symbols-outlined text-lg text-slate-400">history</span>
                                            Riwayat Pengajuan
                                        </a>
                                        <a href="{{ route('activity-logs.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                            <span class="material-symbols-outlined text-lg text-slate-400">history_edu</span>
                                            Logbook Kegiatan Mandiri
                                        </a>
                                    @endif
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-blue-50 hover:text-blue-700">
                                        <span class="material-symbols-outlined text-lg text-slate-400">manage_accounts</span>
                                        Pengaturan Profil Saya
                                    </a>
                                </div>

                                <div class="border-t border-slate-100 px-1.5 py-1.5">
                                    <button type="button" @click="userMenuOpen = false; logoutOpen = true"
                                            class="flex w-full cursor-pointer items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-rose-600 transition-colors hover:bg-rose-50">
                                        <span class="material-symbols-outlined text-lg">logout</span>
                                        Keluar / Logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-1.5 rounded-full bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-xs transition-all hover:bg-blue-700 active:scale-95">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-1.5 rounded-full border border-slate-300 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 shadow-xs transition-all hover:border-slate-400 hover:bg-slate-50 active:scale-95">
                            Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile App Bar (Tinggi Pas 60px Nyaman & Bernapas) -->
        <div class="md:hidden">
            <div class="flex h-16 items-center justify-between px-5 sm:px-6">
                <a href="{{ route('catalog.index') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white shadow-xs">
                        <span class="material-symbols-outlined text-lg">precision_manufacturing</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-extrabold tracking-tight text-slate-900 leading-none">LabCatalog</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-none mt-0.5">Teknik Mesin</span>
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    @auth
                        <x-notification-bell />
                    @endauth

                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                            class="flex h-9 w-9 items-center justify-center rounded-full text-slate-700 hover:bg-slate-100 transition-colors"
                            aria-label="Menu">
                        <span class="material-symbols-outlined text-[22px]" x-text="mobileMenuOpen ? 'close' : 'menu'">menu</span>
                    </button>
                </div>
            </div>

            <!-- Mobile Drawer Dropdown -->
            <div x-show="mobileMenuOpen" x-cloak x-transition
                 class="border-b border-slate-200 bg-white px-4 py-4 space-y-1.5">
                <a href="{{ route('catalog.index') }}#katalog"
                   @click="$store.ui.setActiveSection('#katalog'); mobileMenuOpen = false"
                   :class="($store.ui.activeSection === '#katalog' || !$store.ui.activeSection) && {{ $onCatalogIndex ? 'true' : 'false' }}
                       ? 'bg-blue-50 text-blue-700 font-bold'
                       : 'text-slate-800 hover:text-blue-600'"
                   class="block py-2 px-3 rounded-xl text-sm transition-all">
                    Katalog Alat
                </a>
                <a href="{{ route('catalog.index') }}#panduan"
                   @click="$store.ui.setActiveSection('#panduan'); mobileMenuOpen = false"
                   :class="$store.ui.activeSection === '#panduan' && {{ $onCatalogIndex ? 'true' : 'false' }}
                       ? 'bg-blue-50 text-blue-700 font-bold'
                       : 'text-slate-600 hover:text-slate-900'"
                   class="block py-2 px-3 rounded-xl text-sm transition-all">
                    Panduan SOP
                </a>
                <a href="{{ route('catalog.index') }}#kontak"
                   @click="$store.ui.setActiveSection('#kontak'); mobileMenuOpen = false"
                   :class="$store.ui.activeSection === '#kontak' && {{ $onCatalogIndex ? 'true' : 'false' }}
                       ? 'bg-blue-50 text-blue-700 font-bold'
                       : 'text-slate-600 hover:text-slate-900'"
                   class="block py-2 px-3 rounded-xl text-sm transition-all">
                    Info Kontak &amp; Lab
                </a>

                @auth
                    <div class="pt-2 border-t border-slate-100 space-y-1">
                        @if ($authUser->isLaboran() || $authUser->isKepalaLab())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 py-2 text-sm font-bold text-blue-600">
                                <span class="material-symbols-outlined text-lg">dashboard</span>
                                Kelola Lab (Dashboard)
                            </a>
                        @else
                            <a href="{{ route('loans.create') }}" class="block py-2 text-sm font-semibold text-blue-600">Ajukan Peminjaman</a>
                            <a href="{{ route('loans.history') }}" class="block py-2 text-sm font-medium text-slate-700">Riwayat Pengajuan</a>
                            <a href="{{ route('activity-logs.index') }}" class="block py-2 text-sm font-medium text-slate-700">Logbook Kegiatan Mandiri</a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="block py-2 text-sm font-medium text-slate-700">Pengaturan Profil Saya</a>

                        <div class="flex items-center justify-between border-t border-slate-200 pt-3 mt-2">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $authUser->name }}</p>
                                <p class="text-xs text-blue-600 font-semibold">{{ $roleLabel }}</p>
                            </div>
                            <button type="button" @click="mobileMenuOpen = false; logoutOpen = true" class="cursor-pointer text-xs font-semibold text-rose-600 px-3 py-1.5 rounded-lg hover:bg-rose-50">
                                Keluar
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex gap-2 border-t border-slate-200 pt-3">
                        <a href="{{ route('login') }}" class="flex-1 rounded-full bg-blue-600 py-2 text-center text-xs font-semibold text-white shadow-xs">Masuk</a>
                        <a href="{{ route('register') }}" class="flex-1 rounded-full border border-slate-300 bg-white py-2 text-center text-xs font-semibold text-slate-700">Daftar</a>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Logout Confirmation Modal -->
    <div x-show="logoutOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="logoutOpen = false"></div>
        <div class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border border-rose-100 bg-rose-50">
                <span class="material-symbols-outlined text-2xl text-rose-600">logout</span>
            </div>
            <h3 class="text-lg font-bold tracking-tight text-slate-900">Keluar dari Sistem?</h3>
            <p class="mt-1.5 text-xs text-slate-500">Apakah Anda yakin ingin mengakhiri sesi Anda saat ini?</p>
            <div class="mt-5 flex gap-3">
                <button type="button" @click="logoutOpen = false"
                        class="flex-1 inline-flex cursor-pointer items-center justify-center rounded-full border border-slate-300 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 active:scale-95 transition-all">
                    Batal
                </button>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center rounded-full bg-rose-600 px-4 py-2.5 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 active:scale-95 transition-all">
                        Ya, Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content Canvas -->
    <main class="flex-grow">
        @if (session('success'))
            <div class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-xs" role="alert">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined filled text-emerald-600 text-lg">check_circle</span>
                        <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-emerald-700 hover:text-emerald-900 transition-colors p-1">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 shadow-xs" role="alert">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined filled text-rose-600 text-lg">error</span>
                        <span class="text-xs sm:text-sm font-semibold">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-rose-700 hover:text-rose-900 transition-colors p-1">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                </div>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Sticky Bottom Navigation (Mobile: Navigasi 3 Tab Jempol Sesuai Aturan Bisnis) -->
    @if ($onCatalogIndex)
        <nav class="fixed bottom-0 left-0 right-0 z-40 flex items-center justify-around border-t border-slate-200 bg-white/95 px-3 pb-[max(0.625rem,env(safe-area-inset-bottom))] pt-2 backdrop-blur-md md:hidden"
             aria-label="Navigasi Katalog">
            <!-- Tab 1: Katalog -->
            <a href="{{ route('catalog.index') }}" class="flex w-20 flex-col items-center gap-0.5 py-1 text-[10px] font-bold text-blue-600">
                <span class="material-symbols-outlined filled text-[22px]">storefront</span>
                Katalog
            </a>

            <!-- Tab 2: Filter -->
            <button type="button" @click="$store.ui.filterSheetOpen = true" class="flex w-20 cursor-pointer flex-col items-center gap-0.5 py-1 text-[10px] font-medium text-slate-500 hover:text-slate-800 transition-colors">
                <span class="material-symbols-outlined text-[22px]">tune</span>
                Filter
            </button>

            <!-- Tab 3: Tiket Saya -->
            @auth
                @if ($authUser->isKlien())
                    <a href="{{ route('loans.history') }}" class="flex w-20 flex-col items-center gap-0.5 py-1 text-[10px] font-medium text-slate-500 hover:text-slate-800 transition-colors">
                        <span class="material-symbols-outlined text-[22px]">confirmation_number</span>
                        Tiket Saya
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="flex w-20 flex-col items-center gap-0.5 py-1 text-[10px] font-medium text-slate-500 hover:text-slate-800 transition-colors">
                        <span class="material-symbols-outlined text-[22px]">dashboard</span>
                        Kelola
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="flex w-20 flex-col items-center gap-0.5 py-1 text-[10px] font-medium text-slate-500 hover:text-slate-800 transition-colors">
                    <span class="material-symbols-outlined text-[22px]">login</span>
                    Masuk
                </a>
            @endauth
        </nav>
    @endif

    <!-- Footer Akademik & Kontak (Sesuai Stitch Screen 2, 5 & 6) -->
    <footer id="kontak" class="mt-auto w-full border-t border-slate-200 bg-white text-slate-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Kolom 1 & 2: Identitas & Akreditasi -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-xs">
                            <span class="material-symbols-outlined text-xl">precision_manufacturing</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-base text-slate-900 tracking-tight leading-none">LabCatalog Unwahas</span>
                            <span class="font-mono text-[10px] font-bold text-blue-600 uppercase tracking-wider mt-0.5">FT-TM UNWAHAS</span>
                        </div>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-md leading-relaxed">
                        Sistem Informasi dan Manajemen Inventaris Laboratorium Teknik Mesin, Fakultas Teknik, Universitas Wahid Hasyim Semarang. Mendukung standarisasi praktikum, penelitian, dan riset manufaktur presisi.
                    </p>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-[11px] font-mono text-slate-600 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Terakreditasi BAN-PT &amp; Standar K3 Laboratorium
                    </div>
                </div>

                <!-- Kolom 3: Lokasi & Jam Operasional -->
                <div class="space-y-2.5">
                    <h3 class="text-xs font-bold font-mono uppercase tracking-wider text-slate-900">Lokasi &amp; Jam Layanan</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Gedung D, Kampus 1 UNWAHAS<br>
                        Jl. Menoreh Tengah X No.22, Sampangan, Gajahmungkur, Kota Semarang, Jawa Tengah 50236
                    </p>
                    <div class="text-xs font-mono text-slate-600 space-y-0.5 pt-1">
                        <p>Senin &ndash; Kamis: 08.00 &ndash; 16.00 WIB</p>
                        <p>Jumat: 08.00 &ndash; 15.00 WIB</p>
                    </div>
                </div>

                <!-- Kolom 4: Kontak Akademik & Hotline -->
                <div class="space-y-2.5">
                    <h3 class="text-xs font-bold font-mono uppercase tracking-wider text-slate-900">Kontak Resmi</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Laboratorium Teknik Mesin Unwahas<br>
                        Email: <a href="mailto:lab.mesin@unwahas.ac.id" class="text-blue-600 hover:underline">lab.mesin@unwahas.ac.id</a><br>
                        Telp: (024) 8505680 / 8505681
                    </p>
                    <div class="pt-1">
                        <a href="https://wa.me/6281234567891" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold hover:bg-emerald-100 transition-colors">
                            <span class="material-symbols-outlined text-sm">chat</span>
                            WhatsApp Hotline Lab
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400 font-mono">
                <p>&copy; 2026 Laboratorium Teknik Mesin Universitas Wahid Hasyim Semarang. Hak cipta dilindungi.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('catalog.index') }}#panduan" class="hover:text-blue-600 transition-colors">SOP Pemakaian</a>
                    <a href="{{ route('catalog.index') }}#kontak" class="hover:text-blue-600 transition-colors">Direktori Lab</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
