# Barefoot Martial Arts — Academy ERP

Laravel 9 application for managing a multi-branch Taekwondo academy: students, attendance, belts, fees, inventory, branches, events, parent portal, and head-office reporting.

## Documentation

| Guide | Who should read it |
|-------|---------------------|
| **[HOW-TO-USE.md](HOW-TO-USE.md)** | **Everyone at the academy** — coaches, office, accountants, managers, parents. Plain language, step by step. **Print this one.** |
| **[FEATURES.md](FEATURES.md)** | IT staff and developers only |

Share **HOW-TO-USE.md** with anyone who is not technical.

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_* in .env
php artisan migrate
php artisan storage:link
php artisan serve
```

Visit `http://127.0.0.1:8000` and log in at `/login`.

## Main areas

| Area | URL | Description |
|------|-----|-------------|
| Public site | `/`, `/branches`, `/register-online` | Marketing & signups |
| ERP | `/erp` | Branch operations (staff) |
| Head office | `/erp/hq` | Super admin dashboard |
| Parent portal | `/parent` | Guardians (linked students) |
| Legacy admin | `/admin/home` | Notices & old player admin |

## Roles

`super_admin`, `branch_admin`, `accountant`, `coach`, `staff`, `parent` — see [FEATURES.md §2](FEATURES.md#2-user-roles--permissions).

## Tech stack

- Laravel 9, PHP 8+
- MySQL
- Bootstrap 5 (admin UI)
- DomPDF (invoices, receipts, ID cards, certificates)
- Optional: Chart.js (HQ dashboard)

## Scheduled tasks

```bash
php artisan schedule:run   # via cron every minute
```

- Daily fee reminder emails (`academy:send-fee-reminders`)
- Daily/weekly JSON backups (`academy:backup`)

Details in [FEATURES.md §23](FEATURES.md#23-automated-tasks-cron).

## License

Application code: project-specific. Laravel framework: [MIT](https://opensource.org/licenses/MIT).
