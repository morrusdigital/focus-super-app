# Manual Testing Guide — Day 3 (Task Projects Module)

> **Base URL**: `http://localhost:8000`  
> **Semua password**: `password`  
> **Issues**: #34 · #35

---

## Persiapan Awal

### 1. Jalankan server

```bash
php artisan serve
```

### 2. Reset database & seed data

```bash
php artisan migrate:fresh --seed
```

Perintah ini membuat akun-akun berikut (dari seeder Day 2):

| # | Company | Email | Role |
|---|---|---|---|
| 1 | Focus Group Capital (Holding) | `holding.admin@example.com` | `holding_admin` |
| 2 | Focus Group Capital (Holding) | `finance.holding@example.com` | `finance_holding` |
| 3 | MCB | `company.admin1@example.com` | `company_admin` |
| 4 | MCB | `finance.company1@example.com` | `finance_company` |
| 5 | MCB | `employee1@example.com` | `employee` |
| 6 | JDC | `company.admin2@example.com` | `company_admin` |
| 7 | JDC | `finance.company2@example.com` | `finance_company` |
| 8 | JDC | `employee2@example.com` | `employee` |

### 3. Buat akun Employee sebagai Project Manager Task Project

Jalankan di terminal:

```bash
php artisan tinker --no-interaction
```

Kemudian paste blok berikut:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Employee MCB yang akan dijadikan PM task project
User::create([
    'name'       => 'PM Task',
    'email'      => 'pm.task@example.com',
    'password'   => Hash::make('password'),
    'role'       => 'employee',
    'company_id' => 2, // MCB
    'is_active'  => true,
]);

// Employee MCB biasa (akan dijadikan assignee task)
User::create([
    'name'       => 'Assignee Satu',
    'email'      => 'assignee1@example.com',
    'password'   => Hash::make('password'),
    'role'       => 'employee',
    'company_id' => 2, // MCB
    'is_active'  => true,
]);

echo "Done\n";
exit;
```

---

## Daftar Akun Testing

| Akun | Email | Role | Company |
|---|---|---|---|
| Holding Admin | `holding.admin@example.com` | `holding_admin` | Focus Group Capital |
| Finance Holding | `finance.holding@example.com` | `finance_holding` | Focus Group Capital |
| Company Admin MCB | `company.admin1@example.com` | `company_admin` | MCB |
| Finance Company MCB | `finance.company1@example.com` | `finance_company` | MCB |
| Employee PM Task | `pm.task@example.com` | `employee` | MCB |
| Employee Assignee | `assignee1@example.com` | `employee` | MCB |
| Company Admin JDC | `company.admin2@example.com` | `company_admin` | JDC |

---

## URL Referensi

| Aksi | URL | Method |
|---|---|---|
| Daftar Task Project | `/task-projects` | GET |
| Form buat Task Project | `/task-projects/create` | GET/POST |
| Detail Task Project | `/task-projects/{id}` | GET |
| Form edit Task Project | `/task-projects/{id}/edit` | GET/PUT |
| Hapus Task Project | `/task-projects/{id}` | DELETE |
| Kanban board | `/task-projects/{id}/kanban` | GET |
| Daftar task | `/task-projects/{id}/tasks` | GET |
| Form buat task | `/task-projects/{id}/tasks/create` | GET/POST |
| Form edit task | `/task-projects/{id}/tasks/{task}/edit` | GET/PUT |
| Hapus task | `/task-projects/{id}/tasks/{task}` | DELETE |
| Move task (kanban) | `/task-projects/{id}/tasks/{task}/move` | PATCH |

---

## Matriks Authorization

### Task Project (level project)

| Aksi | `holding_admin` | `company_admin` | `finance_holding` | `finance_company` | `employee` |
|---|:---:|:---:|:---:|:---:|:---:|
| Lihat daftar | ✅ semua | ✅ company sendiri | ✅ semua (read) | ✅ company sendiri (read) | ✅ terlibat saja |
| Lihat detail | ✅ | ✅ company sendiri | ✅ | ✅ company sendiri | ✅ terlibat |
| Buat project | ✅ | ✅ | ❌ | ❌ | ✅ |
| Edit project | ✅ | ✅ company sendiri | ❌ | ❌ | ✅ (PM/creator saja) |
| Hapus project | ✅ | ✅ company sendiri | ❌ | ❌ | ✅ (PM/creator saja) |
| Lihat kanban | ✅ | ✅ company sendiri | ✅ | ✅ company sendiri | ✅ terlibat |

### Task di dalam Task Project

| Aksi | `holding_admin` | `company_admin` | `finance_holding` | `finance_company` | `employee` |
|---|:---:|:---:|:---:|:---:|:---:|
| Buat/edit/hapus task | ✅ | ✅ company sendiri | ❌ | ❌ | ✅ (PM saja) |
| Move status (semua) | ✅ | ✅ company sendiri | ❌ | ❌ | ✅ (PM atau assignee) |

---

## SKENARIO 1 — Sidebar & Akses Halaman Daftar Task Project

### 1A. Holding Admin melihat menu & daftar

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/`
2. **Ekspektasi**: Sidebar menampilkan menu **Task Projects** (dengan ikon check-circle)
3. Klik menu **Task Projects** atau buka `http://localhost:8000/task-projects`
4. **Ekspektasi**: Halaman daftar terbuka, tabel kosong (belum ada project)

### 1B. Company Admin melihat menu

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/`
2. **Ekspektasi**: Sidebar menampilkan menu **Task Projects**
3. Klik **Task Projects**
4. **Ekspektasi**: Halaman daftar terbuka (hanya project dari MCB)

### 1C. Employee melihat menu

**Login sebagai**: `pm.task@example.com`

1. Buka `http://localhost:8000/`
2. **Ekspektasi**: Sidebar menampilkan menu **Task Projects**

### 1D. Finance tidak bisa membuat project (tetapi bisa membuka daftar)

**Login sebagai**: `finance.company1@example.com`

1. Buka `http://localhost:8000/task-projects`
2. **Ekspektasi**: Halaman daftar terbuka — **tidak ada** tombol "Tambah Task Project"
3. Coba akses langsung `http://localhost:8000/task-projects/create`
4. **Ekspektasi**: `403 Forbidden`

---

## SKENARIO 2 — Membuat Task Project

### 2A. Company Admin membuat Task Project baru

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/task-projects/create`
2. **Ekspektasi**: Form tampil dengan field **Nama** dan **Project Manager**
3. Isi:
   - **Nama**: `Project Alpha`
   - **Project Manager**: pilih `PM Task` (pm.task@example.com)
4. Klik **Simpan**
5. **Ekspektasi**:
   - Redirect ke halaman daftar atau detail
   - Flash sukses muncul
   - `Project Alpha` tampil di daftar dengan status **Belum Mulai** dan progress **0%**

> Catat **ID** project yang terbuat dari URL detail (misal `/task-projects/1`).  
> Selanjutnya disebut `{tp_id}`. Contoh: `1`.

### 2B. Holding Admin membuat Task Project dari company manapun

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/task-projects/create`
2. Isi:
   - **Nama**: `Project Beta (Holding)`
   - **Project Manager**: pilih salah satu user
3. Klik **Simpan**
4. **Ekspektasi**: Project tersimpan, muncul di daftar holding admin

### 2C. Validasi form — field wajib

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/task-projects/create`
2. Klik **Simpan** tanpa mengisi apapun
3. **Ekspektasi**: Error validasi muncul untuk field **Nama** dan **Project Manager**

### 2D. Employee membuat Task Project

**Login sebagai**: `pm.task@example.com`

1. Buka `http://localhost:8000/task-projects/create`
2. Isi Nama: `Project Employee`
3. Pilih Project Manager: diri sendiri atau user lain
4. Klik **Simpan**
5. **Ekspektasi**: Project tersimpan (employee diizinkan membuat task project)

---

## SKENARIO 3 — Edit & Hapus Task Project

### 3A. Company Admin mengedit project

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/edit`
2. Ubah nama menjadi `Project Alpha — Edited`
3. Klik **Simpan**
4. **Ekspektasi**: Nama berubah di halaman detail/daftar

### 3B. Finance tidak bisa mengedit

**Login sebagai**: `finance.company1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/edit`
2. **Ekspektasi**: `403 Forbidden`

### 3C. Company Admin JDC tidak bisa mengedit project MCB

**Login sebagai**: `company.admin2@example.com` (JDC)

1. Buka `http://localhost:8000/task-projects/{tp_id}/edit` (project MCB)
2. **Ekspektasi**: `403 Forbidden`

### 3D. Employee non-PM tidak bisa mengedit

**Login sebagai**: `assignee1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/edit`
2. **Ekspektasi**: `403 Forbidden` (employee hanya bisa edit jika PM atau creator)

---

## SKENARIO 4 — Melihat Detail Task Project

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}`
2. **Ekspektasi**:
   - Informasi project tampil: Nama, PM, Dibuat oleh, Tanggal dibuat
   - **Summary cards**: Status project, Progress %, Total task, breakdown per status (Todo/Doing/Blocked/Done)
   - Status badge: **Belum Mulai** (karena belum ada task)
   - Progress: **0%**
   - Tombol: **Daftar Task**, **Kanban**, **Edit** (jika berhak), **Hapus** (jika berhak)

**Login sebagai**: `finance.company1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}`
2. **Ekspektasi**: Halaman tampil (finance bisa melihat), **tidak ada** tombol Edit/Hapus

---

## SKENARIO 5 — Membuat Task di dalam Task Project

**Login sebagai**: `company.admin1@example.com`

### 5A. Buat task pertama (status Todo)

1. Buka `http://localhost:8000/task-projects/{tp_id}/tasks/create`
2. Isi:
   - **Judul**: `Task Satu`
   - **Status**: `Todo`
   - **Progress**: `0`
   - **Due Date**: (tanggal masa depan, misal 7 hari ke depan)
   - **Assignees**: centang `Assignee Satu`
3. Klik **Simpan**
4. **Ekspektasi**: Task tersimpan, muncul di daftar task

### 5B. Buat 3 task tambahan

Buat task berikut sehingga total ada **4 task**:

| Judul | Status | Progress | Assignees |
|---|---|---|---|
| `Task Dua` | `Todo` | `0` | `Assignee Satu` |
| `Task Tiga` | `Todo` | `0` | `Assignee Satu` |
| `Task Empat` | `Todo` | `0` | *(kosong)* |

### 5C. Validasi business rule — status Done wajib progress 100

1. Buka form buat task
2. Isi Judul: `Task Validasi`
3. Set **Status**: `Done`, **Progress**: `50`
4. Klik **Simpan**
5. **Ekspektasi**: Progress otomatis tersimpan sebagai **100** (bukan 50)

### 5D. Validasi business rule — progress 100 otomatis status Done

1. Buka form buat task
2. Set **Status**: `Todo`, **Progress**: `100`
3. Klik **Simpan**
4. **Ekspektasi**: Status otomatis tersimpan sebagai **Done**

### 5E. Validasi business rule — blocked wajib blocked_reason

1. Buka form buat task
2. Set **Status**: `Blocked`, kosongkan field **Alasan Blocked**
3. Klik **Simpan**
4. **Ekspektasi**: Error validasi "Alasan blocked wajib diisi jika status blocked"

### 5F. Employee non-PM tidak bisa membuat task

**Login sebagai**: `assignee1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/tasks/create`
2. **Ekspektasi**: `403 Forbidden` (hanya PM yang bisa manage task)

---

## SKENARIO 6 — Daftar Task & Detail Summary

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/tasks`
2. **Ekspektasi**:
   - **Summary mini-cards** di atas: Total task, Todo, Doing, Blocked, Done, dan Progress %
   - Task dikelompokkan berdasarkan status (section header berwarna berbeda)
   - Setiap task menampilkan: judul, badge status, progress bar, due date, list assignee
   - Tombol **Edit** dan **Hapus** tampil (karena company admin berhak)
3. Kembali ke detail project `http://localhost:8000/task-projects/{tp_id}`
4. **Ekspektasi**: Summary cards terupdate — Total task = **4** (atau sesuai yang dibuat), status = **Berjalan**

---

## SKENARIO 7 — Kanban Board (Tampilan)

### 7A. Akses kanban

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. **Ekspektasi**:
   - Summary bar di atas: status project badge, progress bar `0%`, hitungan per status, tombol **Task Baru**
   - **4 kolom**: **Todo**, **Doing**, **Blocked**, **Done**
   - Semua 4 task muncul di kolom **Todo**
   - Setiap card: judul, progress bar, due date, list assignee
   - Setiap card memiliki tombol move ke semua status lain

### 7B. Finance dapat melihat kanban

**Login sebagai**: `finance.company1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. **Ekspektasi**:
   - Kanban tampil dengan 4 kolom
   - **Tidak ada tombol move** (finance hanya read-only)
   - **Tidak ada tombol Task Baru**

### 7C. Company JDC tidak bisa akses kanban MCB

**Login sebagai**: `company.admin2@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. **Ekspektasi**: `403 Forbidden`

### 7D. Employee yang tidak terlibat tidak bisa akses

**Login sebagai**: `employee2@example.com` (JDC — beda company)

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. **Ekspektasi**: `403 Forbidden`

---

## SKENARIO 8 — Move Task di Kanban

### 8A. Move Todo → Doing

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. Di kolom **Todo**, klik tombol `→ Doing` pada `Task Satu`
3. **Ekspektasi**:
   - Task pindah ke kolom **Doing**
   - Flash alert sukses muncul
   - Badge hitungan kolom **Todo** berkurang 1, **Doing** bertambah 1

### 8B. Move Doing → Blocked (dengan alasan)

1. Di kolom **Doing**, klik `→ Blocked` pada `Task Satu`
2. **Ekspektasi**: Muncul textarea **Alasan Blocked**
3. Isi textarea: `Menunggu approval klien`
4. Konfirmasi / submit
5. **Ekspektasi**:
   - Task pindah ke kolom **Blocked**
   - Alasan blocked tampil di card: *"Menunggu approval klien"*

### 8C. Move → Blocked tanpa alasan ditolak

1. Di kolom **Doing**, klik `→ Blocked` pada task lain
2. **Jangan** isi textarea alasan
3. Submit
4. **Ekspektasi**: Error validasi — **Alasan blocked wajib diisi**

### 8D. Move → Done (progress otomatis 100)

1. Di kolom **Todo**, klik `→ Done` pada `Task Dua`
2. **Ekspektasi**:
   - Task pindah ke kolom **Done**
   - Progress bar **Task Dua** berubah menjadi **100%**
   - Progress bar project di summary bar naik

### 8E. Move Done → Todo (reopen, progress reset ke 0)

1. Di kolom **Done**, klik `→ Todo` pada `Task Dua`
2. **Ekspektasi**:
   - Task kembali ke kolom **Todo**
   - Progress bar project turun

### 8F. Move oleh PM Employee (semua status)

**Login sebagai**: `pm.task@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. **Ekspektasi**:
   - Semua tombol move tampil (PM bisa move ke semua status)
3. Move `Task Tiga` → **Done**
4. **Ekspektasi**: Task pindah, progress bar naik

### 8G. Move oleh Assignee Employee (semua status)

**Login sebagai**: `assignee1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. **Ekspektasi**:
   - Pada task yang di-assign ke `Assignee Satu` → **tombol move tampil** (semua status)
   - Pada `Task Empat` (tidak ada assignee) → **tidak ada tombol move**
3. Klik `→ Done` pada `Task Satu`
4. **Ekspektasi**: Task pindah ke **Done**

### 8H. Finance tidak bisa move

**Login sebagai**: `finance.company1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. **Ekspektasi**: Tidak ada tombol move di card manapun
3. Jika paksa PATCH ke `/task-projects/{tp_id}/tasks/{task}/move`: `403 Forbidden`

---

## SKENARIO 9 — Progress & Status Otomatis Project

**Login sebagai**: `company.admin1@example.com`

Pastikan kondisi awal: 4 task, semua di **Todo**, progress project **0%**.

### 9A. 1 dari 4 task done → progress 25%

1. Move `Task Satu` → **Done**
2. Cek summary di kanban dan/atau halaman detail project
3. **Ekspektasi**:
   - Progress bar = **25%**
   - Status project berubah menjadi **Berjalan**

### 9B. 2 dari 4 task done → progress 50%

1. Move `Task Dua` → **Done**
2. **Ekspektasi**: Progress bar = **50%**

### 9C. 4 dari 4 task done → progress 100%, status Selesai

1. Move `Task Tiga` → **Done**
2. Move `Task Empat` → **Done**
3. **Ekspektasi**:
   - Progress bar = **100%**
   - Warna progress bar berubah dari biru → **hijau**
   - Status project badge berubah menjadi **Selesai**

### 9D. Reopen task → progress turun otomatis

1. Move `Task Satu` → **Todo**
2. **Ekspektasi**: Progress bar turun ke **75%** (3 dari 4 done), status kembali **Berjalan**

### 9E. Ada task blocked → status Terhambat

1. Move `Task Dua` → **Doing**, lalu → **Blocked** (isi alasan)
2. Cek halaman detail `http://localhost:8000/task-projects/{tp_id}`
3. **Ekspektasi**: Status project badge berubah menjadi **Terhambat**

---

## SKENARIO 10 — Isolasi Data Antar Company

### 10A. Holding Admin melihat project semua company

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/task-projects`
2. **Ekspektasi**: Task project dari **MCB** dan company lain semuanya tampil

### 10B. Company Admin JDC hanya melihat project JDC

**Login sebagai**: `company.admin2@example.com`

1. Buka `http://localhost:8000/task-projects`
2. **Ekspektasi**: Hanya project milik **JDC** yang tampil — project MCB tidak muncul

### 10C. Company Admin JDC tidak bisa akses resource MCB

**Login sebagai**: `company.admin2@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}` (project MCB)
2. **Ekspektasi**: `403 Forbidden`
3. Buka `http://localhost:8000/task-projects/{tp_id}/tasks`
4. **Ekspektasi**: `403 Forbidden`

### 10D. Employee MCB tidak melihat project yang tidak melibatkan dirinya

**Login sebagai**: `employee1@example.com` (bukan PM, bukan assignee)

1. Buka `http://localhost:8000/task-projects`
2. **Ekspektasi**: Daftar **kosong** (tidak ada project yang melibatkan employee1)
3. Buka `http://localhost:8000/task-projects/{tp_id}` (project MCB di mana employee1 tidak terlibat)
4. **Ekspektasi**: `403 Forbidden`

---

## SKENARIO 11 — Uji Lintas Skenario (End-to-End)

**Alur penuh dari pembuatan hingga penyelesaian project.**

### Langkah 1 — Company Admin membuat project

**Login sebagai**: `company.admin1@example.com`

1. Buat task project: **"E2E Test Project"**, PM: `PM Task`
2. Simpan → catat `{tp_id}`

### Langkah 2 — PM membuat task-task

**Login sebagai**: `pm.task@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/tasks/create`
2. Buat task:
   - `E2E Task 1` — assign ke `Assignee Satu`, status Todo
   - `E2E Task 2` — assign ke `Assignee Satu`, status Todo
   - `E2E Task 3` — tanpa assignee, status Todo

### Langkah 3 — PM mengecek summary

1. Buka `http://localhost:8000/task-projects/{tp_id}`
2. **Ekspektasi**: 3 task, progress 0%, status Belum Mulai

### Langkah 4 — Assignee mengerjakan task

**Login sebagai**: `assignee1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}/kanban`
2. Move `E2E Task 1` → **Doing**
3. **Ekspektasi**: Task pindah ke kolom Doing

### Langkah 5 — Task terhambat

1. Move `E2E Task 1` → **Blocked**, isi alasan: `Tunggu data dari klien`
2. **Ekspektasi**: Task pindah ke Blocked, alasan tampil di card

### Langkah 6 — PM menyelesaikan semua task

**Login sebagai**: `pm.task@example.com`

1. Move `E2E Task 1` → **Done**
2. Move `E2E Task 2` → **Done**
3. Move `E2E Task 3` → **Done**
4. **Ekspektasi**:
   - Progress = **100%**
   - Status project = **Selesai**
   - Warna progress bar hijau

### Langkah 7 — Finance memverifikasi (read-only)

**Login sebagai**: `finance.company1@example.com`

1. Buka `http://localhost:8000/task-projects/{tp_id}`
2. **Ekspektasi**: Detail tampil, progress 100%, status Selesai
3. Buka kanban — **tidak ada** tombol move atau tombol Task Baru

---

## Ringkasan Checklist Testing Day 3

| # | Skenario | Akun Utama | Ekspektasi | Status |
|---|---|---|---|---|
| 1A | Sidebar menu tampil & akses daftar | `holding.admin` | Menu Task Projects ada, daftar terbuka | ☐ |
| 1B | Company admin sidebar & daftar | `company.admin1` | Menu ada, hanya MCB | ☐ |
| 1C | Employee sidebar | `pm.task` | Menu Task Projects ada | ☐ |
| 1D | Finance buka daftar (no create button) | `finance.company1` | Daftar tampil, no create | ☐ |
| 2A | Buat task project (company admin) | `company.admin1` | Project tersimpan | ☐ |
| 2C | Validasi form create | `company.admin1` | Error muncul | ☐ |
| 2D | Employee bisa membuat task project | `pm.task` | Project tersimpan | ☐ |
| 3A | Edit task project | `company.admin1` | Nama berubah | ☐ |
| 3B | Finance tidak bisa edit | `finance.company1` | 403 | ☐ |
| 3C | Lintas company tidak bisa edit | `company.admin2` | 403 | ☐ |
| 4 | Detail project — summary cards | `company.admin1` | Info & summary tampil | ☐ |
| 5A | Buat task (Todo) | `company.admin1` | Task tersimpan | ☐ |
| 5C | Done → progress 100 otomatis | `company.admin1` | Progress = 100 | ☐ |
| 5D | Progress 100 → status Done otomatis | `company.admin1` | Status = Done | ☐ |
| 5E | Blocked tanpa reason ditolak | `company.admin1` | Error validasi | ☐ |
| 5F | Non-PM tidak bisa buat task | `assignee1` | 403 | ☐ |
| 6 | Daftar task & summary mini-cards | `company.admin1` | Summary & task tampil | ☐ |
| 7A | Kanban tampil 4 kolom | `company.admin1` | 4 kolom, semua task di Todo | ☐ |
| 7B | Finance kanban read-only | `finance.company1` | Tampil, no move button | ☐ |
| 7C | Lintas company tidak bisa akses kanban | `company.admin2` | 403 | ☐ |
| 8A | Move Todo → Doing | `company.admin1` | Task pindah | ☐ |
| 8B | Move → Blocked dengan alasan | `company.admin1` | Task pindah, alasan tampil | ☐ |
| 8C | Move → Blocked tanpa alasan | `company.admin1` | Error validasi | ☐ |
| 8D | Move → Done (progress 100) | `company.admin1` | Task pindah, progress 100 | ☐ |
| 8F | PM employee bisa move semua status | `pm.task` | Semua tombol move ada | ☐ |
| 8G | Assignee bisa move task sendiri | `assignee1` | Move berhasil | ☐ |
| 8H | Finance tidak bisa move | `finance.company1` | 403 | ☐ |
| 9A | 1 task done = 25% | `company.admin1` | Progress 25% | ☐ |
| 9C | 4 task done = 100%, status Selesai | `company.admin1` | Progress 100%, hijau | ☐ |
| 9D | Reopen task → progress turun | `company.admin1` | Progress turun | ☐ |
| 9E | Ada blocked → status Terhambat | `company.admin1` | Badge Terhambat | ☐ |
| 10A | Holding admin lihat semua company | `holding.admin` | Semua project tampil | ☐ |
| 10B | Company admin JDC hanya lihat JDC | `company.admin2` | Hanya JDC | ☐ |
| 10C | JDC tidak bisa akses resource MCB | `company.admin2` | 403 | ☐ |
| 10D | Employee tidak terlibat → daftar kosong | `employee1` | Kosong / 403 | ☐ |
| 11 | End-to-end flow | semua akun | Semua langkah pass | ☐ |
