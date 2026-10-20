<x-guest-layout>
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-xs">
            <span class="material-symbols-outlined text-2xl font-bold">lock_reset</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Lupa Kata Sandi?</h1>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi akun Anda.
        </p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-5 flex items-center gap-2.5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
            <span class="material-symbols-outlined text-base text-emerald-600 shrink-0">check_circle</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                Alamat Email Akun
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

        <div class="pt-2">
            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                <span>Kirim Tautan Reset Password</span>
                <span class="material-symbols-outlined text-sm">send</span>
            </button>
        </div>
    </form>

    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-blue-600 transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            <span>Kembali ke Halaman Masuk</span>
        </a>
    </div>
</x-guest-layout>
