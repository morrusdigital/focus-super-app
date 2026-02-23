# Panduan Developer - MVP Task/Project Management (Laravel)

Dokumen ini adalah panduan implementasi fitur MVP Task/Project Management untuk aplikasi internal Holding + Anak Perusahaan.

## 1) Tujuan MVP

- Company isolation: user non-holding hanya bisa akses data `company_id` miliknya.
- Holding Admin bisa akses lintas company.
- Struktur kerja: `Project -> Task` dengan multi-assignee.
- Status task: `todo`, `doing`, `blocked`, `done`.
- `due_date` optional.
- Assignee boleh set task menjadi `done`.
- Project Manager boleh edit semua task dalam project yang dia manage.
- Kanban board task per project.

## 2) Kondisi Codebase Saat Ini (Baseline)

- Role existing saat ini: `finance_holding` dan `admin_company`.
- Belum ada tabel/entitas: `tasks`, `project_members`, `task_assignees`.
- Belum ada `project_manager_id` di `projects`.
- Belum ada `TaskPolicy`, request class task, dan route/action task-kanban.
- Route saat ini dominan web/Blade (`routes/web.php`) dan modul task-kanban belum tersedia.

## 3) Prinsip Implementasi

- Implementasi bersifat additive dan non-breaking untuk modul existing.
- Gunakan layer Laravel standar:
- `FormRequest` untuk validasi.
- `Policy` untuk authorization.
- `Service` opsional untuk business logic agar controller tetap tipis.
- `Enum` untuk role dan status task.
- Company isolation harus konsisten pada query model tenant.
- Wajib menambah test fitur kritikal sebelum dianggap selesai.

## 4) Strategi Role dan Kompatibilitas

Role target MVP:

- `holding_admin`
- `company_admin`
- `project_manager`
- `member`

Karena codebase existing masih memakai role lama, gunakan fase transisi:

- `finance_holding` diperlakukan setara `holding_admin`.
- `admin_company` diperlakukan setara `company_admin`.
- Role baru (`project_manager`, `member`) mulai dipakai pada modul task/project membership.
- Setelah modul stabil, lakukan migrasi role penuh.

## 5) Struktur Database Target

Tabel yang harus ada:

- `companies`
- `users` (punya `role`, `company_id`)
- `projects` (tambah `project_manager_id`)
- `project_members` (pivot project-user)
- `tasks` (`company_id`, `project_id`, status, progress, blocked_reason, due_date, milestone, wbs, priority, start_date)
- `task_assignees` (pivot task-user)

Catatan:

- `tasks.company_id` wajib disimpan (denormalized dari `projects.company_id`) untuk filtering cepat.
- `project_manager_id` disarankan nullable di fase awal agar data legacy tidak rusak.

## 6) Business Rules Task

Status enum:

- `todo`
- `doing`
- `blocked`
- `done`

Rules:

- Jika status berubah ke `done` -> set `progress = 100`.
- Jika progress diset ke `100` -> auto set status `done`.
- Jika status `blocked` -> `blocked_reason` wajib terisi.
- Overdue: `due_date < today` dan `status != done` dan `due_date` tidak null.
- Assignee manapun boleh mark `done` (cek relasi `task_assignees`).

## 7) Business Rules Progress Project (Auto by System)

Aturan:

- Progress project dihitung otomatis dari rasio task `done`.
- Rumus: `project_progress_percent = (done_tasks / total_tasks) * 100`.
- Jika `total_tasks = 0`, maka `project_progress_percent = 0`.
- Progress project bersifat read-only (tidak diinput manual dari form).

Contoh:

- 1 project punya 4 task, done 1 -> `25%`.
- 1 project punya 4 task, done 2 -> `50%`.
- 1 project punya 4 task, done 4 -> `100%`.

Trigger recalculation:

- Saat task baru dibuat.
- Saat task dihapus.
- Saat status task berubah ke/dari `done`.
- Saat progress task berubah ke `100` lalu auto set `done`.

Catatan implementasi:

- Recalculate dilakukan di backend (observer/service) agar konsisten lintas UI.
- Jika task `done` dibuka lagi (`doing/todo/blocked`), progress project harus turun otomatis.

## 8) Authorization Matrix

### ProjectPolicy

- `view(User, Project)`:
- `holding_admin`: true.
- `company_admin`: `project.company_id == user.company_id`.
- `project_manager`: `project.project_manager_id == user.id` atau anggota project.
- `member`: harus ada di `project_members`.

- `update(User, Project)`:
- `holding_admin`: true.
- `company_admin`: same company.
- `project_manager`: hanya jika `project.project_manager_id == user.id`.

### TaskPolicy

- `view(User, Task)`:
- `holding_admin`: true.
- `company_admin`: same company.
- `project_manager`: project task tersebut dia manage.
- `member`: anggota project (MVP) atau minimal assigned.

- `update(User, Task)`:
- Untuk MVP bisa samakan dengan `view`.

- `markDone(User, Task)`:
- `holding_admin`: true.
- `company_admin`: same company.
- `project_manager`: manage project.
- `member`: harus assignee task.

## 9) Scope Fullstack MVP (Web + Blade)

### Auth

- `POST /login`
- `POST /logout`

### Admin

- `GET/POST/PUT /admin/companies` (holding only)
- `GET/POST/PUT/PATCH /admin/users` (holding + company admin dengan scope)

### Projects

- `GET /projects`
- `POST /projects`
- `GET /projects/{project}`
- `PUT /projects/{project}`
- `POST /projects/{project}/members`
- `DELETE /projects/{project}/members/{user}`

### Tasks

- `GET /tasks/my`
- `GET /projects/{project}/tasks`
- `POST /projects/{project}/tasks`
- `PUT /tasks/{task}`
- `PATCH /tasks/{task}/status`
- `GET /tasks/overdue`

### Kanban

- `GET /projects/{project}/kanban`
- `PATCH /tasks/{task}/move`

## 10) Validasi Minimum (FormRequest)

### TaskStoreRequest / TaskUpdateRequest

- `title`: required, max 255
- `assignees`: required array min 1
- `assignees.*`: exists `users,id` dan company harus cocok untuk non-holding
- `status`: in `todo,doing,blocked,done`
- `progress`: integer `0..100`
- `due_date`: nullable date
- `blocked_reason`: required if status blocked

### ProjectStoreRequest

- `company_id`: required (holding bisa pilih, company admin auto dari user)
- `project_manager_id`: required exists users,id (company harus match untuk non-holding)
- `members`: optional array

## 11) Query dan Filter Wajib

### My Tasks

- Join `tasks` + `task_assignees` by `user_id = me`.
- Default filter `status != done`.
- Sorting: `due_date asc nulls last`, lalu `updated_at desc`.

### Project Tasks

- Filter by `project_id` setelah authorize `view`.
- Support filter: `status`, `milestone`, `assignee`.

### Overdue

- `due_date < today`
- `due_date` not null
- `status != done`
- Company scope tetap berlaku

## 12) Kanban Board Task

Tujuan:

- Menyajikan task dalam kolom `todo`, `doing`, `blocked`, `done` per project.
- Mendukung perpindahan task antar kolom (drag/drop di UI, update status di backend).

Route/Action:

- `GET /projects/{project}/kanban`
- `PATCH /tasks/{task}/move`

Input request `PATCH /tasks/{task}/move` (minimum):

- `status` (`todo|doing|blocked|done`)
- `blocked_reason` (wajib jika pindah ke `blocked`)

Rules:

- Validasi authorization pakai `TaskPolicy`.
- Saat pindah ke `done`, backend set `progress = 100`.
- Saat pindah ke `blocked`, `blocked_reason` wajib.
- Non-assignee tidak boleh mark done (kecuali role yang berhak menurut policy).

Output halaman `GET /projects/{project}/kanban` (contoh data view model):

- `project`
- `columns.todo[]`
- `columns.doing[]`
- `columns.blocked[]`
- `columns.done[]`

## 13) Company Isolation

Aturan wajib:

- Jika role bukan `holding_admin`, semua query tenant harus terfilter `company_id = auth()->user()->company_id`.

Implementasi disarankan:

- Gunakan global scope untuk model tenant utama (`Project`, `Task`) dengan mekanisme bypass aman untuk CLI/seeder/internal process.
- Tetap gunakan `Policy` sebagai lapisan otorisasi final.

## 14) Struktur Folder Rekomendasi

- `app/Models/*`
- `app/Policies/*`
- `app/Http/Controllers/*`
- `app/Http/Requests/*`
- `app/Services/Task/*` (opsional)
- `app/Enums/*`

## 15) Rencana Delivery Bertahap

### PR-1 (Fondasi Data + Domain)

- Tambah migration `project_manager_id`, `project_members`, `tasks`, `task_assignees`.
- Tambah enum role/status.
- Tambah model + relasi.
- Tambah compatibility helper role lama-ke-baru.

### PR-2 (Authorization + Membership)

- Implement `ProjectPolicy` baru sesuai matrix.
- Tambah route/action add/remove project members.
- Tambah request validation untuk project membership.

### PR-3 (Task Core)

- Implement `TaskPolicy`.
- Implement route/action list/create/update/mark status.
- Implement query `my tasks`, `project tasks`, `overdue`.
- Implement business rules status/progress.

### PR-4 (Kanban)

- Implement route/action `GET /projects/{project}/kanban`.
- Implement route/action `PATCH /tasks/{task}/move`.
- Implement struktur data board per status untuk render Blade.
- Tambah validasi rule blocked/done saat move.

### PR-5 (Hardening)

- Feature test authorization lintas role.
- Feature test company isolation.
- Feature test status rules.
- Feature test kanban view + move task antar status.

## 16) Definition of Done per Modul

### Auth

- Login/logout berfungsi.
- Middleware auth bekerja.
- Unauthorized response konsisten.

### Company

- Holding admin bisa CRUD company.
- Isolation antar company berjalan.

### Project

- Bisa create/edit project.
- Bisa add/remove member.
- Akses Project Manager sesuai aturan.
- Listing project terfilter company.
- Progress project tampil dan otomatis berubah berdasarkan jumlah task done.

### Task

- Multi-assignee via pivot berjalan.
- Rules status/progress/blocked berjalan.
- Member hanya lihat task yang diizinkan.
- Endpoint `my tasks`, `project tasks`, `overdue` berfungsi.

### Kanban

- Board task tampil per status (`todo`, `doing`, `blocked`, `done`) per project.
- Task bisa dipindah antar kolom sesuai otorisasi.
- Rule blocked/done tetap enforced saat move.

## 17) Checklist Review Sebelum Merge

- Migration aman untuk data existing.
- Tidak ada query lintas company tanpa filter untuk non-holding.
- Semua controller pakai `FormRequest` dan `Policy`.
- Test fitur utama lulus.
- Test perhitungan progress project (0%, parsial, 100%, dan turun lagi saat reopen) lulus.
- Dokumentasi route/action dan contract input kanban diperbarui jika ada perubahan.
