@extends('layouts.admin', ['title' => 'Tiket Peminjaman Admin', 'pageTitle' => 'Tiket Peminjaman'])

@section('content')
<div x-data="{
    searchQuery: '',
    selectedTab: '{{ request('status', 'all') }}',
    filterByTab(status) {
        this.selectedTab = status;
        const params = new URLSearchParams(window.location.search);
        if (status === 'all') {
            params.delete('status');
        } else {
            params.set('status', status);
        }
        const queryString = params.toString();
        window.location.href = '{{ route('admin.loans.index') }}' + (queryString ? '?' + queryString : '');
    }
}" class="space-y-6">
    <!-- Header Summary & Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">
                {{ request('view') === 'reports' ? 'Laporan & Riwayat Transaksi' : 'Antrean Tiket Peminjaman' }}
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant">
                {{ request('view') === 'reports' 
                    ? 'Rekapitulasi riwayat transaksi peminjaman, data peminjam terverifikasi, dan jejak waktu pengembalian.' 
                    : 'Kelola verifikasi permohonan, serah terima alat, dan pengembalian.' }}
            </p>
        </div>
        <div class="flex gap-2">
            @if(request('view') === 'reports')
                <a href="{{ route('admin.loans.index') }}" 
                   class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap px-4 py-2 bg-surface text-on-surface border border-outline-variant hover:bg-surface-container font-label-md text-label-md rounded-lg transition-all duration-150 shadow-xs active:scale-[0.98] cursor-pointer">
                    <span class="material-symbols-outlined text-sm">confirmation_number</span>
                    Lihat Antrean Tiket
                </a>
            @else
                <a href="{{ route('admin.loans.index', ['status' => 'OVERDUE']) }}" 
                   class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap px-4 py-2 bg-error-container text-error hover:bg-error hover:text-white font-label-md text-label-md rounded-lg transition-all duration-150 shadow-xs active:scale-[0.98] cursor-pointer">
                    <span class="material-symbols-outlined text-sm">warning</span>
                    Monitoring Overdue
                </a>
            @endif
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
                            :class="selectedTab === 'all' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-all duration-150 cursor-pointer active:scale-[0.98]">
                        Semua
                    </button>
                    <button @click="filterByTab('PENDING')" 
                            :class="selectedTab === 'PENDING' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap flex items-center gap-2 transition-colors">
                        Menunggu
                        @if(isset($pendingCount) && $pendingCount > 0)
                            <span class="bg-error text-on-error px-1.5 py-0.5 rounded-full text-[10px] font-bold leading-none">{{ $pendingCount }}</span>
                        @endif
                    </button>
                    <button @click="filterByTab('APPROVED')" 
                            :class="selectedTab === 'APPROVED' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-all duration-150 cursor-pointer active:scale-[0.98]">
                        Disetujui
                    </button>
                    <button @click="filterByTab('PARTIALLY_APPROVED')" 
                            :class="selectedTab === 'PARTIALLY_APPROVED' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-all duration-150 cursor-pointer active:scale-[0.98]">
                        Sebagian
                    </button>
                    <button @click="filterByTab('ON_LOAN')" 
                            :class="selectedTab === 'ON_LOAN' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-all duration-150 cursor-pointer active:scale-[0.98]">
                        Sedang Dipinjam
                    </button>
                    <button @click="filterByTab('OVERDUE')" 
                            :class="selectedTab === 'OVERDUE' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-all duration-150 cursor-pointer active:scale-[0.98]">
                        Terlambat
                    </button>
                    <button @click="filterByTab('RETURNED')" 
                            :class="selectedTab === 'RETURNED' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-all duration-150 cursor-pointer active:scale-[0.98]">
                        Dikembalikan
                    </button>
                    <button @click="filterByTab('REJECTED')" 
                            :class="selectedTab === 'REJECTED' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-all duration-150 cursor-pointer active:scale-[0.98]">
                        Ditolak
                    <button @click="filterByTab('CANCELLED')" 
                            :class="selectedTab === 'CANCELLED' ? 'bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-semibold shadow-xs ring-1 ring-inset ring-white/20' : 'bg-surface text-on-surface-variant hover:bg-surface-container border border-outline-variant active:bg-slate-100'"
                            class="px-4 py-2 rounded-full font-label-md text-label-md whitespace-nowrap transition-all duration-150 cursor-pointer active:scale-[0.98]">
                        Dibatalkan
                    </button>
                </nav>
            </div>

            <!-- Search Field -->
            <div class="flex items-center gap-3 w-full lg:w-auto shrink-0">
                <div class="relative w-full lg:w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                    <input x-model="searchQuery" 
                           class="w-full pl-10 pr-3 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-body-md text-body-md text-on-surface transition-colors" 
                           placeholder="Cari tiket / nama mahasiswa..." 
                           type="text"/>
                </div>
            </div>
        </div>

        <!-- Secondary Filter Toolbar: Date Range & Laboratory -->
        <div class="px-4 py-3 bg-surface-container-low border-b border-outline-variant flex flex-wrap items-center justify-between gap-3 text-sm">
            <form method="GET" action="{{ route('admin.loans.index') }}" class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}"/>
                @endif
                @if (request('view'))
                    <input type="hidden" name="view" value="{{ request('view') }}"/>
                @endif

                @if (auth()->user()->isKepalaLab())
                    <div class="relative w-full sm:w-56">
                        <select name="laboratory_id" onchange="this.form.submit()"
                                class="w-full h-9 pl-3 pr-8 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-xs text-on-surface transition-colors cursor-pointer">
                            <option value="">Semua Laboratorium</option>
                            @foreach ($laboratories as $lab)
                                <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-1.5 w-full sm:w-auto">
                    <span class="text-xs text-on-surface-variant font-medium">Rentang:</span>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" onchange="this.form.submit()"
                           title="Dari tanggal pengajuan"
                           class="h-9 px-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-xs font-mono focus:ring-2 focus:ring-blue-500"/>
                    <span class="text-xs text-on-surface-variant">s.d.</span>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" onchange="this.form.submit()"
                           title="Sampai tanggal pengajuan"
                           class="h-9 px-2.5 rounded-lg border border-outline-variant bg-surface-container-lowest text-xs font-mono focus:ring-2 focus:ring-blue-500"/>
                </div>

                @if (request()->hasAny(['laboratory_id', 'from_date', 'to_date']))
                    <a href="{{ route('admin.loans.index', array_filter(['status' => request('status'), 'view' => request('view')])) }}" 
                       class="text-xs font-semibold text-rose-600 hover:text-rose-700 hover:underline px-2 py-1">
                        Reset Filter
                    </a>
                @endif
            </form>

            <span class="text-xs text-on-surface-variant font-mono">
                Total: {{ $loans->total() }} Tiket
            </span>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto flex-1">
            <table class="min-w-full divide-y divide-outline-variant text-left">
                <thead class="bg-surface-container-low font-label-md text-label-md text-on-surface-variant">
                    <tr>
                        <th class="px-6 py-3.5 whitespace-nowrap" scope="col">No. Tiket</th>
                        <th class="px-6 py-3.5 whitespace-nowrap" scope="col">Nama Pengaju</th>
                        <th class="px-6 py-3.5 whitespace-nowrap" scope="col">Laboratorium</th>
                        <th class="px-6 py-3.5 min-w-[200px]" scope="col">Ringkasan Alat</th>
                        <th class="px-6 py-3.5 whitespace-nowrap" scope="col">Tanggal Pengajuan</th>
                        <th class="px-6 py-3.5 whitespace-nowrap" scope="col">Rentang Pinjam</th>
                        <th class="px-6 py-3.5 whitespace-nowrap text-center" scope="col">Status</th>
                        <th class="px-6 py-3.5 whitespace-nowrap text-right" scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant bg-surface-container-lowest font-body-md text-body-md text-on-surface">
                    @forelse ($loans as $loan)
                        <tr class="hover:bg-surface-container-low transition-colors group"
                            x-show="!searchQuery || '{{ strtolower($loan->ticket_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($loan->user->name ?? '') }}'.includes(searchQuery.toLowerCase())">
                            <!-- Ticket Number -->
                            <td class="px-6 py-3.5 whitespace-nowrap font-medium text-primary font-mono font-bold">
                                {{ $loan->ticket_number }}
                            </td>
                            <!-- Student Name & NIM -->
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-on-surface">{{ $loan->borrower_name ?: ($loan->user->name ?? '-') }}</div>
                                <span class="text-on-surface-variant font-caption-xs text-caption-xs font-mono">ID: {{ $loan->borrower_identity_number ?: ($loan->user->identity_number ?? '-') }}</span>
                            </td>
                            <!-- Laboratory -->
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-badge-xs text-badge-xs font-medium">
                                    <span class="material-symbols-outlined text-xs">science</span>
                                    {{ $loan->laboratory?->name ?? '-' }}
                                </span>
                            </td>
                            <!-- Tool Summary -->
                            <td class="px-6 py-3.5 text-sm text-on-surface-variant">
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
                            <td class="px-6 py-3.5 whitespace-nowrap text-on-surface-variant">
                                {{ $loan->created_at?->format('d M Y') }}
                            </td>
                            <!-- Loan Period -->
                            <td class="px-6 py-3.5 whitespace-nowrap font-medium {{ $loan->status === 'OVERDUE' ? 'text-error font-bold' : 'text-on-surface-variant' }}">
                                {{ $loan->start_date?->format('d M') }} - {{ $loan->end_date?->format('d M Y') }}
                            </td>
                            <!-- Status Badge -->
                            <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                @php
                                    $statusClasses = [
                                        'PENDING' => 'bg-status-pending-bg text-status-pending-text border-status-pending-text/20',
                                        'APPROVED' => 'bg-status-approved-bg text-status-approved-text border-status-approved-text/20',
                                        'PARTIALLY_APPROVED' => 'bg-status-partial-bg text-status-partial-text border-status-partial-text/20',
                                        'REJECTED' => 'bg-status-rejected-bg text-status-rejected-text border-status-rejected-text/20',
                                        'ON_LOAN' => 'bg-status-loan-bg text-status-loan-text border-status-loan-text/20',
                                        'OVERDUE' => 'bg-status-overdue-bg text-status-overdue-text border-status-overdue-text/20',
                                        'RETURNED' => 'bg-status-success-bg text-status-success-text border-status-success-text/20',
                                        'CANCELLED' => 'bg-slate-100 text-slate-700 border-slate-300',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs border {{ $statusClasses[$loan->status] ?? 'bg-surface-container text-on-surface' }}">
                                    {{ $loan->status }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-3.5 whitespace-nowrap text-right">
                                <a href="{{ route('admin.loans.review', $loan) }}" 
                                   class="inline-flex items-center justify-center gap-1 whitespace-nowrap px-3.5 py-1.5 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary rounded-lg font-label-md text-label-md shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">tune</span>
                                    Atur
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-on-surface-variant">
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
