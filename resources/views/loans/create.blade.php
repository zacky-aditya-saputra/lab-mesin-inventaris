@extends('layouts.catalog', ['title' => 'Formulir Pengajuan Peminjaman'])

@section('content')
<div x-data="loanFormWizard({
    initialToolId: {{ Js::from(request('tool_id')) }},
    availableTools: {{ Js::from($toolsList ?? []) }},
    laboratories: {{ Js::from($laboratories->map(fn ($lab) => ['id' => $lab->id, 'name' => $lab->name])->values()) }},
    profileComplete: {{ Js::from($isProfileComplete) }},
    phoneNumber: {{ Js::from(old('phone_number', $user->phone_number)) }},
    hasIdentityCard: {{ Js::from(filled($user->identity_card_path)) }}
})" class="py-10 px-margin-page md:px-gutter-md max-w-3xl mx-auto w-full">
    <!-- Page Header -->
    <div class="mb-8 text-center">
        <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2 font-bold">Formulir Peminjaman Alat</h1>
        <p class="font-body-md text-body-md text-on-surface-variant">Lengkapi data di bawah ini untuk mengajukan peminjaman inventaris laboratorium.</p>
    </div>

    <!-- Step Indicator -->
    <div class="mb-8 relative max-w-xl mx-auto">
        <div class="absolute top-1/2 left-0 w-full h-1 bg-surface-variant -translate-y-1/2 z-0 rounded-full"></div>
        <div class="absolute top-1/2 left-0 h-1 bg-primary -translate-y-1/2 z-0 rounded-full transition-all duration-300"
             :style="'width: ' + ((currentStep - 1) / 2 * 100) + '%'"></div>

        <div class="relative z-10 flex justify-between">
            <!-- Step 1 Indicator -->
            <div class="flex flex-col items-center cursor-pointer" @click="if(currentStep > 1) currentStep = 1">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-label-md text-label-md shadow-sm border-2 transition-colors font-bold"
                     :class="currentStep >= 1 ? 'bg-primary text-on-primary border-primary' : 'bg-surface text-on-surface-variant border-surface-variant'">
                    1
                </div>
                <span class="mt-2 font-label-md text-label-md" :class="currentStep >= 1 ? 'text-primary font-bold' : 'text-on-surface-variant'">Data Peminjam</span>
            </div>

            <!-- Step 2 Indicator -->
            <div class="flex flex-col items-center cursor-pointer" @click="if(currentStep > 2) currentStep = 2">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-label-md text-label-md shadow-sm border-2 transition-colors font-bold"
                     :class="currentStep >= 2 ? 'bg-primary text-on-primary border-primary' : 'bg-surface text-on-surface-variant border-surface-variant'">
                    2
                </div>
                <span class="mt-2 font-label-md text-label-md" :class="currentStep >= 2 ? 'text-primary font-bold' : 'text-on-surface-variant'">Lab &amp; Alat</span>
            </div>

            <!-- Step 3 Indicator -->
            <div class="flex flex-col items-center">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-label-md text-label-md shadow-sm border-2 transition-colors font-bold"
                     :class="currentStep >= 3 ? 'bg-primary text-on-primary border-primary' : 'bg-surface text-on-surface-variant border-surface-variant'">
                    3
                </div>
                <span class="mt-2 font-label-md text-label-md" :class="currentStep >= 3 ? 'text-primary font-bold' : 'text-on-surface-variant'">Jadwal &amp; Surat</span>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <form action="{{ route('loans.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant p-6 md:p-8">
        @csrf

        <!-- STEP 1: Data Peminjam (Otomatis & Read-Only) -->
        <div x-show="currentStep === 1" x-transition>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 border-b border-surface-variant pb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">person</span>
                Data Peminjam
            </h2>

            <!-- Incomplete Profile Alert -->
            @if (! $isProfileComplete)
                <div class="p-4 mb-6 rounded-lg bg-error-container border border-error/30 text-on-error-container flex flex-col sm:flex-row sm:items-center justify-between gap-3" role="alert">
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-outlined text-error mt-0.5" style="font-variation-settings: 'FILL' 1;">warning</span>
                        <span class="font-body-md text-body-md text-sm">Profil Anda belum lengkap (identitas, No. WhatsApp, atau dokumen KTM/KTP kosong). Anda dapat memperbarui No. WhatsApp dan mengunggah dokumen identitas langsung pada langkah ini.</span>
                    </div>
                    <a href="{{ route('profile.edit') }}"
                       class="shrink-0 bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">edit</span>
                        Lengkapi Profil
                    </a>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Nama -->
                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant">
                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">Nama Lengkap</span>
                    <span class="font-title-sm text-title-sm text-on-surface font-semibold">{{ $user->name }}</span>
                </div>

                <!-- Identitas -->
                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant">
                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">
                        {{ $user->identity_type === 'umum' ? 'NIK KTP' : 'NIM (Nomor Induk Mahasiswa)' }}
                    </span>
                    <span class="font-title-sm text-title-sm text-on-surface font-semibold font-mono">{{ $user->identity_number ?? '-' }}</span>
                </div>

                <!-- No HP (dapat diubah langsung) -->
                <div>
                    <label for="phone_number" class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">No. HP (WhatsApp) <span class="text-error">*</span></label>
                    <input id="phone_number"
                           name="phone_number"
                           type="tel"
                           x-model="phoneNumber"
                           placeholder="08xxxxxxxxxx"
                           autocomplete="tel"
                           class="w-full h-12 px-4 rounded-xl border border-outline-variant bg-surface-container-lowest font-title-sm text-title-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-colors"/>
                    <p class="mt-1 font-caption-xs text-caption-xs text-on-surface-variant">Nomor aktif — dapat diubah langsung jika berganti.</p>
                    @error('phone_number')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prodi / Instansi -->
                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant">
                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">{{ $user->identity_type === 'umum' ? 'Instansi / Domisili' : 'Program Studi / Fakultas' }}</span>
                    <span class="font-title-sm text-title-sm text-on-surface font-semibold">{{ $user->institution ?? '-' }}</span>
                </div>
            </div>

            <!-- Dokumen Identitas Terunggah -->
            <div class="mb-6">
                <span class="block font-label-md text-label-md text-on-surface mb-2">Dokumen Identitas (Tersimpan di Profil)</span>
                @if ($user->identity_card_path)
                    @if (in_array(strtolower(pathinfo($user->identity_card_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']))
                        <div class="inline-flex flex-col gap-2 p-3 rounded-xl border border-outline-variant bg-surface-container-low">
                            <img src="{{ asset('storage/'.$user->identity_card_path) }}" alt="Dokumen identitas"
                                 class="h-40 w-auto rounded-lg object-contain bg-surface-container-lowest"/>
                            <a href="{{ asset('storage/'.$user->identity_card_path) }}" target="_blank" rel="noopener"
                               class="font-label-md text-label-md text-primary hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-base">open_in_new</span>
                                Lihat Dokumen
                            </a>
                        </div>
                    @else
                        <a href="{{ asset('storage/'.$user->identity_card_path) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-3 p-4 rounded-xl border border-outline-variant bg-surface-container-low hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">picture_as_pdf</span>
                            <span class="flex flex-col">
                                <span class="font-label-md text-label-md text-on-surface font-semibold">Dokumen Identitas (PDF)</span>
                                <span class="font-caption-xs text-caption-xs text-primary flex items-center gap-1">Lihat Dokumen <span class="material-symbols-outlined text-sm">open_in_new</span></span>
                            </span>
                        </a>
                    @endif
                @else
                    <div class="p-4 rounded-xl border border-dashed border-outline-variant bg-surface-container-low/40 text-on-surface-variant font-body-md text-body-md text-sm">
                        Belum ada dokumen identitas terunggah.
                    </div>
                @endif

                <!-- Toggle Ganti / Perbarui Foto KTM -->
                <div class="mt-3">
                    <button type="button" @click="toggleIdUpload()"
                            class="inline-flex items-center gap-1.5 border border-outline-variant text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-base">photo_camera</span>
                        <span x-text="showIdUpload ? 'Tutup Formulir Ganti Foto' : 'Ganti / Perbarui Foto KTM'"></span>
                    </button>
                    <p class="mt-1.5 font-caption-xs text-caption-xs text-on-surface-variant">Tidak perlu mengganti? Lewati saja — dokumen aktif di profil tetap digunakan.</p>
                </div>

                <!-- Formulir Unggah Dokumen Baru -->
                <div x-show="showIdUpload" x-cloak class="mt-4 p-4 rounded-xl border border-outline-variant bg-surface-container-low">
                    <label for="identity_card_file" class="block font-label-md text-label-md text-on-surface">Unggah Dokumen KTM / KTP Baru</label>
                    <p class="mt-1 font-caption-xs text-caption-xs text-on-surface-variant">Format JPG, PNG, WebP, atau PDF (Maksimal 5MB). Foto otomatis dikompres.</p>
                    <input id="identity_card_file" name="identity_card_file" type="file" accept="image/jpeg,image/png,image/webp,application/pdf"
                        @change="handleIdCardChange($event)"
                        class="mt-2 block w-full text-sm text-on-surface-variant
                               file:mr-3 file:rounded-md file:border-0 file:bg-surface-container-high file:px-3 file:py-1.5
                               file:text-sm file:font-medium file:text-on-surface hover:file:bg-surface-container"/>
                    <p x-show="isCompressingId" x-cloak class="mt-1 text-xs text-primary">Mengompres foto...</p>
                    <p x-show="idCompressionInfo" x-cloak class="mt-1 text-xs text-status-success-text" x-text="idCompressionInfo"></p>
                    <p x-show="idError" x-cloak class="mt-1 text-xs text-error" x-text="idError"></p>
                    <p x-show="hasNewIdCard" x-cloak class="mt-1 text-xs text-status-success-text flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span x-text="'Berkas baru siap diunggah: ' + idFileName"></span>
                    </p>
                    @error('identity_card_file')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Wizard Step 1 Nav -->
            <div class="flex justify-end pt-4 border-t border-surface-variant">
                <button type="button" @click="goToStep2()"
                        :disabled="!canGoStep2"
                        :class="canGoStep2 ? 'bg-primary hover:bg-primary-container text-on-primary' : 'bg-surface-variant text-on-surface-variant/60 cursor-not-allowed'"
                        class="font-label-md text-label-md px-6 py-2.5 rounded-lg transition-colors flex items-center gap-2">
                    Selanjutnya
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- STEP 2: Pilih Laboratorium & Alat -->
        <div x-show="currentStep === 2" x-transition style="display: none;">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 border-b border-surface-variant pb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">handyman</span>
                Pilih Laboratorium &amp; Alat
            </h2>

            <!-- Dropdown Laboratorium Tujuan -->
            <div class="mb-6">
                <label class="font-label-md text-label-md text-on-surface block mb-2" for="laboratory_select">
                    Pilih Laboratorium Tujuan <span class="text-error">*</span>
                </label>
                <select id="laboratory_select"
                        x-model="selectedLab"
                        @change="onLabChange()"
                        class="w-full h-11 px-3 border border-outline-variant rounded-lg font-body-md text-body-md bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="" disabled>-- Pilih Laboratorium --</option>
                    <template x-for="lab in laboratories" :key="lab.id">
                        <option :value="lab.id" x-text="lab.name"></option>
                    </template>
                </select>
                <p class="mt-1.5 font-caption-xs text-caption-xs text-on-surface-variant flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">filter_alt</span>
                    Daftar alat otomatis disaring sesuai laboratorium tujuan.
                </p>
            </div>
            <input type="hidden" name="laboratory_id" :value="selectedLab"/>

            <div class="bg-surface border border-outline-variant rounded-xl overflow-hidden mb-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[550px]">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr>
                                <th class="py-3 px-4 font-label-md text-label-md text-on-surface font-semibold">Pilih Alat</th>
                                <th class="py-3 px-4 font-label-md text-label-md text-on-surface font-semibold w-32">Stok Tersedia</th>
                                <th class="py-3 px-4 font-label-md text-label-md text-on-surface font-semibold w-40">Jumlah Pinjam</th>
                                <th class="py-3 px-4 w-16 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/50">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <!-- Tool Select -->
                                    <td class="py-3 px-4">
                                        <select :name="'items[' + index + '][tool_id]'"
                                                x-model="item.tool_id"
                                                @change="updateToolInfo(item)"
                                                class="w-full h-11 px-3 border border-outline-variant rounded-lg font-body-md text-body-md bg-surface-container-lowest focus:ring-primary focus:border-primary">
                                            <option value="" disabled>-- Pilih Alat Laboratorium --</option>
                                            <template x-for="t in labTools" :key="t.id">
                                                <option :value="t.id" x-text="t.code + ' - ' + t.name + ' (' + t.available_stock + ' Tersedia)'" :disabled="t.available_stock <= 0"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <!-- Available Stock -->
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-badge-xs text-badge-xs font-semibold"
                                              :class="item.max_stock > 0 ? 'bg-status-success-bg text-status-success-text' : 'bg-slate-200 text-slate-600'">
                                            <span x-text="item.max_stock + ' Unit'"></span>
                                        </span>
                                    </td>
                                    <!-- Quantity Selector -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center border border-outline-variant rounded-lg overflow-hidden w-28 bg-surface-container-lowest">
                                            <button type="button"
                                                    @click="if(item.quantity > 1) { item.quantity--; step2Error = ''; }"
                                                    :disabled="item.quantity <= 1 || item.max_stock <= 0"
                                                    :class="(item.quantity <= 1 || item.max_stock <= 0) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-surface-container'"
                                                    class="w-8 h-9 flex items-center justify-center text-on-surface-variant transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">remove</span>
                                            </button>
                                            <input :name="'items[' + index + '][quantity]'"
                                                   type="number"
                                                   x-model.number="item.quantity"
                                                   @input="step2Error = ''"
                                                   :max="item.max_stock"
                                                   :min="item.max_stock > 0 ? 1 : 0"
                                                   :disabled="item.max_stock <= 0"
                                                   class="w-12 h-9 text-center font-body-md text-body-md text-on-surface border-none focus:ring-0 p-0 disabled:bg-surface-container-low disabled:text-on-surface-variant/50"/>
                                            <button type="button"
                                                    @click="if(item.quantity < item.max_stock && item.max_stock > 0) { item.quantity++; step2Error = ''; }"
                                                    :disabled="item.quantity >= item.max_stock || item.max_stock <= 0"
                                                    :class="(item.quantity >= item.max_stock || item.max_stock <= 0) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-surface-container'"
                                                    class="w-8 h-9 flex items-center justify-center text-on-surface-variant transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">add</span>
                                            </button>
                                        </div>
                                    </td>
                                    <!-- Delete Row Action -->
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" @click="removeItem(index)"
                                                class="text-error hover:bg-error-container p-1.5 rounded-md transition-colors"
                                                title="Hapus baris"
                                                x-show="items.length > 1">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty Lab Prompt -->
            <div x-show="!selectedLab" x-cloak class="p-4 mb-4 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface-variant font-body-md text-body-md text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">info</span>
                Silakan pilih laboratorium tujuan terlebih dahulu untuk menampilkan daftar alat.
            </div>

            <!-- Error alert Step 2 -->
            <div x-show="step2Error" x-cloak class="p-3 mb-4 rounded-lg bg-error/10 border border-error/20 text-error flex items-center gap-2 text-sm font-medium">
                <span class="material-symbols-outlined text-base">error</span>
                <span x-text="step2Error"></span>
            </div>

            <!-- Add More Tool Row -->
            <button type="button" @click="addItem()"
                    class="mb-8 font-label-md text-label-md text-primary hover:text-primary-container flex items-center gap-1.5 transition-colors font-semibold">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                Tambah Alat Lain
            </button>

            <!-- Wizard Step 2 Nav -->
            <div class="flex justify-between pt-4 border-t border-surface-variant">
                <button type="button" @click="currentStep = 1"
                        class="bg-surface text-secondary border border-outline-variant font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Kembali
                </button>
                <button type="button" @click="goToStep3()"
                        class="bg-primary text-on-primary font-label-md text-label-md px-6 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                    Selanjutnya
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- STEP 3: Jadwal, Surat & Konfirmasi -->
        <div x-show="currentStep === 3" x-transition style="display: none;">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 border-b border-surface-variant pb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">calendar_month</span>
                Jadwal &amp; Surat Peminjaman
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Tanggal Mulai -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface" for="start_date">Tanggal Mulai Pinjam <span class="text-error">*</span></label>
                    <input class="w-full h-11 px-3 py-2 border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest"
                           id="start_date"
                           name="start_date"
                           type="date"
                           value="{{ old('start_date', now()->format('Y-m-d')) }}"
                           min="{{ now()->format('Y-m-d') }}"
                           required/>
                    @error('start_date')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Pengembalian -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface" for="end_date">Rencana Tanggal Pengembalian <span class="text-error">*</span></label>
                    <input class="w-full h-11 px-3 py-2 border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest"
                           id="end_date"
                           name="end_date"
                           type="date"
                           value="{{ old('end_date', now()->addDays(3)->format('Y-m-d')) }}"
                           min="{{ now()->format('Y-m-d') }}"
                           required/>
                    @error('end_date')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keperluan / Tujuan Peminjaman -->
                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label class="font-label-md text-label-md text-on-surface" for="purpose">Keperluan / Keterangan Peminjaman <span class="text-error">*</span></label>
                    <textarea class="w-full p-3 border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest"
                              id="purpose"
                              name="purpose"
                              rows="3"
                              placeholder="Tuliskan tujuan peminjaman, nama mata kuliah praktikum / judul penelitian skripsi..."
                              required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Upload Surat Peminjaman Resmi (WAJIB, Drag & Drop) -->
            <div class="flex flex-col gap-2 mb-6">
                <label class="font-label-md text-label-md text-on-surface">
                    Unggah Surat Peminjaman Resmi <span class="text-error">*</span>
                </label>
                <p class="font-caption-xs text-caption-xs text-on-surface-variant">Surat permohonan resmi bertanda tangan/kop instansi. Format JPG, PNG, atau PDF (Maksimal 5MB).</p>
                <div class="border-2 border-dashed rounded-xl p-8 flex flex-col items-center justify-center relative group transition-colors"
                     :class="dragActive ? 'border-primary bg-primary/5' : 'border-outline-variant bg-surface-container-low/40 hover:bg-surface-container-low'"
                     @dragover.prevent="dragActive = true"
                     @dragleave.prevent="dragActive = false"
                     @drop.prevent="handleLetterDrop($event)">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant group-hover:text-primary transition-colors mb-2">cloud_upload</span>
                    <p class="font-body-md text-body-md text-on-surface text-center mb-1">
                        <span class="font-semibold text-primary">Klik untuk memilih berkas</span> atau seret &amp; letakkan di sini
                    </p>
                    <p class="font-caption-xs text-caption-xs text-on-surface-variant">JPG, PNG, PDF (Maksimal 5MB)</p>
                    <input id="loan_letter" name="loan_letter" accept=".jpg,.jpeg,.png,.pdf" class="absolute inset-0 opacity-0 cursor-pointer" type="file" @change="handleLetterChange($event)" required/>
                </div>
                <!-- Preview nama & ukuran file -->
                <div x-show="selectedFileName" x-cloak class="mt-2 p-2 bg-status-success-bg/30 border border-status-success-text/30 rounded-lg flex items-center justify-between text-xs text-status-success-text">
                    <span class="flex items-center gap-1 font-medium truncate max-w-xs">
                        <span class="material-symbols-outlined text-sm">description</span>
                        <span x-text="selectedFileName"></span>
                    </span>
                    <span x-text="selectedFileSize" class="font-mono"></span>
                </div>
                <div x-show="letterError" x-cloak class="p-3 rounded-lg bg-error/10 border border-error/20 text-error flex items-center gap-2 text-sm font-medium">
                    <span class="material-symbols-outlined text-base">error</span>
                    <span x-text="letterError"></span>
                </div>
                @error('loan_letter')
                    <p class="font-caption-xs text-caption-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Agreement Checkbox -->
            <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant mb-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" required class="mt-1 rounded border-outline-variant text-primary focus:ring-primary w-5 h-5"/>
                    <span class="font-body-md text-body-md text-on-surface text-sm">
                        Saya menyatakan data yang diisikan benar, surat peminjaman yang diunggah asli, serta bersedia menjaga kondisi fisik peralatan laboratorium dan mengembalikannya tepat waktu sesuai jadwal yang disetujui.
                    </span>
                </label>
            </div>

            <!-- Wizard Step 3 Nav -->
            <div class="flex justify-between pt-4 border-t border-surface-variant">
                <button type="button" @click="currentStep = 2"
                        class="bg-surface text-secondary border border-outline-variant font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Kembali
                </button>
                <button type="submit"
                        class="bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md px-6 py-3 rounded-lg shadow-sm transition-colors flex items-center gap-2 font-semibold">
                    <span class="material-symbols-outlined text-lg">send</span>
                    Kirim Pengajuan Peminjaman
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function loanFormWizard(config) {
        const availableTools = config.availableTools || [];
        const initialToolId = config.initialToolId;
        const profileComplete = config.profileComplete;

        let initialItem = { tool_id: '', quantity: 1, max_stock: 0 };
        let initialLab = '';

        if (initialToolId) {
            const found = availableTools.find(t => t.id == initialToolId);
            if (found) {
                initialLab = String(found.laboratory_id || '');
                initialItem = {
                    tool_id: found.id,
                    quantity: found.available_stock > 0 ? 1 : 0,
                    max_stock: found.available_stock
                };
            }
        }

        return {
            currentStep: 1,
            step2Error: '',
            letterError: '',
            dragActive: false,
            selectedFileName: '',
            selectedFileSize: '',
            profileComplete: profileComplete,
            hasIdentityCard: config.hasIdentityCard,
            phoneNumber: config.phoneNumber ?? '',
            showIdUpload: false,
            hasNewIdCard: false,
            idFileName: '',
            idCompressionInfo: '',
            isCompressingId: false,
            idError: '',
            availableTools: availableTools,
            laboratories: config.laboratories || [],
            selectedLab: initialLab,
            items: [initialItem],
            get canGoStep2() {
                if (this.profileComplete) {
                    return true;
                }
                const hasPhone = String(this.phoneNumber ?? '').trim() !== '';
                const hasIdentity = this.hasIdentityCard || this.hasNewIdCard;
                return hasPhone && hasIdentity;
            },
            get labTools() {
                if (!this.selectedLab) {
                    return [];
                }
                return this.availableTools.filter(t => String(t.laboratory_id) === String(this.selectedLab));
            },
            goToStep2() {
                if (!this.canGoStep2) {
                    return;
                }
                this.currentStep = 2;
            },
            toggleIdUpload() {
                this.showIdUpload = !this.showIdUpload;
            },
            handleIdCardChange(event) {
                this.idError = '';
                this.idCompressionInfo = '';
                const file = event.target.files[0];
                if (!file) {
                    this.hasNewIdCard = false;
                    this.idFileName = '';
                    return;
                }

                if (['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.type)) {
                    if (file.size > 1024 * 1024) {
                        this.compressIdImage(file);
                        return;
                    }
                    this.hasNewIdCard = true;
                    this.idFileName = file.name;
                    return;
                }

                if (file.type === 'application/pdf') {
                    if (file.size > 5 * 1024 * 1024) {
                        this.idError = 'Ukuran file PDF melebihi batas 5MB.';
                        event.target.value = '';
                        this.hasNewIdCard = false;
                        this.idFileName = '';
                        return;
                    }
                    this.hasNewIdCard = true;
                    this.idFileName = file.name;
                    return;
                }

                this.idError = 'Format berkas tidak didukung (JPG, PNG, WebP, atau PDF).';
                event.target.value = '';
            },
            compressIdImage(file) {
                this.isCompressingId = true;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const image = new Image();
                    image.onload = () => {
                        const maxDimension = 1600;
                        let width = image.naturalWidth;
                        let height = image.naturalHeight;
                        if (width > maxDimension || height > maxDimension) {
                            const scale = Math.min(maxDimension / width, maxDimension / height);
                            width = Math.round(width * scale);
                            height = Math.round(height * scale);
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const context = canvas.getContext('2d');
                        context.fillStyle = '#FFFFFF';
                        context.fillRect(0, 0, width, height);
                        context.drawImage(image, 0, 0, width, height);
                        canvas.toBlob((blob) => {
                            this.isCompressingId = false;
                            if (!blob) {
                                this.idError = 'Gagal mengompres foto. Silakan coba lagi.';
                                return;
                            }
                            const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', { type: 'image/jpeg' });
                            const dt = new DataTransfer();
                            dt.items.add(compressedFile);
                            const input = document.getElementById('identity_card_file');
                            if (input) {
                                input.files = dt.files;
                            }
                            const origMb = (file.size / (1024 * 1024)).toFixed(2);
                            const newKb = (blob.size / 1024).toFixed(1);
                            this.idCompressionInfo = 'Foto (' + origMb + ' MB) berhasil dikompres menjadi ' + newKb + ' KB';
                            this.hasNewIdCard = true;
                            this.idFileName = compressedFile.name;
                        }, 'image/jpeg', 0.82);
                    };
                    image.onerror = () => {
                        this.isCompressingId = false;
                        this.idError = 'Foto tidak dapat dibaca. Silakan pilih berkas lain.';
                    };
                    image.src = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            onLabChange() {
                this.step2Error = '';
                this.items = [{ tool_id: '', quantity: 1, max_stock: 0 }];
            },
            validateStep2() {
                this.step2Error = '';
                if (!this.selectedLab) {
                    this.step2Error = 'Harap pilih laboratorium tujuan terlebih dahulu.';
                    return false;
                }
                if (!this.items || this.items.length === 0) {
                    this.step2Error = 'Harap pilih minimal 1 alat laboratorium.';
                    return false;
                }
                for (let i = 0; i < this.items.length; i++) {
                    const item = this.items[i];
                    if (!item.tool_id) {
                        this.step2Error = 'Harap pilih alat laboratorium pada setiap baris.';
                        return false;
                    }
                    if (!this.labTools.some(t => t.id == item.tool_id)) {
                        this.step2Error = 'Alat yang dipilih tidak termasuk laboratorium tujuan. Harap pilih ulang.';
                        return false;
                    }
                    if (item.max_stock <= 0) {
                        this.step2Error = 'Alat yang Anda pilih memiliki stok 0 (habis). Harap pilih alat yang tersedia atau hapus baris tersebut.';
                        return false;
                    }
                    if (item.quantity < 1 || item.quantity > item.max_stock) {
                        this.step2Error = 'Jumlah pinjam tidak boleh melebihi stok tersedia.';
                        return false;
                    }
                }
                return true;
            },
            goToStep3() {
                if (this.validateStep2()) {
                    this.currentStep = 3;
                }
            },
            handleLetterChange(event) {
                this.letterError = '';
                const file = event.target.files[0];
                this.acceptLetterFile(file);

                if (!file) {
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    this.letterError = 'Ukuran surat peminjaman melebihi batas maksimal 5MB.';
                    event.target.value = '';
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                }
            },
            handleLetterDrop(event) {
                this.dragActive = false;
                const file = event.dataTransfer?.files?.[0];
                if (!file) {
                    return;
                }

                const input = document.getElementById('loan_letter');
                if (input) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                }

                this.letterError = '';
                this.acceptLetterFile(file);

                if (file.size > 5 * 1024 * 1024) {
                    this.letterError = 'Ukuran surat peminjaman melebihi batas maksimal 5MB.';
                    if (input) {
                        input.value = '';
                    }
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                }
            },
            acceptLetterFile(file) {
                if (file) {
                    this.selectedFileName = file.name;
                    this.selectedFileSize = (file.size / 1024).toFixed(1) + ' KB';
                } else {
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                }
            },
            addItem() {
                this.items.push({ tool_id: '', quantity: 1, max_stock: 0 });
                this.step2Error = '';
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                    this.step2Error = '';
                }
            },
            updateToolInfo(item) {
                const found = this.labTools.find(t => t.id == item.tool_id);
                if (found) {
                    item.max_stock = found.available_stock;
                    if (found.available_stock <= 0) {
                        item.quantity = 0;
                    } else {
                        if (item.quantity === 0) {
                            item.quantity = 1;
                        } else if (item.quantity > item.max_stock) {
                            item.quantity = item.max_stock;
                        }
                    }
                    this.step2Error = '';
                }
            }
        };
    }
</script>
@endpush
