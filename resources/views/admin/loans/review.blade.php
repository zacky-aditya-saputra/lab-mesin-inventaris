@extends('layouts.admin', ['title' => 'Review Tiket ' . $loan->ticket_number, 'pageTitle' => 'Review Tiket Peminjaman'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('loans.index') }}" class="inline-flex items-center gap-1.5 text-on-surface-variant hover:text-primary transition-colors font-label-md text-label-md">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Kembali ke Daftar Tiket
        </a>
        <div class="flex items-center gap-2">
            <span class="font-mono font-bold text-lg text-primary">{{ $loan->ticket_number }}</span>
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
            <span class="px-3 py-1 rounded-full font-badge-xs text-badge-xs border font-bold {{ $statusClasses[$loan->status] ?? 'bg-surface-container text-on-surface' }}">
                {{ $loan->status }}
            </span>
        </div>
    </div>

    <!-- Student Info & KTM Preview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Student Details Card -->
        <div class="md:col-span-2 bg-surface-container-lowest rounded-xl p-6 border border-outline-variant shadow-sm space-y-4">
            <h3 class="font-title-sm text-title-sm text-on-surface font-bold border-b border-outline-variant pb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">badge</span>
                Informasi Peminjam
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 font-body-md text-body-md">
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Nama Lengkap</span>
                    <p class="font-semibold text-on-surface">{{ $loan->user->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">NIM / NIP</span>
                    <p class="font-mono text-on-surface font-semibold">{{ $loan->user->identity_number ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Email</span>
                    <p class="text-on-surface">{{ $loan->user->email ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">No. HP / WhatsApp</span>
                    <p class="text-on-surface">
                        @if($loan->user->phone_number)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $loan->user->phone_number) }}" target="_blank" 
                               class="text-status-success-text font-medium hover:underline inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">chat</span>
                                {{ $loan->user->phone_number }}
                            </a>
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Periode Peminjaman</span>
                    <p class="font-semibold text-on-surface">{{ $loan->start_date?->format('d M Y') }} s.d. {{ $loan->end_date?->format('d M Y') }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Waktu Pengajuan</span>
                    <p class="text-on-surface">{{ $loan->created_at?->format('d M Y, H:i') }} WIB</p>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-on-surface-variant text-caption-xs font-medium">Keperluan / Keterangan</span>
                    <p class="text-on-surface mt-1 bg-surface-container-low p-3 rounded-lg border border-outline-variant">{{ $loan->purpose }}</p>
                </div>
            </div>
        </div>

        <!-- KTM Preview Card -->
        <div class="bg-surface-container-lowest rounded-xl p-6 border border-outline-variant shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-title-sm text-title-sm text-on-surface font-bold border-b border-outline-variant pb-3 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">credit_card</span>
                    Berkas KTM
                </h3>
                @if($loan->identity_card_path)
                    <div class="aspect-[4/3] rounded-lg overflow-hidden border border-outline-variant bg-surface-container flex items-center justify-center relative group">
                        <img src="{{ asset('storage/' . $loan->identity_card_path) }}" alt="KTM Mahasiswa" class="w-full h-full object-cover">
                        <a href="{{ asset('storage/' . $loan->identity_card_path) }}" target="_blank" 
                           class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white font-label-md text-label-md gap-1">
                            <span class="material-symbols-outlined text-base">fullscreen</span>
                            Lihat Dokumen Asli
                        </a>
                    </div>
                @else
                    <div class="p-8 text-center text-on-surface-variant border-2 border-dashed border-outline-variant rounded-lg">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">image_not_supported</span>
                        <p class="font-caption-xs text-caption-xs">Tidak ada berkas KTM terlampir.</p>
                    </div>
                @endif
            </div>
            @if($loan->identity_card_path)
                <a href="{{ asset('storage/' . $loan->identity_card_path) }}" target="_blank" 
                   class="mt-4 w-full py-2 bg-surface-container-high hover:bg-surface-dim text-on-surface font-label-md text-label-md rounded-lg text-center border border-outline-variant transition-colors block">
                    Buka Berkas Penuh
                </a>
            @endif
        </div>
    </div>

    <!-- Items & Action Section based on State Machine -->
    <div class="bg-surface-container-lowest rounded-xl p-6 md:p-8 border border-outline-variant shadow-sm space-y-6">
        <h3 class="font-title-sm text-title-sm text-on-surface font-bold border-b border-outline-variant pb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">inventory</span>
            Daftar Alat &amp; Aksi Verifikasi
        </h3>

        <!-- Form for PENDING status (Partial Approval / Full Approval / Reject) -->
        @if ($loan->status === 'PENDING')
            <form action="{{ route('loans.update-status', $loan) }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="overflow-x-auto rounded-lg border border-outline-variant">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container text-on-surface font-label-md text-label-md">
                            <tr>
                                <th class="py-3 px-4">Nama Alat</th>
                                <th class="py-3 px-4 font-mono text-xs">Kode</th>
                                <th class="py-3 px-4 text-center">Stok Tersedia</th>
                                <th class="py-3 px-4 text-center">Jml Diminta</th>
                                <th class="py-3 px-4 text-center w-36">Jml Disetujui</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant font-body-md text-body-md">
                            @foreach ($loan->items as $index => $item)
                                <tr>
                                    <td class="py-3.5 px-4 font-medium">{{ $item->tool->name ?? 'Alat' }}</td>
                                    <td class="py-3.5 px-4 font-mono text-xs text-on-surface-variant">{{ $item->tool->code ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="font-semibold {{ ($item->tool->available_stock ?? 0) >= $item->requested_quantity ? 'text-status-success-text' : 'text-error' }}">
                                            {{ $item->tool->available_stock ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold">{{ $item->requested_quantity }}</td>
                                    <td class="py-3.5 px-4 text-center">
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <input type="number" 
                                               name="items[{{ $index }}][approved_quantity]" 
                                               value="{{ old('items.'.$index.'.approved_quantity', min($item->requested_quantity, $item->tool->available_stock ?? 0)) }}"
                                               min="0" 
                                               max="{{ $item->requested_quantity }}"
                                               required
                                               class="w-24 h-10 text-center font-bold rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-primary focus:border-primary"/>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Admin Notes -->
                <div>
                    <label for="admin_notes" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Catatan Verifikasi Admin <span class="text-on-surface-variant font-normal">(Wajib jika menolak atau menyetujui sebagian)</span>
                    </label>
                    <textarea id="admin_notes" name="admin_notes" rows="3"
                              placeholder="Tuliskan alasan penolakan, instruksi pengambilan alat, atau catatan penting..."
                              class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-primary focus:border-primary font-body-md text-body-md"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-outline-variant flex flex-col sm:flex-row justify-end gap-3">
                    <button type="submit" name="action" value="reject" 
                            onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan ini?')"
                            class="px-5 py-2.5 bg-error hover:bg-[#991b1b] text-on-error font-label-md text-label-md rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">cancel</span>
                        Tolak Pengajuan
                    </button>
                    <button type="submit" name="action" value="approve" 
                            class="px-6 py-2.5 bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2 font-semibold">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        Setujui Permohonan (Reservasi Stok)
                    </button>
                </div>
            </form>

        <!-- Form for APPROVED / PARTIALLY_APPROVED status (Confirm Handover to ON_LOAN) -->
        @elseif (in_array($loan->status, ['APPROVED', 'PARTIALLY_APPROVED']))
            <div class="bg-status-approved-bg p-4 rounded-xl border border-status-approved-text/20 mb-6 flex items-start gap-3">
                <span class="material-symbols-outlined text-status-approved-text mt-0.5">info</span>
                <div>
                    <h4 class="font-label-md text-label-md font-bold text-status-approved-text">Alat Sudah Disetujui</h4>
                    <p class="font-body-md text-body-md text-status-approved-text text-sm">
                        Stok telah direservasi. Konfirmasikan serah terima fisik saat mahasiswa mengambil alat di ruang laboratorium.
                    </p>
                </div>
            </div>

            <form action="{{ route('loans.update-status', $loan) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="handover">
                
                <div class="flex justify-end pt-4 border-t border-outline-variant">
                    <button type="submit" 
                            onclick="return confirm('Konfirmasikan bahwa alat telah diserahkan secara fisik kepada mahasiswa?')"
                            class="px-6 py-3 bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md rounded-lg shadow-sm transition-colors flex items-center gap-2 font-bold">
                        <span class="material-symbols-outlined text-base">handshake</span>
                        Konfirmasi Serah Terima Fisik (Ubah Status ke ON_LOAN)
                    </button>
                </div>
            </form>

        <!-- Form for ON_LOAN / OVERDUE status (Confirm Return with condition Baik/Rusak/Hilang) -->
        @elseif (in_array($loan->status, ['ON_LOAN', 'OVERDUE']))
            <form action="{{ route('loans.return', $loan) }}" method="POST" class="space-y-6">
                @csrf

                <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant">
                    <h4 class="font-label-md text-label-md font-bold text-on-surface mb-1">Pemeriksaan Kondisi Pengembalian</h4>
                    <p class="font-body-md text-body-md text-on-surface-variant text-sm">
                        Pilih kondisi fisik tiap unit alat yang dikembalikan sebelum menyelesaikan tiket.
                    </p>
                </div>

                <div class="overflow-x-auto rounded-lg border border-outline-variant">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-surface-container text-on-surface font-label-md text-label-md">
                            <tr>
                                <th class="py-3 px-4">Nama Alat</th>
                                <th class="py-3 px-4 text-center">Jumlah Dipinjam</th>
                                <th class="py-3 px-4 text-center w-52">Kondisi Pengembalian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant font-body-md text-body-md">
                            @foreach ($loan->items as $index => $item)
                                <tr>
                                    <td class="py-3.5 px-4 font-medium">{{ $item->tool->name ?? 'Alat' }}</td>
                                    <td class="py-3.5 px-4 text-center font-bold">{{ $item->approved_quantity ?? $item->requested_quantity }}</td>
                                    <td class="py-3.5 px-4 text-center">
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <select name="items[{{ $index }}][condition]" required
                                                class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-primary focus:border-primary font-body-md text-body-md">
                                            <option value="Baik" selected>Baik (Stok Pulih)</option>
                                            <option value="Rusak">Rusak (Perlu Perbaikan)</option>
                                            <option value="Hilang">Hilang (Aset Berkurang)</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    <label for="return_notes" class="block font-label-md text-label-md text-on-surface mb-1.5">Catatan Pengembalian</label>
                    <textarea id="return_notes" name="admin_notes" rows="2"
                              placeholder="Catatan kondisi saat dikembalikan..."
                              class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-primary focus:border-primary font-body-md text-body-md"></textarea>
                </div>

                <div class="flex justify-end pt-4 border-t border-outline-variant">
                    <button type="submit" 
                            onclick="return confirm('Konfirmasikan pengembalian alat ini?')"
                            class="px-6 py-3 bg-status-success-text hover:opacity-90 text-white font-label-md text-label-md rounded-lg shadow-sm transition-opacity flex items-center gap-2 font-bold">
                        <span class="material-symbols-outlined text-base">check_circle</span>
                        Konfirmasi Pengembalian Fisik Selesai (RETURNED)
                    </button>
                </div>
            </form>

        <!-- Completed / RETURNED or REJECTED state view -->
        @else
            <div class="overflow-x-auto rounded-lg border border-outline-variant">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-surface-container text-on-surface font-label-md text-label-md">
                        <tr>
                            <th class="py-3 px-4">Nama Alat</th>
                            <th class="py-3 px-4 text-center">Jml Disetujui</th>
                            <th class="py-3 px-4 text-center">Kondisi Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant font-body-md text-body-md">
                        @foreach ($loan->items as $item)
                            <tr>
                                <td class="py-3.5 px-4 font-medium">{{ $item->tool->name ?? 'Alat' }}</td>
                                <td class="py-3.5 px-4 text-center font-bold">{{ $item->approved_quantity ?? 0 }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded font-badge-xs text-badge-xs font-semibold
                                          {{ ($item->return_condition ?? 'Baik') === 'Baik' ? 'bg-status-success-bg text-status-success-text' : 'bg-error-container text-error' }}">
                                        {{ $item->return_condition ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($loan->admin_notes)
                <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant">
                    <span class="font-caption-xs text-caption-xs text-on-surface-variant font-medium">Catatan Akhir Admin:</span>
                    <p class="font-body-md text-body-md text-on-surface mt-1 italic">{{ $loan->admin_notes }}</p>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
