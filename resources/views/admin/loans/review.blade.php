@extends('layouts.admin', ['title' => 'Review Tiket ' . $loan->ticket_number, 'pageTitle' => 'Review Tiket Peminjaman'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.loans.index') }}" class="inline-flex items-center gap-1.5 text-on-surface-variant hover:text-primary transition-colors font-label-md text-label-md">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Kembali ke Daftar Tiket
        </a>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-secondary-container text-on-secondary-container font-badge-xs text-badge-xs font-semibold">
                <span class="material-symbols-outlined text-xs">science</span>
                {{ $loan->laboratory?->name ?? '-' }}
            </span>
            <span class="font-mono font-bold text-lg text-primary">{{ $loan->ticket_number }}</span>
            @php
                $statusClasses = [
                    'PENDING' => 'bg-status-pending-bg text-status-pending-text border-status-pending-text/20',
                    'APPROVED' => 'bg-status-approved-bg text-status-approved-text border-status-approved-text/20',
                    'PARTIALLY_APPROVED' => 'bg-status-partial-bg text-status-partial-text border-status-partial-text/20',
                    'REJECTED' => 'bg-status-rejected-bg text-status-rejected-text border-status-rejected-text/20',
                    'CANCELLED' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800',
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

    <!-- Borrower Info -->
    <div class="bg-surface-container-lowest rounded-xl p-6 border border-outline-variant shadow-sm space-y-4">
        <div class="flex flex-wrap items-center justify-between border-b border-outline-variant pb-3 gap-2">
                <h3 class="font-title-sm text-title-sm text-on-surface font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">badge</span>
                    Informasi Peminjam
                </h3>
                @if ($loan->borrower_name)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-mono text-[11px] font-semibold border border-slate-200">
                        <span class="material-symbols-outlined text-xs text-emerald-600">verified</span>
                        Snapshot Permanen
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-body-md text-body-md">
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Nama Lengkap</span>
                    <p class="font-semibold text-on-surface">{{ $loan->borrower_name ?: ($loan->user->name ?? '-') }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Nomor Identitas (NIM / NIK)</span>
                    <p class="font-mono text-on-surface font-semibold">{{ $loan->borrower_identity_number ?: ($loan->user->identity_number ?? '-') }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Asal / Instansi / Domisili</span>
                    <p class="font-semibold text-on-surface">{{ $loan->borrower_institution ?: ($loan->user->institution ?? '-') }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Email Akun</span>
                    <p class="text-on-surface truncate">{{ $loan->user->email ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">No. HP / WhatsApp</span>
                    <p class="text-on-surface flex items-center gap-2 flex-wrap">
                        @php
                            $contactPhone = $loan->borrower_phone ?: ($loan->user->phone_number ?? null);
                        @endphp
                        @if($contactPhone)
                            @php
                                $whatsappNumber = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', (string) $contactPhone));
                                $borrowerName = $loan->borrower_name ?: ($loan->user->name ?? 'Peminjam');
                                $labName = $loan->laboratory?->name ?? 'Laboratorium Teknik Mesin';
                                $ticketNum = $loan->ticket_number;
                                $startDate = $loan->start_date?->translatedFormat('d F Y') ?? $loan->start_date?->format('d/m/Y');
                                $endDate = $loan->end_date?->translatedFormat('d F Y') ?? $loan->end_date?->format('d/m/Y');

                                $toolLines = [];
                                foreach ($loan->items as $i => $item) {
                                    $qty = $item->approved_quantity ?: $item->requested_quantity;
                                    $toolName = $item->tool?->name ?? 'Alat';
                                    $toolLines[] = ($i + 1).". {$toolName} - {$qty} unit";
                                }
                                $toolsListText = implode("\n", $toolLines);

                                $whatsappMessage = "Halo {$borrowerName}, kami dari {$labName} - Prodi Teknik Mesin - Fakultas Teknik Unwahas.\n\n"
                                    ."Mengenai pengajuan tiket peminjaman {$ticketNum} untuk alat:\n"
                                    ."{$toolsListText}\n\n"
                                    ."Periode: {$startDate} s.d. {$endDate}.\n"
                                    ."Mohon konfirmasi kesiapan pengambilan alat di laboratorium. Terima kasih.";
                            @endphp
                            <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener"
                               class="text-status-success-text font-medium hover:underline inline-flex items-center gap-1 font-mono">
                                <span class="material-symbols-outlined text-xs">chat</span>
                                {{ $contactPhone }}
                            </a>
                            <a href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode($whatsappMessage) }}" target="_blank" rel="noopener"
                               title="Hubungi peminjam via WhatsApp Web"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-status-success-bg text-status-success-text font-badge-xs text-badge-xs font-semibold hover:opacity-80 transition-opacity">
                                <span class="material-symbols-outlined text-sm">chat</span>
                                WhatsApp
                            </a>
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Periode Peminjaman</span>
                    <p class="font-mono font-semibold text-on-surface">{{ $loan->start_date?->format('d M Y') }} s.d. {{ $loan->end_date?->format('d M Y') }}</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Waktu Pengajuan</span>
                    <p class="font-mono text-on-surface">{{ $loan->created_at?->format('d M Y, H:i') }} WIB</p>
                </div>
                <div>
                    <span class="text-on-surface-variant text-caption-xs font-medium">Waktu Persetujuan</span>
                    <p class="font-mono text-on-surface">{{ $loan->approved_at ? $loan->approved_at->format('d M Y, H:i').' WIB' : '-' }}</p>
                </div>
                @if ($loan->handed_over_at)
                    <div>
                        <span class="text-on-surface-variant text-caption-xs font-medium">Waktu Serah Terima</span>
                        <p class="font-mono text-on-surface">{{ $loan->handed_over_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                @endif
                @if ($loan->returned_at)
                    <div>
                        <span class="text-on-surface-variant text-caption-xs font-medium">Waktu Pengembalian</span>
                        <p class="font-mono text-on-surface">{{ $loan->returned_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                @endif
                @if ($loan->cancelled_at)
                    <div>
                        <span class="text-on-surface-variant text-caption-xs font-medium text-rose-600">Waktu Pembatalan</span>
                        <p class="font-mono text-rose-600 font-semibold">{{ $loan->cancelled_at->format('d M Y, H:i') }} WIB</p>
                    </div>
                @endif
                <div class="sm:col-span-2 lg:col-span-4">
                    <span class="text-on-surface-variant text-caption-xs font-medium">Keperluan / Keterangan</span>
                    <p class="text-on-surface mt-1 bg-surface-container-low p-3 rounded-lg border border-outline-variant">{{ $loan->purpose }}</p>
                </div>
            </div>
        </div>

    <!-- Supporting Documents Preview Grid (2 Kolom Berdampingan) -->
    @php
        $isImageDocument = fn (?string $path): bool => $path !== null
            && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kolom 1: Berkas Identitas Profil (KTM / KTP) -->
        <div class="bg-surface-container-lowest rounded-xl p-6 border border-outline-variant shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-title-sm text-title-sm text-on-surface font-bold border-b border-outline-variant pb-3 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">credit_card</span>
                    Berkas Identitas (KTM / KTP)
                </h3>
                @if ($loan->identity_card_path && $isImageDocument($loan->identity_card_path))
                    <div class="h-56 w-full rounded-lg overflow-hidden border border-outline-variant bg-slate-900/5 flex items-center justify-center relative group p-2">
                        <img src="{{ route('documents.identity', $loan->user) }}" alt="Dokumen Identitas Peminjam"
                             onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')"
                             class="w-full h-full object-contain">
                        <div class="hidden w-full h-full flex-col items-center justify-center text-slate-400 select-none">
                            <span class="material-symbols-outlined text-4xl text-blue-600/70">badge</span>
                            <span class="text-[11px] font-mono font-medium uppercase tracking-wider mt-2">Dokumen tidak tersedia</span>
                        </div>
                        <a href="{{ route('documents.identity', $loan->user) }}" target="_blank"
                           class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white font-label-md text-label-md gap-1">
                            <span class="material-symbols-outlined text-base">fullscreen</span>
                            Lihat Dokumen Asli
                        </a>
                    </div>
                @elseif ($loan->identity_card_path)
                    <a href="{{ route('documents.identity', $loan->user) }}" target="_blank" rel="noopener"
                       class="h-56 w-full rounded-lg border border-outline-variant bg-surface-container flex flex-col items-center justify-center gap-2 hover:border-primary/50 transition-colors group p-4">
                        <span class="material-symbols-outlined text-5xl text-error" style="font-variation-settings: 'FILL' 1;">picture_as_pdf</span>
                        <span class="font-label-md text-label-md text-on-surface font-semibold truncate max-w-xs text-center px-2">{{ basename($loan->identity_card_path) }}</span>
                        <span class="font-caption-xs text-caption-xs text-primary flex items-center gap-1 font-semibold">Buka Dokumen PDF <span class="material-symbols-outlined text-sm">open_in_new</span></span>
                    </a>
                @else
                    <div class="h-56 w-full text-center text-on-surface-variant border-2 border-dashed border-outline-variant rounded-lg flex flex-col items-center justify-center p-6">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">image_not_supported</span>
                        <p class="font-caption-xs text-caption-xs">Tidak ada berkas identitas terlampir.</p>
                    </div>
                @endif
            </div>
            @if ($loan->identity_card_path)
                <a href="{{ route('documents.identity', $loan->user) }}" target="_blank"
                   class="mt-4 w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-label-md text-label-md rounded-lg text-center border border-slate-200 transition-colors inline-flex items-center justify-center gap-1.5 font-semibold">
                    <span class="material-symbols-outlined text-base">visibility</span>
                    <span>Buka Berkas Penuh</span>
                </a>
            @endif
        </div>

        <!-- Kolom 2: Surat Peminjaman Resmi -->
        <div class="bg-surface-container-lowest rounded-xl p-6 border border-outline-variant shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-title-sm text-title-sm text-on-surface font-bold border-b border-outline-variant pb-3 flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-primary">description</span>
                    Surat Peminjaman Resmi
                </h3>
                @if ($loan->loan_letter_path && $isImageDocument($loan->loan_letter_path))
                    <div class="h-56 w-full rounded-lg overflow-hidden border border-outline-variant bg-slate-900/5 flex items-center justify-center relative group p-2">
                        <img src="{{ route('documents.loan-letter', $loan) }}" alt="Surat Peminjaman Resmi"
                             onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')"
                             class="w-full h-full object-contain">
                        <div class="hidden w-full h-full flex-col items-center justify-center text-slate-400 select-none">
                            <span class="material-symbols-outlined text-4xl text-blue-600/70">draft</span>
                            <span class="text-[11px] font-mono font-medium uppercase tracking-wider mt-2">Surat tidak tersedia</span>
                        </div>
                        <a href="{{ route('documents.loan-letter', $loan) }}" target="_blank"
                           class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white font-label-md text-label-md gap-1">
                            <span class="material-symbols-outlined text-base">fullscreen</span>
                            Lihat Dokumen Asli
                        </a>
                    </div>
                @elseif ($loan->loan_letter_path)
                    <a href="{{ route('documents.loan-letter', $loan) }}" target="_blank" rel="noopener"
                       class="h-56 w-full rounded-lg border border-outline-variant bg-surface-container flex flex-col items-center justify-center gap-2 hover:border-primary/50 transition-colors group p-4">
                        <span class="material-symbols-outlined text-5xl text-primary" style="font-variation-settings: 'FILL' 1;">picture_as_pdf</span>
                        <span class="font-label-md text-label-md text-on-surface font-semibold truncate max-w-xs text-center px-2">{{ basename($loan->loan_letter_path) }}</span>
                        <span class="font-caption-xs text-caption-xs text-primary flex items-center gap-1 font-semibold">Buka Dokumen PDF <span class="material-symbols-outlined text-sm">open_in_new</span></span>
                    </a>
                @else
                    <div class="h-56 w-full text-center text-on-surface-variant border-2 border-dashed border-outline-variant rounded-lg flex flex-col items-center justify-center p-6">
                        <span class="material-symbols-outlined text-4xl text-outline mb-2">draft</span>
                        <p class="font-caption-xs text-caption-xs">Tidak ada surat peminjaman terlampir.</p>
                    </div>
                @endif
            </div>
            @if ($loan->loan_letter_path)
                <a href="{{ route('documents.loan-letter', $loan) }}" target="_blank"
                   class="mt-4 w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-label-md text-label-md rounded-lg text-center border border-slate-200 transition-colors inline-flex items-center justify-center gap-1.5 font-semibold">
                    <span class="material-symbols-outlined text-base">visibility</span>
                    <span>Buka Surat Penuh</span>
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
            <form action="{{ route('admin.loans.update-status', $loan) }}" method="POST" class="space-y-6">
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
                                                       class="w-24 h-10 text-center font-bold rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"/>
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
                              class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-body-md text-body-md transition-colors"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-outline-variant flex flex-col sm:flex-row justify-end gap-3">
                    <button type="submit" name="action" value="reject" 
                            onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan ini?')"
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-5 py-2.5 bg-gradient-to-b from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-on-error font-label-md text-label-md rounded-lg shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                        <span class="material-symbols-outlined text-sm">cancel</span>
                        Tolak Pengajuan
                    </button>
                    <button type="submit" name="action" value="approve" 
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-6 py-2.5 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-label-md text-label-md rounded-lg shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer font-semibold">
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

            <form action="{{ route('admin.loans.update-status', $loan) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" value="handover">
                
                <div class="flex justify-end pt-4 border-t border-outline-variant">
                    <button type="submit" 
                            onclick="return confirm('Konfirmasikan bahwa alat telah diserahkan secara fisik kepada mahasiswa?')"
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-6 py-3 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-label-md text-label-md rounded-lg shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer font-semibold">
                        <span class="material-symbols-outlined text-base">handshake</span>
                        Konfirmasi Serah Terima Fisik (Ubah Status ke ON_LOAN)
                    </button>
                </div>
            </form>

        <!-- Form for ON_LOAN / OVERDUE status (Confirm Return with condition Baik/Rusak/Hilang) -->
        @elseif (in_array($loan->status, ['ON_LOAN', 'OVERDUE']))
            <form action="{{ route('admin.loans.return', $loan) }}" method="POST" class="space-y-6">
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
                                <th class="py-3 px-4 text-center">Jumlah Disetujui</th>
                                <th class="py-3 px-4 text-center w-80">Rincian Kondisi Unit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant font-body-md text-body-md">
                            @foreach ($loan->items as $index => $item)
                                @php
                                    $approvedQty = $item->approved_quantity ?? $item->requested_quantity;
                                @endphp
                                <tr x-data="{
                                    approved: {{ $approvedQty }},
                                    good: {{ $approvedQty }},
                                    damaged: 0,
                                    missing: 0,
                                    get total() { return Number(this.good || 0) + Number(this.damaged || 0) + Number(this.missing || 0); },
                                    get isValid() { return this.total === this.approved; }
                                }">
                                    <td class="py-3.5 px-4 font-medium">
                                        <div class="text-on-surface font-semibold">{{ $item->tool->name ?? 'Alat' }}</div>
                                        <span class="text-xs text-on-surface-variant font-mono">{{ $item->tool->code ?? '' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-base">{{ $approvedQty }}</td>
                                    <td class="py-3.5 px-4">
                                        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <div class="grid grid-cols-3 gap-2">
                                            <div>
                                                <label class="block text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 mb-1">Bagus</label>
                                                <input type="number" min="0" :max="approved" x-model.number="good" name="items[{{ $index }}][returned_good_qty]" required
                                                       class="w-full h-9 px-2 text-center rounded-lg border border-outline-variant bg-surface-container-lowest font-mono text-sm focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-amber-700 dark:text-amber-400 mb-1">Rusak</label>
                                                <input type="number" min="0" :max="approved" x-model.number="damaged" name="items[{{ $index }}][returned_damaged_qty]" required
                                                       class="w-full h-9 px-2 text-center rounded-lg border border-outline-variant bg-surface-container-lowest font-mono text-sm focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            <div>
                                                <label class="block text-[11px] font-semibold text-red-700 dark:text-red-400 mb-1">Hilang</label>
                                                <input type="number" min="0" :max="approved" x-model.number="missing" name="items[{{ $index }}][returned_missing_qty]" required
                                                       class="w-full h-9 px-2 text-center rounded-lg border border-outline-variant bg-surface-container-lowest font-mono text-sm focus:ring-2 focus:ring-blue-500">
                                            </div>
                                        </div>
                                        <div x-show="!isValid" class="text-xs text-red-600 mt-1 font-medium">
                                            Total (<span x-text="total"></span>) harus pas <span x-text="approved"></span> unit.
                                        </div>
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
                              class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-body-md text-body-md transition-colors"></textarea>
                </div>

                <div class="flex justify-end pt-4 border-t border-outline-variant">
                    <button type="submit" 
                            onclick="return confirm('Konfirmasikan pengembalian alat ini?')"
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-6 py-3 bg-gradient-to-b from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-label-md text-label-md rounded-lg shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer font-semibold">
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
                                <td class="py-3.5 px-4 font-medium">
                                    <div class="text-on-surface font-semibold">{{ $item->tool->name ?? 'Alat' }}</div>
                                    <span class="text-xs text-on-surface-variant font-mono">{{ $item->tool->code ?? '' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold">{{ $item->approved_quantity ?? 0 }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($item->returned_good_qty > 0 || $item->returned_damaged_qty > 0 || $item->returned_missing_qty > 0)
                                        <div class="inline-flex items-center gap-1.5 font-mono text-xs">
                                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 font-semibold border border-emerald-200 dark:border-emerald-800">{{ $item->returned_good_qty }} Bagus</span>
                                            @if ($item->returned_damaged_qty > 0)
                                                <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 font-semibold border border-amber-200 dark:border-amber-800">{{ $item->returned_damaged_qty }} Rusak</span>
                                            @endif
                                            @if ($item->returned_missing_qty > 0)
                                                <span class="px-2 py-0.5 rounded bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-400 font-semibold border border-red-200 dark:border-red-800">{{ $item->returned_missing_qty }} Hilang</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded font-badge-xs text-badge-xs font-semibold
                                              {{ ($item->return_condition ?? 'Baik') === 'Baik' ? 'bg-status-success-bg text-status-success-text' : 'bg-error-container text-error' }}">
                                            {{ $item->return_condition ?? '-' }}
                                        </span>
                                    @endif
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

    <!-- Audit Trail / Jejak Audit Transaksi -->
    @if ($loan->logs && $loan->logs->isNotEmpty())
        <div class="bg-surface-container-lowest rounded-xl p-6 border border-outline-variant shadow-sm space-y-4">
            <h3 class="font-title-sm text-title-sm text-on-surface font-bold border-b border-outline-variant pb-3 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Jejak Audit Status Tiket
                </span>
                <span class="font-mono text-caption-xs text-on-surface-variant">{{ $loan->logs->count() }} Transisi Tercatat</span>
            </h3>

            <div class="space-y-3">
                @foreach ($loan->logs->sortByDesc('id') as $log)
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-surface-container-low/60 border border-outline-variant text-sm">
                        <div class="w-8 h-8 rounded-full bg-surface-container flex items-center justify-center flex-shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-base text-primary">sync_alt</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center justify-between gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-on-surface">{{ $log->user->name ?? 'Sistem / Otomatis' }}</span>
                                    <span class="font-mono text-xs text-on-surface-variant font-normal">({{ $log->action }})</span>
                                </div>
                                <span class="font-mono text-xs text-on-surface-variant">{{ $log->created_at ? $log->created_at->format('d M Y, H:i:s') : '-' }} WIB</span>
                            </div>
                            <div class="mt-1 flex items-center gap-2 text-xs">
                                <span class="px-2 py-0.5 rounded bg-surface-container font-mono text-on-surface-variant">{{ $log->previous_status ?: 'BARU' }}</span>
                                <span class="material-symbols-outlined text-xs text-outline">arrow_forward</span>
                                <span class="px-2 py-0.5 rounded bg-primary/10 text-primary font-mono font-semibold">{{ $log->new_status }}</span>
                            </div>
                            @if ($log->notes)
                                <p class="mt-1 text-xs text-on-surface-variant italic bg-surface-container-lowest p-2 rounded border border-outline-variant/60">
                                    "{{ $log->notes }}"
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
