# DriveED Hub - Project Reference Guide

**Last Updated:** January 25, 2026  
**Purpose:** Quick reference for database structure, accounts, and development preferences

---

## 🏫 EXISTING SCHOOLS (PRESERVE THESE!)

**⚠️ IMPORTANT:** Always keep these 3 schools intact. They are seeded by `OldSchoolsSeeder` and `Alpha2TestSeeder`.

1. **Smart Driving School**
   - Slug: `smart-driving`
   - Timezone: Asia/Manila
   - Colors: Primary #3b82f6, Secondary #1e40af, Accent #f59e0b

2. **LySpeed Driving School**
   - Slug: `lyspeed-driving`
   - Timezone: Asia/Manila
   - Colors: Primary #8b5cf6, Secondary #6d28d9, Accent #ec4899

3. **Alpha 2 LMS School** (Created by Alpha2TestSeeder)
   - Slug: TBD (check in database)
   - Used for testing LMS features

---

## 📊 DATABASE SCHEMA REFERENCE

### **schools** Table
```php
id                              // bigint primary key
name                           // string
slug                           // string, unique
timezone                       // string, default 'UTC'
branding                       // json (logo, colors)
settings                       // json (contact, email, address, allow_self_registration)
instructor_removal_notice_days // int, default 7
timestamps                     // created_at, updated_at
```

**❌ DOES NOT HAVE:** email, phone, address, is_active (these are in JSON fields)

---

### **school_settings** Table
```php
id                           // bigint primary key
school_id                    // foreign key (unique, one-to-one)
primary_color                // string(7), default '#2563eb'
secondary_color              // string(7), default '#fbbf24'
accent_color                 // string(7), default '#1e40af'
background_type              // string, default 'color'
background_color             // string, default '#f5f5f5'
background_image             // string, nullable
background_opacity           // int, default 100
sidebar_bg_color             // string(7), default '#ffffff'
sidebar_text_color           // string(7), default '#333333'
sidebar_hover_color          // string(7), default '#f5f5f5'
use_gradient_header          // boolean, default true
header_text_color            // string(7), default '#ffffff'
login_header_layout          // string, default 'horizontal'
login_logo_image             // string, nullable
login_logo_position          // string, default 'left'
login_logo_size              // int, default 40
login_school_name_text       // string, nullable
login_show_school_name       // boolean, default true
login_school_name_position   // string, default 'left'
login_school_name_size       // int, default 24
login_welcome_text           // string, default 'Welcome!'
login_show_welcome_text      // boolean, default true
login_welcome_position       // string, default 'right'
login_welcome_size           // int, default 16
timestamps
```

**❌ DOES NOT HAVE:** logo_path (use login_logo_image instead)

---

### **admins** Table
```php
id                    // bigint primary key
school_id             // foreign key, nullable (null = system admin)
name                  // string
email                 // string, unique (globally unique)
password              // string (hashed)
contact               // string(20), nullable
profile_picture       // string, nullable
role                  // string, default 'school_admin'
is_active             // boolean, default true
remember_token        // string
timestamps
```

**Key Points:**
- `school_id` NULL = System Admin (cross-school access)
- `school_id` set = School Admin (single school)
- Email is globally unique (not per-school)

---

### **instructors** Table
```php
id                       // bigint primary key
school_id                // foreign key, required
name                     // string
email                    // string
password                 // string (hashed)
contact                  // string, nullable
license_number           // string, nullable
bio                      // text, nullable
profile_picture          // string, nullable
status                   // string, default 'active'
course_specializations   // json, nullable
availability             // enum('available', 'unavailable'), default 'available'
remember_token           // string
timestamps

UNIQUE: (school_id, email) // Email unique per school
```

---

### **students** Table
```php
id                    // bigint primary key
school_id             // foreign key, required
name                  // string
email                 // string
password              // string (hashed)
contact               // string, nullable
address               // string, nullable
branch                // string, nullable
location              // string, nullable
profile_picture       // string, nullable
status                // string, default 'active'
role                  // enum('guest', 'student'), default 'guest'
enrollment_date       // date, nullable
remember_token        // string
timestamps

UNIQUE: (school_id, email) // Email unique per school
```

**Key Points:**
- `role = 'guest'` → Not yet approved, can only see guest dashboard
- `role = 'student'` → Approved, can access full student features
- Email is unique per school (can have same email in different schools)

**❌ DOES NOT HAVE:** email_verified_at (removed in migration)

---

### **enrollment_requests** Table
```php
id                           // bigint primary key
school_id                    // foreign key, required
learner_id                   // foreign key to students, required
course_id                    // foreign key to courses, required
status                       // enum('pending', 'approved', 'completed', 'cancelled', 'rejected')
requested_at                 // timestamp, nullable
approved_at                  // timestamp, nullable
approved_by                  // foreign key to admins, nullable
remarks                      // text, nullable
rejection_reason             // text, nullable
rejected_at                  // timestamp, nullable
rejected_by                  // foreign key to admins, nullable
enrolled_at                  // timestamp, nullable
completed_at                 // timestamp, nullable
cancelled_at                 // timestamp, nullable
theoretical_passed           // boolean, default false
theoretical_passed_at        // timestamp, nullable
theoretical_passed_by        // foreign key to admins, nullable
theoretical_pass_notes       // text, nullable
timestamps

UNIQUE: (learner_id, course_id) // One request per learner per course
```

**Critical Note:** Use `learner_id`, NOT `student_id`!

**Status Flow:**
- `pending` → Guest requests enrollment
- `approved` → Admin approves, guest becomes student
- `completed` → Student completes course
- `cancelled` → Student/admin cancels
- `rejected` → Admin rejects request

---

### **courses** Table
```php
id               // bigint primary key
school_id        // foreign key, required
title            // string
description      // text, nullable
banner_image     // string, nullable
features         // text (JSON), nullable
price            // decimal(10,2), default 0
duration_hours   // decimal(5,1), default 0
max_students     // int, nullable
type             // string, default 'standard' (standard, intensive, refresher)
vehicle_type     // string, nullable (sedan, suv, manual, automatic)
status           // string, default 'active' (active, inactive, archived)
is_featured      // boolean, default false
sort_order       // int, default 0
timestamps

INDEX: school_id, status
```

---

### **password_reset_tokens** Table
```php
email       // string, primary key
token       // string (hashed)
user_type   // string (admin, instructor, student)
school_id   // foreign key, nullable
created_at  // timestamp (60-minute expiry)
```

---

## 🔐 ACCOUNT STRUCTURE

### Authentication Guards
1. **admin** → Admin model
2. **instructor** → Instructor model
3. **student** → Student model (includes guests)

### Account Types
1. **System Admin** → `admins.school_id = NULL`, can access all schools
2. **School Admin** → `admins.school_id = X`, manages one school
3. **Instructor** → School-specific, teaches courses
4. **Student** → School-specific, `role='student'`, enrolled in courses
5. **Guest** → School-specific, `role='guest'`, waiting for approval

---

## 🔑 EXISTING TEST ACCOUNTS (From OldSchoolsSeeder)

### Smart Driving School (smart-driving)
- **Admin:** schooladmin@gmail.com / password
- **Admin:** systemadmin@gmail.com / password
- **Instructor:** instructor1@smart.com / password
- **Instructor:** instructor2@smart.com / password
- **Student:** student1@smart.com / password
- **Student:** student2@smart.com / password

### LySpeed Driving School (lyspeed-driving)
- **Admin:** lyspeed.admin@gmail.com / password
- **Admin:** lyspeed.system@gmail.com / password
- **Instructor:** instructor1@lyspeed.com / password
- **Instructor:** instructor2@lyspeed.com / password
- **Student:** student1@lyspeed.com / password
- **Student:** student2@lyspeed.com / password

---

## 🛠️ IMPORTANT CODE PATTERNS

### 1. Always Use Null-Safe Operators for School Settings
```php
// ✅ CORRECT
$school = School::where('slug', $slug)->first();
$settings = $school?->schoolSetting;
$primaryColor = $settings?->primary_color ?? '#2563eb';

// ❌ WRONG - Will cause null reference errors
$settings = $school->schoolSetting;
$primaryColor = $settings->primary_color;
```

### 2. Use learner_id, Not student_id
```php
// ✅ CORRECT
EnrollmentRequest::where('learner_id', $student->id)->get();

// ❌ WRONG
EnrollmentRequest::where('student_id', $student->id)->get();
```

### 3. Check Role, Not Just Authentication
```php
// ✅ CORRECT - Check if approved student
if ($student->role === 'student') {
    // Full student features
} else {
    // Guest dashboard only
}

// ❌ WRONG - Guests can also be authenticated
if (Auth::guard('student')->check()) {
    // This includes guests!
}
```

### 4. School-Specific Queries
```php
// ✅ CORRECT - Always filter by school
$students = Student::where('school_id', $school->id)->get();

// ❌ WRONG - Will show all schools' data
$students = Student::all();
```

### 5. Transaction-Safe Bulk Operations
```php
// ✅ CORRECT
DB::transaction(function () use ($enrollments, $school) {
    foreach ($enrollments as $enrollment) {
        // Update enrollment
        // Send email (with try-catch)
        // Update student role
    }
});

// ❌ WRONG - No rollback on failure
foreach ($enrollments as $enrollment) {
    $enrollment->update(...);
    Mail::send(...); // If this fails, previous updates remain
}
```

---

## 📧 EMAIL SYSTEM

**Current Mode:** Development (MAIL_MAILER=log)
- Emails are **NOT sent** to real addresses
- All emails logged to `storage/logs/laravel.log`
- Safe for testing without spamming users

**Implemented Mailables:**
1. `EnrollmentApproved` → Sent when admin approves enrollment request
2. `EnrollmentRequestReceived` → (Placeholder, not implemented)
3. `SessionReminder` → (Placeholder, not implemented)

---

## 🚀 SEEDERS

### DatabaseSeeder (Main)
Calls: `OldSchoolsSeeder`, `Alpha2TestSeeder`

### OldSchoolsSeeder
- Creates Smart Driving School + LySpeed Driving School
- Creates admins, instructors, students for both schools
- **Always preserve this data!**

### Alpha2TestSeeder
- Creates test data for LMS features
- Uses `EnrollmentRequest` model (not old Enrollment)

### SystemAdminSeeder
- Creates system-wide admin accounts
- Run with: `php artisan db:seed --class=SystemAdminSeeder`

### QuickTestSeeder (New - In Progress)
- Backdoor seeder for easy testing
- Simple credentials: all use "password"
- Creates test-school with admin/instructor/student/guest accounts
- **View credentials in browser:** http://localhost:8000/test/credentials/test-school

---

## 🎨 FRONTEND PATTERNS

### Modal Implementation
**Always follow existing modal pattern:**
```html
<!-- Modal trigger button -->
<button class="btn-action" onclick="openModal('modalId')">Open</button>

<!-- Modal structure -->
<div class="modal-backdrop" id="modalId-backdrop" style="display: none;" onclick="closeModal('modalId')"></div>
<div class="modal" id="modalId" style="display: none;">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Modal Title</h3>
            <span class="modal-close" onclick="closeModal('modalId')">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Content -->
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
    document.getElementById(modalId + '-backdrop').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.getElementById(modalId + '-backdrop').style.display = 'none';
}
</script>
```

### Custom CSS (No Bootstrap JS)
- Use custom CSS classes: `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.card`, `.badge`
- All styles in `resources/css/app.css` and `resources/css/dashboard.css`
- Built with Vite: `npm run build` or `npm run dev`

---

## 📦 RECENTLY ADDED FEATURES

### 1. Password Reset System
**Files:**
- Migration: `2026_01_17_092844_create_password_reset_tokens_table.php`
- Controller: `PasswordResetController.php`
- Views: `resources/views/school/password/forgot.blade.php`, `reset.blade.php`
- Routes: `password.request`, `password.email`, `password.reset`, `password.update`

**How it works:**
- User enters email + user type (admin/instructor/student)
- System finds user in correct table
- Sends reset link (logged to laravel.log in dev mode)
- User clicks link, sets new password
- Token expires after 60 minutes

### 2. Email Notifications
**Files:**
- Mailable: `app/Mail/EnrollmentApproved.php`
- Template: `resources/views/emails/enrollment-approved.blade.php`

**Triggered when:**
- Admin approves enrollment request
- Email sent to learner's email address
- Includes enrollment details, school branding, gradient header

### 3. Bulk Operations
**Files:**
- Controller: `EnrollmentRequestController.php` (bulkApprove, bulkReject methods)
- Routes: `enrollments.bulkApprove`, `enrollments.bulkReject`

**Features:**
- Approve/reject multiple enrollment requests at once
- Transaction-safe (all-or-nothing)
- Sends individual emails for each approval
- Returns success/failure counts

**UI:** ⚠️ Not yet added (needs checkboxes + buttons in views)

### 4. Export Functionality
**Files:**
- Controller: `ExportController.php`
- Views: `resources/views/exports/students-pdf.blade.php`, `enrollments-pdf.blade.php`
- Routes: `exports.students.pdf`, `exports.students.excel`, `exports.enrollments.pdf`, `exports.student.progress.pdf`

**Packages:**
- barryvdh/laravel-dompdf v3.1.1 (PDF generation)
- maatwebsite/excel v1.1.5 (Excel exports)

**UI:** ⚠️ Not yet added (needs export buttons in views)

---

## ⚙️ DEVELOPMENT PREFERENCES

### Do's ✅
1. **Always preserve the 3 existing schools** (Smart, LySpeed, Alpha 2 LMS)
2. **Use null-safe operators** for all relationship chains
3. **Check the actual database schema** before writing seeders
4. **Use learner_id** in enrollment_requests queries
5. **Follow existing modal patterns** (don't reinvent)
6. **Test with QuickTestSeeder** for fast iteration
7. **Use transactions** for multi-step database operations
8. **Log errors** with context (use `Log::warning()`, `Log::error()`)

### Don'ts ❌
1. **Don't delete or modify** existing school data
2. **Don't assume column names** - check migrations first
3. **Don't use student_id** - it's `learner_id`
4. **Don't create new modal patterns** - use existing ones
5. **Don't send real emails** in development
6. **Don't forget to rebuild Vite** after CSS/JS changes: `npm run build`
7. **Don't use Bootstrap JS** - we use custom JavaScript

---

## 🧪 TESTING WORKFLOW

1. **Create test accounts:**
   ```bash
   php artisan db:seed --class=QuickTestSeeder
   ```

2. **Access test URLs:**
   - http://localhost:8000/test-school/admin/dashboard
   - http://localhost:8000/test-school/instructor/dashboard
   - http://localhost:8000/test-school/student/dashboard
   - http://localhost:8000/test-school/guest/dashboard

3. **Check emails in logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Test new features:**
   - Refer to `TESTING_GUIDE_NEW_FEATURES.md`
   - Password reset, bulk operations, exports, email notifications

---

## 📚 DOCUMENTATION FILES

- **ADMIN_ROLES_IMPLEMENTATION.md** - Admin role system documentation
- **CHANGELOG.md** - Version history and changes
- **DEPLOYMENT_CHECKLIST.md** - Production deployment guide
- **MULTI_TENANT_CONVERSION_STATUS.md** - Multi-tenancy implementation status
- **MULTI_TENANT_VIEWS_SETUP.md** - View structure for multi-tenancy
- **PERFORMANCE_OPTIMIZATION.md** - Performance tips and optimizations
- **REDUNDANCIES_FIXED.md** - Code cleanup and refactoring notes
- **SYSTEM_LOGGING_README.md** - Logging system documentation
- **TESTING_GUIDE_NEW_FEATURES.md** - Testing guide for groupmates

---

## 🔧 COMMON COMMANDS

```bash
# Start development server
php artisan serve

# Build frontend assets
npm run build   # Production
npm run dev     # Development (watch mode)

# Run migrations
php artisan migrate

# Seed database (full reset)
php artisan migrate:fresh --seed

# Seed specific seeder
php artisan db:seed --class=QuickTestSeeder

# Check routes
php artisan route:list

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Check for errors
php artisan optimize
```

---

## 🎯 NEXT PRIORITIES (After Testing)

1. **UI Integration:** Add export buttons and bulk operation checkboxes to views
2. **Security:** Rate limiting, input validation, CSRF protection
3. **Performance:** Query optimization, caching, database indexing
4. **Features:** Payment integration, SMS notifications, reporting dashboard
5. **Testing:** PHPUnit tests, automated testing
6. **UX:** Responsive design improvements, loading states, error handling

---

**Remember:** This is a multi-tenant system. Every query should be school-specific!
