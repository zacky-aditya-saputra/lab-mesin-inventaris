# Design System & UI Specification: Sistem Inventaris & E-Catalog Multi-Lab Mesin
## Fakultas Teknik — Universitas Wahid Hasyim Semarang

> **Dokumen Panduan Desain Resmi**:  
> Dokumen ini adalah acuan baku implementasi antarmuka untuk **Laravel Blade + Tailwind CSS + Alpine.js**. Seluruh instruksi dalam dokumen ini dirancang agar dapat dibaca dan dieksekusi secara presisi oleh developer maupun AI coding agent (seperti OpenCode CLI). Sistem dibangun secara mandiri tanpa dependensi admin generator pihak ketiga (seperti Filament) guna menjaga performa tinggi, kendali penuh atas kode, dan estetika kriya perangkat lunak (*software craftsmanship*).

---

## 1. Filosofi Desain & Prinsip "Anti-AI-Slop"

Antarmuka sistem ini tidak boleh terlihat seperti template web generik atau hasil generate AI murahan (*AI slop*) yang identik dengan tombol biru flat saturated, elemen kaku seragam, teks terpotong canggung, dan tata letak tanpa hierarki. Sistem ini adalah **perangkat lunak teknik industri (Engineering-Grade Lab Management System)** yang mengedepankan presisi data, ergonomi pengguna, dan keanggunan utiliter.

### 5 Pilar Utama Kriya Desain (Craftsmanship):
1. **Kedalaman Visual Berlapis (*Layered Depth & Tactile Feel*)**:  
   Hindari tombol flat saturasi tinggi tanpa dimensi. Gunakan kombinasi halus antara gradien mikro (*subtle vertical gradient*), bayangan lembut (*soft multi-layer shadows*), dan garis tepi dalam (*inner highlight ring* `ring-1 ring-inset ring-white/20`) untuk menciptakan kesan tombol fisik yang berbobot dan memuaskan saat ditekan (`active:scale-[0.98]`).
2. **Kerapian Tipografi & Eliminasi *Awkward Wrapping***:  
   Setiap kontainer judul halaman harus dilindungi dengan `min-w-0 flex-1` dan `break-words sm:whitespace-normal` agar judul seperti *"Daftar Klien Lab / Peminjam"* tidak pernah terhimpit secara vertikal menjadi satu kata per baris.
3. **Filter Terstruktur vs Clunky Pills**:  
   Untuk data dengan banyak pilihan (seperti 4 kelompok laboratorium utama teknik mesin), **wajib menggunakan dropdown selector kustom atau menu terpadu yang elegan**, bukan deretan *pill buttons* yang membungkus canggung ke baris kedua. Kategori sekunder disajikan dalam baris chip horizontal dengan *horizontal smooth scrolling* (`overflow-x-auto no-scrollbar`) agar tetap rapi di semua resolusi layar.
4. **Navigasi Terintegrasi & Bersih (Zero Redundancy)**:  
   Navbar tidak boleh menampilkan tombol dobel (misal tautan "Kelola Lab" di tengah sekaligus tombol besar "Dashboard Admin" di kanan). Semua aksi akun pengguna (Role, Profil, Dashboard, Logout) disatukan ke dalam **User Avatar Dropdown Menu** yang rapi dengan efek *glassmorphism* (`backdrop-blur-md bg-white/85`).
5. **Penanganan Visual Sempurna (*Graceful Fallbacks*)**:  
   Tidak boleh ada ikon gambar rusak (*broken image icon*) atau teks alt mentah yang merusak estetika kartu alat. Setiap aset visual yang gagal dimuat atau belum memiliki foto wajib menampilkan ilustrasi placeholder teknik (*blueprint engineering SVG placeholder*) yang proporsional.

---

## 2. Design Tokens & Palet Warna

### 2.1 Palet Warna Utama & Netral
| Peran | Nama Token | Nilai HEX | Tailwind Utility Classes | Deskripsi & Konteks Penggunaan |
|---|---|---|---|---|
| **Primary Brand** | Industrial Blue | `#1D4ED8` (Blue 700) / `#2563EB` (Blue 600) | `bg-blue-600 hover:bg-blue-700 text-white` | Aksi utama (CTA), tombol submit, link aktif |
| **Primary Inset** | Top Highlight | `rgba(255,255,255,0.2)` | `ring-1 ring-inset ring-white/20` | Garis pantulan cahaya mikro pada bagian atas tombol |
| **Secondary Neutral**| Slate Surface | `#F8FAFC` (Slate 50) | `bg-slate-50 border-slate-200 text-slate-700` | Kartu sekunder, latar form, input inactive |
| **Dark Sidebar** | Deep Industrial Slate | `#0F172A` (Slate 900) | `bg-slate-900 border-slate-800 text-slate-200` | Sidebar navigasi admin dengan aksen pencahayaan |
| **Active Nav Accent**| Electric Cyan/Blue | `#3B82F6` (Blue 500) | `border-l-4 border-blue-500 bg-slate-800/80` | Penanda tegas menu sidebar aktif |
| **Card Border** | Subtle Hairline | `#E2E8F0` (Slate 200) | `border border-slate-200/80` | Garis batas tipis berkelas pada kartu data |
| **Text Primary** | Deep Charcoal | `#0F172A` (Slate 900) | `text-slate-900 font-semibold` | Judul, label penting, data metrik |
| **Text Secondary** | Muted Slate | `#475569` (Slate 600) | `text-slate-600` | Paragraf penjelasan, spesifikasi alat |
| **Text Tertiary** | Caption Slate | `#94A3B8` (Slate 400) | `text-slate-400 text-xs` | Timestamp, metadata, petunjuk form |

### 2.2 Semantic Status Tokens (Tiket & Stok)
* `PENDING`: `bg-amber-50 text-amber-700 border border-amber-200/80` (Ikon: `schedule`)
* `APPROVED`: `bg-sky-50 text-sky-700 border border-sky-200/80` (Ikon: `check_circle`)
* `PARTIALLY_APPROVED`: `bg-orange-50 text-orange-700 border border-orange-200/80` (Ikon: `rule`)
* `ON_LOAN`: `bg-indigo-50 text-indigo-700 border border-indigo-200/80` (Ikon: `assignment`)
* `OVERDUE`: `bg-rose-600 text-white shadow-sm font-semibold` (Ikon: `warning`) — *Sengaja solid agar mencolok untuk peringatan darurat*
* `RETURNED`: `bg-emerald-50 text-emerald-700 border border-emerald-200/80` (Ikon: `task_alt`)
* `REJECTED`: `bg-rose-50 text-rose-700 border border-rose-200/80` (Ikon: `cancel`)

---

## 3. Komponen Inti "Anti-AI-Slop"

### 3.1 Tombol Utama (*Primary Button*)
Tombol utama menggabungkan gradien vertikal halus, bayangan terukur, dan efek pegas mikro saat diklik:
```html
<button type="submit" 
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg 
               bg-gradient-to-b from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 
               text-white text-sm font-medium tracking-tight shadow-sm hover:shadow 
               ring-1 ring-inset ring-white/20 active:scale-[0.98] focus:outline-none 
               focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-150 cursor-pointer">
    <span class="material-symbols-outlined text-base">check</span>
    <span>Simpan Perubahan</span>
</button>
```

### 3.2 Tombol Sekunder / Garis Tepi (*Secondary / Outline Button*)
```html
<button type="button" 
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg 
               bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium tracking-tight 
               border border-slate-300/80 hover:border-slate-400 shadow-xs active:bg-slate-100 
               active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-slate-300 
               transition-all duration-150 cursor-pointer">
    <span>Batal</span>
</button>
```

### 3.3 Tombol Bahaya / Destruktif (*Danger Button*)
```html
<button type="button" 
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg 
               bg-gradient-to-b from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 
               text-white text-sm font-medium tracking-tight shadow-sm hover:shadow 
               ring-1 ring-inset ring-white/20 active:scale-[0.98] focus:outline-none 
               focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-150 cursor-pointer">
    <span class="material-symbols-outlined text-base">delete</span>
    <span>Hapus Data</span>
</button>
```

### 3.4 Tombol Pintas WhatsApp Klien (*WhatsApp Micro-Action*)
Bukan tombol hijau mencolok mentah, melainkan tombol terukur dengan border aksen dan interaksi mikro:
```html
<a href="{{ $waUrl }}" target="_blank" rel="noopener"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg 
          bg-emerald-50/80 hover:bg-emerald-100/90 text-emerald-800 border border-emerald-300/70 
          font-medium text-xs shadow-xs hover:shadow-sm active:scale-95 transition-all duration-150">
    <span class="material-symbols-outlined text-[16px] text-emerald-600">chat</span>
    <span>{{ $client->phone_number }}</span>
</a>
```

---

## 4. Spesifikasi Perombakan Antarmuka Utama

### 4.1 Modul E-Katalog Publik (`/katalog`)

#### A. Toolbar Pencarian & Filter Laboratorium (Dropdown Elegan)
* **Kebutuhan**: Menghilangkan 6 pill button bertumpuk yang berantakan. Menggantinya dengan toolbar terpadu.
* **Struktur Toolbar**:
  1. Baris atas: Kotak pencarian (*Search input*) dipasangkan berdampingan dengan **Dropdown Selector Laboratorium**.
  2. Dropdown Lab memiliki ikon laboratorium (`science`), chevron halus, border `border-slate-300`, dan saat dipilih langsung me-redirect atau memicu filter lab yang bersangkutan.
  3. Baris bawah: Deretan chip Kategori yang dapat digeser secara horizontal (*horizontal scroll* `overflow-x-auto no-scrollbar`) dengan indikator aktif yang kontras (`bg-slate-900 text-white` saat aktif vs `bg-white border-slate-200 text-slate-600` saat inaktif).

```html
<!-- Toolbar Filter Terpadu -->
<div class="max-w-4xl mx-auto space-y-4 mb-8">
    <!-- Baris 1: Pencarian & Dropdown Laboratorium -->
    <div class="flex flex-col sm:flex-row gap-3">
        <!-- Live Search Bar -->
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
            <input type="text" x-model="searchQuery"
                   placeholder="Cari nama alat, spesifikasi, atau kode inventaris..." 
                   class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300/90 bg-white 
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-xs 
                          transition-all placeholder:text-slate-400" />
            <button x-show="searchQuery" @click="searchQuery = ''" 
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>

        <!-- Dropdown Filter Laboratorium -->
        <div class="sm:w-72 flex-shrink-0">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-blue-600 text-lg">science</span>
                <select onchange="window.location.href = this.value;"
                        class="w-full pl-10 pr-9 py-3 rounded-xl border border-slate-300/90 bg-white text-sm font-medium 
                               text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-xs transition-all appearance-none cursor-pointer">
                    <option value="{{ route('catalog.index') }}" {{ !$selectedLabSlug ? 'selected' : '' }}>
                        🏛️ Semua Laboratorium (4 Lab Utama)
                    </option>
                    @foreach ($laboratories as $lab)
                        <option value="{{ route('catalog.index', ['lab' => $lab->slug]) }}" {{ $selectedLabSlug === $lab->slug ? 'selected' : '' }}>
                            🔬 {{ $lab->name }}
                        </option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-lg">expand_more</span>
            </div>
        </div>
    </div>

    <!-- Baris 2: Kategori Horizontal Scrollbar -->
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
        <template :key="category" x-for="category in categories">
            <button @click="activeCategory = category"
                    :class="activeCategory === category 
                            ? 'bg-slate-900 text-white font-semibold shadow-xs border-slate-900' 
                            : 'bg-white text-slate-600 hover:bg-slate-100/80 border-slate-200/90 hover:border-slate-300'"
                    class="px-4 py-2 rounded-full text-xs font-medium border whitespace-nowrap transition-all duration-150 cursor-pointer">
                <span x-text="category"></span>
            </button>
        </template>
    </div>
</div>
```

#### B. Kartu Alat (*Tool Card*) & Engineering Placeholder
Jika gambar alat tidak tersedia atau mengalami gagal muat (*404 error*), tampilkan placeholder SVG diagram teknis yang elegan, bukan teks broken alt:
```html
<div class="relative h-48 w-full bg-slate-100 flex items-center justify-center overflow-hidden border-b border-slate-100">
    <template x-if="tool.image">
        <img :src="tool.image" :alt="tool.name" 
             @error="$el.style.display='none'; $el.nextElementSibling.classList.remove('hidden')"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"/>
    </template>
    <!-- Fallback Technical Illustration -->
    <div :class="tool.image ? 'hidden' : 'flex'" 
         class="flex-col items-center justify-center text-slate-400 p-4 text-center select-none">
        <div class="w-14 h-14 rounded-2xl bg-white/80 border border-slate-200/80 shadow-xs flex items-center justify-center mb-2">
            <span class="material-symbols-outlined text-3xl text-blue-600/80">precision_manufacturing</span>
        </div>
        <span class="text-[11px] font-mono font-medium text-slate-500 uppercase tracking-wider" x-text="tool.code"></span>
    </div>
</div>
```

---

### 4.2 Topbar Navigasi Publik & User Dropdown (`layouts/catalog.blade.php`)

* **Konsep**: Menghilangkan tombol dobel yang canggung di header publik dan menggantinya dengan bilah navigasi *glassmorphism* modern serta satu **Avatar Dropdown Interaktif**.
* **Elemen Menu Dropdown Pengguna**:
  - Kartu identitas singkat: Nama Pengguna, Email, dan Badge Peran (`Kepala Lab`, `Laboran Lab X`, `Mahasiswa`).
  - Menu Navigasi:
    - Jika Admin/Laboran: *Dashboard Manajemen Lab* (dengan ikon dashboard) dan *Katalog Publik*.
    - Jika Klien: *Ajukan Peminjaman* dan *Riwayat Tiket Saya*.
    - Semua Role: *Pengaturan Profil Akun*.
  - Pemisah visual (*Divider*).
  - Tombol **Keluar (Logout)** yang memicu modal konfirmasi ramah (*"Apakah Anda yakin ingin mengakhiri sesi?"*).

---

### 4.3 Direktori Klien Lab & Perbaikan Tabel Admin (`admin/clients/index.blade.php`)

#### A. Perbaikan Header Halaman (Mencegah Judul Terhimpit)
Gunakan pembungkus `flex flex-col lg:flex-row lg:items-center justify-between gap-4` dengan `flex-1 min-w-0` pada kontainer judul:
```html
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
    <div class="flex-1 min-w-0">
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Daftar Klien & Peminjam</h2>
        <p class="text-sm text-slate-500 mt-0.5">Direktori peminjam alat, kontak WhatsApp, dan riwayat transaksi peminjaman.</p>
    </div>
    @if (auth()->user()->isKepalaLab())
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex items-center gap-2.5 flex-shrink-0">
            <div class="relative">
                <select name="laboratory_id"
                        class="pl-3.5 pr-8 py-2 bg-white border border-slate-300 rounded-lg text-sm font-medium text-slate-700 
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-xs appearance-none cursor-pointer">
                    <option value="">🏛️ Semua Laboratorium</option>
                    @foreach ($laboratories as $laboratory)
                        <option value="{{ $laboratory->id }}" @selected(request('laboratory_id') == $laboratory->id)>
                            🔬 {{ $laboratory->name }}
                        </option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-base">expand_more</span>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow-xs transition-colors">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                <span>Filter</span>
            </button>
        </form>
    @endif
</div>
```

#### B. Perbaikan Layout Tabel Responsif (Mencegah Horizontal Scrollbar Berlebih)
- Tetapkan proporsi lebar kolom secara terencana (`w-1/3`, `w-44`, `w-auto`).
- Berikan padding seimbang (`px-4 py-3.5`).
- Seluruh baris memiliki transisi hover yang halus (`hover:bg-slate-50/70 transition-colors`).

---

### 4.4 Sidebar & Topbar Dashboard Admin (`layouts/admin.blade.php`)

1. **Sidebar Modern**:
   - Latar belakang: Slate 900 gelap elegan (`#0F172A`).
   - Indikator Menu Aktif: Garis aksen biru terang di sisi kiri (`border-l-4 border-blue-500 bg-slate-800/80 text-white font-semibold`), bukan blok ungu/biru mencolok.
   - Menu Tidak Aktif: Teks `text-slate-400 hover:text-white hover:bg-slate-800/50`.
   - Badge tiket: Kapsul merah lembut dengan angka kontras (`bg-rose-500/20 text-rose-300 border border-rose-500/30 px-2 py-0.5 rounded-full text-xs font-semibold`).
2. **Topbar Admin**:
   - Bersih dengan tinggi `h-16 border-b border-slate-200 bg-white/95 backdrop-blur-md px-6`.
   - Widget Tanggal/Waktu: Menggunakan badge lembut abu-abu dengan ikon jam (`schedule`), bukan teks mentah yang terpojok.
   - Tombol Notifikasi: Ikon lonceng dengan *animated pulse dot* jika ada pesan baru yang belum dibaca.

---

## 5. Checklist Verifikasi Standar Kualitas (Quality Gate)

Sebelum dinyatakan selesai dan lolos verifikasi, seluruh komponen berikut harus memenuhi standar:
- [x] **Filter Lab Katalog**: Menggunakan dropdown responsif terpadu; tidak ada pill buttons yang membungkus canggung.
- [x] **Navigasi Publik**: Tidak ada tombol redundan; avatar dropdown menyatukan seluruh kontrol profil & akses admin.
- [x] **Tombol "Anti-AI-Slop"**: Menggunakan gradien halus, inner ring highlight, bayangan terukur, dan efek active press `active:scale-[0.98]`.
- [x] **Admin Klien Page**: Judul halaman tidak mengalami *awkward vertical wrap*; tabel pas dan proporsional di layar desktop standar tanpa scrollbar horizontal sempit.
- [x] **Image Fallback**: Tidak menampilkan broken image icon jika file foto alat tidak ada di storage; menampilkan SVG technical blueprint.
- [x] **Uji Bebas Regresi**: Seluruh 103 pengujian otomatis (*Pest test suite*) wajib **100% Lulus (Pass)** tanpa error.
- [x] **Aset Frontend Terkompilasi**: Eksekusi `npm run build` berhasil tanpa warning atau syntax error.