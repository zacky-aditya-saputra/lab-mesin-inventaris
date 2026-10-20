<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'LabCatalog Admin' }} - Inventaris Lab Mesin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Vite Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-surface text-on-surface font-body-md h-[100dvh] flex overflow-hidden antialiased" x-data="{ sidebarOpen: false }">
    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-on-surface/50 backdrop-blur-sm z-40 md:hidden"
         style="display: none;"></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-800 text-slate-200 flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out md:static md:translate-x-0 h-full">
        <!-- Brand -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-white/10 flex-shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <span class="material-symbols-outlined text-blue-400 text-2xl" style="font-variation-settings: 'FILL' 1;">science</span>
                <span class="font-headline-sm text-headline-sm text-white font-bold tracking-tight">LabCatalog</span>
            </a>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Lab Scope Info -->
        <div class="px-6 py-3 border-b border-white/10 flex items-center gap-2 flex-shrink-0">
            <span class="material-symbols-outlined text-blue-400 text-base" style="font-variation-settings: 'FILL' 1;">science</span>
            <span class="font-caption-xs text-caption-xs text-slate-400 truncate">
                @if (auth()->user()->isLaboran())
                    Lab: {{ auth()->user()->laboratory?->name ?? '-' }}
                @else
                    Seluruh Laboratorium Mesin
                @endif
            </span>
        </div>

        <!-- Menu -->
        @php
            $activeMenuClass = 'border-l-4 border-blue-500 bg-slate-800/80 text-white font-medium shadow-xs';
            $inactiveMenuClass = 'border-l-4 border-transparent text-slate-400 hover:text-white hover:bg-slate-800/50';
        @endphp
        <nav class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.dashboard') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.dashboard') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.dashboard') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>dashboard</span>
                <span class="font-label-md text-label-md">Dashboard</span>
            </a>

            <!-- Kategori Alat -->
            <a href="{{ route('admin.categories.index') }}" 
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.categories.*') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.categories.*') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.categories.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>category</span>
                <span class="font-label-md text-label-md">Kategori Alat</span>
            </a>

            <!-- Data Alat -->
            <a href="{{ route('admin.tools.index') }}" 
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.tools.index', 'admin.tools.create', 'admin.tools.edit') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.tools.index', 'admin.tools.create', 'admin.tools.edit') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.tools.index', 'admin.tools.create', 'admin.tools.edit') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>inventory_2</span>
                <span class="font-label-md text-label-md">Data Alat</span>
            </a>

            <!-- Mutasi Stok -->
            <a href="{{ route('admin.tools.adjustments') }}" 
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.tools.adjustments') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.tools.adjustments') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.tools.adjustments') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>published_with_changes</span>
                <span class="font-label-md text-label-md">Mutasi Stok</span>
            </a>

            <!-- Tiket Masuk -->
            <a href="{{ route('admin.loans.index') }}" 
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.loans.index', 'admin.loans.review') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.loans.index', 'admin.loans.review') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.loans.index', 'admin.loans.review') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>confirmation_number</span>
                <span class="font-label-md text-label-md">Tiket Masuk</span>
                @if(isset($pendingCount) && $pendingCount > 0)
                    <span class="ml-auto bg-rose-500/20 text-rose-300 border border-rose-500/30 px-2 py-0.5 rounded-full text-xs font-semibold">{{ $pendingCount }}</span>
                @endif
            </a>

            <!-- Klien Lab / Data Peminjam -->
            <a href="{{ route('admin.clients.index') }}"
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.clients.*') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.clients.*') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.clients.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>group</span>
                <span class="font-label-md text-label-md">Klien Lab</span>
            </a>

            <!-- Logbook Mahasiswa -->
            <a href="{{ route('admin.activity-logs.index') }}"
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.activity-logs.*') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.activity-logs.*') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.activity-logs.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>history_edu</span>
                <span class="font-label-md text-label-md">Logbook Mahasiswa</span>
            </a>

            <!-- Monitoring Overdue -->
            <a href="{{ route('admin.loans.index', ['status' => 'OVERDUE']) }}" 
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->fullUrlIs('*status=OVERDUE*') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl opacity-70 group-hover:opacity-100">warning</span>
                <span class="font-label-md text-label-md">Monitoring Overdue</span>
            </a>

            <!-- Laporan & Riwayat -->
            <a href="{{ route('admin.loans.index', ['view' => 'reports']) }}" 
               class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->fullUrlIs('*view=reports*') ? $activeMenuClass : $inactiveMenuClass }}">
                <span class="material-symbols-outlined mr-3 text-xl opacity-70 group-hover:opacity-100">history</span>
                <span class="font-label-md text-label-md">Laporan &amp; Riwayat</span>
            </a>

            @if (auth()->user()->isKepalaLab())
                <!-- Manajemen Laboratorium (khusus kepala lab) -->
                <a href="{{ route('admin.laboratories.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.laboratories.*') ? $activeMenuClass : $inactiveMenuClass }}">
                    <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.laboratories.*') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.laboratories.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>domain</span>
                    <span class="font-label-md text-label-md">Manajemen Lab</span>
                </a>

                <!-- Manajemen Laboran (khusus kepala lab) -->
                <a href="{{ route('admin.laborans.index') }}"
                   class="flex items-center px-3 py-2.5 rounded-r-lg transition-colors group {{ request()->routeIs('admin.laborans.*') ? $activeMenuClass : $inactiveMenuClass }}">
                    <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.laborans.*') ? 'text-blue-400 opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.laborans.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>manage_accounts</span>
                    <span class="font-label-md text-label-md">Manajemen Laboran</span>
                </a>
            @endif

            <div class="pt-4 border-t border-white/10 mt-4">
                <a href="{{ route('catalog.index') }}" target="_blank"
                   class="flex items-center px-3 py-2.5 rounded-r-lg border-l-4 border-transparent text-slate-400 hover:bg-slate-800/50 hover:text-white transition-colors group">
                    <span class="material-symbols-outlined mr-3 text-xl opacity-70 group-hover:opacity-100">open_in_new</span>
                    <span class="font-label-md text-label-md">Lihat Katalog Publik</span>
                </a>
            </div>
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-white/10 flex-shrink-0">
            <div class="flex items-center justify-between">
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 overflow-hidden group p-1 -m-1 rounded-lg hover:bg-white/5 transition-colors" title="Buka Profil Akun">
                    <div class="w-9 h-9 rounded-full bg-blue-500/15 border border-blue-400/30 flex items-center justify-center text-blue-300 font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <p class="font-label-md text-label-md text-white truncate group-hover:text-blue-300 transition-colors">{{ auth()->user()->name ?? 'Admin Lab' }}</p>
                        <p class="font-caption-xs text-caption-xs text-slate-400 truncate flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">edit</span> Ubah Profil
                        </p>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem?');" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-white/5 rounded-md transition-colors" title="Keluar">
                        <span class="material-symbols-outlined text-xl">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full bg-surface overflow-hidden">
        <!-- Top Header -->
        <header class="h-16 bg-white/95 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-4 md:px-6 flex-shrink-0 z-10">
            <div class="flex items-center gap-3 min-w-0">
                <button @click="sidebarOpen = true" class="md:hidden text-on-surface-variant p-1 rounded-md hover:bg-surface-container">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="min-w-0">
                    <h1 class="font-headline-md text-headline-md text-on-surface truncate">{{ $pageTitle ?? $title ?? 'Admin Dashboard' }}</h1>
                </div>
            </div>
            
            <div class="flex items-center gap-3 md:gap-4 flex-shrink-0">
                <x-notification-bell />

                <!-- Widget Tanggal & Waktu -->
                <div class="hidden sm:flex items-center gap-2 rounded-lg bg-slate-100 border border-slate-200/80 px-3 py-1.5 shadow-xs">
                    <span class="material-symbols-outlined text-base text-slate-400">calendar_today</span>
                    <span class="text-xs text-slate-500 whitespace-nowrap">{{ now()->translatedFormat('l, d M Y') }}</span>
                    <span class="w-px h-4 bg-slate-300"></span>
                    <span class="material-symbols-outlined text-base text-slate-400">schedule</span>
                    <span class="text-xs font-semibold text-slate-800 whitespace-nowrap">{{ now()->format('H:i') }} WIB</span>
                </div>
                
                @isset($headerActions)
                    {{ $headerActions }}
                @endisset
            </div>
        </header>

        <!-- Scrollable Content Canvas -->
        <div class="flex-1 overflow-y-auto p-4 md:p-8 bg-surface">
            <div class="max-w-container-max mx-auto space-y-6">
                <!-- Flash Alerts -->
                @if (session('success'))
                    <div class="bg-status-success-bg border border-status-success-text/30 text-status-success-text px-4 py-3 rounded-lg shadow-sm flex items-center justify-between" role="alert">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-status-success-text">check_circle</span>
                            <span class="font-label-md text-label-md">{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-status-success-text hover:opacity-70">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-error-container border border-error/30 text-on-error-container px-4 py-3 rounded-lg shadow-sm flex items-center justify-between" role="alert">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">error</span>
                            <span class="font-label-md text-label-md">{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-on-error-container hover:opacity-70">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-error-container border border-error/30 text-on-error-container p-4 rounded-lg shadow-sm" role="alert">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-error">warning</span>
                            <span class="font-label-md text-label-md font-bold">Terdapat beberapa kesalahan pengisian:</span>
                        </div>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Page Main Body -->
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </div>
    </main>

    @stack('scripts')
</body>
</html>
