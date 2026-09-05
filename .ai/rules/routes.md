---
paths:
  - 'routes/**/*.php'
---

# Routes

## Admin loan routes use admin.loans.* names
In routes/web.php, all admin routes (dashboard, categories.*, tools.*, loans.index, loans.review, loans.update-status, loans.return) live in the single group with prefix('admin') and name('admin.'), so final names are admin.loans.*. Student routes keep loans.create/store/history/show in the shared loans namespace. Never register admin loan routes with bare loans.* names.
