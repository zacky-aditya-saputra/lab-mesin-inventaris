@extends('layouts.admin', ['title' => 'Ubah Data Alat', 'pageTitle' => 'Ubah Data Alat'])

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
            <h2 class="font-headline-sm text-headline-sm text-on-surface">Ubah Informasi Alat: {{ $tool->name }}</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Perbarui data teknis, penyesuaian stok, atau status keaktifan alat.</p>
        </div>

        <form action="{{ route('admin.tools.update', $tool) }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kategori -->
                <div>
                    <label for="category_id" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Kategori Alat <span class="text-error">*</span>
                    </label>
                    <select id="category_id" name="category_id" required
                            class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary font-body-md text-body-md">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $tool->category_id) == $category->id ? 'selected' : '' }}>
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
                    <label for="code" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Kode Inventaris <span class="text-error">*</span>
                    </label>
                    <input id="code" name="code" type="text" value="{{ old('code', $tool->code) }}" required
                           class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary font-body-md text-body-md uppercase font-mono"/>
                    @error('code')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Alat -->
                <div class="md:col-span-2">
                    <label for="name" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Nama Alat <span class="text-error">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name', $tool->name) }}" required
                           class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary font-body-md text-body-md"/>
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
                              class="w-full p-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary font-body-md text-body-md">{{ old('specification', $tool->specification) }}</textarea>
                    @error('specification')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Total Stok Fisik -->
                <div>
                    <label for="total_stock" class="block font-label-md text-label-md text-on-surface mb-1.5">
                        Jumlah Total Stok Fisik <span class="text-error">*</span>
                    </label>
                    <input id="total_stock" name="total_stock" type="number" min="0" value="{{ old('total_stock', $tool->total_stock) }}" required
                           class="w-full h-11 px-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary font-body-md text-body-md"/>
                    <p class="mt-1 font-caption-xs text-caption-xs text-on-surface-variant">Stok tersedia saat ini: <strong>{{ $tool->available_stock }}</strong></p>
                    @error('total_stock')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div class="flex flex-col justify-center">
                    <label class="block font-label-md text-label-md text-on-surface mb-2">Status Ketersediaan</label>
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tool->is_active) ? 'checked' : '' }}
                               class="rounded border-outline-variant text-primary focus:ring-primary w-5 h-5"/>
                        <span class="font-body-md text-body-md text-on-surface">Tampilkan & aktifkan di E-Catalog publik</span>
                    </label>
                </div>

                <!-- Unggah Foto Alat -->
                <div class="md:col-span-2">
                    <label class="block font-label-md text-label-md text-on-surface mb-1.5">Ganti Foto Alat</label>
                    @if($tool->image_path)
                        <div class="mb-3 flex items-center gap-4">
                            <img src="{{ asset('storage/' . $tool->image_path) }}" alt="{{ $tool->name }}" class="w-20 h-20 object-cover rounded-lg border border-outline-variant">
                            <span class="font-caption-xs text-caption-xs text-on-surface-variant">Gambar saat ini terpasang. Unggah file baru untuk mengganti.</span>
                        </div>
                    @endif
                    <div class="border-2 border-dashed border-outline-variant rounded-xl p-6 flex flex-col items-center justify-center bg-surface-container-low/40 hover:bg-surface-container-low transition-colors cursor-pointer relative">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-2">add_photo_alternate</span>
                        <p class="font-body-md text-body-md text-on-surface text-center mb-1">
                            <span class="font-semibold text-primary">Klik untuk memilih gambar baru</span> atau seret ke sini
                        </p>
                        <p class="font-caption-xs text-caption-xs text-on-surface-variant">JPG, PNG, WebP (Maksimal 2MB)</p>
                        <input id="image" name="image" type="file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer"/>
                    </div>
                    @error('image')
                        <p class="mt-1 font-caption-xs text-caption-xs text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-6 border-t border-outline-variant flex justify-end gap-3">
                <a href="{{ route('admin.tools.index') }}" 
                   class="px-5 py-2.5 rounded-lg border border-outline-variant text-secondary bg-surface-container-lowest hover:bg-surface-container-low font-label-md text-label-md transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-primary hover:bg-primary-container text-on-primary rounded-lg font-label-md text-label-md shadow-sm transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">save</span>
                    Perbarui Data Alat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
