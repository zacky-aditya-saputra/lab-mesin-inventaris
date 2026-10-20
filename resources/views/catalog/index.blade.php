@extends('layouts.catalog', ['title' => 'Katalog Alat Laboratorium'])

@php
    $labShortName = fn (string $name): string => str_replace('Laboratorium', 'Lab', $name);

    $categoryIcon = fn (string $name): string => match (true) {
        str_contains(strtolower($name), 'ukur') => 'straighten',
        str_contains(strtolower($name), 'perkakas') || str_contains(strtolower($name), 'mesin') => 'precision_manufacturing',
        str_contains(strtolower($name), 'las') => 'local_fire_department',
        str_contains(strtolower($name), 'otomotif') => 'directions_car',
        str_contains(strtolower($name), 'listrik') || str_contains(strtolower($name), 'elektronik') => 'bolt',
        str_contains(strtolower($name), 'hidrolik') || str_contains(strtolower($name), 'pneumatik') => 'compress',
        default => 'category',
    };

    $categoryIconMap = collect($categoriesList ?? ['Semua Alat'])
        ->mapWithKeys(fn (string $name): array => [$name => $categoryIcon($name)])
        ->all();

    $buildingPhoto = null;
    foreach (['gedung-teknik.jpg', 'gedung-teknik.png', 'gedung-teknik.webp', 'gedung.jpg', 'gedung.png', 'gedung-teknik.jpeg'] as $candidate) {
        if (file_exists(public_path('images/' . $candidate))) {
            $buildingPhoto = asset('images/' . $candidate);
            break;
        }
    }
@endphp

@section('content')
<div x-data="labCatalog({
        categories: {{ Js::from($categoriesList ?? ['Semua Alat']) }},
        categoryIcons: {{ Js::from($categoryIconMap) }},
        tools: {{ Js::from($toolsList ?? []) }},
        initialLab: {{ Js::from($selectedLabSlug ?? 'all') }}
    })"
     @keydown.escape.window="detailModalOpen = false; $store.ui.filterSheetOpen = false; $store.ui.sopSheetOpen = false"
     class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-6 lg:py-8 flex-1">

    <!-- Mobile SOP Quick Trigger -->
    <button type="button" @click="$store.ui.sopSheetOpen = true"
            class="mb-4 flex w-full cursor-pointer items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-left transition-colors active:bg-slate-50 md:hidden shadow-xs">
        <span class="flex items-center gap-2 text-xs font-medium text-slate-800">
            <span class="material-symbols-outlined text-base text-blue-600">info</span>
            SOP: Pengajuan H-2 &amp; Wajib APD Lengkap
        </span>
        <span class="flex items-center gap-0.5 text-xs font-semibold text-blue-600">
            Detail
            <span class="material-symbols-outlined text-sm">chevron_right</span>
        </span>
    </button>

    <!-- Main Content: Desktop 2-Kolom (Sidebar Sticky w-64 + Kolom Grid Peralatan) -->
    <div class="flex flex-col gap-6 lg:flex-row lg:gap-8 items-start">

        <!-- Sidebar Filter (Desktop Sticky w-64, Digital Atelier Inspired) -->
        <aside class="hidden lg:block w-64 shrink-0 sticky top-24 lg:top-28 space-y-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 tracking-tight">Filters</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Refine your selection</p>
                </div>

                <!-- Search Input di Sidebar -->
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                    <input type="text" x-model="$store.ui.search"
                           placeholder="Cari alat / kode..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-7 text-xs text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none transition-colors"/>
                    <button type="button" x-show="$store.ui.search" x-cloak @click="$store.ui.search = ''"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-xs">close</span>
                    </button>
                </div>

                <!-- Filter Kategori Alat (Active Soft Pill Design) -->
                <div class="border-t border-slate-100 pt-4">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Kategori Alat</label>
                    <div class="space-y-1">
                        <template :key="category" x-for="category in categories">
                            <button type="button" @click="activeCategory = category"
                                    class="w-full px-3.5 py-2.5 rounded-full border text-xs font-medium flex items-center justify-between text-left transition-all cursor-pointer"
                                    :class="activeCategory === category ? 'border-blue-200 bg-blue-50 text-blue-700 font-semibold shadow-xs' : 'border-transparent bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-50'">
                                <span class="flex items-center gap-2.5 truncate">
                                    <span class="material-symbols-outlined text-base shrink-0" :class="activeCategory === category ? 'text-blue-600' : 'text-slate-400'" x-text="categoryIcons[category] || 'category'"></span>
                                    <span class="truncate" x-text="category"></span>
                                </span>
                                <span class="font-mono text-[10px] shrink-0 ml-1" :class="activeCategory === category ? 'text-blue-600 font-bold' : 'text-slate-400'" x-text="categoryCount(category)"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Filter Laboratorium Pengampu -->
                <div class="border-t border-slate-100 pt-4">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Laboratorium Pengampu</label>
                    <div class="space-y-1">
                        <button type="button" @click="selectLab('all')"
                                class="w-full px-3.5 py-2 rounded-xl border text-xs font-medium flex items-center justify-between text-left transition-all cursor-pointer"
                                :class="selectedLab === 'all' ? 'border-blue-200 bg-blue-50 text-blue-700 font-semibold' : 'border-transparent bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-50'">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-blue-600">hub</span>
                                Semua Lab
                            </span>
                            <span class="font-mono text-[10px] text-slate-400" x-text="laboratoryCount('all')"></span>
                        </button>
                        @foreach ($laboratories as $lab)
                            <button type="button" @click="selectLab('{{ $lab->slug }}')"
                                    class="w-full px-3.5 py-2 rounded-xl border text-xs font-medium flex items-center justify-between text-left transition-all cursor-pointer"
                                    :class="selectedLab === '{{ $lab->slug }}' ? 'border-blue-200 bg-blue-50 text-blue-700 font-semibold' : 'border-transparent bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-50'">
                                <span class="flex items-center gap-2 min-w-0">
                                    <span class="material-symbols-outlined text-base text-cyan-600 shrink-0">science</span>
                                    <span class="truncate">{{ $labShortName($lab->name) }}</span>
                                </span>
                                <span class="flex items-center gap-1 shrink-0">
                                    <span class="bg-slate-100 text-slate-500 font-mono text-[9px] px-1 py-0.5 rounded">{{ $lab->code }}</span>
                                    <span class="font-mono text-[10px] text-slate-400" x-text="laboratoryCount('{{ $lab->slug }}')"></span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Toggle Stok Siap Pakai -->
                <div class="border-t border-slate-100 pt-4">
                    <button type="button" @click="onlyAvailable = !onlyAvailable"
                            class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left transition-colors"
                            :class="onlyAvailable ? 'bg-blue-50' : 'bg-slate-50 hover:bg-slate-100'">
                        <div>
                            <span class="block text-xs font-semibold" :class="onlyAvailable ? 'text-blue-700' : 'text-slate-700'">Hanya Siap Pakai</span>
                            <span class="block text-[11px] text-slate-400">Sembunyikan stok habis</span>
                        </div>
                        <span class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors" :class="onlyAvailable ? 'bg-blue-600' : 'bg-slate-300'">
                            <span class="absolute h-4 w-4 rounded-full bg-white shadow-xs transition-all" :class="onlyAvailable ? 'left-[18px]' : 'left-[2px]'"></span>
                        </span>
                    </button>
                </div>

                <!-- Clear All Button -->
                <button type="button" @click="resetFilters"
                        class="w-full py-2 text-center text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors cursor-pointer border-t border-slate-100 pt-3">
                    Reset Semua Filter (Clear All)
                </button>
            </div>
        </aside>

        <!-- Tool Grid Section (Main Column) -->
        <section class="min-w-0 flex-1 w-full">
            <!-- Header Section (Clean & Authoritative, Digital Atelier Style) -->
            <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight">Koleksi Peralatan Laboratorium</h1>
                    <p class="text-xs sm:text-sm text-slate-500 max-w-2xl mt-1.5 leading-relaxed">
                        Eksplorasi inventaris peralatan praktikum dan riset presisi lintas laboratorium Teknik Mesin Unwahas. Cek ketersediaan unit secara real-time sebelum mengajukan peminjaman mandiri.
                    </p>
                </div>

                <!-- Desktop Search Input -->
                <div class="hidden sm:block w-72 relative shrink-0">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" x-model="$store.ui.search" placeholder="Cari nama alat / kode..."
                           class="w-full bg-white border border-slate-200 pl-10 pr-8 py-2 rounded-full text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-xs transition-all">
                    <button type="button" x-show="$store.ui.search" x-cloak @click="$store.ui.search = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            aria-label="Bersihkan pencarian">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar (Phone Only) -->
            <div class="sm:hidden mb-4 relative">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
                <input type="text" x-model="$store.ui.search" placeholder="Cari nama alat / kode..."
                       class="w-full bg-white border border-slate-200 pl-9 pr-8 py-2 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-xs transition-all">
                <button type="button" x-show="$store.ui.search" x-cloak @click="$store.ui.search = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                        aria-label="Bersihkan pencarian">
                    <span class="material-symbols-outlined text-xs">close</span>
                </button>
            </div>

            <!-- Hero Quick Guide Cyan Banner (Digital Atelier Pattern with Authentic Campus Architecture Overlay) -->
            <div class="rounded-3xl bg-[#26D0ED] p-6 sm:p-7 lg:p-8 mb-6 flex flex-col md:flex-row items-stretch justify-between gap-6 relative overflow-hidden shadow-sm">
                <!-- Foto Gedung Teknik Unwahas (Authentic Building Overlay - Anti AI-Slop) -->
                @if ($buildingPhoto)
                    <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
                        <img src="{{ $buildingPhoto }}"
                             alt="Gedung Fakultas Teknik Unwahas"
                             class="h-full w-full object-cover object-[center_60%] opacity-45 sm:opacity-50 mix-blend-multiply filter contrast-125 brightness-95" />
                        <!-- Gradient wash: protect left text while letting building architecture shine on center & right -->
                        <div class="absolute inset-0 bg-gradient-to-r from-[#26D0ED] via-[#26D0ED]/60 to-transparent"></div>
                    </div>
                @else
                    <!-- Fallback Photo Slot (Auto activates when file exists in public/images/gedung-teknik.jpg) -->
                    <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
                        <img src="{{ asset('images/gedung-teknik.jpeg') }}"
                             alt="Gedung Fakultas Teknik Unwahas"
                             class="h-full w-full object-cover object-[center_60%] opacity-45 sm:opacity-50 mix-blend-multiply filter contrast-125"
                             onerror="this.style.display='none'" />
                        <div class="absolute inset-0 bg-gradient-to-r from-[#26D0ED] via-[#26D0ED]/60 to-transparent"></div>
                    </div>
                @endif

                <!-- Left side -->
                <div class="flex flex-col justify-between max-w-xl z-10">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-900/75">
                            Panduan Singkat
                        </p>
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-950 tracking-tight mt-1 leading-tight">
                            CARI ALAT, CEK STOK &amp; AJUKAN MANDIRI
                        </h2>
                        <p class="text-xs sm:text-sm font-medium text-slate-900/90 mt-1.5 leading-relaxed">
                            Pantau sisa stok siap pakai secara real-time, pilih alat yang dibutuhkan, dan ajukan peminjaman langsung tanpa formulir kertas.
                        </p>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-slate-900/85">
                        <span class="font-medium">Pilih alat &rarr; Isi formulir &rarr; Ambil di lab</span>
                        <span class="text-slate-900/30">&bull;</span>
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-700"></span>
                            Stok terhubung langsung
                        </span>
                    </div>
                </div>

                <!-- Right side: Glass Card -->
                <div class="w-full md:w-52 lg:w-56 shrink-0 rounded-2xl bg-white/30 backdrop-blur-md border border-white/50 p-5 lg:p-6 flex flex-col items-center justify-center text-center shadow-xs z-10">
                    <span class="font-mono text-4xl lg:text-5xl font-black text-slate-900 leading-none">3</span>
                    <span class="text-[11px] font-bold tracking-wider uppercase text-slate-900/80 mt-1.5">LANGKAH MUDAH</span>
                    <span class="text-xs text-slate-800/90 font-semibold mt-0.5">Cek Stok &amp; Pinjam</span>
                </div>

                <!-- Subtle Background Decoration Circles for Depth -->
                <div class="pointer-events-none absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-white/15 blur-2xl"></div>
                <div class="pointer-events-none absolute -left-12 -top-12 w-48 h-48 rounded-full bg-white/10 blur-xl"></div>
            </div>

            <!-- Horizontal Chip Carousel (Mobile Only) -->
            <div class="mb-4 flex items-center gap-2 overflow-x-auto no-scrollbar py-1 md:hidden">
                <button type="button" @click="selectLab('all')"
                        class="inline-flex shrink-0 cursor-pointer items-center gap-1 whitespace-nowrap rounded-full border px-3.5 py-1.5 text-xs transition-colors"
                        :class="selectedLab === 'all' ? 'border-blue-600 bg-blue-600 font-semibold text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100'">
                    Semua Lab
                </button>
                @foreach ($laboratories as $lab)
                    <button type="button" @click="selectLab('{{ $lab->slug }}')"
                            class="inline-flex shrink-0 cursor-pointer items-center gap-1 whitespace-nowrap rounded-full border px-3.5 py-1.5 text-xs transition-colors"
                            :class="selectedLab === '{{ $lab->slug }}' ? 'border-blue-600 bg-blue-600 font-semibold text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100'">
                        {{ $labShortName($lab->name) }}
                    </button>
                @endforeach
                <button type="button" @click="onlyAvailable = !onlyAvailable"
                        class="inline-flex shrink-0 cursor-pointer items-center gap-1 whitespace-nowrap rounded-full border px-3.5 py-1.5 text-xs transition-colors"
                        :class="onlyAvailable ? 'border-emerald-500 bg-emerald-500 font-semibold text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100'">
                    <span class="material-symbols-outlined text-sm">check_circle</span>
                    Ready Stok
                </button>
                <button type="button" @click="$store.ui.filterSheetOpen = true"
                        class="inline-flex shrink-0 cursor-pointer items-center gap-1 whitespace-nowrap rounded-full border border-blue-200 bg-blue-50 px-3.5 py-1.5 text-xs font-semibold text-blue-700 transition-colors active:bg-blue-100">
                    <span class="material-symbols-outlined text-sm">tune</span>
                    Filter
                </button>
            </div>

            <!-- Control Bar: Hitungan Item & Reset Filter -->
            <div class="mb-4 flex items-center justify-between gap-2 pb-3 border-b border-slate-200">
                <p class="text-xs font-semibold text-slate-500">
                    Menampilkan <span class="font-mono font-bold text-slate-900" x-text="filteredTools.length"></span>
                    dari <span class="font-mono font-bold text-slate-900" x-text="tools.length"></span> peralatan laboratorium
                </p>
                <button type="button" x-show="activeFilterCount > 0" x-cloak @click="resetFilters"
                        class="flex cursor-pointer items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    <span class="material-symbols-outlined text-sm">restart_alt</span>
                    Reset Filter
                </button>
            </div>

            <!-- Horizontal Media Card List (Mobile-First Anti-Pecah: Media 88x88px) -->
            <div class="flex flex-col gap-3 md:hidden" x-show="filteredTools.length > 0">
                <template :key="'m-' + tool.id" x-for="tool in filteredTools">
                    <article @click="openDetail(tool)"
                             class="group flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200/90 bg-white p-3 shadow-xs transition-all active:scale-[0.99] hover:border-blue-300">
                        <!-- Media 88x88px di Ponsel -->
                        <div class="relative flex h-[88px] w-[88px] shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-100 bg-slate-100">
                            <template x-if="tool.image">
                                <img :src="tool.image" :alt="tool.name"
                                     @@error="$el.style.display='none'; $el.nextElementSibling.classList.remove('hidden')"
                                     class="h-full w-full object-cover"/>
                            </template>
                            <div :class="tool.image ? 'hidden' : 'flex'"
                                 class="flex-col items-center justify-center p-2 text-center text-slate-400 select-none">
                                <span class="material-symbols-outlined text-2xl text-blue-600/70">precision_manufacturing</span>
                                <span class="mt-1 font-mono text-[8px] font-semibold uppercase tracking-wider text-slate-500" x-text="tool.code"></span>
                            </div>
                        </div>

                        <!-- Konten Informasi Alat (Mobile) -->
                        <div class="min-w-0 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="font-mono text-[9px] font-bold text-slate-400 uppercase tracking-wider" x-text="tool.code"></span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="truncate text-[10px] font-semibold text-blue-600 uppercase tracking-wide" x-text="tool.laboratory"></span>
                                </div>
                                <h3 class="line-clamp-2 text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors" x-text="tool.name"></h3>
                            </div>

                            <div class="mt-2 flex items-center justify-between gap-2 border-t border-slate-100 pt-1.5">
                                <div>
                                    <span class="truncate font-mono text-[11px] font-bold" :class="stockTextClass(tool)" x-text="'Tersedia: ' + (tool.available_stock || 0) + ' Unit'"></span>
                                </div>

                                <template x-if="tool.available_stock > 0">
                                    <a :href="loanUrl(tool)" @click.stop
                                       class="inline-flex h-8 shrink-0 items-center gap-1 rounded-full bg-blue-600 px-3 text-xs font-semibold text-white shadow-xs active:bg-blue-700 transition-colors">
                                        <span class="material-symbols-outlined text-sm">add</span>
                                        <span>Pinjam</span>
                                    </a>
                                </template>
                                <template x-if="tool.available_stock < 1">
                                    <span class="inline-flex h-8 shrink-0 items-center gap-1 rounded-full border border-slate-200 bg-slate-100 px-2.5 text-[11px] font-semibold text-slate-400">
                                        Habis
                                    </span>
                                </template>
                            </div>
                        </div>
                    </article>
                </template>
            </div>

            <!-- Product Card Grid (Desktop: 3-4 Kolom) -->
            <div class="hidden grid-cols-1 gap-5 sm:grid-cols-2 md:grid lg:grid-cols-3 xl:grid-cols-4" x-show="filteredTools.length > 0">
                <template :key="'d-' + tool.id" x-for="tool in filteredTools">
                    <article @click="openDetail(tool)"
                             class="group flex cursor-pointer flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs transition-all duration-200 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-500/5">
                        
                        <!-- Media Container (Aspect 4:3) -->
                        <div class="relative flex aspect-[4/3] w-full items-center justify-center overflow-hidden border-b border-slate-100 bg-slate-100">
                            <template x-if="tool.image">
                                <img :src="tool.image" :alt="tool.name"
                                     @@error="$el.style.display='none'; $el.nextElementSibling.classList.remove('hidden')"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"/>
                            </template>
                            <div :class="tool.image ? 'hidden' : 'flex'"
                                 class="flex-col items-center justify-center p-4 text-center text-slate-400 select-none">
                                <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                                    <span class="material-symbols-outlined text-2xl text-blue-600/80">precision_manufacturing</span>
                                </div>
                                <span class="font-mono text-[10px] font-medium uppercase tracking-wider text-slate-500" x-text="tool.code"></span>
                            </div>

                            <!-- Laboratory Badge Overlay -->
                            <span class="absolute left-3 top-3 inline-flex max-w-[62%] items-center gap-1 rounded-full bg-white/95 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-700 shadow-xs backdrop-blur"
                                  x-show="tool.laboratory">
                                <span class="material-symbols-outlined text-[12px]">science</span>
                                <span class="truncate" x-text="tool.laboratory"></span>
                            </span>

                            <!-- Stock Status Badge Overlay -->
                            <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider shadow-xs backdrop-blur"
                                  :class="tool.available_stock > 0 ? 'bg-cyan-500 text-white' : 'bg-rose-500 text-white'">
                                <span x-text="tool.available_stock > 0 ? 'READY' : 'HABIS'"></span>
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="flex flex-grow flex-col justify-between p-4">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="font-mono text-[10px] font-semibold text-slate-400" x-text="tool.code"></span>
                                    <span class="truncate text-[10px] font-semibold uppercase tracking-wide text-slate-400" x-text="tool.category"></span>
                                </div>

                                <h3 class="line-clamp-2 text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors" x-text="tool.name"></h3>
                                <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-500" x-text="tool.specification || 'Lihat detail untuk rincian spesifikasi.'"></p>
                            </div>

                            <!-- Footer Card: Stok & Tombol Pinjam -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col gap-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 shrink-0 rounded-full" :class="statusDotClass(tool)"></span>
                                        <span class="font-mono text-xs font-bold" :class="stockTextClass(tool)" x-text="'Tersedia: ' + (tool.available_stock || 0) + ' Unit'"></span>
                                    </span>
                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold" :class="statusBadgeClass(tool)" x-text="tool.status"></span>
                                </div>

                                <template x-if="tool.available_stock > 0">
                                    <a :href="loanUrl(tool)" @click.stop
                                       class="flex h-9 w-full items-center justify-center gap-1 rounded-full bg-blue-600 text-xs font-semibold text-white shadow-xs hover:bg-blue-700 active:scale-98 transition-all">
                                        <span class="material-symbols-outlined text-base">add</span>
                                        + Pinjam Alat
                                    </a>
                                </template>
                                <template x-if="tool.available_stock < 1">
                                    <span class="flex h-9 w-full cursor-not-allowed items-center justify-center gap-1 rounded-full border border-slate-200 bg-slate-100 text-xs font-semibold text-slate-400">
                                        <span class="material-symbols-outlined text-sm">block</span>
                                        Tidak Tersedia
                                    </span>
                                </template>
                            </div>
                        </div>
                    </article>
                </template>
            </div>

            <!-- Empty State -->
            <div class="flex flex-grow flex-col items-center justify-center py-16 text-center" style="display: none;" x-show="filteredTools.length === 0">
                <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full border border-slate-200 bg-white shadow-xs">
                    <span class="material-symbols-outlined text-3xl text-slate-300">search_off</span>
                </div>
                <h3 class="mb-1 text-base font-bold text-slate-900">Peralatan Tidak Ditemukan</h3>
                <p class="max-w-md text-xs text-slate-500">
                    Tidak ditemukan alat yang cocok dengan kata kunci pencarian atau filter yang dipilih. Silakan ubah filter Anda.
                </p>
                <button type="button" @click="resetFilters"
                        class="mt-4 inline-flex cursor-pointer items-center gap-1.5 rounded-full bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-blue-700 transition-colors">
                    <span class="material-symbols-outlined text-sm">restart_alt</span>
                    Reset Semua Filter
                </button>
            </div>
        </section>
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION: PANDUAN STANDAR OPERASIONAL PROSEDUR (Anchor: #panduan)         -->
    <!-- ========================================================================= -->
    <section id="panduan" class="mt-16 scroll-mt-24 space-y-10 border-t border-slate-200 pt-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <span class="font-mono text-xs font-bold uppercase tracking-wider text-blue-600 block mb-1">01 / Prosedur Baku</span>
                <h2 class="text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight">Alur 4 Tahap Peminjaman Alat &amp; Fasilitas</h2>
            </div>
            <p class="text-xs text-slate-500 max-w-md">
                Setiap instrumen ukur presisi dan perkakas mesin tunduk pada pelaporan berjenjang guna menjamin keselamatan dan akurasi kalibrasi.
            </p>
        </div>

        <!-- 4 Sequential Step Columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Stage 01 -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-mono text-xs font-bold">01</span>
                        <span class="material-symbols-outlined text-blue-600 text-xl">inventory_2</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm mb-1">Penelusuran &amp; Stok</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Cek katalog daring mesin &amp; perkakas untuk memastikan ketersediaan unit, status kalibrasi, dan kapasitas daya.
                        </p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 font-mono text-[11px] text-slate-500 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-xs text-blue-600">info</span>
                    Verifikasi kode inventaris
                </div>
            </div>

            <!-- Stage 02 -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-mono text-xs font-bold">02</span>
                        <span class="material-symbols-outlined text-blue-600 text-xl">edit_calendar</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm mb-1">Pengajuan Daring H-2</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Submit formulir peminjaman sekurang-kurangnya dua hari kerja sebelum tanggal pelaksanaan dengan melampirkan modul/proposal.
                        </p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 font-mono text-[11px] text-amber-700 font-semibold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-xs">warning</span>
                    Batas submit 15.00 WIB
                </div>
            </div>

            <!-- Stage 03 -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-mono text-xs font-bold">03</span>
                        <span class="material-symbols-outlined text-blue-600 text-xl">verified_user</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm mb-1">Validasi Teknisi</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Laboran memeriksa kelaikan alat, menghindari jadwal bentrok dengan praktikum reguler, dan merilis persetujuan tiket.
                        </p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 font-mono text-[11px] text-slate-500 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-xs text-blue-600">qr_code_2</span>
                    Notifikasi tiket resmi
                </div>
            </div>

            <!-- Stage 04 -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-mono text-xs font-bold">04</span>
                        <span class="material-symbols-outlined text-blue-600 text-xl">fact_check</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm mb-1">Inspeksi &amp; Serah Terima</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Hadir di laboratorium bersama teknisi untuk pengujian kondisi awal, verifikasi kelengkapan, dan penandatanganan serah terima.
                        </p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 font-mono text-[11px] text-emerald-700 font-semibold flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-xs">check_circle</span>
                    Wajib presensi fisik
                </div>
            </div>
        </div>

        <!-- Special Banner: Dispensasi Skripsi & Tugas Akhir -->
        <div class="rounded-2xl border border-blue-200 bg-blue-50/70 p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 shadow-xs">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5">
                    <span class="material-symbols-outlined text-2xl">assignment_turned_in</span>
                </div>
                <div class="space-y-1">
                    <span class="font-mono text-[11px] uppercase font-bold text-blue-700 tracking-wider">Dispensasi Riset Akademik</span>
                    <h3 class="font-bold text-base text-slate-900">Skema Khusus Skripsi &amp; Proyek Tugas Akhir</h3>
                    <p class="text-xs text-slate-600 leading-relaxed max-w-3xl">
                        Mahasiswa tingkat akhir berhak atas masa pinjam hingga <strong>7 (tujuh) hari kerja berturut-turut</strong> dengan melampirkan Surat Rekomendasi bermeterai dari Dosen Pembimbing Skripsi serta rincian metodologi pengujian laboratorium.
                    </p>
                </div>
            </div>
            <div class="shrink-0">
                <a href="#katalog" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-600 text-white font-semibold text-xs shadow-xs hover:bg-blue-700 transition-colors">
                    <span>Mulai Pilih Alat</span>
                    <span class="material-symbols-outlined text-sm">arrow_upward</span>
                </a>
            </div>
        </div>

        <!-- Standar Operasional K3 Ruang Mesin -->
        <div class="space-y-6 pt-4">
            <div>
                <span class="font-mono text-xs font-bold uppercase tracking-wider text-rose-600 block mb-1">02 / Keselamatan Kerja</span>
                <h3 class="text-xl lg:text-2xl font-bold text-slate-900 tracking-tight">Standar Operasional K3 Ruang Mesin</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                    Zona praktikum permesinan dan uji material memiliki risiko mekanis serta termal. Seluruh praktikan wajib menaati ketentuan K3 berikut:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Panel 1: APD Wajib -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2.5 mb-3.5 pb-2.5 border-b border-slate-100">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">shield</span>
                        </span>
                        <h4 class="font-bold text-sm text-slate-900">APD Wajib Praktikan</h4>
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 shrink-0 mt-0.5">check_circle</span>
                            <span><strong>Jas Lab Katun Tebal:</strong> Lengan panjang berkancing rapat melindungi dari percikan gram panas.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 shrink-0 mt-0.5">check_circle</span>
                            <span><strong>Sepatu Safety Sol Baja:</strong> Standar SNI/ISO toecap tahan benturan beban berat.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600 shrink-0 mt-0.5">check_circle</span>
                            <span><strong>Kacamata Goggles Polikarbonat:</strong> Pelindung mata dari lontaran serpihan logam permesinan.</span>
                        </li>
                    </ul>
                </div>

                <!-- Panel 2: Pantangan Kerja -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2.5 mb-3.5 pb-2.5 border-b border-slate-100">
                        <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">warning</span>
                        </span>
                        <h4 class="font-bold text-sm text-slate-900">Pantangan Operasional</h4>
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-rose-600 shrink-0 mt-0.5">cancel</span>
                            <span><strong>Dilarang Sarung Tangan Longgar:</strong> Berisiko tinggi terlilit poros spindel mesin bubut atau frais.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-rose-600 shrink-0 mt-0.5">cancel</span>
                            <span><strong>Dilarang Mengoperasikan Tanpa Laboran:</strong> Mesin bertegangan tinggi wajib disupervisi teknisi.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-rose-600 shrink-0 mt-0.5">cancel</span>
                            <span><strong>Dilarang Meninggalkan Mesin Menyala:</strong> Selalu matikan saklar daya saat melakukan pengukuran benda kerja.</span>
                        </li>
                    </ul>
                </div>

                <!-- Panel 3: Tanggap Darurat -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2.5 mb-3.5 pb-2.5 border-b border-slate-100">
                        <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">emergency</span>
                        </span>
                        <h4 class="font-bold text-sm text-slate-900">Prosedur Darurat</h4>
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-600 shrink-0 mt-0.5">report_problem</span>
                            <span><strong>Tombol Emergency Stop:</strong> Tekan tombol jamur merah di panel utama bila terjadi getaran abnormal atau macet.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-600 shrink-0 mt-0.5">report_problem</span>
                            <span><strong>Kotak P3K &amp; APAR:</strong> Tersedia di pilar timur Gedung D Lantai 1 dekat pintu keluar evakuasi.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-600 shrink-0 mt-0.5">report_problem</span>
                            <span><strong>Lapor Cepat:</strong> Segera beri tahu laboran jaga bila terdapat anomali bau kabel terbakar atau kebocoran pelumas.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- SECTION: DIREKTORI KONTAK & LOKASI LAB (Anchor: #kontak)                 -->
    <!-- ========================================================================= -->
    <section id="kontak" class="mt-16 scroll-mt-24 space-y-10 border-t border-slate-200 pt-12 mb-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <span class="font-mono text-xs font-bold uppercase tracking-wider text-blue-600 block mb-1">03 / Informasi Kontak</span>
                <h2 class="text-2xl lg:text-3xl font-extrabold text-slate-900 tracking-tight">Sentra Laboratorium Teknik Mesin</h2>
            </div>
            <p class="text-xs text-slate-500 max-w-md">
                Setiap laboratorium memiliki penanggung jawab khusus (laboran) dengan jalur komunikasi langsung via WhatsApp Hotline dan telepon ekstensi kampus.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Lab 1: LPP -->
            <article class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="font-mono text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded uppercase">LPP-01</span>
                            <h3 class="text-base font-bold text-slate-900 mt-1.5">Lab Proses Produksi &amp; Fabrikasi</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-[10px] font-semibold text-slate-600 shrink-0">Gedung D Lt. 1</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Mesin bubut konvensional, mesin frais, mesin bor, stasiun las SMAW/GTAW, gerinda potong &amp; pedestal.
                    </p>
                    <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">Bpk. Tri Widodo</p>
                            <p class="text-[11px] text-slate-500">Teknisi Fabrikasi &amp; Permesinan</p>
                        </div>
                        <span class="font-mono text-xs font-bold text-blue-600">Ekst. 101</span>
                    </div>
                </div>
                <div class="pt-2 flex items-center gap-3">
                    <a href="https://wa.me/6281234567891?text=Halo%20Bpk%20Tri%20Widodo,%20saya%20ingin%20konsultasi%20alat%20Lab%20LPP" target="_blank" rel="noopener noreferrer"
                       class="flex-1 py-2 px-3 rounded-full bg-blue-600 text-white font-semibold text-xs flex items-center justify-center gap-1.5 hover:bg-blue-700 transition-colors shadow-xs">
                        <span class="material-symbols-outlined text-sm">chat</span>
                        WhatsApp Hotline
                    </a>
                </div>
            </article>

            <!-- Lab 2: LMAT -->
            <article class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="font-mono text-[10px] font-bold text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded uppercase">LMAT-02</span>
                            <h3 class="text-base font-bold text-slate-900 mt-1.5">Lab Metalurgi &amp; Pengujian Material</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-[10px] font-semibold text-slate-600 shrink-0">Gedung D Lt. 2</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Universal Testing Machine (UTM 50 kN) tarik/tekan, uji kekerasan Rockwell &amp; Brinell, uji impak Charpy 300J, muffle furnace 1200°C.
                    </p>
                    <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">Bpk. Kurnia Sandi</p>
                            <p class="text-[11px] text-slate-500">Teknisi Pengujian Bahan &amp; Metalografi</p>
                        </div>
                        <span class="font-mono text-xs font-bold text-cyan-600">Ekst. 102</span>
                    </div>
                </div>
                <div class="pt-2 flex items-center gap-3">
                    <a href="https://wa.me/6281234567892?text=Halo%20Bpk%20Kurnia%20Sandi,%20saya%20ingin%20konsultasi%20uji%20bahan%20Lab%20LMAT" target="_blank" rel="noopener noreferrer"
                       class="flex-1 py-2 px-3 rounded-full bg-cyan-600 text-white font-semibold text-xs flex items-center justify-center gap-1.5 hover:bg-cyan-700 transition-colors shadow-xs">
                        <span class="material-symbols-outlined text-sm">chat</span>
                        WhatsApp Hotline
                    </a>
                </div>
            </article>

            <!-- Lab 3: LPR -->
            <article class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="font-mono text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded uppercase">LPR-03</span>
                            <h3 class="text-base font-bold text-slate-900 mt-1.5">Lab Perancangan CAD/CAM &amp; Metrologi</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-[10px] font-semibold text-slate-600 shrink-0">Gedung D Lt. 2</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Workstation CAD/FEA, Surface Roughness Tester Mitutoyo, mikrometer digital 0.001 mm, 3D Printer FDM.
                    </p>
                    <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">Sdr. Rizki Pratama</p>
                            <p class="text-[11px] text-slate-500">Instruktur CAD &amp; Instrumen Metrologi</p>
                        </div>
                        <span class="font-mono text-xs font-bold text-indigo-600">Ekst. 103</span>
                    </div>
                </div>
                <div class="pt-2 flex items-center gap-3">
                    <a href="https://wa.me/6281234567893?text=Halo%20Sdr%20Rizki%20Pratama,%20saya%20ingin%20konsultasi%20alat%20Lab%20LPR" target="_blank" rel="noopener noreferrer"
                       class="flex-1 py-2 px-3 rounded-full bg-indigo-600 text-white font-semibold text-xs flex items-center justify-center gap-1.5 hover:bg-indigo-700 transition-colors shadow-xs">
                        <span class="material-symbols-outlined text-sm">chat</span>
                        WhatsApp Hotline
                    </a>
                </div>
            </article>

            <!-- Lab 4: LEN -->
            <article class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded uppercase">LEN-04</span>
                            <h3 class="text-base font-bold text-slate-900 mt-1.5">Lab Konversi Energi &amp; Termofluida</h3>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-[10px] font-semibold text-slate-600 shrink-0">Gedung D Lt. 1</span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Turbin air Pelton/Francis, bomb calorimeter, alat uji konduktivitas termal, blower fluida dinamik, motor bakar uji 4-langkah.
                    </p>
                    <div class="bg-slate-50 rounded-xl p-3 flex items-center justify-between text-xs">
                        <div>
                            <p class="font-bold text-slate-900">Bpk. Agus Supriyanto</p>
                            <p class="text-[11px] text-slate-500">Teknisi Mesin Termal &amp; Aliran Fluida</p>
                        </div>
                        <span class="font-mono text-xs font-bold text-emerald-600">Ekst. 104</span>
                    </div>
                </div>
                <div class="pt-2 flex items-center gap-3">
                    <a href="https://wa.me/6281234567894?text=Halo%20Bpk%20Agus%20Supriyanto,%20saya%20ingin%20konsultasi%20alat%20Lab%20LEN" target="_blank" rel="noopener noreferrer"
                       class="flex-1 py-2 px-3 rounded-full bg-emerald-600 text-white font-semibold text-xs flex items-center justify-center gap-1.5 hover:bg-emerald-700 transition-colors shadow-xs">
                        <span class="material-symbols-outlined text-sm">chat</span>
                        WhatsApp Hotline
                    </a>
                </div>
            </article>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- MODAL: DETAIL SPESIFIKASI ALAT (Inspirasi Dribbble Minimalist Floating)   -->
    <!-- ========================================================================= -->
    <div x-show="detailModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-md transition-opacity duration-300"
         style="display: none;">
        
        <!-- Floating Vertical Equipment Detail Card (rounded-3xl, Pristine Visual) -->
        <div @click.away="detailModalOpen = false"
             x-show="detailModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Minimalist Circular Close Button -->
            <button type="button" @click="detailModalOpen = false"
                    class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-slate-500 hover:text-slate-900 shadow-sm backdrop-blur flex items-center justify-center transition-all cursor-pointer"
                    aria-label="Tutup jendela inspeksi">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>

            <!-- Scrollable Content Container -->
            <div class="overflow-y-auto flex-1">
                <!-- 1. Hero Visual (Pristine, Anti-AI-slop Product View) -->
                <div class="relative w-full aspect-[16/10] bg-slate-100 overflow-hidden flex items-center justify-center">
                    <template x-if="selectedTool?.image">
                        <img :src="selectedTool.image" :alt="selectedTool.name"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out hover:scale-105"/>
                    </template>
                    <div :class="selectedTool?.image ? 'hidden' : 'flex'"
                         class="flex-col items-center justify-center p-6 text-slate-400 select-none">
                        <div class="mb-2 flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-200 bg-white shadow-xs">
                            <span class="material-symbols-outlined text-4xl text-blue-600/80">precision_manufacturing</span>
                        </div>
                        <span class="font-mono text-xs font-semibold uppercase tracking-wider text-slate-500" x-text="selectedTool?.code"></span>
                    </div>

                    <!-- Minimal Technical Registration Stamp -->
                    <span class="absolute bottom-3 left-4 font-mono text-[10px] uppercase tracking-widest text-slate-500 bg-white/80 px-2 py-0.5 rounded backdrop-blur-xs"
                          x-text="'REF // ' + (selectedTool?.code || 'TM-LAB')"></span>
                </div>

                <!-- 2. Technical Information & Compact Specs -->
                <div class="flex flex-col px-6 pt-5">
                    <!-- Tool Title -->
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-snug"
                        x-text="selectedTool?.name"></h2>

                    <!-- Subtle Metadata (Clean Plain-text, No Rainbow Pills) -->
                    <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-500 font-medium">
                        <span x-text="selectedTool?.category || 'Peralatan Teknis'"></span>
                        <span class="text-slate-300">&bull;</span>
                        <span x-text="selectedTool?.laboratory || 'Laboratorium Mesin'"></span>
                        <span class="text-slate-300">&bull;</span>
                        <span>Gedung D</span>
                    </div>

                    <!-- Concise Purpose Description -->
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mt-3 whitespace-pre-line"
                       x-text="selectedTool?.specification || 'Peralatan penunjang praktikum dan penelitian presisi mahasiswa Teknik Mesin Universitas Wahid Hasyim.'"></p>

                    <!-- Compact Key Specs Strip (2 Key Specs Only) -->
                    <div class="mt-4 py-2.5 px-4 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between text-xs text-slate-700">
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono text-slate-400 text-[11px]">Kode Aset:</span>
                            <span class="font-mono font-semibold text-slate-900" x-text="selectedTool?.code"></span>
                        </div>
                        <span class="text-slate-300">&bull;</span>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono text-slate-400 text-[11px]">Total Unit:</span>
                            <span class="font-semibold text-slate-900" x-text="(selectedTool?.total_stock || 0) + ' Unit'"></span>
                        </div>
                    </div>

                    <!-- Interactive Collapsible Accordion ([ ▾ Lihat Spesifikasi Lengkap ]) -->
                    <details class="group mt-3.5 mb-2" id="accordion-specs">
                        <summary class="list-none flex items-center justify-between text-xs font-semibold text-blue-600 hover:text-blue-700 cursor-pointer py-1.5 transition-colors select-none">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base transition-transform duration-200 group-open:rotate-180">expand_more</span>
                                <span>Lihat Spesifikasi Lengkap &amp; Aksesoris</span>
                            </span>
                            <span class="font-mono text-[10px] text-slate-400 uppercase tracking-wider group-open:hidden">[ Rincian ]</span>
                        </summary>
                        <div class="mt-2.5 pt-2 pb-2 space-y-2 text-xs border-t border-slate-100 transition-all duration-300">
                            <div class="flex items-baseline justify-between py-1">
                                <span class="text-slate-500 font-medium">Laboratorium Pengampu</span>
                                <span class="font-semibold text-slate-900 text-right" x-text="selectedTool?.laboratory"></span>
                            </div>
                            <div class="flex items-baseline justify-between py-1">
                                <span class="text-slate-500 font-medium">Status Kelaikan</span>
                                <span class="font-semibold text-emerald-600 text-right">Terkalibrasi Standar Laboratorium</span>
                            </div>
                            <div class="flex items-baseline justify-between py-1">
                                <span class="text-slate-500 font-medium">Syarat Pengajuan</span>
                                <span class="font-semibold text-slate-900 text-right">H-2 Hari Kerja &amp; Wajib APD</span>
                            </div>
                            <div class="flex flex-col py-1">
                                <span class="text-slate-500 font-medium mb-1">Catatan Serah Terima</span>
                                <span class="text-slate-600 text-[11px] leading-relaxed">
                                    Pengecekan fisik dan tanda terima berita acara wajib dilakukan bersama laboran saat serah terima alat.
                                </span>
                            </div>
                        </div>
                    </details>
                </div>
            </div>

            <!-- 3. Footer Telemetry & Action Row -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0">
                <!-- Stock Status Telemetry -->
                <div class="flex flex-col">
                    <div class="font-mono text-xl sm:text-2xl font-bold text-slate-900 tracking-tight leading-none">
                        <span x-text="selectedTool?.available_stock || 0"></span>
                        <span class="text-xs font-normal text-slate-500">Unit</span>
                    </div>
                    <div class="text-[11px] font-semibold flex items-center gap-1.5 mt-1" :class="stockTextClass(selectedTool || {})">
                        <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(selectedTool || {})"></span>
                        <span x-text="selectedTool?.status || 'Tersedia'"></span>
                    </div>
                </div>

                <!-- Primary Action CTA -->
                <template x-if="(selectedTool?.available_stock || 0) > 0">
                    <a :href="selectedTool ? loanUrl(selectedTool) : '#'"
                       class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-semibold text-xs sm:text-sm px-6 py-2.5 rounded-full shadow-md shadow-blue-600/20 transition-all cursor-pointer flex items-center gap-2">
                        <span>Ajukan Peminjaman</span>
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </a>
                </template>
                <template x-if="(selectedTool?.available_stock || 0) < 1">
                    <button type="button" disabled
                            class="bg-slate-200 text-slate-400 font-semibold text-xs sm:text-sm px-5 py-2.5 rounded-full cursor-not-allowed flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">block</span>
                        <span>Stok Habis</span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BOTTOM SHEET: FILTER LENGKAP (Mobile)                                    -->
    <!-- ========================================================================= -->
    <div x-cloak>
        <div x-show="$store.ui.filterSheetOpen" x-transition.opacity.duration.200ms
             class="fixed inset-0 z-[60] bg-slate-900/50 backdrop-blur-sm"
             @click="$store.ui.filterSheetOpen = false"></div>
        <div x-show="$store.ui.filterSheetOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed inset-x-0 bottom-0 z-[60] flex max-h-[85vh] flex-col rounded-t-2xl bg-white shadow-2xl sm:mx-auto sm:max-w-lg">
            <div class="mx-auto mt-3 h-1.5 w-10 shrink-0 rounded-full bg-slate-200"></div>
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600">tune</span>
                    <h2 class="text-base font-bold text-slate-900">Filter Pencarian</h2>
                </div>
                <button type="button" @click="$store.ui.filterSheetOpen = false"
                        class="cursor-pointer rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Tutup filter">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto px-5 py-4">
                <div>
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">Laboratorium Pengampu</p>
                    <div class="space-y-1.5">
                        <button type="button" @click="selectLab('all')"
                                class="flex w-full cursor-pointer items-center justify-between gap-2 rounded-xl border px-3 py-2.5 text-left text-sm font-medium transition-colors"
                                :class="selectedLab === 'all' ? 'border-blue-200 bg-blue-50 text-blue-700 font-semibold' : 'border-slate-200 bg-white text-slate-700'">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">hub</span>
                                Semua Laboratorium
                            </span>
                            <span class="material-symbols-outlined text-base" :class="selectedLab === 'all' ? 'text-blue-600' : 'text-transparent'">radio_button_checked</span>
                        </button>
                        @foreach ($laboratories as $lab)
                            <button type="button" @click="selectLab('{{ $lab->slug }}')"
                                    class="flex w-full cursor-pointer items-center justify-between gap-2 rounded-xl border px-3 py-2.5 text-left text-sm font-medium transition-colors"
                                    :class="selectedLab === '{{ $lab->slug }}' ? 'border-blue-200 bg-blue-50 text-blue-700 font-semibold' : 'border-slate-200 bg-white text-slate-700'">
                                <span class="flex min-w-0 items-center gap-2">
                                    <span class="material-symbols-outlined text-lg text-cyan-600">science</span>
                                    <span class="truncate">{{ $lab->name }}</span>
                                    <span class="shrink-0 rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-slate-500">{{ $lab->code }}</span>
                                </span>
                                <span class="material-symbols-outlined text-base" :class="selectedLab === '{{ $lab->slug }}' ? 'text-blue-600' : 'text-transparent'">radio_button_checked</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">Kategori Alat</p>
                    <div class="flex flex-wrap gap-2">
                        <template :key="'sheet-' + category" x-for="category in categories">
                            <button type="button" @click="activeCategory = category"
                                    class="cursor-pointer rounded-full border px-3.5 py-2 text-xs transition-colors"
                                    :class="activeCategory === category ? 'border-blue-600 bg-blue-600 font-semibold text-white' : 'border-slate-200 bg-white text-slate-700'">
                                <span x-text="category"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.08em] text-slate-400">Ketersediaan</p>
                    <button type="button" @click="onlyAvailable = !onlyAvailable"
                            class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border px-3 py-2.5 text-left transition-colors"
                            :class="onlyAvailable ? 'border-blue-200 bg-blue-50' : 'border-slate-200 bg-white'">
                        <div>
                            <span class="block text-sm font-semibold" :class="onlyAvailable ? 'text-blue-700' : 'text-slate-700'">Hanya Alat Siap Pakai</span>
                            <span class="block text-xs text-slate-500">Sembunyikan alat dengan stok habis</span>
                        </div>
                        <span class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors" :class="onlyAvailable ? 'bg-blue-600' : 'bg-slate-300'">
                            <span class="absolute h-4 w-4 rounded-full bg-white shadow-xs transition-all" :class="onlyAvailable ? 'left-[18px]' : 'left-[2px]'"></span>
                        </span>
                    </button>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-3 border-t border-slate-100 px-5 py-4">
                <button type="button" @click="resetFilters"
                        class="cursor-pointer rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-50">
                    Reset
                </button>
                <button type="button" @click="$store.ui.filterSheetOpen = false"
                        class="flex-1 cursor-pointer rounded-full bg-blue-600 py-2.5 text-sm font-semibold text-white shadow-xs transition-colors hover:bg-blue-700">
                    Terapkan Filter
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- BOTTOM SHEET: DETAIL SOP PEMINJAMAN (Mobile)                             -->
    <!-- ========================================================================= -->
    <div x-cloak>
        <div x-show="$store.ui.sopSheetOpen" x-transition.opacity.duration.200ms
             class="fixed inset-0 z-[60] bg-slate-900/50 backdrop-blur-sm"
             @click="$store.ui.sopSheetOpen = false"></div>
        <div x-show="$store.ui.sopSheetOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed inset-x-0 bottom-0 z-[60] flex max-h-[85vh] flex-col rounded-t-2xl bg-white shadow-2xl">
            <div class="mx-auto mt-3 h-1.5 w-10 shrink-0 rounded-full bg-slate-200"></div>
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="flex items-center gap-2 text-base font-bold text-slate-900">
                    <span class="material-symbols-outlined text-blue-600">gavel</span>
                    SOP Peminjaman Alat
                </h2>
                <button type="button" @click="$store.ui.sopSheetOpen = false"
                        class="cursor-pointer rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        aria-label="Tutup SOP">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="flex-1 space-y-4 overflow-y-auto px-5 py-4 text-sm text-slate-600">
                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs leading-relaxed text-blue-900">
                    <span class="font-bold">Pengajuan H-2:</span> formulir peminjaman wajib diajukan paling lambat 2 hari kerja sebelum jadwal praktikum agar dapat diverifikasi laboran.
                </div>
                <ol class="space-y-3">
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 font-mono text-[11px] font-bold text-white">1</span>
                        <span>Pilih alat pada katalog, periksa ketersediaan stok, lalu tekan <span class="font-semibold text-slate-900">+ Pinjam Alat</span>.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 font-mono text-[11px] font-bold text-white">2</span>
                        <span>Lengkapi formulir peminjaman: keperluan, jumlah unit, serta surat rekomendasi modul.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 font-mono text-[11px] font-bold text-white">3</span>
                        <span>Pantau tiket pengajuan Anda; laboran akan memverifikasi pada jam layanan 08.00&ndash;16.00 WIB.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 font-mono text-[11px] font-bold text-white">4</span>
                        <span>Tunjukkan tiket yang disetujui kepada laboran saat pengambilan alat di laboratorium.</span>
                    </li>
                </ol>
                <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-amber-700">
                    <span class="material-symbols-outlined text-base shrink-0 mt-0.5">health_and_safety</span>
                    <span><span class="font-bold">Wajib APD:</span> jas lab, safety goggles, dan sepatu tertutup harus dikenakan selama berada di area kerja mesin.</span>
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
            tools: data.tools || [],
            categories: data.categories || ['Semua Alat'],
            categoryIcons: data.categoryIcons || {},
            selectedLab: data.initialLab || 'all',
            activeCategory: 'Semua Alat',
            onlyAvailable: false,
            detailModalOpen: false,
            selectedTool: null,

            get filteredTools() {
                const query = (this.$store.ui.search || '').toLowerCase().trim();

                return this.tools.filter((tool) => {
                    const haystack = [tool.name, tool.code, tool.specification, tool.category, tool.laboratory];
                    const matchesSearch = !query || haystack.some((value) => value && String(value).toLowerCase().includes(query));
                    const matchesCategory = this.activeCategory === 'Semua Alat' || tool.category === this.activeCategory;
                    const matchesLab = !this.selectedLab || this.selectedLab === 'all' || tool.laboratory_slug === this.selectedLab;
                    const matchesAvailability = !this.onlyAvailable || (tool.available_stock || 0) > 0;

                    return matchesSearch && matchesCategory && matchesLab && matchesAvailability;
                });
            },

            get activeFilterCount() {
                return (this.$store.ui.search ? 1 : 0)
                    + (this.activeCategory !== 'Semua Alat' ? 1 : 0)
                    + (this.selectedLab !== 'all' ? 1 : 0)
                    + (this.onlyAvailable ? 1 : 0);
            },

            categoryCount(category) {
                if (category === 'Semua Alat') {
                    return this.tools.length;
                }

                return this.tools.filter((tool) => tool.category === category).length;
            },

            laboratoryCount(slug) {
                if (slug === 'all') {
                    return this.tools.length;
                }

                return this.tools.filter((tool) => tool.laboratory_slug === slug).length;
            },

            selectLab(slug) {
                this.selectedLab = slug;
                this.updateLabUrl();
            },

            updateLabUrl() {
                try {
                    const url = new URL(window.location.href);
                    if (this.selectedLab && this.selectedLab !== 'all') {
                        url.searchParams.set('lab', this.selectedLab);
                    } else {
                        url.searchParams.delete('lab');
                    }
                    window.history.replaceState({}, '', url.toString());
                } catch (e) {}
            },

            resetFilters() {
                this.$store.ui.search = '';
                this.activeCategory = 'Semua Alat';
                this.selectedLab = 'all';
                this.onlyAvailable = false;
                this.updateLabUrl();
            },

            openDetail(tool) {
                this.selectedTool = tool;
                this.detailModalOpen = true;
            },

            stockTextClass(tool) {
                const stock = tool.available_stock || 0;
                if (stock <= 0) {
                    return 'text-rose-600';
                }
                if (stock === 1) {
                    return 'text-amber-600';
                }

                return 'text-emerald-600';
            },

            statusDotClass(tool) {
                const classes = {
                    'Tersedia': 'bg-emerald-500',
                    'Stok Terbatas': 'bg-amber-500',
                    'Tidak Tersedia': 'bg-rose-500',
                };

                return classes[tool.status] || 'bg-slate-400';
            },

            statusBadgeClass(tool) {
                const classes = {
                    'Tersedia': 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    'Stok Terbatas': 'border-amber-200 bg-amber-50 text-amber-700',
                    'Tidak Tersedia': 'border-rose-200 bg-rose-50 text-rose-700',
                };

                return classes[tool.status] || 'border-slate-200 bg-slate-100 text-slate-600';
            },

            loanUrl(tool) {
                return '{{ route('loans.create') }}?tool_id=' + tool.id;
            },
        };
    }
</script>
@endpush
