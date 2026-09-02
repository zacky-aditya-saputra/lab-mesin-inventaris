<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk - LabCatalog</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-container text-on-surface font-body-md flex flex-col min-h-screen antialiased">
    <!-- TopNavBar Component -->
    <header class="bg-surface border-b border-outline-variant shadow-sm fixed top-0 w-full z-50">
        <div class="flex justify-between items-center h-16 px-gutter-md max-w-container-max mx-auto">
            <!-- Brand -->
            <a href="{{ route('catalog.index') }}" class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">science</span>
                <span class="font-headline-sm text-headline-sm text-primary font-bold">LabCatalog</span>
            </a>
            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-6">
                <a class="text-on-surface-variant hover:text-primary transition-colors duration-200 font-label-md text-label-md" href="{{ route('catalog.index') }}">Katalog</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors duration-200 font-label-md text-label-md" href="#panduan">Panduan</a>
                <a class="text-on-surface-variant hover:text-primary transition-colors duration-200 font-label-md text-label-md" href="#kontak">Kontak</a>
            </nav>
            <!-- Action -->
            <div class="flex items-center gap-3">
                <a class="text-primary font-bold font-label-md text-label-md" href="{{ route('login') }}">Masuk</a>
                <a class="text-on-surface-variant hover:text-primary font-label-md text-label-md" href="{{ route('register') }}">Daftar</a>
            </div>
        </div>
    </header>

    <!-- Main Content Canvas -->
    <main class="flex-grow flex items-center justify-center pt-24 pb-12 px-gutter-md w-full">
        <!-- Login Card -->
        <div class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant p-8 relative overflow-hidden">
            <!-- Subtle top accent -->
            <div class="absolute top-0 left-0 w-full h-1 bg-primary"></div>

            <!-- Header -->
            <div class="mb-8">
                <h1 class="font-headline-md text-headline-md text-on-surface font-bold mb-2">Masuk ke LabCatalog</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Gunakan akun Anda untuk mengajukan peminjaman alat laboratorium.</p>
            </div>

            <!-- Validation Error Alert -->
            @if ($errors->any())
                <div class="bg-error-container border border-error rounded-lg p-4 mb-6 flex items-start gap-3">
                    <span class="material-symbols-outlined text-error mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                    <div class="flex-1">
                        <p class="font-label-md text-label-md text-on-error-container">
                            {{ $errors->first() }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Session Status -->
            @if (session('status'))
                <div class="bg-status-success-bg border border-status-success-text/30 text-status-success-text rounded-lg p-4 mb-6 font-label-md text-label-md">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login') }}" class="space-y-5" method="POST">
                @csrf

                <!-- Email Field -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface-variant mb-1.5" for="email">Email Address</label>
                    <input autofocus 
                           class="w-full rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-1 focus:ring-primary h-11 px-3 font-body-md text-body-md transition-shadow" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="mahasiswa@univ.edu" 
                           required 
                           type="email"/>
                    @error('email')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface-variant mb-1.5" for="password">Password</label>
                    <input class="w-full rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-1 focus:ring-primary h-11 px-3 font-body-md text-body-md transition-shadow" 
                           id="password" 
                           name="password" 
                           placeholder="••••••••" 
                           required 
                           type="password"/>
                    @error('password')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input class="rounded border-outline-variant text-primary focus:ring-primary w-4 h-4 cursor-pointer transition-colors" 
                               name="remember" 
                               type="checkbox"/>
                        <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-on-surface transition-colors">Ingat saya</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a class="font-label-md text-label-md text-primary hover:underline transition-all" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button class="w-full bg-primary text-on-primary font-label-md text-label-md rounded-lg h-11 mt-6 flex items-center justify-center hover:bg-primary-container transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary" 
                        type="submit">
                    Masuk
                </button>
            </form>

            <!-- Registration Link -->
            <div class="mt-8 text-center border-t border-outline-variant pt-6">
                <p class="font-body-md text-body-md text-on-surface-variant">
                    Belum punya akun? 
                    <a class="text-primary font-label-md text-label-md font-semibold hover:underline ml-1" href="{{ route('register') }}">
                        Daftar sekarang
                    </a>
                </p>
            </div>
        </div>
    </main>

    <!-- Footer Component -->
    <footer class="bg-sidebar-dark w-full mt-auto">
        <div class="flex flex-col md:flex-row justify-between items-center py-6 px-gutter-md max-w-container-max mx-auto gap-4">
            <!-- Brand / Copyright -->
            <div class="flex flex-col items-center md:items-start gap-1">
                <span class="font-headline-sm text-headline-sm text-primary-fixed font-bold">LabCatalog</span>
                <span class="font-caption-xs text-caption-xs text-surface-variant opacity-70">© 2026 Laboratorium Teknik Mesin Unwahas</span>
            </div>
            <!-- Footer Links -->
            <div class="flex items-center gap-6">
                <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Kebijakan Privasi</a>
                <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Syarat &amp; Ketentuan</a>
                <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Bantuan</a>
            </div>
        </div>
    </footer>
</body>
</html>
