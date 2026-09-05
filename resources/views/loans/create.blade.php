@extends('layouts.catalog', ['title' => 'Formulir Pengajuan Peminjaman'])

@section('content')
<div x-data="loanFormWizard({
    initialToolId: {{ Js::from(request('tool_id')) }},
    availableTools: {{ Js::from($toolsList ?? []) }}
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
                <span class="mt-2 font-label-md text-label-md" :class="currentStep >= 1 ? 'text-primary font-bold' : 'text-on-surface-variant'">Identitas</span>
            </div>

            <!-- Step 2 Indicator -->
            <div class="flex flex-col items-center cursor-pointer" @click="if(currentStep > 2) currentStep = 2">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-label-md text-label-md shadow-sm border-2 transition-colors font-bold"
                     :class="currentStep >= 2 ? 'bg-primary text-on-primary border-primary' : 'bg-surface text-on-surface-variant border-surface-variant'">
                    2
                </div>
                <span class="mt-2 font-label-md text-label-md" :class="currentStep >= 2 ? 'text-primary font-bold' : 'text-on-surface-variant'">Pilih Alat</span>
            </div>

            <!-- Step 3 Indicator -->
            <div class="flex flex-col items-center">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-label-md text-label-md shadow-sm border-2 transition-colors font-bold"
                     :class="currentStep >= 3 ? 'bg-primary text-on-primary border-primary' : 'bg-surface text-on-surface-variant border-surface-variant'">
                    3
                </div>
                <span class="mt-2 font-label-md text-label-md" :class="currentStep >= 3 ? 'text-primary font-bold' : 'text-on-surface-variant'">Jadwal &amp; Keperluan</span>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <form action="{{ route('loans.store') }}" method="POST" enctype="multipart/form-data" 
          class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant p-6 md:p-8">
        @csrf

        <!-- STEP 1: Data Identitas Peminjam -->
        <div x-show="currentStep === 1" x-transition>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 border-b border-surface-variant pb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">person</span>
                Data Peminjam
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Nama Mahasiswa -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface" for="borrower_name">Nama Lengkap <span class="text-error">*</span></label>
                    <input class="w-full h-11 px-3 py-2 border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest" 
                           id="borrower_name" 
                           name="borrower_name" 
                           value="{{ old('borrower_name', auth()->user()->name ?? '') }}" 
                           required 
                           type="text"/>
                </div>

                <!-- No HP -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface" for="borrower_phone">No. HP (WhatsApp) <span class="text-error">*</span></label>
                    <input class="w-full h-11 px-3 py-2 border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest" 
                           id="borrower_phone" 
                           name="borrower_phone" 
                           value="{{ old('borrower_phone', auth()->user()->phone_number ?? '') }}" 
                           placeholder="08xxxxxxxxxx" 
                           required 
                           type="tel"/>
                </div>

                <!-- NIM / NIP -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface" for="borrower_identity">NIM / NIP <span class="text-error">*</span></label>
                    <input class="w-full h-11 px-3 py-2 border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest font-mono" 
                           id="borrower_identity" 
                           name="borrower_identity" 
                           value="{{ old('borrower_identity', auth()->user()->identity_number ?? '') }}" 
                           required 
                           type="text"/>
                </div>

                <!-- Instansi / Prodi -->
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-label-md text-on-surface" for="institution">Program Studi / Instansi <span class="text-error">*</span></label>
                    <input class="w-full h-11 px-3 py-2 border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary bg-surface-container-lowest" 
                           id="institution" 
                           name="institution" 
                           value="{{ old('institution', 'Teknik Mesin Unwahas') }}" 
                           placeholder="Contoh: Teknik Mesin S1" 
                           required 
                           type="text"/>
                </div>
            </div>

            <!-- Upload KTM -->
            <div class="flex flex-col gap-2 mb-6">
                <label class="font-label-md text-label-md text-on-surface">Unggah Kartu Tanda Mahasiswa (KTM) <span class="text-error">*</span></label>
                <div class="border-2 border-dashed border-outline-variant rounded-xl p-8 flex flex-col items-center justify-center bg-surface-container-low/40 hover:bg-surface-container-low transition-colors cursor-pointer relative group">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant group-hover:text-primary transition-colors mb-2">cloud_upload</span>
                    <p class="font-body-md text-body-md text-on-surface text-center mb-1">
                        <span class="font-semibold text-primary">Klik untuk memilih berkas KTM</span> atau seret file ke sini
                    </p>
                    <p class="font-caption-xs text-caption-xs text-on-surface-variant">JPG, PNG, PDF (Maksimal 2MB)</p>
                    <input id="identity_card" name="identity_card" accept=".jpg,.jpeg,.png,.pdf" class="absolute inset-0 opacity-0 cursor-pointer" type="file" required/>
                </div>
                @error('identity_card')
                    <p class="font-caption-xs text-caption-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Wizard Step 1 Nav -->
            <div class="flex justify-end pt-4 border-t border-surface-variant">
                <button type="button" @click="currentStep = 2" 
                        class="bg-primary text-on-primary font-label-md text-label-md px-6 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                    Selanjutnya
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- STEP 2: Pilih Multi-Item Alat -->
        <div x-show="currentStep === 2" x-transition style="display: none;">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 border-b border-surface-variant pb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">handyman</span>
                Daftar Alat yang Dipinjam
            </h2>

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
                                                required
                                                class="w-full h-11 px-3 border border-outline-variant rounded-lg font-body-md text-body-md bg-surface-container-lowest focus:ring-primary focus:border-primary">
                                            <option value="" disabled>-- Pilih Alat Laboratorium --</option>
                                            <template x-for="t in availableTools" :key="t.id">
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
                                            <button type="button" @click="if(item.quantity > 1) item.quantity--" 
                                                    class="w-8 h-9 flex items-center justify-center text-on-surface-variant hover:bg-surface-container transition-colors">
                                                <span class="material-symbols-outlined text-[18px]">remove</span>
                                            </button>
                                            <input :name="'items[' + index + '][quantity]'" 
                                                   type="number" 
                                                   x-model.number="item.quantity" 
                                                   :max="item.max_stock" 
                                                   min="1" 
                                                   required
                                                   class="w-12 h-9 text-center font-body-md text-body-md text-on-surface border-none focus:ring-0 p-0"/>
                                            <button type="button" @click="if(item.quantity < item.max_stock) item.quantity++" 
                                                    class="w-8 h-9 flex items-center justify-center text-on-surface-variant hover:bg-surface-container transition-colors">
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
                <button type="button" @click="currentStep = 3" 
                        class="bg-primary text-on-primary font-label-md text-label-md px-6 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                    Selanjutnya
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
        </div>

        <!-- STEP 3: Jadwal, Keperluan & Konfirmasi -->
        <div x-show="currentStep === 3" x-transition style="display: none;">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 border-b border-surface-variant pb-4 flex items-center gap-2 font-bold">
                <span class="material-symbols-outlined text-primary">calendar_month</span>
                Jadwal &amp; Keperluan Peminjaman
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

            <!-- Agreement Checkbox -->
            <div class="p-4 bg-surface-container-low rounded-xl border border-outline-variant mb-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" required class="mt-1 rounded border-outline-variant text-primary focus:ring-primary w-5 h-5"/>
                    <span class="font-body-md text-body-md text-on-surface text-sm">
                        Saya menyatakan data yang diisikan benar dan bersedia menjaga kondisi fisik peralatan laboratorium serta mengembalikannya tepat waktu sesuai jadwal yang disetujui.
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

        let initialItem = { tool_id: '', quantity: 1, max_stock: 0 };
        if (initialToolId) {
            const found = availableTools.find(t => t.id == initialToolId);
            if (found) {
                initialItem = { tool_id: found.id, quantity: 1, max_stock: found.available_stock };
            }
        }

        return {
            currentStep: 1,
            availableTools: availableTools,
            items: [initialItem],
            addItem() {
                this.items.push({ tool_id: '', quantity: 1, max_stock: 0 });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            updateToolInfo(item) {
                const found = this.availableTools.find(t => t.id == item.tool_id);
                if (found) {
                    item.max_stock = found.available_stock;
                    if (item.quantity > item.max_stock) {
                        item.quantity = item.max_stock > 0 ? 1 : 0;
                    }
                }
            }
        };
    }
</script>
@endpush
