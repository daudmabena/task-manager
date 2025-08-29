# Installed Packages Configuration

This document outlines the packages installed and their configuration for the Task Manager application.

## Laravel Packages

### 1. Spatie Laravel Permission
**Package**: `spatie/laravel-permission`
**Purpose**: Role and permission management

#### Configuration:
- Config file: `config/permission.php`
- Migrations: `create_permission_tables` migration
- Basic roles: `admin`, `manager`, `user`
- Permissions: `view tasks`, `create tasks`, `edit tasks`, `delete tasks`, `manage users`, `view activity logs`, `view audits`

#### Usage Example:
```php path=null start=null
// Assign role to user
$user->assignRole('admin');

// Check permission
$user->hasPermissionTo('create tasks');

// Check role
$user->hasRole('admin');

// In policy/controller
$this->authorize('create', Task::class);
```

### 2. Spatie Laravel Activity Log
**Package**: `spatie/laravel-activitylog`
**Purpose**: Track model changes and user activities

#### Configuration:
- Config file: `config/activitylog.php`
- Migrations: `create_activity_log_table` and related
- Applied to: `User` and `Task` models

#### Usage Example:
```php path=null start=null
// Manual logging
activity()
    ->performedOn($task)
    ->causedBy(auth()->user())
    ->log('Task created');

// Automatic logging (via LogsActivity trait)
// Logs changes automatically when model is updated
```

### 3. Spatie Laravel Query Builder
**Package**: `spatie/laravel-query-builder`
**Purpose**: Build complex API queries with filtering, sorting, and includes

#### Usage Example:
```php path=/Users/daud.mabena/Sites/task-manager/app/Http/Controllers/TaskController.php start=22
$tasks = QueryBuilder::for(Task::class)
    ->with(['user', 'assignedUser'])
    ->allowedFilters([
        'title',
        'status',
        'priority',
        AllowedFilter::exact('user_id'),
        AllowedFilter::exact('assigned_to'),
        AllowedFilter::scope('due_soon'),
    ])
    ->allowedSorts([
        'title',
        'status',
        'priority',
        'due_date',
        'created_at',
        'updated_at',
    ])
    ->defaultSort('-created_at')
    ->paginate(10);
```

#### API Usage:
```bash
# Filter by status
GET /tasks?filter[status]=pending

# Sort by due date
GET /tasks?sort=due_date

# Filter and sort
GET /tasks?filter[status]=pending&sort=-created_at

# Filter by user
GET /tasks?filter[user_id]=1
```

### 4. Laravel Telescope
**Package**: `laravel/telescope`
**Purpose**: Application debugging and monitoring

#### Configuration:
- Installed via `php artisan telescope:install`
- Access via: `/telescope` (in local environment)
- Environment variable: `TELESCOPE_ENABLED=true`

#### Features:
- Request monitoring
- Database query analysis
- Job monitoring
- Mail tracking
- Exception tracking

### 5. Laravel Debugbar (Development)
**Package**: `barryvdh/laravel-debugbar`
**Purpose**: Development debugging toolbar

#### Configuration:
- Config file: `config/debugbar.php`
- Environment variable: `DEBUGBAR_ENABLED=true`
- Shows: queries, timeline, memory usage, logs

### 6. Laravel Auditing
**Package**: `owen-it/laravel-auditing`
**Purpose**: Model change auditing

#### Configuration:
- Config file: `config/audit.php`
- Migration: `create_audits_table`
- Applied to: `Task` model
- Environment variable: `AUDITING_ENABLED=true`

#### Usage:
```php path=null start=null
// Get audit trail for a model
$task->audits;

// Get latest audit
$task->audits()->latest()->first();
```

## React Packages

### 1. Inertia.js Progress
**Package**: `@inertiajs/progress`
**Purpose**: Show loading progress bar during page transitions

#### Configuration:
```typescript path=/Users/daud.mabena/Sites/task-manager/resources/js/app.tsx start=13
// Initialize Inertia progress bar
progress.init({
    color: '#4B5563',
    showSpinner: true,
});
```

### 2. Lucide React
**Package**: `lucide-react`
**Purpose**: Beautiful, customizable SVG icons

#### Usage Example:
```typescript path=/Users/daud.mabena/Sites/task-manager/resources/js/components/TaskCard.tsx start=2
import { 
    Calendar, 
    User, 
    Clock, 
    AlertCircle, 
    CheckCircle2, 
    Circle,
    Edit3,
    Trash2,
    MoreHorizontal 
} from 'lucide-react';
```

### 3. React Toastify
**Package**: `react-toastify`
**Purpose**: Toast notifications

#### Configuration:
```typescript path=/Users/daud.mabena/Sites/task-manager/resources/js/app.tsx start=26
<ToastContainer
    position="top-right"
    autoClose={5000}
    hideProgressBar={false}
    newestOnTop={false}
    closeOnClick
    rtl={false}
    pauseOnFocusLoss
    draggable
    pauseOnHover
    theme="light"
/>
```

#### Usage:
```typescript path=/Users/daud.mabena/Sites/task-manager/resources/js/utils/toast.ts start=4
import { showToast } from '../utils/toast';

showToast.success('Task created successfully');
showToast.error('Failed to save task');
showToast.warning('Task is overdue');
showToast.info('Status updated');
```

## Model Configuration

### Task Model Features:
- **Auditing**: Tracks all changes via `owen-it/laravel-auditing`
- **Activity Logging**: Logs activities via `spatie/laravel-activitylog`  
- **Query Builder**: Supports filtering and sorting
- **Permissions**: Protected by role-based access control

### User Model Features:
- **Roles & Permissions**: Via `spatie/laravel-permission`
- **Activity Logging**: Tracks user changes

## Development Tools

### Accessing Telescope:
Visit `/telescope` in your browser (local environment only)

### Using Debugbar:
The debugbar appears at the bottom of pages in development mode when `APP_DEBUG=true`

## Security Notes

- **Telescope**: Only enabled in local environment
- **Debugbar**: Only enabled in development
- **Permissions**: All task operations are protected by policies
- **Auditing**: Tracks all model changes for compliance

## Next Steps

1. Create task management pages using the TaskCard component
2. Implement role assignment in user management
3. Add activity log viewing for administrators  
4. Set up proper API endpoints with query builder filtering
5. Configure Telescope for production monitoring (optional)
