@extends('layouts.catalog', ['title' => 'Logbook Kegiatan Laboratorium'])

@section('content')
<div class="py-10 px-margin-page md:px-gutter-md max-w-5xl mx-auto w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="font-headline-sm text-headline-sm text-on-surface font-bold">Logbook Kegiatan Laboratorium</h1>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Catat kehadiran praktikum, riset skripsi, dan kegiatan mandiri di seluruh laboratorium Teknik Mesin.</p>
        </div>
        <a href="{{ route('activity-logs.create') }}" 
           class="inline-flex items-center justify-center gap-2 whitespace-nowrap h-10 px-5 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white rounded-lg font-semibold shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer text-sm">
            <span class="material-symbols-outlined text-sm">add</span>
            Catat Kegiatan Baru
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 bg-surface-container-lowest p-4 rounded-xl border border-outline-variant shadow-xs">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('activity-logs.index') }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ !request('status') ? 'bg-primary text-white shadow-xs' : 'bg-surface-container hover:bg-surface-container-high text-on-surface-variant' }}">
                Semua Catatan
            </a>
            <a href="{{ route('activity-logs.index', ['status' => 'PENDING']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('status') === 'PENDING' ? 'bg-amber-600 text-white shadow-xs' : 'bg-surface-container hover:bg-surface-container-high text-on-surface-variant' }}">
                Menunggu Verifikasi
            </a>
            <a href="{{ route('activity-logs.index', ['status' => 'VERIFIED']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('status') === 'VERIFIED' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-surface-container hover:bg-surface-container-high text-on-surface-variant' }}">
                Terverifikasi
            </a>
            <a href="{{ route('activity-logs.index', ['status' => 'REJECTED']) }}" 
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ request('status') === 'REJECTED' ? 'bg-rose-600 text-white shadow-xs' : 'bg-surface-container hover:bg-surface-container-high text-on-surface-variant' }}">
                Ditolak
            </a>
        </div>
    </div>

    <!-- Logs List -->
    <div class="space-y-4">
        @forelse ($logs as $log)
            <div class="p-5 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-xs hover:border-blue-300 transition-all">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-outline-variant/60 pb-3 mb-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-secondary-container text-on-secondary-container">
                            <span class="material-symbols-outlined text-xs">science</span>
                            {{ $log->laboratory->name }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            {{ $log->activity_type }}
                        </span>
                        <span class="font-mono text-xs text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_today</span>
                            {{ $log->activity_date->translatedFormat('d M Y') }}
                        </span>
                        <span class="font-mono text-xs text-on-surface-variant flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">schedule</span>
                            {{ substr($log->time_in, 0, 5) }} - {{ $log->time_out ? substr($log->time_out, 0, 5) : 'Selesai' }}
                        </span>
                    </div>

                    <div>
                        @if ($log->status === 'VERIFIED')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                                <span class="material-symbols-outlined text-sm">verified</span> Terverifikasi
                            </span>
                        @elseif ($log->status === 'REJECTED')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-300 dark:border-rose-800">
                                <span class="material-symbols-outlined text-sm">cancel</span> Ditolak
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-300 dark:border-amber-800">
                                <span class="material-symbols-outlined text-sm">hourglass_empty</span> Menunggu Verifikasi
                            </span>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="font-title-sm text-title-sm text-on-surface font-bold">{{ $log->title }}</h3>
                    @if ($log->description)
                        <p class="font-body-md text-body-md text-on-surface-variant mt-1.5 whitespace-pre-line">{{ $log->description }}</p>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-xs text-on-surface-variant">
                    <div>
                        @if ($log->verifier)
                            <span>Diverifikasi oleh: <strong class="text-on-surface font-semibold">{{ $log->verifier->name }}</strong></span>
                        @else
                            <span class="italic text-slate-400">Belum diverifikasi petugas laboran</span>
                        @endif
                    </div>

                    @if ($log->status === 'PENDING')
                        <form method="POST" action="{{ route('activity-logs.destroy', $log) }}" onsubmit="return confirm('Hapus catatan kegiatan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-600 hover:text-rose-700 hover:underline inline-flex items-center gap-1 font-medium">
                                <span class="material-symbols-outlined text-sm">delete</span> Hapus
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-12 text-center bg-surface-container-lowest rounded-xl border border-outline-variant shadow-xs">
                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <span class="material-symbols-outlined text-3xl">menu_book</span>
                </div>
                <h3 class="font-title-sm text-title-sm text-on-surface font-bold">Belum Ada Catatan Kegiatan</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-md mx-auto">Anda belum memiliki catatan kegiatan laboratorium. Gunakan tombol di atas untuk mencatat aktivitas praktikum atau riset Anda.</p>
                <div class="mt-5">
                    <a href="{{ route('activity-logs.create') }}" 
                       class="inline-flex items-center justify-center gap-2 whitespace-nowrap h-10 px-5 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white rounded-lg font-semibold shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all text-sm">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Catat Kegiatan Sekarang
                    </a>
                </div>
            </div>
        @endforelse

        <!-- Pagination -->
        @if ($logs->hasPages())
            <div class="pt-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
