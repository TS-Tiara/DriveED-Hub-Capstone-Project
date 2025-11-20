# Multi-Tenant Views Setup Guide

## Current Status
- ✅ Multi-tenant routing works (can access any school via URL)
- ✅ Database isolation works (each school has separate data via `school_id`)
- ❌ Only `drivingschool1` views exist in `resources/views/drivingschool1/`

## The Problem
When trying to access any school other than `drivingschool1` (e.g., `elite-driving-academy`):
- User gets a white screen / View not found error
- System looks for: `resources/views/elite-driving-academy/login.blade.php` → Not found
- System falls back to: `resources/views/schools/default/login.blade.php` → Not found
- Result: Error

## How the View Resolution Works
The `School` model has a `resolveView()` method that:
1. First checks if `resources/views/{school-slug}/{view}.blade.php` exists
2. If not found, falls back to `resources/views/schools/default/{view}.blade.php`
3. If neither exists, throws an error

## Solution: Create Default Fallback Views

### Option A: Universal Default Template (Recommended)
Create a `schools/default/` folder that all schools use unless they have custom views.

**Command:**
```powershell
New-Item -ItemType Directory -Force -Path "resources\views\schools\default"
Copy-Item -Path "resources\views\drivingschool1\*" -Destination "resources\views\schools\default\" -Recurse -Force
```

**Result:**
- All schools can now be accessed with the same view templates
- Data shown is specific to each school (controlled by database `school_id`)
- Each school owner sees only their own data

### Option B: School-Specific Views (For Custom Branding)
Create separate view folders for each school if they need custom layouts/branding.

**Command (for each new school):**
```powershell
Copy-Item -Path "resources\views\drivingschool1\*" -Destination "resources\views\{new-school-slug}\" -Recurse -Force
```

## After Setup
Once `schools/default/` is created:
- Any school added to the database will automatically work
- No need to create view folders for each new school
- School-specific customization is still possible by creating a school-specific folder

## Test After Setup
1. Create a test school in database (or use existing `elite-driving-academy`)
2. Access: `http://localhost:8000/elite-driving-academy`
3. Should see login page (same template as drivingschool1, but for that school's data)

## Current Test School
- URL: `http://localhost:8000/drivingschool1`
- Admin: `admin@drivingschool1.com` / `password123`
- Data: 3 admins, 5 instructors, 10 students, 3 courses, 60 schedules

---

## Important Note for Seeders
**When creating seeders for real accounts:**
- Use Gmail addresses (e.g., `user@gmail.com`) instead of custom domain emails
- Users will use their real-life emails and phone numbers
- Test accounts can use fake data, but production seeders should accommodate real Gmail addresses

---
**Note:** This is for LATER implementation after core system features are complete.
