<x-guest-layout>
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-xs">
            <span class="material-symbols-outlined text-2xl font-bold">security</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Konfirmasi Keamanan</h1>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Ini adalah area sistem yang terlindungi. Harap konfirmasikan kata sandi akun Anda sebelum melanjutkan.
        </p>
    </div>

    <!-- Alert Kesalahan Validasi -->
    @if ($errors->any())
        <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <span class="font-bold block mb-0.5">Konfirmasi Gagal:</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Kata Sandi Akun
            </label>
            <div class="relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       autofocus
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
            </div>
            @error('password')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                <span class="material-symbols-outlined text-sm">lock_open</span>
                <span>Konfirmasi Kata Sandi</span>
            </button>
        </div>
    </form>
</x-guest-layout>
