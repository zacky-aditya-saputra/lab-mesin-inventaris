@extends('layouts.catalog', ['title' => 'Masuk - LabCatalog'])

@section('content')
<div class="mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8 py-8 lg:py-12 flex-1 flex items-center justify-center">
    <div class="w-full grid grid-cols-1 lg:grid-cols-12 rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        
        <!-- Sisi Kiri: Showcase Gedung Fakultas Teknik Unwahas (Desktop Only) -->
        <div class="lg:col-span-5 bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 p-8 sm:p-10 text-white flex-col justify-between relative overflow-hidden hidden lg:flex">
            <!-- Background Foto Gedung Teknik Asli -->
            @if (file_exists(public_path('images/gedung-teknik.jpeg')))
                <img src="{{ asset('images/gedung-teknik.jpeg') }}"
                     alt="Gedung Fakultas Teknik Unwahas"
                     class="absolute inset-0 w-full h-full object-cover object-[center_60%] opacity-25 mix-blend-overlay filter contrast-125" />
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-blue-900/40 to-transparent"></div>

            <!-- Content Atas -->
            <div class="relative z-10 space-y-4">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-semibold text-white">
                    <span class="material-symbols-outlined text-sm">location_on</span>
                    <span>Kampus II Unwahas</span>
                </div>
                <div>
                    <span class="text-xs font-bold text-blue-200 uppercase tracking-wider block">FAKULTAS TEKNIK</span>
                    <h2 class="text-2xl font-black tracking-tight text-white mt-1 leading-snug">Pusat Peminjaman &amp; Inventaris Lab</h2>
                </div>
            </div>

            <!-- Content Tengah (Poin Keunggulan) -->
            <div class="relative z-10 space-y-3.5 my-8">
                <div class="flex items-start gap-3 text-xs text-blue-100">
                    <span class="material-symbols-outlined text-emerald-400 text-lg shrink-0 mt-0.5">verified</span>
                    <span><strong>6 Laboratorium Pengampu</strong> siap melayani praktikum, riset skripsi, dan kegiatan mahasiswa.</span>
                </div>
                <div class="flex items-start gap-3 text-xs text-blue-100">
                    <span class="material-symbols-outlined text-cyan-400 text-lg shrink-0 mt-0.5">sync</span>
                    <span><strong>Stok Terhubung Real-Time</strong> untuk memantau ketersediaan unit alat sebelum mengajukan.</span>
                </div>
                <div class="flex items-start gap-3 text-xs text-blue-100">
                    <span class="material-symbols-outlined text-amber-400 text-lg shrink-0 mt-0.5">task_alt</span>
                    <span><strong>Peminjaman Mandiri</strong> tanpa formulir kertas fisik dengan verifikasi cepat oleh laboran.</span>
                </div>
            </div>

            <!-- Content Bawah -->
            <div class="relative z-10 pt-4 border-t border-white/15 text-[11px] text-blue-200/80">
                &copy; {{ date('Y') }} Laboratorium Teknik Mesin &middot; Universitas Wahid Hasyim
            </div>
        </div>

        <!-- Sisi Kanan: Formulir Masuk -->
        <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-center">
            <div class="mb-6">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold tracking-wide uppercase mb-2">
                    Portal Masuk Civitas &amp; Klien
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Masuk ke LabCatalog</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Gunakan akun Anda untuk mengakses peminjaman alat dan logbook kegiatan.</p>
            </div>

            <!-- Status Session (Misal: Password berhasil di-reset) -->
            @if (session('status'))
                <div class="mb-5 flex items-center gap-2.5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm">
                    <span class="material-symbols-outlined text-lg text-emerald-600 shrink-0">check_circle</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <!-- Alert Kesalahan Validasi -->
            @if ($errors->any())
                <div class="mb-5 flex items-start gap-2.5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
                    <span class="material-symbols-outlined text-lg text-rose-600 shrink-0 mt-0.5">error</span>
                    <div>
                        <span class="font-bold block">Gagal Masuk:</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4" x-data="{ showPass: false }">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">mail</span>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               placeholder="nama@unwahas.ac.id"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-700">
                            Kata Sandi
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                                Lupa kata sandi?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                        <input :type="showPass ? 'text' : 'password'"
                               id="password"
                               name="password"
                               required
                               placeholder="••••••••"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-10 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                        <button type="button" @click="showPass = !showPass"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 cursor-pointer"
                                aria-label="Toggle password visibility">
                            <span class="material-symbols-outlined text-lg" x-text="showPass ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox"
                               name="remember"
                               class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                        <span class="text-xs font-medium text-slate-600">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                        <span>Masuk ke Akun</span>
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </button>
                </div>
            </form>

            <!-- Switcher to Register -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs sm:text-sm text-slate-500">
                    Belum memiliki akun?
                    <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:text-blue-700 hover:underline ml-1">
                        Daftar akun baru
                    </a>
                </p>
            </div>

            <!-- Info untuk Laboran/Admin -->
            <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-start gap-2.5 text-[11px] text-slate-500">
                <span class="material-symbols-outlined text-slate-400 text-base shrink-0 mt-0.5">info</span>
                <span>Laboran dan Kepala Lab juga dapat langsung masuk melalui portal ini untuk mengakses dashboard pengelolaan laboratorium.</span>
            </div>
        </div>

    </div>
</div>
@endsection
