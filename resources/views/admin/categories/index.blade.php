@extends('layouts.admin', ['title' => 'Manajemen Kategori Alat', 'pageTitle' => 'Manajemen Kategori'])

@section('content')
<div x-data="{
    addModalOpen: false,
    editModalOpen: false,
    deleteModalOpen: false,
    editFormAction: '',
    deleteFormAction: '',
    addForm: { name: '', code: '' },
    editCategory: { id: '', name: '', code: '', description: '' },
    deleteCategory: { id: '', name: '' },
    searchQuery: '',
    suggestCode(name) {
        const words = (name || '').toUpperCase().split(/\s+/)
            .map(word => word.replace(/[^A-Z]/g, ''))
            .filter(word => word.length > 0);
        if (words.length === 0) return '';
        if (words.length >= 2) {
            return words.slice(0, 3).map(word => word[0]).join('');
        }
        const letters = words[0];
        const consonants = letters.replace(/[AEIOU]/g, '');
        return (consonants.length >= 2 ? consonants : letters).slice(0, 3);
    },
    openEditModal(category) {
        this.editCategory = { ...category };
        this.editFormAction = '{{ url('admin/categories') }}/' + category.id;
        this.editModalOpen = true;
    },
    openDeleteModal(category) {
        this.deleteCategory = { ...category };
        this.deleteFormAction = '{{ url('admin/categories') }}/' + category.id;
        this.deleteModalOpen = true;
    }
}">
    <!-- Action Topbar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Daftar Kategori Alat</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Kelola klasifikasi dan kategori inventaris alat laboratorium.</p>
        </div>
        <button @click="addForm = { name: '', code: '' }; addModalOpen = true"
                class="inline-flex items-center justify-center gap-2 whitespace-nowrap bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
            <span class="material-symbols-outlined text-sm">add</span>
            Tambah Kategori
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-4 border-b border-outline-variant flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-surface-container-low/50">
            <div class="relative w-full sm:w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-70">search</span>
                <input x-model="searchQuery" 
                       class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-shadow" 
                       placeholder="Cari kategori..." 
                       type="text"/>
            </div>
            <div class="font-caption-xs text-caption-xs text-on-surface-variant">
                Menampilkan {{ $categories->count() }} kategori
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-container overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container text-on-surface font-label-md text-label-md border-b border-outline-variant">
                        <th class="py-3.5 px-6 font-semibold w-16">No</th>
                        <th class="py-3.5 px-6 font-semibold">Nama Kategori</th>
                        <th class="py-3.5 px-6 font-semibold">Deskripsi</th>
                        <th class="py-3.5 px-6 font-semibold text-right">Jumlah Alat</th>
                        <th class="py-3.5 px-6 font-semibold text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="font-body-md text-body-md divide-y divide-outline-variant">
                    @forelse ($categories as $index => $category)
                        <tr class="hover:bg-surface-container-low transition-colors group"
                            x-show="!searchQuery || '{{ strtolower($category->name) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($category->code ?? '') }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($category->description ?? '') }}'.includes(searchQuery.toLowerCase())">
                            <td class="py-3.5 px-6 text-on-surface-variant font-mono text-xs">
                                {{ $categories->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-6 font-medium text-on-surface">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-xl">category</span>
                                    <span>{{ $category->name }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-badge-xs text-badge-xs font-mono bg-primary/10 text-primary border border-primary/20"
                                          title="Kode singkatan kategori">
                                        {{ $category->code_or_default }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-on-surface-variant max-w-xs truncate">
                                {{ $category->description ?? '-' }}
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-badge-xs text-badge-xs bg-secondary-container text-on-secondary-container">
                                    {{ $category->tools_count ?? $category->tools->count() }} Alat
                                </span>
                            </td>
                            <td class="py-3.5 px-6">
                                <div class="flex justify-center space-x-1">
                                    <button type="button"
                                            @click="openEditModal({ id: {{ $category->id }}, name: '{{ addslashes($category->name) }}', code: '{{ addslashes($category->code ?? '') }}', description: '{{ addslashes($category->description ?? '') }}' })"
                                            class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-primary-fixed rounded-lg transition-colors" 
                                            title="Edit Kategori">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                    <button type="button"
                                            @click="openDeleteModal({ id: {{ $category->id }}, name: '{{ addslashes($category->name) }}' })"
                                            class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container rounded-lg transition-colors" 
                                            title="Hapus Kategori">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-on-surface-variant">
                                <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-2xl text-outline">category</span>
                                </div>
                                <p class="font-title-sm text-title-sm text-on-surface">Belum ada kategori alat</p>
                                <p class="font-caption-xs text-caption-xs mt-1">Klik tombol Tambah Kategori untuk menambahkan kategori baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($categories->hasPages())
            <div class="p-4 border-t border-outline-variant bg-surface-container-lowest">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Add Category -->
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
            <!-- Header -->
            <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                <h2 class="font-title-sm text-title-sm text-on-surface">Tambah Kategori Baru</h2>
                <button @click="addModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="create_name">
                            Nama Kategori <span class="text-error">*</span>
                        </label>
                        <input id="create_name" name="name" type="text" required x-model="addForm.name"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                               placeholder="Contoh: Alat Ukur Presisi"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="create_code">
                            Kode Singkatan Kategori <span class="text-error">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input id="create_code" name="code" type="text" maxlength="10" required x-model="addForm.code"
                                   :placeholder="suggestCode(addForm.name) ? suggestCode(addForm.name) : 'Contoh: UK'"
                                   class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500 uppercase font-mono"/>
                            <button type="button" x-show="suggestCode(addForm.name) && addForm.code !== suggestCode(addForm.name)" x-cloak
                                    @click="addForm.code = suggestCode(addForm.name)"
                                    class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-2 rounded-lg border border-primary/30 bg-primary/10 text-primary hover:bg-primary/20 font-caption-xs text-caption-xs shadow-xs active:scale-[0.98] transition-all duration-150 whitespace-nowrap cursor-pointer">
                                <span class="material-symbols-outlined text-sm">auto_awesome</span>
                                Gunakan Saran: <span class="font-semibold font-mono" x-text="suggestCode(addForm.name)"></span>
                            </button>
                        </div>
                        <p class="mt-1 font-caption-xs text-caption-xs text-on-surface-variant">Wajib diisi, huruf kapital, maksimal 10 karakter, dan harus unik. Gunakan saran otomatis jika bingung.</p>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="create_description">
                            Deskripsi <span class="text-on-surface-variant font-normal">(Opsional)</span>
                        </label>
                        <textarea id="create_description" name="description" rows="3"
                                  class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500 resize-none"
                                  placeholder="Tuliskan deskripsi singkat kategori ini..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low flex justify-end space-x-3">
                    <button type="button" @click="addModalOpen = false"
                            class="px-4 py-2 border border-slate-300/90 text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 rounded-lg font-label-md text-label-md shadow-xs active:bg-slate-100 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary rounded-lg font-label-md text-label-md shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Category -->
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
            <!-- Header -->
            <div class="px-6 py-4 border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                <h2 class="font-title-sm text-title-sm text-on-surface">Ubah Kategori</h2>
                <button @click="editModalOpen = false" class="text-on-surface-variant hover:text-on-surface">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Form -->
            <form :action="editFormAction" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="edit_name">
                            Nama Kategori <span class="text-error">*</span>
                        </label>
                        <input id="edit_name" name="name" type="text" required x-model="editCategory.name"
                               class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500"/>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="edit_code">
                            Kode Singkatan Kategori <span class="text-error">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input id="edit_code" name="code" type="text" maxlength="10" required x-model="editCategory.code"
                                   :placeholder="suggestCode(editCategory.name) ? suggestCode(editCategory.name) : 'Contoh: UK'"
                                   class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500 uppercase font-mono"/>
                            <button type="button" x-show="suggestCode(editCategory.name) && editCategory.code !== suggestCode(editCategory.name)" x-cloak
                                    @click="editCategory.code = suggestCode(editCategory.name)"
                                    class="flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-2 rounded-lg border border-primary/30 bg-primary/10 text-primary hover:bg-primary/20 font-caption-xs text-caption-xs shadow-xs active:scale-[0.98] transition-all duration-150 whitespace-nowrap cursor-pointer">
                                <span class="material-symbols-outlined text-sm">auto_awesome</span>
                                Gunakan Saran: <span class="font-semibold font-mono" x-text="suggestCode(editCategory.name)"></span>
                            </button>
                        </div>
                        <p class="mt-1 font-caption-xs text-caption-xs text-on-surface-variant">Wajib diisi, huruf kapital, maksimal 10 karakter, dan harus unik. Gunakan saran otomatis jika bingung.</p>
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface mb-1.5" for="edit_description">
                            Deskripsi <span class="text-on-surface-variant font-normal">(Opsional)</span>
                        </label>
                        <textarea id="edit_description" name="description" rows="3" x-model="editCategory.description"
                                  class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-outline-variant bg-surface-container-low flex justify-end space-x-3">
                    <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2 border border-slate-300/90 text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 rounded-lg font-label-md text-label-md shadow-xs active:bg-slate-100 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-on-primary rounded-lg font-label-md text-label-md shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                        Perbarui Kategori
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
                <h3 class="font-title-sm text-title-sm text-on-surface mb-2">Hapus Kategori?</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mb-6">
                    Apakah Anda yakin ingin menghapus kategori <span class="font-semibold text-on-surface" x-text="deleteCategory.name"></span>? <br/>
                    <span class="font-medium text-error text-xs">Kategori yang memiliki relasi alat tidak dapat dihapus permanen.</span>
                </p>
                <form :action="deleteFormAction" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex space-x-3 justify-center">
                        <button type="button" @click="deleteModalOpen = false"
                                class="flex-1 px-4 py-2 border border-slate-300/90 text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 rounded-lg font-label-md text-label-md shadow-xs active:bg-slate-100 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 bg-gradient-to-b from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-on-error rounded-lg font-label-md text-label-md shadow-xs ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all duration-150 cursor-pointer">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
