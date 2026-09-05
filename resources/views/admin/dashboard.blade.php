@extends('layouts.admin', ['title' => 'Dashboard Utama', 'pageTitle' => 'Dashboard Utama'])

@section('content')
<div class="space-y-6">
    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Card 1: Tiket Menunggu Verifikasi -->
        <a href="{{ route('admin.loans.index', ['status' => 'PENDING']) }}" 
           class="bg-surface-container-lowest rounded-xl p-padding-card shadow-sm border border-outline-variant flex flex-col justify-between hover:shadow-md transition-shadow group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant group-hover:text-primary transition-colors">Tiket Menunggu</p>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mt-1">{{ $pendingLoansCount ?? 0 }}</h2>
                </div>
                <div class="w-10 h-10 rounded-lg bg-status-pending-bg text-status-pending-text flex items-center justify-center">
                    <span class="material-symbols-outlined">hourglass_empty</span>
                </div>
            </div>
            <div class="flex items-center space-x-1 text-status-pending-text font-caption-xs text-caption-xs">
                <span class="material-symbols-outlined text-[16px]">info</span>
                <span>Butuh Verifikasi</span>
            </div>
        </a>

        <!-- Card 2: Tiket Terlambat (Overdue) -->
        <a href="{{ route('admin.loans.index', ['status' => 'OVERDUE']) }}" 
           class="bg-surface-container-lowest rounded-xl p-padding-card shadow-sm border border-outline-variant flex flex-col justify-between hover:shadow-md transition-shadow group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant group-hover:text-error transition-colors">Tiket Terlambat</p>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mt-1">{{ $overdueLoansCount ?? 0 }}</h2>
                </div>
                <div class="w-10 h-10 rounded-lg bg-status-overdue-bg text-status-overdue-text flex items-center justify-center">
                    <span class="material-symbols-outlined">warning</span>
                </div>
            </div>
            <div class="flex items-center space-x-1 text-error font-caption-xs text-caption-xs">
                <span class="material-symbols-outlined text-[16px]">priority_high</span>
                <span>Perlu Tindak Lanjut</span>
            </div>
        </a>

        <!-- Card 3: Alat Sedang Dipinjam -->
        <a href="{{ route('admin.loans.index', ['status' => 'ON_LOAN']) }}" 
           class="bg-surface-container-lowest rounded-xl p-padding-card shadow-sm border border-outline-variant flex flex-col justify-between hover:shadow-md transition-shadow group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant group-hover:text-primary transition-colors">Sedang Dipinjam</p>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mt-1">{{ $activeLoansCount ?? 0 }}</h2>
                </div>
                <div class="w-10 h-10 rounded-lg bg-status-loan-bg text-status-loan-text flex items-center justify-center">
                    <span class="material-symbols-outlined">assignment</span>
                </div>
            </div>
            <div class="flex items-center space-x-1 text-status-loan-text font-caption-xs text-caption-xs">
                <span class="material-symbols-outlined text-[16px]">schedule</span>
                <span>Dalam Peminjaman</span>
            </div>
        </a>

        <!-- Card 4: Total Master Alat -->
        <a href="{{ route('admin.tools.index') }}" 
           class="bg-surface-container-lowest rounded-xl p-padding-card shadow-sm border border-outline-variant flex flex-col justify-between hover:shadow-md transition-shadow group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant group-hover:text-primary transition-colors">Total Alat Lab</p>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mt-1">{{ $totalTools ?? 0 }}</h2>
                </div>
                <div class="w-10 h-10 rounded-lg bg-status-success-bg text-status-success-text flex items-center justify-center">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
            </div>
            <div class="flex items-center space-x-1 text-status-success-text font-caption-xs text-caption-xs">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                <span>{{ $totalCategories ?? 0 }} Kategori • {{ $totalStock ?? 0 }} Unit Fisik</span>
            </div>
        </a>
    </div>

    <!-- Quick Actions Banner -->
    <div class="bg-gradient-to-r from-primary to-primary-container text-on-primary rounded-xl p-6 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="font-title-sm text-title-sm font-bold text-white mb-1">Pintasan Layanan Cepat</h3>
            <p class="font-body-md text-body-md text-on-primary-container opacity-90">Verifikasi antrean tiket pengajuan mahasiswa atau daftarkan unit alat laboratorium baru.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.loans.index') }}" class="px-4 py-2 bg-white text-primary font-label-md text-label-md font-semibold rounded-lg hover:bg-slate-100 transition-colors shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">confirmation_number</span>
                Lihat Antrean Tiket
            </a>
            <a href="{{ route('admin.tools.create') }}" class="px-4 py-2 bg-primary-fixed text-on-primary-fixed font-label-md text-label-md font-semibold rounded-lg hover:bg-white transition-colors shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span>
                Tambah Alat Baru
            </a>
        </div>
    </div>

    <!-- Recent Tickets Table -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <div class="p-4 md:p-6 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/50">
            <div>
                <h3 class="font-title-sm text-title-sm text-on-surface">Pengajuan Tiket Terbaru</h3>
                <p class="font-caption-xs text-caption-xs text-on-surface-variant">Daftar permohonan peminjaman alat terkini dari mahasiswa.</p>
            </div>
            <a href="{{ route('admin.loans.index') }}" class="text-primary hover:underline font-label-md text-label-md flex items-center gap-1">
                Lihat Semua Tiket
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="px-6 py-3.5 font-semibold">No. Tiket</th>
                        <th class="px-6 py-3.5 font-semibold">Nama Mahasiswa</th>
                        <th class="px-6 py-3.5 font-semibold">Item Alat</th>
                        <th class="px-6 py-3.5 font-semibold">Rentang Pinjam</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                    @forelse ($recentLoans ?? [] as $loan)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-6 py-4 font-mono font-medium text-primary">
                                {{ $loan->ticket_number }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-on-surface">{{ $loan->user->name ?? '-' }}</div>
                                <div class="font-caption-xs text-caption-xs text-on-surface-variant">NIM: {{ $loan->user->identity_number ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant">
                                @if($loan->items->isNotEmpty())
                                    {{ $loan->items->first()->tool->name ?? 'Alat' }}
                                    @if($loan->items->count() > 1)
                                        <span class="font-caption-xs text-caption-xs text-secondary font-medium">+{{ $loan->items->count() - 1 }} lainnya</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant whitespace-nowrap">
                                {{ $loan->start_date?->format('d M') }} - {{ $loan->end_date?->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusClasses = [
                                        'PENDING' => 'bg-status-pending-bg text-status-pending-text border-status-pending-text/20',
                                        'APPROVED' => 'bg-status-approved-bg text-status-approved-text border-status-approved-text/20',
                                        'PARTIALLY_APPROVED' => 'bg-status-partial-bg text-status-partial-text border-status-partial-text/20',
                                        'REJECTED' => 'bg-status-rejected-bg text-status-rejected-text border-status-rejected-text/20',
                                        'ON_LOAN' => 'bg-status-loan-bg text-status-loan-text border-status-loan-text/20',
                                        'OVERDUE' => 'bg-status-overdue-bg text-status-overdue-text border-status-overdue-text/20',
                                        'RETURNED' => 'bg-status-success-bg text-status-success-text border-status-success-text/20',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs border {{ $statusClasses[$loan->status] ?? 'bg-surface-container text-on-surface' }}">
                                    {{ $loan->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.loans.review', $loan) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md transition-colors shadow-sm">
                                    Atur
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl text-outline">inbox</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface">Belum ada aktivitas tiket terbaru</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Pengajuan peminjaman oleh mahasiswa akan muncul di tabel ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
