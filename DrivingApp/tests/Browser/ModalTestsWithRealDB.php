<?php

/**
 * Browser Modal Test Suite using Laravel Dusk - Production Database Tests
 * 
 * Tests all modal functionality using the PRODUCTION/DEV MySQL database.
 * This test suite uses pre-seeded accounts from the UnifiedSeeder.
 * 
 * IMPORTANT: Before running these tests:
 * 1. Make sure your MySQL database is running
 * 2. Make sure you've run: php artisan migrate:fresh --seed
 * 3. Start the server: php artisan serve
 * 4. Run tests: php artisan dusk tests/Browser/ModalTestsWithRealDB.php
 * 
 * Test Accounts (from UnifiedSeeder):
 * - School: smart-driving (Smart Driving School)
 * - Admin: schooladmin@gmail.com / password123
 * - Instructor: juan.delacruz@smartdriving.com / password123
 * - Student: studenttest@gmail.com / password123
 * 
 * @version 1.0
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ModalTestsWithRealDB extends DuskTestCase
{
    // Use the seeded test accounts
    protected $schoolSlug = 'smart-driving';
    protected $adminEmail = 'schooladmin@gmail.com';
    protected $adminPassword = 'password123';
    protected $instructorEmail = 'juan.delacruz@smartdriving.com';
    protected $instructorPassword = 'password123';

    /**
     * Helper method to login as admin via the browser
     */
    protected function loginAsAdmin(Browser $browser)
    {
        $browser->visit("/{$this->schoolSlug}")
                ->waitFor('input[name="email"]')
                ->type('input[name="email"]', $this->adminEmail)
                ->type('input[name="password"]', $this->adminPassword)
                ->click('button[type="submit"]')
                ->pause(3000)
                ->assertPathBeginsWith("/{$this->schoolSlug}/admin");
    }

    /**
     * Helper method to login as instructor via the browser
     */
    protected function loginAsInstructor(Browser $browser)
    {
        $browser->visit("/{$this->schoolSlug}")
                ->waitFor('input[name="email"]')
                ->type('input[name="email"]', $this->instructorEmail)
                ->type('input[name="password"]', $this->instructorPassword)
                ->click('button[type="submit"]')
                ->pause(3000)
                ->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
    }

    // ===========================================
    // ADMIN LOGIN TESTS
    // ===========================================

    /** @test */
    public function admin_can_login_with_seeded_account()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}")
                    ->assertSee('Log In')
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->adminEmail)
                    ->type('input[name="password"]', $this->adminPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000)
                    ->assertPathBeginsWith("/{$this->schoolSlug}/admin")
                    ->assertSee('Dashboard');
        });
    }

    // ===========================================
    // USER MANAGEMENT MODAL TESTS
    // ===========================================

    /** @test */
    public function user_management_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->assertSee('User Management');
        });
    }

    /** @test */
    public function create_student_modal_opens_and_closes()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->waitFor('button.btn-create', 10)
                    ->click('.btn-create')
                    ->waitFor('#createStudentModal[style*="block"], #createStudentModal.active, #createStudentModal:not([style*="none"])')
                    ->pause(300)
                    ->assertVisible('#createStudentModal')
                    ->assertSee('Add New Student')
                    // Close modal
                    ->click('#createStudentModal .btn-cancel')
                    ->pause(500);
        });
    }

    /** @test */
    public function create_student_modal_has_required_fields()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->waitFor('.btn-create')
                    ->click('.btn-create')
                    ->waitFor('#createStudentModal[style*="block"], #createStudentModal.active')
                    ->pause(300)
                    ->assertPresent('#createStudentModal input[name="name"]')
                    ->assertPresent('#createStudentModal input[name="email"]')
                    ->assertPresent('#createStudentModal input[name="password"]')
                    ->assertPresent('#createStudentModal input[name="contact"]');
        });
    }

    /** @test */
    public function can_fill_create_student_modal_form()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->waitFor('.btn-create')
                    ->click('.btn-create')
                    ->waitFor('#createStudentModal[style*="block"], #createStudentModal.active')
                    ->pause(300)
                    ->type('#createStudentModal input[name="name"]', 'Dusk Test Student')
                    ->type('#createStudentModal input[name="email"]', 'dusktest' . time() . '@test.com')
                    ->type('#createStudentModal input[name="password"]', 'password123')
                    ->type('#createStudentModal input[name="contact"]', '09123456789')
                    ->assertInputValue('#createStudentModal input[name="name"]', 'Dusk Test Student');
        });
    }

    // ===========================================
    // COURSE MANAGEMENT MODAL TESTS
    // ===========================================

    /** @test */
    public function courses_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->assertPathIs("/{$this->schoolSlug}/admin/courses")
                    ->assertSee('Course');
        });
    }

    /** @test */
    public function create_course_modal_opens()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->waitFor('.btn-create, [onclick*="openCourseModal"]')
                    ->click('.btn-create, [onclick*="openCourseModal"]')
                    ->waitFor('#courseModal[style*="block"], #courseModal.active')
                    ->pause(300)
                    ->assertVisible('#courseModal')
                    ->assertSee('Course');
        });
    }

    /** @test */
    public function create_course_modal_has_all_fields()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->waitFor('.btn-create, [onclick*="openCourseModal"]')
                    ->click('.btn-create, [onclick*="openCourseModal"]')
                    ->waitFor('#courseModal[style*="block"], #courseModal.active')
                    ->pause(300)
                    ->assertPresent('#courseTitle, #courseModal input[name="title"]')
                    ->assertPresent('#courseDescription, #courseModal textarea[name="description"]')
                    ->assertPresent('#courseType, #courseModal select[name="type"]')
                    ->assertPresent('#courseStatus, #courseModal select[name="status"]');
        });
    }

    /** @test */
    public function can_fill_create_course_modal_form()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->waitFor('.btn-create, [onclick*="openCourseModal"]')
                    ->click('.btn-create, [onclick*="openCourseModal"]')
                    ->waitFor('#courseModal[style*="block"], #courseModal.active')
                    ->pause(300)
                    ->type('#courseTitle', 'Dusk Test Course')
                    ->type('#courseDescription', 'A test course created by Dusk')
                    ->assertInputValue('#courseTitle', 'Dusk Test Course');
        });
    }

    // ===========================================
    // SCHEDULE MANAGEMENT MODAL TESTS
    // ===========================================

    /** @test */
    public function schedules_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/schedules")
                    ->assertPathIs("/{$this->schoolSlug}/admin/schedules")
                    ->assertSee('Schedule');
        });
    }

    /** @test */
    public function create_schedule_modal_opens()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/schedules")
                    ->waitFor('.btn-create, [onclick*="openCreateModal"]')
                    ->click('.btn-create, [onclick*="openCreateModal"]')
                    ->waitFor('#createModal[style*="block"], #createModal.active')
                    ->pause(300)
                    ->assertVisible('#createModal');
        });
    }

    // ===========================================
    // ENROLLMENT REQUESTS MODAL TESTS
    // ===========================================

    /** @test */
    public function enrollment_requests_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/enrollments")
                    ->assertPathIs("/{$this->schoolSlug}/admin/enrollments")
                    ->assertSee('Enrollment');
        });
    }

    /** @test */
    public function enrollment_page_has_reject_modal_structure()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/enrollments")
                    ->assertPresent('#rejectModal');
        });
    }

    // ===========================================
    // REMOVAL REQUESTS MODAL TESTS
    // ===========================================

    /** @test */
    public function removal_requests_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/removal-requests")
                    ->assertPathIs("/{$this->schoolSlug}/admin/removal-requests");
        });
    }

    /** @test */
    public function removal_requests_has_approve_modal()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/removal-requests")
                    ->assertPresent('#approveModal');
        });
    }

    /** @test */
    public function removal_requests_has_reject_modal()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/removal-requests")
                    ->assertPresent('#rejectModal');
        });
    }

    // ===========================================
    // SETTINGS PAGE TESTS
    // ===========================================

    /** @test */
    public function settings_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/settings")
                    ->assertPathIs("/{$this->schoolSlug}/admin/settings")
                    ->assertSee('Settings');
        });
    }

    // ===========================================
    // INSTRUCTOR TESTS
    // ===========================================

    /** @test */
    public function instructor_can_login_with_seeded_account()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}")
                    ->type('email', $this->instructorEmail)
                    ->type('password', $this->instructorPassword)
                    ->press('Log In')
                    ->waitForLocation("/{$this->schoolSlug}/instructor*", 15)
                    ->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
        });
    }

    /** @test */
    public function instructor_timeslots_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            
            $browser->visit("/{$this->schoolSlug}/instructor/timeslots")
                    ->assertPathIs("/{$this->schoolSlug}/instructor/timeslots");
        });
    }

    // ===========================================
    // FULL NAVIGATION TEST
    // ===========================================

    /** @test */
    public function admin_can_navigate_to_all_modal_pages()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            // User management
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->assertPathIs("/{$this->schoolSlug}/admin/user-management");
            
            // Courses
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->assertPathIs("/{$this->schoolSlug}/admin/courses");
            
            // Schedules
            $browser->visit("/{$this->schoolSlug}/admin/schedules")
                    ->assertPathIs("/{$this->schoolSlug}/admin/schedules");
            
            // Enrollments
            $browser->visit("/{$this->schoolSlug}/admin/enrollments")
                    ->assertPathIs("/{$this->schoolSlug}/admin/enrollments");
            
            // Removal requests
            $browser->visit("/{$this->schoolSlug}/admin/removal-requests")
                    ->assertPathIs("/{$this->schoolSlug}/admin/removal-requests");
            
            // Settings
            $browser->visit("/{$this->schoolSlug}/admin/settings")
                    ->assertPathIs("/{$this->schoolSlug}/admin/settings");
        });
    }
}
