@extends('layouts.catalog', ['title' => 'Terlalu Banyak Permintaan (429)'])

@section('content')
<main class="flex-grow flex items-center justify-center px-4 py-16">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center mb-5 border border-amber-200">
            <span class="material-symbols-outlined text-3xl">lock_clock</span>
        </div>

        <h1 class="text-xl font-bold text-slate-900 mb-2 tracking-tight">
            Terlalu Banyak Permintaan
        </h1>
        
        <p class="text-xs sm:text-sm text-slate-600 mb-6 leading-relaxed">
            Terlalu banyak percobaan dalam waktu singkat. Harap tunggu beberapa saat sebelum mencoba kembali.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('login') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-xl shadow-xs transition-all">
                <span class="material-symbols-outlined text-base">login</span>
                Halaman Masuk
            </a>
            <a href="{{ route('catalog.index') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs sm:text-sm font-semibold px-5 py-2.5 rounded-xl transition-all">
                Katalog Alat
            </a>
        </div>
    </div>
</main>
@endsection
