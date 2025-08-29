# Task Manager

A modern, full-featured task management application built with Laravel, Inertia.js, and React. This application provides comprehensive project management capabilities with user authentication, role-based access control, and advanced task tracking features.

## 🚀 Features

### Core Functionality

- **Task Management**: Create, edit, delete, and track tasks with detailed information
- **Project Organization**: Organize tasks by systems, processes, and functions
- **User Management**: Complete user account management with roles and permissions
- **Activity Logging**: Track all changes and activities across the system
- **Audit Trail**: Comprehensive audit logging for compliance and debugging

### User Management & Security

- **Role-Based Access Control**: Granular permissions using Spatie Laravel Permission
- **User Authentication**: Secure login/logout with email verification
- **Permission Management**: Create and assign custom permissions to roles
- **Direct Permissions**: Assign permissions directly to users (in addition to roles)
- **Soft Deletes**: Safe deletion with data recovery capabilities

### Advanced Features

- **Real-time Search**: Search across users, tasks, and systems
- **Filtering & Sorting**: Advanced filtering and sorting capabilities
- **Pagination**: Efficient data pagination for large datasets
- **Responsive Design**: Modern, mobile-friendly interface
- **Dark/Light Mode**: Theme switching with system preference detection

## 🛠️ Technology Stack

### Backend

- **Laravel 12**: Modern PHP framework
- **MySQL**: Primary database
- **Spatie Laravel Permission**: Role and permission management
- **Spatie Laravel Activity Log**: Activity logging and audit trails
- **OwenIt Laravel Auditing**: Model change auditing
- **Laravel Telescope**: Application debugging and monitoring

### Frontend

- **React 18**: Modern JavaScript library
- **TypeScript**: Type-safe JavaScript development
- **Inertia.js**: SPA-like experience without API complexity
- **Tailwind CSS**: Utility-first CSS framework
- **Shadcn/ui**: Modern React component library
- **Lucide React**: Beautiful icon library

### Development Tools

- **Vite**: Fast build tool and development server
- **ESLint**: Code linting and formatting
- **PHPUnit**: PHP testing framework
- **Laravel Debugbar**: Development debugging

## 📋 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.3+** with required extensions
- **Composer** for PHP dependency management
- **Node.js 18+** and **npm** for frontend dependencies
- **MySQL 8.0+** or **MariaDB 10.5+**
- **Git** for version control

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/daudmabena/task-manager.git
cd task-manager
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update your `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Database Setup

```bash
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 6. Build Frontend Assets

```bash
npm run build
```

### 7. Start the Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## 👥 User Management

### Default Roles

The application comes with three default roles:

1. **Admin**: Full system access with all permissions
2. **Manager**: Task management and system monitoring permissions
3. **User**: Basic task creation and editing permissions

### Default User

- **Email**: admin@example.com (create this user)
- **Role**: Admin (assigned automatically)

### Permission System

The application uses a granular permission system:

#### User Management Permissions

- `view users` - View user listings
- `create users` - Create new users
- `edit users` - Edit existing users
- `delete users` - Delete users
- `assign roles` - Assign roles to users
- `manage permissions` - Manage system permissions

#### Task Management Permissions

- `view tasks` - View task listings
- `create tasks` - Create new tasks
- `edit tasks` - Edit existing tasks
- `delete tasks` - Delete tasks

#### System Management Permissions

- `view systems` - View system listings
- `create systems` - Create new systems
- `edit systems` - Edit existing systems
- `delete systems` - Delete systems

## 📁 Project Structure

```
task-manager/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── UserAccountController.php    # User management
│   │   │   ├── SystemsController.php        # System management
│   │   │   └── TaskController.php           # Task management
│   │   ├── Requests/
│   │   │   └── User/                        # Form request validation
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php                         # User model with roles
│   │   ├── System.php                       # System model
│   │   └── Task.php                         # Task model
│   └── Providers/
├── resources/
│   └── js/
│       ├── pages/
│       │   ├── Users/                       # User management pages
│       │   ├── Systems/                     # System management pages
│       │   └── Tasks/                       # Task management pages
│       ├── components/                      # Reusable React components
│       └── layouts/                         # Page layouts
├── database/
│   ├── migrations/                          # Database migrations
│   └── seeders/                             # Database seeders
└── routes/
    └── web.php                              # Application routes
```

## 🔧 Configuration

### Permission Configuration

The application uses Spatie Laravel Permission. Configuration is in `config/permission.php`.

### Activity Logging

Activity logging is configured in `config/activitylog.php`.

### Database Configuration

Database configuration is in `config/database.php`.

## 🧪 Testing

### Run PHP Tests

```bash
php artisan test
```

### Run Frontend Tests

```bash
npm test
```

## 🚀 Deployment

### Production Build

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Environment Variables

Ensure all production environment variables are set:

- Database credentials
- Application key
- Mail configuration
- Queue configuration (if using)

## 📊 Database Schema

### Core Tables

- `users` - User accounts and authentication
- `systems` - System/project definitions
- `tasks` - Task management
- `roles` - User roles (Spatie Permission)
- `permissions` - System permissions (Spatie Permission)
- `model_has_roles` - Role assignments (Spatie Permission)
- `model_has_permissions` - Permission assignments (Spatie Permission)
- `activity_log` - Activity logging (Spatie Activity Log)
- `audits` - Model change auditing (OwenIt Auditing)

## 🔒 Security Features

- **CSRF Protection**: All forms include CSRF tokens
- **SQL Injection Prevention**: Eloquent ORM with parameter binding
- **XSS Protection**: Input sanitization and output escaping
- **Role-Based Access Control**: Granular permission system
- **Soft Deletes**: Safe data deletion
- **Audit Logging**: Complete change tracking
- **Input Validation**: Comprehensive form validation

## 🎨 UI/UX Features

- **Responsive Design**: Mobile-first approach
- **Dark/Light Mode**: Theme switching
- **Modern Components**: Shadcn/ui component library
- **Beautiful Icons**: Lucide React icon library
- **Loading States**: Smooth loading indicators
- **Toast Notifications**: User feedback system
- **Form Validation**: Real-time validation feedback

## 🔧 Development

### Code Style

- **PHP**: PSR-12 coding standards
- **JavaScript**: ESLint configuration
- **TypeScript**: Strict type checking

### Git Workflow

1. Create feature branch
2. Make changes
3. Run tests
4. Submit pull request

### Debugging

- **Laravel Telescope**: Application debugging
- **Laravel Debugbar**: Development debugging
- **Browser DevTools**: Frontend debugging

## 📝 API Documentation

The application uses Inertia.js, which provides a SPA-like experience without building a separate API. All data is passed through server-side rendering.

### Key Endpoints

- `GET /users` - User management
- `GET /systems` - System management
- `GET /tasks` - Task management
- `POST /users` - Create user
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:

- Create an issue in the repository
- Check the documentation
- Review the code comments

## 🔄 Changelog

### Version 1.0.0

- Initial release
- User management system
- Role-based access control
- Task management
- System management
- Activity logging
- Audit trails

---

**Built with ❤️ using Laravel, Inertia.js, and React**
