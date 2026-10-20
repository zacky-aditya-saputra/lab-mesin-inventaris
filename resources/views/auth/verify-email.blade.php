<x-guest-layout>
    <div class="text-center mb-6">
        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-xs">
            <span class="material-symbols-outlined text-2xl font-bold">mark_email_unread</span>
        </div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Verifikasi Alamat Email</h1>
        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
            Terima kasih telah mendaftar di LabCatalog! Sebelum mulai mengajukan peminjaman, silakan verifikasi alamat email Anda melalui tautan yang baru saja kami kirimkan.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 flex items-center gap-2.5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
            <span class="material-symbols-outlined text-base text-emerald-600 shrink-0">mark_email_read</span>
            <span>Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda daftarkan.</span>
        </div>
    @endif

    <div class="space-y-3 pt-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                <span class="material-symbols-outlined text-base">outgoing_mail</span>
                <span>Kirim Ulang Email Verifikasi</span>
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full py-2 px-4 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-sm">logout</span>
                <span>Keluar / Logout</span>
            </button>
        </form>
    </div>
</x-guest-layout>
