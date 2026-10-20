@extends('layouts.admin', ['title' => 'Riwayat Mutasi & Perbaikan Stok', 'pageTitle' => 'Mutasi Stok'])

@section('content')
<div class="space-y-6">
    <!-- Action Topbar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.tools.index') }}" class="inline-flex items-center gap-1 text-on-surface-variant hover:text-primary transition-colors text-xs font-medium">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Kembali ke Master Inventaris
                </a>
            </div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Riwayat Mutasi &amp; Perbaikan Stok</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Jejak audit seluruh perubahan stok master dan aksi pemulihan alat rusak.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.tools.index') }}" 
               class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap px-4 py-2 bg-surface text-on-surface border border-outline-variant hover:bg-surface-container font-label-md text-label-md rounded-lg transition-all duration-150 shadow-xs active:scale-[0.98] cursor-pointer">
                <span class="material-symbols-outlined text-sm">inventory_2</span>
                Daftar Alat
            </a>
            <a href="{{ route('admin.tools.create') }}" 
               class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-4 py-2 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary rounded-lg font-label-md text-label-md shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                <span class="material-symbols-outlined text-sm">add</span>
                Tambah Alat Baru
            </a>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden flex flex-col">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-outline-variant flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-surface-container-low/50">
            <form method="GET" action="{{ route('admin.tools.adjustments') }}" class="flex flex-col sm:flex-row sm:items-center gap-3 w-full lg:w-auto flex-1">
                <!-- Search Box -->
                <div class="relative w-full sm:w-72">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-xl">search</span>
                    <input name="search" value="{{ request('search') }}"
                           class="w-full h-10 pl-10 pr-4 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm transition-shadow" 
                           placeholder="Cari nama atau kode alat..." 
                           type="text"/>
                </div>

                <!-- Type Filter -->
                <select name="type" onchange="this.form.submit()"
                        class="h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm cursor-pointer">
                    <option value="">Semua Tipe Mutasi</option>
                    <option value="repair" {{ request('type') === 'repair' ? 'selected' : '' }}>Perbaikan Alat (Repair)</option>
                    <option value="manual_adjustment" {{ request('type') === 'manual_adjustment' ? 'selected' : '' }}>Penyesuaian Manual (Adjustment)</option>
                </select>

                <!-- Laboratory Filter (Kepala Lab only) -->
                @if (auth()->user()->isKepalaLab())
                    <div class="relative w-full sm:w-64">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-base">science</span>
                        <select name="laboratory_id" onchange="this.form.submit()"
                                class="w-full h-10 pl-9 pr-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 text-sm text-on-surface cursor-pointer">
                            <option value="">Semua Laboratorium</option>
                            @foreach ($laboratories as $lab)
                                <option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(request('search') || request('type') || request('laboratory_id'))
                    <a href="{{ route('admin.tools.adjustments') }}" class="text-xs text-primary hover:underline self-center">
                        Reset Filter
                    </a>
                @endif
            </form>

            <div class="font-caption-xs text-caption-xs text-on-surface-variant whitespace-nowrap">
                Menampilkan {{ $adjustments->count() }} dari {{ $adjustments->total() }} catatan mutasi
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="px-6 py-3.5 font-semibold whitespace-nowrap">Waktu &amp; Tanggal</th>
                        <th class="px-6 py-3.5 font-semibold">Nama Alat</th>
                        <th class="px-6 py-3.5 font-semibold">Laboratorium</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Tipe Mutasi</th>
                        <th class="px-6 py-3.5 font-semibold text-center">Perubahan</th>
                        <th class="px-6 py-3.5 font-semibold">Dampak Stok</th>
                        <th class="px-6 py-3.5 font-semibold">Eksekutor</th>
                        <th class="px-6 py-3.5 font-semibold">Alasan / Catatan Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant font-body-md text-body-md text-on-surface">
                    @forelse ($adjustments as $adj)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <!-- Waktu & Tanggal -->
                            <td class="px-6 py-3.5 whitespace-nowrap font-mono text-xs text-on-surface-variant">
                                <div>{{ $adj->created_at ? $adj->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-[11px] text-outline">{{ $adj->created_at ? $adj->created_at->format('H:i:s') : '-' }} WIB</div>
                            </td>

                            <!-- Nama Alat & Kode -->
                            <td class="px-6 py-3.5 font-medium">
                                <div class="font-semibold text-on-surface">{{ $adj->tool->name ?? 'Alat Dihapus' }}</div>
                                <span class="font-mono text-xs text-on-surface-variant">{{ $adj->tool->code ?? '-' }}</span>
                            </td>

                            <!-- Laboratorium -->
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-badge-xs text-badge-xs font-medium">
                                    <span class="material-symbols-outlined text-xs">science</span>
                                    {{ $adj->tool->laboratory->name ?? '-' }}
                                </span>
                            </td>

                            <!-- Tipe Mutasi -->
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                @if($adj->type === 'repair')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-badge-xs text-badge-xs font-semibold">
                                        <span class="material-symbols-outlined text-xs">build</span>
                                        Perbaikan Alat
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-badge-xs text-badge-xs font-semibold">
                                        <span class="material-symbols-outlined text-xs">tune</span>
                                        Penyesuaian Stok
                                    </span>
                                @endif
                            </td>

                            <!-- Perubahan (Delta) -->
                            <td class="px-6 py-3.5 text-center whitespace-nowrap font-mono font-bold">
                                @if($adj->delta > 0)
                                    <span class="text-emerald-600">+{{ $adj->delta }} Unit</span>
                                @elseif($adj->delta < 0)
                                    <span class="text-rose-600">{{ $adj->delta }} Unit</span>
                                @else
                                    <span class="text-on-surface-variant">0 Unit</span>
                                @endif
                            </td>

                            <!-- Dampak Stok Sebelum -> Sesudah -->
                            <td class="px-6 py-3.5 whitespace-nowrap font-mono text-xs">
                                <div class="flex items-center gap-1">
                                    <span class="text-on-surface-variant">Tersedia:</span>
                                    <span class="font-medium text-slate-500">{{ $adj->before_available }}</span>
                                    <span class="material-symbols-outlined text-[12px] text-outline">arrow_forward</span>
                                    <span class="font-bold text-emerald-700">{{ $adj->after_available }}</span>
                                </div>
                                <div class="flex items-center gap-1 mt-0.5">
                                    <span class="text-on-surface-variant">Rusak:</span>
                                    <span class="font-medium text-slate-500">{{ $adj->before_damaged }}</span>
                                    <span class="material-symbols-outlined text-[12px] text-outline">arrow_forward</span>
                                    <span class="font-bold text-amber-700">{{ $adj->after_damaged }}</span>
                                </div>
                            </td>

                            <!-- Eksekutor -->
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <div class="font-medium text-on-surface text-sm">{{ $adj->user->name ?? 'Sistem' }}</div>
                                <div class="text-[11px] text-on-surface-variant capitalize">{{ $adj->user->role ?? '-' }}</div>
                            </td>

                            <!-- Alasan / Catatan Tindakan -->
                            <td class="px-6 py-3.5 max-w-xs">
                                <p class="text-xs text-on-surface bg-surface-container-low p-2 rounded border border-outline-variant/60 italic">
                                    "{{ $adj->reason }}"
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-16 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl text-outline">published_with_changes</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface">Belum ada riwayat mutasi stok</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Data mutasi otomatis tercatat ketika laboran melakukan perbaikan alat rusak atau penyesuaian stok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($adjustments->hasPages())
            <div class="p-4 border-t border-outline-variant bg-surface-container-lowest">
                {{ $adjustments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
