@extends('layouts.admin', ['title' => 'Manajemen Data Alat', 'pageTitle' => 'Manajemen Data Alat'])

@section('content')
<div x-data="{
    searchQuery: '',
    selectedCategory: 'all',
    deleteModalOpen: false,
    deleteFormAction: '',
    deleteToolName: '',
    isRepairModalOpen: false,
    repairToolId: null,
    repairToolName: '',
    repairMaxQuantity: 1,
    openDeleteModal(id, name) {
        this.deleteToolName = name;
        this.deleteFormAction = '{{ url('admin/tools') }}/' + id;
        this.deleteModalOpen = true;
    },
    openRepairModal(id, name, damagedStock) {
        this.repairToolId = id;
        this.repairToolName = name;
        this.repairMaxQuantity = damagedStock;
        this.isRepairModalOpen = true;
    },
    closeRepairModal() {
        this.isRepairModalOpen = false;
    }
}">
    <!-- Action Topbar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Master Inventaris Alat</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Daftar lengkap peralatan laboratorium, stok fisik, dan ketersediaan.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.tools.adjustments') }}" 
               class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap h-10 px-4 bg-surface text-on-surface border border-outline-variant hover:bg-surface-container font-label-md text-label-md rounded-lg transition-all duration-150 shadow-xs active:scale-[0.98] cursor-pointer">
                <span class="material-symbols-outlined text-sm">published_with_changes</span>
                Riwayat Mutasi &amp; Perbaikan
            </a>
            <a href="{{ route('admin.tools.create') }}" 
               class="inline-flex items-center justify-center gap-2 whitespace-nowrap h-10 px-4 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary rounded-lg font-label-md text-label-md shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                <span class="material-symbols-outlined text-sm">add</span>
                Tambah Alat Baru
            </a>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-outline-variant flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-surface-container-low/50">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto flex-1">
                <div class="relative w-full sm:w-72">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-xl">search</span>
                    <input x-model="searchQuery" 
                           class="w-full h-10 pl-10 pr-4 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-shadow" 
                           placeholder="Cari nama, kode alat..." 
                           type="text"/>
                </div>
                <select x-model="selectedCategory" 
                        class="h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm cursor-pointer">
                    <option value="all">Semua Kategori</option>
                    @if(isset($categories))
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    @endif
                </select>
                @if (auth()->user()->isKepalaLab())
                    <form method="GET" action="{{ route('admin.tools.index') }}" class="w-full sm:w-auto">
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-base">science</span>
                            <select name="laboratory_id" onchange="this.form.submit()" aria-label="Filter Laboratorium"
                                    class="w-full sm:w-64 h-10 pl-9 pr-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm font-body-md text-body-md text-on-surface transition-shadow cursor-pointer">
                                <option value="">Semua Laboratorium</option>
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endif
            </div>
            <div class="font-caption-xs text-caption-xs text-on-surface-variant whitespace-nowrap">
                Menampilkan {{ $tools->count() }} dari {{ $tools->total() ?? $tools->count() }} alat
            </div>
        </div>

        <!-- Tool Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="px-6 py-3.5 font-semibold w-16">Item</th>
                        <th class="px-6 py-3.5 font-semibold">Nama Alat</th>
                        <th class="px-6 py-3.5 font-semibold">Kategori</th>
                        <th class="px-6 py-3.5 font-semibold">Laboratorium</th>
                        <th class="px-6 py-3.5 font-semibold">Kode Inv.</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Total Stok</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Tersedia</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Rusak</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-6 py-3.5 font-semibold text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                    @forelse ($tools as $tool)
                        <tr class="hover:bg-surface-container-low transition-colors group"
                            x-show="(!searchQuery || '{{ strtolower($tool->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($tool->code) }}'.includes(searchQuery.toLowerCase())) && (selectedCategory === 'all' || selectedCategory == '{{ $tool->category_id }}')">
                            <!-- Image/Icon Item -->
                            <td class="px-6 py-3.5">
                                <div class="w-10 h-10 rounded-lg bg-surface-container border border-outline-variant/50 flex items-center justify-center overflow-hidden">
                                    @if($tool->image_path)
                                        <img src="{{ asset('storage/' . $tool->image_path) }}" alt="{{ $tool->name }}"
                                             onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')"
                                             class="w-full h-full object-cover">
                                        <span class="material-symbols-outlined text-outline hidden">precision_manufacturing</span>
                                    @else
                                        <span class="material-symbols-outlined text-outline">precision_manufacturing</span>
                                    @endif
                                </div>
                            </td>
                            <!-- Tool Name -->
                            <td class="px-6 py-3.5 font-medium text-on-surface">
                                <div class="font-semibold">{{ $tool->name }}</div>
                                @if($tool->specification)
                                    <div class="font-caption-xs text-caption-xs text-on-surface-variant truncate max-w-xs">{{ $tool->specification }}</div>
                                @endif
                            </td>
                            <!-- Category -->
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-badge-xs text-badge-xs border border-slate-200">
                                    {{ $tool->category->name ?? 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <!-- Laboratory -->
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-badge-xs text-badge-xs font-medium">
                                    <span class="material-symbols-outlined text-xs">science</span>
                                    {{ $tool->laboratory?->name ?? '-' }}
                                </span>
                            </td>
                            <!-- Inventory Code -->
                            <td class="px-6 py-3.5 font-mono text-xs text-on-surface-variant">
                                {{ $tool->code }}
                            </td>
                            <!-- Total Stock -->
                            <td class="px-6 py-3.5 text-right font-mono font-medium">
                                {{ $tool->total_stock }}
                            </td>
                            <!-- Available Stock -->
                            <td class="px-6 py-3.5 text-right">
                                <span class="font-mono font-semibold {{ $tool->available_stock > 0 ? 'text-status-success-text' : 'text-error' }}">
                                    {{ $tool->available_stock }}
                                </span>
                            </td>
                            <!-- Damaged Stock -->
                            <td class="px-6 py-3.5 text-right">
                                <span class="font-mono font-semibold {{ $tool->damaged_stock > 0 ? 'text-amber-600' : 'text-on-surface-variant' }}">
                                    {{ $tool->damaged_stock }}
                                </span>
                            </td>
                            <!-- Active Status Badge -->
                            <td class="px-6 py-3.5 text-center">
                                @if($tool->is_active)
                                    <span class="inline-flex items-center gap-1 bg-status-success-bg text-status-success-text px-2 py-0.5 rounded-full font-badge-xs text-badge-xs border border-status-success-text/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-status-success-text"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-badge-xs text-badge-xs border border-slate-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <!-- Action Buttons -->
                            <td class="px-6 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($tool->damaged_stock > 0)
                                        <button type="button"
                                                @click="openRepairModal({{ $tool->id }}, '{{ addslashes($tool->name) }}', {{ $tool->damaged_stock }})"
                                                class="p-1.5 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30 rounded-lg transition-colors cursor-pointer" 
                                                title="Tandai Selesai Diperbaiki">
                                            <span class="material-symbols-outlined text-base">build</span>
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.tools.edit', $tool) }}" 
                                       class="p-1.5 text-primary hover:bg-primary-fixed rounded-lg transition-colors" 
                                       title="Edit Alat">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </a>
                                    <button type="button"
                                            @click="openDeleteModal({{ $tool->id }}, '{{ addslashes($tool->name) }}')"
                                            class="p-1.5 text-error hover:bg-error-container rounded-lg transition-colors" 
                                            title="Hapus Alat">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl text-outline">inventory_2</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface">Belum ada data alat</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Klik tombol Tambah Alat Baru untuk mendaftarkan inventaris.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($tools->hasPages())
            <div class="p-4 border-t border-outline-variant bg-surface-container-lowest">
                {{ $tools->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Deletion Confirmation -->
    <div x-show="deleteModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/50 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="deleteModalOpen = false" 
             class="w-full max-w-sm bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-error-container text-error flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
                <h3 class="font-title-sm text-title-sm text-on-surface mb-2">Hapus Data Alat?</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mb-6">
                    Apakah Anda yakin ingin menghapus <span class="font-semibold text-on-surface" x-text="deleteToolName"></span>? <br/>
                    <span class="font-medium text-error text-xs">Data alat akan dinonaktifkan / soft-deleted demi integritas riwayat peminjaman.</span>
                </p>
                <form :action="deleteFormAction" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex space-x-3 justify-center">
                        <button type="button" @click="deleteModalOpen = false"
                                class="flex-1 px-4 py-2 border border-slate-300/90 text-slate-700 bg-surface-container-lowest hover:bg-slate-50 hover:border-slate-400 rounded-lg font-label-md text-label-md shadow-xs active:bg-slate-100 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-gradient-to-b from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-on-error rounded-lg font-label-md text-label-md shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Tandai Selesai Diperbaiki (FR-LAB-05 / AC-STK-04) -->
    <div x-show="isRepairModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/50 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="closeRepairModal()" 
             class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-950/30 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined">build</span>
                    </div>
                    <div>
                        <h3 class="font-title-sm text-title-sm text-on-surface font-bold">Tandai Selesai Diperbaiki</h3>
                        <p class="font-caption-xs text-caption-xs text-on-surface-variant" x-text="repairToolName"></p>
                    </div>
                </div>

                <form :action="'{{ url('admin/tools') }}/' + repairToolId + '/repair'" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block font-label-md text-label-md text-on-surface mb-1">Jumlah Unit yang Selesai Diperbaiki</label>
                            <input type="number" name="quantity" min="1" :max="repairMaxQuantity" value="1" required
                                   class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest font-mono focus:ring-2 focus:ring-blue-500">
                            <span class="text-xs text-on-surface-variant mt-1 block">Maksimal unit rusak saat ini: <strong class="font-mono text-amber-700" x-text="repairMaxQuantity"></strong> unit</span>
                        </div>

                        <div>
                            <label class="block font-label-md text-label-md text-on-surface mb-1">Catatan Tindakan Teknis / Perbaikan</label>
                            <textarea name="notes" rows="3" required placeholder="Contoh: Penggantian bearing dan kalibrasi ulang spindle..."
                                      class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="closeRepairModal()" 
                                class="px-4 py-2 border border-slate-300/90 text-slate-700 bg-surface-container-lowest hover:bg-slate-50 hover:border-slate-400 rounded-lg font-label-md text-label-md shadow-xs active:bg-slate-100 active:scale-[0.98] transition-all text-sm font-medium">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-gradient-to-b from-amber-600 to-amber-700 hover:from-amber-500 hover:to-amber-600 text-white rounded-lg font-semibold shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all text-sm">
                            Pulihkan ke Stok Tersedia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
