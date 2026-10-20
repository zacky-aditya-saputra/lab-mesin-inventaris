<x-guest-layout>
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-xs">
            <span class="material-symbols-outlined text-2xl font-bold">key</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Atur Ulang Kata Sandi</h1>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Silakan buat kata sandi baru yang kuat untuk mengamankan akun LabCatalog Anda.
        </p>
    </div>

    <!-- Alert Kesalahan Validasi -->
    @if ($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <span class="font-bold block mb-0.5">Gagal Mengatur Kata Sandi:</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Alamat Email
            </label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">mail</span>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email', $request->email) }}"
                       required
                       autofocus
                       autocomplete="username"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
            </div>
            @error('email')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Baru -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Kata Sandi Baru
            </label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       autocomplete="new-password"
                       placeholder="Minimal 8 karakter"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
            </div>
            @error('password')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Konfirmasi Password -->
        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Konfirmasi Kata Sandi Baru
            </label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock_reset</span>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       placeholder="Ulangi kata sandi baru"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
            </div>
            @error('password_confirmation')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                <span>Simpan Kata Sandi Baru</span>
                <span class="material-symbols-outlined text-sm">check</span>
            </button>
        </div>
    </form>
</x-guest-layout>
