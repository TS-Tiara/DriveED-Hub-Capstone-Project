<?php

/**
 * Admin Modal Test Suite using Laravel Dusk
 * 
 * Tests all admin modal functionality using pre-seeded accounts.
 * 
 * @version 1.0
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminModalTest extends DuskTestCase
{
    protected $schoolSlug = 'smart-driving';
    protected $adminEmail = 'schooladmin@gmail.com';
    protected $adminPassword = 'password123';
    protected $instructorEmail = 'juan.delacruz@smartdriving.com';
    protected $instructorPassword = 'password123';
    protected $currentTestNumber = 0;
    protected $currentTestName = '';

    /**
     * Helper to create organized screenshot path
     */
    protected function screenshot(Browser $browser, string $stepName): void
    {
        $folder = "Test {$this->currentTestNumber} - {$this->currentTestName}";
        $browser->screenshot("{$folder}/{$stepName}");
    }

    /**
     * Helper method to login as admin via the browser
     */
    protected function loginAsAdmin(Browser $browser): void
    {
        $browser->visit("/{$this->schoolSlug}")
                ->waitFor('input[name="email"]')
                ->type('input[name="email"]', $this->adminEmail)
                ->type('input[name="password"]', $this->adminPassword);
        $this->screenshot($browser, '01-credentials-entered');
        $browser->click('button[type="submit"]')
                ->pause(3000);
        $this->screenshot($browser, '02-admin-logged-in');
        $browser->assertPathBeginsWith("/{$this->schoolSlug}/admin");
    }

    /**
     * Helper method to login as instructor via the browser
     */
    protected function loginAsInstructor(Browser $browser): void
    {
        $browser->visit("/{$this->schoolSlug}")
                ->waitFor('input[name="email"]')
                ->type('input[name="email"]', $this->instructorEmail)
                ->type('input[name="password"]', $this->instructorPassword);
        $this->screenshot($browser, '01-credentials-entered');
        $browser->click('button[type="submit"]')
                ->pause(3000);
        $this->screenshot($browser, '02-instructor-logged-in');
        $browser->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
    }

    // ===========================================
    // LOGIN TESTS
    // ===========================================

    public function test_admin_can_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}")
                    ->screenshot('admin-login-01-page-loaded')
                    ->assertSee('Log In')
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->adminEmail)
                    ->type('input[name="password"]', $this->adminPassword)
                    ->screenshot('admin-login-02-credentials-filled')
                    ->click('button[type="submit"]')
                    ->pause(3000)
                    ->screenshot('admin-login-03-dashboard-loaded')
                    ->assertPathBeginsWith("/{$this->schoolSlug}/admin")
                    ->assertSee('Dashboard');
        });
    }

    public function test_instructor_can_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}")
                    ->screenshot('instructor-login-01-page-loaded')
                    ->assertSee('Log In')
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->instructorEmail)
                    ->type('input[name="password"]', $this->instructorPassword)
                    ->screenshot('instructor-login-02-credentials-filled')
                    ->click('button[type="submit"]')
                    ->pause(3000)
                    ->screenshot('instructor-login-03-dashboard-loaded')
                    ->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
        });
    }

    // ===========================================
    // USER MANAGEMENT PAGE TESTS
    // ===========================================

    public function test_user_management_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(3000)
                    ->screenshot('user-mgmt-01-page-loaded');
            
            // Get current URL
            $url = $browser->driver->getCurrentURL();
            $this->assertStringContainsString('user-management', $url);
        });
    }

    public function test_create_student_modal_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->screenshot('student-modal-01-page-loaded')
                    ->waitFor('button[onclick="openCreateStudentModal()"]', 10)
                    ->click('button[onclick="openCreateStudentModal()"]')
                    ->pause(500)
                    ->screenshot('student-modal-02-modal-opened')
                    ->assertVisible('#createStudentModal');
        });
    }

    public function test_create_student_modal_has_required_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->screenshot('student-fields-01-page-loaded')
                    ->click('button[onclick="openCreateStudentModal()"]')
                    ->pause(500)
                    ->screenshot('student-fields-02-modal-with-fields')
                    ->assertPresent('#createStudentModal input[name="name"]')
                    ->assertPresent('#createStudentModal input[name="email"]')
                    ->assertPresent('#createStudentModal input[name="password"]');
        });
    }

    public function test_can_fill_create_student_form(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $uniqueEmail = 'dusktest' . time() . '@test.com';
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->screenshot('student-fill-01-page-loaded')
                    ->click('button[onclick="openCreateStudentModal()"]')
                    ->pause(500)
                    ->screenshot('student-fill-02-modal-opened')
                    ->type('#createStudentModal input[name="name"]', 'Dusk Test Student')
                    ->type('#createStudentModal input[name="email"]', $uniqueEmail)
                    ->type('#createStudentModal input[name="password"]', 'password123')
                    ->screenshot('student-fill-03-form-filled')
                    ->assertInputValue('#createStudentModal input[name="name"]', 'Dusk Test Student');
        });
    }

    public function test_create_instructor_modal_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->screenshot('instructor-modal-01-page-loaded')
                    // Click the instructors tab
                    ->click('.tab[data-tab="instructors"]')
                    ->pause(500)
                    ->screenshot('instructor-modal-02-tab-clicked')
                    ->waitFor('button[onclick="openCreateInstructorModal()"]', 10)
                    ->click('button[onclick="openCreateInstructorModal()"]')
                    ->pause(500)
                    ->screenshot('instructor-modal-03-modal-opened')
                    ->assertVisible('#createInstructorModal');
        });
    }

    // ===========================================
    // COURSE MANAGEMENT PAGE TESTS
    // ===========================================

    public function test_courses_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000)
                    ->screenshot('courses-01-page-loaded')
                    ->assertSee('Course');
        });
    }

    public function test_create_course_modal_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000)
                    ->screenshot('course-modal-01-page-loaded')
                    ->waitFor('button[onclick*="openCourseModal"], .btn-create', 10)
                    ->click('button[onclick*="openCourseModal"], .btn-create')
                    ->pause(500)
                    ->screenshot('course-modal-02-modal-opened')
                    ->assertVisible('#courseModal');
        });
    }

    // ===========================================
    // SCHEDULE MANAGEMENT PAGE TESTS
    // ===========================================

    public function test_schedules_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/schedules")
                    ->pause(2000)
                    ->screenshot('schedules-01-page-loaded')
                    ->assertSee('Schedule');
        });
    }

    public function test_create_schedule_modal_opens(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/schedules")
                    ->pause(2000)
                    ->screenshot('schedule-modal-01-page-loaded')
                    ->waitFor('button[onclick*="openCreateModal"], .btn-create', 10)
                    ->click('button[onclick*="openCreateModal"], .btn-create')
                    ->pause(500)
                    ->screenshot('schedule-modal-02-modal-opened')
                    ->assertVisible('#createModal');
        });
    }

    // ===========================================
    // ENROLLMENT REQUESTS PAGE TESTS
    // ===========================================

    public function test_enrollment_requests_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/enrollments")
                    ->pause(2000)
                    ->screenshot('enrollments-01-page-loaded')
                    ->assertSee('Enrollment');
        });
    }

    public function test_enrollment_reject_modal_exists(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/enrollments")
                    ->pause(2000)
                    ->screenshot('enrollment-reject-modal-01-page-with-modal')
                    ->assertPresent('#rejectModal');
        });
    }

    // ===========================================
    // REMOVAL REQUESTS PAGE TESTS
    // ===========================================

    public function test_removal_requests_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/removal-requests")
                    ->pause(2000)
                    ->screenshot('removal-01-page-loaded');
        });
    }

    public function test_removal_requests_approve_modal_exists(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/removal-requests")
                    ->pause(2000)
                    ->screenshot('removal-approve-modal-01-page-with-modal')
                    ->assertPresent('#approveModal');
        });
    }

    public function test_removal_requests_reject_modal_exists(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/removal-requests")
                    ->pause(2000)
                    ->screenshot('removal-reject-modal-01-page-with-modal')
                    ->assertPresent('#rejectModal');
        });
    }

    // ===========================================
    // SETTINGS PAGE TESTS
    // ===========================================

    public function test_settings_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/settings")
                    ->pause(2000)
                    ->screenshot('settings-01-page-loaded')
                    ->assertSee('Settings');
        });
    }

    // ===========================================
    // INSTRUCTOR PAGES TESTS
    // ===========================================

    public function test_instructor_dashboard_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            
            $browser->visit("/{$this->schoolSlug}/instructor/dashboard")
                    ->pause(2000)
                    ->screenshot('instructor-dashboard-01-page-loaded')
                    ->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
        });
    }

    public function test_instructor_timeslots_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            
            $browser->visit("/{$this->schoolSlug}/instructor/timeslots")
                    ->pause(2000)
                    ->screenshot('instructor-timeslots-01-page-loaded')
                    ->assertSee('Time');
        });
    }

    // ===========================================
    // NAVIGATION TESTS
    // ===========================================

    public function test_admin_can_navigate_all_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            // Dashboard
            $browser->visit("/{$this->schoolSlug}/admin")
                    ->pause(1500)
                    ->screenshot('nav-01-dashboard')
                    ->assertSee('Dashboard');
            
            // User Management
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(1500)
                    ->screenshot('nav-02-user-management')
                    ->assertSee('User Management');
            
            // Courses
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(1500)
                    ->screenshot('nav-03-courses')
                    ->assertSee('Course');
            
            // Schedules
            $browser->visit("/{$this->schoolSlug}/admin/schedules")
                    ->pause(1500)
                    ->screenshot('nav-04-schedules')
                    ->assertSee('Schedule');
            
            // Enrollments
            $browser->visit("/{$this->schoolSlug}/admin/enrollments")
                    ->pause(1500)
                    ->screenshot('nav-05-enrollments')
                    ->assertSee('Enrollment');
            
            // Settings
            $browser->visit("/{$this->schoolSlug}/admin/settings")
                    ->pause(1500)
                    ->screenshot('nav-06-settings')
                    ->assertSee('Settings');
        });
    }
}
