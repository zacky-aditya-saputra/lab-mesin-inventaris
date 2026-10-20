@extends('layouts.catalog', ['title' => 'Catat Kegiatan Laboratorium Baru'])

@section('content')
<div class="py-10 px-margin-page md:px-gutter-md max-w-2xl mx-auto w-full">
    <!-- Breadcrumb & Back -->
    <div class="mb-6">
        <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline mb-2">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Kembali ke Logbook Kegiatan
        </a>
        <h1 class="font-headline-sm text-headline-sm text-on-surface font-bold">Catat Kegiatan Laboratorium</h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">Isi formulir aktivitas kerja laboratorium untuk dokumentasi dan verifikasi oleh laboran terkait.</p>
    </div>

    <!-- Form Card -->
    <div class="p-6 sm:p-8 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm">
        <form method="POST" action="{{ route('activity-logs.store') }}" class="space-y-6">
            @csrf

            <!-- Laboratorium -->
            <div>
                <label for="laboratory_id" class="block font-label-md text-label-md text-on-surface font-semibold mb-1.5">
                    Laboratorium Lokasi Kegiatan <span class="text-error">*</span>
                </label>
                <select id="laboratory_id" name="laboratory_id" required
                        class="w-full h-11 px-3.5 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-on-surface cursor-pointer">
                    <option value="">Pilih Laboratorium...</option>
                    @foreach ($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>
                            {{ $lab->name }} ({{ $lab->code }})
                        </option>
                    @endforeach
                </select>
                @error('laboratory_id')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tanggal & Jam -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="activity_date" class="block font-label-md text-label-md text-on-surface font-semibold mb-1.5">
                        Tanggal Kegiatan <span class="text-error">*</span>
                    </label>
                    <input type="date" id="activity_date" name="activity_date" 
                           value="{{ old('activity_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required
                           class="w-full h-11 px-3.5 rounded-lg border border-outline-variant bg-surface-container-lowest font-mono text-sm focus:ring-2 focus:ring-blue-500">
                    @error('activity_date')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="time_in" class="block font-label-md text-label-md text-on-surface font-semibold mb-1.5">
                        Jam Masuk <span class="text-error">*</span>
                    </label>
                    <input type="time" id="time_in" name="time_in" 
                           value="{{ old('time_in', now()->format('H:i')) }}" required
                           class="w-full h-11 px-3.5 rounded-lg border border-outline-variant bg-surface-container-lowest font-mono text-sm focus:ring-2 focus:ring-blue-500">
                    @error('time_in')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="time_out" class="block font-label-md text-label-md text-on-surface font-semibold mb-1.5">
                        Jam Keluar (Opsional)
                    </label>
                    <input type="time" id="time_out" name="time_out" 
                           value="{{ old('time_out') }}"
                           class="w-full h-11 px-3.5 rounded-lg border border-outline-variant bg-surface-container-lowest font-mono text-sm focus:ring-2 focus:ring-blue-500">
                    @error('time_out')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Jenis Kegiatan -->
            <div>
                <label for="activity_type" class="block font-label-md text-label-md text-on-surface font-semibold mb-1.5">
                    Jenis Kegiatan <span class="text-error">*</span>
                </label>
                <select id="activity_type" name="activity_type" required
                        class="w-full h-11 px-3.5 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-on-surface cursor-pointer">
                    <option value="">Pilih Jenis Kegiatan...</option>
                    <option value="Praktikum" {{ old('activity_type') === 'Praktikum' ? 'selected' : '' }}>Praktikum Terjadwal</option>
                    <option value="Penelitian" {{ old('activity_type') === 'Penelitian' ? 'selected' : '' }}>Penelitian Dosen / Mahasiswa</option>
                    <option value="Tugas Akhir" {{ old('activity_type') === 'Tugas Akhir' ? 'selected' : '' }}>Pengerjaan Tugas Akhir / Skripsi</option>
                    <option value="Mandiri" {{ old('activity_type') === 'Mandiri' ? 'selected' : '' }}>Kegiatan Mandiri / Pelatihan / Lainnya</option>
                </select>
                @error('activity_type')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Judul Kegiatan -->
            <div>
                <label for="title" class="block font-label-md text-label-md text-on-surface font-semibold mb-1.5">
                    Judul / Topik Kegiatan <span class="text-error">*</span>
                </label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       placeholder="Contoh: Pengujian Uji Tarik Baja St 37 Modul 2"
                       class="w-full h-11 px-3.5 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-on-surface">
                @error('title')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div>
                <label for="description" class="block font-label-md text-label-md text-on-surface font-semibold mb-1.5">
                    Uraian / Ringkasan Pekerjaan (Opsional)
                </label>
                <textarea id="description" name="description" rows="4"
                          placeholder="Jelaskan secara ringkas proses yang dilakukan, spesimen yang diuji, atau perkakas yang dipergunakan..."
                          class="w-full p-3.5 rounded-lg border border-outline-variant bg-surface-container-lowest focus:ring-2 focus:ring-blue-500 font-body-md text-body-md text-on-surface">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-outline-variant">
                <a href="{{ route('activity-logs.index') }}" 
                   class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 bg-surface-container-lowest hover:bg-slate-50 font-semibold text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white rounded-lg font-semibold shadow-xs hover:shadow-sm ring-1 ring-inset ring-white/20 active:scale-[0.98] transition-all text-sm">
                    <span class="material-symbols-outlined text-sm">save</span>
                    Simpan Catatan Kegiatan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
