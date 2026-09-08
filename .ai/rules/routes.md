---
paths:
  - 'routes/**/*.php'
---

# Routes

## Admin loan routes use admin.loans.* names
In routes/web.php, all admin routes (dashboard, categories.*, tools.*, loans.index, loans.review, loans.update-status, loans.return) live in the single group with prefix('admin') and name('admin.'), so final names are admin.loans.*. Student routes keep loans.create/store/history/show in the shared loans namespace. Never register admin loan routes with bare loans.* names.

## Import the admin LoanController with an alias
routes/web.php imports both App\Http\Controllers\LoanController (student) and App\Http\Controllers\Admin\LoanController, whose short names collide. Import the admin one as `use App\Http\Controllers\Admin\LoanController as AdminLoanController;` and reference AdminLoanController::class in the admin group. Importing both under the bare LoanController name is a fatal error.
