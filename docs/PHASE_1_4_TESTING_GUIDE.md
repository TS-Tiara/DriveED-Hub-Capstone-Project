# Comprehensive Testing Guide: Phases 1-4

## Quick Start - Running the System

```bash
php artisan serve
```

Visit: http://localhost:8000

## Prerequisites

Run seeders in order before testing:
```bash
php artisan db:seed --class=TwoSchoolTestSeeder
php artisan db:seed --class=TestDataSeeder
```

---

## Demo Accounts (Seeded by TwoSchoolTestSeeder)

**All passwords are: `password123`**

### School Admin Accounts (1 per school)

| Email | School | Role |
|-------|--------|------|
| admin@schoola.test | Driving School A | school_admin |
| admin@schoolb.test | Driving School B | school_admin |

**Login URL (School A):** http://localhost:8000/driving-school-a/login  
**Login URL (School B):** http://localhost:8000/driving-school-b/login

### Instructor Accounts (6 total: 3 per school)

**Driving School A (school ID: 2)**

| Email | Name | Branch | License Status |
|-------|------|--------|----------------|
| instructorA1@test.com | Instructor A1 | Main Branch | verified |
| instructorA2@test.com | Instructor A2 | Main Branch | verified |
| instructorA3@test.com | Instructor A3 | Sub Branch | verified |

**Driving School B (school ID: 3)**

| Email | Name | Branch | License Status |
|-------|------|--------|----------------|
| instructorB1@test.com | Instructor B1 | Main Branch | verified |
| instructorB2@test.com | Instructor B2 | Main Branch | verified |
| instructorB3@test.com | Instructor B3 | Sub Branch | verified |

### Student Accounts (12 total: 6 per school)

**Driving School A**

| Email | Name | Branch | Experience | TDC Passed? |
|-------|------|--------|------------|-------------|
| studentA1@test.com | Student A1 | Sub Branch (branch 1) | new_driver | No |
| studentA2@test.com | Student A2 | Main Branch (branch 0) | new_driver | Yes |
| studentA3@test.com | Student A3 | Sub Branch (branch 1) | new_driver | No |
| studentA4@test.com | Student A4 | Main Branch (branch 0) | experienced | Yes |
| studentA5@test.com | Student A5 | Sub Branch (branch 1) | experienced | No |
| studentA6@test.com | Student A6 | Main Branch (branch 0) | experienced | Yes |

**Driving School B** (studentB1@test.com through studentB6@test.com)

Same pattern: odd-numbered students (B1, B3, B5) have NOT passed TDC; even-numbered (B2, B4, B6) HAVE passed TDC.

### Guest Accounts (6 total: 3 per school)

| Email | School |
|-------|--------|
| guestA1@test.com | Driving School A |
| guestA2@test.com | Driving School A |
| guestA3@test.com | Driving School A |
| guestB1@test.com | Driving School B |
| guestB2@test.com | Driving School B |
| guestB3@test.com | Driving School B |

### School Details

| School Name | Slug | School ID | Primary Branches |
|-------------|------|-----------|-----------------|
| Driving School A | driving-school-a | 2 | Main Branch, Sub Branch |
| Driving School B | driving-school-b | 3 | Main Branch, Sub Branch |

---

## Seeded Data Summary

| Entity | Count (per school) | Total (2 schools) |
|--------|---------|---------|
| Schools | 1 | 2 |
| Admins | 1 | 2 |
| Instructors | 3 | 6 |
| Students | 6 | 12 |
| Guests | 3 | 6 |
| Branches | 2 | 4 |
| Courses | 3 | 1 |
| Course Packages | 3 | 6 |
| Enrollment Requests | 6 | 12 |
| Time Slots* | 6 | 12 |
| Bookings* | 5 | 10 |
| Schedule Instructors* | 6 | 12 |

\* Seeded by TestDataSeeder, not TwoSchoolTestSeeder

### Course Details (per school)

| Title | Type | Course Type | License Type | Hours Required | Package |
|-------|------|-------------|--------------|----------------|---------|
| Manual | Theoretical | theoretical | non_professional | 15 | Manual Standard (2000.00) |
| Practical | Practical | practical | professional | 10 | Practical Standard (5000.00) |
| Fun Combo Drivers Pack | Combo | combo | non_professional | 25 | Fun Combo Full Package (6000.00) |

### Enrollment Request Status

- Students who passed TDC (A2, A4, A6, B2, B4, B6): status = **approved**, payment_status = **paid**
- Students who did NOT pass TDC (A1, A3, A5, B1, B3, B5): status = **pending**, payment_status = **pending**

---

## Phase 1: Database & Seeded Data

### 1.1 Login as School Admin
1. Go to http://localhost:8000/driving-school-a/login
2. Login: admin@schoola.test / password123
3. Verify: School admin dashboard loads with school data for Driving School A
4. Also verify: admin@schoolb.test can log in at http://localhost:8000/driving-school-b/login

### 1.2 Check Seeded Data
1. Navigate to School Management -> View Schools
2. Verify: 2 schools exist (Driving School A, Driving School B)
3. For each school, verify:
   - 1 admin exists
   - 3 instructors with verified license status
   - 6 students with profiles
   - 3 courses (TDC/Manual, PDC/Practical, Combo)
   - 6 enrollment requests (3 approved, 3 pending)
4. Check enrollment requests -> 12 records total (6 per school)

### 1.3 Database Integrity
- time_slots all have instructors linked via schedule_instructors
- bookings all link to valid enrollment_request_id
- instructor_working_hours table exists

---

## Phase 2: Core Features

### 2.1 Admin - Time Slot Management
1. Login as admin@schoola.test / password123
2. Go to Admin -> Schedules & Time Slots
3. Verify: Table shows all slots with date, time, course, instructors, status
4. Click "Create Time Slot"
5. Verify form: date, start/end time, course dropdown, instructor select
6. Duration validation: minimum 60 minutes (error if less)
7. PDC auto-sets max_instructors=1, max_students=1
8. TDC allows multiple instructors

### 2.2 Instructor - Schedule View
1. Login as instructorA1@test.com / password123
2. Go to My Schedule
3. Verify: Can see assigned slots, booked students, toggle availability

### 2.3 Student - Booking Flow
1. Login as studentA2@test.com / password123 (approved enrollment)
2. Go to Courses -> Select course -> View Schedule
3. Verify: Can see available slots, book if enrolled

### 2.4 Double-Booking Prevention
1. As student, try booking a full slot
2. Verify: "No spots available" error

---

## Phase 3: Lesson Progress

### 3.1 Lesson Progress Model
1. As instructor, go to Students -> Select a student
2. Verify: Lesson progress section shows modules, lessons, status
3. Can mark lessons completed, progress saved

### 3.2 Phase Progression
1. As instructor, request phase progression
2. As admin, approve/reject in Phase Progressions panel

---

## Phase 4: Scheduling Rules

### 4.1 Admin UI - Scheduling Settings
1. Login as admin@schoola.test / password123
2. Admin -> Settings -> Scheduling Rules
3. Verify fields: Max TDC (300), Max PDC (180), Min Gap (15)
4. Change values, save, verify persistence

### 4.2 Instructor Working Hours Management
1. Admin -> User Management
2. Verify: Each instructor has "Working Hours" button (clock icon)
3. Click -> Form shows 7 days
4. Set Monday: 09:00-17:00, break 12:00-13:00
5. Save -> Success message
6. Test with instructorA1@test.com (Main Branch, School A)

### 4.3 Duration Limit Validation
1. Create TDC slot > 300 min -> Error
2. Create TDC slot = 300 min -> Success
3. Create PDC slot > 180 min -> Error

### 4.4 Working Hours Enforcement
1. Set working hours for Monday: 09:00-17:00 (for instructorA1)
2. Create slot 08:00-09:00 -> Error (outside hours)
3. Create slot 12:30-13:30 -> Error (during break)
4. Create slot 10:00-11:00 -> Success

### 4.5 Daily Teaching Limit
1. Set 8 teachable hours for Monday
2. Create 4-hour slot -> Success
3. Create 5-hour slot -> Error (exceeds 8hr limit)

### 4.6 Backwards Compatibility
1. No working hours set -> Slots allowed
2. Set hours after slots exist -> Existing slots remain valid

### 4.7 Error Handling
1. Non-existent instructor working hours -> 404
2. Unauthenticated access -> Redirect to login
3. No raw error pages/stack traces

---

## Test Checklist

| # | Test | Expected Result |
|---|------|-----------------|
| 1 | School admin login (admin@schoola.test) | Dashboard loads |
| 2 | School admin login (admin@schoolb.test) | Dashboard loads |
| 3 | Verify 2 schools exist | Count = 2 |
| 4 | Verify 6 instructors | Count per school = 3 |
| 5 | Verify 12 students | Count per school = 6 |
| 6 | Verify 3 courses per school | Manual, Practical, Combo |
| 7 | Create time slot no instructors | Success |
| 8 | TDC slot > 300 min | Error: exceeds maximum |
| 9 | TDC slot = 300 min | Success |
| 10 | PDC slot > 180 min | Error: exceeds maximum |
| 11 | Slot outside instructor hours | Error: outside working hours |
| 12 | Slot during instructor break | Error: during break |
| 13 | Exceed daily teaching limit | Error: daily limit exceeded |
| 14 | Save working hours | Success message |
| 15 | Uncheck day in form | Removes working hours |
| 16 | Non-existent instructor | 404 |
| 17 | Unauthenticated access | Redirect to login |
| 18 | Save scheduling settings | Values persisted |
| 19 | Existing slots after enabling hours | Still valid |
| 20 | Assign instructor via slot edit | Working hours validated |
| 21 | Student booking (approved enrollment) | Booking created |
| 22 | Instructor schedule view | Shows assigned slots |
| 23 | Student progress | Shows completed lessons |
| 24 | Phase progression request | Appears in admin panel |

---

## Troubleshooting

If any test fails:
- Check storage/logs/laravel.log
- Run: php artisan migrate:status
- Re-seed: php artisan db:seed --class=TwoSchoolTestSeeder --force
- Re-seed test data: php artisan db:seed --class=TestDataSeeder --force
- Clear cache: php artisan optimize:clear