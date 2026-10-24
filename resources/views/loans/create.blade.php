@extends('layouts.catalog', ['title' => 'Formulir Pengajuan Peminjaman'])

@section('content')
<div x-data="loanFormWizard({
    initialToolId: {{ Js::from(request('tool_id')) }},
    availableTools: {{ Js::from($toolsList ?? []) }},
    laboratories: {{ Js::from($laboratories->map(fn ($lab) => ['id' => $lab->id, 'name' => $lab->name, 'code' => $lab->code])->values()) }},
    profileComplete: {{ Js::from($isProfileComplete) }},
    phoneNumber: {{ Js::from(old('phone_number', $user->phone_number)) }},
    hasIdentityCard: {{ Js::from(filled($user->identity_card_path)) }},
    oldLab: {{ Js::from(old('laboratory_id', '')) }},
    oldItems: {{ Js::from(old('items', [])) }},
    initialStep: {{ $errors->hasAny(['items', 'items.*', 'laboratory_id']) ? 2 : ($errors->hasAny(['start_date', 'end_date', 'purpose', 'loan_letter', 'integrity_pledge']) ? 3 : 1) }}
})" class="py-8 px-4 sm:px-6 md:px-8 max-w-4xl mx-auto w-full">

    <!-- Page Header (Anti-AI-slop: Tanpa eyebrow pill mengambang) -->
    <div class="mb-8 text-center sm:text-left">
        <nav class="flex items-center gap-2 text-xs font-mono text-slate-400 mb-2">
            <a class="hover:text-blue-600 transition-colors" href="{{ route('catalog.index') }}">Katalog</a>
            <span>/</span>
            <span class="text-slate-800 font-bold">Formulir Pengajuan Peminjaman</span>
        </nav>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Formulir Peminjaman Alat</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
            Ajukan permohonan peminjaman perkakas dan mesin laboratorium secara mandiri minimal H-2 hari kerja sebelum praktikum atau riset.
        </p>
    </div>

    <!-- Stepper Indicator (3 Tahap Sesuai Stitch Screen 2) -->
    <div class="mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 relative">
            <!-- Step 1 -->
            <button type="button" @click="if(currentStep > 1) currentStep = 1"
                    class="bg-white p-4 rounded-2xl border text-left flex items-center gap-3.5 relative overflow-hidden transition-all shadow-xs cursor-pointer"
                    :class="currentStep === 1 ? 'border-blue-300 ring-2 ring-blue-100' : 'border-slate-200'">
                <div class="w-1.5 h-full absolute left-0 top-0 transition-colors"
                     :class="currentStep >= 1 ? 'bg-blue-600' : 'bg-transparent'"></div>
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 font-mono text-xs font-bold transition-colors"
                     :class="currentStep > 1 ? 'bg-blue-600 text-white' : (currentStep === 1 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500')">
                    <template x-if="currentStep > 1">
                        <span class="material-symbols-outlined text-[18px]">check</span>
                    </template>
                    <template x-if="currentStep <= 1">
                        <span>01</span>
                    </template>
                </div>
                <div class="min-w-0">
                    <span class="font-mono text-xs font-bold block" :class="currentStep === 1 ? 'text-blue-700' : 'text-slate-800'">01 Data Peminjam</span>
                    <p class="text-[11px] text-slate-400 font-medium">Identitas Akun Terverifikasi</p>
                </div>
            </button>

            <!-- Step 2 -->
            <button type="button" @click="if(canGoStep2 && currentStep > 2) currentStep = 2"
                    class="bg-white p-4 rounded-2xl border text-left flex items-center gap-3.5 relative overflow-hidden transition-all shadow-xs"
                    :class="currentStep === 2 ? 'border-blue-300 ring-2 ring-blue-100' : 'border-slate-200'">
                <div class="w-1.5 h-full absolute left-0 top-0 transition-colors"
                     :class="currentStep >= 2 ? 'bg-blue-600' : 'bg-transparent'"></div>
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 font-mono text-xs font-bold transition-colors"
                     :class="currentStep > 2 ? 'bg-blue-600 text-white' : (currentStep === 2 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500')">
                    <template x-if="currentStep > 2">
                        <span class="material-symbols-outlined text-[18px]">check</span>
                    </template>
                    <template x-if="currentStep <= 2">
                        <span>02</span>
                    </template>
                </div>
                <div class="min-w-0">
                    <span class="font-mono text-xs font-bold block" :class="currentStep === 2 ? 'text-blue-700' : 'text-slate-800'">02 Konfirmasi Alat</span>
                    <p class="text-[11px] text-slate-400 font-medium">Seleksi &amp; Kuantitas</p>
                </div>
            </button>

            <!-- Step 3 -->
            <div class="bg-white p-4 rounded-2xl border text-left flex items-center gap-3.5 relative overflow-hidden transition-all shadow-xs"
                 :class="currentStep === 3 ? 'border-blue-300 ring-2 ring-blue-100' : 'border-slate-200'">
                <div class="w-1.5 h-full absolute left-0 top-0 transition-colors"
                     :class="currentStep === 3 ? 'bg-blue-600' : 'bg-transparent'"></div>
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 font-mono text-xs font-bold transition-colors"
                     :class="currentStep === 3 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500'">
                    <span>03</span>
                </div>
                <div class="min-w-0">
                    <span class="font-mono text-xs font-bold block" :class="currentStep === 3 ? 'text-blue-700' : 'text-slate-800'">03 Jadwal &amp; Surat Peminjaman</span>
                    <p class="text-[11px] text-slate-400 font-medium">Validasi &amp; Kirim</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <form id="loan-submission-form" action="{{ route('loans.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-7 md:p-8">
        @csrf

        <!-- STEP 1: Data Diri Peminjam -->
        <div x-show="currentStep === 1" x-transition>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 mb-6 border-b border-slate-100 gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                        <span class="material-symbols-outlined text-2xl">badge</span>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Data Diri Peminjam</h2>
                        <p class="text-xs text-slate-500">Identitas peminjam terdaftar Laboratorium Teknik Mesin</p>
                    </div>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-mono font-semibold w-fit border border-slate-200">
                    <span class="material-symbols-outlined text-sm text-blue-600">verified</span>
                    Kategori: {{ $user->roleLabel() }}
                </div>
            </div>

            <!-- Incomplete Profile Alert -->
            @if (! $isProfileComplete)
                <div class="p-4 mb-6 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs" role="alert">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-amber-600 mt-0.5 shrink-0">warning</span>
                        <div class="text-xs sm:text-sm">
                            <span class="font-bold block mb-0.5">Profil Belum Lengkap</span>
                            Nomor WhatsApp aktif atau dokumen identitas (KTM/KTP) belum lengkap. Anda dapat memperbarui nomor WhatsApp dan mengunggah dokumen langsung di langkah ini.
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}"
                       class="shrink-0 inline-flex items-center justify-center gap-1.5 bg-white hover:bg-amber-100 text-amber-800 text-xs font-semibold px-3 py-1.5 rounded-lg border border-amber-300 shadow-xs transition-all">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        Edit Profil
                    </a>
                </div>
            @endif

            <!-- 4 Fields Grid (Read-only + Editable Phone) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- Nama Lengkap -->
                <div class="space-y-1">
                    <label class="text-xs font-mono text-slate-500 font-bold flex items-center justify-between">
                        <span>NAMA LENGKAP</span>
                        <span class="material-symbols-outlined text-xs text-slate-400">lock</span>
                    </label>
                    <input class="w-full bg-slate-50 px-3.5 py-2.5 rounded-xl border border-slate-200 font-semibold text-xs sm:text-sm text-slate-800 cursor-not-allowed outline-none"
                           readonly type="text" value="{{ $user->name }}"/>
                </div>

                <!-- NIM / NIK -->
                <div class="space-y-1">
                    <label class="text-xs font-mono text-slate-500 font-bold flex items-center justify-between">
                        <span>{{ $user->identity_type === 'umum' ? 'NOMOR INDUK KEPENDUDUKAN (NIK)' : 'NOMOR INDUK MAHASISWA (NIM)' }}</span>
                        <span class="material-symbols-outlined text-xs text-slate-400">lock</span>
                    </label>
                    <input class="w-full bg-slate-50 px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono font-bold text-xs sm:text-sm text-slate-800 cursor-not-allowed outline-none"
                           readonly type="text" value="{{ $user->identity_number ?? '-' }}"/>
                </div>

                <!-- Program Studi / Instansi -->
                <div class="space-y-1">
                    <label class="text-xs font-mono text-slate-500 font-bold flex items-center justify-between">
                        <span>{{ $user->identity_type === 'umum' ? 'INSTANSI / DOMISILI' : 'PROGRAM STUDI & FAKULTAS' }}</span>
                        <span class="material-symbols-outlined text-xs text-slate-400">lock</span>
                    </label>
                    <input class="w-full bg-slate-50 px-3.5 py-2.5 rounded-xl border border-slate-200 font-medium text-xs sm:text-sm text-slate-800 cursor-not-allowed outline-none"
                           readonly type="text" value="{{ $user->institution ?? ($user->identity_type === 'umum' ? 'Umum' : 'S1 Teknik Mesin (Fakultas Teknik)') }}"/>
                </div>

                <!-- No WhatsApp Aktif (Editable) -->
                <div class="space-y-1">
                    <label for="phone_number" class="text-xs font-mono text-slate-500 font-bold flex items-center justify-between">
                        <span>NO. WHATSAPP AKTIF</span>
                        <span class="text-[10px] text-blue-600 font-bold">Dapat Diperbarui</span>
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">call</span>
                        <input id="phone_number"
                               name="phone_number"
                               type="tel"
                               x-model="phoneNumber"
                               placeholder="08xxxxxxxxxx"
                               class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 bg-white font-mono text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition-colors"/>
                    </div>
                    @error('phone_number')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- KTM / KTP Digital Section -->
            <div class="mb-6 space-y-2">
                <span class="text-xs font-mono text-slate-500 font-bold block">{{ $user->identity_type === 'umum' ? 'KARTU IDENTITAS (KTP DIGITAL)' : 'KARTU TANDA MAHASISWA (KTM DIGITAL)' }}</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Preview KTM / KTP Aktif -->
                    @if ($user->identity_card_path)
                        <div class="bg-slate-50 border border-slate-200 p-3.5 rounded-xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-blue-100/70 text-blue-700 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-xl">badge</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-mono font-bold text-slate-800 truncate">{{ ($user->identity_type === 'umum' ? 'KTP_' : 'KTM_') . Str::slug($user->name) }}.pdf</p>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold font-mono border border-emerald-200">
                                        {{ $user->identity_type === 'umum' ? 'KTP Valid Tersimpan' : 'KTM Valid Tersimpan' }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" @click="toggleIdUpload()"
                                    class="shrink-0 text-xs font-mono font-bold text-blue-600 hover:underline">
                                <span x-text="showIdUpload ? 'Tutup' : 'Ganti Berkas'"></span>
                            </button>
                        </div>
                    @else
                        <div class="p-3.5 rounded-xl border border-dashed border-amber-300 bg-amber-50/50 text-amber-800 text-xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-amber-600">warning</span>
                            Belum ada dokumen identitas terunggah di profil.
                        </div>
                    @endif

                    <!-- Upload Input -->
                    <div class="bg-slate-50 border border-dashed border-slate-300 p-3.5 rounded-xl flex items-center justify-center text-center cursor-pointer hover:bg-blue-50/30 hover:border-blue-300 transition-colors"
                         @click="showIdUpload = true">
                        <div class="flex items-center gap-2 text-slate-600 text-xs font-medium">
                            <span class="material-symbols-outlined text-base text-slate-400">upload_file</span>
                            <span>Unggah berkas baru (.PDF, .JPG max 5MB)</span>
                        </div>
                    </div>
                </div>

                <!-- Form Upload Berkas Baru -->
                <div x-show="showIdUpload" x-cloak class="mt-3 p-4 rounded-xl border border-blue-200 bg-blue-50/40">
                    <label for="identity_card_file" class="block text-xs font-bold text-slate-800 mb-1">Pilih Berkas KTM / KTP Baru</label>
                    <input id="identity_card_file" name="identity_card_file" type="file" accept="image/jpeg,image/png,image/webp,application/pdf"
                           @change="handleIdCardChange($event)"
                           class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-blue-700 cursor-pointer"/>
                    <p x-show="hasNewIdCard" x-cloak class="mt-1.5 text-xs text-emerald-600 font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span x-text="'Berkas baru siap diunggah: ' + idFileName"></span>
                    </p>
                    <p x-show="idError" x-cloak class="mt-1.5 text-xs text-rose-600 font-medium" x-text="idError"></p>
                </div>
            </div>

            <!-- Checkbox Konfirmasi Kebenaran Data Pribadi -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 mb-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox"
                           x-model="personalDataConfirmed"
                           @change="step1Error = ''"
                           class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer"/>
                    <span class="text-xs text-slate-700 leading-relaxed select-none">
                        Saya menyatakan bahwa data pribadi (No. HP WhatsApp aktif dan dokumen identitas) yang tertera di atas adalah benar dan sah milik saya pribadi untuk keperluan administrasi peminjaman laboratorium.
                    </span>
                </label>
            </div>

            <!-- Error Alert Step 1 -->
            <div x-show="step1Error" x-cloak class="p-3 mb-5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-2 text-xs font-medium">
                <span class="material-symbols-outlined text-base shrink-0">error</span>
                <span x-text="step1Error"></span>
            </div>

            <!-- Wizard Step 1 Nav -->
            <div class="flex justify-end pt-5 border-t border-slate-100">
                <button type="button" @click="goToStep2()"
                        :disabled="!canGoStep2 || !personalDataConfirmed"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full text-xs sm:text-sm font-semibold transition-all cursor-pointer"
                        :class="(canGoStep2 && personalDataConfirmed)
                            ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs active:scale-[0.98]'
                            : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed'">
                    Lanjut ke Konfirmasi Alat
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- STEP 2: Konfirmasi Alat Terpilih (Single-Item Loan Flow) -->
        <div x-show="currentStep === 2" x-transition style="display: none;">
            <div class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100">
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <span class="material-symbols-outlined text-2xl">precision_manufacturing</span>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Instrumen Alat yang Diajukan</h2>
                    <p class="text-xs text-slate-500">Spesifikasi mesin dan batas kuantitas unit</p>
                </div>
            </div>

            <!-- Fallback Selector Bila Pengguna Mengubah / Belum Memilih Alat -->
            <div class="mb-5">
                <label class="block text-xs font-mono font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                    Pilih / Ubah Alat Laboratorium
                </label>
                <select x-model="singleToolId" @change="onToolSelectChange()"
                        class="w-full h-10 px-3.5 border border-slate-300 rounded-xl text-xs sm:text-sm bg-white text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 shadow-2xs">
                    <option value="" disabled>-- Pilih Salah Satu Alat Tersedia --</option>
                    <template x-for="tool in availableTools" :key="tool.id">
                        <option :value="tool.id"
                                :disabled="tool.available_stock <= 0"
                                x-text="tool.code + ' · ' + tool.name + (tool.available_stock <= 0 ? ' (Stok Habis)' : ' (' + tool.available_stock + ' Tersedia)')">
                        </option>
                    </template>
                </select>
                @error('items')
                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                @enderror
                @error('items.0.tool_id')
                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                @enderror
                @error('laboratory_id')
                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Hidden Inputs for Single Item Submission -->
            <input type="hidden" name="laboratory_id" :value="selectedLab"/>
            <input type="hidden" name="items[0][tool_id]" :value="singleToolId"/>

            <!-- Highlight Tool Card (Sesuai Stitch Screen 2) -->
            <div x-show="selectedTool" class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 mb-6 flex flex-col md:flex-row gap-5 items-start">
                <div class="w-full md:w-48 h-36 shrink-0 rounded-xl overflow-hidden bg-slate-200 border border-slate-200 relative flex items-center justify-center">
                    <template x-if="selectedTool?.image">
                        <img :src="selectedTool.image" :alt="selectedTool.name" class="w-full h-full object-cover"/>
                    </template>
                    <div :class="selectedTool?.image ? 'hidden' : 'flex'" class="flex-col items-center justify-center text-slate-400 p-2 text-center">
                        <span class="material-symbols-outlined text-3xl text-blue-600/70">precision_manufacturing</span>
                        <span class="mt-1 font-mono text-[9px] uppercase tracking-wider text-slate-500" x-text="selectedTool?.code"></span>
                    </div>
                    <div class="absolute top-2 left-2 bg-slate-900/80 text-white text-[9px] font-mono px-2 py-0.5 rounded font-bold backdrop-blur-xs">
                        MESIN UTAMA
                    </div>
                </div>

                <div class="flex-1 space-y-2 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="font-mono text-xs font-bold text-slate-700 bg-white border border-slate-200 px-2 py-0.5 rounded"
                              x-text="selectedTool?.code"></span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-bold"
                              :class="(selectedTool?.available_stock || 0) > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="(selectedTool?.available_stock || 0) > 0 ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                            <span x-text="'Tersedia: ' + (selectedTool?.available_stock || 0) + ' Unit'"></span>
                        </span>
                    </div>

                    <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight" x-text="selectedTool?.name"></h3>

                    <div class="space-y-1 text-xs text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-slate-400">domain</span>
                            <span x-text="currentLabName + ' · Gedung D'"></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm text-slate-400">schedule</span>
                            <span>Pengajuan H-2 Hari Kerja · Layanan 08.00 - 16.00 WIB</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quantity Counter Stepper & Single-Tool Policy Banner -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-center mb-6">
                <!-- Quantity Stepper -->
                <div class="bg-slate-50 border border-slate-200 p-4 rounded-2xl space-y-2.5">
                    <label class="text-xs font-mono font-bold text-slate-700 block">JUMLAH UNIT YANG DIPINJAM</label>
                    <div class="flex items-center justify-between bg-white border border-slate-200 p-1.5 rounded-xl">
                        <button type="button" @click="decrementQty()"
                                :disabled="singleQuantity <= 1"
                                :class="singleQuantity <= 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-100 text-slate-700 cursor-pointer'"
                                class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-lg">remove</span>
                        </button>
                        <div class="text-center">
                            <span class="font-mono text-xl font-extrabold text-blue-600" x-text="singleQuantity"></span>
                            <span class="font-mono text-xs text-slate-500 ml-1 font-bold">Unit</span>
                        </div>
                        <button type="button" @click="incrementQty()"
                                :disabled="singleQuantity >= maxStockAvailable || maxStockAvailable <= 0"
                                :class="(singleQuantity >= maxStockAvailable || maxStockAvailable <= 0) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-100 text-slate-700 cursor-pointer'"
                                class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-lg">add</span>
                        </button>
                    </div>
                    <input type="hidden" name="items[0][quantity]" :value="singleQuantity"/>
                    <p class="text-[11px] text-slate-500 font-mono text-center" x-text="'Batas stok: ' + maxStockAvailable + ' Unit siap pakai'"></p>
                    @error('items.0.quantity')
                        <p class="text-xs text-rose-600 font-medium text-center mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Single-Tool Policy Banner (Sesuai Stitch Screen 2) -->
                <div class="lg:col-span-2 bg-blue-50/70 border border-blue-200 p-4 sm:p-5 rounded-2xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-xl text-blue-600 shrink-0 mt-0.5">info</span>
                    <div class="space-y-1">
                        <h4 class="text-xs font-mono font-bold text-slate-900 uppercase tracking-wide">Kebijakan 1 Tiket 1 Jenis Alat</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pengajuan difokuskan khusus untuk 1 jenis alat per tiket demi tertib administrasi checklist kalibrasi laboran. Bila memerlukan alat pendukung lain, silakan buat tiket terpisah setelah pengajuan ini diajukan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error alert Step 2 -->
            <div x-show="step2Error" x-cloak class="p-3 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-2 text-xs font-medium">
                <span class="material-symbols-outlined text-base shrink-0">error</span>
                <span x-text="step2Error"></span>
            </div>

            <!-- Wizard Step 2 Nav -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3 pt-5 border-t border-slate-100">
                <button type="button" @click="currentStep = 1"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-full border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Sebelumnya
                </button>
                <button type="button" @click="goToStep3()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-xs transition-all cursor-pointer">
                    Lanjut ke Jadwal &amp; Surat Peminjaman
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- STEP 3: Jadwal Penggunaan, Surat Peminjaman & Pakta K3 -->
        <div x-show="currentStep === 3" x-transition style="display: none;">
            <div class="flex items-center gap-3 pb-5 mb-6 border-b border-slate-100">
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <span class="material-symbols-outlined text-2xl">event_available</span>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">Jadwal Penggunaan &amp; Surat Peminjaman</h2>
                    <p class="text-xs text-slate-500">Penetapan rentang waktu dan berkas surat peminjaman resmi</p>
                </div>
            </div>

            <!-- Date Range (Mulai & Kembali) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- Tanggal Mulai -->
                <div class="space-y-1">
                    <label class="block text-xs font-mono font-bold text-slate-600 uppercase tracking-wider" for="start_date">
                        TANGGAL MULAI PINJAM <span class="text-rose-500">*</span>
                    </label>
                    <input class="w-full h-11 px-3.5 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white shadow-2xs"
                           id="start_date"
                           name="start_date"
                           type="date"
                           x-model="startDate"
                           @change="updateEndDateMin()"
                           value="{{ old('start_date', now()->addDays(2)->format('Y-m-d')) }}"
                           min="{{ now()->addDays(2)->format('Y-m-d') }}"
                           required/>
                    <p class="text-[11px] text-slate-500 flex items-center gap-1">
                        <span class="material-symbols-outlined text-xs text-blue-600">info</span>
                        SOP H-2: Paling awal {{ now()->addDays(2)->translatedFormat('d F Y') }}
                    </p>
                    @error('start_date')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Pengembalian -->
                <div class="space-y-1">
                    <label class="block text-xs font-mono font-bold text-slate-600 uppercase tracking-wider" for="end_date">
                        TANGGAL PENGEMBALIAN <span class="text-rose-500">*</span>
                    </label>
                    <input class="w-full h-11 px-3.5 border border-slate-300 rounded-xl text-xs sm:text-sm font-mono text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white shadow-2xs"
                           id="end_date"
                           name="end_date"
                           type="date"
                           x-model="endDate"
                           value="{{ old('end_date', now()->addDays(5)->format('Y-m-d')) }}"
                           :min="startDate"
                           :max="maxEndDate"
                           required/>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Durasi maks 14 hari kalender</span>
                        <span x-show="loanDuration > 0" class="font-mono font-bold text-blue-600" x-text="loanDuration + ' Hari'"></span>
                    </div>
                    @error('end_date')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keperluan / Topik Riset Textarea -->
                <div class="sm:col-span-2 space-y-1">
                    <label class="block text-xs font-mono font-bold text-slate-600 uppercase tracking-wider" for="purpose">
                        KEPERLUAN PEMINJAMAN / TOPIK RISET <span class="text-rose-500">*</span>
                    </label>
                    <textarea class="w-full p-3.5 border border-slate-300 rounded-xl text-xs sm:text-sm text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white shadow-2xs leading-relaxed"
                              id="purpose"
                              name="purpose"
                              rows="3"
                              placeholder="Tuliskan tujuan peminjaman secara jelas (misal: Praktikum Pemesinan &amp; Riset Tugas Akhir: Pembuatan spesimen poros bertingkat baja ST41)..."
                              required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Upload Surat Peminjaman Resmi (Dropzone) -->
            <div class="mb-6 space-y-1.5">
                <label class="block text-xs font-mono font-bold text-slate-600 uppercase tracking-wider">
                    SURAT PEMINJAMAN (PDF/JPG, MAKS 5MB) <span class="text-rose-500">*</span>
                </label>
                <div class="border-2 border-dashed rounded-2xl p-6 sm:p-7 flex flex-col items-center justify-center relative group transition-all cursor-pointer shadow-2xs"
                     :class="dragActive ? 'border-blue-600 bg-blue-50/60 ring-4 ring-blue-100' : 'border-slate-300 bg-slate-50 hover:bg-blue-50/30 hover:border-blue-400'"
                     @dragover.prevent="dragActive = true"
                     @dragleave.prevent="dragActive = false"
                     @drop.prevent="handleLetterDrop($event)">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-2.5">
                        <span class="material-symbols-outlined text-2xl">description</span>
                    </div>
                    <p class="text-xs sm:text-sm font-semibold text-slate-800 text-center">
                        <span class="text-blue-600 hover:underline">Pilih berkas surat peminjaman</span> atau seret &amp; letakkan di sini
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Format PDF, JPG, atau PNG (Maks 5MB)</p>
                    <input id="loan_letter" name="loan_letter" accept=".jpg,.jpeg,.png,.pdf" class="absolute inset-0 opacity-0 cursor-pointer" type="file" @change="handleLetterChange($event)" required/>
                </div>

                <!-- Preview file terunggah -->
                <div x-show="selectedFileName" x-cloak class="mt-2 p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-800 shadow-2xs">
                    <span class="flex items-center gap-1.5 font-semibold truncate max-w-xs sm:max-w-md">
                        <span class="material-symbols-outlined text-emerald-600 text-base">check_circle</span>
                        <span x-text="selectedFileName"></span>
                    </span>
                    <span x-text="selectedFileSize" class="font-mono text-emerald-700 font-bold shrink-0 ml-2"></span>
                </div>
                @error('loan_letter')
                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pakta Integritas & Keselamatan Kerja K3 -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 space-y-2 mb-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox"
                           name="integrity_pledge"
                           value="1"
                           x-model="integrityPledged"
                           required
                           class="mt-1 w-4 h-4 rounded text-blue-600 focus:ring-0 accent-blue-600 cursor-pointer"/>
                    <div class="space-y-1">
                        <span class="text-xs font-mono font-bold text-slate-900 block uppercase">
                            Pakta Integritas &amp; Keselamatan Kerja (K3 Laboratorium)
                        </span>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Saya telah membaca dan menyetujui seluruh tata tertib laboratorium Teknik Mesin Unwahas. Saya wajib mengenakan Jas Lab, Kacamata Pelindung, dan Sepatu Safety saat beroperasi, serta bertanggung jawab penuh atas keutuhan dan kalibrasi unit.
                        </p>
                        <p class="text-[11px] text-rose-600 font-medium">
                            Peringatan: Kerusakan akibat kelalaian operasional menjadi beban penggantian pihak peminjam dan menangguhkan penerbitan Surat Bebas Lab.
                        </p>
                    </div>
                </label>
                @error('integrity_pledge')
                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Error alert Step 3 -->
            <div x-show="step3Error" x-cloak class="p-3 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center gap-2 text-xs font-medium">
                <span class="material-symbols-outlined text-base shrink-0">error</span>
                <span x-text="step3Error"></span>
            </div>

            <!-- Wizard Step 3 Nav -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-between items-center gap-3 pt-5 border-t border-slate-100">
                <button type="button" @click="currentStep = 2"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-full border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Sebelumnya
                </button>
                <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
                    <span class="text-xs text-slate-400 font-mono text-center sm:text-right hidden sm:block">
                        Verifikasi teknisi: &lt; 24 jam kerja
                    </span>
                    <button type="button" @click="openConfirmModal()"
                            :disabled="!integrityPledged"
                            :class="integrityPledged
                                ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs active:scale-[0.98] cursor-pointer'
                                : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed'"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-full text-xs sm:text-sm font-semibold shadow-md transition-all">
                        <span>Kirim Pengajuan Peminjaman</span>
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal Konfirmasi Akhir Sebelum Form Disubmit -->
    <div x-show="confirmModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="confirm-modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
             @click="if (!isSubmitting) confirmModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-3xl bg-white border border-slate-200 text-left shadow-2xl transition-all sm:max-w-lg w-full p-6 sm:p-7"
                 @keydown.escape.window="if (!isSubmitting) confirmModalOpen = false">
                <div class="flex items-start gap-3.5 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">verified_user</span>
                    </div>
                    <div>
                        <h3 id="confirm-modal-title" class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                            Konfirmasi Pengajuan Peminjaman
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pastikan data instrumen dan durasi yang Anda ajukan telah benar.
                        </p>
                    </div>
                </div>

                <!-- Ringkasan Konfirmasi -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 mb-4 space-y-2 text-xs">
                    <div class="flex justify-between py-0.5 border-b border-slate-200/70">
                        <span class="text-slate-500">Peminjam:</span>
                        <span class="font-bold text-slate-900">{{ $user->name }} ({{ $user->identity_number }})</span>
                    </div>
                    <div class="flex justify-between py-0.5 border-b border-slate-200/70">
                        <span class="text-slate-500">Alat Diajukan:</span>
                        <span class="font-bold text-slate-900" x-text="selectedTool?.name"></span>
                    </div>
                    <div class="flex justify-between py-0.5 border-b border-slate-200/70">
                        <span class="text-slate-500">Jumlah Unit:</span>
                        <span class="font-mono font-bold text-blue-600" x-text="singleQuantity + ' Unit'"></span>
                    </div>
                    <div class="flex justify-between py-0.5 border-b border-slate-200/70">
                        <span class="text-slate-500">Laboratorium:</span>
                        <span class="font-bold text-slate-900" x-text="currentLabName"></span>
                    </div>
                    <div class="flex justify-between py-0.5">
                        <span class="text-slate-500">Jadwal Pinjam:</span>
                        <span class="font-mono font-bold text-slate-900">
                            <span x-text="startDate"></span> s.d. <span x-text="endDate"></span>
                            <span class="text-blue-600" x-text="'(' + loanDuration + ' Hari)'"></span>
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    <button type="button" @click="confirmModalOpen = false" :disabled="isSubmitting"
                            class="px-4 py-2.5 rounded-full border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 text-xs sm:text-sm font-semibold transition-all cursor-pointer">
                        Periksa Kembali
                    </button>
                    <button type="button" @click="submitFinalForm()" :disabled="isSubmitting"
                            class="px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-xs transition-all cursor-pointer flex items-center justify-center gap-1.5">
                        <span x-show="!isSubmitting" class="material-symbols-outlined text-base">check_circle</span>
                        <span x-show="isSubmitting" class="material-symbols-outlined text-base animate-spin" style="display: none;">progress_activity</span>
                        <span x-text="isSubmitting ? 'Mengirim...' : 'Ya, Kirim Pengajuan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function loanFormWizard(config) {
        const availableTools = config.availableTools || [];
        const initialToolId = config.initialToolId;
        const profileComplete = config.profileComplete;

        let selectedId = '';
        let initialQuantity = 1;
        let initialLab = config.oldLab ? String(config.oldLab) : '';

        if (config.oldItems && Array.isArray(config.oldItems) && config.oldItems.length > 0) {
            const firstOld = config.oldItems[0];
            selectedId = firstOld.tool_id ? Number(firstOld.tool_id) : '';
            initialQuantity = Number(firstOld.quantity ?? 1);
        } else if (initialToolId) {
            selectedId = Number(initialToolId);
        } else if (availableTools.length > 0) {
            selectedId = availableTools[0].id;
        }

        const foundTool = availableTools.find(t => t.id == selectedId);
        if (foundTool && !initialLab) {
            initialLab = String(foundTool.laboratory_id || '');
        }

        return {
            currentStep: config.initialStep || 1,
            personalDataConfirmed: (config.initialStep || 1) > 1,
            step1Error: '',
            step2Error: '',
            step3Error: '',
            confirmModalOpen: false,
            isSubmitting: false,
            startDate: '{{ old('start_date', now()->addDays(2)->format('Y-m-d')) }}',
            endDate: '{{ old('end_date', now()->addDays(5)->format('Y-m-d')) }}',
            integrityPledged: false,
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
            idError: '',
            availableTools: availableTools,
            laboratories: config.laboratories || [],
            selectedLab: initialLab,
            singleToolId: selectedId,
            singleQuantity: initialQuantity,

            get selectedTool() {
                return this.availableTools.find(t => t.id == this.singleToolId) || null;
            },

            get maxStockAvailable() {
                return this.selectedTool ? this.selectedTool.available_stock : 0;
            },

            get currentLabName() {
                const lab = this.laboratories.find(l => String(l.id) === String(this.selectedLab));
                return lab ? lab.name : '-';
            },

            get maxEndDate() {
                if (!this.startDate) return '';
                const d = new Date(this.startDate);
                d.setDate(d.getDate() + 14);
                return d.toISOString().split('T')[0];
            },

            get loanDuration() {
                if (!this.startDate || !this.endDate) return 0;
                const s = new Date(this.startDate);
                const e = new Date(this.endDate);
                const diff = Math.ceil((e - s) / (1000 * 60 * 60 * 24));
                return diff >= 0 ? diff : 0;
            },

            updateEndDateMin() {
                if (this.endDate && this.endDate < this.startDate) {
                    this.endDate = this.startDate;
                }
                if (this.maxEndDate && this.endDate > this.maxEndDate) {
                    this.endDate = this.maxEndDate;
                }
            },

            get canGoStep2() {
                if (this.profileComplete) {
                    return true;
                }
                const hasPhone = String(this.phoneNumber ?? '').trim() !== '';
                const hasIdentity = this.hasIdentityCard || this.hasNewIdCard;
                return hasPhone && hasIdentity;
            },

            onToolSelectChange() {
                const found = this.selectedTool;
                if (found) {
                    this.selectedLab = String(found.laboratory_id || '');
                    this.singleQuantity = found.available_stock > 0 ? 1 : 0;
                }
                this.step2Error = '';
            },

            decrementQty() {
                if (this.singleQuantity > 1) {
                    this.singleQuantity--;
                    this.step2Error = '';
                }
            },

            incrementQty() {
                if (this.singleQuantity < this.maxStockAvailable) {
                    this.singleQuantity++;
                    this.step2Error = '';
                }
            },

            goToStep2() {
                this.step1Error = '';
                const hasPhone = String(this.phoneNumber ?? '').trim() !== '';
                const hasIdentity = this.hasIdentityCard || this.hasNewIdCard;
                if (!hasPhone) {
                    this.step1Error = 'No. HP (WhatsApp) wajib diisi untuk koordinasi peminjaman.';
                    return;
                }
                if (!hasIdentity) {
                    this.step1Error = 'Dokumen identitas (KTM/KTP) wajib tersedia atau diunggah.';
                    return;
                }
                if (!this.personalDataConfirmed) {
                    this.step1Error = 'Harap centang konfirmasi kebenaran data pribadi peminjam.';
                    return;
                }
                this.currentStep = 2;
            },

            validateStep2() {
                this.step2Error = '';
                if (!this.singleToolId) {
                    this.step2Error = 'Harap pilih salah satu alat laboratorium.';
                    return false;
                }
                if (!this.selectedTool) {
                    this.step2Error = 'Alat yang dipilih tidak valid.';
                    return false;
                }
                if (this.maxStockAvailable <= 0) {
                    this.step2Error = 'Alat ini sedang tidak memiliki stok siap pakai (stok 0).';
                    return false;
                }
                if (this.singleQuantity < 1 || this.singleQuantity > this.maxStockAvailable) {
                    this.step2Error = 'Jumlah unit yang dipinjam tidak boleh melebihi stok tersedia.';
                    return false;
                }
                return true;
            },

            goToStep3() {
                if (this.validateStep2()) {
                    this.currentStep = 3;
                }
            },

            toggleIdUpload() {
                this.showIdUpload = !this.showIdUpload;
            },

            handleIdCardChange(event) {
                this.idError = '';
                const file = event.target.files[0];
                if (!file) {
                    this.hasNewIdCard = false;
                    this.idFileName = '';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    this.idError = 'Ukuran file melebihi batas 5MB.';
                    event.target.value = '';
                    this.hasNewIdCard = false;
                    this.idFileName = '';
                    return;
                }

                this.hasNewIdCard = true;
                this.idFileName = file.name;
            },

            handleLetterChange(event) {
                this.letterError = '';
                const file = event.target.files[0];
                this.acceptLetterFile(file);

                if (file && file.size > 5 * 1024 * 1024) {
                    this.letterError = 'Ukuran surat peminjaman melebihi batas 5MB.';
                    event.target.value = '';
                    this.selectedFileName = '';
                    this.selectedFileSize = '';
                }
            },

            handleLetterDrop(event) {
                this.dragActive = false;
                const file = event.dataTransfer?.files?.[0];
                if (!file) return;

                const input = document.getElementById('loan_letter');
                if (input) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                }

                this.letterError = '';
                this.acceptLetterFile(file);

                if (file.size > 5 * 1024 * 1024) {
                    this.letterError = 'Ukuran surat peminjaman melebihi batas 5MB.';
                    if (input) input.value = '';
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

            openConfirmModal() {
                this.step3Error = '';

                if (!this.startDate) {
                    this.step3Error = 'Tanggal mulai pinjam wajib diisi.';
                    return;
                }
                if (!this.endDate) {
                    this.step3Error = 'Tanggal pengembalian wajib diisi.';
                    return;
                }
                if (this.endDate < this.startDate) {
                    this.step3Error = 'Tanggal pengembalian tidak boleh sebelum tanggal mulai pinjam.';
                    return;
                }
                if (this.loanDuration > 14) {
                    this.step3Error = 'Maksimal durasi peminjaman adalah 14 hari.';
                    return;
                }

                const purposeEl = document.getElementById('purpose');
                if (!purposeEl || !purposeEl.value.trim()) {
                    this.step3Error = 'Keperluan peminjaman wajib diisi.';
                    return;
                }

                const letterInput = document.getElementById('loan_letter');
                const hasFile = (letterInput && letterInput.files && letterInput.files.length > 0) || this.selectedFileName;
                if (!hasFile) {
                    this.step3Error = 'Surat peminjaman wajib diunggah.';
                    return;
                }

                if (!this.integrityPledged) {
                    this.step3Error = 'Anda wajib menyetujui pakta integritas dan keselamatan kerja K3.';
                    return;
                }

                this.confirmModalOpen = true;
            },

            submitFinalForm() {
                if (this.isSubmitting) return;
                this.isSubmitting = true;
                const form = document.getElementById('loan-submission-form');
                if (form) form.submit();
            }
        };
    }
</script>
@endpush
