---
paths:
  - 'database/seeders/**'
---

# Seeders

## Seeder harus forceDelete tools (soft delete + FK restrict)
Tool menggunakan SoftDeletes dan tools.category_id FK-nya restrictOnDelete, jadi `Tool::query()->delete()` hanya soft-delete dan memblokir penghapusan categories di DatabaseSeeder. Gunakan `Tool::withTrashed()->forceDelete()` sebelum menghapus categories.
