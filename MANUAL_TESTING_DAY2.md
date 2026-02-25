# Manual Testing Guide — Day 2 (User Management Module)

> **Base URL**: `http://localhost:8000`  
> **Semua password**: `password`  
> **Issues**: #28 · #29 · #30 · #31 · #32

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

Perintah ini membuat:

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
| ... | (MDC, DF, FTC: pola sama dengan nomor 3–5) | ... | ... |

> **Akun utama yang dipakai testing ini:**
> - `holding.admin@example.com` — akses penuh ke semua company
> - `company.admin1@example.com` — akses terbatas ke MCB saja
> - `finance.holding@example.com` — tidak punya akses user management
> - `employee1@example.com` — tidak punya akses user management

---

## URL Referensi

| Aksi | URL | Method |
|---|---|---|
| Daftar user | `/users` | GET |
| Form tambah user | `/users/create` | GET/POST |
| Detail user | `/users/{id}` | GET |
| Form edit user | `/users/{id}/edit` | GET/PUT |
| Toggle aktif/nonaktif | `/users/{id}/activate` | POST |
| Reset password | `/users/{id}/reset-password` | POST |

---

## Matriks Authorization User Management

| Aksi | `holding_admin` | `company_admin` | `finance_holding` | `finance_company` | `employee` |
|---|:---:|:---:|:---:|:---:|:---:|
| Lihat daftar user | ✅ semua | ✅ company sendiri | ❌ | ❌ | ❌ |
| Lihat detail user | ✅ semua | ✅ company sendiri | ❌ | ❌ | ❌ |
| Tambah user | ✅ semua company | ✅ company sendiri | ❌ | ❌ | ❌ |
| Edit user | ✅ semua | ✅ company sendiri | ❌ | ❌ | ❌ |
| Toggle aktif/nonaktif | ✅ semua | ✅ company sendiri | ❌ | ❌ | ❌ |
| Reset password | ✅ semua | ✅ company sendiri | ❌ | ❌ | ❌ |

---

## SKENARIO 1 — Akses Halaman Daftar User (Issue #28 · #29)

### 1A. Holding Admin: melihat semua user

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/users`
2. **Ekspektasi**:
   - Halaman tampil berisi daftar user dari **semua company**
   - Ada kolom: **Nama, Email, Role, Perusahaan, Status, Aksi**
   - Setiap baris user menampilkan badge status **Aktif** (hijau)
   - Ada tombol **Tambah User** di pojok kanan atas
   - Tertera jumlah total user, misalnya `(17 total)` atau sesuai jumlah seeder

### 1B. Company Admin: hanya melihat user perusahaannya

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/users`
2. **Ekspektasi**:
   - Hanya user dengan company **MCB** yang tampil
   - User dari JDC, MDC, DF, FTC **tidak muncul**
   - Tidak ada dropdown filter **Perusahaan** (hanya muncul untuk holding_admin)

### 1C. User tanpa izin: akses ditolak

**Login sebagai**: `finance.holding@example.com`

1. Buka `http://localhost:8000/users`
2. **Ekspektasi**: `403 Forbidden`

**Uji ulang untuk `employee1@example.com`** → sama, harus `403 Forbidden`.

**Tanpa login:**

1. Logout terlebih dahulu
2. Buka `http://localhost:8000/users`
3. **Ekspektasi**: Redirect ke `/login`

---

## SKENARIO 2 — Filter & Pagination Daftar User (Issue #29)

**Login sebagai**: `holding.admin@example.com`

### 2A. Filter berdasarkan Nama

1. Buka `http://localhost:8000/users`
2. Di kolom filter **Nama**, ketik `Admin`
3. Klik **Filter**
4. **Ekspektasi**: Hanya user yang namanya mengandung kata "Admin" yang tampil

### 2B. Filter berdasarkan Role

1. Di dropdown **Role**, pilih `company_admin`
2. Klik **Filter**
3. **Ekspektasi**: Hanya user dengan role `company_admin` yang tampil

### 2C. Filter berdasarkan Status

1. Di dropdown **Status**, pilih `Aktif`
2. Klik **Filter**
3. **Ekspektasi**: Hanya user dengan status aktif yang tampil

### 2D. Filter berdasarkan Perusahaan (Holding Admin saja)

1. Di dropdown **Perusahaan**, pilih `MCB`
2. Klik **Filter**
3. **Ekspektasi**: Hanya user dari MCB yang tampil

### 2E. Kombinasi filter

1. Pilih Role = `employee`, Perusahaan = `MCB`
2. Klik **Filter**
3. **Ekspektasi**: Tampil hanya employee dari MCB

### 2F. Reset filter

1. Klik tombol **Reset**
2. **Ekspektasi**: Semua filter bersih, semua user tampil kembali

### 2G. Company Admin tidak bisa filter lintas company

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/users`
2. **Ekspektasi**: Filter dropdown **Perusahaan tidak ada** — field ini hanya muncul untuk holding_admin
3. Meski URL dimanipulasi dengan `?company_id=2`, hasil tetap terkunci ke MCB saja

---

## SKENARIO 3 — Tambah User (Issue #30)

### 3A. Holding Admin: tambah user ke company manapun

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/users/create`
2. **Ekspektasi**: Form tampil dengan field **Nama, Email, Password, Konfirmasi Password, Role, Perusahaan**
3. Isi:
   - **Nama**: `Uji Tambah`
   - **Email**: `uji.tambah@example.com`
   - **Password**: `password123`
   - **Konfirmasi Password**: `password123`
   - **Role**: `finance_company`
   - **Perusahaan**: `MCB`
4. Klik **Simpan**
5. **Ekspektasi**: Redirect ke `/users`, flash sukses muncul, user `Uji Tambah` tampil di daftar

### 3B. Company Admin: tambah user (tidak dapat memilih perusahaan)

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/users/create`
2. **Ekspektasi**: Form **tidak ada** dropdown Perusahaan (otomatis ke company sendiri)
3. Isi:
   - **Nama**: `Uji Company Admin`
   - **Email**: `uji.ca@example.com`
   - **Password**: `password123`
   - **Konfirmasi Password**: `password123`
   - **Role**: `employee`
4. Klik **Simpan**
5. **Ekspektasi**: User tersimpan dengan company **MCB** secara otomatis

### 3C. Anti privilege escalation: company admin tidak bisa assign role tinggi

**Login sebagai**: `company.admin1@example.com`

1. Buka `http://localhost:8000/users/create`
2. **Ekspektasi**: Dropdown Role hanya menampilkan **3 pilihan**: `company_admin`, `finance_company`, `employee`
3. Role `holding_admin` dan `finance_holding` **tidak tampil** di dropdown

### 3D. Validasi form — field wajib

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/users/create`
2. Klik **Simpan** tanpa mengisi apapun
3. **Ekspektasi**: Muncul pesan error di bawah setiap field wajib (nama, email, password, role, perusahaan)

### 3E. Validasi — password tidak cocok

1. Isi nama, email, dan role
2. **Password**: `password123`, **Konfirmasi**: `salah123`
3. Klik **Simpan**
4. **Ekspektasi**: Error "Konfirmasi password tidak cocok"

### 3F. Validasi — password kurang dari 8 karakter

1. **Password**: `abc`, **Konfirmasi**: `abc`
2. **Ekspektasi**: Error "Password minimal 8 karakter"

### 3G. Validasi — email sudah terpakai

1. Isi email dengan yang sudah ada, misal `holding.admin@example.com`
2. **Ekspektasi**: Error "Email sudah terdaftar"

---

## SKENARIO 4 — Edit User (Issue #30)

### 4A. Edit user sendiri atau dalam scope

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/users`
2. Klik **Detail** pada user `company.admin1@example.com`
3. Catat **ID** dari URL, misal `/users/3` → `{user_id} = 3`
4. Buka `http://localhost:8000/users/{user_id}/edit`
5. **Ekspektasi**: Form tampil dengan **Nama, Email, Role** — **tidak ada field Perusahaan**
6. Ubah **Nama** menjadi `Company Admin Satu`
7. Klik **Simpan**
8. **Ekspektasi**: Redirect ke halaman detail, perubahan nama tersimpan

### 4B. Company admin edit user beda company: ditolak

**Login sebagai**: `company.admin1@example.com`

1. Cari ID user dari JDC (bukan MCB) melalui URL atau dari sesi holding admin
2. Buka `http://localhost:8000/users/{id_user_jdc}/edit`
3. **Ekspektasi**: `403 Forbidden`

### 4C. Anti privilege escalation: tidak bisa mengubah company via edit

**Login sebagai**: `holding.admin@example.com`

1. Buka form edit user manapun
2. **Ekspektasi**: Field **Perusahaan tidak ada** di form edit (company tidak bisa diubah setelah dibuat)

---

## SKENARIO 5 — Aktivasi & Deaktivasi User (Issue #31)

### 5A. Toggle nonaktifkan user

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/users`
2. Klik **Detail** pada user `employee1@example.com`
3. Halaman detail akan menampilkan badge **Aktif** dan tombol **Nonaktifkan**
4. Klik tombol **Nonaktifkan**
5. **Ekspektasi**:
   - Redirect kembali ke halaman detail
   - Flash sukses muncul: "User berhasil dinonaktifkan"
   - Badge status berubah menjadi **Nonaktif** (abu-abu)
   - Tombol berubah menjadi **Aktifkan**

### 5B. Tampilan status di daftar user

1. Kembali ke `http://localhost:8000/users`
2. **Ekspektasi**: Badge status `employee1` menampilkan **Nonaktif** (abu-abu)

### 5C. Filter status nonaktif

1. Di filter **Status**, pilih `Nonaktif`
2. Klik **Filter**
3. **Ekspektasi**: Hanya `employee1` yang muncul (atau user nonaktif lainnya)

### 5D. Login sebagai user nonaktif: ditolak dengan pesan jelas

1. Logout dari akun holding admin
2. Buka `http://localhost:8000/login`
3. Login dengan: `employee1@example.com` / `password`
4. **Ekspektasi**:
   - Login **gagal**, tidak masuk ke aplikasi
   - Muncul pesan error di form: **"Akun Anda telah dinonaktifkan. Silakan hubungi administrator."**
   - Berbeda dengan pesan salah password: "Email atau kata sandi salah."

### 5E. Reaktivasi user → bisa login kembali

**Login sebagai**: `holding.admin@example.com`

1. Buka detail `employee1`
2. Klik tombol **Aktifkan**
3. **Ekspektasi**: Badge kembali menjadi **Aktif**, flash sukses muncul
4. Logout, lalu coba login kembali sebagai `employee1@example.com` / `password`
5. **Ekspektasi**: Login **berhasil**, masuk ke dashboard

### 5F. Company Admin: hanya bisa toggle user di company sendiri

**Login sebagai**: `company.admin1@example.com`

1. Buka detail user dari MCB → tombol **Nonaktifkan** tampil ✅
2. Coba akses langsung URL `/users/{id_user_jdc}/activate` via browser (POST tidak bisa via URL biasa, tapi bisa dicoba lewat form manipulation)
3. **Ekspektasi**: `403 Forbidden`

### 5G. Non-admin tidak bisa toggle

**Login sebagai**: `finance.holding@example.com`

1. Buka detail user manapun
2. **Ekspektasi**: Tombol **Nonaktifkan / Aktifkan tidak tampil**
3. Jika paksa akses POST `/users/{id}/activate`: `403 Forbidden`

---

## SKENARIO 6 — Reset Password User (Issue #32)

### 6A. Holding Admin: reset password user manapun

**Login sebagai**: `holding.admin@example.com`

1. Buka `http://localhost:8000/users`
2. Klik **Detail** pada user `employee1@example.com`
3. Scroll ke bawah → akan ada card **Reset Password**
4. Isi:
   - **Password Baru**: `newpassword123`
   - **Konfirmasi Password**: `newpassword123`
5. Klik **Reset Password**
6. **Ekspektasi**:
   - Redirect ke halaman detail yang sama
   - Flash sukses: "Password berhasil direset"
7. Logout, kemudian login sebagai `employee1@example.com` dengan password `newpassword123`
8. **Ekspektasi**: Login **berhasil** — membuktikan password berubah

### 6B. Password tersimpan sebagai hash (bukan plaintext)

Ini dilakukan lewat tinker, bukan UI — tetapi dapat diverifikasi dengan percobaan login:

1. Reset password `employee1` menjadi `securepass`
2. Login berhasil dengan `securepass` → membuktikan password di-hash saat tersimpan (bukan plaintext)
3. Login dengan password lama `password` → **gagal** → membuktikan hash berubah

### 6C. Validasi FormRequest

**Login sebagai**: `holding.admin@example.com`

Buka halaman detail user manapun, scroll ke card **Reset Password**.

**Test 1 — Password wajib diisi:**

1. Kosongkan semua field, klik **Reset Password**
2. **Ekspektasi**: Error "Password baru wajib diisi"

**Test 2 — Minimal 8 karakter:**

1. Isi **Password Baru**: `abc` (kurang dari 8 karakter), Konfirmasi: `abc`
2. Klik **Reset Password**
3. **Ekspektasi**: Error "Password minimal 8 karakter"

**Test 3 — Konfirmasi tidak cocok:**

1. Isi **Password Baru**: `newpassword123`, **Konfirmasi**: `salahkonfirmasi`
2. Klik **Reset Password**
3. **Ekspektasi**: Error "Konfirmasi password tidak cocok"

**Test 4 — Tepat 8 karakter: valid:**

1. Isi **Password Baru**: `pass1234` (8 karakter), **Konfirmasi**: `pass1234`
2. **Ekspektasi**: Reset berhasil

### 6D. Company Admin: hanya bisa reset password user di company sendiri

**Login sebagai**: `company.admin1@example.com`

1. Buka detail user MCB → card Reset Password **tampil** ✅
2. Coba buka detail user JDC → `403 Forbidden` (card tidak pernah muncul)

### 6E. Non-admin tidak bisa reset password

**Login sebagai**: `finance.holding@example.com`

1. Buka `http://localhost:8000/users/3`
2. **Ekspektasi**: `403 Forbidden` (tidak dapat akses halaman detail user sama sekali)

**Login sebagai**: `employee1@example.com`

1. Coba akses `http://localhost:8000/users/3`
2. **Ekspektasi**: `403 Forbidden`

### 6F. Cross-scope: ditolak 403

**Login sebagai**: `company.admin1@example.com` (MCB)

1. Cari ID user dari JDC (misal ID 6)
2. Paksa POST ke `http://localhost:8000/users/6/reset-password` dengan data valid
3. **Ekspektasi**: `403 Forbidden` — password user JDC **tidak berubah**

---

## SKENARIO 7 — Uji Lintas Skenario (End-to-End)

Skenario berikut menguji semua fitur Day 2 secara berurutan.

**Login sebagai**: `holding.admin@example.com`

### Langkah 1 — Buat user baru untuk MCB

1. Buka `http://localhost:8000/users/create`
2. Isi:
   - Nama: `Test User E2E`
   - Email: `test.e2e@example.com`
   - Password: `password123`
   - Konfirmasi: `password123`
   - Role: `employee`
   - Perusahaan: `MCB`
3. Simpan → user muncul di daftar dengan badge **Aktif**

### Langkah 2 — Edit user tersebut

1. Buka detail `Test User E2E` → klik Edit
2. Ubah nama menjadi `Test User E2E (Edited)`
3. Simpan → nama berubah di halaman detail

### Langkah 3 — Reset password

1. Di halaman detail, scroll ke card Reset Password
2. Set password baru: `resetpass123`
3. Submit → flash sukses

### Langkah 4 — Nonaktifkan user

1. Klik tombol **Nonaktifkan**
2. Badge berubah menjadi **Nonaktif**

### Langkah 5 — Verifikasi login ditolak

1. Logout
2. Login sebagai `test.e2e@example.com` / `resetpass123`
3. **Ekspektasi**: Error "Akun Anda telah dinonaktifkan…"

### Langkah 6 — Reaktivasi & login berhasil

1. Login kembali sebagai `holding.admin@example.com`
2. Aktifkan kembali `Test User E2E`
3. Logout, login sebagai `test.e2e@example.com` / `resetpass123`
4. **Ekspektasi**: Login **berhasil** masuk ke dashboard

---

## Ringkasan Checklist Testing Day 2

| # | Skenario | Akun | Ekspektasi | Status |
|---|---|---|---|---|
| 1A | Daftar user semua company | `holding.admin` | Semua user tampil | ☐ |
| 1B | Daftar user company sendiri | `company.admin1` | Hanya MCB tampil | ☐ |
| 1C | Akses ditolak | `finance.holding` | 403 | ☐ |
| 2A–2F | Filter nama/role/status/company | `holding.admin` | Filter bekerja | ☐ |
| 2G | Company admin tidak bisa filter lintas company | `company.admin1` | Terkunci MCB | ☐ |
| 3A | Tambah user (holding) | `holding.admin` | User tersimpan | ☐ |
| 3B | Tambah user (company) | `company.admin1` | Auto MCB | ☐ |
| 3C | Role terbatas untuk company admin | `company.admin1` | Hanya 3 role | ☐ |
| 3D–3G | Validasi form create | `holding.admin` | Error muncul | ☐ |
| 4A | Edit user | `holding.admin` | Nama berubah | ☐ |
| 4B | Edit user beda company: ditolak | `company.admin1` | 403 | ☐ |
| 4C | Company tidak bisa diubah via edit | `holding.admin` | No company field | ☐ |
| 5A | Nonaktifkan user | `holding.admin` | Badge nonaktif | ☐ |
| 5B | Status tampil di list | `holding.admin` | Badge terlihat | ☐ |
| 5D | Login user nonaktif ditolak | `employee1` | Pesan jelas | ☐ |
| 5E | Reaktivasi → bisa login | `holding.admin` + `employee1` | Login sukses | ☐ |
| 5F | Company admin tidak bisa toggle lintas company | `company.admin1` | 403 | ☐ |
| 6A | Reset password | `holding.admin` | Login dengan pass baru | ☐ |
| 6C-1 | Validasi: password wajib | `holding.admin` | Error | ☐ |
| 6C-2 | Validasi: minimal 8 karakter | `holding.admin` | Error | ☐ |
| 6C-3 | Validasi: konfirmasi tidak cocok | `holding.admin` | Error | ☐ |
| 6D | Reset hanya scope sendiri | `company.admin1` | 403 lintas company | ☐ |
| 7 | End-to-end flow | semua akun | Semua langkah pass | ☐ |
