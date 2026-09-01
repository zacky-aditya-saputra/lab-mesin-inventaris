---
paths:
  - 'routes/**/*.php'
---

# Routes

## Admin loan routes use loans.* names without admin. prefix
In routes/web.php, admin verification/approval routes use final route names loans.index, loans.review, loans.update-status, and loans.return (NOT admin.loans.*). They live in a separate group with prefix('admin') and middleware ['auth', 'role:admin'] but WITHOUT the ->name('admin.') prefix. Dashboard, categories.*, and tools.* remain inside the admin. name-prefixed group. Keep student loans.create/store/history/show and these admin loans.* in the shared loans namespace.
