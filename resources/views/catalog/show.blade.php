@extends('layouts.catalog', ['title' => $tool->name])

@section('content')
<div class="py-8 px-4 sm:px-6 md:px-8 max-w-xl mx-auto w-full flex flex-col gap-6">

    <!-- Breadcrumb & Back Link (Clean Navigation) -->
    <div>
        <a href="{{ route('catalog.index') }}"
           class="inline-flex items-center gap-1.5 text-slate-500 hover:text-blue-600 transition-colors font-medium text-xs sm:text-sm group">
            <span class="material-symbols-outlined text-base group-hover:-translate-x-0.5 transition-transform">arrow_back</span>
            Kembali ke Katalog Utama
        </a>
    </div>

    <!-- Floating Minimalist Equipment Detail Card (Inspirasi Dribbble rounded-3xl) -->
    <article class="relative w-full bg-white rounded-3xl shadow-xl border border-slate-200/90 overflow-hidden flex flex-col transition-all">

        <!-- 1. Hero Visual (Pristine, Anti-AI-slop Product View) -->
        <div class="relative w-full aspect-[16/10] bg-slate-100 overflow-hidden flex items-center justify-center border-b border-slate-100">
            @if ($tool->image_path)
                <img src="{{ asset('storage/'.$tool->image_path) }}" alt="{{ $tool->name }}"
                     decoding="async"
                     class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"/>
            @else
                <div class="flex flex-col items-center justify-center p-6 text-slate-400 select-none">
                    <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-200 bg-white shadow-xs">
                        <span class="material-symbols-outlined text-4xl text-blue-600/80">precision_manufacturing</span>
                    </div>
                    <span class="font-mono text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $tool->code }}</span>
                </div>
            @endif

            <!-- Minimal Technical Registration Stamp -->
            <span class="absolute bottom-3 left-4 font-mono text-[10px] uppercase tracking-widest text-slate-500 bg-white/85 px-2 py-0.5 rounded backdrop-blur-xs shadow-2xs">
                REF // {{ $tool->code }}
            </span>
        </div>

        <!-- 2. Technical Information & Compact Specs -->
        <div class="flex flex-col px-6 pt-5">
            <!-- Tool Title -->
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-snug">
                {{ $tool->name }}
            </h1>

            <!-- Subtle Metadata (Clean Plain-text, Tanpa Gelembung Warna-warni) -->
            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-500 font-medium">
                <span>{{ $tool->category?->name ?? 'Peralatan Teknis' }}</span>
                <span class="text-slate-300">&bull;</span>
                <span>{{ $tool->laboratory?->name ?? 'Laboratorium Mesin' }}</span>
                <span class="text-slate-300">&bull;</span>
                <span>Gedung D</span>
            </div>

            <!-- Concise Purpose Description -->
            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mt-3 whitespace-pre-line">
                {{ $tool->specification ?? 'Peralatan penunjang praktikum dan penelitian presisi mahasiswa Teknik Mesin Universitas Wahid Hasyim.' }}
            </p>

            <!-- Compact Key Specs Strip (2 Key Specs Awal Sesuai Aturan Desain) -->
            <div class="mt-4 py-2.5 px-4 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between text-xs text-slate-700">
                <div class="flex items-center gap-1.5">
                    <span class="font-mono text-slate-400 text-[11px]">Kode Aset:</span>
                    <span class="font-mono font-semibold text-slate-900">{{ $tool->code }}</span>
                </div>
                <span class="text-slate-300">&bull;</span>
                <div class="flex items-center gap-1.5">
                    <span class="font-mono text-slate-400 text-[11px]">Total Fisik:</span>
                    <span class="font-semibold text-slate-900">{{ $tool->total_stock }} Unit</span>
                </div>
            </div>

            <!-- Interactive Collapsible Accordion ([ ▾ Lihat Spesifikasi Lengkap ]) -->
            <details class="group mt-3.5 mb-2" id="accordion-specs">
                <summary class="list-none flex items-center justify-between text-xs font-semibold text-blue-600 hover:text-blue-700 cursor-pointer py-1.5 transition-colors select-none">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base transition-transform duration-200 group-open:rotate-180">expand_more</span>
                        <span>Lihat Spesifikasi Lengkap &amp; Aksesoris</span>
                    </span>
                    <span class="font-mono text-[10px] text-slate-400 uppercase tracking-wider group-open:hidden">[ Rincian ]</span>
                </summary>
                <div class="mt-2.5 pt-2 pb-2 space-y-2 text-xs border-t border-slate-100 transition-all duration-300">
                    <div class="flex items-baseline justify-between py-1">
                        <span class="text-slate-500 font-medium">Laboratorium Pengampu</span>
                        <span class="font-semibold text-slate-900 text-right">{{ $tool->laboratory?->name ?? '-' }}</span>
                    </div>
                    @if ($tool->laboratory?->location)
                        <div class="flex items-baseline justify-between py-1">
                            <span class="text-slate-500 font-medium">Lokasi Ruangan</span>
                            <span class="font-semibold text-slate-900 text-right">{{ $tool->laboratory->location }}</span>
                        </div>
                    @endif
                    <div class="flex items-baseline justify-between py-1">
                        <span class="text-slate-500 font-medium">Status Kelaikan</span>
                        <span class="font-semibold text-emerald-600 text-right">Terkalibrasi Standar Laboratorium</span>
                    </div>
                    <div class="flex items-baseline justify-between py-1">
                        <span class="text-slate-500 font-medium">Syarat Pengajuan</span>
                        <span class="font-semibold text-slate-900 text-right">H-2 Hari Kerja &amp; Wajib APD</span>
                    </div>
                    <div class="flex flex-col py-1">
                        <span class="text-slate-500 font-medium mb-1">Catatan Serah Terima</span>
                        <span class="text-slate-600 text-[11px] leading-relaxed">
                            Pengecekan kondisi nol (zero check), kelengkapan aksesoris, dan tanda terima berita acara wajib dilakukan bersama laboran saat serah terima alat.
                        </span>
                    </div>
                </div>
            </details>
        </div>

        <!-- 3. Footer Telemetry & Action Row -->
        <div class="mt-4 px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0">
            <!-- Stock Status Telemetry (Monospace Digits + Pulse Dot) -->
            <div class="flex flex-col">
                <div class="font-mono text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-none">
                    {{ $tool->available_stock }}
                    <span class="text-xs font-normal text-slate-500">Unit</span>
                </div>
                <div class="text-[11px] font-semibold flex items-center gap-1.5 mt-1 {{ $tool->available_stock > 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $tool->available_stock > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <span>{{ $availabilityStatus }}</span>
                </div>
            </div>

            <!-- Primary Action Pill CTA (Direct Single-Item Loan) -->
            @if ($tool->available_stock > 0)
                <a href="{{ route('loans.create', ['tool_id' => $tool->id]) }}"
                   class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold text-xs sm:text-sm px-6 py-2.5 rounded-full shadow-md shadow-blue-600/20 transition-all cursor-pointer flex items-center gap-2">
                    <span>Ajukan Peminjaman</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </a>
            @else
                <button type="button" disabled
                        class="bg-slate-200 text-slate-400 font-semibold text-xs sm:text-sm px-5 py-2.5 rounded-full cursor-not-allowed flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">block</span>
                    <span>Stok Habis</span>
                </button>
            @endif
        </div>
    </article>
</div>
@endsection
