@extends('layouts.catalog', ['title' => 'Daftar Akun - LabCatalog'])

@section('content')
<div class="mx-auto w-full max-w-3xl px-4 sm:px-6 lg:px-8 py-8 lg:py-12 flex-1 flex items-center justify-center">
    <div class="w-full bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 relative overflow-hidden"
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

        <!-- Subtle Top Accent -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-blue-600"></div>

        <!-- Header -->
        <div class="mb-8">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[11px] font-bold tracking-wide uppercase mb-2">
                Pendaftaran Akun Baru
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Daftar Akun LabCatalog</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Lengkapi data identitas berikut untuk mendapatkan akses peminjaman peralatan di 6 Laboratorium Teknik Mesin Unwahas.</p>
        </div>

        <!-- Alert Kesalahan Validasi -->
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <span class="material-symbols-outlined text-rose-600 text-lg">error</span>
                    <span>Terdapat kesalahan pengisian formulir:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 ml-6">
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
                <label for="name" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           autofocus
                           placeholder="Contoh: Budi Santoso"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                </div>
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jenis Peminjam (Radio Cards) -->
            <div>
                <span class="block text-xs sm:text-sm font-semibold text-slate-700 mb-2">
                    Kategori Peminjam <span class="text-rose-500">*</span>
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-start gap-3 p-3.5 sm:p-4 rounded-2xl border cursor-pointer transition-all"
                           :class="borrowerType === 'mahasiswa' ? 'border-blue-600 bg-blue-50/70 shadow-xs ring-1 ring-blue-600' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'">
                        <input type="radio"
                               name="identity_type"
                               value="mahasiswa"
                               x-model="borrowerType"
                               class="mt-1 h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                        <div>
                            <span class="block text-xs sm:text-sm font-bold text-slate-900">Mahasiswa Unwahas</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5 leading-snug">Peminjaman menggunakan NIM &amp; Kartu Tanda Mahasiswa (KTM).</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 sm:p-4 rounded-2xl border cursor-pointer transition-all"
                           :class="borrowerType === 'umum' ? 'border-blue-600 bg-blue-50/70 shadow-xs ring-1 ring-blue-600' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/50'">
                        <input type="radio"
                               name="identity_type"
                               value="umum"
                               x-model="borrowerType"
                               class="mt-1 h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                        <div>
                            <span class="block text-xs sm:text-sm font-bold text-slate-900">Masyarakat / Umum / Mitra</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5 leading-snug">Peminjaman menggunakan NIK &amp; Kartu Tanda Penduduk (KTP).</span>
                        </div>
                    </label>
                </div>
                @error('identity_type')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- NIM / NIK & Program Studi / Instansi (2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- NIM (Mahasiswa) / NIK (Umum) -->
                <div>
                    <label for="identity_number" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        <span x-text="borrowerType === 'mahasiswa' ? 'NIM (Nomor Induk Mahasiswa)' : 'NIK (Nomor Induk KTP)'"></span> <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">badge</span>
                        <input type="text"
                               id="identity_number"
                               name="identity_number"
                               value="{{ old('identity_number') }}"
                               required
                               :placeholder="borrowerType === 'mahasiswa' ? 'Contoh: 21102001' : 'Contoh: 3374012345670001'"
                               class="w-full font-mono rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                    </div>
                    @error('identity_number')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Program Studi / Instansi -->
                <div>
                    <label for="institution" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        <span x-text="borrowerType === 'mahasiswa' ? 'Program Studi / Fakultas' : 'Nama Instansi / Domisili'"></span> <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">school</span>
                        <input type="text"
                               id="institution"
                               name="institution"
                               value="{{ old('institution') }}"
                               required
                               :placeholder="borrowerType === 'mahasiswa' ? 'Contoh: Teknik Mesin S1' : 'Contoh: CV Karya Teknik - Semarang'"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                    </div>
                    @error('institution')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- No. WhatsApp & Email (2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- No. WhatsApp -->
                <div>
                    <label for="phone_number" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        No. WhatsApp <span class="text-rose-500">*</span>
                        <span class="text-[11px] font-normal text-slate-400">(Untuk Notifikasi Peminjaman)</span>
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">call</span>
                        <input type="tel"
                               id="phone_number"
                               name="phone_number"
                               value="{{ old('phone_number') }}"
                               required
                               placeholder="Contoh: 081234567890"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                    </div>
                    @error('phone_number')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">mail</span>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               placeholder="mahasiswa@unwahas.ac.id"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                    </div>
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password & Konfirmasi Password (2 Kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               placeholder="Minimal 8 karakter"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                        Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock_reset</span>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               required
                               placeholder="Ulangi kata sandi"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 shadow-xs transition-all" />
                    </div>
                </div>
            </div>

            <!-- Upload Berkas Identitas (KTM / KTP) -->
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-4 sm:p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm font-semibold text-slate-800">
                        <span x-text="borrowerType === 'mahasiswa' ? 'Unggah Kartu Tanda Mahasiswa (KTM)' : 'Unggah Kartu Tanda Penduduk (KTP)'"></span>
                        <span class="text-rose-500">*</span>
                    </span>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Format: JPG, PNG, PDF</span>
                </div>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                    Unggah foto kartu identitas resmi yang masih berlaku. Foto resolusi tinggi dari kamera smartphone akan otomatis dikompres oleh sistem untuk mempercepat proses.
                </p>

                <input accept="image/jpeg,image/png,image/webp,application/pdf"
                       class="sr-only"
                       id="identity_card_file"
                       name="identity_card_file"
                       required
                       type="file"
                       x-ref="fileInput"
                       @change="handleFileChange($event)"/>

                <div class="flex items-center gap-3 flex-wrap">
                    <button type="button"
                            :disabled="isCompressing"
                            @click="$refs.fileInput.click()"
                            class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-xs transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-lg text-blue-600" x-show="!isCompressing">upload_file</span>
                        <span class="material-symbols-outlined text-lg text-blue-600 animate-spin" x-show="isCompressing" x-cloak>progress_activity</span>
                        <span x-text="isCompressing ? 'Sedang Mengompres Foto...' : 'Pilih Berkas Identitas'"></span>
                    </button>

                    <div class="flex items-center gap-1.5 text-xs text-slate-600 truncate max-w-full">
                        <span class="material-symbols-outlined text-emerald-600 text-base shrink-0" x-cloak x-show="fileName && !errorMessage">check_circle</span>
                        <span x-text="fileName || 'Belum ada berkas dipilih'" class="truncate font-medium"></span>
                        <span x-show="fileSize" x-text="'(' + fileSize + ')'" class="font-mono text-[11px] text-slate-400 shrink-0"></span>
                    </div>
                </div>

                <!-- Info Kompresi Otomatis Sukses -->
                <div x-show="compressionInfo" x-cloak class="mt-3 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 text-base shrink-0">auto_fix_high</span>
                    <span x-text="compressionInfo"></span>
                </div>

                <!-- Error Pesan Client-Side -->
                <div x-show="errorMessage" x-cloak class="mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-600 text-base shrink-0">error</span>
                    <span x-text="errorMessage"></span>
                </div>

                @error('identity_card_file')
                    <p class="mt-2 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit"
                        :disabled="isCompressing"
                        class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-xs hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-base" x-show="!isCompressing">person_add</span>
                    <span class="material-symbols-outlined text-base animate-spin" x-show="isCompressing" x-cloak>progress_activity</span>
                    <span x-text="isCompressing ? 'Sedang Menyiapkan Berkas...' : 'Daftar Akun Sekarang'"></span>
                </button>
            </div>
        </form>

        <!-- Switcher to Login -->
        <div class="mt-8 pt-5 border-t border-slate-100 text-center">
            <p class="text-xs sm:text-sm text-slate-500">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-700 hover:underline ml-1">
                    Masuk ke akun Anda
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
