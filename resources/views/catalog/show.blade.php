@extends('layouts.catalog', ['title' => $tool->name])

@section('content')
<div class="pt-8 pb-16 px-gutter-md max-w-container-max mx-auto w-full">
    <!-- Breadcrumb & Back -->
    <div class="mb-6">
        <a href="{{ route('catalog.index') }}"
           class="inline-flex items-center gap-1.5 text-on-surface-variant hover:text-primary transition-colors font-label-md text-label-md">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Kembali ke Katalog
        </a>
    </div>

    <!-- Detail Card -->
    <div class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 p-6 md:p-8">
            <!-- Left: Image -->
            <div class="aspect-square rounded-xl border border-outline-variant bg-surface-container flex items-center justify-center overflow-hidden p-4">
                @if ($tool->image_path)
                    <img src="{{ asset('storage/'.$tool->image_path) }}" alt="{{ $tool->name }}"
                         class="w-full h-full object-contain mix-blend-multiply"/>
                @else
                    <div class="flex flex-col items-center justify-center text-outline">
                        <span class="material-symbols-outlined text-6xl">precision_manufacturing</span>
                    </div>
                @endif
            </div>

            <!-- Right: Info -->
            <div class="flex flex-col">
                <div class="mb-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-secondary-container px-2.5 py-1 rounded font-badge-xs text-badge-xs text-on-secondary-container font-mono font-semibold">{{ $tool->code }}</span>
                        <span class="bg-surface-container px-2.5 py-1 rounded font-badge-xs text-badge-xs text-secondary border border-outline-variant">{{ $tool->category?->name }}</span>
                    </div>
                    <h1 class="font-headline-md text-headline-md text-on-surface font-bold mb-2">{{ $tool->name }}</h1>
                </div>

                <!-- Technical Specs -->
                <div class="mb-6">
                    <h2 class="font-label-md text-label-md text-on-surface mb-2 font-semibold border-b border-outline-variant pb-1.5">Deskripsi &amp; Spesifikasi</h2>
                    <div class="font-body-md text-body-md text-on-surface-variant whitespace-pre-line text-sm">{{ $tool->specification ?? 'Tidak ada spesifikasi khusus.' }}</div>
                </div>

                <!-- Availability Box -->
                <div class="bg-surface-container-low rounded-xl border border-outline-variant p-4 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">Stok Fisik Total</span>
                            <span class="font-title-sm text-title-sm text-on-surface font-bold">{{ $tool->total_stock }} Unit</span>
                        </div>
                        <div>
                            <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">Tersedia untuk Diajukan</span>
                            <div class="flex items-center gap-2">
                                <span class="font-title-sm text-title-sm font-bold text-status-success-text">{{ $tool->available_stock }} Unit</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-badge-xs text-badge-xs {{ $availabilityStatus === 'Tersedia' ? 'bg-status-success-bg text-status-success-text' : ($availabilityStatus === 'Stok Terbatas' ? 'bg-status-pending-bg text-status-pending-text' : 'bg-slate-200 text-slate-600') }}">
                                    {{ $availabilityStatus }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-auto pt-4 border-t border-outline-variant flex justify-end gap-3">
                    <a href="{{ route('catalog.index') }}"
                       class="px-5 py-2.5 rounded-lg font-label-md text-label-md text-secondary bg-surface-container hover:bg-surface-container-highest transition-colors border border-outline-variant">
                        Tutup
                    </a>
                    <a href="{{ route('loans.create', ['tool_id' => $tool->id]) }}"
                       class="px-5 py-2.5 rounded-lg font-label-md text-label-md text-on-primary bg-primary hover:bg-primary-container transition-colors shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                        Ajukan Peminjaman
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
