@extends('layouts.admin', ['title' => 'Monitoring Alat Terlambat', 'pageTitle' => 'Monitoring Alat Terlambat'])

@section('content')
<div class="space-y-6" x-data="{ searchQuery: '' }">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Monitoring Keterlambatan (Overdue)</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Pantau dan kelola peminjaman alat laboratorium yang telah melewati batas tanggal jatuh tempo.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.loans.index') }}" 
               class="bg-surface-container-lowest border border-outline-variant text-on-surface px-4 py-2 rounded-lg font-label-md text-label-md hover:bg-surface-container transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Kembali ke Antrean
            </a>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Total Overdue -->
        <div class="bg-surface-container-lowest p-padding-card rounded-xl border border-outline-variant shadow-sm flex flex-col gap-2 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10 text-status-overdue-bg">
                <span class="material-symbols-outlined text-[64px]">warning</span>
            </div>
            <span class="font-label-md text-label-md text-on-surface-variant">Total Tiket Terlambat</span>
            <span class="font-headline-lg text-headline-lg text-error font-bold">{{ $overdueLoans->count() }}</span>
            <span class="font-caption-xs text-caption-xs text-error flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">priority_high</span>
                Perlu tindak lanjut &amp; penagihan fisik
            </span>
        </div>

        <!-- Card 2: Alat Terkait -->
        <div class="bg-surface-container-lowest p-padding-card rounded-xl border border-outline-variant shadow-sm flex flex-col gap-2">
            <span class="font-label-md text-label-md text-on-surface-variant">Kategori Masalah</span>
            <span class="font-headline-lg text-headline-lg text-on-surface font-bold">Keterlambatan</span>
            <span class="font-caption-xs text-caption-xs text-on-surface-variant">Tidak ada denda finansial (Sesuai SOP)</span>
        </div>

        <!-- Card 3: Konfirmasi Pengembalian -->
        <div class="bg-surface-container-lowest p-padding-card rounded-xl border border-outline-variant shadow-sm flex flex-col gap-2">
            <span class="font-label-md text-label-md text-on-surface-variant">Status Verifikasi Fisik</span>
            <span class="font-headline-lg text-headline-lg text-status-approved-text font-bold">Pemeriksaan</span>
            <span class="font-caption-xs text-caption-xs text-on-surface-variant">Kondisi: Baik / Rusak / Hilang</span>
        </div>
    </div>

    <!-- Overdue Table Section -->
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden flex flex-col">
        <!-- Controls -->
        <div class="p-4 border-b border-outline-variant bg-surface-container-low flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="relative w-full sm:w-80">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                <input x-model="searchQuery" 
                       class="w-full pl-10 pr-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest font-body-md text-body-md text-on-surface focus:ring-1 focus:ring-primary focus:border-primary" 
                       placeholder="Cari nama mahasiswa, no tiket..." 
                       type="text"/>
            </div>
            <div class="font-caption-xs text-caption-xs text-on-surface-variant">
                Menampilkan {{ $overdueLoans->count() }} data keterlambatan
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-full">
                <thead class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                    <tr>
                        <th class="py-3.5 px-6 font-semibold">No. Tiket</th>
                        <th class="py-3.5 px-6 font-semibold">Peminjam</th>
                        <th class="py-3.5 px-6 font-semibold">Alat yang Dipinjam</th>
                        <th class="py-3.5 px-6 font-semibold">Jatuh Tempo</th>
                        <th class="py-3.5 px-6 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-6 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                    @forelse ($overdueLoans as $loan)
                        <tr class="hover:bg-surface-container-low transition-colors"
                            x-show="!searchQuery || '{{ strtolower($loan->ticket_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($loan->user->name ?? '') }}'.includes(searchQuery.toLowerCase())">
                            <td class="py-4 px-6 font-mono font-bold text-error">
                                {{ $loan->ticket_number }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-on-surface">{{ $loan->user->name ?? '-' }}</div>
                                <div class="font-caption-xs text-caption-xs text-on-surface-variant">NIM: {{ $loan->user->identity_number ?? '-' }}</div>
                                @if($loan->user->phone_number)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $loan->user->phone_number) }}" target="_blank" 
                                       class="inline-flex items-center gap-1 font-caption-xs text-caption-xs text-status-success-text hover:underline mt-0.5">
                                        <span class="material-symbols-outlined text-xs">chat</span>
                                        {{ $loan->user->phone_number }}
                                    </a>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-on-surface-variant">
                                @foreach ($loan->items as $item)
                                    <div>{{ $item->approved_quantity ?? $item->requested_quantity }}x {{ $item->tool->name ?? 'Alat' }}</div>
                                @endforeach
                            </td>
                            <td class="py-4 px-6 text-error font-semibold whitespace-nowrap">
                                {{ $loan->end_date?->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs bg-status-overdue-bg text-status-overdue-text border border-status-overdue-text/20">
                                    TERLAMBAT
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('admin.loans.review', $loan) }}" 
                                   class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md transition-colors shadow-sm">
                                    Proses Pengembalian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-status-success-bg text-status-success-text flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface font-bold">Tidak Ada Keterlambatan</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Semua alat yang sedang dipinjam masih dalam batas periode yang disetujui.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
