@extends('layouts.admin', ['title' => 'Laporan & Riwayat Transaksi', 'pageTitle' => 'Laporan & Riwayat'])

@section('content')
<div class="space-y-6" x-data="{ searchQuery: '' }">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Laporan &amp; Riwayat Transaksi</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Arsip historis seluruh transaksi peminjaman, pelacakan kondisi alat, dan audit trail laboratorium.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()" 
                    class="bg-surface-container-lowest border border-outline-variant text-on-surface px-4 py-2 rounded-lg font-label-md text-label-md hover:bg-surface-container transition-colors flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-base">print</span>
                Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden flex flex-col">
        <!-- Filter Controls -->
        <div class="p-4 border-b border-outline-variant bg-surface-container-low/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="relative w-full sm:w-80">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
                <input x-model="searchQuery" 
                       class="w-full pl-10 pr-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest font-body-md text-body-md text-on-surface focus:ring-1 focus:ring-primary focus:border-primary" 
                       placeholder="Cari no. tiket, nama mahasiswa..." 
                       type="text"/>
            </div>
            <div class="font-caption-xs text-caption-xs text-on-surface-variant">
                Total {{ $transactions->total() ?? $transactions->count() }} catatan transaksi
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-full">
                <thead class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                    <tr>
                        <th class="py-3.5 px-6 font-semibold">No. Tiket</th>
                        <th class="py-3.5 px-6 font-semibold">Peminjam</th>
                        <th class="py-3.5 px-6 font-semibold">Item &amp; Jumlah</th>
                        <th class="py-3.5 px-6 font-semibold">Tanggal Pinjam</th>
                        <th class="py-3.5 px-6 font-semibold text-center">Status Akhir</th>
                        <th class="py-3.5 px-6 font-semibold text-center">Kondisi Pengembalian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                    @forelse ($transactions as $tx)
                        <tr class="hover:bg-surface-container-low transition-colors"
                            x-show="!searchQuery || '{{ strtolower($tx->ticket_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($tx->user->name ?? '') }}'.includes(searchQuery.toLowerCase())">
                            <td class="py-4 px-6 font-mono font-bold text-primary">
                                {{ $tx->ticket_number }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-on-surface">{{ $tx->user->name ?? '-' }}</div>
                                <div class="font-caption-xs text-caption-xs text-on-surface-variant">NIM: {{ $tx->user->identity_number ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-6 text-on-surface-variant">
                                @foreach ($tx->items as $item)
                                    <div>{{ $item->approved_quantity ?? $item->requested_quantity }}x {{ $item->tool->name ?? 'Alat' }}</div>
                                @endforeach
                            </td>
                            <td class="py-4 px-6 text-on-surface-variant whitespace-nowrap">
                                {{ $tx->start_date?->format('d M Y') }} s.d. {{ $tx->end_date?->format('d M Y') }}
                            </td>
                            <td class="py-4 px-6 text-center">
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs border {{ $statusClasses[$tx->status] ?? 'bg-surface-container text-on-surface' }}">
                                    {{ $tx->status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @php
                                    $hasDamaged = $tx->items->contains('return_condition', 'Rusak');
                                    $hasLost = $tx->items->contains('return_condition', 'Hilang');
                                    $hasGood = $tx->items->contains('return_condition', 'Baik');
                                @endphp
                                @if($hasLost)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs bg-error text-white font-semibold">
                                        <span class="material-symbols-outlined text-xs">error</span> Hilang
                                    </span>
                                @elseif($hasDamaged)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs bg-status-partial-bg text-status-partial-text border border-status-partial-text/20 font-semibold">
                                        <span class="material-symbols-outlined text-xs">warning</span> Rusak
                                    </span>
                                @elseif($hasGood)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs bg-status-success-bg text-status-success-text border border-status-success-text/20 font-semibold">
                                        <span class="material-symbols-outlined text-xs">check</span> Baik
                                    </span>
                                @else
                                    <span class="text-outline text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl text-outline">history</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface font-bold">Belum Ada Riwayat Transaksi</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Seluruh arsip peminjaman yang selesai akan terdata di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($transactions->hasPages())
            <div class="p-4 border-t border-outline-variant bg-surface">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
