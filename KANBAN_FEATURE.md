# Kanban Board Feature Documentation

## Overview

Fitur Kanban Board yang terintegrasi dengan sistem portfolio management, termasuk milestone tracking, checklist management, dan portfolio dashboard reporting.

## Features Implemented

### 1. Database & Models ✅
- **Migrations** untuk tables:
  - `projects` - Project management
  - `boards` - Kanban board per project
  - `columns` - Board columns (Backlog, To Do, In Progress, etc.)
  - `cards` - Task cards with assignee, due date, priority
  - `checklists` - Checklist groups per card
  - `checklist_items` - Individual checklist items
  - `milestones` - Project milestones
  - `notifications` - In-app notifications

- **Eloquent Models** dengan relasi lengkap:
  - Project → Board (one-to-one)
  - Project → Milestones (one-to-many)
  - Board → Columns (one-to-many)
  - Column → Cards (one-to-many)
  - Card → Checklists (one-to-many)
  - Checklist → ChecklistItems (one-to-many)
  - Card → User (assignee)

- **Auto-creation**: Saat Project dibuat, otomatis membuat:
  - 1 Board dengan 7 default columns
  - 5 default milestones (Inisiasi → Closing)

### 2. Backend API & Rules ✅

#### API Endpoints

**Projects**
```
GET    /api/projects              - List all projects
POST   /api/projects              - Create new project
GET    /api/projects/{id}         - Get project details
PUT    /api/projects/{id}         - Update project
DELETE /api/projects/{id}         - Delete project
```

**Boards**
```
GET    /api/projects/{project}/boards/{board} - Get board with columns and cards
```

**Cards**
```
GET    /api/projects/{project}/boards/{board}/cards        - List all cards
POST   /api/projects/{project}/boards/{board}/cards        - Create card
GET    /api/projects/{project}/boards/{board}/cards/{id}   - Get card details
PUT    /api/projects/{project}/boards/{board}/cards/{id}   - Update card
DELETE /api/projects/{project}/boards/{board}/cards/{id}   - Delete card
PATCH  /api/projects/{project}/boards/{board}/cards/{id}/move - Move card
```

**Checklists**
```
POST   /api/checklists                      - Create checklist
DELETE /api/checklists/{id}                 - Delete checklist
```

**Checklist Items**
```
POST   /api/checklist-items                 - Create item
PUT    /api/checklist-items/{id}            - Update item
DELETE /api/checklist-items/{id}            - Delete item
POST   /api/checklist-items/{id}/toggle     - Toggle completion
```

**Dashboard**
```
GET    /api/dashboard                       - Get portfolio dashboard data
```

#### Authorization Policies

- **ProjectPolicy**: Only manager/admin can create/update/delete
- **BoardPolicy**: Only project members can view
- **CardPolicy**: 
  - Anyone can create
  - Admin, project manager, or assignee can update/move
  - Admin or project manager can delete

### 3. Drag & Drop Logic ✅

**Backend** (`CardController@move`):
- Atomic reorder dalam DB transaction
- Handle movement within same column
- Handle movement to different column
- Broadcasts `KanbanCardMoved` event

**Frontend** (Vue components):
- Native HTML5 drag and drop
- Optimistic UI updates
- Real-time sync via Laravel Echo

### 4. Milestone & Checklist ✅

**Milestone Template**:
- Auto-created with project: Inisiasi → Perencanaan → Eksekusi → Monitoring → Closing
- Can mark as completed with timestamp
- Positioned sequentially

**Checklist Progress Calculation**:
- Automatically updates card progress when checklist items toggle
- Formula: `progress = (completed_items / total_items) * 100`
- Real-time update on item save/delete

### 5. Dashboard/Reporting ✅

Dashboard endpoint provides:
- **Summary**:
  - Total projects
  - Active projects count
  - Average progress across portfolio
  - Overdue tasks count
  - Tasks due within 48 hours

- **Tasks by Status**: Breakdown by column names
- **Top 5 Projects**: By pending tasks count
- **My Assigned Tasks**: User's tasks with due dates and progress

### 6. Automations & Notifications ✅

**Scheduled Job** (`SendCardDueReminders`):
- Runs daily at 9 AM
- Sends notifications for:
  - Cards due within 48 hours
  - Overdue cards
- Prevents duplicate notifications (once per day)

**Notification Types**:
- `CardAssignedNotification` - When card assigned to user
- `CardDueSoonNotification` - Due date within 48 hours
- `CardOverdueNotification` - Past due date

**Real-time Events**:
- `KanbanCardMoved` - Broadcast when card moved
- `KanbanCardUpdated` - Broadcast when card updated

## Installation & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Seed Demo Data (Optional)

```bash
php artisan db:seed --class=KanbanSeeder
```

This will create:
- Demo company
- 3 users (manager, 2 developers)
- 1 project with full board setup
- Sample cards across different columns

### 3. Setup Queue Worker

For notifications and broadcasting:

```bash
php artisan queue:work
```

### 4. Setup Task Scheduler

Add to your cron:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Setup Broadcasting (Optional)

For real-time updates, configure Laravel Echo:

```bash
npm install --save laravel-echo pusher-js
```

Update `resources/js/bootstrap.js`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

## Frontend Components

### Vue Components (Pinia Store-based)

**Available Components**:
- `KanbanBoard.vue` - Main board container
- `KanbanColumn.vue` - Column with drag-drop support
- `KanbanCard.vue` - Individual card display
- `PortfolioDashboard.vue` - Portfolio dashboard

**Pinia Store**:
- `stores/kanban.js` - State management for board, columns, cards
- Real-time event listeners
- API integration methods

### Usage Example

```vue
<template>
  <KanbanBoard />
</template>

<script setup>
import KanbanBoard from '@/components/KanbanBoard.vue';
</script>
```

## API Usage Examples

### Create Project

```javascript
const response = await axios.post('/api/projects', {
  name: 'New E-commerce Site',
  description: 'Build modern e-commerce platform',
  status: 'active',
  start_date: '2026-02-01',
  end_date: '2026-08-01',
  budget: 100000
});

// Response includes auto-created board and milestones
```

### Move Card

```javascript
const response = await axios.patch(
  `/api/projects/1/boards/1/cards/5/move`,
  {
    target_column_id: 3,
    target_position: 2
  }
);
```

### Toggle Checklist Item

```javascript
const response = await axios.post('/api/checklist-items/10/toggle');
// Card progress automatically updated
```

### Get Dashboard Data

```javascript
const response = await axios.get('/api/dashboard');

console.log(response.data.summary);
// {
//   total_projects: 5,
//   active_projects: 3,
//   avg_progress: 65.5,
//   overdue_tasks: 2,
//   tasks_due_soon: 5
// }
```

## Testing

### Run Feature Tests

```bash
php artisan test --filter=ProjectTest
php artisan test --filter=CardTest
php artisan test --filter=ChecklistTest
php artisan test --filter=DashboardTest
```

### Test Coverage

- ✅ Project CRUD operations
- ✅ Board creation on project create
- ✅ Card CRUD operations
- ✅ Card movement within column
- ✅ Card movement between columns
- ✅ Checklist item toggle
- ✅ Progress calculation
- ✅ Dashboard data retrieval
- ✅ Event broadcasting

## Default Column Structure

When a project is created, these columns are automatically generated:

1. **Backlog** (#6b7280) - Ideas and future tasks
2. **Ready** (#3b82f6) - Ready to be worked on
3. **To Do** (#8b5cf6) - Planned for current sprint
4. **In Progress** (#f59e0b) - Currently being worked on
5. **Review** (#ec4899) - Ready for review
6. **Blocked** (#ef4444) - Blocked by dependencies
7. **Done** (#10b981) - Completed tasks

## Default Milestone Template

1. **Inisiasi** - Project initiation phase
2. **Perencanaan** - Planning phase
3. **Eksekusi** - Execution phase
4. **Monitoring** - Monitoring and control phase
5. **Closing** - Project closing phase

## Security Considerations

- All API endpoints require authentication (`auth:sanctum`)
- Policies enforce role-based access control
- Users can only see projects from their company
- Card operations restricted to authorized users
- Database transactions ensure data consistency during moves

## Performance Optimization

- Eager loading relationships to prevent N+1 queries
- Indexed columns for fast querying (position, due_date, assignee_id)
- Pagination on project listings (15 per page)
- Efficient reordering algorithm for card movements

## Troubleshooting

### Cards not moving
- Check authorization policies
- Verify column_id belongs to same board
- Check JavaScript console for errors

### Notifications not sending
- Ensure queue worker is running
- Check notification table exists
- Verify user has email/database channel enabled

### Real-time updates not working
- Configure broadcasting driver (Pusher/Redis)
- Check Echo client setup
- Verify channel authorization

## Future Enhancements

Potential improvements:
- File attachments on cards
- Card comments/activity log
- Card templates
- Time tracking
- Sprint planning features
- Gantt chart view
- Export to PDF/Excel
- Mobile app support

## License

This feature is part of the Focus Super App project.
