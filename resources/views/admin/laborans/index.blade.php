@extends('layouts.admin', ['title' => 'Manajemen Laboran', 'pageTitle' => 'Manajemen Laboran'])

@section('content')
<div x-data="{
    addModalOpen: false,
    editModalOpen: false,
    deleteModalOpen: false,
    editFormAction: '',
    deleteFormAction: '',
    addForm: { name: '', identity_number: '', email: '', password: '', laboratory_id: '' },
    editLaboran: { id: '', name: '', identity_number: '', email: '', password: '', laboratory_id: '' },
    deleteLaboran: { id: '', name: '', laboratory: '' },
    openEditModal(laboran) {
        this.editLaboran = { ...laboran, password: '' };
        this.editFormAction = '{{ url('admin/laborans') }}/' + laboran.id;
        this.editModalOpen = true;
    },
    openDeleteModal(laboran) {
        this.deleteLaboran = { ...laboran };
        this.deleteFormAction = '{{ url('admin/laborans') }}/' + laboran.id;
        this.deleteModalOpen = true;
    }
}">
    <!-- Action Topbar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Daftar Laboran</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Kelola akun staf laboratorium dan penugasan lab masing-masing.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <form method="GET" action="{{ route('admin.laborans.index') }}" class="flex items-center gap-2">
                <select name="laboratory_id" onchange="this.form.submit()"
                        class="px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                    <option value="">Semua Lab</option>
                    @foreach ($laboratories as $laboratory)
                        <option value="{{ $laboratory->id }}" @selected(request('laboratory_id') == $laboratory->id)>{{ $laboratory->name }}</option>
                    @endforeach
                </select>
                @if (request('laboratory_id'))
                    <a href="{{ route('admin.laborans.index') }}" class="p-2 text-on-surface-variant hover:text-on-surface" title="Reset Filter">
                        <span class="material-symbols-outlined text-base">close</span>
                    </a>
                @endif
            </form>
            <button @click="addForm = { name: '', identity_number: '', email: '', password: '', laboratory_id: '' }; addModalOpen = true"
                    class="bg-primary hover:bg-primary-container text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">person_add</span>
                Tambah Laboran
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <div class="table-container overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="py-3.5 px-6 font-semibold w-16">No</th>
                        <th class="py-3.5 px-6 font-semibold">Nama Laboran</th>
                        <th class="py-3.5 px-6 font-semibold">NIP / No. Pegawai</th>
                        <th class="py-3.5 px-6 font-semibold">Email</th>
                        <th class="py-3.5 px-6 font-semibold">Lab</th>
                        <th class="py-3.5 px-6 font-semibold text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="font-body-md text-body-md divide-y divide-outline-variant">
                    @forelse ($laborans as $index => $laboran)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-3.5 px-6 text-on-surface-variant font-mono text-xs">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-6 font-medium text-on-surface">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($laboran->name, 0, 2)) }}
                                    </div>
                                    <span>{{ $laboran->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 font-mono text-on-surface-variant">{{ $laboran->identity_number ?? '-' }}</td>
                            <td class="py-3.5 px-6 text-on-surface-variant">{{ $laboran->email }}</td>
                            <td class="py-3.5 px-6">
                                @if ($laboran->laboratory)
                                    <div class="flex items-center gap-2">
                                        @if ($laboran->laboratory->code)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded font-mono text-badge-xs text-badge-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                                                {{ $laboran->laboratory->code }}
                                            </span>
                                        @endif
                                        <span class="font-medium text-on-surface text-sm">
                                            {{ $laboran->laboratory->name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs bg-surface-container text-outline">
                                        Belum Ditugaskan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6">
                                <div class="flex justify-center space-x-1">
                                    <button type="button"
                                            @click="openEditModal({ id: {{ $laboran->id }}, name: '{{ addslashes($laboran->name) }}', identity_number: '{{ addslashes($laboran->identity_number ?? '') }}', email: '{{ addslashes($laboran->email) }}', laboratory_id: '{{ $laboran->laboratory_id ?? '' }}' })"
                                            class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-primary-fixed rounded transition-colors"
                                            title="Edit Laboran">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <button type="button"
                                            @click="openDeleteModal({ id: {{ $laboran->id }}, name: '{{ addslashes($laboran->name) }}', laboratory: '{{ addslashes($laboran->laboratory?->name ?? '-') }}' })"
                                            class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container rounded transition-colors"
                                            title="Hapus Laboran">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl text-outline">manage_accounts</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface">Belum ada akun laboran</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Klik tombol Tambah Laboran untuk membuat akun baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Add Laboran -->
    <div x-show="addModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/50 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="addModalOpen = false"
             class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                <h2 class="font-title-sm text-title-sm text-on-surface">Tambah Laboran Baru</h2>
                <button @click="addModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.laborans.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="create_laboran_name">Nama Lengkap <span class="text-error">*</span></label>
                        <input id="create_laboran_name" name="name" type="text" required x-model="addForm.name"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                               placeholder="Contoh: Budi Santoso"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="create_laboran_nip">NIP / No. Pegawai <span class="text-error">*</span></label>
                        <input id="create_laboran_nip" name="identity_number" type="text" required x-model="addForm.identity_number"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md font-mono focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                               placeholder="Contoh: 199501012020121001"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="create_laboran_email">Email <span class="text-error">*</span></label>
                        <input id="create_laboran_email" name="email" type="email" required x-model="addForm.email"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                               placeholder="laboran@unwahas.ac.id"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="create_laboran_password">Password <span class="text-error">*</span></label>
                        <input id="create_laboran_password" name="password" type="password" required x-model="addForm.password"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                               placeholder="Minimal 8 karakter"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="create_laboran_lab">Laboratorium Tugas <span class="text-error">*</span></label>
                        <select id="create_laboran_lab" name="laboratory_id" required x-model="addForm.laboratory_id"
                                class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="">- Pilih Laboratorium -</option>
                            @foreach ($laboratories as $laboratory)
                                <option value="{{ $laboratory->id }}">{{ $laboratory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="font-caption-xs text-caption-xs text-on-surface-variant">Akun otomatis berperan sebagai <span class="font-semibold">Laboran</span> dengan jenis identitas <span class="font-semibold">Pegawai</span>.</p>
                </div>
                <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low flex justify-end space-x-3">
                    <button type="button" @click="addModalOpen = false"
                            class="px-4 py-2 border border-outline-variant text-secondary bg-surface-container-lowest hover:bg-surface-container-low rounded-md font-label-md text-label-md transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-md font-label-md text-label-md shadow-sm transition-colors">
                        Simpan Laboran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Laboran -->
    <div x-show="editModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/50 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="editModalOpen = false"
             class="w-full max-w-md bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                <h2 class="font-title-sm text-title-sm text-on-surface">Ubah Laboran</h2>
                <button @click="editModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form :action="editFormAction" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="edit_laboran_name">Nama Lengkap <span class="text-error">*</span></label>
                        <input id="edit_laboran_name" name="name" type="text" required x-model="editLaboran.name"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="edit_laboran_nip">NIP / No. Pegawai <span class="text-error">*</span></label>
                        <input id="edit_laboran_nip" name="identity_number" type="text" required x-model="editLaboran.identity_number"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md font-mono focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="edit_laboran_email">Email <span class="text-error">*</span></label>
                        <input id="edit_laboran_email" name="email" type="email" required x-model="editLaboran.email"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="edit_laboran_password">
                            Reset Password <span class="text-on-surface-variant font-normal">(Kosongkan jika tidak diubah)</span>
                        </label>
                        <input id="edit_laboran_password" name="password" type="password" x-model="editLaboran.password"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                               placeholder="Isi untuk mereset password (min. 8 karakter)"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="edit_laboran_lab">Ganti Lab Tugas <span class="text-error">*</span></label>
                        <select id="edit_laboran_lab" name="laboratory_id" required x-model="editLaboran.laboratory_id"
                                class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-md font-body-md text-body-md focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="">- Pilih Laboratorium -</option>
                            @foreach ($laboratories as $laboratory)
                                <option value="{{ $laboratory->id }}">{{ $laboratory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low flex justify-end space-x-3">
                    <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2 border border-outline-variant text-secondary bg-surface-container-lowest hover:bg-surface-container-low rounded-md font-label-md text-label-md transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary hover:bg-primary-container text-on-primary rounded-md font-label-md text-label-md shadow-sm transition-colors">
                        Perbarui Laboran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Delete Confirmation -->
    <div x-show="deleteModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-on-surface/50 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="deleteModalOpen = false"
             class="w-full max-w-sm bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-error-container text-error flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
                <h3 class="font-title-sm text-title-sm text-on-surface mb-2">Hapus Akun Laboran?</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mb-6">
                    Akun <span class="font-semibold text-on-surface" x-text="deleteLaboran.name"></span>
                    (Lab <span class="font-semibold" x-text="deleteLaboran.laboratory"></span>) akan dihapus permanen dari sistem.
                </p>
                <form :action="deleteFormAction" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex space-x-3 justify-center">
                        <button type="button" @click="deleteModalOpen = false"
                                class="flex-1 px-4 py-2 border border-outline-variant text-secondary bg-surface-container-lowest hover:bg-surface-container-low rounded-md font-label-md text-label-md transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-error hover:bg-[#991b1b] text-on-error rounded-md font-label-md text-label-md shadow-sm transition-colors">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
