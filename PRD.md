# SPESIFIKASI KEBUTUHAN SISTEM (SOFTWARE REQUIREMENTS SPECIFICATION)
## Sistem Layanan Pengelolaan, Peminjaman Alat, dan E-Catalog Berbasis Web
### Laboratorium Teknik Mesin, Universitas Wahid Hasyim (Unwahas)

| Atribut Dokumen | Keterangan |
|---|---|
| Nama Proyek | Sistem Layanan Pengelolaan, Peminjaman Alat, dan E-Catalog Berbasis Web |
| Lokasi Studi Kasus | Laboratorium Proses Produksi Teknik Mesin, Universitas Wahid Hasyim |
| Periode Pengerjaan | 24 Agustus 2026 - 24 Oktober 2026 |
| Metodologi Pengembangan | Waterfall |
| Tim Pengembang | 2 (dua) orang Mahasiswa Kerja Praktik |
| Status Dokumen | Final Draft (Revisi 1.1 — Perluasan Cakupan Klien & Dokumen Identitas) |
| Catatan Revisi | CR-01: Perluasan cakupan pengguna ("Mahasiswa" → "Klien" mencakup Civitas Akademika Unwahas & Klien Umum), form pengajuan adaptif berdasarkan jenis klien (NIM/KTM bagi Mahasiswa Unwahas, NIK/KTP bagi Klien Umum), serta peninjauan tiket admin yang menampilkan jenis klien dan dokumen identitas. Tanpa perubahan skema database, state machine, maupun role autentikasi (tetap biner Admin vs Klien). |

---

## 1. GAMBARAN UMUM & TUJUAN

### 1.1 Latar Belakang

Laboratorium Teknik Mesin Unwahas hingga saat ini masih menjalankan proses pengelolaan aset dan peminjaman alat secara manual, baik melalui pencatatan pada buku inventaris fisik maupun melalui komunikasi informal (lisan atau pesan singkat) antara klien (baik civitas akademika Unwahas maupun klien umum/masyarakat) dan petugas laboratorium (laboran/teknisi). Pendekatan manual ini menimbulkan sejumlah permasalahan operasional, di antaranya:

1. **Minimnya transparansi ketersediaan alat.** Klien tidak memiliki akses informasi mengenai jenis, spesifikasi, dan status ketersediaan alat secara real-time sebelum mengajukan peminjaman, sehingga sering terjadi kunjungan yang sia-sia ke laboratorium.
2. **Risiko kehilangan data dan human error.** Pencatatan manual pada buku fisik rentan terhadap kesalahan pencatatan, kehilangan dokumen, maupun ketidaksesuaian data stok akibat pembaruan yang tidak konsisten.
3. **Proses verifikasi yang tidak terstruktur.** Tidak adanya alur persetujuan (approval) yang baku menyebabkan sulitnya melacak status suatu pengajuan peminjaman (apakah sudah disetujui, sedang dipinjam, atau sudah dikembalikan).
4. **Sulitnya pelacakan alat yang terlambat dikembalikan (overdue).** Petugas laboratorium tidak memiliki alat bantu otomatis untuk memantau alat-alat yang seharusnya sudah dikembalikan namun belum kembali.

Berdasarkan permasalahan tersebut, dibutuhkan sebuah sistem informasi berbasis web yang mampu mendigitalisasi proses e-katalog alat laboratorium sekaligus alur peminjaman dan pengelolaan inventaris, sehingga proses administrasi laboratorium menjadi lebih efisien, transparan, dan akuntabel bagi seluruh kalangan pengguna.

### 1.2 Tujuan Utama Sistem

Tujuan pengembangan sistem ini adalah untuk:

1. Menyediakan **e-catalog digital** yang memuat data alat laboratorium secara lengkap (nama, kategori, spesifikasi, dan status ketersediaan) yang dapat diakses oleh klien secara mandiri (self-service).
2. Membangun **alur pengajuan peminjaman alat digital** yang terstruktur, mulai dari pengajuan oleh klien hingga verifikasi oleh admin/teknisi laboratorium.
3. Mengotomatisasi **pengelolaan stok fisik alat** melalui mekanisme *state machine* pada status tiket peminjaman, sehingga jumlah stok tersedia selalu konsisten dengan kondisi riil di lapangan.
4. Menyediakan **dashboard administrasi** bagi admin/teknisi laboratorium untuk mengelola data master alat, memverifikasi pengajuan peminjaman berdasarkan keabsahan identitas peminjam, dan memantau alat yang berstatus terlambat (overdue).
5. Meningkatkan **akuntabilitas dan jejak audit (audit trail)** atas setiap transaksi peminjaman dan pengembalian alat laboratorium.

---

## 2. USER PERSONAS & ROLE

Sistem ini dirancang dengan dua peran pengguna (role-based access) yang memiliki hak akses dan tujuan penggunaan yang berbeda.

### 2.1 Persona 1 — Admin / Teknisi Laboratorium

| Aspek | Deskripsi |
|---|---|
| Peran dalam sistem | Pengelola penuh sistem (full access) |
| Tujuan utama | Menjaga akurasi data inventaris, memverifikasi keabsahan identitas klien, dan memastikan proses peminjaman berjalan tertib |
| Hak akses | - Mengelola (CRUD) master data kategori dan alat<br>- Melakukan verifikasi (approval/rejection) atas tiket pengajuan peminjaman berdasarkan profil klien dan dokumen identitas (KTM atau KTP)<br>- Memperbarui status tiket peminjaman sesuai alur state machine<br>- Memantau dan mengelola stok fisik alat<br>- Melihat riwayat transaksi peminjaman dan laporan alat overdue |
| Contoh kebutuhan | "Saya perlu memastikan alat yang saya setujui untuk dipinjam otomatis berkurang stoknya, dan otomatis bertambah kembali saat alat tersebut dikonfirmasi telah dikembalikan." |

### 2.2 Persona 2 — Klien (Peminjam)

| Aspek | Deskripsi |
|---|---|
| Peran dalam sistem | Pengguna publik/klien (self-service) |
| Sub-Kategori Klien | a. **Civitas Akademika Unwahas (Mahasiswa/Dosen):** Pengguna internal kampus yang meminjam alat untuk keperluan praktikum, tugas akhir/skripsi, penelitian dosen/mahasiswa, atau kegiatan akademik; verifikasi identitas menggunakan **Kartu Tanda Mahasiswa (KTM)** bagi mahasiswa.<br>b. **Klien Umum (Masyarakat umum, institusi luar, mitra industri/UKM):** Pengguna eksternal (praktisi bengkel, teknisi industri, instansi luar, UMKM, atau perorangan) yang membutuhkan fasilitas laboratorium untuk keperluan pengujian, manufaktur, atau proyek profesional; verifikasi identitas menggunakan **Kartu Tanda Penduduk (KTP)**. |
| Tujuan utama | Mencari informasi alat dan mengajukan peminjaman tanpa harus datang langsung ke laboratorium terlebih dahulu |
| Hak akses | - Melihat dan mencari alat pada e-catalog (tanpa perlu login)<br>- Melihat detail spesifikasi alat<br>- Login/registrasi untuk mengajukan peminjaman<br>- Mengisi dan mengirimkan form pengajuan peminjaman (memilih jenis klien/identitas, mengisi data identitas adaptif NIM/NIK beserta asal/instansi, dan mengunggah berkas KTM/KTP yang sesuai)<br>- Memantau status pengajuan peminjaman miliknya secara transparan |
| Contoh kebutuhan | "Sebagai mahasiswa Teknik Mesin Unwahas maupun teknisi bengkel umum, saya ingin tahu apakah jangka sorong (caliper) tersedia untuk dipinjam minggu depan, dan saya ingin mengajukan peminjamannya secara online tanpa harus bolak-balik ke lab." |

---

## 3. KEBUTUHAN FUNGSIONAL (FUNCTIONAL REQUIREMENTS)

Kebutuhan fungsional dibagi menjadi dua modul besar, selaras dengan pembagian tanggung jawab tim pengembang (Faiq — Frontend, Zacky — Backend & Database).

### 3.1 Modul A — Frontend / Klien (E-Catalog & Peminjaman)

**A.1 Halaman E-Catalog Publik**

| ID | Kebutuhan Fungsional | Deskripsi |
|---|---|---|
| FR-A-01 | Live Search Alat | Sistem menyediakan kolom pencarian alat secara real-time (tanpa reload halaman) menggunakan Alpine.js, berdasarkan nama atau kata kunci alat. |
| FR-A-02 | Filter Kategori | Pengguna dapat menyaring daftar alat berdasarkan kategori (misalnya: Alat Ukur, Alat Uji Material, Alat Bantu Praktikum). |
| FR-A-03 | Modal Detail Spesifikasi | Saat kartu alat diklik, sistem menampilkan modal (pop-up) berisi detail spesifikasi alat: nama, kode/kode inventaris, kategori, deskripsi, gambar, serta status ketersediaan (jumlah stok tersedia). |
| FR-A-04 | Indikator Status Ketersediaan | Setiap alat pada katalog menampilkan label status secara visual (contoh: "Tersedia", "Stok Terbatas", "Tidak Tersedia") berdasarkan jumlah stok fisik yang tersisa. |
| FR-A-05 | Akses Tanpa Login | Halaman e-catalog dapat diakses oleh publik/klien tanpa perlu melakukan autentikasi terlebih dahulu (guest access). |

**A.2 Alur & Validasi Form Pengajuan Peminjaman**

| ID | Kebutuhan Fungsional | Deskripsi |
|---|---|---|
| FR-A-06 | Autentikasi Sebelum Pengajuan | Klien wajib login (melalui Laravel Breeze) sebelum dapat mengakses form pengajuan peminjaman. |
| FR-A-07 | Form Pengajuan Peminjaman | Form berisi:<br>1. **Pemilihan Jenis Klien / Identitas:** Pilihan berupa radio button/dropdown dengan opsi **[Mahasiswa Unwahas]** atau **[Masyarakat / Klien Umum]**.<br>2. **Data & Identitas Peminjam (adaptif terhadap jenis klien yang dipilih):**<br>&nbsp;&nbsp;&nbsp;&nbsp;- Jika **Mahasiswa Unwahas**: Nama Lengkap, No. HP/WhatsApp yang bisa dihubungi, **Nomor Identitas = NIM**, dan **Asal = Program Studi / Fakultas**.<br>&nbsp;&nbsp;&nbsp;&nbsp;- Jika **Klien Umum**: Nama Lengkap, No. HP/WhatsApp yang bisa dihubungi, **Nomor Identitas = NIK (Nomor Induk Kependudukan/KTP)**, dan **Asal = Nama Instansi / Perusahaan / Alamat Domisili**.<br>3. **Unggah Dokumen Identitas (adaptif):** Komponen unggah file dinamis yang menyesuaikan jenis klien — unggah **KTM (Kartu Tanda Mahasiswa)** untuk Mahasiswa Unwahas atau unggah **KTP** untuk Klien Umum.<br>4. **Detail Peminjaman:** Pilihan alat (dapat lebih dari satu item/multi-item), jumlah kuantitas yang diajukan per alat, rentang tanggal peminjaman (tanggal mulai s.d. tanggal pengembalian), serta keperluan/keterangan peminjaman. |
| FR-A-08 | Validasi Rentang Tanggal | Sistem melakukan validasi agar tanggal pengembalian tidak boleh mendahului tanggal peminjaman, serta membatasi peminjaman tidak melebihi durasi maksimum yang ditentukan admin (contoh: maksimum 7 hari). |
| FR-A-09 | Validasi Ketersediaan Stok | Sistem memvalidasi bahwa jumlah alat yang diajukan tidak melebihi jumlah stok fisik yang tersedia pada tanggal pengajuan. |
| FR-A-10 | Validasi Unggah Dokumen Identitas (KTM / KTP) | Sistem memvalidasi keberadaan berkas identitas sesuai jenis klien yang dipilih (KTM bagi Mahasiswa Unwahas, KTP bagi Klien Umum), format file (JPG/JPEG, PNG, PDF), dan batas ukuran maksimum berkas (maks. 2 MB) sebagai syarat verifikasi identitas peminjam. |
| FR-A-11 | Riwayat & Status Pengajuan | Klien dapat melihat daftar riwayat pengajuan peminjaman miliknya beserta status terkini (Menunggu, Disetujui, Ditolak, Sedang Dipinjam, Dikembalikan, Terlambat). |
| FR-A-12 | Notifikasi Status (In-App) | Sistem menampilkan notifikasi/perubahan status pada halaman riwayat peminjaman klien setiap kali admin memperbarui status tiket. |

### 3.2 Modul B — Backend / Admin Dashboard (Inventory & Approval)

**B.1 Manajemen Master Data**

| ID | Kebutuhan Fungsional | Deskripsi |
|---|---|---|
| FR-B-01 | CRUD Kategori Alat | Admin dapat menambah, melihat, mengubah, dan menghapus data kategori alat secara manual melalui antarmuka kustom (tanpa Filament). |
| FR-B-02 | CRUD Data Alat | Admin dapat menambah, melihat, mengubah, dan menghapus data master alat, meliputi: nama, kategori, kode inventaris, deskripsi, spesifikasi, gambar, dan jumlah stok fisik total. |
| FR-B-03 | Manajemen Stok Manual | Admin dapat melakukan penyesuaian (adjustment) stok secara manual di luar transaksi peminjaman, misalnya untuk mencatat alat baru, alat rusak, atau alat hilang. |

**B.2 Sistem Verifikasi Tiket Peminjaman (Approval)**

| ID | Kebutuhan Fungsional | Deskripsi |
|---|---|---|
| FR-B-04 | Daftar Tiket Masuk | Admin dapat melihat daftar seluruh tiket pengajuan peminjaman yang berstatus PENDING. Antarmuka peninjauan tiket harus **secara jelas menampilkan jenis klien** (Mahasiswa Unwahas / Klien Umum) beserta **dokumen identitas yang dilampirkan (KTM atau KTP)**, disertai profil pengaju (nama, nomor identitas NIM/NIK, asal: Program Studi/Fakultas atau Nama Instansi/Perusahaan/Alamat Domisili), keperluan, dan pratinjau berkas identitas yang diunggah. |
| FR-B-05 | Aksi Persetujuan/Penolakan | Admin dapat menyetujui (approve) atau menolak (reject) tiket pengajuan berdasarkan hasil verifikasi data dan dokumen identitas, disertai kolom catatan/alasan (khusus penolakan). |
| FR-B-06 | Konfirmasi Serah Terima Alat | Admin dapat mengubah status tiket dari APPROVED menjadi ON_LOAN pada saat alat secara fisik diserahkan kepada klien di laboratorium. |
| FR-B-07 | Konfirmasi Pengembalian Alat | Admin dapat mengubah status tiket dari ON_LOAN (atau OVERDUE) menjadi RETURNED pada saat alat secara fisik dikembalikan oleh klien dan diverifikasi kondisinya oleh admin. |
| FR-B-08 | Deteksi Alat Terlambat (Overdue) | Sistem secara otomatis menandai tiket dengan status ON_LOAN sebagai OVERDUE apabila tanggal saat ini telah melewati tanggal jatuh tempo pengembalian. |
| FR-B-09 | Laporan & Riwayat Transaksi | Admin dapat melihat dan menyaring riwayat seluruh transaksi peminjaman berdasarkan rentang tanggal, status, kategori peminjam, atau nama alat. |

**B.3 State Machine Status Tiket Peminjaman**

Inti dari modul backend adalah mekanisme *state machine* yang mengatur transisi status tiket peminjaman sekaligus efeknya terhadap kuantitas stok fisik alat. **Alur transisi status ini berlaku sama untuk semua jenis klien** (Mahasiswa Unwahas maupun Klien Umum). Alur transisi status dirancang sebagai berikut:

```
PENDING → APPROVED → ON_LOAN → RETURNED
   │                     │
   └──→ REJECTED         └──→ OVERDUE → RETURNED
```

| ID | Status | Pemicu Transisi | Efek Terhadap Stok Fisik |
|---|---|---|---|
| FR-B-10 | PENDING | Klien mengirimkan form pengajuan peminjaman | Tidak ada perubahan stok (stok masih berstatus "dipesan sementara"/reserved secara logis, namun kuantitas fisik belum dikurangi) |
| FR-B-11 | APPROVED | Admin menyetujui tiket PENDING | Tidak ada perubahan stok fisik (alat belum diserahterimakan) |
| FR-B-12 | REJECTED | Admin menolak tiket PENDING | Tidak ada perubahan stok |
| FR-B-13 | ON_LOAN | Admin mengonfirmasi serah terima alat kepada klien | **Stok fisik alat dikurangi** sejumlah kuantitas yang disetujui |
| FR-B-14 | OVERDUE | Sistem mendeteksi tanggal jatuh tempo terlewati pada tiket berstatus ON_LOAN | Tidak ada perubahan stok (alat masih tercatat sebagai belum kembali) |
| FR-B-15 | RETURNED | Admin mengonfirmasi alat telah dikembalikan secara fisik oleh klien (dari status ON_LOAN maupun OVERDUE) | **Stok fisik alat ditambahkan kembali** sejumlah kuantitas yang dikembalikan |

> **Catatan teknis (untuk Zacky/Backend):**
> 1. Implementasi transisi status dan perubahan stok direkomendasikan menggunakan Eloquent Model Events/Observer pada model Peminjaman (Loan), dengan setiap perubahan status dibungkus dalam DB Transaction agar konsistensi data antara tabel tiket dan tabel stok alat tetap terjaga (atomicity).
> 2. Kolom penyimpanan identitas pada database bersifat **generik**: `identity_card_path` pada tabel `loan_requests` mampu menampung berkas identitas baik dari KTM maupun KTP, dan `identity_number` pada tabel `users` mampu menampung NIM maupun NIK — sehingga **tidak diperlukan migration/perubahan skema database baru** (Strict Constraint CR-01).
> 3. Jenis klien (Mahasiswa Unwahas / Klien Umum) merupakan atribut data pada level profil/pengajuan; pada tingkat backend, role otentikasi **tetap terbagi secara biner (Admin vs Non-Admin/Klien)** tanpa penambahan role autentikasi baru.

---

**B.4 Aturan Bisnis Tambahan & Penanganan Kondisi Khusus (Edge Cases)***

***1. Mekanisme Reservasi Stok (Mencegah Double Approval)***

Permasalahan: jika pengurangan stok baru terjadi saat status berubah menjadi ON_LOAN, maka dua tiket berbeda untuk alat yang sama berpotensi sama-sama disetujui (APPROVED) melebihi stok fisik yang tersedia, karena pada tahap APPROVED sistem belum mengunci kuantitas tersebut.

Aturan yang ditetapkan:

| ID | Aturan | Deskripsi |
|---|---|---|
| FR-B-16 | Reservasi Stok saat APPROVED | Saat admin menyetujui tiket (PENDING → APPROVED), sistem **langsung mengurangi stok tersedia (available_stock)**, bukan menunggu ON_LOAN. Kolom stok fisik total tetap utuh; yang dikurangi adalah stok yang "dapat diajukan" oleh klien lain. |
| FR-B-17 | Pemisahan Stok Fisik vs Stok Tersedia | Tabel alat menyimpan dua nilai: `total_stock` (jumlah fisik riil di lab) dan `available_stock` (jumlah yang boleh diajukan saat ini = total_stock dikurangi seluruh kuantitas pada tiket berstatus APPROVED/ON_LOAN/OVERDUE). |
| FR-B-18 | Validasi Concurrency saat Approval | Saat admin menekan tombol "Setujui", sistem melakukan pengecekan ulang `available_stock` di dalam DB Transaction (idealnya dengan row locking / `lockForUpdate()` pada Eloquent) sebelum benar-benar mengubah status, untuk mencegah dua admin/dua proses menyetujui tiket berbeda atas stok yang sama secara bersamaan (race condition). |
| FR-B-19 | Auto-Reject saat Stok Tidak Cukup | Jika saat admin mencoba menyetujui tiket ternyata `available_stock` sudah tidak mencukupi (karena telah dikunci tiket lain lebih dulu), sistem menolak aksi approval tersebut dan menampilkan notifikasi kepada admin bahwa stok sudah tidak mencukupi, tanpa membatalkan tiket secara otomatis — admin diberi opsi untuk menolak (REJECTED) atau menunggu. |

> Catatan: dengan aturan ini, pengurangan stok yang dijelaskan di FR-B-13 (transisi ke ON_LOAN) direvisi — pengurangan `available_stock` sudah terjadi sejak APPROVED, sedangkan transisi ke ON_LOAN hanya menandai bahwa alat **secara fisik sudah berpindah tangan**, tanpa mengubah angka stok lagi.

***2. Penanganan Persetujuan Sebagian (Partial Approval)***

Permasalahan: PRD awal mengasumsikan approval bersifat all-or-nothing, padahal klien bisa mengajukan beberapa alat/kuantitas dalam satu tiket, sementara admin mungkin hanya sanggup menyetujui sebagian.

Aturan yang ditetapkan:

| ID | Aturan | Deskripsi |
|---|---|---|
| FR-B-20 | Approval per Item, bukan per Tiket | Jika satu tiket berisi lebih dari satu jenis alat, admin dapat menyetujui, menolak, atau mengubah kuantitas **per item** di dalam tiket tersebut, bukan hanya menyetujui/menolak keseluruhan tiket. |
| FR-B-21 | Status Turunan Tiket | Status keseluruhan tiket mengikuti status item-item di dalamnya, dengan status tambahan **PARTIALLY_APPROVED** apabila sebagian item disetujui dan sebagian ditolak/diubah kuantitasnya. |
| FR-B-22 | Transparansi ke Klien | Klien dapat melihat rincian per item pada riwayat pengajuannya, termasuk item mana yang disetujui penuh, disetujui sebagian (misalnya diajukan 3, disetujui 2), atau ditolak, lengkap dengan catatan alasan dari admin. |

***3. Penanganan Alat Rusak atau Hilang saat Pengembalian***

Permasalahan: PRD awal hanya mengasumsikan dua kemungkinan hasil pengembalian, yaitu RETURNED (stok kembali utuh) — padahal secara riil, alat yang kembali bisa dalam kondisi rusak atau bahkan tidak kembali sama sekali (hilang).

Aturan yang ditetapkan:

| ID | Aturan | Deskripsi |
|---|---|---|
| FR-B-23 | Pencatatan Kondisi saat Pengembalian | Saat admin mengonfirmasi pengembalian, sistem mewajibkan admin memilih kondisi alat: **Baik**, **Rusak**, atau **Hilang**, sebelum status tiket dapat diubah menjadi RETURNED. |
| FR-B-24 | Efek terhadap Stok — Kondisi Baik | Jika kondisi "Baik", `available_stock` dan `total_stock` bertambah kembali sesuai kuantitas yang dipinjam (sesuai aturan awal). |
| FR-B-25 | Efek terhadap Stok — Kondisi Rusak | Jika kondisi "Rusak", `total_stock` **tidak bertambah** (alat dikeluarkan dari peredaran/available), namun tetap tercatat sebagai aset fisik dengan status terpisah (misalnya field `condition_status = damaged` pada level unit/log alat), sehingga tidak otomatis bisa dipinjam lagi sebelum diverifikasi/diperbaiki secara manual oleh admin. |
| FR-B-26 | Efek terhadap Stok — Kondisi Hilang | Jika kondisi "Hilang", baik `available_stock` maupun `total_stock` **dikurangi permanen** sejumlah unit yang hilang, dan sistem mencatat riwayat kehilangan tersebut sebagai log terpisah untuk kebutuhan audit/pelaporan admin (tanpa mekanisme denda finansial, sesuai batasan masalah pada BAB 6). |
| FR-B-27 | Status Tiket untuk Kasus Rusak/Hilang | Status tiket tetap berakhir di RETURNED (alat dianggap selesai diproses secara administratif), namun disertai flag/keterangan kondisi akhir (Baik/Rusak/Hilang) yang tercatat permanen pada riwayat transaksi, agar dapat dibedakan dari pengembalian normal saat pelaporan. |

***4. Penghapusan Data Master Alat yang Memiliki Riwayat Transaksi***

Permasalahan: PRD awal belum mengatur apa yang terjadi bila admin mencoba menghapus data alat pada master data, padahal alat tersebut sudah memiliki riwayat transaksi peminjaman (integritas data historis berisiko rusak jika dihapus permanen).

Aturan yang ditetapkan:

| ID | Aturan | Deskripsi |
|---|---|---|
| FR-B-28 | Soft Delete untuk Data Alat | Penghapusan data alat tidak dilakukan secara permanen (hard delete), melainkan menggunakan mekanisme **soft delete** (Laravel `SoftDeletes` trait) sehingga data tetap tersimpan di database namun tidak lagi ditampilkan pada e-catalog maupun pilihan form pengajuan baru. |
| FR-B-29 | Larangan Hard Delete jika Memiliki Riwayat | Sistem mencegah admin melakukan penghapusan permanen (force delete) pada alat yang memiliki relasi ke tabel transaksi peminjaman mana pun, guna menjaga integritas riwayat/audit trail. |
| FR-B-30 | Riwayat Tetap Utuh setelah Soft Delete | Tiket peminjaman lama yang mereferensikan alat yang sudah di-soft-delete tetap dapat ditampilkan secara utuh pada riwayat transaksi (dengan penanda "Alat sudah tidak aktif/dihapus dari katalog"), tanpa menghilangkan data historisnya. |
| FR-B-31 | Opsi Nonaktifkan (Alternatif Delete) | Sebagai alternatif yang lebih disarankan bagi admin, sistem menyediakan opsi **"Nonaktifkan Alat"** (mengubah status is_active menjadi false) yang secara fungsional setara dengan soft delete dari sisi tampilan katalog, namun secara eksplisit menggambarkan maksud "alat sedang tidak dioperasikan" dibanding kesan "dihapus". |

> **Catatan revisi diagram state machine (B.3):** Dengan aturan FR-B-16 s.d. FR-B-31 di atas, pengurangan stok terjadi **sejak status APPROVED** (bukan ON_LOAN), dan status RETURNED kini bercabang tiga kemungkinan efek stok (Baik/Rusak/Hilang) alih-alih satu efek tunggal "stok bertambah".

---

## 4. KEBUTUHAN NON-FUNGSIONAL (NON-FUNCTIONAL REQUIREMENTS)

### 4.1 Spesifikasi Perangkat Lunak (Software Requirements)

| Komponen | Spesifikasi |
|---|---|
| Bahasa Pemrograman | PHP 8.4.24 |
| Framework Backend | Laravel 13.29.0 |
| Autentikasi | Laravel Breeze (Blade Stack) |
| Framework CSS | Tailwind CSS |
| Library JavaScript | Alpine.js (untuk interaktivitas ringan sisi klien) |
| Database | SQLite |
| Web Server (Lingkungan Pengembangan) | Nginx (via Laravel Herd) |
| Sistem Operasi Server (Deployment, opsional) | Windows / Linux (Ubuntu Server) dengan Nginx / Apache Web Server |
| Version Control | Git & GitHub |
| Desain Antarmuka | Figma (tahap perancangan UI/UX) |

### 4.2 Spesifikasi Perangkat Keras (Minimum, untuk Lingkungan Pengembangan)

| Komponen | Spesifikasi Minimum |
|---|---|
| Prosesor | Setara Intel Core i3 Gen 8 / AMD Ryzen 3 ke atas |
| RAM | 4 GB (disarankan 8 GB untuk menjalankan Herd, browser, dan code editor secara bersamaan) |
| Penyimpanan | SSD dengan ruang kosong minimal 10 GB |
| Sistem Operasi | Windows 10/11, macOS, atau Linux |
| Koneksi Internet | Stabil, untuk kebutuhan dependency management (Composer & NPM) dan kolaborasi tim |

### 4.3 Keamanan (Security)

| ID | Kebutuhan Non-Fungsional | Deskripsi |
|---|---|---|
| NFR-S-01 | Autentikasi Berbasis Role | Sistem menerapkan pemisahan akses (role-based access control) antara role Admin/Teknisi dan role Klien menggunakan mekanisme autentikasi bawaan Laravel Breeze, dikombinasikan dengan Middleware dan Gate/Policy kustom. Pada tingkat backend, role otentikasi **tetap terbagi secara biner (Admin vs Non-Admin/Klien)**; sub-kategori klien (Civitas Akademika: Mahasiswa/Dosen Unwahas, atau Klien Umum) merupakan atribut data pada level profil/pengajuan, bukan role autentikasi terpisah. |
| NFR-S-02 | Proteksi CSRF | Seluruh form (login, registrasi, pengajuan peminjaman, CRUD admin) dilindungi dengan CSRF token bawaan Laravel. |
| NFR-S-03 | Validasi Input Server-Side | Seluruh input pengguna divalidasi di sisi server menggunakan Laravel Form Request/Validator, tidak hanya mengandalkan validasi sisi klien. |
| NFR-S-04 | Hashing Password | Password pengguna disimpan dalam bentuk hash (bcrypt) menggunakan mekanisme bawaan Laravel. |
| NFR-S-05 | Pembatasan Akses Rute | Rute-rute pada dashboard admin dilindungi middleware autentikasi dan otorisasi, sehingga tidak dapat diakses oleh pengguna dengan role Klien maupun pengguna publik yang belum login. |
| NFR-S-06 | Validasi Berkas Unggahan | Berkas dokumen identitas (KTM bagi Mahasiswa Unwahas atau KTP bagi Klien Umum) yang diunggah divalidasi jenis MIME type (image/jpeg, image/png, application/pdf) dan batas ukuran berkas maksimum (maksimal 2 MB) di sisi server untuk mencegah unggahan berkas berbahaya. |

### 4.4 Kebutuhan Non-Fungsional Lainnya

| ID | Kategori | Deskripsi |
|---|---|---|
| NFR-U-01 | Usability | Antarmuka dirancang responsif (mobile-friendly) menggunakan Tailwind CSS agar dapat diakses dengan nyaman melalui perangkat mobile maupun desktop klien. |
| NFR-P-01 | Performance | Fitur live search dan filter kategori pada e-catalog harus merespons dalam waktu kurang dari 1 detik untuk jumlah data alat skala laboratorium (± ratusan item). |
| NFR-M-01 | Maintainability | Kode program mengikuti pola arsitektur MVC standar Laravel agar mudah dipelihara dan dikembangkan lebih lanjut oleh pihak laboratorium pasca proyek KP selesai. |
| NFR-C-01 | Compatibility | Sistem dapat diakses dengan baik pada browser modern (Google Chrome, Mozilla Firefox, Microsoft Edge) versi terbaru. |

---

## 5. USER FLOW (ALUR PENGGUNA)

Berikut narasi alur pengguna (klien, baik mahasiswa maupun masyarakat umum) secara end-to-end, mulai dari melihat katalog hingga alat dikembalikan dan stok bertambah kembali.

**Langkah 1 — Eksplorasi E-Catalog (Guest)**
Klien mengakses halaman utama sistem tanpa perlu login. Klien menggunakan fitur live search dan/atau filter kategori untuk menemukan alat yang dibutuhkan, misalnya "Jangka Sorong Digital".

**Langkah 2 — Melihat Detail Spesifikasi**
Klien mengklik kartu alat yang diminati. Sistem menampilkan modal berisi detail spesifikasi alat beserta status ketersediaan stok saat itu (contoh: "Tersedia — 3 unit").

**Langkah 3 — Login/Registrasi**
Karena tertarik untuk meminjam, klien mengklik tombol "Ajukan Peminjaman". Sistem mengarahkan klien ke halaman login (atau registrasi apabila belum memiliki akun) menggunakan Laravel Breeze.

**Langkah 4 — Mengisi Form Pengajuan Peminjaman**
Setelah berhasil login, klien diarahkan ke form pengajuan peminjaman. Klien memilih alat dan jumlah yang ingin dipinjam, menentukan rentang tanggal peminjaman, mengisi keperluan peminjaman, memilih **Jenis Klien / Identitas** ([Mahasiswa Unwahas] atau [Masyarakat / Klien Umum]), mengisi data identitas yang menyesuaikan pilihan tersebut (**NIM** + Program Studi/Fakultas bagi Mahasiswa Unwahas, atau **NIK** + Nama Instansi/Perusahaan/Alamat Domisili bagi Klien Umum), serta mengunggah dokumen identitas yang bersesuaian (**KTM** bagi civitas akademika Unwahas, atau **KTP** bagi klien umum).

**Langkah 5 — Validasi & Pengiriman Tiket**
Sistem memvalidasi seluruh isian form (kelengkapan data peminjam, rentang tanggal, ketersediaan stok, kesesuaian jenis identitas, serta format dan ukuran berkas dokumen identitas). Apabila valid, sistem membuat tiket peminjaman baru dengan status **PENDING** dan menampilkan konfirmasi pengajuan kepada klien.

**Langkah 6 — Verifikasi oleh Admin/Teknisi**
Admin laboratorium menerima tiket PENDING pada dashboard admin, memeriksa kesesuaian data peminjam, jenis klien, serta keabsahan dokumen identitas (KTM atau KTP) yang diunggah. Berdasarkan hasil pemeriksaan, admin memutuskan untuk **menyetujui (APPROVED)** atau **menolak (REJECTED)** tiket tersebut. Klien dapat memantau perubahan status ini secara transparan melalui halaman riwayat peminjamannya.

**Langkah 7 — Serah Terima Alat Secara Fisik**
Pada tanggal peminjaman yang disepakati, klien datang langsung ke laboratorium untuk mengambil alat secara fisik. Admin mengonfirmasi serah terima ini pada sistem, sehingga status tiket berubah dari APPROVED menjadi **ON_LOAN**, dan sistem secara otomatis mencatat alat telah aktif berpindah tangan ke klien.

**Langkah 8 — Pemantauan Selama Masa Peminjaman**
Selama alat dipinjam, sistem memantau tanggal jatuh tempo pengembalian. Apabila klien belum mengembalikan alat hingga melewati tanggal tersebut, sistem secara otomatis mengubah status tiket menjadi **OVERDUE**, yang akan tampil sebagai peringatan pada dashboard admin.

**Langkah 9 — Pengembalian Alat**
Klien mengembalikan alat secara fisik ke laboratorium. Admin memeriksa kondisi fisik alat (Baik/Rusak/Hilang) dan mengonfirmasi pengembalian pada sistem, sehingga status tiket berubah menjadi **RETURNED**.

**Langkah 10 — Pemulihan Stok**
Begitu status tiket dikonfirmasi RETURNED, sistem secara otomatis memperbarui jumlah stok alat sesuai kondisi pengembalian fisik, sehingga alat kembali muncul sebagai tersedia pada e-catalog dan dapat diajukan peminjamannya kembali oleh klien lain.

---

## 6. BATASAN MASALAH (SCOPE & LIMITATIONS)

Mengingat proyek dikerjakan oleh 2 (dua) orang mahasiswa magang dalam jangka waktu terbatas (2 bulan) dengan metodologi Waterfall, ruang lingkup sistem dibatasi secara tegas sebagai berikut agar target penyelesaian tetap realistis dan terhindar dari *over-engineering*:

1. **Tidak ada modul denda/pembayaran elektronik.** Sistem tidak menangani perhitungan denda keterlambatan dalam bentuk uang maupun integrasi payment gateway. Penanganan keterlambatan (OVERDUE) bersifat administratif/informatif bagi admin, bukan finansial. Layanan yang kini terbuka bagi klien eksternal pun **tidak melibatkan transaksi finansial daring apa pun** — pembayaran kompensasi/sewa alat (jika ada) disepakati dan diproses secara manual di luar sistem.
2. **Tidak menggunakan arsitektur microservices.** Sistem dibangun sebagai aplikasi monolitik berbasis Laravel (MVC) sesuai dengan skala kebutuhan laboratorium tunggal, tanpa pemisahan layanan (services) yang terdistribusi.
3. **Tidak menggunakan package admin panel pihak ketiga (Filament, Nova, dll).** Seluruh antarmuka admin dan klien dibangun secara manual menggunakan Blade, Tailwind CSS, dan Alpine.js sebagai bagian dari capaian pembelajaran tim.
4. **Tidak ada notifikasi real-time melalui email/WhatsApp/push notification.** Pembaruan status peminjaman cukup ditampilkan melalui halaman riwayat pada aplikasi (in-app notification/status update), tanpa integrasi layanan pihak ketiga untuk pengiriman notifikasi eksternal.
5. **Tidak ada fitur multi-cabang/multi-laboratorium.** Sistem dirancang khusus untuk satu unit, yaitu Laboratorium Teknik Mesin Unwahas, bukan sistem generik lintas laboratorium/fakultas.
6. **Tidak menggunakan sistem antrean (queue) atau job scheduler kompleks di luar kebutuhan dasar.** Deteksi status OVERDUE cukup diimplementasikan melalui scheduled task/command Artisan sederhana, tanpa memerlukan message broker eksternal (seperti Redis Queue/RabbitMQ).
7. **Basis data menggunakan SQLite**, bukan basis data terdistribusi atau berskala enterprise (seperti PostgreSQL cluster), karena volume data dan jumlah pengguna bersifat skala laboratorium kampus, bukan skala industri.
8. **Pengambilan dan pengembalian alat fisik wajib dilakukan secara langsung (on-site) di Laboratorium Teknik Mesin Unwahas.** Meskipun layanan peminjaman terbuka bagi civitas akademika Unwahas maupun masyarakat umum (klien non-akademik), sistem tidak menyediakan fitur logistik pengiriman alat ke luar kampus (tidak ada external delivery / jasa ekspedisi) maupun peminjaman lintas cabang laboratorium.
9. **Verifikasi dokumen identitas peminjam (KTM bagi mahasiswa atau KTP bagi masyarakat umum) dilakukan secara manual oleh admin** melalui pemeriksaan visual berkas yang diunggah pada dashboard, tanpa integrasi OCR (Optical Character Recognition) maupun sistem verifikasi identitas kependudukan/kepegawaian otomatis (seperti Dukcapil atau PDDIKTI API).
10. **Pengujian sistem dilakukan pada skala terbatas (User Acceptance Testing internal)**, tidak mencakup pengujian beban (load testing) skala besar, mengingat jumlah pengguna aktif diperkirakan tidak melebihi skala ratusan pengguna/klien aktif per semester.

---

*Dokumen ini disusun sebagai bagian dari perencanaan sistem pada tahap Analisa Kebutuhan (Requirement Analysis) dalam metodologi Waterfall — Telah disesuaikan berdasarkan Change Request (CR-01: Perluasan Cakupan Klien & Dokumen Identitas).*