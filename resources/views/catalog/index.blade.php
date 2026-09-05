@extends('layouts.catalog', ['title' => 'Katalog Alat Laboratorium'])

@section('content')
<div x-data="labCatalog({
    categories: {{ Js::from($categoriesList ?? ['Semua Alat']) }},
    tools: {{ Js::from($toolsList ?? []) }}
})" class="min-h-screen flex flex-col">
    <!-- Main Content Container -->
    <div class="pt-8 pb-16 px-gutter-md max-w-container-max mx-auto w-full flex-grow flex flex-col">
        <!-- Hero Section -->
        <header class="text-center mb-10 mt-4">
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-3 font-bold">Katalog Alat Laboratorium</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto mb-6">
                Laboratorium Proses Produksi Teknik Mesin Universitas Wahid Hasyim. Temukan spesifikasi alat dan ketersediaan stok secara real-time.
            </p>
            <div class="max-w-2xl mx-auto relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline">search</span>
                <input class="w-full pl-12 pr-4 py-3.5 rounded-xl border-outline-variant bg-surface-container-lowest focus:ring-primary focus:border-primary shadow-sm transition-all font-body-md text-body-md" 
                       placeholder="Cari nama alat, kode inventaris, atau kategori..." 
                       type="text" 
                       x-model="searchQuery"/>
            </div>
        </header>

        <!-- Category Filter Pills -->
        <div class="flex flex-wrap justify-center gap-2.5 mb-10">
            <template :key="category" x-for="category in categories">
                <button :class="activeCategory === category 
                                ? 'bg-primary text-on-primary font-semibold shadow-sm' 
                                : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest transition-colors'" 
                        @click="activeCategory = category" 
                        class="px-4 py-2 rounded-full font-label-md text-label-md border border-outline-variant transition-colors" 
                        x-text="category">
                </button>
            </template>
        </div>

        <!-- Tool Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="filteredTools.length > 0">
            <template :key="tool.id" x-for="tool in filteredTools">
                <div @click="openDetail(tool)"
                     class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm hover:shadow-md hover:border-primary/50 transition-all overflow-hidden flex flex-col cursor-pointer group">
                    <div class="relative h-48 w-full bg-surface-container flex items-center justify-center overflow-hidden">
                        <template x-if="tool.image">
                            <img :src="tool.image" :alt="tool.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"/>
                        </template>
                        <template x-if="!tool.image">
                            <div class="flex flex-col items-center justify-center text-outline">
                                <span class="material-symbols-outlined text-5xl">precision_manufacturing</span>
                            </div>
                        </template>
                    </div>

                    <div class="p-padding-card flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2.5">
                                <span class="font-caption-xs text-caption-xs text-secondary px-2.5 py-1 bg-secondary-container rounded font-mono font-semibold" x-text="tool.code"></span>
                                <span class="font-caption-xs text-caption-xs text-on-surface-variant font-medium" x-text="tool.category"></span>
                            </div>
                            <h3 class="font-title-sm text-title-sm text-on-surface mb-2 group-hover:text-primary transition-colors line-clamp-1" x-text="tool.name"></h3>
                            <p class="font-body-md text-body-md text-on-surface-variant line-clamp-2 mb-4 text-xs" x-text="tool.specification || 'Tidak ada deskripsi singkat.'"></p>
                        </div>

                        <div class="pt-4 border-t border-outline-variant flex items-center justify-between mt-auto">
                            <!-- Status Badge -->
                            <span :class="{
                                      'bg-status-success-bg text-status-success-text border-status-success-text/30': tool.status === 'Tersedia',
                                      'bg-status-pending-bg text-status-pending-text border-status-pending-text/30': tool.status === 'Stok Terbatas',
                                      'bg-slate-200 text-slate-600 border-slate-300': tool.status === 'Tidak Tersedia'
                                  }" 
                                  class="inline-flex items-center px-2.5 py-1 rounded-full font-badge-xs text-badge-xs border">
                                <span class="material-symbols-outlined text-[14px] mr-1" 
                                      :style="tool.status === 'Tersedia' ? 'font-variation-settings: \'FILL\' 1;' : ''">
                                    inventory_2
                                </span>
                                <span x-text="tool.status + ' (' + tool.available_stock + ')'"></span>
                            </span>

                            <span class="text-primary font-label-md text-label-md group-hover:translate-x-1 transition-transform flex items-center gap-0.5">
                                Detail
                                <span class="material-symbols-outlined text-sm">chevron_right</span>
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div class="flex flex-col items-center justify-center py-20 text-center flex-grow" style="display: none;" x-show="filteredTools.length === 0">
            <div class="w-24 h-24 mb-4 bg-surface-container rounded-full flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl text-outline">search_off</span>
            </div>
            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-2 font-bold">Alat tidak ditemukan</h3>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-md">
                Kami tidak dapat menemukan alat yang cocok dengan kata kunci pencarian atau filter kategori Anda.
            </p>
            <button @click="resetFilters" class="mt-4 px-4 py-2 bg-primary text-on-primary rounded-lg font-label-md text-label-md hover:bg-primary-container transition-colors">
                Reset Pencarian
            </button>
        </div>
    </div>

    <!-- Modal: Detail Spesifikasi Alat (Screen 2) -->
    <div x-show="detailModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-sidebar-dark/50 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="detailModalOpen = false" 
             class="bg-surface-container-lowest w-full max-w-3xl rounded-xl shadow-2xl flex flex-col max-h-[90vh] overflow-hidden border border-outline-variant">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-outline-variant bg-surface-container-low">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">info</span>
                    <h2 class="font-title-sm text-title-sm text-on-surface font-bold">Detail Spesifikasi Alat</h2>
                </div>
                <button @click="detailModalOpen = false" class="text-on-surface-variant hover:text-primary transition-colors rounded-full p-1">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-6 md:p-8" x-if="selectedTool">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    <!-- Left: Image -->
                    <div class="flex flex-col gap-4">
                        <div class="aspect-square rounded-xl border border-outline-variant bg-surface-container flex items-center justify-center overflow-hidden p-4 relative group">
                            <template x-if="selectedTool?.image">
                                <img :src="selectedTool.image" :alt="selectedTool.name" class="w-full h-full object-contain mix-blend-multiply"/>
                            </template>
                            <template x-if="!selectedTool?.image">
                                <div class="flex flex-col items-center justify-center text-outline">
                                    <span class="material-symbols-outlined text-6xl">precision_manufacturing</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right: Info -->
                    <div class="flex flex-col">
                        <div class="mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-secondary-container px-2.5 py-1 rounded font-badge-xs text-badge-xs text-on-secondary-container font-mono font-semibold" x-text="selectedTool?.code"></span>
                                <span class="bg-surface-container px-2.5 py-1 rounded font-badge-xs text-badge-xs text-secondary border border-outline-variant" x-text="selectedTool?.category"></span>
                            </div>
                            <h3 class="font-headline-md text-headline-md text-on-surface font-bold mb-2" x-text="selectedTool?.name"></h3>
                        </div>

                        <!-- Technical Specs -->
                        <div class="mb-6">
                            <h4 class="font-label-md text-label-md text-on-surface mb-2 font-semibold border-b border-outline-variant pb-1.5">Deskripsi &amp; Spesifikasi</h4>
                            <div class="font-body-md text-body-md text-on-surface-variant whitespace-pre-line text-sm" x-text="selectedTool?.specification || 'Tidak ada spesifikasi khusus.'"></div>
                        </div>

                        <!-- Availability Box -->
                        <div class="bg-surface-container-low rounded-xl border border-outline-variant p-4 mb-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">Stok Fisik Total</span>
                                    <span class="font-title-sm text-title-sm text-on-surface font-bold" x-text="(selectedTool?.total_stock || 0) + ' Unit'"></span>
                                </div>
                                <div>
                                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">Tersedia untuk Diajukan</span>
                                    <div class="flex items-center gap-2">
                                        <span class="font-title-sm text-title-sm font-bold text-status-success-text" x-text="(selectedTool?.available_stock || 0) + ' Unit'"></span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-badge-xs text-badge-xs"
                                              :class="selectedTool?.available_stock > 0 ? 'bg-status-success-bg text-status-success-text' : 'bg-slate-200 text-slate-600'"
                                              x-text="selectedTool?.status"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="mt-auto pt-4 border-t border-outline-variant flex justify-end gap-3">
                            <button @click="detailModalOpen = false" 
                                    class="px-5 py-2.5 rounded-lg font-label-md text-label-md text-secondary bg-surface-container hover:bg-surface-container-highest transition-colors border border-outline-variant">
                                Tutup
                            </button>
                            <a :href="'{{ route('loans.create') }}?tool_id=' + (selectedTool?.id || '')" 
                               class="px-5 py-2.5 rounded-lg font-label-md text-label-md text-on-primary bg-primary hover:bg-primary-container transition-colors shadow-sm flex items-center gap-2">
                                <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                                Ajukan Peminjaman
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function labCatalog(data) {
        return {
            searchQuery: '',
            activeCategory: 'Semua Alat',
            categories: data.categories || ['Semua Alat'],
            tools: data.tools || [],
            detailModalOpen: false,
            selectedTool: null,
            openDetail(tool) {
                this.selectedTool = tool;
                this.detailModalOpen = true;
            },
            get filteredTools() {
                return this.tools.filter(tool => {
                    const q = this.searchQuery.toLowerCase();
                    const matchesSearch = !q || 
                        tool.name.toLowerCase().includes(q) || 
                        tool.code.toLowerCase().includes(q) ||
                        (tool.category && tool.category.toLowerCase().includes(q));
                    const matchesCategory = this.activeCategory === 'Semua Alat' || tool.category === this.activeCategory;
                    return matchesSearch && matchesCategory;
                });
            },
            resetFilters() {
                this.searchQuery = '';
                this.activeCategory = 'Semua Alat';
            }
        };
    }
</script>
@endpush
