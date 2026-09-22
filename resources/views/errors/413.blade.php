@extends('layouts.catalog', ['title' => 'Berkas Terlalu Besar (413)'])

@section('content')
<main class="flex-grow flex items-center justify-center px-gutter-md py-16">
    <div class="w-full max-w-md bg-surface-container-lowest rounded-2xl shadow-sm border border-outline-variant p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-error-container text-error mx-auto flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">cloud_alert</span>
        </div>

        <h1 class="font-headline-md text-headline-md font-bold text-on-surface mb-3">Ukuran Berkas Terlalu Besar</h1>
        
        <p class="font-body-md text-body-md text-on-surface-variant mb-6 leading-relaxed">
            Berkas yang Anda unggah melebihi kapasitas transfer server. Sistem membatasi ukuran berkas maksimal <strong>5MB</strong> per berkas.
        </p>

        <div class="p-4 bg-surface-container-low rounded-xl text-left border border-outline-variant mb-6 space-y-2">
            <p class="font-label-md text-label-md font-semibold text-on-surface flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-base">tips_and_updates</span>
                Solusi Cepat:
            </p>
            <ul class="text-xs text-on-surface-variant list-disc list-inside space-y-1">
                <li>Gunakan foto berformat JPG/PNG/WebP (sistem otomatis mengompres foto di form pendaftaran).</li>
                <li>Jika menggunakan PDF, kompres dokumen terlebih dahulu di bawah 5MB.</li>
            </ul>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="javascript:history.back()" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary text-on-primary font-label-md text-label-md px-6 py-2.5 rounded-lg hover:bg-primary-container transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali ke Formulir
            </a>
            <a href="{{ route('catalog.index') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 border border-outline-variant text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container transition-colors">
                Katalog Alat
            </a>
        </div>
    </div>
</main>
@endsection
