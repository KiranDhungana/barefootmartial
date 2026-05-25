# Barefoot Martial Arts — Feature Guide (Technical)

> **For branch staff and parents (non-technical):** use **[HOW-TO-USE.md](HOW-TO-USE.md)** — plain language, no code, no IT words.

This document explains **every feature** in the academy ERP: what it does, who can use it, URLs, and configuration for IT staff and developers.

**Base URL examples** (local): `http://127.0.0.1:8000`  
**ERP prefix:** `/erp`  
**Login:** `/login`

---

## Table of contents

1. [Getting started](#1-getting-started)
2. [User roles & permissions](#2-user-roles--permissions)
3. [Public website](#3-public-website)
4. [Parent portal](#4-parent-portal)
5. [ERP dashboard](#5-erp-dashboard)
6. [Students](#6-students)
7. [Attendance & QR](#7-attendance--qr)
8. [Belts & certificates](#8-belts--certificates)
9. [Invoices & payments](#9-invoices--payments)
10. [Fee tracking & reminders](#10-fee-tracking--reminders)
11. [Inventory & uniform sales](#11-inventory--uniform-sales)
12. [Branches & schedules](#12-branches--schedules)
13. [Events & tournaments](#13-events--tournaments)
14. [Online registrations](#14-online-registrations)
15. [Expenses](#15-expenses)
16. [Reports & compliance](#16-reports--compliance)
17. [Notifications](#17-notifications)
18. [Head office (HQ) dashboard](#18-head-office-hq-dashboard)
19. [Audit log](#19-audit-log)
20. [ERP users, trainers & salary](#20-erp-users-trainers--salary)
21. [Two-factor authentication](#21-two-factor-authentication)
22. [Legacy admin](#22-legacy-admin)
23. [Automated tasks (cron)](#23-automated-tasks-cron)
24. [Configuration (.env)](#24-configuration-env)
25. [Not included](#25-not-included)

---

## 1. Getting started

### Requirements

- PHP 8.0+ (8.1+ recommended)
- Composer
- MySQL / MariaDB
- Node.js (optional, for front-end assets)

### Install & run

```bash
composer install
cp .env.example .env
php artisan key:generate
# Edit .env with database credentials
php artisan migrate
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`.

### First login

Create a user in the database or use **Legacy admin** (`/admin/reg`) if you have a super-admin flag (`is_admin = 1`). For ERP, assign a `role` such as `super_admin` and optionally `branch_id`.

Super admins are redirected to **Head office** (`/erp/hq`). Other staff go to **Branch console** (`/erp`).

---

## 2. User roles & permissions

| Role | ERP access | Branch scope | Finance | Notes |
|------|------------|--------------|---------|--------|
| **super_admin** | Yes | All branches | Yes | HQ dashboard, create branches, ERP users, trainers, salary |
| **branch_admin** | Yes | Own branch only | Yes | Mark official registration, import students, audit log |
| **accountant** | Yes | Own branch only | Yes | Invoices, fees, inventory, reports |
| **coach** | Yes | Own branch only | No | Students, attendance, belts |
| **staff** | Yes | Own branch only | No | General branch operations |
| **parent** | No | — | View child fees only | Parent portal only |
| **player** | No | — | — | Legacy site player (if used) |

**Branch scoping:** Users with a `branch_id` (except super admin) only see students, invoices, and attendance for that branch.

---

## 3. Public website

No login required.

| Feature | URL | How to use |
|---------|-----|------------|
| Home | `/` | Marketing / welcome page |
| Branches | `/branches` | Lists active branches (name, address, phone, member count) |
| Online registration | `/register-online` | Parent fills form → creates a **pending** request in ERP |
| Events | `/events` | Published events/tournaments from ERP |
| Coaches | `/coaches` | Static info page |
| Notices | `/notices` | Latest notices (same data as legacy notice system) |
| About | `/about-us` | About page |
| Gallery | `/gallary` | Gallery page |
| Contact | `/contact` | Contact form (email) |
| Notices (legacy list) | `/notice-home` | Older notice browsing UI |

**Managing public content**

- **Branches / events / schedules:** ERP (see sections below). Toggle branch `is_active` and event `is_published`.
- **Notices:** Legacy admin → Add notice / Delete notice (super admin).

---

## 4. Parent portal

| URL | `/parent` (after login) |
|-----|-------------------------|
| **Who** | Users with role `parent` linked to one or more students |

### Create a parent account (staff)

1. Open a student in ERP → **Profile** tab → **Create parent login**.
2. Enter parent name, email, and password.
3. Share login URL: `/login`.

### What parents see

- Dropdown to switch between linked children
- Child profile summary (branch, belt, status, coach)
- **Attendance** percentage for the current month
- **Fee invoices** (number, due date, status, balance)
- Recent **notices**

Parents cannot edit data or pay online in the system.

---

## 5. ERP dashboard

| URL | `/erp` |
|-----|--------|
| **Who** | All ERP roles |

Branch-level overview: student counts, quick links, and alerts. Super admins use **Head office** for organization-wide metrics (see [§18](#18-head-office-hq-dashboard)).

---

## 6. Students

| URL | `/erp/students` |
|-----|-----------------|
| **Who** | All ERP roles (branch-scoped) |

### Registration workflow

1. **Add student** → saved as **pending** with code prefix `PRE-` (configurable).
2. Fill required fields: name, branch, join date, belt rank (and other profile fields).
3. When complete, **Mark official registration** → code becomes `BFN-` (or `ACADEMY_MEMBERSHIP_PREFIX`) and status is **official**.
4. Only **official** students can: attendance, billing, belt promotion, ID card PDF, QR check-in.

### Student profile tabs

| Tab | Content |
|-----|---------|
| **Profile** | Photo, contact, parent, QR links, actions |
| **Attendance** | Monthly % and recent records |
| **Fees** | Invoices, payment slip link (finance roles) |
| **Uniforms** | Uniform/inventory line items from invoices |
| **Certificates** | Belt promotion history + certificate PDF links |

### Actions (profile)

| Action | Description |
|--------|-------------|
| Edit | Update profile fields, photo, scholarship, uniform status |
| Bill fees / admission | Create invoice (finance, official only) |
| Belt promotion | Open belt module for this student |
| Download ID card | PDF with photo, details, QR (official only) |
| WhatsApp reminder | Opens WhatsApp with fee reminder text |
| Create parent login | Creates `parent` user linked to this student |
| Delete | Removes student (confirm) |

### Import students

| URL | `/erp/students/import` |
|-----|-------------------------|
| **Who** | Super admin, branch admin |

- Download **CSV template**
- Upload CSV **or** add rows manually
- Imported students start as **pending**; mark official after review

### Audit

Creates and updates to students (and invoices) are logged when audit is enabled (see [§19](#19-audit-log)).

---

## 7. Attendance & QR

### Daily attendance

| URL | `/erp/attendance` |
|-----|-------------------|
| **Who** | All ERP roles |

1. Pick a **date**.
2. For each official **active** student: mark **Present**, **Late**, or **Absent** (saved per day).
3. View **monthly summary** (% present+late), **inactive** list (no check-in for X days), and **low attendance** warnings.

**Inactive threshold:** `ACADEMY_ATTENDANCE_INACTIVE_DAYS` (default 14).  
**Low attendance:** below `ACADEMY_ATTENDANCE_LOW_PERCENT` (default 40%).

### Bulk attendance

| URL | `/erp/attendance/bulk` |
|-----|------------------------|

Mark an entire class roster for one date in a single form → **Save all**.

### QR check-in

Each official student has a unique `qr_token`.

| Purpose | URL pattern |
|---------|-------------|
| **Public verify** (belt, status, branch) | `/verify/{token}` |
| **Staff check-in** (marks present today) | `/erp/attendance/scan/{token}` (must be logged into ERP) |

On student profile → **Profile** tab → QR section shows both links.

**Rules:** Pending/unofficial, inactive, or suspended students are blocked from check-in.

---

## 8. Belts & certificates

| URL | `/erp/belts` |
|-----|--------------|
| **Who** | All ERP roles |

1. Open belt list or promote from student profile.
2. **Promote** → select new belt, date, notes.
3. System checks eligibility (minimum months since last exam: `ACADEMY_BELT_MONTHS_BETWEEN`).
4. Download **certificate PDF** (`CERT-YYYY-####`) from promotion history or Certificates tab.

Belt ranks are configured in `config/academy.php` → `belt_ranks`.

---

## 9. Invoices & payments

| URL | `/erp/invoices` |
|-----|-----------------|
| **Who** | Super admin, branch admin, accountant |

### Create invoice

| URL | `/erp/invoices/create` |

1. Select **official** student.
2. Add **fee line items** (admission, monthly, uniform, belt, equipment, etc.).
3. Optionally add **inventory items** (deducts stock).
4. Set discount, scholarship **full waiver**, late fee, due date.
5. Optionally record **initial payment** on save.

### Invoice statuses

| Status | Meaning |
|--------|---------|
| pending | Nothing paid |
| partial | Some payment received |
| paid | Fully paid |
| overdue | Past due with balance (auto-updated) |

### On invoice detail page

| Action | Description |
|--------|-------------|
| Record payment | Partial or full; generates receipt number `RCP-YYYY-#####` |
| Mark paid in full | Pays remaining balance (cash) |
| Apply late fee | Adds configured or custom late fee |
| Mark pending | Clears payments, resets to pending |
| Invoice PDF | Branded PDF with logo |
| Payment slip PDF | Balance due slip for in-person payment |
| Receipt PDF | Per payment record |

**Payment methods:** cash, bank, card, other (configurable).

**Scholarship:** Full waiver sets amount to 0 and flags invoice accordingly.

> **Online card/UPI payments are not built in** — fees are recorded manually after payment at the branch.

---

## 10. Fee tracking & reminders

| URL | `/erp/fees` |
|-----|-------------|
| **Who** | Finance roles |

- Dashboard of outstanding balances by student/invoice
- Filter by status (pending, partial, overdue)

### Manual reminders

| URL | `/erp/fees/reminders` |

- List of students/invoices with open balances
- **WhatsApp** links with pre-filled reminder text (uses student or parent phone)

### Automated email reminders

Command: `php artisan academy:send-fee-reminders`  
Scheduled daily at **09:00** (see [§23](#23-automated-tasks-cron)).

Sends email when parent email exists (parent user or valid `parent_contact` email). Configure `MAIL_*` in `.env`.

---

## 11. Inventory & uniform sales

| URL | `/erp/inventory` |
|-----|------------------|
| **Who** | Finance roles |

### Stock

- View items per branch (uniforms, gloves, guards, etc.)
- **Adjust stock** (receive, issue, correction)
- **Transfer** stock between branches

### Selling via invoices

When creating an invoice, add inventory lines → stock decreases automatically.

### Uniform sales report

| URL | `/erp/inventory/report` |

- Monthly revenue from uniform fee type + inventory line items
- **Export CSV** for Excel

---

## 12. Branches & schedules

### Branches

| URL | `/erp/branches` |
|-----|-----------------|
| **View** | All ERP roles (branch users see own branch) |
| **Create / edit** | Super admin only |

Fields: name, code, address, phone, email, **active on public site**.

Branch detail shows linked **class schedules**.

### Class schedules

| URL | `/erp/schedules` |
|-----|------------------|
| **Who** | All ERP roles (branch-scoped) |

Add classes: branch, name, day of week, start/end time, coach, belt level, active flag.

Public visitors see branch info on `/branches`; schedules are managed here for staff reference (and branch detail page).

---

## 13. Events & tournaments

| URL | `/erp/events` |
|-----|-------------|
| **Who** | All ERP roles (branch-scoped) |

1. **Create event:** title, description, date, deadline, fee, branch (optional), **published** flag.
2. Published events appear on `/events`.
3. On event detail → **Register student** (official students only).
4. Track registrations: category, fee, status.

---

## 14. Online registrations

| URL | `/erp/online-registrations` |
|-----|------------------------------|
| **Who** | All ERP roles (branch-scoped) |

Workflow:

1. Visitor submits `/register-online`.
2. Request appears here as **pending**.
3. **Convert** → creates a **pending** student record pre-filled from the form.
4. Complete profile in ERP → **Mark official** when ready.

---

## 15. Expenses

| URL | `/erp/expenses` |
|-----|-----------------|
| **Who** | Finance roles |

- Record branch expenses: date, category, amount, description
- Filter by branch and month
- Categories: hall rent, equipment, travel, event, utilities, salary, other

---

## 16. Reports & compliance

### Summary reports

| URL | `/erp/reports` |
|-----|----------------|
| **Who** | Finance roles |

Organization/branch summaries with **CSV** and **PDF** export.

### Branch reports

| URL | `/erp/branch-reports` |

Daily/monthly branch performance + **PDF** export.

### Compliance dashboard

| URL | `/erp/compliance` |
|-----|-------------------|
| **Who** | Super admin, finance roles |

Per-branch scores (0–100%):

- **Registration** — % of students marked official
- **Uniform** — % of official students with uniform status documented
- **Reporting** — invoicing activity this month
- **Overall** — weighted total

Used for HQ branch rankings.

---

## 17. Notifications

| URL | `/erp/notifications` |
|-----|----------------------|
| **Who** | Finance roles |

### Send message

1. Select student.
2. Channel: **Email** (sends via mail), **SMS** or **WhatsApp** (logged only — no gateway; use fee reminders for WhatsApp links).
3. Enter subject/body → **Send / log**.

### Invoice reminders

- List of open invoices → **Send reminder** (email if available, else SMS log).

### History

Recent `notification_logs`: channel, recipient, status.

---

## 18. Head office (HQ) dashboard

| URL | `/erp/hq` |
|-----|-----------|
| **Who** | Super admin only |

Organization-wide widgets:

- Branch count, students (official / pending), new today
- Fees collected this month, outstanding & overdue counts
- Inactive students (14+ days)
- Active vs inactive status counts
- Uniform sales & belt exams this month
- **Branch rankings** (revenue, growth, attendance, compliance)
- Attendance chart (current month)
- **Growth chart** — official registrations last 6 months

Links to branch reports, compliance, expenses, belts.

---

## 19. Audit log

| URL | `/erp/audit-logs` |
|-----|-------------------|
| **Who** | Super admin, branch admin |

Read-only log of changes to **students** and **invoices** (who changed what and when).

---

## 20. ERP users, trainers & salary

### ERP users

| URL | `/erp/users` |
|-----|--------------|
| **Who** | Super admin |

Create staff logins: name, email, password, **role**, optional **branch**.

### Trainers

| URL | `/erp/trainers` |
|-----|-----------------|
| **Who** | Super admin |

CRUD trainer records for salary and reference.

### Salary

| URL | `/erp/salary` |
|-----|--------------|
| **Who** | Super admin |

Generate salary report for a period → view / **PDF** export.

---

## 21. Two-factor authentication

| URL | `/erp/account/two-factor` |
|-----|---------------------------|
| **Who** | Any logged-in user |

1. Generate secret → scan in authenticator app.
2. Confirm with code → 2FA enabled.
3. Next login: email/password → **two-factor challenge** page.

Disable from the same settings page.

---

## 22. Legacy admin

| URL | `/admin/home` |
|-----|---------------|
| **Who** | Super admin (`is_admin` / legacy check) |

Older features kept for compatibility:

| Feature | URL | Purpose |
|---------|-----|---------|
| Dashboard | `/admin/home` | Legacy dashboard |
| Add player | `/admin/reg` | Register legacy player users |
| Add notice | `/add-notice` | Upload notice (title, description, file) |
| Delete notice | `/delete-notice` | Remove notices |

ERP sidebar still shows these under **Legacy admin** for super admins.

---

## 23. Automated tasks (cron)

Register in server crontab:

```bash
* * * * * cd /path/to/barefoot && php artisan schedule:run >> /dev/null 2>&1
```

| Command | Schedule | Purpose |
|---------|----------|---------|
| `academy:send-fee-reminders` | Daily 09:00 | Email fee reminders for open invoices |
| `academy:backup` | Daily 02:00 | JSON export of core tables to `storage/app/backups` |
| `academy:backup` | Sunday 03:00 | Weekly backup (same command) |

### Manual runs

```bash
php artisan academy:send-fee-reminders
php artisan academy:send-fee-reminders --dry-run
php artisan academy:backup
```

---

## 24. Configuration (.env)

| Variable | Default | Purpose |
|----------|---------|---------|
| `ACADEMY_MEMBERSHIP_PREFIX` | `BFN` | Official student code prefix |
| `ACADEMY_DEFAULT_LATE_FEE` | `0` | Default late fee on invoices |
| `ACADEMY_BELT_MONTHS_BETWEEN` | `3` | Minimum months between belt exams |
| `ACADEMY_ATTENDANCE_INACTIVE_DAYS` | `14` | Days without check-in = inactive alert |
| `ACADEMY_ATTENDANCE_LOW_PERCENT` | `40` | Low monthly attendance threshold |
| `ACADEMY_LOGO_PATH` | `images/logo.png` | Logo on PDFs (under `public/`) |
| `MAIL_*` | — | Required for email reminders & notifications |

Full list in `.env.example`. Business rules (fee types, belts, roles) are in `config/academy.php`.

---

## 25. Not included

| Feature | Status |
|---------|--------|
| **Online payments** (card, UPI, gateway) | Not implemented — use payment slip + manual payment recording |
| **SMS gateway** | SMS/WhatsApp from notifications UI are **logged only**; use WhatsApp links on fee reminders |
| **Student self-login** | Only **parent** portal for guardians; no student-facing app |

---

## Quick URL reference

### Public

```
/  /branches  /register-online  /events  /coaches  /notices
/contact  /about-us  /gallary  /notice-home
/verify/{token}
```

### Auth & portals

```
/login  /home  /parent
```

### ERP (requires login + ERP role)

```
/erp  /erp/hq  /erp/students  /erp/students/import
/erp/attendance  /erp/attendance/bulk
/erp/belts  /erp/branches  /erp/schedules  /erp/events
/erp/online-registrations  /erp/audit-logs
/erp/invoices  /erp/fees  /erp/fees/reminders
/erp/inventory  /erp/inventory/report
/erp/expenses  /erp/reports  /erp/branch-reports
/erp/compliance  /erp/notifications
/erp/users  /erp/trainers  /erp/salary
/erp/account/two-factor
```

### Legacy admin

```
/admin/home  /admin/reg  /add-notice  /delete-notice
```

---

*Last updated for Phases 1–4 of the Barefoot academy ERP.*
