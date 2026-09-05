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
<body class="bg-surface text-on-surface font-body-md h-screen flex overflow-hidden antialiased" x-data="{ sidebarOpen: false }">
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
           class="fixed inset-y-0 left-0 z-50 w-64 bg-sidebar-dark text-on-primary flex flex-col flex-shrink-0 transition-transform duration-300 ease-in-out md:static md:translate-x-0 h-full">
        <!-- Brand -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-white/10 flex-shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary-fixed text-2xl" style="font-variation-settings: 'FILL' 1;">science</span>
                <span class="font-headline-sm text-headline-sm text-primary-fixed font-bold tracking-tight">LabCatalog</span>
            </a>
            <button @click="sidebarOpen = false" class="md:hidden text-secondary-fixed-dim hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Menu -->
        <nav class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('admin.dashboard') ? 'bg-primary-container text-on-primary-container font-semibold' : 'text-secondary-fixed-dim hover:bg-white/5 hover:text-white' }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.dashboard') ? 'opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.dashboard') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>dashboard</span>
                <span class="font-label-md text-label-md">Dashboard</span>
            </a>

            <!-- Kategori Alat -->
            <a href="{{ route('admin.categories.index') }}" 
               class="flex items-center px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('admin.categories.*') ? 'bg-primary-container text-on-primary-container font-semibold' : 'text-secondary-fixed-dim hover:bg-white/5 hover:text-white' }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.categories.*') ? 'opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.categories.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>category</span>
                <span class="font-label-md text-label-md">Kategori Alat</span>
            </a>

            <!-- Data Alat -->
            <a href="{{ route('admin.tools.index') }}" 
               class="flex items-center px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('admin.tools.*') ? 'bg-primary-container text-on-primary-container font-semibold' : 'text-secondary-fixed-dim hover:bg-white/5 hover:text-white' }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('admin.tools.*') ? 'opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('admin.tools.*') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>inventory_2</span>
                <span class="font-label-md text-label-md">Data Alat</span>
            </a>

            <!-- Tiket Masuk -->
            <a href="{{ route('loans.index') }}" 
               class="flex items-center px-3 py-2.5 rounded-lg transition-colors group {{ request()->routeIs('loans.index', 'loans.review') ? 'bg-primary-container text-on-primary-container font-semibold' : 'text-secondary-fixed-dim hover:bg-white/5 hover:text-white' }}">
                <span class="material-symbols-outlined mr-3 text-xl {{ request()->routeIs('loans.index', 'loans.review') ? 'opacity-100' : 'opacity-70 group-hover:opacity-100' }}" {{ request()->routeIs('loans.index', 'loans.review') ? 'style="font-variation-settings: \'FILL\' 1;"' : '' }}>confirmation_number</span>
                <span class="font-label-md text-label-md">Tiket Masuk</span>
                @if(isset($pendingCount) && $pendingCount > 0)
                    <span class="ml-auto bg-error text-on-error font-badge-xs text-badge-xs py-0.5 px-2 rounded-full">{{ $pendingCount }}</span>
                @endif
            </a>

            <!-- Monitoring Overdue -->
            <a href="{{ route('loans.index', ['status' => 'OVERDUE']) }}" 
               class="flex items-center px-3 py-2.5 rounded-lg transition-colors group {{ request()->fullUrlIs('*status=OVERDUE*') ? 'bg-primary-container text-on-primary-container font-semibold' : 'text-secondary-fixed-dim hover:bg-white/5 hover:text-white' }}">
                <span class="material-symbols-outlined mr-3 text-xl opacity-70 group-hover:opacity-100">warning</span>
                <span class="font-label-md text-label-md">Monitoring Overdue</span>
            </a>

            <!-- Laporan & Riwayat -->
            <a href="{{ route('loans.index', ['view' => 'reports']) }}" 
               class="flex items-center px-3 py-2.5 rounded-lg transition-colors group {{ request()->fullUrlIs('*view=reports*') ? 'bg-primary-container text-on-primary-container font-semibold' : 'text-secondary-fixed-dim hover:bg-white/5 hover:text-white' }}">
                <span class="material-symbols-outlined mr-3 text-xl opacity-70 group-hover:opacity-100">history</span>
                <span class="font-label-md text-label-md">Laporan &amp; Riwayat</span>
            </a>

            <div class="pt-4 border-t border-white/10 mt-4">
                <a href="{{ route('catalog.index') }}" target="_blank"
                   class="flex items-center px-3 py-2.5 rounded-lg text-secondary-fixed-dim hover:bg-white/5 hover:text-white transition-colors group">
                    <span class="material-symbols-outlined mr-3 text-xl opacity-70 group-hover:opacity-100">open_in_new</span>
                    <span class="font-label-md text-label-md">Lihat Katalog Publik</span>
                </a>
            </div>
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-white/10 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                    </div>
                    <div class="truncate">
                        <p class="font-label-md text-label-md text-white truncate">{{ auth()->user()->name ?? 'Admin Lab' }}</p>
                        <p class="font-caption-xs text-caption-xs text-secondary-fixed-dim truncate">{{ auth()->user()->email ?? 'admin@lab.edu' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="p-1.5 text-secondary-fixed-dim hover:text-error hover:bg-white/5 rounded-md transition-colors" title="Keluar">
                        <span class="material-symbols-outlined text-xl">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full bg-surface overflow-hidden">
        <!-- Top Header -->
        <header class="h-16 bg-surface-container-lowest border-b border-outline-variant flex items-center justify-between px-4 md:px-8 flex-shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="md:hidden text-on-surface-variant p-1 rounded-md hover:bg-surface-container">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div>
                    <h1 class="font-headline-md text-headline-md text-on-surface">{{ $pageTitle ?? $title ?? 'Admin Dashboard' }}</h1>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block text-on-surface-variant font-caption-xs text-caption-xs">
                    <span>{{ now()->translatedFormat('l, d M Y') }}</span><br/>
                    <span class="font-label-md text-label-md text-on-surface font-semibold">{{ now()->format('H:i') }} WIB</span>
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
