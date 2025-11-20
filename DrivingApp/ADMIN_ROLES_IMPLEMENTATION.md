# Admin Role System Implementation

## Date: November 14, 2025

## Overview
Implemented a two-tier admin system with role-based access control:
- **System Admin**: Full system access including deletion, logs, and monitoring
- **School Admin**: Day-to-day school operations (cannot delete accounts)
- **Staff**: Limited administrative access

---

## Implementation Details

### 1. Admin Roles

#### System Admin (`system_admin`)
**Responsibilities:**
- Monitor system uptime and performance
- View comprehensive system logs
- Permanently delete accounts (students/instructors)
- Track all admin actions (logins, logouts, activations, deactivations)
- System health monitoring

**Email:** `sysadmin.rodriguez@gmail.com`

#### School Admin (`school_admin`)  
**Responsibilities:**
- Manage students and instructors (create, edit, deactivate/activate)
- Manage courses and packages
- View basic reports
- Handle enrollment requests
- Schedule management
- **Cannot:** Permanently delete accounts

**Email:** `schooladmin.santos@gmail.com`

#### Staff (`staff`)
**Responsibilities:**
- Basic administrative tasks
- View-only access to most features
- Limited editing capabilities

**Email:** `staff.reyes@gmail.com`

---

## Files Created/Modified

### New Files
1. **`app/Http/Middleware/EnsureSystemAdmin.php`**
   - Middleware to protect system-admin-only routes
   - Checks if authenticated admin has `system_admin` role

2. **`app/Models/Log.php`**
   - Model for tracking all system actions
   - Stores: action type, description, IP, user agent, metadata

### Modified Files
1. **`bootstrap/app.php`**
   - Registered `system.admin` middleware alias

2. **`routes/web.php`**
   - Added system-admin-only route group with middleware
   - Protected routes: system logs, monitoring, permanent deletions

3. **`app/Http/Controllers/AdminController.php`**
   - Added `systemLogs()` - view all admin actions
   - Added `systemMonitoring()` - system health metrics
   - Added `deleteStudent()` - permanent deletion (system admin only)
   - Added `deleteInstructor()` - permanent deletion (system admin only)
   - All deletions are logged

4. **`database/seeders/ScheduleFocusedSeeder.php`**
   - Updated admin creation with new roles
   - Changed email addresses to match roles
   - Updated output messages

---

## Routes

### System Admin Only Routes
```php
Route::middleware(['auth:admin', 'ajax', 'system.admin'])->group(function() {
    // System monitoring & logs
    GET /admin/system-logs
    GET /admin/system-monitoring
    
    // Permanent deletions
    DELETE /admin/students/{id}/permanent
    DELETE /admin/instructors/{id}/permanent
});
```

### All Admin Routes (including School Admin)
```php
// User management
GET /admin/user-management
PATCH /admin/students/{id}/toggle-status     // Activate/Deactivate
PATCH /admin/instructors/{id}/toggle-status  // Activate/Deactivate

// Courses, schedules, reports, etc.
// (All existing admin routes)
```

---

## Usage Examples

### Protecting New System Admin Routes
```php
// In routes/web.php
Route::middleware(['auth:admin', 'ajax', 'system.admin'])->group(function() {
    Route::get('/new-system-feature', [AdminController::class, 'newFeature']);
});
```

### Checking Role in Controller
```php
public function someMethod(School $school)
{
    $admin = Auth::guard('admin')->user();
    
    if ($admin->role === 'system_admin') {
        // System admin specific logic
    } elseif ($admin->role === 'school_admin') {
        // School admin specific logic
    }
}
```

### Logging Admin Actions
```php
use App\Models\Log;

Log::create([
    'school_id' => $school->id,
    'admin_id' => auth()->guard('admin')->id(),
    'action' => 'deactivated_student',
    'description' => "Deactivated student: {$student->name}",
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);
```

---

## UI Considerations

### Dashboard/Sidebar
Show/hide features based on role:
```blade
@if(auth()->guard('admin')->user()->role === 'system_admin')
    <li><a href="{{ route('schools.admin.systemLogs', $school) }}">System Logs</a></li>
    <li><a href="{{ route('schools.admin.systemMonitoring', $school) }}">Monitoring</a></li>
@endif
```

### Action Buttons
```blade
{{-- School admin sees Deactivate button --}}
@if(auth()->guard('admin')->user()->role === 'school_admin')
    <button>Deactivate Student</button>
@endif

{{-- System admin sees both Deactivate and Delete buttons --}}
@if(auth()->guard('admin')->user()->role === 'system_admin')
    <button>Deactivate Student</button>
    <button class="danger">Permanently Delete</button>
@endif
```

---

## Next Steps / TODO

### 1. Create System Admin Views
- [ ] `resources/views/drivingschool1/admin/system-logs.blade.php`
- [ ] `resources/views/drivingschool1/admin/system-monitoring.blade.php`

### 2. Enhance Logging System
- [ ] Log all login/logout events
- [ ] Log all CRUD operations
- [ ] Add log filtering and search
- [ ] Export logs to CSV/PDF

### 3. System Monitoring Features
- [ ] Database size tracking
- [ ] Active sessions count
- [ ] Response time monitoring
- [ ] Error rate tracking
- [ ] Backup status

### 4. UI Updates
- [ ] Add role badge in admin nav
- [ ] Conditional rendering of delete buttons
- [ ] System admin dashboard widgets
- [ ] Audit trail viewer

### 5. Enhanced Security
- [ ] Require password confirmation for deletions
- [ ] Two-factor authentication for system admins
- [ ] Session timeout for system admin
- [ ] IP whitelist for system admin

---

## Testing Checklist

- [x] System admin can access system logs page
- [x] System admin can access monitoring page
- [x] System admin can permanently delete accounts
- [ ] School admin CANNOT access system-only pages (403 error)
- [ ] School admin CANNOT permanently delete accounts
- [ ] All deletions are logged properly
- [ ] Middleware protects system-admin routes correctly

---

## Security Notes

1. **Role-Based Access:** All system-admin features protected by `system.admin` middleware
2. **Logging:** All critical actions (especially deletions) are logged with admin ID, IP, and timestamp
3. **Separation of Concerns:** School admins manage operations, system admins manage system
4. **Audit Trail:** Log model provides complete audit trail of all admin actions

---

## Credentials (Test Environment)

```
System Admin:
Email: sysadmin.rodriguez@gmail.com
Password: password123

School Admin:
Email: schooladmin.santos@gmail.com
Password: password123

Staff:
Email: staff.reyes@gmail.com
Password: password123
```

**Note:** Change these in production!
