@extends('layouts.admin', ['title' => 'Tambah Alat Baru', 'pageTitle' => 'Tambah Alat Baru'])

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb & Back -->
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.tools.index') }}" class="inline-flex items-center gap-1.5 text-on-surface-variant hover:text-primary transition-colors font-label-md text-label-md">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Kembali ke Daftar Alat
        </a>
    </div>

    <!-- Card Form -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <div class="p-6 border-b border-outline-variant bg-surface-container-low/50">
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Informasi Data Alat</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Lengkapi data teknis, kategori, kuantitas stok, dan foto alat laboratorium.</p>
        </div>

        <form action="{{ route('admin.tools.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6"
              x-data="{
                  laboratoryId: @js(auth()->user()->isLaboran() ? auth()->user()->laboratory_id : old('laboratory_id', '')),
                  categoryId: @js(old('category_id', '')),
                  isGeneratingCode: false,
                  setLaboratory(event) {
                      this.laboratoryId = event.target.value;
                      this.autoGenerateCode();
                  },
                  setCategory(event) {
                      this.categoryId = event.target.value;
                      this.autoGenerateCode();
                  },
                  async autoGenerateCode() {
                      if (!this.laboratoryId || !this.categoryId) return;
                      const codeInput = document.getElementById('code');
                      if (!codeInput) return;
                      this.isGeneratingCode = true;
                      try {
                          const params = new URLSearchParams({ laboratory_id: this.laboratoryId, category_id: this.categoryId });
                          const response = await fetch('{{ route('admin.tools.generate-code') }}?' + params.toString());
                          if (!response.ok) return;
                          const data = await response.json();
                          if (data.code) {
                              codeInput.value = data.code;
                              codeInput.dispatchEvent(new Event('input', { bubbles: true }));
                          }
                      } catch (error) {
                          console.error('Gagal men-generate kode alat:', error);
                      } finally {
                          this.isGeneratingCode = false;
                      }
                  }
              }">
            @csrf

            <!-- Laboratorium Tujuan -->
            @if (auth()->user()->isLaboran())
                <input type="hidden" name="laboratory_id" value="{{ auth()->user()->laboratory_id }}"/>
                <div class="p-4 rounded-xl bg-primary-container/40 border border-outline-variant flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">science</span>
                    <div>
                        <span class="block font-caption-xs text-caption-xs text-on-surface-variant">Laboratorium</span>
                        <span class="font-label-md text-label-md text-on-surface font-semibold">{{ auth()->user()->laboratory?->name ?? '-' }}</span>
                        <span class="block font-caption-xs text-caption-xs text-on-surface-variant mt-0.5">Otomatis sesuai laboratorium yang Anda ampu.</span>
                    </div>
                </div>
            @else
                <div>
                    <label for="laboratory_id" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Laboratorium Tujuan <span class="text-error">*</span>
                    </label>
                    <select id="laboratory_id" name="laboratory_id" required @change="setLaboratory($event)"
                            class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-body-md text-body-md">
                        <option value="" disabled {{ old('laboratory_id') ? '' : 'selected' }}>Pilih Laboratorium</option>
                        @foreach ($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>
                                {{ $lab->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('laboratory_id')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kategori -->
                <div>
                    <label for="category_id" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Kategori Alat <span class="text-error">*</span>
                    </label>
                    <select id="category_id" name="category_id" required @change="setCategory($event)"
                            class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-body-md text-body-md">
                        <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Pilih Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kode Alat -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="code" class="block font-label-md text-label-md text-on-surface">
                            Kode Inventaris <span class="text-error">*</span>
                        </label>
                        <span x-show="isGeneratingCode" x-cloak
                              class="inline-flex items-center gap-1 font-caption-xs text-caption-xs text-primary">
                            <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                            Men-generate kode...
                        </span>
                    </div>
                    <input id="code" name="code" type="text" value="{{ old('code') }}" required
                           placeholder="Contoh: LABTM1-UK-0926-001"
                           class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-body-md text-body-md uppercase font-mono"/>
                    <p class="mt-1 font-caption-xs text-caption-xs text-on-surface-variant">
                        Kode di-generate otomatis sesuai format LABTM{Lab}-{Kategori}-{BulanTahun}-{NoUrut}. Masih dapat disunting manual.
                    </p>
                    @error('code')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Alat -->
                <div class="md:col-span-2">
                    <label for="name" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Nama Alat <span class="text-error">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                           placeholder="Contoh: Mikroskop Binokuler Digital Pro"
                           class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-body-md text-body-md"/>
                    @error('name')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Spesifikasi -->
                <div class="md:col-span-2">
                    <label for="specification" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Spesifikasi Teknis & Deskripsi
                    </label>
                    <textarea id="specification" name="specification" rows="4"
                              placeholder="Rentang ukur, ketelitian, material, kelengkapan aksesoris, petunjuk operasional..."
                              class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-body-md text-body-md">{{ old('specification') }}</textarea>
                    @error('specification')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Stok Fisik -->
                <div>
                    <label for="total_stock" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Jumlah Total Stok Fisik <span class="text-error">*</span>
                    </label>
                    <input id="total_stock" name="total_stock" type="number" min="0" value="{{ old('total_stock', 1) }}" required
                           class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-body-md text-body-md"/>
                    @error('total_stock')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div class="flex flex-col justify-center">
                    <label class="block font-label-md text-label-md text-on-surface mb-2">Status Ketersediaan</label>
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="rounded border-outline-variant text-primary focus:ring-primary w-5 h-5"/>
                        <span class="font-body-md text-body-md text-on-surface">Tampilkan & aktifkan di E-Catalog publik</span>
                    </label>
                </div>

                <!-- Unggah Foto Alat (dengan Auto-Compression via HTML5 Canvas) -->
                <div class="md:col-span-2"
                     x-data="{
                         previewUrl: null,
                         fileName: '',
                         fileSize: '',
                         compressionInfo: '',
                         isCompressing: false,
                         errorMessage: '',
                         handleImageChange(event) {
                             this.errorMessage = '';
                             this.compressionInfo = '';
                             const file = event.target.files[0];
                             if (!file) {
                                 this.clearImage();
                                 return;
                             }
                             if (!['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.type)) {
                                 this.errorMessage = 'Format file tidak didukung. Harap pilih gambar JPG, PNG, atau WebP.';
                                 this.clearImage();
                                 event.target.value = '';
                                 return;
                             }
                             if (file.size > 1024 * 1024) {
                                 this.compressImage(file);
                                 return;
                             }
                             this.applyPreview(file, '');
                         },
                         compressImage(file) {
                             this.isCompressing = true;
                             const reader = new FileReader();
                             reader.onload = (e) => {
                                 const image = new Image();
                                 image.onload = () => {
                                     const maxDimension = 1280;
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
                                         this.isCompressing = false;
                                         if (!blob) {
                                             this.errorMessage = 'Gagal mengompres gambar. Silakan coba file lain.';
                                             this.clearImage();
                                             event.target.value = '';
                                             return;
                                         }
                                         const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', { type: 'image/jpeg' });
                                         const dataTransfer = new DataTransfer();
                                         dataTransfer.items.add(compressedFile);
                                         document.getElementById('image').files = dataTransfer.files;
                                         const originalSizeMb = (file.size / (1024 * 1024)).toFixed(2);
                                         const newSizeKb = (blob.size / 1024).toFixed(1);
                                         this.applyPreview(compressedFile, 'Foto asli ' + originalSizeMb + ' MB berhasil dikompres otomatis menjadi ' + newSizeKb + ' KB');
                                     }, 'image/jpeg', 0.8);
                                 };
                                 image.onerror = () => {
                                     this.isCompressing = false;
                                     this.errorMessage = 'Gagal memuat gambar. File mungkin rusak.';
                                     this.clearImage();
                                     event.target.value = '';
                                 };
                                 image.src = e.target.result;
                             };
                             reader.readAsDataURL(file);
                         },
                         applyPreview(file, compressionInfo) {
                             this.fileName = file.name;
                             this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
                             this.compressionInfo = compressionInfo;
                             const reader = new FileReader();
                             reader.onload = (e) => { this.previewUrl = e.target.result; };
                             reader.readAsDataURL(file);
                         },
                         clearImage() {
                             this.previewUrl = null;
                             this.fileName = '';
                             this.fileSize = '';
                             this.compressionInfo = '';
                             const input = document.getElementById('image');
                             if (input) input.value = '';
                         }
                     }">
                    <label class="block font-label-md text-label-md text-on-surface mb-1.5">Foto / Gambar Alat</label>

                    <!-- Alert Validasi Error Sisi Klien -->
                    <div x-show="errorMessage" x-cloak class="p-3 mb-3 rounded-lg bg-error/10 border border-error/20 text-error flex items-center justify-between gap-2 text-xs font-medium">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">error</span>
                            <span x-text="errorMessage"></span>
                        </div>
                        <button type="button" @click="errorMessage = ''" class="hover:opacity-75">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>

                    <!-- Kartu Pratinjau Foto (Ketika Foto Dipilih) -->
                    <div x-show="previewUrl" x-cloak class="p-4 rounded-xl border border-outline-variant bg-surface-container-low/50 flex flex-col sm:flex-row items-center sm:items-start gap-4">
                        <img :src="previewUrl" :alt="fileName" class="h-36 w-36 object-contain rounded-lg border border-outline-variant bg-surface-container flex-shrink-0" />
                        <div class="flex-1 min-w-0 text-center sm:text-left flex flex-col justify-between h-full py-1">
                            <div>
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-badge-xs font-medium bg-primary/10 text-primary border border-primary/20">
                                        Foto Baru Terpilih
                                    </span>
                                    <span x-show="compressionInfo" x-cloak
                                          class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-badge-xs font-medium bg-status-success-bg text-status-success-text border border-status-success-text/20">
                                        <span class="material-symbols-outlined text-xs">compress</span>
                                        <span x-text="compressionInfo"></span>
                                    </span>
                                </div>
                                <p class="font-label-md text-label-md text-on-surface truncate font-semibold" x-text="fileName"></p>
                                <p class="font-caption-xs text-caption-xs text-on-surface-variant mt-0.5" x-text="fileSize"></p>
                            </div>
                            <div class="mt-4 flex items-center justify-center sm:justify-start gap-2">
                                <button type="button" 
                                        @click="clearImage()" 
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-error/30 text-error hover:bg-error/10 font-label-md text-xs font-medium shadow-xs active:scale-[0.98] transition-all duration-150 cursor-pointer">
                                    <span class="material-symbols-outlined text-base">delete</span>
                                    Ganti / Hapus Foto
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Dropzone Default (Ketika Belum Ada Foto) -->
                    <div x-show="!previewUrl" class="border-2 border-dashed border-outline-variant rounded-xl p-6 flex flex-col items-center justify-center bg-surface-container-low/40 hover:bg-surface-container-low transition-colors cursor-pointer relative">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-2">add_photo_alternate</span>
                        <p class="font-body-md text-body-md text-on-surface text-center mb-1">
                            <span class="font-semibold text-primary">Klik untuk memilih gambar</span> atau seret ke sini
                        </p>
                        <p class="font-caption-xs text-caption-xs text-on-surface-variant" x-show="!isCompressing">JPG, PNG, WebP. Foto di atas 1 MB dikompres otomatis</p>
                        <p class="font-caption-xs text-caption-xs text-primary font-medium flex items-center gap-1" x-show="isCompressing" x-cloak>
                            <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                            Mengompres foto...
                        </p>
                        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/jpg" @change="handleImageChange($event)" class="absolute inset-0 opacity-0 cursor-pointer"/>
                    </div>
                    @error('image')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-6 border-t border-outline-variant flex justify-end gap-3">
                <a href="{{ route('admin.tools.index') }}" 
                   class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-5 py-2.5 rounded-lg bg-white hover:bg-slate-50 text-slate-700 font-label-md text-label-md border border-slate-300/90 hover:border-slate-400 shadow-xs active:bg-slate-100 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap px-6 py-2.5 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary rounded-lg font-label-md text-label-md shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">save</span>
                    Simpan Data Alat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
