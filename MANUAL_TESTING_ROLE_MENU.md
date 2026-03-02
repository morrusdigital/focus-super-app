# Manual Testing Guide — Role Menu Management

> **Base URL**: `http://localhost:8000`  
> **Semua password**: `password`  
> **Feature**: Manajemen Role Menu — konfigurasi menu sidebar secara dinamis per role

---

## Latar Belakang Fitur

Fitur ini memungkinkan **Holding Admin** untuk mengatur menu apa saja yang tampil di sidebar untuk setiap role pengguna. Perubahan langsung berlaku saat user berikutnya memuat halaman (bersifat real-time melalui cache TTL 10 menit).

### Role yang Dapat Dikonfigurasi

| Role | Label | Default Menu |
|---|---|---|
| `company_admin` | Company Admin | Projects, Task Projects, Rekap Project, Manajemen User, Ajukan BP, Daftar BP Saya, Rekening, Master (Kategori BP, Akun, Pajak) |
| `finance_holding` | Finance Holding | Projects, Rekap Project, Review BP, Semua BP |
| `finance_company` | Finance Company | Projects, Ajukan BP, Daftar BP Saya, Rekening, Master (Kategori BP, Akun, Pajak) |
| `employee` | Employee | Projects, Task Projects, My Tasks, Overdue Tasks |

### Role yang Tidak Dapat Dikonfigurasi

| Role | Keterangan |
|---|---|
| `holding_admin` | Selalu mendapatkan semua menu + Manajemen Role Menu. Tidak dapat diubah. |

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

Perintah ini membuat user dan juga mengisi tabel `role_menus` dengan konfigurasi default per role.

### 3. Verifikasi akun testing

| # | Role | Email | Password |
|---|---|---|---|
| 1 | `holding_admin` | `holding.admin@example.com` | `password` |
| 2 | `finance_holding` | `finance.holding@example.com` | `password` |
| 3 | `company_admin` | `company.admin1@example.com` | `password` |
| 4 | `finance_company` | `finance.company1@example.com` | `password` |
| 5 | `employee` | `employee1@example.com` | `password` |

---

## URL Referensi

| Aksi | URL | Method |
|---|---|---|
| Daftar konfigurasi role menu | `/role-menus` | GET |
| Form edit menu untuk role tertentu | `/role-menus/{role}/edit` | GET |
| Simpan konfigurasi menu | `/role-menus/{role}` | PUT |

---

## Katalog Menu yang Tersedia

| Key | Label Tampil | Keterangan |
|---|---|---|
| `projects` | Projects | Daftar project operasional |
| `task_projects` | Task Projects | Manajemen task project mandiri |
| `project_recap` | Rekap Project | Laporan rekapitulasi project |
| `user_management` | Manajemen User | Kelola user dalam perusahaan |
| `review_bp` | Review BP | Tinjau BP yang diajukan (dengan filter `?status=submitted`) |
| `all_bp` | Semua BP | Semua budget plan |
| `submit_bp` | Ajukan BP | Buat budget plan baru |
| `my_bp` | Daftar BP Saya | Daftar budget plan sendiri |
| `bank_accounts` | Rekening | Data rekening bank |
| `bp_categories` | Kategori BP | Master kategori budget plan (submenu) |
| `chart_accounts` | Akun | Chart of accounts (submenu) |
| `tax_masters` | Pajak | Master pajak (submenu) |
| `my_tasks` | My Tasks | Task yang diassign ke saya |
| `overdue_tasks` | Overdue Tasks | Task yang sudah melewati due date |

> **Catatan**: `Manajemen Role Menu` selalu muncul untuk `holding_admin` dan tidak ada di katalog yang bisa dikonfigurasi.

---

## SKENARIO 1 — Akses Halaman Manajemen Role Menu

### 1A. Holding Admin: dapat mengakses halaman

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/role-menus`
2. **Ekspektasi**:
   - Halaman tampil dengan judul **"Manajemen Role Menu"**
   - Terdapat **4 card** untuk role yang dapat dikonfigurasi: Company Admin, Finance Holding, Finance Company, Employee
   - Terdapat **1 card khusus** (bordered kuning) untuk **Holding Admin** yang menampilkan semua menu dan **tidak bisa diedit** (tombol disabled)
   - Setiap card menampilkan:
     - Nama dan badge role
     - **Progress bar** menunjukkan persentase menu yang aktif
     - **Badge-badge** menu yang sedang aktif untuk role tersebut
     - Tombol **"Atur Menu Role Ini"**
3. **Ekspektasi Sidebar**: Menu **"Manajemen Role Menu"** tampil di sidebar sisi kiri

### 1B. Role lain: tidak dapat mengakses

**Test untuk setiap role non-holding_admin:**

**Login sebagai**: `company.admin1@example.com`
1. Buka `http://localhost:8000/role-menus`
2. **Ekspektasi**: `403 Forbidden`

**Ulangi untuk**:
- `finance.holding@example.com` → `403 Forbidden`
- `finance.company1@example.com` → `403 Forbidden`
- `employee1@example.com` → `403 Forbidden`

### 1C. Tanpa login: redirect ke login

1. Logout
2. Buka `http://localhost:8000/role-menus`
3. **Ekspektasi**: Redirect ke `/login`

---

## SKENARIO 2 — Tampilan Sidebar Default per Role

### 2A. Default menu Holding Admin

**Login sebagai**: `holding.admin@example.com`

1. Perhatikan sidebar kiri
2. **Ekspektasi sidebar berisi** (urutan dari atas):
   - Projects
   - Task Projects
   - Rekap Project
   - Manajemen User
   - **Manajemen Role Menu** ← menu eksklusif holding admin
   - Review BP
   - Semua BP
   - Ajukan BP
   - Daftar BP Saya
   - Rekening
   - Master (submenu lipat: Kategori BP, Akun, Pajak)
   - My Tasks
   - Overdue Tasks

### 2B. Default menu Company Admin

**Login sebagai**: `company.admin1@example.com`

1. Perhatikan sidebar
2. **Ekspektasi sidebar berisi**:
   - Projects
   - Task Projects
   - Rekap Project
   - Manajemen User
   - Ajukan BP
   - Daftar BP Saya
   - Rekening
   - Master ▾ (Kategori BP, Akun, Pajak)
3. **Tidak ada**: Review BP, Semua BP, My Tasks, Overdue Tasks, Manajemen Role Menu

### 2C. Default menu Finance Holding

**Login sebagai**: `finance.holding@example.com`

1. **Ekspektasi sidebar berisi**:
   - Projects
   - Rekap Project
   - Review BP
   - Semua BP
2. **Tidak ada**: Manajemen User, Task Projects, My Tasks, master items, dll.

### 2D. Default menu Finance Company

**Login sebagai**: `finance.company1@example.com`

1. **Ekspektasi sidebar berisi**:
   - Projects
   - Ajukan BP
   - Daftar BP Saya
   - Rekening
   - Master ▾ (Kategori BP, Akun, Pajak)

### 2E. Default menu Employee

**Login sebagai**: `employee1@example.com`

1. **Ekspektasi sidebar berisi**:
   - Projects
   - Task Projects
   - My Tasks
   - Overdue Tasks

---

## SKENARIO 3 — Membuka Form Edit Role Menu

**Login sebagai**: `holding.admin@example.com`

### 3A. Buka form edit Company Admin

1. Buka `http://localhost:8000/role-menus`
2. Klik **"Atur Menu Role Ini"** pada card **Company Admin**
3. **Ekspektasi**:
   - URL berubah ke `/role-menus/company_admin/edit`
   - Judul halaman: **"Atur Menu: Company Admin"**
   - **Panel kiri** menampilkan info role (label, badge, jumlah menu terpilih)
   - **Panel kanan** menampilkan daftar semua menu sebagai checkbox card
   - Menu yang saat ini aktif memiliki **checkbox checked** dan card berwarna biru muda
   - Menu yang tidak aktif memiliki **checkbox unchecked** dan card berwarna abu-abu
   - Ada tombol **"Pilih Semua"** dan **"Hapus Semua"** di header panel kanan
   - Bagian **"Menu Master Data"** terpisah di bawah (Kategori BP, Akun, Pajak) dengan keterangan submenu

### 3B. Buka form edit Finance Holding

1. Kembali ke `/role-menus`
2. Klik **"Atur Menu Role Ini"** pada card **Finance Holding**
3. **Ekspektasi**: URL `/role-menus/finance_holding/edit`, hanya 4 menu yang checked (projects, project_recap, review_bp, all_bp)

### 3C. Coba akses edit holding_admin (404)

1. Coba buka langsung: `http://localhost:8000/role-menus/holding_admin/edit`
2. **Ekspektasi**: `404 Not Found` — holding_admin tidak bisa dikonfigurasi

### 3D. Akses form oleh role non-holding_admin (403)

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/role-menus/company_admin/edit`
2. **Ekspektasi**: `403 Forbidden`

---

## SKENARIO 4 — Mengubah Konfigurasi Menu

**Login sebagai**: `holding.admin@example.com`

### 4A. Tambah menu ke Employee

**Setup**: Login sebagai employee dulu untuk melihat sidebar awal.

Buka tab baru → login sebagai `employee1@example.com` → catat sidebar (default: Projects, Task Projects, My Tasks, Overdue Tasks).

Kembali ke tab holding admin:

1. Buka `/role-menus/employee/edit`
2. **Tambahkan** centang pada: **"Rekap Project"** dan **"Task Projects"** (sudah checked), tambah **"Bank Accounts"**
3. Klik **"Simpan Perubahan"**
4. **Ekspektasi**:
   - Redirect ke `/role-menus`
   - Flash sukses: **"Menu untuk role "Employee" berhasil diperbarui."**
   - Card Employee di index menampilkan badge baru "Rekening"
5. **Verifikasi di akun Employee**: Refresh halaman di tab employee
6. **Ekspektasi**: Sidebar employee sekarang ada menu **"Rekening"** (butuh refresh karena cache)

### 4B. Hapus menu dari Company Admin

1. Buka `/role-menus/company_admin/edit`
2. **Hapus centang** pada: **"Manajemen User"** dan **"Rekap Project"**
3. Klik **"Simpan Perubahan"**
4. **Verifikasi di akun Company Admin**: Login sebagai `company.admin1@example.com`
5. **Ekspektasi**: Sidebar **tidak ada** "Manajemen User" dan "Rekap Project"
6. **Verifikasi route masih dapat diakses**: Buka langsung `http://localhost:8000/users` di akun company admin
7. **Ekspektasi**: Halaman tetap dapat diakses (menu tersembunyi bukan berarti route diblokir, Policy yang mengatur)

> **Catatan penting**: Fitur ini mengatur **tampilan menu sidebar**, bukan **otorisasi route**. Policy tetap berlaku secara independen.

### 4C. Gunakan tombol "Pilih Semua"

1. Buka `/role-menus/employee/edit`
2. Klik tombol **"Hapus Semua"**
3. **Ekspektasi**: Semua checkbox menjadi unchecked, counter di panel kiri menampilkan `0 / 14 menu`
4. Klik tombol **"Pilih Semua"**
5. **Ekspektasi**: Semua checkbox menjadi checked, counter berubah menjadi `14 / 14 menu`
6. Klik **"Simpan Perubahan"**
7. Login sebagai `employee1@example.com` → **Ekspektasi**: Sidebar menampilkan semua 14 menu

### 4D. Simpan dengan 0 menu terpilih

1. Buka `/role-menus/finance_company/edit`
2. Klik **"Hapus Semua"**
3. Klik **"Simpan Perubahan"**
4. Login sebagai `finance.company1@example.com`
5. **Ekspektasi**: Sidebar kosong, tampil teks _"Tidak ada menu"_

### 4E. Toggle visual on/off saat klik checkbox (tanpa submit)

1. Buka `/role-menus/company_admin/edit`
2. Klik checkbox yang sedang **checked** (misalnya "Projects")
3. **Ekspektasi**: Card langsung berubah warna dari **biru muda** ke **abu-abu** tanpa reload halaman
4. Counter di panel kiri berkurang 1
5. Klik kembali checkbox tersebut
6. **Ekspektasi**: Card kembali berwarna **biru muda**, counter bertambah 1

---

## SKENARIO 5 — Master Submenu Dinamis

### 5A. Hanya sebagian Master aktif → submenu tampil dengan item terpilih saja

**Login sebagai**: `holding.admin@example.com`

1. Buka `/role-menus/employee/edit`
2. Aktifkan hanya **"Kategori BP"** dari bagian Master (tanpa Akun dan Pajak)
3. Simpan
4. Login sebagai `employee1@example.com`
5. **Ekspektasi**: Di sidebar ada submenu **"Master"** yang ketika diklik/di-hover hanya menampilkan **"Kategori BP"** (bukan Akun atau Pajak)

### 5B. Semua Master aktif → semua muncul di submenu

1. Dari holding admin, buka `/role-menus/employee/edit`
2. Aktifkan **Kategori BP, Akun, dan Pajak**
3. Simpan
4. Login sebagai `employee1@example.com`
5. **Ekspektasi**: Submenu "Master" berisi 3 item: Kategori BP, Akun, Pajak

### 5C. Tidak ada Master aktif → submenu tidak muncul sama sekali

1. Dari holding admin, pada role employee, hapus semua master items
2. Simpan
3. Login sebagai employee
4. **Ekspektasi**: Tidak ada submenu **"Master"** sama sekali di sidebar

---

## SKENARIO 6 — Holding Admin Menu Selalu Fixed

### 6A. Holding Admin tidak punya tombol edit di card-nya

**Login sebagai**: `holding.admin@example.com`

1. Buka `/role-menus`
2. Lihat card **"Holding Admin"** (border kuning)
3. **Ekspektasi**:
   - Card diberi warna border kuning (warning)
   - Tombol di footer card bertulisan **"Akses Penuh — Tidak Dapat Diubah"** dan **disabled** (tidak bisa diklik)
   - Semua menu ditampilkan sebagai badge termasuk "Manajemen Role Menu"

### 6B. Menu Manajemen Role Menu HANYA tampil untuk Holding Admin

**Login sebagai**: `company.admin1@example.com`

1. Perhatikan sidebar
2. **Ekspektasi**: Tidak ada **"Manajemen Role Menu"** di sidebar company admin

Ulangi untuk finance_holding, finance_company, employee → semua tidak boleh memiliki menu ini.

---

## SKENARIO 7 — Isolasi Company pada Manajemen Role Menu

> Fitur ini bersifat **global per role**, bukan per company. Artinya, jika `company_admin` dikonfigurasi memiliki menu X, maka SEMUA `company_admin` di semua company akan mendapatkan menu X.

### 7A. Perubahan berlaku untuk semua company dengan role yang sama

**Login sebagai**: `holding.admin@example.com`

1. Tambah menu **"Rekap Project"** ke role `employee`
2. Simpan
3. Login sebagai `employee1@example.com` (dari company MCB) → verifikasi menu "Rekap Project" ada
4. Login sebagai `employee2@example.com` (dari company JDC) → **Ekspektasi sama**: menu "Rekap Project" juga ada

---

## SKENARIO 8 — Ketahanan & Edge Cases

### 8A. POST manipulation: kirim menu key yang tidak valid

Coba manipulasi form untuk kirim key yang tidak ada di katalog (misal `fake_menu`):

1. Buka developer tools → inspect form di `/role-menus/employee/edit`
2. Tambahkan input hidden: `<input name="menu_keys[]" value="fake_menu">`
3. Submit form
4. **Ekspektasi**: Key `fake_menu` **diabaikan** — hanya key yang valid dari katalog yang tersimpan

### 8B. PUT ke role yang tidak valid (404)

Coba akses: `http://localhost:8000/role-menus/nonexistent_role/edit`

**Ekspektasi**: `404 Not Found`

### 8C. Cache invalidation setelah simpan

1. Buka sidebar sebagai company_admin (cache di-load)
2. Dari holding admin, ubah konfigurasi company_admin
3. Refresh halaman di akun company_admin
4. **Ekspektasi**: Sidebar langsung mencerminkan perubahan (cache di-invalidate saat save)

### 8D. Setelah migrate:fresh --seed → konfigurasi kembali ke default

```bash
php artisan migrate:fresh --seed
```

Login sebagai setiap role → **Ekspektasi**: Menu kembali ke konfigurasi default seperti Skenario 2.

---

## SKENARIO 9 — End-to-End Flow Lengkap

**Login sebagai**: `holding.admin@example.com`

### Langkah 1 — Lihat konfigurasi awal

1. Buka `/role-menus`
2. Catat jumlah menu tiap role (sesuai default dari tabel di Latar Belakang)

### Langkah 2 — Konfigurasi ulang Employee menjadi "power user"

1. Klik "Atur Menu Role Ini" pada card **Employee**
2. Klik **"Pilih Semua"**
3. Simpan
4. **Ekspektasi**: Card Employee di index menampilkan progress bar 100% dan 14 badge menu

### Langkah 3 — Verifikasi tampilan sidebar employee

1. Login sebagai `employee1@example.com`
2. **Ekspektasi**: Sidebar rami — semua 14 menu tampil termasuk Master submenu

### Langkah 4 — Reset Employee ke hanya task-related

1. Login kembali sebagai holding admin → `/role-menus/employee/edit`
2. Klik **"Hapus Semua"**
3. Centang manual: **My Tasks** dan **Overdue Tasks** saja
4. Simpan

### Langkah 5 — Verifikasi penyederhanaan

1. Login sebagai `employee1@example.com`
2. **Ekspektasi**: Sidebar hanya ada 2 menu: **My Tasks** dan **Overdue Tasks**
3. Coba akses langsung: `http://localhost:8000/projects` → **Ekspektasi**: Halaman tetap bisa diakses (route diproteksi Policy, bukan menu)

### Langkah 6 — Kembali ke konfigurasi default

1. Holding admin: `/role-menus/employee/edit`
2. Set kembali: Projects, Task Projects, My Tasks, Overdue Tasks
3. Simpan
4. Refresh sidebar employee → **Ekspektasi**: kembali ke 4 menu default

---

## Ringkasan Checklist Testing

| # | Skenario | Akun | Ekspektasi | Status |
|---|---|---|---|---|
| 1A | Holding admin akses `/role-menus` | `holding.admin` | Halaman tampil, 4 card + 1 card holding fixed | ☐ |
| 1B | Non-holding admin akses `/role-menus` | `company.admin1` | 403 | ☐ |
| 1B | Non-holding admin akses `/role-menus` | `finance.holding` | 403 | ☐ |
| 1B | Non-holding admin akses `/role-menus` | `employee1` | 403 | ☐ |
| 1C | Tanpa login | Guest | Redirect ke `/login` | ☐ |
| 2A | Default sidebar holding_admin | `holding.admin` | Semua menu + Manajemen Role Menu | ☐ |
| 2B | Default sidebar company_admin | `company.admin1` | 8 menu, tanpa Review BP dan My Tasks | ☐ |
| 2C | Default sidebar finance_holding | `finance.holding` | 4 menu: Projects, Rekap, Review BP, Semua BP | ☐ |
| 2D | Default sidebar finance_company | `finance.company1` | 5 menu: Projects, BP, Rekening, Master | ☐ |
| 2E | Default sidebar employee | `employee1` | 4 menu: Projects, Task Projects, My Tasks, Overdue | ☐ |
| 3A | Buka form edit company_admin | `holding.admin` | Form tampil, checkbox sesuai default | ☐ |
| 3C | Buka edit holding_admin | `holding.admin` | 404 Not Found | ☐ |
| 3D | Non-holding akses form edit | `company.admin1` | 403 | ☐ |
| 4A | Tambah menu ke Employee | `holding.admin` | Employee sidebar bertambah | ☐ |
| 4B | Hapus menu dari Company Admin | `holding.admin` | Sidebar company admin berkurang | ☐ |
| 4C | Tombol Pilih Semua / Hapus Semua | `holding.admin` | Counter real-time berubah | ☐ |
| 4D | Simpan 0 menu terpilih | `holding.admin` | Sidebar kosong, pesan "Tidak ada menu" | ☐ |
| 4E | Toggle visual checkbox | `holding.admin` | Warna card berubah tanpa reload | ☐ |
| 5A | Master submenu partial | `holding.admin` | Hanya item terpilih muncul | ☐ |
| 5B | Semua master aktif | `holding.admin` | Submenu berisi 3 item | ☐ |
| 5C | Tidak ada master aktif | `holding.admin` | Submenu "Master" tidak muncul | ☐ |
| 6A | Card holding admin tidak bisa diedit | `holding.admin` | Tombol disabled | ☐ |
| 6B | Non-holding tidak lihat menu Manajemen Role | semua non-admin | Tidak ada menu tersebut di sidebar | ☐ |
| 7A | Perubahan berlaku lintas company | `holding.admin` | employee1 & employee2 mendapat menu sama | ☐ |
| 8A | Key tidak valid diabaikan | `holding.admin` | `fake_menu` tidak tersimpan | ☐ |
| 8B | Role tidak valid → 404 | `holding.admin` | 404 | ☐ |
| 8C | Cache invalidation | `holding.admin` | Perubahan langsung terlihat setelah refresh | ☐ |
| 9 | End-to-end flow lengkap | semua | Semua langkah pass | ☐ |
