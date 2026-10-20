@extends('layouts.catalog', ['title' => 'Riwayat Pengajuan Saya'])

@section('content')
<div class="py-8 px-4 sm:px-6 md:px-8 max-w-7xl mx-auto w-full flex flex-col gap-8"
     x-data="{
         searchQuery: '',
         currentFilter: 'all',
         expandedTicket: {{ $loanRequests->first()?->id ?? 'null' }},
         toggleTicket(id) {
             this.expandedTicket = this.expandedTicket === id ? null : id;
         },
         matchesFilter(status) {
             if (this.currentFilter === 'all') return true;
             if (this.currentFilter === 'menunggu') return status === 'PENDING';
             if (this.currentFilter === 'dipinjam') return status === 'ON_LOAN';
             if (this.currentFilter === 'terlambat') return status === 'OVERDUE';
             if (this.currentFilter === 'disetujui') return status === 'APPROVED' || status === 'PARTIALLY_APPROVED';
             if (this.currentFilter === 'selesai') return status === 'RETURNED';
             if (this.currentFilter === 'dibatalkan') return status === 'CANCELLED' || status === 'REJECTED';
             return true;
         }
     }">

    @php
        $totalLoansCount = method_exists($loanRequests, 'total') ? $loanRequests->total() : $loanRequests->count();
        $onLoanCount = $loanRequests->where('status', 'ON_LOAN')->count();
        $pendingCount = $loanRequests->where('status', 'PENDING')->count();
        $overdueCount = $loanRequests->where('status', 'OVERDUE')->count();
        $approvedCount = $loanRequests->whereIn('status', ['APPROVED', 'PARTIALLY_APPROVED'])->count();
        $returnedCount = $loanRequests->where('status', 'RETURNED')->count();
        $cancelledCount = $loanRequests->whereIn('status', ['CANCELLED', 'REJECTED'])->count();

        $statusConfig = [
            'PENDING' => [
                'label' => 'Menunggu Verifikasi',
                'pill' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500 animate-pulse',
                'icon' => 'hourglass_top',
            ],
            'APPROVED' => [
                'label' => 'Disetujui',
                'pill' => 'bg-sky-50 text-sky-700 border-sky-200',
                'dot' => 'bg-sky-600',
                'icon' => 'check_circle',
            ],
            'PARTIALLY_APPROVED' => [
                'label' => 'Disetujui Sebagian',
                'pill' => 'bg-purple-50 text-purple-700 border-purple-200',
                'dot' => 'bg-purple-600',
                'icon' => 'rule',
            ],
            'ON_LOAN' => [
                'label' => 'Sedang Dipinjam',
                'pill' => 'bg-blue-50 text-blue-700 border-blue-200',
                'dot' => 'bg-blue-600',
                'icon' => 'handyman',
            ],
            'OVERDUE' => [
                'label' => 'Terlambat',
                'pill' => 'bg-rose-50 text-rose-700 border-rose-200 font-bold',
                'dot' => 'bg-rose-600 animate-ping',
                'icon' => 'report_problem',
            ],
            'RETURNED' => [
                'label' => 'Selesai Dikembalikan',
                'pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-600',
                'icon' => 'verified',
            ],
            'CANCELLED' => [
                'label' => 'Dibatalkan',
                'pill' => 'bg-slate-100 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-400',
                'icon' => 'cancel',
            ],
            'REJECTED' => [
                'label' => 'Ditolak',
                'pill' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'icon' => 'cancel',
            ],
        ];
    @endphp

    <!-- Top Bar: Header & Action Button -->
    <header class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Riwayat Pengajuan Saya</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Pantau status verifikasi tiket peminjaman peralatan laboratorium secara transparan. Tunjukkan bukti tiket resmi saat pengambilan unit di laboratorium terkait.
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('loans.create') }}"
               class="rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-6 py-2.5 shadow-xs hover:shadow-sm inline-flex items-center gap-2 transition-all active:scale-95 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Ajukan Peminjaman Baru</span>
            </a>
        </div>
    </header>

    <!-- Summary Metric Cards (Clean industrial white with refined borders - Anti AI Slop) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Card 1: Total Pengajuan -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pengajuan</span>
                <span class="font-mono text-2xl sm:text-3xl font-bold text-slate-900 mt-1 block">{{ $totalLoansCount }}</span>
                <span class="text-xs text-slate-400 mt-0.5 block">Seluruh riwayat tiket saya</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">receipt_long</span>
            </div>
        </div>

        <!-- Card 2: Sedang Dipinjam -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Dipinjam</span>
                <span class="font-mono text-2xl sm:text-3xl font-bold text-slate-900 mt-1 block">{{ $onLoanCount }}</span>
                <span class="text-xs text-slate-400 mt-0.5 block">Alat aktif di tangan Anda</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">handyman</span>
            </div>
        </div>

        <!-- Card 3: Menunggu Verifikasi -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">Menunggu Verifikasi</span>
                <span class="font-mono text-2xl sm:text-3xl font-bold text-slate-900 mt-1 block">{{ $pendingCount }}</span>
                <span class="text-xs text-slate-400 mt-0.5 block">Dalam antrean verifikasi laboran</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl">hourglass_top</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-1">
        <!-- Search Input -->
        <div class="relative w-full lg:w-96 shrink-0">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
            <input x-model="searchQuery"
                   id="ticket-search"
                   type="text"
                   class="w-full bg-white border border-slate-200 rounded-xl pl-11 pr-4 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-xs transition-all"
                   placeholder="Cari tiket (LOAN-...), alat, atau keperluan..." />
        </div>

        <!-- Horizontal Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1.5 lg:pb-0 scrollbar-none" id="status-filters">
            <button type="button"
                    @click="currentFilter = 'all'"
                    :class="currentFilter === 'all' ? 'bg-slate-900 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 font-medium'"
                    class="rounded-full px-4 py-1.5 text-xs shrink-0 transition-colors cursor-pointer">
                Semua ({{ $totalLoansCount }})
            </button>

            <button type="button"
                    @click="currentFilter = 'menunggu'"
                    :class="currentFilter === 'menunggu' ? 'bg-slate-900 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 font-medium'"
                    class="rounded-full px-3.5 py-1.5 text-xs shrink-0 transition-colors cursor-pointer">
                Menunggu ({{ $pendingCount }})
            </button>

            <button type="button"
                    @click="currentFilter = 'dipinjam'"
                    :class="currentFilter === 'dipinjam' ? 'bg-slate-900 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 font-medium'"
                    class="rounded-full px-3.5 py-1.5 text-xs shrink-0 transition-colors cursor-pointer">
                Sedang Dipinjam ({{ $onLoanCount }})
            </button>

            <button type="button"
                    @click="currentFilter = 'terlambat'"
                    :class="currentFilter === 'terlambat' ? 'bg-slate-900 text-white shadow-xs font-semibold' : 'bg-white text-rose-600 border border-rose-200 hover:bg-rose-50 font-medium'"
                    class="rounded-full px-3.5 py-1.5 text-xs shrink-0 transition-colors flex items-center gap-1.5 cursor-pointer">
                @if($overdueCount > 0)
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                @endif
                Terlambat ({{ $overdueCount }})
            </button>

            <button type="button"
                    @click="currentFilter = 'disetujui'"
                    :class="currentFilter === 'disetujui' ? 'bg-slate-900 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 font-medium'"
                    class="rounded-full px-3.5 py-1.5 text-xs shrink-0 transition-colors cursor-pointer">
                Disetujui ({{ $approvedCount }})
            </button>

            <button type="button"
                    @click="currentFilter = 'selesai'"
                    :class="currentFilter === 'selesai' ? 'bg-slate-900 text-white shadow-xs font-semibold' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 font-medium'"
                    class="rounded-full px-3.5 py-1.5 text-xs shrink-0 transition-colors cursor-pointer">
                Selesai ({{ $returnedCount }})
            </button>

            <button type="button"
                    @click="currentFilter = 'dibatalkan'"
                    :class="currentFilter === 'dibatalkan' ? 'bg-slate-900 text-white shadow-xs font-semibold' : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-300 font-medium'"
                    class="rounded-full px-3.5 py-1.5 text-xs shrink-0 transition-colors cursor-pointer">
                Dibatalkan ({{ $cancelledCount }})
            </button>
        </div>
    </div>

    <!-- Ticket Accordion Container -->
    <div class="flex flex-col gap-4" id="ticket-list-container">
        @forelse ($loanRequests as $loan)
            @php
                $statusInfo = $statusConfig[$loan->status] ?? [
                    'label' => $loan->status,
                    'pill' => 'bg-slate-100 text-slate-700 border-slate-200',
                    'dot' => 'bg-slate-400',
                    'icon' => 'info',
                ];

                $labName = $loan->laboratory->name ?? ($loan->items->first()?->tool?->laboratory?->name ?? 'Lab Teknik Mesin');
                $labLocation = $loan->laboratory->location ?? ($loan->items->first()?->tool?->laboratory?->location ?? 'Gedung D');
                $toolsSummary = $loan->items->map(fn($item) => ($item->tool->name ?? 'Alat') . ' [' . $item->requested_quantity . ' Unit]')->join(', ');
            @endphp

            <div class="ticket-card bg-white border {{ $loan->status === 'OVERDUE' ? 'border-rose-200 hover:border-rose-300' : 'border-slate-200 hover:border-slate-300' }} rounded-2xl shadow-xs overflow-hidden transition-all duration-200"
                 x-show="matchesFilter('{{ $loan->status }}') && (!searchQuery || '{{ strtolower($loan->ticket_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($loan->purpose)) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($loan->items->pluck('tool.name')->join(' '))) }}'.includes(searchQuery.toLowerCase()))">

                <!-- Ticket Card Header (Clickable Accordion Toggle) -->
                <div class="p-5 sm:p-6 cursor-pointer select-none flex flex-col md:flex-row md:items-center justify-between gap-4"
                     @click="toggleTicket({{ $loan->id }})">
                    <div class="flex flex-col gap-2">
                        <!-- Top Meta: Ticket Number, Status Badge, Lab Info -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="font-mono font-bold text-slate-900 text-xs sm:text-sm tracking-wide bg-slate-100 px-2.5 py-1 rounded-md flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-blue-600">confirmation_number</span>
                                {{ $loan->ticket_number }}
                            </span>

                            <span class="border font-semibold px-3 py-0.5 rounded-full text-xs flex items-center gap-1.5 whitespace-nowrap {{ $statusInfo['pill'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusInfo['dot'] }}"></span>
                                {{ $statusInfo['label'] }}
                            </span>

                            <span class="text-xs text-slate-300 hidden sm:inline">•</span>

                            <span class="text-xs font-medium text-slate-600 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-slate-400">domain</span>
                                {{ $labName }} &middot; {{ $labLocation }}
                            </span>
                        </div>

                        <!-- Mid Meta: Purpose & Date Range -->
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 sm:gap-4 text-xs text-slate-600">
                            <span class="font-semibold text-slate-800 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px] text-slate-500">assignment</span>
                                {{ $loan->purpose }}
                            </span>

                            <span class="text-slate-300 hidden sm:inline">|</span>

                            <span class="text-slate-500 flex items-center gap-1 font-mono text-xs">
                                <span class="material-symbols-outlined text-[15px] text-slate-400">calendar_month</span>
                                {{ $loan->start_date?->translatedFormat('d M Y') }} &ndash; {{ $loan->end_date?->translatedFormat('d M Y') }}
                            </span>
                        </div>

                        <!-- Summary of Tools -->
                        <div class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
                            <span class="material-symbols-outlined text-[15px] text-slate-400">inventory_2</span>
                            <span class="truncate max-w-xl">{{ $toolsSummary ?: 'Belum ada item' }}</span>
                        </div>
                    </div>

                    <!-- Right Meta: Item Badge, Cancel Quick Action, Chevron -->
                    <div class="flex items-center justify-between md:justify-end gap-3 pt-2 md:pt-0 border-t border-slate-100 md:border-0 shrink-0">
                        <span class="font-mono text-xs text-blue-700 font-semibold bg-blue-50 px-2.5 py-1 rounded-lg">
                            {{ $loan->items->count() }} Item Instrumen
                        </span>

                        @if($loan->status === 'PENDING')
                            <form method="POST" action="{{ route('loans.cancel', $loan) }}"
                                  @click.stop
                                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan tiket ini?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="text-rose-600 hover:bg-rose-50 border border-rose-200 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1 cursor-pointer">
                                    <span class="material-symbols-outlined text-[15px]">close</span>
                                    Batalkan Pengajuan
                                </button>
                            </form>
                        @endif

                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center transition-transform duration-200"
                             :class="expandedTicket === {{ $loan->id }} ? 'rotate-180 text-blue-600 bg-blue-50' : ''">
                            <span class="material-symbols-outlined text-[20px]">expand_more</span>
                        </div>
                    </div>
                </div>

                <!-- Expanded Body (Accordion Content) -->
                <div x-show="expandedTicket === {{ $loan->id }}"
                     x-transition
                     class="bg-slate-50/70 p-5 sm:p-6 border-t border-slate-100 flex flex-col gap-5">

                    <!-- Special Alert Overdue -->
                    @if($loan->status === 'OVERDUE')
                        <div class="bg-white border border-rose-200 rounded-xl p-4 flex items-start gap-3 shadow-xs">
                            <span class="material-symbols-outlined text-rose-600 text-[22px] shrink-0 mt-0.5">warning</span>
                            <div class="flex flex-col gap-1 text-xs">
                                <span class="font-bold text-rose-900">Perhatian: Alat Melewati Batas Maksimal Peminjaman</span>
                                <p class="text-slate-600 leading-relaxed">
                                    Peralatan belum tercatat dikembalikan di loket inventaris laboratorium. Sesuai regulasi, keterlambatan dikenakan penalti administratif dan penangguhan akses laboratorium hingga unit diserahkan dalam kondisi lengkap.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Special Alert Pending -->
                    @if($loan->status === 'PENDING')
                        <div class="bg-amber-50/80 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                            <span class="material-symbols-outlined text-amber-600 text-[20px] shrink-0 mt-0.5">info</span>
                            <div class="text-xs text-amber-900 leading-relaxed">
                                <strong>Permohonan dalam antrean tinjauan laboran.</strong> Petugas laboratorium sedang memeriksa ketersediaan jadwal dan stok unit fisik agar tidak berbenturan dengan agenda praktikum mahasiswa.
                            </div>
                        </div>
                    @endif

                    <!-- Verifier Notes Box -->
                    @if($loan->admin_notes)
                        <div class="bg-white border border-slate-200 rounded-xl p-4 flex items-start gap-3.5 shadow-2xs">
                            <div class="w-9 h-9 rounded-full bg-slate-800 text-white font-mono font-bold text-xs flex items-center justify-center shrink-0">
                                {{ $loan->approver ? Str::upper(substr($loan->approver->name, 0, 2)) : 'LB' }}
                            </div>
                            <div class="flex flex-col gap-1 w-full">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-slate-900">
                                        {{ $loan->approver->name ?? 'Petugas Laboran' }}
                                        <span class="font-normal text-slate-500">(Laboratorium)</span>
                                    </span>
                                    @if($loan->approved_at)
                                        <span class="text-[11px] text-slate-400">Diverifikasi pada {{ $loan->approved_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                    &ldquo;{{ $loan->admin_notes }}&rdquo;
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Purpose & Notes Summary -->
                    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs">
                        <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Keperluan Lengkap:</span>
                        <p class="text-xs sm:text-sm text-slate-900 leading-relaxed">{{ $loan->purpose }}</p>
                    </div>

                    <!-- Attached Documents -->
                    @if($loan->loan_letter_path || $loan->identity_card_path)
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider mr-1">Dokumen Lampiran:</span>
                            @if($loan->loan_letter_path)
                                <a href="{{ route('documents.loan-letter', $loan) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 shadow-xs active:scale-[0.98] transition-all">
                                    <span class="material-symbols-outlined text-sm text-blue-600">description</span>
                                    Surat Peminjaman Resmi
                                    <span class="material-symbols-outlined text-xs text-slate-400">open_in_new</span>
                                </a>
                            @endif
                            @if($loan->identity_card_path)
                                <a href="{{ route('documents.identity', auth()->user()) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 shadow-xs active:scale-[0.98] transition-all">
                                    <span class="material-symbols-outlined text-sm text-blue-600">badge</span>
                                    KTM / KTP Peminjam
                                    <span class="material-symbols-outlined text-xs text-slate-400">open_in_new</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Equipment Table & Kondisi Fisik -->
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                        <div class="px-4 py-3 bg-slate-100/60 border-b border-slate-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Peralatan &amp; Kondisi Fisik</span>
                            <span class="text-xs text-slate-500 font-mono">{{ $loan->items->count() }} Unit Terdata</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[560px]">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700 uppercase tracking-wider">
                                        <th class="py-2.5 px-4">Nama Alat</th>
                                        <th class="py-2.5 px-4 font-mono text-xs">Kode Inventaris</th>
                                        <th class="py-2.5 px-4 text-center">Diajukan</th>
                                        <th class="py-2.5 px-4 text-center">Disetujui</th>
                                        <th class="py-2.5 px-4 text-center">Status Item</th>
                                        <th class="py-2.5 px-4 text-center">Hasil Pengembalian</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                                    @foreach ($loan->items as $item)
                                        <tr class="hover:bg-slate-50/60 transition-colors">
                                            <td class="py-2.5 px-4 font-semibold text-slate-900">
                                                <div>{{ $item->tool->name ?? 'Alat Laboratorium' }}</div>
                                                @if($item->rejection_reason)
                                                    <div class="text-xs text-rose-600 font-medium mt-1 flex items-center gap-1">
                                                        <span class="material-symbols-outlined text-[13px]">cancel</span>
                                                        <span>Alasan: {{ $item->rejection_reason }}</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-4 font-mono text-xs text-slate-500">{{ $item->tool->code ?? '-' }}</td>
                                            <td class="py-2.5 px-4 text-center font-mono font-medium">{{ $item->requested_quantity }} Unit</td>
                                            <td class="py-2.5 px-4 text-center font-mono font-bold {{ $item->approved_quantity > 0 ? 'text-blue-600' : 'text-slate-400' }}">
                                                {{ $item->approved_quantity !== null ? $item->approved_quantity . ' Unit' : '-' }}
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                @if($item->status === 'APPROVED')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Disetujui
                                                    </span>
                                                @elseif($item->status === 'REJECTED')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                        Ditolak
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                        Menunggu
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                @if($item->returned_good_qty > 0 || $item->returned_damaged_qty > 0 || $item->returned_missing_qty > 0)
                                                    <div class="flex flex-wrap items-center justify-center gap-1 font-mono text-xs">
                                                        @if($item->returned_good_qty > 0)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold" title="Kondisi Bagus">
                                                                {{ $item->returned_good_qty }} Bagus
                                                            </span>
                                                        @endif
                                                        @if($item->returned_damaged_qty > 0)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 font-semibold" title="Kondisi Rusak">
                                                                {{ $item->returned_damaged_qty }} Rusak
                                                            </span>
                                                        @endif
                                                        @if($item->returned_missing_qty > 0)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 font-semibold" title="Unit Hilang">
                                                                {{ $item->returned_missing_qty }} Hilang
                                                            </span>
                                                        @endif
                                                    </div>
                                                @elseif($item->return_condition)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                                          {{ $item->return_condition === 'Baik' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                                        {{ $item->return_condition }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-400 text-xs">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Cancel Action Form (Only for PENDING status) -->
                    @if($loan->status === 'PENDING')
                        <div class="flex justify-end pt-2">
                            <form method="POST" action="{{ route('loans.cancel', $loan) }}"
                                  @click.stop
                                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan peminjaman ini? Tindakan pembatalan tiket tidak dapat dibatalkan.')">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold border border-rose-300 bg-white hover:bg-rose-50 text-rose-700 shadow-xs active:scale-[0.98] transition-all cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">cancel</span>
                                    Batalkan Permohonan Ini
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <!-- Zero State: Belum Ada Riwayat Sama Sekali -->
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center flex flex-col items-center justify-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl">content_paste_off</span>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900">Belum Ada Riwayat Pengajuan</h3>
                <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1 leading-relaxed">
                    Anda belum pernah membuat pengajuan peminjaman alat laboratorium. Klik tombol di bawah untuk mulai mengajukan peminjaman pertama Anda.
                </p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <a href="{{ route('loans.create') }}"
                       class="rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm px-6 py-2.5 shadow-xs inline-flex items-center gap-2 transition-all">
                        <span>Ajukan Peminjaman Sekarang</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if (method_exists($loanRequests, 'hasPages') && $loanRequests->hasPages())
        <div class="p-4 border-t border-slate-200 bg-white rounded-2xl shadow-xs">
            {{ $loanRequests->links() }}
        </div>
    @endif

    <!-- Guidance & SOP Banner (Stitch Screen 4) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-[22px]">info</span>
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-bold text-slate-900">Alur Verifikasi Peminjaman Alat Praktikum</span>
                <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                    Tiket yang berstatus <strong>Disetujui</strong> wajib ditunjukkan dalam format digital atau cetak kepada petugas laboran Gedung D. Bawa KTA/KTM Mahasiswa Unwahas fisik yang masih aktif sebagai jaminan administrasi.
                </p>
            </div>
        </div>
        <a href="{{ route('catalog.index') }}#panduan"
           class="shrink-0 text-xs font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 hover:underline">
            <span>Baca Panduan SOP Lengkap</span>
            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
        </a>
    </div>
</div>
@endsection
