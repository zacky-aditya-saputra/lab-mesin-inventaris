# Design System & UI Specification: Sistem Inventaris & E-Catalog Lab Mesin

> Dokumen ini adalah acuan baku implementasi antarmuka bagi tim frontend menggunakan **Laravel Blade + Tailwind CSS + Alpine.js**, diturunkan dari PRD/SRS "Sistem Layanan Pengelolaan, Peminjaman Alat, dan E-Catalog Berbasis Web" (Lab Proses Produksi Teknik Mesin Unwahas). Tidak menggunakan Filament atau admin generator pihak ketiga — seluruh komponen di bawah ini dibangun manual.

---

## 1. Design Principles & Theme

**Filosofi desain:** modern, bersih (clean), dan **utiliter** — antarmuka harus terasa seperti alat kerja teknik, bukan produk konsumer yang playful. Prioritas utama adalah keterbacaan data (spesifikasi alat, status stok, status tiket) di atas dekorasi visual.

Prinsip yang dipegang:

1. **Mobile-first & responsif.** Mahasiswa mengakses e-catalog dan form pengajuan dominan dari HP; admin/laboran cenderung memakai desktop di ruang lab — kedua konteks harus setara nyamannya.
2. **Data-dense tapi tidak sesak.** Dashboard admin menampilkan banyak data tabular (inventaris, tiket) — gunakan whitespace dan pengelompokan visual (card, section divider) alih-alih memadatkan semuanya dalam satu blok.
3. **Status selalu terlihat jelas.** Karena inti sistem adalah *state machine* tiket peminjaman, warna dan label status harus konsisten dan mudah dipindai mata (scannable) di semua halaman — katalog, riwayat mahasiswa, maupun dashboard admin.
4. **Aksi destruktif dibuat sengaja sulit disenggol.** Tombol tolak (reject), hapus, atau tandai hilang/rusak harus visually distinct (warna merah/rose) dan selalu meminta konfirmasi sebelum dieksekusi.

**Tone & manner:** Berdasarkan referensi visual yang dilampirkan (dashboard bertema violet/indigo dengan sidebar gelap, kartu metrik, dan tabel bergaris), kami mengadopsi *struktur layout*-nya (sidebar admin gelap, kartu ringkasan metrik di atas tabel, baris tabel bergaris/striped, badge berwarna) tetapi menggeser palet dasar ke arah **slate + blue** yang lebih netral-teknis, agar terasa seperti software inventaris teknik dan bukan platform edukasi konsumer. Warna indigo dari referensi tetap dipertahankan sebagai salah satu warna semantik status (`ON_LOAN`), bukan sebagai warna brand utama, untuk menghindari tabrakan makna warna.

---

## 2. Design Tokens & Visual Hierarchy

### 2.1 Color Palette

| Peran | Warna | HEX | Tailwind Class (bg / text) |
|---|---|---|---|
| Primary (CTA utama, link aktif, tombol approve) | Blue 600 | `#2563EB` | `bg-blue-600` / `text-blue-600` |
| Primary Hover | Blue 700 | `#1D4ED8` | `hover:bg-blue-700` |
| Secondary (aksi sekunder, ikon netral) | Slate 600 | `#475569` | `bg-slate-600` / `text-slate-600` |
| Sidebar Admin (gelap, terinspirasi referensi) | Slate 900 | `#0F172A` | `bg-slate-900` |
| Neutral Text — Heading | Slate 900 | `#0F172A` | `text-slate-900` |
| Neutral Text — Body | Slate 600 | `#475569` | `text-slate-600` |
| Neutral Text — Muted/Subtext | Slate 400 | `#94A3B8` | `text-slate-400` |
| Surface / Background App | Slate 50 | `#F8FAFC` | `bg-slate-50` |
| Surface / Card | White | `#FFFFFF` | `bg-white` |
| Border Default | Slate 200 | `#E2E8F0` | `border-slate-200` |
| Border Focus (input aktif) | Blue 500 | `#3B82F6` | `focus:border-blue-500` |

**Semantic Status Colors — Status Tiket Peminjaman**

| Status | Warna | Tailwind (badge) | Catatan |
|---|---|---|---|
| `PENDING` | Amber | `bg-amber-100 text-amber-800 border border-amber-200` | Menunggu verifikasi admin |
| `APPROVED` | Sky | `bg-sky-100 text-sky-800 border border-sky-200` | Disetujui penuh, stok sudah direservasi |
| `PARTIALLY_APPROVED` | Orange | `bg-orange-100 text-orange-800 border border-orange-200` | Dibedakan dari PENDING agar admin tidak salah baca |
| `REJECTED` | Rose | `bg-rose-100 text-rose-800 border border-rose-200` | Ditolak |
| `ON_LOAN` | Indigo | `bg-indigo-100 text-indigo-800 border border-indigo-200` | Alat sedang di tangan peminjam |
| `OVERDUE` | Rose (solid, lebih tegas) | `bg-rose-600 text-white` | Sengaja dibuat solid (bukan pastel) agar mencolok di dashboard admin |
| `RETURNED` | Emerald | `bg-emerald-100 text-emerald-800 border border-emerald-200` | Selesai, kondisi Baik |
| `RETURNED` (kondisi Rusak/Hilang) | Emerald outline + ikon peringatan | `bg-emerald-50 text-emerald-700 border border-amber-300` + ikon `⚠` | Tetap RETURNED secara status, tapi diberi penanda kondisi akhir |

**Semantic Status Colors — Ketersediaan Stok (E-Catalog)**

| Label | Kondisi | Tailwind |
|---|---|---|
| Tersedia | `available_stock` > 3 | `bg-emerald-100 text-emerald-800` |
| Stok Terbatas | `available_stock` 1-3 | `bg-amber-100 text-amber-800` |
| Tidak Tersedia | `available_stock` = 0 | `bg-slate-200 text-slate-500` |
| Maintenance / Nonaktif | `is_active` = false | `bg-slate-800 text-white` |

### 2.2 Typography

- **Font family:** `font-sans` (stack Tailwind default — Inter/System UI). Tidak perlu font kustom agar loading ringan dan konsisten lintas device.

| Elemen | Class Tailwind | Kegunaan |
|---|---|---|
| H1 | `text-3xl font-bold text-slate-900` | Judul halaman utama (misal "Katalog Alat Laboratorium") |
| H2 | `text-2xl font-semibold text-slate-900` | Judul section (misal "Detail Spesifikasi") |
| H3 | `text-lg font-semibold text-slate-900` | Judul card / sub-section |
| H4 | `text-base font-semibold text-slate-800` | Label grup form |
| Body | `text-sm text-slate-600` | Paragraf, deskripsi alat |
| Subtext / Caption | `text-xs text-slate-400` | Keterangan tambahan, timestamp |
| Badge Text | `text-xs font-medium` | Selalu dipasangkan dengan class badge di atas |
| Label Form | `text-sm font-medium text-slate-700` | Label input |

### 2.3 Elevation, Radius & Spacing

| Token | Nilai | Class | Kegunaan |
|---|---|---|---|
| Radius Card | 12px | `rounded-xl` | Card katalog, card metrik admin |
| Radius Button/Input | 8px | `rounded-lg` | Tombol, input field |
| Radius Badge/Pill | full | `rounded-full` | Badge status |
| Shadow Card (default) | halus | `shadow-sm` | Card di grid katalog |
| Shadow Card (hover) | sedang | `hover:shadow-md transition-shadow` | Efek hover kartu alat |
| Shadow Modal | kuat | `shadow-xl` | Modal, dropdown menu |
| Padding Card | — | `p-4 md:p-6` | Konsisten di semua card |
| Gap Grid Katalog | — | `gap-4 md:gap-6` | Spacing antar kartu alat |
| Container Max Width | — | `max-w-7xl mx-auto px-4` | Pembungkus halaman publik |

---

## 3. Core Component Library

### 3.1 Buttons & Action Triggers

```html
<!-- Primary Button -->
<button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-medium
               hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
               disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
  Ajukan Peminjaman
</button>

<!-- Secondary / Outline Button -->
<button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white
               text-slate-700 text-sm font-medium hover:bg-slate-50
               focus:outline-none focus:ring-2 focus:ring-slate-300">
  Batal
</button>

<!-- Danger / Reject Button -->
<button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-rose-600 text-white text-sm font-medium
               hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
  Tolak Tiket
</button>

<!-- Icon Button (aksi tabel: edit/delete) -->
<button class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition-colors"
        aria-label="Edit alat">
  <svg class="w-4 h-4" ...></svg>
</button>
```

### 3.2 Badges & Status Pills

```html
<!-- Badge status tiket (dinamis via Blade @php/match) -->
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
             bg-amber-100 text-amber-800 border border-amber-200">
  <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
  Menunggu Verifikasi
</span>

<!-- Badge stok tersedia -->
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
  Tersedia · 5 unit
</span>
```

Rekomendasi: buat sebagai **Blade component** `<x-status-badge :status="$loan->status" />` yang me-*resolve* class warna dari sebuah array mapping di helper/config, bukan di-copy manual tiap halaman — agar konsisten dan mudah diubah satu tempat saja.

### 3.3 Form Controls

```html
<!-- Text Input -->
<div>
  <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
  <input type="text" name="name"
         class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm
                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                placeholder:text-slate-400"
         placeholder="Sesuai KTM">
  <p class="mt-1 text-xs text-rose-600" x-show="errors.name">{{ $errors->first('name') }}</p>
</div>

<!-- Dropdown Select (Kategori) -->
<select class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm bg-white
               focus:outline-none focus:ring-2 focus:ring-blue-500">
  <option value="">Semua Kategori</option>
  <option value="alat-ukur-presisi">Alat Ukur Presisi</option>
</select>

<!-- Date Range Picker (Alpine.js, dua native date input dikunci silang) -->
<div x-data="{ start: '', end: '' }" class="grid grid-cols-2 gap-3">
  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Pinjam</label>
    <input type="date" x-model="start" :min="new Date().toISOString().split('T')[0]"
           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm">
  </div>
  <div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Kembali</label>
    <input type="date" x-model="end" :min="start"
           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm">
  </div>
</div>

<!-- File Upload — drag & drop + kamera HP (untuk KTM) -->
<div x-data="{ fileName: '', dragging: false }"
     @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
     @drop.prevent="dragging = false; fileName = $event.dataTransfer.files[0]?.name"
     :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-slate-300 bg-slate-50'"
     class="rounded-xl border-2 border-dashed p-6 text-center transition-colors">
  <input type="file" name="identity_card" accept="image/*,application/pdf" capture="environment"
         class="hidden" x-ref="fileInput" @change="fileName = $event.target.files[0]?.name">
  <p class="text-sm text-slate-600 mb-2">Seret berkas ke sini, atau</p>
  <button type="button" @click="$refs.fileInput.click()"
          class="text-sm font-medium text-blue-600 hover:underline">Pilih Berkas / Ambil Foto KTM</button>
  <p class="mt-2 text-xs text-slate-400" x-show="fileName" x-text="fileName"></p>
  <p class="mt-1 text-xs text-slate-400">Format JPG, PNG, atau PDF — maks. 2MB</p>
</div>
<!-- atribut capture="environment" memicu kamera belakang HP secara langsung pada browser mobile -->

<!-- Input Number Kuantitas (dengan stepper) -->
<div x-data="{ qty: 1, max: 5 }" class="inline-flex items-center border border-slate-300 rounded-lg">
  <button type="button" @click="qty = Math.max(1, qty - 1)"
          class="px-3 py-2 text-slate-500 hover:bg-slate-50">−</button>
  <input type="number" x-model.number="qty" :max="max" min="1"
         class="w-12 text-center text-sm border-x border-slate-300 py-2 focus:outline-none">
  <button type="button" @click="qty = Math.min(max, qty + 1)"
          class="px-3 py-2 text-slate-500 hover:bg-slate-50">+</button>
</div>
```

### 3.4 Data Cards — Katalog Alat

```html
<div class="group rounded-xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow overflow-hidden">
  <div class="relative aspect-[4/3] bg-slate-100">
    <img src="{{ asset('storage/'.$tool->image_path) }}" alt="{{ $tool->name }}"
         class="w-full h-full object-cover">
    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-xs font-medium bg-white/90 text-slate-700 shadow-sm">
      {{ $tool->category->name }}
    </span>
  </div>
  <div class="p-4">
    <h3 class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $tool->name }}</h3>
    <p class="mt-1 text-xs text-slate-500">Kode: {{ $tool->code }}</p>
    <div class="mt-3 flex items-center justify-between">
      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
        Tersedia · {{ $tool->available_stock }}
      </span>
      <button @click="$dispatch('open-tool-modal', { toolId: {{ $tool->id }} })"
              class="text-xs font-medium text-blue-600 hover:underline">Lihat Detail</button>
    </div>
  </div>
</div>
```

### 3.5 Modals & Drawers

```html
<!-- Modal Detail Spesifikasi Alat -->
<div x-data="{ open: false, toolId: null }"
     @open-tool-modal.window="toolId = $event.detail.toolId; open = true"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
  <div x-show="open" x-transition.opacity @click="open = false"
       class="absolute inset-0 bg-slate-900/60"></div>

  <div x-show="open"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95"
       x-transition:enter-end="opacity-100 scale-100"
       class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[85vh] overflow-y-auto">
    <div class="flex items-center justify-between p-5 border-b border-slate-200">
      <h2 class="text-lg font-semibold text-slate-900">Detail Spesifikasi Alat</h2>
      <button @click="open = false" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">✕</button>
    </div>
    <div class="p-5">
      <!-- Konten dimuat dinamis (fetch berdasarkan toolId) atau di-render server via Blade @foreach dengan x-show -->
    </div>
  </div>
</div>
```

Pola yang sama (`x-show` + `x-transition` + event `$dispatch`/`.window`) dipakai untuk **drawer konfirmasi approval tiket** di admin, hanya arah transisinya digeser dari kanan (`translate-x-full` → `translate-x-0`) agar terasa seperti panel samping, bukan modal tengah.

### 3.6 Data Tables (Admin)

```html
<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
  <table class="min-w-full divide-y divide-slate-200 text-sm">
    <thead class="bg-slate-50">
      <tr>
        <th class="px-4 py-3 text-left font-medium text-slate-500">No. Tiket</th>
        <th class="px-4 py-3 text-left font-medium text-slate-500">Peminjam</th>
        <th class="px-4 py-3 text-left font-medium text-slate-500">Status</th>
        <th class="px-4 py-3 text-right font-medium text-slate-500">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      @foreach ($loans as $i => $loan)
      <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50/50' }} hover:bg-blue-50/40 transition-colors">
        <td class="px-4 py-3 font-medium text-slate-800">{{ $loan->ticket_number }}</td>
        <td class="px-4 py-3 text-slate-600">{{ $loan->user->name }}</td>
        <td class="px-4 py-3"><x-status-badge :status="$loan->status" /></td>
        <td class="px-4 py-3 text-right space-x-1">
          <button class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600">👁</button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div class="flex items-center justify-between px-4 py-3 border-t border-slate-200 text-xs text-slate-500">
    {{ $loans->links() }} <!-- Pagination bawaan Laravel, style di-override via config Tailwind pagination view -->
  </div>
</div>
```

---

## 4. Screen Breakdown & Layout Specifications

### 4.1 Halaman E-Catalog Publik

- **Navbar publik:** `sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200`, berisi logo lab + tombol "Masuk" (login).
- **Hero/Search section:** judul singkat + input live search besar (`x-data="{ q: '' }"`, `x-model.debounce.400ms="q"`, `@input="fetchTools()"`).
- **Filter kategori:** baris chip horizontal, scrollable di mobile (`flex gap-2 overflow-x-auto pb-2`), setiap chip toggle `bg-blue-600 text-white` saat aktif vs `bg-white border border-slate-300 text-slate-600` saat tidak aktif.
- **Grid katalog:** `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6` — 1 kolom di HP, 2 di tablet, 3 di desktop, sesuai pola card pada 3.4.
- **Mobile:** search & filter tetap di atas tapi menyusun vertikal, filter kategori jadi horizontal-scroll chip (bukan dropdown, agar tap-friendly).

### 4.2 Modal Detail Spesifikasi Alat

Layout dua kolom di desktop (`grid md:grid-cols-2 gap-6`), satu kolom di mobile:
- **Kolom kiri:** galeri foto (gambar utama + thumbnail strip kecil di bawahnya bila ada >1 foto).
- **Kolom kanan:** tabel spesifikasi teknis (`<dl>` dengan `dt`/`dd` dipasangkan, bukan tabel HTML biasa, agar lebih ringan di-skim), lalu indikator ketersediaan yang membedakan dua angka secara eksplisit:
  ```html
  <div class="grid grid-cols-2 gap-3 mt-4">
    <div class="rounded-lg bg-slate-50 p-3">
      <p class="text-xs text-slate-400">Stok Fisik Total</p>
      <p class="text-lg font-semibold text-slate-900">{{ $tool->total_stock }}</p>
    </div>
    <div class="rounded-lg bg-emerald-50 p-3">
      <p class="text-xs text-emerald-600">Tersedia untuk Diajukan</p>
      <p class="text-lg font-semibold text-emerald-700">{{ $tool->available_stock }}</p>
    </div>
  </div>
  ```
  Pemisahan ini penting secara UX agar mahasiswa memahami bahwa `total_stock` ≠ `available_stock` (sebagian sudah direservasi tiket lain yang APPROVED/ON_LOAN).
- Tombol "Ajukan Peminjaman" di footer modal, disabled (`opacity-50 cursor-not-allowed`) jika `available_stock == 0`.

### 4.3 Formulir Pengajuan Peminjaman (Multi-Step)

Dikelola dengan satu `x-data` di elemen pembungkus (`{ step: 1, form: {...} }`), setiap step adalah `<div x-show="step === n">` — data tidak hilang saat berpindah step karena tetap dalam satu component tree Alpine.

- **Step Indicator** (di atas form): 3 bulatan bernomor terhubung garis, bulatan aktif `bg-blue-600 text-white`, terlewati `bg-emerald-500 text-white`, belum tercapai `bg-slate-200 text-slate-400`.
- **Step 1 — Identitas & Unggah KTM:** Nama Lengkap, No. HP, Instansi, komponen upload KTM (3.3).
- **Step 2 — Pilihan Alat & Kuantitas:** list alat terpilih dengan komponen stepper kuantitas (3.3), tombol "+ Tambah Alat Lain" membuka pencarian alat singkat (reuse live search) untuk menambah baris baru secara dinamis (`x-for` di atas array `form.items`).
- **Step 3 — Rentang Tanggal & Keperluan:** date range picker (3.3) + textarea keperluan, lalu ringkasan akhir (recap semua alat & tanggal) sebelum tombol submit final.
- Navigasi: tombol "Lanjut" divalidasi per-step di sisi klien (Alpine) sebagai *quick feedback*, namun **validasi final tetap wajib di server** (Form Request Laravel) sebelum data disimpan — validasi klien hanya kenyamanan, bukan pengganti keamanan.

### 4.4 Dashboard & Manajemen Inventaris Admin

- **Sidebar** (`w-64 bg-slate-900 text-slate-300`, fixed di desktop, jadi off-canvas drawer di mobile lewat `x-show`/`translate-x`): menu Dashboard, Katalog Alat, Kategori, Tiket Masuk, Riwayat Transaksi.
- **Kartu ringkasan metrik** di atas dashboard (`grid grid-cols-2 lg:grid-cols-4 gap-4`): Total Alat, Tiket Aktif (PENDING+APPROVED+ON_LOAN), Tiket Overdue (warna aksen rose agar mencolok), Alat Nonaktif/Maintenance.
- **Tabel master inventaris** (CRUD) memakai pola 3.6, dengan tombol "+ Tambah Alat" primary di kanan atas tabel.
- **Halaman validasi/approval tiket:** tabel tiket PENDING, klik baris membuka **drawer** (bukan modal — karena berisi banyak informasi: detail pengaju, KTM, daftar item per alat) dengan aksi approve/reject **per item** (checkbox/toggle per baris alat di dalam tiket), sesuai aturan partial approval pada PRD.

### 4.5 Mobile Navigation

- **Sisi mahasiswa/publik:** bottom navigation bar (`fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 flex justify-around py-2`, hanya tampil `md:hidden`) berisi: Katalog, Riwayat Pengajuan, Profil — memudahkan navigasi jempol tanpa harus scroll ke navbar atas.
- **Sisi admin:** tidak memakai bottom nav (dashboard admin diasumsikan lebih sering diakses desktop di ruang lab); sebagai gantinya sidebar berubah jadi off-canvas drawer yang dibuka lewat tombol hamburger di topbar mobile.

---

## 5. Micro-interactions & State Management (Alpine.js)

| Interaksi | Implementasi |
|---|---|
| Toggle modal/drawer | `x-data="{ open: false }"`, dipicu `@click="open = true"`, ditutup via overlay click, tombol ✕, atau `@keydown.escape.window="open = false"` |
| Live text filter (katalog) | `x-model.debounce.400ms="query"` dikombinasikan `@input` yang memanggil `fetch()` ke endpoint pencarian, hasil di-render ulang lewat `x-for` atau swap partial HTML |
| Filter kategori (chip toggle) | State `selectedCategory` di root `x-data`, setiap chip `@click="selectedCategory = 'kategori-x'"`, class dinamis via `:class` |
| Tab switching (mis. tab "Semua/Aktif/Selesai" di riwayat mahasiswa) | `x-data="{ tab: 'semua' }"`, konten tiap tab `x-show="tab === '...'"`, tombol tab `:class="tab === '...' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500'"` |
| Validasi form instan (sisi klien) | `x-data` menyimpan object `errors`, dicek `@input`/`@blur` per field sebelum submit, ditampilkan lewat `x-show="errors.field"` — **tetap didampingi validasi server** |
| Multi-step form | Satu `x-data` root menyimpan `step` dan seluruh `form` object; berpindah step tidak reload halaman dan tidak kehilangan data |
| Konfirmasi aksi destruktif | `x-data="{ confirming: false }"`, klik tombol hapus/tolak pertama kali hanya mengubah tombol jadi "Yakin?" (`x-show="!confirming"` vs `x-show="confirming"`), klik kedua baru submit — alternatif ringan dari modal konfirmasi terpisah untuk aksi kecil di dalam tabel |

---

## 6. Directory & File Blueprint (Laravel Blade Structure)

```
resources/views/
├── layouts/
│   ├── app.blade.php              # Layout dasar (head, Vite, slot)
│   ├── guest.blade.php            # Layout publik/e-catalog (navbar + bottom nav mobile)
│   └── admin.blade.php            # Layout dashboard admin (sidebar + topbar)
│
├── components/                    # Blade components reusable
│   ├── status-badge.blade.php     # <x-status-badge :status="" />
│   ├── stock-badge.blade.php      # <x-stock-badge :tool="" />
│   ├── button.blade.php           # <x-button variant="primary|outline|danger" />
│   ├── modal.blade.php            # <x-modal name="" /> generik (slot konten)
│   ├── data-table.blade.php       # wrapper tabel + pagination styling
│   └── form/
│       ├── input.blade.php
│       ├── select.blade.php
│       ├── date-range.blade.php
│       └── file-upload.blade.php
│
├── catalog/                       # Modul Frontend/Mahasiswa - E-Catalog
│   ├── index.blade.php            # Halaman katalog publik + live search + filter
│   └── partials/
│       └── tool-card.blade.php
│
├── loans/                         # Modul Frontend/Mahasiswa - Peminjaman
│   ├── create.blade.php           # Form multi-step pengajuan
│   ├── history.blade.php          # Riwayat & status pengajuan mahasiswa (FR-A-11)
│   └── partials/
│       └── loan-item-row.blade.php
│
└── admin/                         # Modul Backend/Admin Dashboard
    ├── dashboard.blade.php        # Kartu metrik ringkasan
    ├── categories/
    │   ├── index.blade.php
    │   └── form.blade.php
    ├── tools/
    │   ├── index.blade.php
    │   └── form.blade.php
    ├── loan-requests/
    │   ├── index.blade.php        # Daftar tiket masuk (PENDING dkk.)
    │   └── show.blade.php         # Detail tiket + drawer approval per item
    └── reports/
        └── index.blade.php        # Laporan & riwayat transaksi (FR-B-09)
```

**Catatan struktural:**
- Folder `components/` menampung seluruh elemen dari BAB 3 dokumen ini — hindari menulis ulang class Tailwind badge/button secara manual di tiap halaman.
- Folder `catalog/` dan `loans/` adalah tanggung jawab utama sisi frontend publik (Faiq); folder `admin/` adalah tanggung jawab utama dashboard admin, namun tetap memakai komponen yang sama dari `components/` agar konsisten visual antara sisi publik dan admin.