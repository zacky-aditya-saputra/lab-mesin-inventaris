<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daftar Akun - LabCatalog</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface font-body-md min-h-screen flex flex-col antialiased">
    <!-- TopNavBar -->
    <header class="bg-surface fixed top-0 w-full z-50 border-b border-outline-variant shadow-sm flex justify-between items-center h-16 px-gutter-md mx-auto">
        <a href="{{ route('catalog.index') }}" class="font-headline-sm text-headline-sm text-primary font-bold flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">science</span>
            LabCatalog
        </a>
        <a class="font-label-md text-label-md text-primary hover:text-primary-container transition-colors duration-200" href="{{ route('login') }}">
            Masuk
        </a>
    </header>

    <!-- Main Registration Section -->
    <main class="flex-grow flex items-center justify-center p-margin-page mt-16 pt-8 pb-12">
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant w-full max-w-lg p-padding-card">
            <div class="mb-6 text-center">
                <h1 class="font-headline-md text-headline-md text-on-surface mb-2 font-bold">Daftar Akun Baru</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">Lengkapi data di bawah ini untuk mulai menggunakan layanan peminjaman alat laboratorium.</p>
            </div>

            <!-- Validation Error Alert -->
            @if ($errors->any())
                <div class="bg-error-container border border-error/30 text-on-error-container p-4 rounded-lg mb-6 shadow-sm" role="alert">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">error</span>
                        <span class="font-label-md text-label-md font-semibold">Terdapat kesalahan pendaftaran:</span>
                    </div>
                    <ul class="list-disc list-inside text-caption-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="name">
                        Nama Lengkap <span class="text-error">*</span>
                    </label>
                    <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-primary shadow-sm" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           placeholder="Contoh: Budi Santoso" 
                           required 
                           type="text"/>
                    @error('name')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NIM / NIP -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="identity_number">
                        NIM / NIP / Nomor Identitas <span class="text-error">*</span>
                    </label>
                    <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-primary shadow-sm font-mono" 
                           id="identity_number" 
                           name="identity_number" 
                           value="{{ old('identity_number') }}" 
                           placeholder="Contoh: 21102001 / 19850101..." 
                           required 
                           type="text"/>
                    @error('identity_number')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Address -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="email">
                        Email Address <span class="text-error">*</span>
                    </label>
                    <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-primary shadow-sm" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="mahasiswa@unwahas.ac.id" 
                           required 
                           type="email"/>
                    @error('email')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No HP / WhatsApp -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="phone_number">
                        No. HP / WhatsApp <span class="text-on-surface-variant font-normal">(Untuk Notifikasi)</span>
                    </label>
                    <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-primary shadow-sm" 
                           id="phone_number" 
                           name="phone_number" 
                           value="{{ old('phone_number') }}" 
                           placeholder="08xxxxxxxxxx" 
                           type="tel"/>
                    @error('phone_number')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="password">
                        Password <span class="text-error">*</span>
                    </label>
                    <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-primary shadow-sm" 
                           id="password" 
                           name="password" 
                           placeholder="Minimal 8 karakter" 
                           required 
                           type="password"/>
                    @error('password')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="password_confirmation">
                        Konfirmasi Password <span class="text-error">*</span>
                    </label>
                    <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-primary shadow-sm" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           placeholder="Ulangi password" 
                           required 
                           type="password"/>
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button class="w-full bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md py-3 px-4 rounded-lg shadow-sm transition-colors duration-200 flex items-center justify-center gap-2" 
                            type="submit">
                        <span class="material-symbols-outlined text-sm">person_add</span>
                        Daftar Akun Mahasiswa
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center font-body-md text-body-md">
                <span class="text-on-surface-variant">Sudah punya akun?</span>
                <a class="text-primary font-semibold hover:underline ml-1" href="{{ route('login') }}">Masuk</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-sidebar-dark w-full mt-auto flex flex-col md:flex-row justify-between items-center py-6 px-gutter-md max-w-container-max mx-auto gap-4">
        <div class="font-headline-sm text-headline-sm text-primary-fixed font-bold">
            LabCatalog
        </div>
        <div class="font-caption-xs text-caption-xs text-surface-variant opacity-70">
            © 2026 Laboratorium Teknik Mesin Unwahas
        </div>
        <nav class="flex gap-4">
            <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Kebijakan Privasi</a>
            <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Syarat &amp; Ketentuan</a>
            <a class="font-caption-xs text-caption-xs text-surface-variant opacity-70 hover:opacity-100 transition-opacity" href="#">Bantuan</a>
        </nav>
    </footer>
</body>
</html>
