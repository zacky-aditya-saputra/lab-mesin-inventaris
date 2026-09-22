@extends('layouts.admin', ['title' => 'Klien Lab / Data Peminjam', 'pageTitle' => 'Klien Lab'])

@section('content')
<div class="space-y-6">
    <!-- Action Topbar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex-1 min-w-0">
            <h2 class="font-headline-sm text-headline-sm text-on-surface break-words sm:whitespace-normal">Daftar Klien Lab / Peminjam</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Data peminjam alat beserta kontak WhatsApp dan riwayat peminjaman.</p>
        </div>
        @if (auth()->user()->isKepalaLab())
            <form method="GET" action="{{ route('admin.clients.index') }}" class="flex items-center gap-2.5 flex-shrink-0">
                <div class="relative">
                    <select name="laboratory_id"
                            class="pl-3.5 pr-8 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-xs appearance-none cursor-pointer transition-all">
                        <option value="">🏛️ Semua Laboratorium</option>
                        @foreach ($laboratories as $laboratory)
                            <option value="{{ $laboratory->id }}" @selected(request('laboratory_id') == $laboratory->id)>🔬 {{ $laboratory->name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-base">expand_more</span>
                </div>
                <button type="submit"
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white text-sm font-medium tracking-tight shadow-sm hover:shadow ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                    <span class="material-symbols-outlined text-base">filter_alt</span>
                    Filter
                </button>
            </form>
        @endif
    </div>

    <!-- Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <div class="table-container overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="py-3.5 px-4 font-semibold w-1/3">Nama Peminjam</th>
                        <th class="py-3.5 px-4 font-semibold w-44">No. WhatsApp</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Asal Prodi / Instansi</th>
                        <th class="py-3.5 px-4 font-semibold w-36">Dokumen KTM/KTP</th>
                        <th class="py-3.5 px-4 font-semibold text-center w-28">Total Riwayat Pinjam</th>
                    </tr>
                </thead>
                <tbody class="font-body-md text-body-md divide-y divide-outline-variant">
                    @forelse ($clients as $client)
                        @php
                            $waNumber = preg_replace('/\D/', '', (string) $client->phone_number);
                            if ($waNumber !== '' && str_starts_with($waNumber, '0')) {
                                $waNumber = '62'.substr($waNumber, 1);
                            }
                            $waUrl = $waNumber !== '' ? 'https://wa.me/'.$waNumber : null;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 font-medium text-on-surface">
                                <div class="flex items-center gap-2">
                                    <div class="w-9 h-9 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($client->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="block truncate">{{ $client->name }}</span>
                                        <span class="block font-caption-xs text-caption-xs text-on-surface-variant truncate">{{ $client->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-on-surface-variant">
                                @if ($waUrl)
                                    <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300/70 bg-emerald-50/80 hover:bg-emerald-100 text-emerald-800 font-medium text-xs shadow-xs active:scale-95 transition-all duration-150">
                                        <span class="material-symbols-outlined text-sm text-emerald-600">chat</span>
                                        {{ $client->phone_number }}
                                    </a>
                                @else
                                    <span class="text-outline">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-on-surface-variant">{{ $client->institution ?? '-' }}</td>
                            <td class="py-3.5 px-4">
                                @if ($client->identity_card_path)
                                    <a href="{{ asset('storage/'.$client->identity_card_path) }}" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1.5 text-primary hover:underline font-label-sm text-label-sm">
                                        <span class="material-symbols-outlined text-base">visibility</span>
                                        Lihat Dokumen
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs bg-surface-container text-outline">
                                        Belum Ada
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs bg-secondary-container text-on-secondary-container">
                                    {{ $client->loan_requests_count ?? 0 }}x Pinjam
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl text-outline">group</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface">Belum ada data peminjam</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Klien akan terdaftar otomatis setelah mengajukan peminjaman alat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($clients->hasPages())
            <div class="p-4 border-t border-outline-variant bg-surface-container-lowest">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
