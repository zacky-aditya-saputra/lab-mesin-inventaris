# Design System: LabCatalog Teknik Mesin Unwahas (Digital Atelier Adaptation)

Dokumen sistem desain antarmuka resmi untuk aplikasi **LabCatalog (Sistem E-Katalog & Inventaris Peralatan Laboratorium Fakultas Teknik Mesin Universitas Wahid Hasyim Semarang)**.

Sistem desain ini mengadaptasi tata letak estetis **Digital Atelier** (referensi: *header 3-bagian simetris*, *sidebar kiri terintegrasi*, *banner cyan di kolom kanan*, dan *grid produk proporsional 4-kolom*), yang ditransformasikan menjadi **Laboratorium Teknik Mesin Modern & Industrial**.

> **Model Peminjaman Tunggal (Single-Item Loan Flow)**:
> Sistem menggunakan alur peminjaman langsung per satu jenis alat (*single tool loan*). Fitur keranjang belanja ditiadakan agar mahasiswa fokus pada peralatan spesifik yang dibutuhkan per pengajuan, memudahkan verifikasi oleh laboran dan serah terima fisik di laboratorium.

---

## 1. Perbandingan Visual & Diagram Tata Letak (Exact Layout Blueprint)

Mengacu langsung pada referensi visual, halaman katalog desktop memiliki struktur tata letak 2-kolom terpadu di bawah navigasi atas:

```
+----------------------------------------------------------------------------------------------------------+
|  LabCatalog                  Katalog   Peminjaman Saya   Panduan SOP   Kontak Lab                🔍   👤   | <- 1. Top Navbar (3-Section)
+----------------------------------------------------------------------------------------------------------+
|                                                                                                          |
|  [KOLOM KIRI: SIDEBAR w-64]      [KOLOM KANAN: KONTEN UTAMA flex-1 min-w-0]                              |
|                                                                                                          |
|  Filters                         Koleksi Peralatan Laboratorium                                          |
|  Refine your selection           Eksplorasi inventaris peralatan praktikum dan riset presisi ...         |
|                                                                                                          |
|  (🏷️ Kategori Alat      )        +---------------------------------------------------------------------+ | <- 3. Quick Guide Banner
|                                  | [PANDUAN SINGKAT]                                                   | |    (Cyan Background)
|  🔬 Laboratorium Pengampu        | CARI ALAT, CEK STOK & AJUKAN MANDIRI              +---------------+ | |
|                                  | Pantau stok real-time, pilih alat, dan ajukan     | 3             | | |
|  ⚙️ Kondisi & Kalibrasi          | langsung tanpa formulir kertas.                   | LANGKAH MUDAH | | |
|                                  |                                                   | Cek & Pinjam  | | |
|  📦 Ketersediaan Stok            | ALUR : [ 1. PILIH ALAT → 2. FORMULIR → 3. AMBIL ] +---------------+ | |
|                                  +---------------------------------------------------------------------+ |
|  Clear All                                                                                               |
|                                  Menampilkan 14 dari 14 alat                                             |
|                                                                                                          |
|                                  +-------------+  +-------------+  +-------------+  +-------------+      | <- 4. Equipment Grid
|                                  | [Foto 3:4]  |  | [Foto 3:4]  |  | [Foto 3:4]  |  | [Foto 3:4]  |      |    (4 Kolom)
|                                  | [READY]     |  | [HABIS]     |  | [READY]     |  | [READY]     |      |
|                                  | Nama Alat   |  | Nama Alat   |  | Nama Alat   |  | Nama Alat   |      |
|                                  | Lab · Kode  |  | Lab · Kode  |  | Lab · Kode  |  | Lab · Kode  |      |
|                                  | Stok: 3     |  | Stok: 0     |  | Stok: 2     |  | Stok: 5     |      |
|                                  | [ Pinjam ]  |  | [ Habis ]   |  | [ Pinjam ]  |  | [ Pinjam ]  |      |
|                                  +-------------+  +-------------+  +-------------+  +-------------+      |
+----------------------------------------------------------------------------------------------------------+
```

---

## 2. Navigasi Atas (Top Navigation Bar - 3 Sections)

Navigasi atas membagi ruang secara seimbang menjadi 3 area (`flex items-center justify-between h-18 px-8 bg-white border-b border-slate-100 sticky top-0 z-50`):

### 2.1 Sisi Kiri (Brand Identity)
* **Wordmark**: `LabCatalog` (atau logo lab Teknik Mesin Unwahas + teks `LabCatalog`).
* **Tipografi**: `text-xl lg:text-2xl font-black text-slate-900 tracking-tight font-sans`.
* **Karakter**: Bersih, tegas, tanpa pembungkus badge atau ornamen mencolok.

### 2.2 Sisi Tengah (Flush Bottom-Aligned Blue Container)
Tautan navigasi berada di tengah bilah dalam kontainer persegi panjang berwarna biru terang (*vibrant blue* `bg-blue-600 rounded-t-2xl rounded-b-none px-5 lg:px-7 py-3 text-white shadow-xs`) yang menempel rapat tanpa celah (*flush / bottom-aligned* `-mb-px`) pada batas bawah navbar:
* **Bentuk & Sudut**: Dua sudut atas membulat (`rounded-t-2xl`), sedangkan dua sudut bawah siku-siku/lurus (`rounded-b-none`) yang menyatu dan memotong lurus batas bawah navbar (*bottom border*).
* **Menu Navigasi Horizontal**:
  1. **Katalog**: `text-xs lg:text-sm font-semibold text-white` (dengan highlight aktif halus `bg-white/20 px-3 lg:px-3.5 py-1 rounded-lg`).
  2. **Riwayat**: `text-xs lg:text-sm font-semibold text-white/90 hover:text-white`.
  3. **Pengajuan**: `text-xs lg:text-sm font-semibold text-white/90 hover:text-white`.
  4. **Logbook Kegiatan**: `text-xs lg:text-sm font-semibold text-white/90 hover:text-white`.
  5. **Panduan**: `text-xs lg:text-sm font-semibold text-white/90 hover:text-white`.
  6. **Info Kontak**: `text-xs lg:text-sm font-semibold text-white/90 hover:text-white`.
* **Distribusi & Spasi**: `gap-2.5 lg:gap-5` terdistribusi merata dengan *padding* yang proporsional di dalam kontainer.

### 2.3 Sisi Kanan (Notifikasi & Akun Pengguna)
* **Notifikasi**: Tombol lonceng bulat bersih (`x-notification-bell`).
* **Profil Pengguna**: Wadah pill (`rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3.5 shadow-xs`) memuat avatar lingkaran biru huruf inisial + nama tebal dan peran biru di bawahnya + chevron dropdown.
*(Catatan: Ikon keranjang belanja ditiadakan demi kesederhanaan alur).*

---

## 3. Kolom Kiri: Sidebar Filter (Filters & Refine)

Berada di kolom kiri halaman desktop (`w-60 lg:w-64 shrink-0 pr-6 pt-6 sticky top-24`):

### 3.1 Header Sidebar
* **Judul**: `Filters` (`text-lg font-bold text-slate-900 tracking-tight`).
* **Subjudul**: `Refine your selection` (`text-xs text-slate-400 mt-0.5 block`).

### 3.2 Daftar Filter Vertikal
Mengikuti format referensi (*icon + label*, dengan item aktif berbentuk pill halus):
1. **Kategori Alat** (Item Terpilih / Aktif):
   * Tampilan: Wadah pill melingkar lembut (`rounded-full bg-blue-50/80 border border-blue-100 px-4 py-2.5 flex items-center gap-3 text-blue-700 font-semibold text-xs shadow-xs`).
   * Ikon: `category` (warna biru `#2563EB`).
2. **Laboratorium Pengampu**:
   * Tampilan: Baris interaktif bersih (`px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:text-slate-900 text-xs font-medium cursor-pointer transition-colors`).
   * Ikon: `science` atau `precision_manufacturing`.
3. **Kondisi & Kalibrasi**:
   * Tampilan: `px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:text-slate-900 text-xs font-medium cursor-pointer`.
   * Ikon: `verified` atau `tune`.
4. **Status Ketersediaan**:
   * Tampilan: `px-4 py-2.5 flex items-center gap-3 text-slate-600 hover:text-slate-900 text-xs font-medium cursor-pointer`.
   * Ikon: `inventory_2`.

### 3.3 Tombol Reset
* **Clear All**: Teks bersih di bawah daftar filter (`text-blue-600 hover:text-blue-700 text-xs font-semibold cursor-pointer px-4 pt-4 block`).

---

## 4. Kolom Kanan: Konten Utama & Hero Quick Guide Banner

Kolom kanan menampung judul halaman, hero banner cyan panduan ringkas, dan grid alat (`flex-1 min-w-0 pt-6`):

### 4.1 Judul Halaman & Subjudul (Header Konten)
* **Judul Utama**: `Koleksi Peralatan Laboratorium` (`text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight`).
* **Deskripsi**: `Eksplorasi inventaris peralatan praktikum dan riset presisi lintas laboratorium Teknik Mesin Unwahas. Cek ketersediaan unit secara real-time sebelum mengajukan peminjaman mandiri.` (`text-sm text-slate-500 max-w-2xl mt-2 leading-relaxed`).

### 4.2 Hero Quick Guide Banner (Cyan Rounded Banner)
Banner berada **di dalam kolom kanan**, tepat di bawah judul koleksi dan di atas grid alat:
* **Wadah Luar**:
  * Warna: Cyan cerah menyegarkan (`bg-[#26D0ED]` atau `bg-gradient-to-r from-cyan-400 to-cyan-500`).
  * Radius: `rounded-3xl` (24px).
  * Spacing: `p-7 lg:p-9 mt-6 flex flex-col md:flex-row items-stretch justify-between gap-6 relative overflow-hidden shadow-sm`.
* **Sisi Kiri Banner**:
  * **Tag Subjudul**: Teks uppercase bersih (`text-[11px] font-bold uppercase tracking-wider text-slate-900/75`).
    * Teks: `Panduan Singkat`.
  * **Judul Besar**: `CARI ALAT, CEK STOK & AJUKAN MANDIRI` (`text-2xl lg:text-3xl font-extrabold text-slate-950 tracking-tight mt-1`).
  * **Subjudul Ringkas**: `Pantau sisa stok siap pakai secara real-time, pilih alat yang dibutuhkan, dan ajukan peminjaman langsung tanpa formulir kertas.` (`text-sm lg:text-base font-medium text-slate-900/90 mt-1.5`).
  * **Informasi Alur Ringkas**:
    * Teks alami: `Pilih alat → Isi formulir → Ambil di lab` · `Stok terhubung langsung` (tanpa kapsul gelap atau tanda kurung buatan).
* **Sisi Kanan Banner (Glass Card)**:
  * Kartu persegi melengkung di dalam banner (`w-full md:w-56 shrink-0 rounded-2xl bg-white/25 backdrop-blur-md border border-white/40 p-6 flex flex-col items-center justify-center text-center shadow-xs`):
    * Angka Besar: **3** (`font-mono text-4xl lg:text-5xl font-black text-slate-900`).
    * Label: **LANGKAH MUDAH** (`text-[11px] font-bold tracking-wider uppercase text-slate-900/80 mt-1`).
    * Sub-label: `Cek Stok & Pinjam`.
* **Overlay Foto Otentik Gedung Teknik (Anti AI-Slop Campus Presence)**:
  * Foto asli Gedung Fakultas Teknik Unwahas (`public/images/gedung-teknik.jpeg`) diposisikan sebagai backdrop overlay di dalam banner cyan:
    * `absolute inset-0 pointer-events-none overflow-hidden z-0`.
    * Gambar: `object-cover object-[center_60%] opacity-45 sm:opacity-50 mix-blend-multiply filter contrast-125 brightness-95`.
    * Layer Gradien Proteksi: `bg-gradient-to-r from-[#26D0ED] via-[#26D0ED]/60 to-transparent` memastikan teks panduan di sisi kiri berkontras tajam (WCAG AA), sementara fasad arsitektur gedung penuh dan papan nama "FAKULTAS TEKNIK" tampil tegak dan jelas di tengah dan di latar belakang kartu kaca.

---

## 5. Grid Kartu Alat (Equipment Product Cards)

Tepat di bawah banner cyan terdapat baris keterangan jumlah alat (`Menampilkan 14 dari 14 alat`) dan grid 4 kolom:

### 5.1 Spesifikasi Kartu Desktop
* **Grid**: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-6`.
* **Rasio Foto**: **Portrait Tinggi (3:4)** yang lega dan profesional.
* **Wadah Kartu**:
  * `rounded-2xl overflow-hidden bg-white border border-slate-200/80 shadow-xs hover:shadow-lg hover:shadow-blue-500/8 hover:-translate-y-1 transition-all duration-200 flex flex-col`.
* **Media Container**:
  * Rasio `aspect-[3/4]` dengan latar studio foto bersih (`bg-slate-100/80`).
  * **Badge Pojok Kiri Atas**:
    * Alat Ready: Kapsul Cyan (`bg-cyan-500 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md shadow-xs`) bertuliskan `READY STOCK`.
    * Alat Habis: Kapsul Merah (`bg-rose-500 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md`) bertuliskan `STOK HABIS`.
* **Konten Informasi Bawah**:
  * **Nama Alat**: `font-bold text-slate-900 text-sm leading-snug line-clamp-1 mt-3 px-3.5`.
  * **Sub-info**: `text-xs text-slate-500 font-mono px-3.5 mt-0.5` (contoh: `LPP · Gedung D Lt. 1`).
  * **Telemetri & Aksi (Footer Kartu)**:
    * `flex items-center justify-between px-3.5 pb-3.5 pt-2 border-t border-slate-100 mt-2`:
      * Angka Stok: `font-mono text-xs font-bold text-emerald-600` (misal: `3 Unit`).
      * Tombol Pill: `Pinjam Alat` (`px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-full shadow-xs active:scale-95 transition-all`) yang langsung mengarahkan pengguna ke formulir peminjaman alat tersebut.

---

## 6. Detail Spesifikasi Alat (Minimalist Floating Modal & Card)

Mengadaptasi tata letak kartu minimalis modern (referensi kartu mengambang *rounded-3xl*, visual bersih di atas, tipografi bernapas, dan tombol pill empuk di bawah):

```
+-----------------------------------------------------------+
|                                                           |
|             [ FOTO ALAT BERSIH & FOKUS ]                  |
|             (Rasio Lebar, Latar Studio Netral)            |
|                                                           |
+-----------------------------------------------------------+
| Mesin Bubut Konvensional 1000mm                           |
| Mesin Perkakas · Lab Proses Produksi · Gedung D Lt. 1     |
|                                                           |
| Peralatan fabrikasi presisi untuk pembubutan logam,       |
| ulir drat, dan permesinan silindris praktikum mesin.      |
|                                                           |
| Spesifikasi Kunci:                                        |
| • 3-Phase 380V / 2.2 kW  • Jarak Senter 1000mm            |
|                                                           |
| [ ▾ Lihat Spesifikasi Lengkap (Toleransi, RPM, Aksesoris) ]| <- Tombol Expand/Accordion
|                                                           |
| +-------------------------------------------------------+ |
| | 3 Unit                       [  Ajukan Peminjaman  ]  | | <- Baris Telemetri & CTA
| | Tersedia Siap Pakai                                   | |
| +-------------------------------------------------------+ |
+-----------------------------------------------------------+
```

### 6.1 Karakteristik & Hierarki Antarmuka
1. **Wadah Utama**: `max-w-md lg:max-w-lg rounded-3xl bg-white shadow-2xl overflow-hidden p-0 relative border border-slate-100`.
2. **Visual Atas**: Foto alat tampil dominan memenuhi separuh atas kartu dengan sudut melengkung atas (`rounded-t-3xl`), latar belakang studio hangat/netral (`bg-slate-50`), tanpa stiker badge yang berisik.
3. **Area Konten (Tengah)**:
   * **Judul Alat**: `text-xl lg:text-2xl font-bold text-slate-900 tracking-tight mt-5 px-6`.
   * **Atribut Tipis**: Teks biasa abu-abu lembut tanpa kapsul (`text-xs text-slate-400 font-medium px-6 mt-1 flex items-center gap-2`). Contoh: `Mesin Perkakas · Lab Proses Produksi · Gedung D Lt. 1`.
   * **Deskripsi Singkat**: 1-2 baris penjelasan fungsi utama (`text-sm text-slate-600 leading-relaxed px-6 mt-3`).
   * **Spesifikasi Inti & Tombol Selebihnya (Collapsible)**:
     - Ditampilkan 2-3 spesifikasi utama saja agar kartu tetap bersih dan tidak sesak.
     - **Tombol "Lihat Spesifikasi Lengkap"**: Tautan/tombol teks minimalis (`text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 mt-2.5 px-6 cursor-pointer`).
     - Jika diklik, bagian bawah meluas menampilkan rincian lanjutan (rentang toleransi, kecepatan spindle, kelengkapan chuck & pahat) tanpa merusak kerapian awal kartu.
4. **Footer Aksi Bawah (Sejajar & Seimbang)**:
   * Terletak di dasar kartu (`flex items-center justify-between px-6 py-5 mt-4 border-t border-slate-100/80 bg-white`):
     - **Sisi Kiri (Stok Telemetri)**:
       * Angka besar tebal: `text-xl font-mono font-bold text-slate-900` (atau `text-emerald-600`) -> `3 Unit`.
       * Keterangan kecil di bawah angka: `text-[11px] text-slate-400 font-medium` -> `Tersedia Siap Pakai`.
     - **Sisi Kanan (Tombol Aksi Utama)**:
       * Tombol pill lembut, empuk, dan elegan: `Ajukan Peminjaman` (`bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-3 rounded-full shadow-md shadow-blue-500/15 active:scale-95 transition-all cursor-pointer`).
       * Mengarahkan langsung ke formulir peminjaman dengan alat ini terpilih otomatis.

---

## 7. Formulir Pengajuan Peminjaman (Single-Item Wizard)

Formulir dirancang fokus meminjam satu jenis alat per pengajuan:
* **Step 1: Data Mahasiswa**: Nama lengkap, NIM, No. WhatsApp aktif, Program Studi, dan pratinjau dokumen KTM.
* **Step 2: Konfirmasi Alat & Kuantitas**:
  * Kartu info alat yang dipilih (foto, nama, kode inventaris, laboratorium pengampu).
  * Input kuantitas unit yang ingin dipinjam (stepper angka dengan validasi otomatis tidak boleh melebihi sisa stok tersedia).
* **Step 3: Jadwal, Dokumen & Integritas**:
  * Tanggal mulai peminjaman (minimal H-2).
  * Tanggal pengembalian (maksimal 7 hari kerja).
  * Keperluan peminjaman (textarea).
  * Unggah surat permohonan/rekomendasi dosen (PDF maks 2MB).
  * Checkbox persetujuan tata tertib K3 laboratorium.

---

## 8. Riwayat Pengajuan Saya (Student Tickets)

* **3 Kartu Ringkasan Putih**: Total Pengajuan, Sedang Dipinjam, Menunggu Verifikasi.
* **Tabel / Kartu Tiket Interaktif**: Nomor tiket (font-mono), nama alat tunggal yang diajukan beserta jumlahnya, tanggal pinjam, dan badge status resmi.
* **Aksi Cepat**: Cetak Bukti Tiket (PDF) jika disetujui untuk serah terima fisik alat di laboratorium.

---

## 9. Adaptasi Mobile-First (Anti-Pecah)

1. **Top Bar Mobile (54px)**: Logo ringkas dan tombol pencarian instan melingkar.
2. **Horizontal Chip Carousel**: Deretan chip filter lab dan kategori yang digeser mendatar.
3. **Horizontal Media Card**: Kartu alat mendatar (media 88x88px di kiri, teks & tombol "Pinjam" di kanan).
4. **Sticky Bottom Nav**: 3 menu utama jempol:
   * **Katalog** (`storefront`)
   * **Filter** (`tune`)
   * **Tiket Saya** (`confirmation_number`)
