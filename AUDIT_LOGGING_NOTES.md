# Audit Logging Implementation Notes — Revision-11

## Scope in this change
- Reuses the existing `audit_logs` table, `AuditLog` model, Audit Log page, filters, and CSV export.
- Adds a reusable `App\Services\AuditLogger` that records authenticated administrator ID, action, entity, target ID, and JSON snapshots using the existing `old_value` / `new_value` columns.
- Student creation via Input Data form and Student CSV import is logged.
- Student updates record old/new student fields and semester GPA rows in the same database transaction as the update.
- Student deletion records the student's identifying/academic fields before deletion.
- Grade create/update through the Input Data Grades form and grade CSV import is logged with before/after grade values.
- Grade rows removed when saving a Grades form are logged as deletes.
- Audit snapshots deliberately exclude credentials and are limited to explicit academic fields.

## Files changed
- `app/Services/AuditLogger.php` (new)
- `app/Http/Controllers/InputDataController.php`
- `app/Http/Controllers/StudentController.php`
- `app/Services/InputDataImporter.php`

## Existing database structure
The existing migration `2026_09_14_000318_create_audit_logs_table.php` already defines `admin_id`, `action_type`, `target_entity`, `target_id`, `old_value`, and `new_value`; no migration is required for this scope.

## Verification performed
- PHP syntax checks passed for `AuditLogger.php`, `StudentController.php`, `InputDataController.php`, and `InputDataImporter.php`.
- End-to-end tests against a running Laravel database have not been executed in this environment. Test create/update/delete students, save grades, import student/grade CSVs, and inspect `/audit-logs` after applying this ZIP.

## Known scope boundary
This is phase C: student and grade operations only. Other entities (batch, period, lecturer, course, class, course record) are not yet instrumented. Logging is skipped if there is no authenticated admin ID; normal application routes are already inside the `auth` middleware.
