@extends('layouts.admin', ['title' => 'Manajemen Data Alat', 'pageTitle' => 'Manajemen Data Alat'])

@section('content')
<div x-data="{
    searchQuery: '',
    selectedCategory: 'all',
    deleteModalOpen: false,
    deleteFormAction: '',
    deleteToolName: '',
    openDeleteModal(id, name) {
        this.deleteToolName = name;
        this.deleteFormAction = '{{ url('admin/tools') }}/' + id;
        this.deleteModalOpen = true;
    }
}">
    <!-- Action Topbar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Master Inventaris Alat</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Daftar lengkap peralatan laboratorium, stok fisik, dan ketersediaan.</p>
        </div>
        <a href="{{ route('admin.tools.create') }}" 
           class="h-10 px-4 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md flex items-center gap-2 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-sm">add</span>
            Tambah Alat Baru
        </a>
    </div>

    <!-- Main Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-outline-variant flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-surface-container-low/50">
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-1">
                <div class="relative w-full sm:w-72">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-xl">search</span>
                    <input x-model="searchQuery" 
                           class="w-full h-10 pl-10 pr-4 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-shadow" 
                           placeholder="Cari nama, kode alat..." 
                           type="text"/>
                </div>
                <select x-model="selectedCategory" 
                        class="h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                    <option value="all">Semua Kategori</option>
                    @if(isset($categories))
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    @endif
                </select>
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
                        <th class="px-6 py-3.5 font-semibold">Kode Inv.</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Total Stok</th>
                        <th class="px-6 py-3.5 font-semibold text-right">Tersedia</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Status</th>
                        <th class="px-6 py-3.5 font-semibold text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                    @forelse ($tools as $tool)
                        <tr class="hover:bg-surface-container-low transition-colors group"
                            x-show="(!searchQuery || '{{ strtolower($tool->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($tool->code) }}'.includes(searchQuery.toLowerCase())) && (selectedCategory === 'all' || selectedCategory == '{{ $tool->category_id }}')">
                            <!-- Image/Icon Item -->
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 rounded-lg bg-surface-container border border-outline-variant/50 flex items-center justify-center overflow-hidden">
                                    @if($tool->image_path)
                                        <img src="{{ asset('storage/' . $tool->image_path) }}" alt="{{ $tool->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="material-symbols-outlined text-outline">science</span>
                                    @endif
                                </div>
                            </td>
                            <!-- Tool Name -->
                            <td class="px-6 py-4 font-medium text-on-surface">
                                <div class="font-semibold">{{ $tool->name }}</div>
                                @if($tool->specification)
                                    <div class="font-caption-xs text-caption-xs text-on-surface-variant truncate max-w-xs">{{ $tool->specification }}</div>
                                @endif
                            </td>
                            <!-- Category -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-badge-xs text-badge-xs border border-slate-200">
                                    {{ $tool->category->name ?? 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <!-- Inventory Code -->
                            <td class="px-6 py-4 font-mono text-xs text-on-surface-variant">
                                {{ $tool->code }}
                            </td>
                            <!-- Total Stock -->
                            <td class="px-6 py-4 text-right font-medium">
                                {{ $tool->total_stock }}
                            </td>
                            <!-- Available Stock -->
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold {{ $tool->available_stock > 0 ? 'text-status-success-text' : 'text-error' }}">
                                    {{ $tool->available_stock }}
                                </span>
                            </td>
                            <!-- Active Status Badge -->
                            <td class="px-6 py-4 text-center">
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
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.tools.edit', $tool) }}" 
                                       class="p-1.5 text-primary hover:bg-primary-fixed rounded transition-colors" 
                                       title="Edit Alat">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </a>
                                    <button type="button"
                                            @click="openDeleteModal({{ $tool->id }}, '{{ addslashes($tool->name) }}')"
                                            class="p-1.5 text-error hover:bg-error-container rounded transition-colors" 
                                            title="Hapus Alat">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-on-surface-variant">
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
                                class="flex-1 px-4 py-2 border border-outline-variant text-secondary bg-surface-container-lowest hover:bg-surface-container-low rounded-md font-label-md text-label-md transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-error hover:bg-[#991b1b] text-on-error rounded-md font-label-md text-label-md shadow-sm transition-colors">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
