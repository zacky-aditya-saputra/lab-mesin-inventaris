@extends('layouts.catalog')

@section('title', 'Daftar Akun')

@section('content')
<main class="flex-grow flex items-start justify-center px-gutter-md py-10">
    <div class="w-full max-w-2xl bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant p-8 relative overflow-hidden"
         x-data="{
             borrowerType: '{{ old('identity_type', 'mahasiswa') }}',
             fileName: '',
             fileSize: '',
             compressionInfo: '',
             isCompressing: false,
             errorMessage: '',
             handleFileChange(event) {
                 this.errorMessage = '';
                 this.compressionInfo = '';
                 const file = event.target.files[0];
                 if (!file) {
                     this.clearFile();
                     return;
                 }

                 if (['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.type)) {
                     if (file.size > 1024 * 1024) {
                         this.compressImage(file);
                         return;
                     }
                     this.applyFile(file, '');
                     return;
                 }

                 if (file.type === 'application/pdf') {
                     if (file.size > 2 * 1024 * 1024) {
                         this.errorMessage = 'Ukuran berkas PDF melebihi batas maksimal 2MB. Silakan pilih berkas yang lebih kecil.';
                         this.clearFile();
                         return;
                     }
                     this.applyFile(file, '');
                     return;
                 }

                 this.errorMessage = 'Format berkas tidak didukung. Harap pilih gambar JPG, PNG, atau dokumen PDF.';
                 this.clearFile();
             },
             compressImage(file) {
                 this.isCompressing = true;
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
                             this.isCompressing = false;
                             if (!blob) {
                                 this.errorMessage = 'Gagal mengompres gambar. Silakan gunakan berkas lain.';
                                 this.clearFile();
                                 return;
                             }
                             const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', { type: 'image/jpeg' });
                             const dataTransfer = new DataTransfer();
                             dataTransfer.items.add(compressedFile);
                             if (this.$refs.fileInput) {
                                 this.$refs.fileInput.files = dataTransfer.files;
                             }
                             const originalSizeMb = (file.size / (1024 * 1024)).toFixed(2);
                             const newSizeKb = (blob.size / 1024).toFixed(1);
                             this.applyFile(compressedFile, 'Foto (' + originalSizeMb + ' MB) berhasil dikompres otomatis menjadi ' + newSizeKb + ' KB');
                         }, 'image/jpeg', 0.82);
                     };
                     image.onerror = () => {
                         this.isCompressing = false;
                         this.errorMessage = 'Gagal membaca berkas gambar. Format mungkin tidak valid atau rusak.';
                         this.clearFile();
                     };
                     image.src = e.target.result;
                 };
                 reader.readAsDataURL(file);
             },
             applyFile(file, compressionInfo) {
                 this.fileName = file.name;
                 this.fileSize = (file.size / 1024).toFixed(1) + ' KB';
                 this.compressionInfo = compressionInfo;
             },
             clearFile() {
                 this.fileName = '';
                 this.fileSize = '';
                 this.compressionInfo = '';
                 if (this.$refs.fileInput) {
                     this.$refs.fileInput.value = '';
                 }
             }
         }">

        <!-- Subtle top accent -->
        <div class="absolute top-0 left-0 w-full h-1 bg-primary"></div>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="font-headline-md text-headline-md text-on-surface font-bold mb-2">Daftar Akun Baru</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Lengkapi data di bawah ini untuk mulai menggunakan layanan peminjaman alat laboratorium.</p>
        </div>

        <!-- Validation Error Alert -->
        @if ($errors->any())
            <div class="bg-error-container border border-error/30 text-on-error-container p-4 rounded-lg mb-6 shadow-sm" role="alert">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">error</span>
                    <span class="font-label-md text-label-md font-semibold">Terdapat kesalahan pendaftaran:</span>
                </div>
                <ul class="list-disc list-inside text-caption-xs space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data" class="space-y-5" @submit="if(isCompressing) { $event.preventDefault(); return false; }">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="name">
                    Nama Lengkap <span class="text-error">*</span>
                </label>
                <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary shadow-sm transition-shadow"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Contoh: Budi Santoso"
                       required
                       type="text"/>
                @error('name')
                    <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jenis Peminjam (Radio Adaptive) -->
            <div>
                <span class="block font-label-md text-label-md text-on-surface mb-1.5">Jenis Peminjam <span class="text-error">*</span></span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition-colors"
                           :class="borrowerType === 'mahasiswa' ? 'border-primary bg-primary/5' : 'border-outline-variant bg-surface-container-lowest hover:border-on-surface-variant'">
                        <input class="mt-0.5 h-4 w-4 border-outline-variant text-primary focus:ring-primary cursor-pointer"
                               name="identity_type"
                               type="radio"
                               value="mahasiswa"
                               x-model="borrowerType"/>
                        <span>
                            <span class="block font-label-md text-label-md text-on-surface font-medium">Mahasiswa Unwahas</span>
                            <span class="block font-caption-xs text-caption-xs text-on-surface-variant">Peminjaman menggunakan NIM &amp; KTM</span>
                        </span>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition-colors"
                           :class="borrowerType === 'umum' ? 'border-primary bg-primary/5' : 'border-outline-variant bg-surface-container-lowest hover:border-on-surface-variant'">
                        <input class="mt-0.5 h-4 w-4 border-outline-variant text-primary focus:ring-primary cursor-pointer"
                               name="identity_type"
                               type="radio"
                               value="umum"
                               x-model="borrowerType"/>
                        <span>
                            <span class="block font-label-md text-label-md text-on-surface font-medium">Masyarakat / Umum</span>
                            <span class="block font-caption-xs text-caption-xs text-on-surface-variant">Peminjaman menggunakan NIK &amp; KTP</span>
                        </span>
                    </label>
                </div>
                @error('identity_type')
                    <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- NIM (Mahasiswa) / NIK KTP (Umum) -->
            <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="identity_number">
                    <span x-text="borrowerType === 'mahasiswa' ? 'NIM (Nomor Induk Mahasiswa)' : 'NIK (Nomor Induk KTP)'"></span> <span class="text-error">*</span>
                </label>
                <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary shadow-sm font-mono transition-shadow"
                       id="identity_number"
                       name="identity_number"
                       value="{{ old('identity_number') }}"
                       :placeholder="borrowerType === 'mahasiswa' ? 'Contoh: 21102001' : 'Contoh: 3374012345670001'"
                       required
                       type="text"/>
                @error('identity_number')
                    <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Program Studi/Fakultas (Mahasiswa) / Nama Instansi/Domisili (Umum) -->
            <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="institution">
                    <span x-text="borrowerType === 'mahasiswa' ? 'Program Studi / Fakultas' : 'Nama Instansi / Domisili'"></span> <span class="text-error">*</span>
                </label>
                <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary shadow-sm transition-shadow"
                       id="institution"
                       name="institution"
                       value="{{ old('institution') }}"
                       :placeholder="borrowerType === 'mahasiswa' ? 'Contoh: Teknik Mesin S1' : 'Contoh: CV Karya Teknik - Semarang'"
                       required
                       type="text"/>
                @error('institution')
                    <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- No. WhatsApp -->
            <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="phone_number">
                    No. WhatsApp <span class="text-error">*</span> <span class="text-on-surface-variant font-normal">(Untuk Notifikasi)</span>
                </label>
                <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary shadow-sm transition-shadow"
                       id="phone_number"
                       name="phone_number"
                       value="{{ old('phone_number') }}"
                       placeholder="08xxxxxxxxxx"
                       required
                       type="tel"/>
                @error('phone_number')
                    <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="email">
                    Email Address <span class="text-error">*</span>
                </label>
                <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary shadow-sm transition-shadow"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="mahasiswa@unwahas.ac.id"
                       required
                       type="email"/>
                @error('email')
                    <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="password">
                        Password <span class="text-error">*</span>
                    </label>
                    <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary shadow-sm transition-shadow"
                           id="password"
                           name="password"
                           placeholder="Minimal 8 karakter"
                           required
                           type="password"/>
                    @error('password')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="password_confirmation">
                        Konfirmasi Password <span class="text-error">*</span>
                    </label>
                    <input class="w-full h-11 px-3 rounded-lg border-outline-variant bg-surface-container-lowest text-on-surface font-body-md text-body-md focus:border-primary focus:ring-1 focus:ring-primary shadow-sm transition-shadow"
                           id="password_confirmation"
                           name="password_confirmation"
                           placeholder="Ulangi password"
                           required
                           type="password"/>
                </div>
            </div>

            <!-- Upload Berkas Identitas (KTM / KTP) -->
            <div>
                <span class="block font-label-md text-label-md text-on-surface mb-1.5">
                    <span x-text="borrowerType === 'mahasiswa' ? 'Upload Kartu Tanda Mahasiswa (KTM)' : 'Upload Kartu Tanda Penduduk (KTP)'"></span> <span class="text-error">*</span>
                </span>
                <p class="font-caption-xs text-caption-xs text-on-surface-variant mb-2">Format JPG, PNG, WebP, atau PDF. Maksimal 2MB (Foto kamera HP berukuran besar akan otomatis dikompres oleh sistem).</p>

                <input accept="image/jpeg,image/png,image/webp,application/pdf"
                       class="sr-only"
                       id="identity_card_file"
                       name="identity_card_file"
                       required
                       type="file"
                       x-ref="fileInput"
                       @change="handleFileChange($event)"/>

                <div class="flex items-center gap-3 flex-wrap">
                    <button class="inline-flex items-center gap-2 border border-outline-variant bg-surface-container rounded-lg px-4 py-2 font-label-md text-label-md text-on-surface hover:bg-surface-container-high transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            type="button"
                            :disabled="isCompressing"
                            @click="$refs.fileInput.click()">
                        <span class="material-symbols-outlined text-lg" x-show="!isCompressing">upload_file</span>
                        <span class="material-symbols-outlined text-lg animate-spin" x-show="isCompressing" x-cloak>progress_activity</span>
                        <span x-text="isCompressing ? 'Mengompres Foto...' : 'Pilih Berkas'"></span>
                    </button>

                    <p class="font-caption-xs text-caption-xs text-on-surface-variant flex items-center gap-1 max-w-full truncate">
                        <span class="material-symbols-outlined text-status-success-text text-sm" x-cloak x-show="fileName && !errorMessage" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        <span x-text="fileName || 'Belum ada berkas dipilih'"></span>
                        <span x-show="fileSize" x-text="'(' + fileSize + ')'" class="font-mono ml-1 text-on-surface-variant"></span>
                    </p>
                </div>

                <!-- Info Kompresi Otomatis Sukses -->
                <div x-show="compressionInfo" x-cloak class="mt-2.5 p-2.5 bg-status-success-bg/30 border border-status-success-text/30 text-status-success-text text-xs rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">auto_fix_high</span>
                    <span x-text="compressionInfo"></span>
                </div>

                <!-- Error Pesan Client-Side -->
                <div x-show="errorMessage" x-cloak class="mt-2.5 p-2.5 bg-error/10 border border-error/20 text-error text-xs rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-base">error</span>
                    <span x-text="errorMessage"></span>
                </div>

                @error('identity_card_file')
                    <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-3">
                <button class="w-full bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md py-3 px-4 rounded-lg shadow-sm transition-colors duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isCompressing"
                        type="submit">
                    <span class="material-symbols-outlined text-sm" x-show="!isCompressing">person_add</span>
                    <span class="material-symbols-outlined text-sm animate-spin" x-show="isCompressing" x-cloak>progress_activity</span>
                    <span x-text="isCompressing ? 'Sedang Menyiapkan Berkas...' : 'Daftar Akun'"></span>
                </button>
            </div>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center border-t border-outline-variant pt-6">
            <p class="font-body-md text-body-md text-on-surface-variant">
                Sudah punya akun?
                <a class="text-primary font-label-md text-label-md font-semibold hover:underline ml-1" href="{{ route('login') }}">Masuk</a>
            </p>
        </div>
    </div>
</main>
@endsection
