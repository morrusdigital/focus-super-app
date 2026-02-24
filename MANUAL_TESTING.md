# Manual Testing Guide — Focus Super App (MVP Task Management)

> **Base URL**: `http://localhost:8000`  
> **Semua password**: `password`

---

## Persiapan Awal

### 1. Jalankan server

```bash
php artisan serve
```

### 2. Reset database & seed data awal

```bash
php artisan migrate:fresh --seed
```

Perintah ini membuat:

| # | Company | Akun | Role |
|---|---|---|---|
| 1 | Focus Group Capital (Holding) | `finance.holding@example.com` | `finance_holding` |
| 2 | MCB | `admin.company1@example.com` | `admin_company` |
| 3 | JDC | `admin.company2@example.com` | `admin_company` |
| 4 | MDC | `admin.company3@example.com` | `admin_company` |
| 5 | DF | `admin.company4@example.com` | `admin_company` |
| 6 | FTC | `admin.company5@example.com` | `admin_company` |

### 3. Buat akun Project Manager & Member (untuk MCB / company_id = 2)

Jalankan di terminal:

```bash
php artisan tinker --no-interaction
```

Kemudian paste blok berikut:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name'       => 'Project Manager',
    'email'      => 'pm@example.com',
    'password'   => Hash::make('password'),
    'role'       => 'project_manager',
    'company_id' => 2,
]);

User::create([
    'name'       => 'Member Satu',
    'email'      => 'member@example.com',
    'password'   => Hash::make('password'),
    'role'       => 'member',
    'company_id' => 2,
]);

echo "Done\n";
exit;
```

---

## Daftar Akun Testing

| Akun | Email | Role | Company |
|---|---|---|---|
| Finance Holding | `finance.holding@example.com` | `finance_holding` → holding admin | Focus Group Capital |
| Admin MCB | `admin.company1@example.com` | `admin_company` → company admin | MCB |
| Project Manager | `pm@example.com` | `project_manager` | MCB |
| Member | `member@example.com` | `member` | MCB |

---

## Alur Testing

---

### ✅ SKENARIO 1 — Login & Akses Halaman Utama

**Login sebagai**: `admin.company1@example.com`

1. Buka `http://localhost:8000/login`
2. Isi email: `admin.company1@example.com`, password: `password`
3. Klik **Login**
4. **Ekspektasi**: Redirect ke `http://localhost:8000/` (dashboard)
5. Logout: klik tombol **Logout**

**Uji akses tanpa login:**
1. Tanpa login, buka `http://localhost:8000/projects`
2. **Ekspektasi**: Redirect ke halaman login

---

### ✅ SKENARIO 2 — Membuat Project (Admin Company)

**Login sebagai**: `admin.company1@example.com`

1. Buka `http://localhost:8000/projects`
2. Klik **Create Project** (atau `http://localhost:8000/projects/create`)
3. Isi:
   - **Name**: `Demo Project`
   - **Project Manager**: pilih `Project Manager` (pm@example.com) *(jika ada field ini di form)*
4. Klik **Save**
5. **Ekspektasi**: Project tersimpan, muncul di list `/projects`

> Catat **ID project** yang terbuat (lihat dari URL saat klik project, misal `/projects/1`).  
> Selanjutnya ID ini disebut `{project_id}`. Contoh: `1`.

---

### ✅ SKENARIO 3 — Manajemen Member Project (Issue #6)

**Login sebagai**: `admin.company1@example.com` atau `pm@example.com`

1. Buka `http://localhost:8000/projects/{project_id}` (halaman detail project)
2. Cari section **Members**
3. Tambahkan `member@example.com` sebagai member
4. **Ekspektasi**: Member muncul di daftar member project
5. Coba hapus member, lalu tambah kembali

**Uji isolasi:**
- Login sebagai `admin.company2@example.com` (beda company)
- Buka `http://localhost:8000/projects/{project_id}`
- **Ekspektasi**: 403 Forbidden

---

### ✅ SKENARIO 4 — Task CRUD (Issue #7)

**Login sebagai**: `pm@example.com`

#### Buat Task

1. Buka `http://localhost:8000/projects/{project_id}/tasks/create`
2. Isi:
   - **Title**: `Task Pertama`
   - **Description**: `Deskripsi task pertama`
   - **Status**: `Todo`
   - **Priority**: `Medium`
   - **Due Date**: (pilih tanggal masa depan, misal 7 hari ke depan)
   - **Assignees**: centang `Member Satu`
3. Klik **Save**
4. **Ekspektasi**: Task tersimpan, muncul di list `/projects/{project_id}/tasks`

Buat **3 task lagi** sehingga total ada **4 task**, semua status `todo`:
- `Task Kedua` (assign: member@example.com)
- `Task Ketiga` (assign: member@example.com)
- `Task Keempat` (no assignee)

#### Edit Task

1. Buka `http://localhost:8000/projects/{project_id}/tasks/{task_id}/edit`
2. Ubah title jadi `Task Pertama — Edit`
3. Klik **Update**
4. **Ekspektasi**: Perubahan tersimpan

#### Uji Authorization Task

- Login sebagai `member@example.com`
- Coba akses `http://localhost:8000/projects/{project_id}/tasks/create`
- **Ekspektasi**: 403 Forbidden (member tidak bisa buat/edit task)

---

### ✅ SKENARIO 5 — My Tasks & Overdue (Issue #8)

#### My Tasks

**Login sebagai**: `member@example.com`

1. Buka `http://localhost:8000/tasks/my`
2. **Ekspektasi**: Tampil task-task yang di-assign ke `member@example.com` dengan status bukan `done`
3. Task diurutkan berdasarkan due date (paling dekat di atas)

#### Overdue Tasks

**Login sebagai**: `pm@example.com`

1. Edit salah satu task → set **Due Date** ke tanggal **kemarin** (past date)
2. Buka `http://localhost:8000/tasks/overdue`
3. **Ekspektasi**: Task dengan due date lampau dan status bukan `done` muncul di sini

**Uji isolasi role:**

- Login sebagai `finance.holding@example.com`
- Buka `http://localhost:8000/tasks/my`
- **Ekspektasi**: Tampil task dari **semua** company (akses penuh)

- Login sebagai `admin.company1@example.com`
- Buka `http://localhost:8000/tasks/my`
- **Ekspektasi**: Tampil task dari MCB saja

---

### ✅ SKENARIO 6 — Kanban Board Read-Only (Issue #9)

**Login sebagai**: `pm@example.com`

1. Buka `http://localhost:8000/projects/{project_id}/kanban`
2. **Ekspektasi**:
   - 4 kolom: **To Do**, **In Progress**, **Blocked**, **Done**
   - Semua task muncul di kolom **To Do**
   - Setiap kolom menampilkan badge jumlah task
   - Ada **progress bar project** di atas board (`0%` karena belum ada yang done)

**Login sebagai**: `member@example.com`

1. Buka `http://localhost:8000/projects/{project_id}/kanban`
2. **Ekspektasi**: Halaman bisa diakses, task tampil

**Uji isolasi:**

- Login sebagai `admin.company2@example.com` (beda company)
- Buka `http://localhost:8000/projects/{project_id}/kanban`
- **Ekspektasi**: 403 Forbidden

---

### ✅ SKENARIO 7 — Move Task Antar Kolom (Issue #10)

**Login sebagai**: `pm@example.com`

**A. Move todo → doing**

1. Buka `http://localhost:8000/projects/{project_id}/kanban`
2. Di kolom **To Do**, klik tombol `→ In Progress` pada `Task Pertama`
3. **Ekspektasi**: Task pindah ke kolom **In Progress**, flash alert sukses muncul

**B. Move doing → blocked**

1. Di kolom **In Progress**, klik `→ Blocked` pada `Task Pertama`
2. **Ekspektasi**: Muncul textarea **Alasan Blocked**
3. Isi textarea: `Menunggu approval klien`
4. Klik kembali `→ Blocked`
5. **Ekspektasi**: Task pindah ke kolom **Blocked**, reason tampil di card

**C. Move blocked tanpa reason → ditolak**

1. Klik `→ Blocked` pada task yang sedang `doing`
2. Langsung klik `→ Blocked` **tanpa** mengisi textarea
3. **Ekspektasi**: Validasi error — `blocked_reason` wajib diisi

**D. Move → done (oleh PM)**

1. Klik `→ Done` pada `Task Pertama`
2. **Ekspektasi**:
   - Task pindah ke kolom **Done**
   - Progress bar project berubah dari `0%` → `25%` (1 dari 4 task done)

**E. Move done → todo (reopen, oleh PM)**

1. Di kolom **Done**, klik `→ To Do` pada `Task Pertama`
2. **Ekspektasi**:
   - Task kembali ke kolom **To Do**
   - Progress bar turun dari `25%` → `0%`

**F. Move oleh assignee member (hanya → Done)**

1. Login sebagai `member@example.com`
2. Buka `http://localhost:8000/projects/{project_id}/kanban`
3. **Ekspektasi**: Pada task yang di-assign ke member, hanya tombol `→ Done` yang tampil
4. Klik `→ Done`
5. **Ekspektasi**: Task pindah ke **Done**, progress bar naik

**G. Member bukan assignee tidak bisa move**

1. Login sebagai `member@example.com`
2. Pada `Task Keempat` (tidak ada assignee-nya) → tidak ada tombol move sama sekali

---

### ✅ SKENARIO 8 — Auto-Calculate Project Progress (Issue #11)

**Login sebagai**: `pm@example.com`

1. Buka `http://localhost:8000/projects/{project_id}/kanban`
2. Pastikan semua 4 task ada di status **todo** → progress bar menunjukkan `0%`

**Langkah 1 — 1 task done = 25%**

1. Move `Task Pertama` → **Done**
2. **Ekspektasi**: Progress bar berubah menjadi `25%`

**Langkah 2 — 2 task done = 50%**

1. Move `Task Kedua` → **Done**
2. **Ekspektasi**: Progress bar = `50%`

**Langkah 3 — 4 task done = 100%**

1. Move `Task Ketiga` → **Done**
2. Move `Task Keempat` → **Done**
3. **Ekspektasi**: Progress bar = `100%`, warna berubah dari **biru** → **hijau**

**Langkah 4 — Reopen task (progress turun otomatis)**

1. Move `Task Pertama` → **To Do**
2. **Ekspektasi**: Progress bar turun ke `75%` (3 dari 4 done)

**Langkah 5 — Tambah task baru**

1. Buka `http://localhost:8000/projects/{project_id}/tasks/create`
2. Buat task baru `Task Kelima` (status Todo)
3. Kembali ke kanban `http://localhost:8000/projects/{project_id}/kanban`
4. **Ekspektasi**: Progress bar turun — sekarang 3 dari 5 task = `60%`

**Langkah 6 — Uji non-done↔non-done tidak mengubah progress**

1. Catat nilai progress saat ini (misal `60%`)
2. Move salah satu task **Todo → In Progress** (bukan ke Done)
3. **Ekspektasi**: Progress bar **tidak berubah** (masih `60%`)

---

## Ringkasan URL Penting

| Aksi | URL | Method |
|---|---|---|
| Login | `/login` | GET/POST |
| Dashboard | `/` | GET |
| List Project | `/projects` | GET |
| Buat Project | `/projects/create` | GET/POST |
| Detail Project | `/projects/{id}` | GET |
| Tambah Member | `/projects/{id}/members` | POST |
| Hapus Member | `/projects/{id}/members/{user}` | DELETE |
| List Task | `/projects/{id}/tasks` | GET |
| Buat Task | `/projects/{id}/tasks/create` | GET/POST |
| Edit Task | `/projects/{id}/tasks/{task}/edit` | GET/PUT |
| My Tasks | `/tasks/my` | GET |
| Overdue Tasks | `/tasks/overdue` | GET |
| Kanban Board | `/projects/{id}/kanban` | GET |
| Move Task | `/tasks/{task}/move` | PATCH |

---

## Matriks Authorization

| Aksi | `finance_holding` | `admin_company` | `project_manager` | `member` |
|---|:---:|:---:|:---:|:---:|
| Lihat semua project | ✅ semua | ✅ company sendiri | ✅ manage sendiri | ✅ joined |
| Buat project | ✅ | ✅ | ❌ | ❌ |
| Tambah/hapus member | ✅ | ✅ | ✅ manage sendiri | ❌ |
| Buat/edit task | ✅ | ✅ | ✅ manage sendiri | ❌ |
| Move task (semua status) | ✅ | ✅ | ✅ manage sendiri | ❌ |
| Move task → done saja | ✅ | ✅ | ✅ | ✅ (assignee) |
| Lihat kanban | ✅ | ✅ | ✅ | ✅ (joined) |
| My Tasks | ✅ (semua) | ✅ (company) | ✅ (PM-nya) | ✅ (assignee) |
