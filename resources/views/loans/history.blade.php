@extends('layouts.catalog', ['title' => 'Riwayat Pengajuan Saya'])

@section('content')
<div class="py-10 px-margin-page md:px-gutter-md max-w-container-max mx-auto w-full flex flex-col gap-6"
     x-data="{
         searchQuery: '',
         expandedTicket: null,
         toggleTicket(id) {
             this.expandedTicket = this.expandedTicket === id ? null : id;
         }
     }">
    <!-- Page Header -->
    <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2 font-bold">Riwayat Pengajuan Saya</h1>
            <p class="text-on-surface-variant font-body-md text-body-md max-w-2xl">
                Pantau status pengajuan peminjaman alat laboratorium Anda secara transparan. Klik pada baris tiket untuk melihat rincian item alat dan catatan dari admin/teknisi.
            </p>
        </div>
        <a href="{{ route('loans.create') }}" 
           class="px-5 py-2.5 bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md rounded-lg shadow-sm transition-colors flex items-center gap-2 flex-shrink-0">
            <span class="material-symbols-outlined text-base">add</span>
            Ajukan Peminjaman Baru
        </a>
    </header>

    <!-- Stats & Search Row -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center bg-surface-container-lowest p-4 rounded-xl border border-outline-variant shadow-sm">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-primary"></span>
                <span class="font-label-md text-label-md text-on-surface">{{ $loanRequests->count() }} Total Pengajuan</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-status-loan-text"></span>
                <span class="font-label-md text-label-md text-on-surface">{{ $loanRequests->where('status', 'ON_LOAN')->count() }} Aktif Dipinjam</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-status-pending-text"></span>
                <span class="font-label-md text-label-md text-on-surface">{{ $loanRequests->where('status', 'PENDING')->count() }} Menunggu</span>
            </div>
        </div>
        <div class="relative w-full sm:w-72">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-sm">search</span>
            <input x-model="searchQuery" 
                   class="w-full pl-9 pr-3 py-2 bg-surface rounded-lg border border-outline-variant focus:border-primary focus:ring-1 focus:ring-primary font-body-md text-body-md transition-colors" 
                   placeholder="Cari nomor tiket / nama alat..." 
                   type="text"/>
        </div>
    </div>

    <!-- Table List Container -->
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden flex flex-col">
        <!-- Table Header (Desktop) -->
        <div class="hidden md:grid grid-cols-12 gap-4 p-4 bg-surface-container-low border-b border-outline-variant font-label-md text-label-md text-on-surface font-semibold items-center">
            <div class="col-span-3">No. Tiket &amp; Tanggal</div>
            <div class="col-span-4">Ringkasan Alat</div>
            <div class="col-span-3">Periode Pinjam</div>
            <div class="col-span-2 text-right">Status</div>
        </div>

        <!-- List Items (Accordion) -->
        <div class="divide-y divide-outline-variant">
            @forelse ($loanRequests as $loan)
                <div class="hover:bg-surface-container-low/40 transition-colors"
                     x-show="!searchQuery || '{{ strtolower($loan->ticket_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($loan->purpose) }}'.includes(searchQuery.toLowerCase())">
                    <!-- Ticket Clickable Header Row -->
                    <div class="p-4 cursor-pointer grid grid-cols-1 md:grid-cols-12 gap-4 items-start md:items-center"
                         @click="toggleTicket({{ $loan->id }})">
                        <!-- Ticket Number & Date -->
                        <div class="col-span-1 md:col-span-3 flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span class="font-title-sm text-title-sm text-on-surface font-bold font-mono">{{ $loan->ticket_number }}</span>
                                @if($loan->status === 'PENDING')
                                    <span class="w-2 h-2 rounded-full bg-status-pending-text" title="Menunggu Verifikasi"></span>
                                @elseif($loan->status === 'OVERDUE')
                                    <span class="w-2 h-2 rounded-full bg-status-overdue-bg animate-ping" title="Terlambat"></span>
                                @endif
                            </div>
                            <span class="font-caption-xs text-caption-xs text-on-surface-variant">
                                Diajukan: {{ $loan->created_at?->format('d M Y, H:i') }}
                            </span>
                        </div>

                        <!-- Summary Alat -->
                        <div class="col-span-1 md:col-span-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-outline">science</span>
                            <span class="font-body-md text-body-md text-on-surface truncate">
                                @if($loan->items->isNotEmpty())
                                    {{ $loan->items->first()->tool->name ?? 'Alat' }}
                                    @if($loan->items->count() > 1)
                                        <span class="text-secondary font-medium">+{{ $loan->items->count() - 1 }} lainnya</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </span>
                        </div>

                        <!-- Date Range -->
                        <div class="col-span-1 md:col-span-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-outline text-sm">calendar_today</span>
                            <span class="font-body-md text-body-md text-on-surface-variant">
                                {{ $loan->start_date?->format('d M') }} - {{ $loan->end_date?->format('d M Y') }}
                            </span>
                        </div>

                        <!-- Status & Toggle Icon -->
                        <div class="col-span-1 md:col-span-2 flex justify-between md:justify-end items-center gap-3">
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
                            <span class="px-2.5 py-1 rounded-full font-badge-xs text-badge-xs border whitespace-nowrap {{ $statusClasses[$loan->status] ?? 'bg-surface-container text-on-surface' }}">
                                {{ $loan->status }}
                            </span>
                            <span class="material-symbols-outlined text-on-surface-variant transition-transform duration-200"
                                  :class="expandedTicket === {{ $loan->id }} ? 'rotate-180' : ''">
                                expand_more
                            </span>
                        </div>
                    </div>

                    <!-- Accordion Expanded Details -->
                    <div x-show="expandedTicket === {{ $loan->id }}" x-transition class="bg-surface-container-low px-6 py-5 border-t border-outline-variant/60">
                        <div class="space-y-4">
                            <!-- Keperluan & Admin Notes -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-surface-container-lowest p-4 rounded-lg border border-outline-variant">
                                <div>
                                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant font-medium">Tujuan / Keperluan:</span>
                                    <p class="font-body-md text-body-md text-on-surface mt-1">{{ $loan->purpose }}</p>
                                </div>
                                <div>
                                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant font-medium">Catatan Petugas Lab:</span>
                                    <p class="font-body-md text-body-md mt-1 {{ $loan->admin_notes ? 'text-on-surface italic' : 'text-outline italic' }}">
                                        {{ $loan->admin_notes ?: 'Belum ada catatan verifikasi.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Item Detail Table -->
                            <div>
                                <h4 class="font-label-md text-label-md text-on-surface mb-2 font-bold">Rincian Item Alat</h4>
                                <div class="overflow-x-auto rounded-lg border border-outline-variant bg-surface-container-lowest">
                                    <table class="w-full text-left font-body-md text-body-md">
                                        <thead>
                                            <tr class="bg-surface-container border-b border-outline-variant text-on-surface font-label-md text-label-md">
                                                <th class="py-2.5 px-4">Nama Alat</th>
                                                <th class="py-2.5 px-4 font-mono text-xs">Kode</th>
                                                <th class="py-2.5 px-4 text-center">Jml Diajukan</th>
                                                <th class="py-2.5 px-4 text-center">Jml Disetujui</th>
                                                <th class="py-2.5 px-4 text-center">Kondisi Kembali</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-outline-variant/40 text-on-surface">
                                            @foreach ($loan->items as $item)
                                                <tr>
                                                    <td class="py-2.5 px-4 font-medium">{{ $item->tool->name ?? 'Alat' }}</td>
                                                    <td class="py-2.5 px-4 font-mono text-xs text-on-surface-variant">{{ $item->tool->code ?? '-' }}</td>
                                                    <td class="py-2.5 px-4 text-center">{{ $item->requested_quantity }}</td>
                                                    <td class="py-2.5 px-4 text-center font-semibold {{ $item->approved_quantity > 0 ? 'text-status-approved-text' : 'text-outline' }}">
                                                        {{ $item->approved_quantity ?? '-' }}
                                                    </td>
                                                    <td class="py-2.5 px-4 text-center">
                                                        @if($item->return_condition)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded font-badge-xs text-badge-xs font-semibold
                                                                  {{ $item->return_condition === 'Baik' ? 'bg-status-success-bg text-status-success-text' : 'bg-error-container text-error' }}">
                                                                {{ $item->return_condition }}
                                                            </span>
                                                        @else
                                                            <span class="text-outline text-xs">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-on-surface-variant">
                    <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                        <span class="material-symbols-outlined text-3xl text-outline">receipt_long</span>
                    </div>
                    <h3 class="font-title-sm text-title-sm text-on-surface font-bold">Belum Ada Riwayat Pengajuan</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1 max-w-sm mx-auto">
                        Anda belum pernah membuat pengajuan peminjaman alat. Klik tombol di bawah untuk membuat pengajuan baru.
                    </p>
                    <a href="{{ route('loans.create') }}" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-primary text-on-primary rounded-lg font-label-md text-label-md hover:bg-primary-container transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-sm">add</span>
                        Ajukan Peminjaman Sekarang
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if (method_exists($loanRequests, 'hasPages') && $loanRequests->hasPages())
            <div class="p-4 border-t border-outline-variant bg-surface-container-lowest">
                {{ $loanRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
