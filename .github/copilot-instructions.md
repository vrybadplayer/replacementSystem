# AI Coding Agent Instructions

## Project Overview
This is a **Laravel 12 web application** for managing class replacement scheduling in an educational institution. It's a role-based system with **Admin** and **Student** users managing timetables and replacement options.

- **Framework**: Laravel 12 (PHP 8.2+)
- **Frontend**: Blade templates with Tailwind CSS 4.0 and Vite build system
- **Database**: Uses Eloquent ORM with session-based authentication
- **Key Models**: User (with role-based access)

## Architecture & Key Components

### Authentication & Authorization
- **Location**: [../config/auth.php](../config/auth.php), [../app/Models/User.php](../app/Models/User.php)
- **Pattern**: Session-based with role-enum field (`admin` or `student`)
- **User Methods**: `isAdmin()`, `isStudent()` helpers on User model
- **Custom Middleware**: [../app/Http/Controllers/RoleMiddleware.php](../app/Http/Controllers/RoleMiddleware.php) validates route access by `role:admin|student` parameter
- **Setup**: Register middleware alias in [../bootstrap/app.php](../bootstrap/app.php) using `$middleware->alias()`

### Routing & Controllers
- **Location**: [routes/web.php](routes/web.php)
- **Pattern**: Three route groups:
  1. **Guest routes** (`middleware: guest`) - Login page, login form submission
  2. **Admin routes** (`middleware: ['auth', 'role:admin']`) - Dashboards, course/subject management
  3. **Student routes** (`middleware: ['auth', 'role:student']`) - Timetable views, replacement selection
- **Key Controllers**: TimetableController, CourseController, SubjectController, LoginController
- **Important**: Root `/` route checks `Auth::user()->isAdmin()` to redirect to correct dashboard

### Data Patterns
- **Timetable Data**: Currently hardcoded in [TimetableController.php](app/Http/Controllers/TimetableController.php) as arrays with keys: `id`, `subject`, `venue`, `lecturer`, `duration` (hours), `type` (normal/replacement/clash)
- **Time Slots**: Generated dynamically (8:00 AM to 10:00 PM) by looping hours
- **User Sessions**: Laravel's default session driver with Remember Token support

## Development Workflows

### Setup
```bash
composer install
npm install
php artisan migrate --force
npm run build
```

### Local Development
- **Run all services**: `composer run dev` (uses concurrently to run server, queue, logs, Vite)
- **Manual runs**:
  - PHP server: `php artisan serve`
  - Vite dev server: `npm run dev`
  - Queue listener: `php artisan queue:listen --tries=1`

### Testing
```bash
composer run test
# Clears config cache before running tests
```

### Build for Production
```bash
npm run build
```

## Project-Specific Conventions

### File Organization
- **Controllers**: [app/Http/Controllers/](app/Http/Controllers/) - One controller per major feature
- **Models**: [app/Models/](app/Models/) - Currently only User model defined
- **Views**: [resources/views/](resources/views/) - Organized by role (admin/, student/, subject/) and feature
- **CSS/JS Assets**: [resources/css/](resources/css/), [resources/js/](resources/js/) - Compiled via Vite into public/

### Blade Template Patterns
- **Admin Dashboard**: [admin_dashboard.blade.php](resources/views/admin_dashboard.blade.php)
- **Student Dashboard**: [student_dashboard.blade.php](resources/views/student_dashboard.blade.php)
- **Component System**: Blade components in [resources/views/components/](resources/views/components/)
- **Auth Check**: Use `@auth`, `@guest` directives; check role with `Auth::user()->isAdmin()` or `Auth::user()->isStudent()`

### Styling
- **Framework**: Tailwind CSS 4.0 (no JIT config needed in v4)
- **CSS Entry Point**: [resources/css/app.css](resources/css/app.css)
- **JavaScript**: ES6 modules via Vite

## Critical Integration Points

### Database Migrations
- **Location**: [database/migrations/](database/migrations/)
- **Pattern**: Migrations are numbered sequentially (000001, 000002, etc.)
- **Key Tables**: `users` (with `role` enum), `password_reset_tokens`, `sessions`
- **Run**: `php artisan migrate` or `php artisan migrate:rollback`

### Seeders & Factories
- **Location**: [database/seeders/](database/seeders/), [database/factories/](database/factories/)
- **User Seeder**: Can populate test users with different roles (modify [DatabaseSeeder.php](database/seeders/DatabaseSeeder.php))

### Configuration
- **Key Files**: [config/](config/) - app.php, auth.php, database.php, cache.php
- **Env Variables**: Copy `.env.example` to `.env` and configure database, app key (auto-generated)

## Code Examples

### Adding a Protected Route
```php
// In routes/web.php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
});
```

### Creating a Restricted Controller Method
```php
// User model automatically provides auth checks
if (Auth::user()->isAdmin()) {
    // Admin-only logic
} elseif (Auth::user()->isStudent()) {
    // Student-only logic
}
```

### Using Blade Role Checks
```blade
@if(Auth::user()->isAdmin())
    <a href="{{ route('admin.courses') }}">Manage Courses</a>
@endif
```

## Common Task Patterns

- **Add new route**: Define in [routes/web.php](routes/web.php) with appropriate middleware group
- **Add controller method**: Create/edit controller in [app/Http/Controllers/](app/Http/Controllers/), return view or JSON
- **Create model**: Use `php artisan make:model ModelName`, add to [app/Models/](app/Models/)
- **Add view**: Create `.blade.php` file in [resources/views/](resources/views/) organized by feature/role
- **Create migration**: Use `php artisan make:migration CreateTableName` (naming convention is key)
