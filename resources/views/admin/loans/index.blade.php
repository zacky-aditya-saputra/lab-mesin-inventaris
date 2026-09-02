@extends('layouts.admin', ['title' => 'Tiket Peminjaman Admin', 'pageTitle' => 'Tiket Peminjaman'])

@section('content')
<div x-data="{
    searchQuery: '',
    selectedTab: '{{ request('status', 'all') }}',
    filterByTab(status) {
        this.selectedTab = status;
        if (status === 'all') {
            window.location.href = '{{ route('loans.index') }}';
        } else {
            window.location.href = '{{ route('loans.index') }}?status=' + status;
        }
    }
}" class="space-y-6">
    <!-- Header Summary & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Antrean Tiket Peminjaman</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Kelola verifikasi permohonan, serah terima alat, dan pengembalian.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('loans.index', ['status' => 'OVERDUE']) }}" 
               class="px-4 py-2 bg-error-container text-error hover:bg-error hover:text-white font-label-md text-label-md rounded-lg transition-colors flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-sm">warning</span>
                Monitoring Overdue
            </a>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden flex flex-col">
        <!-- Toolbar: Tabs & Search -->
        <div class="p-4 border-b border-outline-variant flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-surface">
            <!-- Tabs Navigation -->
            <div class="overflow-x-auto no-scrollbar -mx-4 px-4 lg:mx-0 lg:px-0">
                <nav class="flex gap-2 min-w-max pb-1">
                    <button @click="filterByTab('all')" 
                            :class="selectedTab === 'all' ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-colors">
                        Semua
                    </button>
                    <button @click="filterByTab('PENDING')" 
                            :class="selectedTab === 'PENDING' ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap flex items-center gap-2 transition-colors">
                        Menunggu
                        @if(isset($pendingCount) && $pendingCount > 0)
                            <span class="bg-error text-on-error px-1.5 py-0.5 rounded-full text-[10px] font-bold leading-none">{{ $pendingCount }}</span>
                        @endif
                    </button>
                    <button @click="filterByTab('APPROVED')" 
                            :class="selectedTab === 'APPROVED' ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-colors">
                        Disetujui
                    </button>
                    <button @click="filterByTab('PARTIALLY_APPROVED')" 
                            :class="selectedTab === 'PARTIALLY_APPROVED' ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-colors">
                        Sebagian
                    </button>
                    <button @click="filterByTab('ON_LOAN')" 
                            :class="selectedTab === 'ON_LOAN' ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-colors">
                        Sedang Dipinjam
                    </button>
                    <button @click="filterByTab('OVERDUE')" 
                            :class="selectedTab === 'OVERDUE' ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-colors">
                        Terlambat
                    </button>
                    <button @click="filterByTab('RETURNED')" 
                            :class="selectedTab === 'RETURNED' ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-colors">
                        Dikembalikan
                    </button>
                    <button @click="filterByTab('REJECTED')" 
                            :class="selectedTab === 'REJECTED' ? 'bg-primary text-on-primary font-semibold shadow-sm' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-colors">
                        Ditolak
                    </button>
                </nav>
            </div>

            <!-- Search Field -->
            <div class="flex items-center gap-3 w-full lg:w-auto shrink-0">
                <div class="relative w-full lg:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input x-model="searchQuery" 
                           class="w-full pl-10 pr-3 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:border-primary font-body-md text-body-md text-on-surface transition-colors" 
                           placeholder="Cari tiket / nama mahasiswa..." 
                           type="text"/>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto flex-1">
            <table class="min-w-full divide-y divide-outline-variant text-left">
                <thead class="bg-surface-container-low font-label-md text-label-md text-on-surface-variant">
                    <tr>
                        <th class="px-6 py-4 whitespace-nowrap" scope="col">No. Tiket</th>
                        <th class="px-6 py-4 whitespace-nowrap" scope="col">Nama Pengaju</th>
                        <th class="px-6 py-4 min-w-[200px]" scope="col">Ringkasan Alat</th>
                        <th class="px-6 py-4 whitespace-nowrap" scope="col">Tanggal Pengajuan</th>
                        <th class="px-6 py-4 whitespace-nowrap" scope="col">Rentang Pinjam</th>
                        <th class="px-6 py-4 whitespace-nowrap text-center" scope="col">Status</th>
                        <th class="px-6 py-4 whitespace-nowrap text-right" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant bg-surface-container-lowest font-body-md text-body-md text-on-surface">
                    @forelse ($loans as $loan)
                        <tr class="hover:bg-surface-container-low transition-colors group"
                            x-show="!searchQuery || '{{ strtolower($loan->ticket_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($loan->user->name ?? '') }}'.includes(searchQuery.toLowerCase())">
                            <!-- Ticket Number -->
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-primary font-mono font-bold">
                                {{ $loan->ticket_number }}
                            </td>
                            <!-- Student Name & NIM -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-on-surface">{{ $loan->user->name ?? '-' }}</div>
                                <span class="text-on-surface-variant font-caption-xs text-caption-xs">NIM: {{ $loan->user->identity_number ?? '-' }}</span>
                            </td>
                            <!-- Tool Summary -->
                            <td class="px-6 py-4 text-sm text-on-surface-variant">
                                @if($loan->items->isNotEmpty())
                                    <span class="font-medium text-on-surface">{{ $loan->items->first()->requested_quantity }}x {{ $loan->items->first()->tool->name ?? 'Alat' }}</span>
                                    @if($loan->items->count() > 1)
                                        <span class="text-secondary font-medium block text-xs">+{{ $loan->items->count() - 1 }} item lainnya</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <!-- Submission Date -->
                            <td class="px-6 py-4 whitespace-nowrap text-on-surface-variant">
                                {{ $loan->created_at?->format('d M Y') }}
                            </td>
                            <!-- Loan Period -->
                            <td class="px-6 py-4 whitespace-nowrap font-medium {{ $loan->status === 'OVERDUE' ? 'text-error font-bold' : 'text-on-surface-variant' }}">
                                {{ $loan->start_date?->format('d M') }} - {{ $loan->end_date?->format('d M Y') }}
                            </td>
                            <!-- Status Badge -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
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
                            <!-- Actions -->
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('loans.review', $loan) }}" 
                                   class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md transition-colors shadow-sm">
                                    <span class="material-symbols-outlined text-sm">tune</span>
                                    Atur
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl text-outline">confirmation_number</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface">Tidak ada tiket peminjaman</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Belum ada data tiket untuk kategori status ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($loans->hasPages())
            <div class="p-4 border-t border-outline-variant bg-surface">
                {{ $loans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
