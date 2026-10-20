<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Ubah Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Perbarui data identitas, kontak, dan dokumen verifikasi akun Anda.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @php
            $isLabStaff = $user->isLaboran() || $user->isKepalaLab();
        @endphp

        <div>
            <x-input-label for="identity_type" :value="__('Jenis Identitas')" />
            @if ($isLabStaff)
                <input type="text" value="Staf/Pegawai Laboratorium" disabled
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed dark:bg-gray-800 dark:text-gray-400" />
            @else
                <select id="identity_type" name="identity_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" autocomplete="off">
                    <option value="">- Pilih Jenis Identitas -</option>
                    <option value="mahasiswa" @selected(old('identity_type', $user->identity_type) === 'mahasiswa')>Mahasiswa</option>
                    <option value="umum" @selected(old('identity_type', $user->identity_type) === 'umum')>Umum</option>
                </select>
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('identity_type')" />
        </div>

        <div>
            <x-input-label for="identity_number" :value="$isLabStaff ? __('NIP / No. Pegawai') : __('Nomor Identitas (NIM / NIK)')" />
            <x-text-input id="identity_number" name="identity_number" type="text" class="mt-1 block w-full" :value="old('identity_number', $user->identity_number)" autocomplete="off" />
            <x-input-error class="mt-2" :messages="$errors->get('identity_number')" />
        </div>

        @if ($isLabStaff)
            <div>
                <x-input-label for="role_display" :value="__('Role Akun (Terkunci)')" />
                <input id="role_display" type="text" value="{{ $user->role === 'laboran' ? 'Laboran' : 'Kepala Lab' }}" disabled
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed dark:bg-gray-800 dark:text-gray-400" />
            </div>

            <div>
                <x-input-label for="laboratory_display" :value="__('Lab Tugas (Terkunci - hanya dapat diubah Kepala Lab)')" />
                <input id="laboratory_display" type="text" value="{{ $user->laboratory?->name ?? 'Belum Ditugaskan' }}" disabled
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed dark:bg-gray-800 dark:text-gray-400" />
            </div>
        @endif

        <div>
            <x-input-label for="phone_number" :value="__('No. WhatsApp')" />
            <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" placeholder="08xxxxxxxxxx" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
        </div>

        <div>
            <x-input-label for="institution" :value="__('Institusi / Domisili')" />
            <x-text-input id="institution" name="institution" type="text" class="mt-1 block w-full" :value="old('institution', $user->institution)" autocomplete="organization" />
            <x-input-error class="mt-2" :messages="$errors->get('institution')" />
        </div>

        <div>
            <x-input-label for="identity_card_file" :value="__('Dokumen Identitas (KTM / KTP)')" />

            @if ($user->identity_card_path)
                <div class="mt-2 flex items-center justify-between gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-800 dark:bg-green-900/20">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="h-5 w-5 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300 truncate">
                            {{ __('Dokumen aktif') }}: <span class="font-medium">{{ basename($user->identity_card_path) }}</span>
                        </span>
                    </div>
                    <a href="{{ route('documents.identity', $user) }}" target="_blank" rel="noopener"
                        class="shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                        {{ __('Lihat Dokumen') }}
                    </a>
                </div>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    {{ __('Unggah berkas baru hanya jika ingin memperbarui dokumen identitas (JPG/PNG/WebP/PDF, maks 5MB).') }}
                </p>
            @else
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    {{ __('Belum ada dokumen identitas terunggah. Format JPG/PNG/WebP/PDF, maks 5MB.') }}
                </p>
            @endif

            <div x-data="{
                fileName: '',
                compressionInfo: '',
                isCompressing: false,
                errorMessage: '',
                handleFileChange(event) {
                    this.errorMessage = '';
                    this.compressionInfo = '';
                    const file = event.target.files[0];
                    if (!file) return;

                    if (['image/jpeg', 'image/png', 'image/webp', 'image/jpg'].includes(file.type)) {
                        if (file.size > 1024 * 1024) {
                            this.compressImage(file);
                            return;
                        }
                        this.fileName = file.name;
                        return;
                    }

                    if (file.type === 'application/pdf') {
                        if (file.size > 5 * 1024 * 1024) {
                            this.errorMessage = 'Ukuran file PDF melebihi batas 5MB.';
                            event.target.value = '';
                            this.fileName = '';
                            return;
                        }
                        this.fileName = file.name;
                        return;
                    }

                    this.errorMessage = 'Format berkas tidak didukung (JPG, PNG, atau PDF).';
                    event.target.value = '';
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
                                if (!blob) return;
                                const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.jpg', { type: 'image/jpeg' });
                                const dt = new DataTransfer();
                                dt.items.add(compressedFile);
                                document.getElementById('identity_card_file').files = dt.files;
                                const origMb = (file.size / (1024 * 1024)).toFixed(2);
                                const newKb = (blob.size / 1024).toFixed(1);
                                this.compressionInfo = 'Foto (' + origMb + ' MB) berhasil dikompres menjadi ' + newKb + ' KB';
                                this.fileName = compressedFile.name;
                            }, 'image/jpeg', 0.82);
                        };
                        image.onerror = () => { this.isCompressing = false; };
                        image.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }">
                <input id="identity_card_file" name="identity_card_file" type="file" accept="image/jpeg,image/png,image/webp,application/pdf"
                    @change="handleFileChange($event)"
                    class="mt-2 block w-full text-sm text-gray-600 dark:text-gray-400
                           file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-1.5
                           file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200
                           dark:file:bg-gray-800 dark:file:text-gray-300 dark:hover:file:bg-gray-700" />
                <p x-show="isCompressing" x-cloak class="mt-1 text-xs text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                    <span>Mengompres foto...</span>
                </p>
                <p x-show="compressionInfo" x-cloak class="mt-1 text-xs text-green-600 dark:text-green-400" x-text="compressionInfo"></p>
                <p x-show="errorMessage" x-cloak class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="errorMessage"></p>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('identity_card_file')" />
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>

            <button type="button" @click="isEditing = false"
                    class="inline-flex items-center gap-1.5 border border-outline-variant text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container transition-colors">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                {{ __('Batal / Kembali') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
