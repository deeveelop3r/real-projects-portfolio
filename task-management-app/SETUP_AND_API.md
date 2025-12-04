# Task Management App - Complete Setup & API Guide

## Quick Start

### Backend Setup

```bash
# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed

# Cache and optimization
php artisan optimize

# Serve
php artisan serve --port=8000
```

### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Configure API endpoint
# Edit .env.local
VITE_API_URL=http://localhost:8000/api

# Development server
npm run dev
```

## Database Schema

### 9 Core Tables

1. **users** - User accounts and authentication
2. **teams** - Team/Organization management
3. **team_members** - Team membership and roles
4. **projects** - Project management
5. **tasks** - Task management with priorities and status
6. **task_comments** - Task discussions
7. **notifications** - User notifications
8. **activity_logs** - Action tracking
9. **attachments** - File attachments for tasks

## API Endpoints

### Tasks

```
GET    /api/tasks                    # List all tasks
POST   /api/tasks                    # Create task
GET    /api/tasks/:id                # Get task details
PUT    /api/tasks/:id                # Update task
DELETE /api/tasks/:id                # Delete task
POST   /api/tasks/:id/status         # Change status
POST   /api/tasks/:id/assign         # Assign task
POST   /api/tasks/:id/comments       # Add comment
```

### Projects

```
GET    /api/projects                 # List projects
POST   /api/projects                 # Create project
GET    /api/projects/:id             # Get project details
PUT    /api/projects/:id             # Update project
DELETE /api/projects/:id             # Delete project
GET    /api/projects/:id/tasks       # Get project tasks
```

### Teams

```
GET    /api/teams                    # List teams
POST   /api/teams                    # Create team
GET    /api/teams/:id                # Get team details
POST   /api/teams/:id/members        # Add member
DELETE /api/teams/:id/members/:uid   # Remove member
```

## Task Properties

```php
{
  "id": 1,
  "project_id": 5,
  "title": "Implement authentication",
  "description": "Add JWT authentication to API",
  "priority": "high",          // low, medium, high, urgent
  "status": "in_progress",     // todo, in_progress, in_review, done, blocked
  "start_date": "2024-01-15",
  "due_date": "2024-01-25",
  "assigned_to": 3,
  "created_by": 1,
  "created_at": "2024-01-10T10:30:00Z",
  "updated_at": "2024-01-20T15:45:00Z"
}
```

## Project Properties

```php
{
  "id": 5,
  "team_id": 1,
  "name": "Q1 Product Launch",
  "description": "Launch new product features",
  "start_date": "2024-01-01",
  "end_date": "2024-03-31",
  "status": "active",           // planning, active, on_hold, completed, archived
  "color": "#4F46E5",
  "owner_id": 1,
  "progress": 45,
  "tasks_count": 24,
  "completed_tasks": 11,
  "overdue_tasks": 2,
  "created_at": "2024-01-01T00:00:00Z"
}
```

## Real-time Features

### WebSocket Events

```javascript
// Task updates
socket.on('task:created', (task) => {})
socket.on('task:updated', (task) => {})
socket.on('task:deleted', (taskId) => {})
socket.on('task:status-changed', (task) => {})

// Notifications
socket.on('notification:new', (notification) => {})
socket.on('notification:read', (notificationId) => {})

// Team updates
socket.on('team:member-joined', (member) => {})
socket.on('team:member-left', (userId) => {})
```

## Key Features Implementation

### 1. Kanban Board

```vue
<KanbanBoard
  :tasks="tasks"
  :statuses="['todo', 'in_progress', 'in_review', 'done']"
  @task-moved="updateTaskStatus"
/>
```

### 2. Calendar View

```vue
<CalendarView
  :tasks="tasks"
  :projects="projects"
  @date-selected="selectDate"
/>
```

### 3. Gantt Chart

```vue
<GanttChart
  :tasks="tasks"
  :projects="projects"
  start-date="2024-01-01"
  end-date="2024-12-31"
/>
```

### 4. Real-time Notifications

```javascript
// Client
const notificationService = new NotificationService();
notificationService.onTaskAssigned((task) => {
  showNotification(`You've been assigned: ${task.title}`);
});

// Server
TaskAssignedEvent::dispatch($task);
```

### 5. Team Collaboration

```php
// Comment with mentions
$task->comments()->create([
  'user_id' => $user->id,
  'comment' => '@john Please review this task',
]);

// Notification sent to @john
```

## Performance Tips

1. **Pagination**
   - Use pagination for large datasets
   - Default 20 items per page

2. **Caching**
   - Cache project list per team
   - Cache task statistics
   - Use Redis for real-time data

3. **Database Optimization**
   - Index on status, priority, due_date
   - Eager load relationships
   - Use soft deletes for data integrity

4. **Frontend**
   - Code splitting by route
   - Virtual scrolling for large lists
   - Lazy load images and attachments

## Security Considerations

- ✅ JWT token-based authentication
- ✅ Role-based access control (RBAC)
- ✅ Input validation and sanitization
- ✅ Rate limiting on API endpoints
- ✅ CSRF protection
- ✅ XSS prevention
- ✅ SQL injection prevention (ORM)

## Deployment

### Docker Setup

```dockerfile
FROM php:8.1-fpm
RUN composer install
RUN php artisan migrate
EXPOSE 8000
CMD ["php", "artisan", "serve"]
```

### Production Deployment

```bash
# 1. SSH to server
ssh user@production.com

# 2. Clone and setup
git clone repository
cd project
composer install --no-dev
php artisan migrate --force

# 3. Configure web server (Nginx/Apache)
# 4. Setup SSL certificate
# 5. Configure Redis for caching
# 6. Start Laravel
php artisan serve --host=0.0.0.0
```

## Troubleshooting

### WebSocket Connection Issues
- Check Redis is running
- Verify socket.io configuration
- Check CORS settings

### Task Not Syncing
- Clear browser cache
- Check WebSocket connection
- Verify database transaction

### Performance Issues
- Run `php artisan optimize`
- Enable Redis caching
- Check slow query logs

## Support

- Documentation: https://docs.task-management-app.com
- Issues: https://github.com/deeveelop3r/task-management-app/issues
- Email: support@task-management-app.com
