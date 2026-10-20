@extends('layouts.admin', ['title' => 'Manajemen Laboratorium', 'pageTitle' => 'Manajemen Laboratorium'])

@section('content')
<div x-data="{
    addModalOpen: false,
    editModalOpen: false,
    deleteModalOpen: false,
    editFormAction: '',
    deleteFormAction: '',
    addForm: { name: '', code: '', location: '', description: '', is_active: true },
    editLab: { id: '', name: '', code: '', location: '', description: '', is_active: true },
    deleteLab: { id: '', name: '', code: '' },
    openEditModal(lab) {
        this.editLab = { ...lab };
        this.editFormAction = '{{ url('admin/laboratories') }}/' + lab.id;
        this.editModalOpen = true;
    },
    openDeleteModal(lab) {
        this.deleteLab = { ...lab };
        this.deleteFormAction = '{{ url('admin/laboratories') }}/' + lab.id;
        this.deleteModalOpen = true;
    }
}">
    <!-- Action Topbar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface font-bold">Daftar Laboratorium</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Kelola unit laboratorium di lingkungan Program Studi Teknik Mesin Unwahas.</p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="addForm = { name: '', code: '', location: '', description: '', is_active: true }; addModalOpen = true"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                <span class="material-symbols-outlined text-sm">add_circle</span>
                Tambah Laboratorium
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
                        <th class="py-3.5 px-6 font-semibold">Laboratorium</th>
                        <th class="py-3.5 px-6 font-semibold">Kode</th>
                        <th class="py-3.5 px-6 font-semibold">Lokasi</th>
                        <th class="py-3.5 px-6 font-semibold text-center">Jumlah Alat</th>
                        <th class="py-3.5 px-6 font-semibold text-center">Laboran</th>
                        <th class="py-3.5 px-6 font-semibold text-center">Status</th>
                        <th class="py-3.5 px-6 font-semibold text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="font-body-md text-body-md divide-y divide-outline-variant">
                    @forelse ($laboratories as $index => $lab)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-3.5 px-6 text-on-surface-variant font-mono text-xs">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-6 font-medium text-on-surface">
                                <div class="font-semibold text-on-surface">{{ $lab->name }}</div>
                                @if ($lab->description)
                                    <div class="text-xs text-on-surface-variant line-clamp-1 mt-0.5">{{ $lab->description }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded font-mono text-badge-xs text-badge-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                    {{ $lab->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-on-surface-variant text-sm">{{ $lab->location ?? '-' }}</td>
                            <td class="py-3.5 px-6 text-center font-mono font-semibold">{{ $lab->tools_count }}</td>
                            <td class="py-3.5 px-6 text-center font-mono font-semibold">{{ $lab->users_count }}</td>
                            <td class="py-3.5 px-6 text-center">
                                <form method="POST" action="{{ route('admin.laboratories.toggle-active', $lab) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-badge-xs text-badge-xs font-semibold transition-all duration-150 cursor-pointer {{ $lab->is_active ? 'bg-status-success-bg text-status-success-text hover:bg-emerald-200' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}"
                                            title="Klik untuk mengubah status">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $lab->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $lab->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="openEditModal({{ Js::from($lab) }})"
                                            class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-surface-container rounded-lg transition-colors cursor-pointer"
                                            title="Edit Laboratorium">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button @click="openDeleteModal({{ Js::from($lab) }})"
                                            class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container/50 rounded-lg transition-colors cursor-pointer"
                                            title="Hapus Laboratorium">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-on-surface-variant">
                                <span class="material-symbols-outlined text-4xl text-outline mb-2">science</span>
                                <p class="font-body-md text-body-md">Belum ada laboratorium yang terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Laboratorium -->
    <div x-show="addModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
         @keydown.escape.window="addModalOpen = false">
        <div class="bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant max-w-lg w-full p-6 space-y-5"
             @click.away="addModalOpen = false">
            <div class="flex items-center justify-between border-b border-outline-variant pb-3">
                <h3 class="font-title-sm text-title-sm text-on-surface font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_circle</span>
                    Tambah Laboratorium Baru
                </h3>
                <button @click="addModalOpen = false" class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.laboratories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1">Nama Laboratorium <span class="text-error">*</span></label>
                    <input type="text" name="name" x-model="addForm.name" required placeholder="Contoh: Laboratorium Komputasi & Desain"
                           class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-sm"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1">Kode Lab <span class="text-error">*</span></label>
                        <input type="text" name="code" x-model="addForm.code" required placeholder="LKD" maxlength="10"
                               @input="addForm.code = addForm.code.toUpperCase()"
                               class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-mono text-sm uppercase"/>
                        <p class="text-[11px] text-on-surface-variant mt-1">Maks. 10 karakter kapital.</p>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1">Lokasi Gedung / Ruangan</label>
                        <input type="text" name="location" x-model="addForm.location" placeholder="Gedung D, Lantai 2"
                               class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-sm"/>
                    </div>
                </div>

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1">Deskripsi / Ruang Lingkup</label>
                    <textarea name="description" x-model="addForm.description" rows="3" placeholder="Fasilitas praktikum simulasi CAD/CAM..."
                              class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-sm"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0"/>
                    <input type="checkbox" name="is_active" id="add_is_active" value="1" x-model="addForm.is_active"
                           class="rounded border-outline-variant text-primary focus:ring-blue-500 w-4 h-4"/>
                    <label for="add_is_active" class="text-sm font-medium text-on-surface cursor-pointer">Status Aktif (Tampilkan di Katalog &amp; Formulir)</label>
                </div>

                <div class="pt-4 border-t border-outline-variant flex justify-end gap-2.5">
                    <button type="button" @click="addModalOpen = false"
                            class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-label-md text-label-md rounded-lg border border-slate-300 shadow-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-label-md text-label-md rounded-lg shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] font-semibold cursor-pointer">
                        Simpan Laboratorium
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Laboratorium -->
    <div x-show="editModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
         @keydown.escape.window="editModalOpen = false">
        <div class="bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant max-w-lg w-full p-6 space-y-5"
             @click.away="editModalOpen = false">
            <div class="flex items-center justify-between border-b border-outline-variant pb-3">
                <h3 class="font-title-sm text-title-sm text-on-surface font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">edit</span>
                    Edit Data Laboratorium
                </h3>
                <button @click="editModalOpen = false" class="text-on-surface-variant hover:text-on-surface p-1 rounded-lg">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>

            <form method="POST" :action="editFormAction" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1">Nama Laboratorium <span class="text-error">*</span></label>
                    <input type="text" name="name" x-model="editLab.name" required
                           class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-sm"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1">Kode Lab <span class="text-error">*</span></label>
                        <input type="text" name="code" x-model="editLab.code" required maxlength="10"
                               @input="editLab.code = editLab.code.toUpperCase()"
                               class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-mono text-sm uppercase"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1">Lokasi Gedung / Ruangan</label>
                        <input type="text" name="location" x-model="editLab.location"
                               class="w-full h-10 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-sm"/>
                    </div>
                </div>

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1">Deskripsi / Ruang Lingkup</label>
                    <textarea name="description" x-model="editLab.description" rows="3"
                              class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-sm"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0"/>
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" x-model="editLab.is_active"
                           class="rounded border-outline-variant text-primary focus:ring-blue-500 w-4 h-4"/>
                    <label for="edit_is_active" class="text-sm font-medium text-on-surface cursor-pointer">Status Aktif</label>
                </div>

                <div class="pt-4 border-t border-outline-variant flex justify-end gap-2.5">
                    <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-label-md text-label-md rounded-lg border border-slate-300 shadow-xs cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-label-md text-label-md rounded-lg shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] font-semibold cursor-pointer">
                        Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Laboratorium -->
    <div x-show="deleteModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
         @keydown.escape.window="deleteModalOpen = false">
        <div class="bg-surface-container-lowest rounded-xl shadow-xl border border-outline-variant max-w-md w-full p-6 space-y-4"
             @click.away="deleteModalOpen = false">
            <div class="flex items-center gap-3 text-error">
                <span class="material-symbols-outlined text-3xl">warning</span>
                <h3 class="font-title-sm text-title-sm text-on-surface font-bold">Hapus Laboratorium</h3>
            </div>
            <p class="font-body-md text-body-md text-on-surface-variant text-sm">
                Apakah Anda yakin ingin menghapus laboratorium <strong class="text-on-surface" x-text="deleteLab.name"></strong> (<span class="font-mono" x-text="deleteLab.code"></span>)?
            </p>
            <p class="font-caption-xs text-caption-xs text-rose-600 bg-rose-50 p-2.5 rounded-lg border border-rose-200">
                Peringatan: Laboratorium yang masih memiliki data alat, staf laboran, atau riwayat peminjaman tidak dapat dihapus demi integritas data akademik.
            </p>

            <form method="POST" :action="deleteFormAction" class="pt-3 border-t border-outline-variant flex justify-end gap-2.5">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false"
                        class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-label-md text-label-md rounded-lg border border-slate-300 shadow-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-gradient-to-b from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-label-md text-label-md rounded-lg shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] font-semibold cursor-pointer">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
