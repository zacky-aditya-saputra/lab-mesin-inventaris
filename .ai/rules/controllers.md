---
paths:
  - app/Http/Controllers/ProfileController.php
---

# Controllers

## users.identity_type NOT NULL; staff terkunci sebagai pegawai
Kolom users.identity_type adalah NOT NULL dengan default 'mahasiswa'. Jangan mengisi identity_type dengan null saat update profil — hanya set jika request menyediakannya. Untuk user laboran/kepala_lab, controller memaksa identity_type = 'pegawai' dan mengecualikan role serta laboratory_id dari mass assignment (lab tugas hanya boleh diubah Kepala Lab via admin/laborans).
