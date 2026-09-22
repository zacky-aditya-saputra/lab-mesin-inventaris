@extends(auth()->user()->isLaboran() || auth()->user()->isKepalaLab() ? 'layouts.admin' : 'layouts.catalog', ['title' => 'Profil Pengguna'])

@section('content')
<div class="py-10 px-gutter-md max-w-4xl mx-auto w-full space-y-6" x-data="{ isEditing: {{ $errors->any() ? 'true' : 'false' }} }">
    <div class="mb-6">
        <h1 class="font-headline-md text-headline-md text-on-surface font-bold">Profil Akun</h1>
        <p class="font-body-md text-body-md text-on-surface-variant">Kelola data identitas, informasi kontak, dan dokumen verifikasi akun Anda.</p>
    </div>

    @php
        $identityCardExtension = $user->identity_card_path ? strtolower(pathinfo($user->identity_card_path, PATHINFO_EXTENSION)) : null;
        $identityCardIsImage = in_array($identityCardExtension, ['jpg', 'jpeg', 'png', 'webp']);
        $identityCardUrl = $user->identity_card_path ? asset('storage/'.$user->identity_card_path) : null;
    @endphp

    <!-- VIEW MODE: Kartu Identitas Digital (Read-Only) -->
    <div x-show="!isEditing" class="p-6 sm:p-8 bg-surface-container-lowest border border-outline-variant shadow-sm rounded-xl">
        <!-- Header Kartu -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-outline-variant pb-5 mb-6">
            <div>
                <h2 class="font-title-md text-title-md text-on-surface font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">badge</span>
                    Kartu Identitas Digital
                </h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Ringkasan data identitas Anda yang tersimpan di sistem.</p>
            </div>
            @if (filled($user->identity_type))
                <span class="self-start inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full font-badge-sm text-badge-sm font-semibold
                             {{ $user->identity_type === 'umum' ? 'bg-tertiary-container text-on-tertiary-container' : ($user->identity_type === 'pegawai' ? 'bg-secondary-container text-on-secondary-container' : 'bg-primary-container text-on-primary-container') }}">
                    <span class="material-symbols-outlined text-base">{{ $user->identity_type === 'umum' ? 'public' : ($user->identity_type === 'pegawai' ? 'badge' : 'school') }}</span>
                    {{ $user->identity_type === 'umum' ? 'Umum' : ($user->identity_type === 'pegawai' ? 'Staf/Pegawai Laboratorium' : 'Mahasiswa') }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Foto Dokumen Identitas -->
            <div class="md:col-span-1">
                <span class="block font-label-md text-label-md text-on-surface-variant mb-2">Foto KTM / KTP Aktif</span>
                @if ($identityCardUrl && $identityCardIsImage)
                    <div class="rounded-xl overflow-hidden border border-outline-variant bg-surface-container-low">
                        <img src="{{ $identityCardUrl }}" alt="Dokumen identitas aktif" class="w-full h-48 object-contain bg-surface-container-lowest"/>
                    </div>
                    <a href="{{ $identityCardUrl }}" target="_blank" rel="noopener"
                       class="mt-3 w-full inline-flex items-center justify-center gap-1.5 border border-outline-variant text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-base">visibility</span>
                        Pratinjau Dokumen
                    </a>
                @elseif ($identityCardUrl)
                    <a href="{{ $identityCardUrl }}" target="_blank" rel="noopener"
                       class="flex flex-col items-start gap-3 p-4 rounded-xl border border-outline-variant bg-surface-container-low hover:bg-surface-container transition-colors">
                        <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">picture_as_pdf</span>
                        <span class="flex flex-col">
                            <span class="font-label-md text-label-md text-on-surface font-semibold">Dokumen Identitas (PDF)</span>
                            <span class="font-caption-xs text-caption-xs text-primary flex items-center gap-1">Pratinjau Dokumen <span class="material-symbols-outlined text-sm">open_in_new</span></span>
                        </span>
                    </a>
                @else
                    <div class="h-48 rounded-xl border border-dashed border-outline-variant bg-surface-container-low/40 flex flex-col items-center justify-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-4xl">no_photography</span>
                        <span class="font-body-md text-body-md text-sm text-center px-4">Belum ada dokumen identitas terunggah.</span>
                    </div>
                @endif
            </div>

            <!-- Data Identitas -->
            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4 content-start">
                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant">
                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">Nama Lengkap</span>
                    <span class="font-title-sm text-title-sm text-on-surface font-semibold">{{ $user->name }}</span>
                </div>

                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant">
                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">
                        {{ $user->identity_type === 'umum' ? 'NIK (Nomor Induk Kependudukan)' : ($user->identity_type === 'pegawai' ? 'NIP / No. Pegawai' : 'NIM (Nomor Induk Mahasiswa)') }}
                    </span>
                    <span class="font-title-sm text-title-sm text-on-surface font-semibold font-mono">{{ $user->identity_number ?? '-' }}</span>
                </div>

                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant">
                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">Instansi / Domisili</span>
                    <span class="font-title-sm text-title-sm text-on-surface font-semibold">{{ $user->institution ?? '-' }}</span>
                </div>

                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant">
                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">No. WhatsApp</span>
                    <span class="font-title-sm text-title-sm text-on-surface font-semibold">{{ $user->phone_number ?? '-' }}</span>
                </div>

                <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant sm:col-span-2">
                    <span class="block font-caption-xs text-caption-xs text-on-surface-variant mb-1">Email</span>
                    <span class="font-title-sm text-title-sm text-on-surface font-semibold break-all">{{ $user->email }}</span>
                </div>
            </div>
        </div>

        <!-- Tombol Utama -->
        <div class="border-t border-outline-variant pt-5 mt-6 flex justify-end">
            <button type="button" @click="isEditing = true"
                    class="bg-primary text-on-primary font-label-md text-label-md px-6 py-2.5 rounded-lg shadow-sm hover:bg-primary-container transition-colors flex items-center gap-2 font-semibold">
                <span class="material-symbols-outlined text-base">edit</span>
                Ubah Profil
            </button>
        </div>
    </div>

    <!-- EDIT MODE: Formulir Pengubahan Profil -->
    <div x-show="isEditing" x-cloak class="p-6 sm:p-8 bg-surface-container-lowest border border-outline-variant shadow-sm rounded-xl">
        <div class="max-w-2xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <!-- Perbarui Password -->
    <div x-show="!isEditing" class="p-6 sm:p-8 bg-surface-container-lowest border border-outline-variant shadow-sm rounded-xl">
        <div class="max-w-2xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <!-- Hapus Akun -->
    <div x-show="!isEditing" class="p-6 sm:p-8 bg-surface-container-lowest border border-outline-variant shadow-sm rounded-xl">
        <div class="max-w-2xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
