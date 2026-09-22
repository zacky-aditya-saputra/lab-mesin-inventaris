@extends('layouts.admin', ['title' => 'Dashboard Utama', 'pageTitle' => 'Dashboard Utama'])

@section('content')
<div class="space-y-6">
    <!-- Laboran: Managed Laboratory Banner -->
    @if (isset($managedLaboratory) && $managedLaboratory)
        <div class="bg-primary-container text-on-primary-container rounded-xl p-4 shadow-sm flex items-center gap-3">
            <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">science</span>
            <div>
                <span class="block font-label-md text-label-md font-semibold">Laboratorium yang Diampu</span>
                <span class="font-body-md text-body-md">{{ $managedLaboratory->name }}{{ $managedLaboratory->location ? ' — '.$managedLaboratory->location : '' }}</span>
            </div>
        </div>
    @endif

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

    <!-- Tool & Category Summary with Quick View -->
    <div x-data="{
        quickViewTool: null,
        quickViewCategory: null,
        openToolQuickView(tool) { this.quickViewCategory = null; this.quickViewTool = tool; },
        openCategoryQuickView(category) { this.quickViewTool = null; this.quickViewCategory = category; }
    }">
        <!-- Kategori Summary Cards -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
            <div class="p-4 md:p-6 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/50">
                <div>
                    <h3 class="font-title-sm text-title-sm text-on-surface">Ringkasan Kategori Alat</h3>
                    <p class="font-caption-xs text-caption-xs text-on-surface-variant">Klik kartu kategori untuk pratinjau cepat.</p>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="text-primary hover:underline font-label-md text-label-md flex items-center gap-1">
                    Kelola Kategori
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="p-4 md:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                @forelse ($categorySummary ?? [] as $category)
                    <button type="button"
                            @click="openCategoryQuickView({ id: {{ $category->id }}, name: '{{ addslashes($category->name) }}', code: '{{ addslashes($category->code ?? '') }}', description: '{{ addslashes($category->description ?? '') }}', toolsCount: {{ $category->tools_count ?? 0 }} })"
                            class="text-left cursor-pointer bg-surface-container-low border border-outline-variant rounded-xl p-4 hover:border-primary hover:shadow-md transition-all group">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md font-badge-xs text-badge-xs font-mono bg-primary/10 text-primary border border-primary/20">
                                {{ $category->code_or_default }}
                            </span>
                            <span class="material-symbols-outlined text-outline text-base opacity-0 group-hover:opacity-100 transition-opacity">visibility</span>
                        </div>
                        <p class="font-title-sm text-title-sm text-on-surface font-semibold group-hover:text-primary transition-colors truncate">{{ $category->name }}</p>
                        <p class="font-caption-xs text-caption-xs text-on-surface-variant mt-1">{{ $category->tools_count ?? 0 }} alat terdaftar</p>
                    </button>
                @empty
                    <p class="col-span-full text-center text-on-surface-variant font-body-md text-body-md py-6">Belum ada kategori alat.</p>
                @endforelse
            </div>
        </div>

        <!-- Tools Summary Table -->
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
            <div class="p-4 md:p-6 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/50">
                <div>
                    <h3 class="font-title-sm text-title-sm text-on-surface">Alat Terbaru</h3>
                    <p class="font-caption-xs text-caption-xs text-on-surface-variant">Klik baris alat untuk pratinjau cepat.</p>
                </div>
                <a href="{{ route('admin.tools.index') }}" class="text-primary hover:underline font-label-md text-label-md flex items-center gap-1">
                    Kelola Alat
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                            <th class="px-6 py-3.5 font-semibold">Kode Inventaris</th>
                            <th class="px-6 py-3.5 font-semibold">Nama Alat</th>
                            <th class="px-6 py-3.5 font-semibold">Kategori</th>
                            <th class="px-6 py-3.5 font-semibold text-center">Stok</th>
                            <th class="px-6 py-3.5 font-semibold">Lokasi Lab</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                        @forelse ($recentTools ?? [] as $tool)
                            <tr class="hover:bg-surface-container-low transition-colors cursor-pointer"
                                @click="openToolQuickView({ id: {{ $tool->id }}, code: '{{ addslashes($tool->code) }}', name: '{{ addslashes($tool->name) }}', category: '{{ addslashes($tool->category?->name ?? '-') }}', availableStock: {{ $tool->available_stock }}, totalStock: {{ $tool->total_stock }}, laboratory: '{{ addslashes($tool->laboratory?->name ?? '-') }}', specification: '{{ addslashes($tool->specification ?? '-') }}', image: '{{ $tool->image_path ? asset('storage/'.$tool->image_path) : '' }}' })">
                                <td class="px-6 py-4 font-mono font-medium text-primary">{{ $tool->code }}</td>
                                <td class="px-6 py-4 font-medium text-on-surface">{{ $tool->name }}</td>
                                <td class="px-6 py-4 text-on-surface-variant">{{ $tool->category?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs {{ $tool->available_stock > 0 ? 'bg-status-success-bg text-status-success-text' : 'bg-error-container text-error' }}">
                                        {{ $tool->available_stock }} / {{ $tool->total_stock }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant">{{ $tool->laboratory?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                    <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                        <span class="material-symbols-outlined text-2xl text-outline">inventory_2</span>
                                    </div>
                                    <p class="font-title-sm text-title-sm text-on-surface">Belum ada alat terdaftar</p>
                                    <p class="font-caption-xs text-caption-xs mt-1">Tambahkan alat baru melalui menu Data Alat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick View Modal: Alat -->
        <div x-show="quickViewTool"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/50 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="quickViewTool = null"
                 class="w-full max-w-lg bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden"
                 x-show="quickViewTool">
                <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                    <h2 class="font-title-sm text-title-sm text-on-surface">Pratinjau Alat</h2>
                    <button type="button" @click="quickViewTool = null" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-4" x-show="quickViewTool">
                    <div class="flex items-start gap-4">
                        <div class="w-28 h-28 rounded-lg border border-outline-variant bg-surface-container-low flex items-center justify-center flex-shrink-0 overflow-hidden">
                            <template x-if="quickViewTool && quickViewTool.image">
                                <img :src="quickViewTool.image" :alt="quickViewTool.name" class="w-full h-full object-cover"/>
                            </template>
                            <template x-if="quickViewTool && !quickViewTool.image">
                                <span class="material-symbols-outlined text-4xl text-outline">image</span>
                            </template>
                        </div>
                        <div class="min-w-0">
                            <p class="font-mono font-label-md text-label-md text-primary" x-text="quickViewTool?.code"></p>
                            <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mt-0.5" x-text="quickViewTool?.name"></h3>
                            <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">
                                Kategori: <span class="font-medium text-on-surface" x-text="quickViewTool?.category"></span>
                            </p>
                            <p class="font-body-sm text-body-sm text-on-surface-variant">
                                Lokasi: <span class="font-medium text-on-surface" x-text="quickViewTool?.laboratory"></span>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg bg-surface-container-low border border-outline-variant text-center">
                            <p class="font-caption-xs text-caption-xs text-on-surface-variant">Stok Tersedia</p>
                            <p class="font-headline-sm text-headline-sm font-bold text-status-success-text" x-text="quickViewTool?.availableStock"></p>
                        </div>
                        <div class="p-3 rounded-lg bg-surface-container-low border border-outline-variant text-center">
                            <p class="font-caption-xs text-caption-xs text-on-surface-variant">Total Stok</p>
                            <p class="font-headline-sm text-headline-sm font-bold text-on-surface" x-text="quickViewTool?.totalStock"></p>
                        </div>
                    </div>

                    <div>
                        <p class="font-label-md text-label-md text-on-surface-variant mb-1">Spesifikasi</p>
                        <p class="font-body-md text-body-md text-on-surface" x-text="quickViewTool?.specification"></p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low flex justify-between items-center gap-3" x-show="quickViewTool">
                    <form :action="'{{ url('admin/tools') }}/' + quickViewTool?.id" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus alat ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-error hover:bg-[#991b1b] text-on-error rounded-md font-label-md text-label-md shadow-sm transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">delete</span>
                            Hapus Alat
                        </button>
                    </form>
                    <a :href="'{{ url('admin/tools') }}/' + quickViewTool?.id + '/edit'"
                       class="px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-md font-label-md text-label-md shadow-sm transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        Edit Alat
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick View Modal: Kategori -->
        <div x-show="quickViewCategory"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/50 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="quickViewCategory = null"
                 class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden"
                 x-show="quickViewCategory">
                <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                    <h2 class="font-title-sm text-title-sm text-on-surface">Pratinjau Kategori</h2>
                    <button type="button" @click="quickViewCategory = null" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-6 space-y-4" x-show="quickViewCategory">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md font-badge-sm text-badge-sm font-mono bg-primary/10 text-primary border border-primary/20"
                              x-text="quickViewCategory?.code"></span>
                        <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold" x-text="quickViewCategory?.name"></h3>
                    </div>

                    <div>
                        <p class="font-label-md text-label-md text-on-surface-variant mb-1">Deskripsi</p>
                        <p class="font-body-md text-body-md text-on-surface" x-text="quickViewCategory?.description || 'Tidak ada deskripsi.'"></p>
                    </div>

                    <div class="p-3 rounded-lg bg-surface-container-low border border-outline-variant flex items-center justify-between">
                        <span class="font-label-md text-label-md text-on-surface-variant">Jumlah Alat</span>
                        <span class="font-title-md text-title-md font-bold text-primary" x-text="(quickViewCategory?.toolsCount ?? 0) + ' alat'"></span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low flex justify-between items-center gap-3" x-show="quickViewCategory">
                    <form :action="'{{ url('admin/categories') }}/' + quickViewCategory?.id" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus kategori ini? Kategori yang masih memuat alat akan ditolak sistem.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-error hover:bg-[#991b1b] text-on-error rounded-md font-label-md text-label-md shadow-sm transition-colors flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">delete</span>
                            Hapus Kategori
                        </button>
                    </form>
                    <a href="{{ route('admin.categories.index') }}"
                       class="px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-md font-label-md text-label-md shadow-sm transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        Edit Kategori
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Kepala Lab: Laboratory Breakdown -->
    @if (isset($laboratoryBreakdown) && $laboratoryBreakdown)
        <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
            <div class="p-4 md:p-6 border-b border-outline-variant flex justify-between items-center bg-surface-container-low/50">
                <div>
                    <h3 class="font-title-sm text-title-sm text-on-surface">Ringkasan per Laboratorium</h3>
                    <p class="font-caption-xs text-caption-xs text-on-surface-variant">Rekapitulasi aset dan tiket aktif pada seluruh laboratorium.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                            <th class="px-6 py-3.5 font-semibold">Laboratorium</th>
                            <th class="px-6 py-3.5 font-semibold text-right">Alat Aktif</th>
                            <th class="px-6 py-3.5 font-semibold text-center">Menunggu</th>
                            <th class="px-6 py-3.5 font-semibold text-center">Sedang Dipinjam</th>
                            <th class="px-6 py-3.5 font-semibold text-center">Terlambat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                        @foreach ($laboratoryBreakdown as $lab)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-on-surface">{{ $lab->name }}</div>
                                    <div class="font-caption-xs text-caption-xs text-on-surface-variant font-mono">{{ $lab->code }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.tools.index', ['laboratory_id' => $lab->id]) }}" class="font-semibold text-primary hover:underline">{{ $lab->active_tools_count }}</a>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.loans.index', ['laboratory_id' => $lab->id, 'status' => 'PENDING']) }}" class="font-semibold {{ $lab->pending_loans_count > 0 ? 'text-status-pending-text' : 'text-on-surface-variant' }} hover:underline">{{ $lab->pending_loans_count }}</a>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.loans.index', ['laboratory_id' => $lab->id, 'status' => 'ON_LOAN']) }}" class="font-semibold {{ $lab->on_loan_loans_count > 0 ? 'text-status-loan-text' : 'text-on-surface-variant' }} hover:underline">{{ $lab->on_loan_loans_count }}</a>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.loans.index', ['laboratory_id' => $lab->id, 'status' => 'OVERDUE']) }}" class="font-semibold {{ $lab->overdue_loans_count > 0 ? 'text-error' : 'text-on-surface-variant' }} hover:underline">{{ $lab->overdue_loans_count }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

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
