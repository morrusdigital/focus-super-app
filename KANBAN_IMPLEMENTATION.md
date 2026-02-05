# Fitur Kanban Board - Focus Super App

## Overview
Fitur Kanban Board lengkap dengan milestone tracking, checklist, dan drag-and-drop functionality terintegrasi dengan template Enzo yang sudah ada.

## Fitur yang Sudah Dibuat

### 1. Database & Models ✅
- **Projects**: Project management dengan company, manager, timeline, budget
- **Boards**: Kanban board untuk setiap project (auto-created)
- **Columns**: 7 kolom default (Backlog, To Do, In Progress, Review, Testing, Done, Cancelled)
- **Cards**: Task cards dengan priority, assignee, due date, progress tracking
- **Checklists**: Checklist untuk setiap card
- **Checklist Items**: Item dalam checklist dengan auto-progress update
- **Milestones**: 5 milestone default per project

### 2. Backend Web Controllers ✅
- `ProjectController`: Full CRUD untuk projects + method `board()` untuk menampilkan Kanban
- `CardController`: Method `store()` untuk menambahkan card dari web interface

### 3. Views (Blade Templates) ✅
Semua view sudah mengikuti template Enzo yang ada:

#### Projects
- `resources/views/projects/index.blade.php`: List projects dengan filter, status badges
- `resources/views/projects/create.blade.php`: Form create project
- `resources/views/projects/show.blade.php`: Detail project dengan milestone
- `resources/views/projects/edit.blade.php`: Form edit project
- `resources/views/projects/board.blade.php`: Kanban board dengan drag & drop

#### Features di Board View:
- Drag & drop cards antar kolom
- Add card modal per kolom
- Milestone checkbox dengan API toggle
- Card progress bar (dari checklist)
- Priority badges (Low, Medium, High, Urgent)
- Due date dengan overdue indicator
- Assignee avatar
- Card count per column
- Responsive horizontal scroll

### 4. Routes ✅
**Web Routes** (`routes/web.php`):
```php
Route::resource('projects', ProjectController::class);
Route::get('projects/{project}/board', [ProjectController::class, 'board']);
Route::post('projects/{project}/cards', [CardController::class, 'store']);
```

**API Routes** (`routes/api.php`):
- `/api/projects/{project}/boards/{board}/cards/{card}/move` - Move card (drag & drop)
- `/api/milestones/{milestone}/toggle` - Toggle milestone completion
- Plus semua CRUD endpoints untuk cards, checklists, checklist items

### 5. Authorization ✅
- `ProjectPolicy`: Admin & Manager bisa create/update/delete
- `BoardPolicy`: Semua company members bisa view
- `CardPolicy`: Admin, Manager, dan Assignee bisa update

### 6. Navigation ✅
Menu sidebar sudah ditambahkan di `layouts/app.blade.php`:
- **Projects** menu item dengan icon briefcase
- Active state sesuai route

### 7. Notifications & Events ✅
- `CardAssignedNotification`: Email notification saat card di-assign
- `CardDueSoonNotification`: Reminder 48 jam sebelum deadline
- `CardOverdueNotification`: Notification untuk overdue cards
- `KanbanCardMoved`: Broadcasting untuk real-time updates
- `SendCardDueReminders`: Scheduled job untuk automated reminders

### 8. Testing ✅
Feature tests untuk:
- Project CRUD (5 tests) ✅
- Card movement & progress (5 tests) ✅
- Checklist & auto-progress (5 tests) ✅
- Dashboard portfolio (1 test) ✅

## Cara Menggunakan

### 1. Akses Menu
Setelah login, klik menu **"Projects"** di sidebar kiri

### 2. Create Project
1. Klik tombol **"Tambah Project"**
2. Isi form:
   - Nama Project
   - Deskripsi
   - Project Manager
   - Status (Planning/Active/On Hold/Completed/Cancelled)
   - Timeline (Start & End Date)
   - Budget
3. Klik **"Simpan Project"**
4. Otomatis akan redirect ke Kanban Board dengan:
   - 7 kolom default
   - 5 milestone default

### 3. Kanban Board
Di halaman board, Anda bisa:

#### Menambah Card:
1. Klik tombol **"Add Card"** di bawah kolom yang diinginkan
2. Isi detail card (title, description, priority, due date, assignee)
3. Submit

#### Drag & Drop:
1. Click & hold card yang ingin dipindah
2. Drag ke kolom tujuan
3. Drop
4. Perubahan otomatis tersimpan ke database

#### Toggle Milestone:
1. Lihat panel Milestones di kanan atas
2. Klik checkbox untuk mark complete/incomplete
3. Status otomatis tersimpan via API

#### Melihat Detail Card:
Klik card untuk ke halaman detail (fitur ini bisa dikembangkan lebih lanjut)

### 4. Edit Project
1. Di list projects, klik icon **Edit** (pensil)
2. Update informasi
3. Klik **"Update Project"**

## Tech Stack
- **Backend**: Laravel 11, PostgreSQL
- **Frontend**: Blade Templates, Bootstrap 5, jQuery
- **Drag & Drop**: Native HTML5 Drag & Drop API
- **Icons**: Feather Icons
- **Authentication**: Laravel Session Auth
- **Real-time**: Laravel Broadcasting (optional, sudah disiapkan)

## Database Seeding
Untuk testing, jalankan:
```bash
php artisan db:seed --class=KanbanSeeder
```

Akan create:
- 1 company
- 3 users (admin, manager, member)
- 1 project dengan full board
- 10 cards dengan berbagai status
- Checklists dan items

## API Endpoints (Optional)
Jika ingin integrate dengan mobile app atau SPA:

### Projects
- `GET /api/projects` - List projects
- `POST /api/projects` - Create project
- `GET /api/projects/{id}` - Show project
- `PUT /api/projects/{id}` - Update project
- `DELETE /api/projects/{id}` - Delete project

### Boards
- `GET /api/projects/{project}/boards/{board}` - Get board with columns & cards

### Cards
- `GET /api/projects/{project}/boards/{board}/cards` - List cards
- `POST /api/projects/{project}/boards/{board}/cards` - Create card
- `PATCH /api/projects/{project}/boards/{board}/cards/{card}/move` - Move card
- `PUT /api/projects/{project}/boards/{board}/cards/{card}` - Update card
- `DELETE /api/projects/{project}/boards/{board}/cards/{card}` - Delete card

### Dashboard
- `GET /api/dashboard` - Portfolio summary statistics

## Auto-Generated Features
Saat create project, otomatis dibuat:

### 7 Kolom Default:
1. 📋 Backlog
2. ✅ To Do
3. ⚙️ In Progress
4. 👀 Review
5. 🧪 Testing
6. ✔️ Done
7. ❌ Cancelled

### 5 Milestone Default:
1. Project Initiation
2. Planning & Design
3. Development
4. Testing & QA
5. Deployment & Launch

## Customization
Jika ingin mengubah default columns/milestones, edit di `app/Models/Project.php` method `booted()`.

## Notes
- Drag & drop menggunakan native HTML5, tidak perlu library tambahan
- Progress cards dihitung otomatis dari checklist completion
- Overdue indicator otomatis muncul jika due_date terlewat
- Authorization sudah terintegrasi dengan Laravel Policies
- Flash messages (success/error) sudah ditambahkan di layout

## Next Steps (Opsional)
- [ ] Card detail modal (in-place editing tanpa redirect)
- [ ] Filter cards by assignee/priority/due date
- [ ] Attach files ke cards
- [ ] Comments system di cards
- [ ] Activity log/history per project
- [ ] Export board to PDF/Excel
- [ ] Real-time collaboration dengan Pusher/WebSocket
- [ ] Dashboard portfolio dengan charts (sudah ada API-nya)
- [ ] Email digest (weekly summary)
- [ ] Mobile responsive improvements
