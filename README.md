# Focus Super App

Aplikasi web internal berbasis Laravel untuk kebutuhan holding dan anak perusahaan, dengan fokus saat ini pada:

- Manajemen project dan cashflow operasional.
- Multi-company data isolation.
- Role-based access control.
- Fondasi menuju modul task management + kanban.

## Tech Stack

- PHP 8.2+
- Laravel 12
- Blade (server-rendered fullstack)
- MySQL/SQLite (untuk local test bisa pakai SQLite)
- PHPUnit untuk test

## Modul Saat Ini

- Authentication (`/login`, `/logout`)
- Companies + user role basis perusahaan
- Projects
- Project terms (termin)
- Project receipts (cash in)
- Project expenses (cash out)
- Project vendors
- Project progress log
- Project recap
- Budget plans
- Chart accounts
- Company bank accounts
- Tax masters

## Role Saat Ini (Existing)

Role yang sudah dipakai di codebase:

- `finance_holding`
- `admin_company`

Catatan:

- Modul MVP Task/Project Management akan memakai role target:
- `holding_admin`, `company_admin`, `project_manager`, `member`
- Dengan fase kompatibilitas role lama ke role baru.

## Dokumentasi Project

- Panduan pengembangan MVP Task/Project Management + Kanban: `README_MVP_TASK_PROJECT_MANAGEMENT.md`

## Setup Local Development

1. Install dependency PHP:
```bash
composer install
```

2. Siapkan environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Atur koneksi database di `.env`, lalu migrate dan seed:
```bash
php artisan migrate
php artisan db:seed
```

4. Install dependency frontend:
```bash
npm install
```

5. Jalankan aplikasi:
```bash
php artisan serve
npm run dev
```

## Seeded User (Default)

Saat menjalankan `php artisan db:seed`, akun default yang dibuat:

- `finance.holding@example.com` / `password`
- `admin.company1@example.com` / `password`
- `admin.company2@example.com` / `password`
- `admin.company3@example.com` / `password`
- `admin.company4@example.com` / `password`
- `admin.company5@example.com` / `password`

## Menjalankan Test

```bash
php artisan test
```

## Arsitektur Backend yang Dipakai

- Authorization via `Policy`
- Validasi input via `FormRequest`
- Domain logic via model/service
- Multi-company scoping di level query + policy

## Catatan Pengembangan

- Utamakan perubahan additive dan hindari breaking change pada modul existing.
- Untuk fitur baru task/kanban, ikuti rule dan DoD di `README_MVP_TASK_PROJECT_MANAGEMENT.md`.
