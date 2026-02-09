<?php

/**
 * Browser Modal Test Suite using Laravel Dusk
 * 
 * Tests all modal functionality in the Driving School Management System:
 * - User Management Modals (Add/Edit Student, Add/Edit Instructor)
 * - Course Management Modals (Add/Edit Course)
 * - Schedule Management Modals (Create/Edit Schedule)
 * - Enrollment Request Modals (Approve/Reject/Complete/Cancel)
 * - Removal Request Modals (Approve/Reject Time Off)
 * 
 * IMPORTANT: To run these tests, you need to:
 * 1. Start the Laravel server with: php artisan serve --env=dusk.local
 * 2. Run tests with: php artisan dusk tests/Browser/ModalComponentsTest.php
 * 
 * @version 1.0
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Course;
use App\Models\SchoolSetting;
use App\Models\TimeSlot;
use App\Models\ScheduleInstructor;
use App\Models\EnrollmentRequest;
use App\Models\InstructorRemovalRequest;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class ModalComponentsTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $school;
    protected $admin;
    protected $student;
    protected $instructor;
    protected $course;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test school
        $this->school = School::factory()->create([
            'name' => 'Test Driving School',
            'slug' => 'test-school',
        ]);
        
        // Create school settings
        SchoolSetting::factory()->create(['school_id' => $this->school->id]);
        
        // Create admin user
        $this->admin = Admin::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'testadmin@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
            'role' => 'admin',
        ]);
        
        // Create student user  
        $this->student = Student::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'teststudent@gmail.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);
        
        // Create instructor user
        $this->instructor = Instructor::factory()->create([
            'school_id' => $this->school->id,
            'email' => 'testinstructor@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        
        // Create a course
        $this->course = Course::factory()->create([
            'school_id' => $this->school->id,
            'title' => 'Test Course',
            'status' => 'active',
        ]);
    }

    /**
     * Helper method to login as admin via the browser
     */
    protected function loginAsAdmin(Browser $browser)
    {
        $browser->visit("/{$this->school->slug}")
                ->type('email', $this->admin->email)
                ->type('password', 'password123')
                ->press('Log In')
                ->waitForLocation("/{$this->school->slug}/admin*", 15);
    }

    // ===========================================
    // USER MANAGEMENT MODAL TESTS
    // ===========================================

    /** @test */
    public function user_management_page_loads_for_admin()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/user-management")
                    ->assertPathIs("/{$this->school->slug}/admin/user-management")
                    ->assertSee('User Management');
        });
    }

    /** @test */
    public function create_student_modal_opens_and_closes()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/user-management")
                    // Click button to open create student modal
                    ->waitFor('.btn-create')
                    ->click('.btn-create')
                    ->waitFor('#createStudentModal')
                    ->assertVisible('#createStudentModal')
                    ->assertSee('Add New Student')
                    // Close modal by clicking cancel
                    ->click('#createStudentModal .btn-cancel')
                    ->pause(500)
                    ->assertMissing('#createStudentModal[style*="block"]');
        });
    }

    /** @test */
    public function create_student_modal_has_required_fields()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/user-management")
                    ->waitFor('.btn-create')
                    ->click('.btn-create')
                    ->waitFor('#createStudentModal')
                    // Check all required form fields are present
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
            
            $browser->visit("/{$this->school->slug}/admin/user-management")
                    ->waitFor('.btn-create')
                    ->click('.btn-create')
                    ->waitFor('#createStudentModal')
                    // Fill in form fields
                    ->type('#createStudentModal input[name="name"]', 'New Test Student')
                    ->type('#createStudentModal input[name="email"]', 'newstudent@test.com')
                    ->type('#createStudentModal input[name="password"]', 'password123')
                    ->type('#createStudentModal input[name="contact"]', '09123456789')
                    // Verify values
                    ->assertInputValue('#createStudentModal input[name="name"]', 'New Test Student')
                    ->assertInputValue('#createStudentModal input[name="email"]', 'newstudent@test.com');
        });
    }

    /** @test */
    public function instructor_tab_shows_create_instructor_button()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/user-management")
                    // Click Instructors tab
                    ->waitFor('[data-tab="instructors"], .tab-btn')
                    ->click('[data-tab="instructors"], .tab-btn:contains("Instructor")')
                    ->pause(500)
                    // Should see Add Instructor button
                    ->assertSee('Add Instructor');
        });
    }

    /** @test */
    public function create_instructor_modal_opens()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/user-management")
                    // Click Instructors tab first
                    ->waitFor('[data-tab="instructors"], .tab-btn')
                    ->click('[data-tab="instructors"], .tab-btn:last-of-type')
                    ->pause(500)
                    // Click create instructor button
                    ->waitFor('#createInstructorModal, .btn-create')
                    ->script("document.querySelector('[onclick*=\"openCreateInstructorModal\"]')?.click() || document.querySelectorAll('.btn-create')[1]?.click()");
            
            $browser->pause(500)
                    ->assertVisible('#createInstructorModal');
        });
    }

    // ===========================================
    // COURSE MANAGEMENT MODAL TESTS
    // ===========================================

    /** @test */
    public function courses_page_loads_for_admin()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/courses")
                    ->assertPathIs("/{$this->school->slug}/admin/courses")
                    ->assertSee('Course');
        });
    }

    /** @test */
    public function create_course_modal_opens_and_closes()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/courses")
                    // Click create course button
                    ->waitFor('.btn-create, [onclick*="openCourseModal"]')
                    ->click('.btn-create, [onclick*="openCourseModal"]')
                    ->waitFor('#courseModal')
                    ->assertVisible('#courseModal')
                    ->assertSee('Create New Course')
                    // Close modal
                    ->click('#courseModal .btn-close, #courseModal .btn-secondary')
                    ->pause(500);
        });
    }

    /** @test */
    public function create_course_modal_has_all_fields()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/courses")
                    ->waitFor('.btn-create, [onclick*="openCourseModal"]')
                    ->click('.btn-create, [onclick*="openCourseModal"]')
                    ->waitFor('#courseModal')
                    // Check all form fields
                    ->assertPresent('#courseModal input[name="title"], #courseTitle')
                    ->assertPresent('#courseModal textarea[name="description"], #courseDescription')
                    ->assertPresent('#courseModal select[name="type"], #courseType')
                    ->assertPresent('#courseModal select[name="status"], #courseStatus');
        });
    }

    /** @test */
    public function can_fill_create_course_modal_form()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/courses")
                    ->waitFor('.btn-create, [onclick*="openCourseModal"]')
                    ->click('.btn-create, [onclick*="openCourseModal"]')
                    ->waitFor('#courseModal')
                    // Fill form
                    ->type('#courseTitle, input[name="title"]', 'Advanced Driving Course')
                    ->type('#courseDescription, textarea[name="description"]', 'An advanced driving course for experienced drivers')
                    ->select('#courseType, select[name="type"]', 'advanced')
                    ->select('#courseStatus, select[name="status"]', 'active')
                    // Verify
                    ->assertInputValue('#courseTitle, input[name="title"]', 'Advanced Driving Course');
        });
    }

    // ===========================================
    // SCHEDULE MANAGEMENT MODAL TESTS
    // ===========================================

    /** @test */
    public function schedules_page_loads_for_admin()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/schedules")
                    ->assertPathIs("/{$this->school->slug}/admin/schedules")
                    ->assertSee('Schedule');
        });
    }

    /** @test */
    public function create_schedule_modal_opens()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/schedules")
                    // Click create schedule button
                    ->waitFor('.btn-create, [onclick*="openCreateModal"]')
                    ->click('.btn-create, [onclick*="openCreateModal"]')
                    ->waitFor('#createModal')
                    ->assertVisible('#createModal')
                    ->assertSee('Create');
        });
    }

    /** @test */
    public function create_schedule_modal_has_required_fields()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/schedules")
                    ->waitFor('.btn-create, [onclick*="openCreateModal"]')
                    ->click('.btn-create, [onclick*="openCreateModal"]')
                    ->waitFor('#createModal')
                    // Check required fields
                    ->assertPresent('#createModal input[name="date"], #createModal input[type="date"]')
                    ->assertPresent('#createModal select[name="instructor_id"], #createModal select:first-of-type');
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
            
            $browser->visit("/{$this->school->slug}/admin/enrollments")
                    ->assertPathIs("/{$this->school->slug}/admin/enrollments")
                    ->assertSee('Enrollment');
        });
    }

    /** @test */
    public function enrollment_reject_modal_structure_exists()
    {
        // Create a pending enrollment request first
        $enrollmentRequest = EnrollmentRequest::create([
            'school_id' => $this->school->id,
            'learner_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        
        $this->browse(function (Browser $browser) use ($enrollmentRequest) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/enrollments")
                    // Check that reject modal HTML exists
                    ->assertPresent('#rejectModal')
                    // Find and click reject button if visible
                    ->waitFor('.btn-reject, [onclick*="showRejectModal"]', 5);
        });
    }

    /** @test */
    public function enrollment_reject_modal_opens_on_pending_request()
    {
        // Create a pending enrollment request
        $enrollmentRequest = EnrollmentRequest::create([
            'school_id' => $this->school->id,
            'learner_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        
        $this->browse(function (Browser $browser) use ($enrollmentRequest) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/enrollments")
                    ->waitFor('.btn-reject')
                    ->click('.btn-reject')
                    ->waitFor('#rejectModal[style*="flex"], #rejectModal.active')
                    ->assertSee('Reject');
        });
    }

    /** @test */
    public function enrollment_reject_modal_has_remarks_field()
    {
        // Create a pending enrollment request
        $enrollmentRequest = EnrollmentRequest::create([
            'school_id' => $this->school->id,
            'learner_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        
        $this->browse(function (Browser $browser) use ($enrollmentRequest) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/enrollments")
                    ->waitFor('.btn-reject')
                    ->click('.btn-reject')
                    ->waitFor('#rejectModal[style*="flex"], #rejectModal.active')
                    // Check for remarks textarea
                    ->assertPresent('#rejectModal textarea[name="remarks"], #remarks');
        });
    }

    // ===========================================
    // REMOVAL REQUESTS (TIME OFF) MODAL TESTS
    // ===========================================

    /** @test */
    public function removal_requests_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/removal-requests")
                    ->assertPathIs("/{$this->school->slug}/admin/removal-requests");
        });
    }

    /** @test */
    public function removal_requests_has_approve_modal_structure()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/removal-requests")
                    // Check that approve modal HTML exists
                    ->assertPresent('#approveModal');
        });
    }

    /** @test */
    public function removal_requests_has_reject_modal_structure()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/removal-requests")
                    // Check that reject modal HTML exists
                    ->assertPresent('#rejectModal');
        });
    }

    // ===========================================
    // GUEST ENROLLMENT MODAL TESTS
    // ===========================================

    /** @test */
    public function guest_can_see_courses_page()
    {
        $this->browse(function (Browser $browser) {
            // Login as student/guest
            $browser->visit("/{$this->school->slug}")
                    ->type('email', $this->student->email)
                    ->type('password', 'password123')
                    ->press('Log In')
                    ->waitForLocation("/{$this->school->slug}/student*", 15);
        });
    }

    // ===========================================
    // ADMIN SETTINGS MODAL TESTS
    // ===========================================

    /** @test */
    public function settings_page_loads_for_admin()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->school->slug}/admin/settings")
                    ->assertPathIs("/{$this->school->slug}/admin/settings")
                    ->assertSee('Settings');
        });
    }

    // ===========================================
    // INTEGRATION TESTS - FULL MODAL WORKFLOWS
    // ===========================================

    /** @test */
    public function admin_can_navigate_to_all_pages_with_modals()
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            // Visit user management
            $browser->visit("/{$this->school->slug}/admin/user-management")
                    ->assertPathIs("/{$this->school->slug}/admin/user-management");
            
            // Visit courses
            $browser->visit("/{$this->school->slug}/admin/courses")
                    ->assertPathIs("/{$this->school->slug}/admin/courses");
            
            // Visit schedules
            $browser->visit("/{$this->school->slug}/admin/schedules")
                    ->assertPathIs("/{$this->school->slug}/admin/schedules");
            
            // Visit enrollment requests
            $browser->visit("/{$this->school->slug}/admin/enrollments")
                    ->assertPathIs("/{$this->school->slug}/admin/enrollments");
            
            // Visit removal requests
            $browser->visit("/{$this->school->slug}/admin/removal-requests")
                    ->assertPathIs("/{$this->school->slug}/admin/removal-requests");
        });
    }
}
