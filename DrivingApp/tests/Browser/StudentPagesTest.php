<?php

/**
 * Comprehensive Student Dashboard and Pages Tests
 * Tests 260-290: All student features
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudentPagesTest extends DuskTestCase
{
    protected $schoolSlug = 'smart-driving';
    
    // Student Account (Enrolled Student)
    protected $studentEmail = 'maria.santos@gmail.com';
    protected $studentPassword = 'password123';
    
    // School Admin Account
    protected $adminEmail = 'schooladmin@gmail.com';
    protected $adminPassword = 'password123';
    
    // Instructor Account
    protected $instructorEmail = 'juan.delacruz@smartdriving.com';
    protected $instructorPassword = 'password123';
    
    // Guest Account (Becomes Student after verification/enrollment)
    protected $guestEmail = 'guest.user@gmail.com';
    protected $guestPassword = 'password123';
    
    protected $currentTestNumber = 0;
    protected $currentTestName = '';
    protected $role = 'Student';

    protected function screenshot(Browser $browser, string $stepName): void
    {
        $folderNumber = str_pad((string) $this->currentTestNumber, 3, '0', STR_PAD_LEFT);
        $scenario = "Test {$folderNumber} - {$this->currentTestName}";
        $this->captureRoleScreenshot($browser, $this->role, $scenario, $stepName);
    }

    protected function loginAsStudent(Browser $browser): void
    {
        $browser->visit("/{$this->schoolSlug}")
                ->waitFor('input[name="email"]')
                ->type('input[name="email"]', $this->studentEmail)
                ->type('input[name="password"]', $this->studentPassword)
                ->click('button[type="submit"]')
                ->pause(3000)
                ->assertPathBeginsWith("/{$this->schoolSlug}/student");
    }

    protected function loginAsGuest(Browser $browser): void
    {
        $browser->visit("/{$this->schoolSlug}")
                ->waitFor('input[name="email"]')
                ->type('input[name="email"]', $this->guestEmail)
                ->type('input[name="password"]', $this->guestPassword)
                ->click('button[type="submit"]')
                ->pause(3000)
                ->assertPathBeginsWith("/{$this->schoolSlug}/guest");
    }

    protected function loginAsAdmin(Browser $browser): void
    {
        $browser->visit("/{$this->schoolSlug}")
                ->waitFor('input[name="email"]')
                ->type('input[name="email"]', $this->adminEmail)
                ->type('input[name="password"]', $this->adminPassword)
                ->click('button[type="submit"]')
                ->pause(3000)
                ->assertPathBeginsWith("/{$this->schoolSlug}/admin");
    }

    protected function loginAsInstructor(Browser $browser): void
    {
        $browser->visit("/{$this->schoolSlug}")
                ->waitFor('input[name="email"]')
                ->type('input[name="email"]', $this->instructorEmail)
                ->type('input[name="password"]', $this->instructorPassword)
                ->click('button[type="submit"]')
                ->pause(3000)
                ->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
    }

    /**
     * @group student
     * @group dashboard
     */
    public function test_260_student_dashboard_display(): void
    {
        $this->currentTestNumber = 260;
        $this->currentTestName = 'Student Dashboard Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-student-logged-in');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/student");
            $this->screenshot($browser, '02-on-student-dashboard');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-dashboard-fully-loaded');
        });
    }

    /**
     * @group student
     * @group dashboard
     */
    public function test_261_dashboard_statistics(): void
    {
        $this->currentTestNumber = 261;
        $this->currentTestName = 'Student Dashboard Statistics';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-dashboard-loaded');
            
            $browser->pause(2000);
            $this->screenshot($browser, '02-statistics-visible');
            
            $browser->assertVisible('.card, .stat-card');
            $this->screenshot($browser, '03-statistics-cards-displayed');
        });
    }

    /**
     * @group student
     * @group courses
     */
    public function test_265_courses_page_display(): void
    {
        $this->currentTestNumber = 265;
        $this->currentTestName = 'Student Courses Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/student/courses");
            $this->screenshot($browser, '02-navigating-to-courses');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-courses-page-loaded');
            
            $browser->assertSee('Courses');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group student
     * @group courses
     */
    public function test_266_view_available_courses(): void
    {
        $this->currentTestNumber = 266;
        $this->currentTestName = 'View Available Courses';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            
            $browser->visit("/{$this->schoolSlug}/student/courses")
                    ->pause(2000);
            $this->screenshot($browser, '01-courses-page-loaded');
            
            $browser->assertVisible('.course-card, .course-item, table');
            $this->screenshot($browser, '02-courses-list-visible');
        });
    }

    /**
     * @group student
     * @group schedule
     */
    public function test_281_schedule_page_display(): void
    {
        $this->currentTestNumber = 281;
        $this->currentTestName = 'Student Schedule Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/student/schedule");
            $this->screenshot($browser, '02-navigating-to-schedule');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-schedule-page-loaded');
            
            $browser->assertSee('Schedule');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group student
     * @group schedule
     */
    public function test_282_view_available_timeslots(): void
    {
        $this->currentTestNumber = 282;
        $this->currentTestName = 'View Available Timeslots';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            
            $browser->visit("/{$this->schoolSlug}/student/schedule")
                    ->pause(2000);
            $this->screenshot($browser, '01-schedule-page-loaded');
            
            // Should show calendar or timeslot list
            $browser->assertVisible('.timeslot, .schedule-item, table');
            $this->screenshot($browser, '02-timeslots-visible');
        });
    }

    /**
     * @group student
     * @group progress
     */
    public function test_291_progress_page_display(): void
    {
        $this->currentTestNumber = 291;
        $this->currentTestName = 'Student Progress Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/student/progress");
            $this->screenshot($browser, '02-navigating-to-progress');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-progress-page-loaded');
            
            $browser->assertSee('Progress');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group student
     * @group progress
     */
    public function test_292_view_progress_timeline(): void
    {
        $this->currentTestNumber = 292;
        $this->currentTestName = 'View Progress Timeline';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            
            $browser->visit("/{$this->schoolSlug}/student/progress")
                    ->pause(2000);
            $this->screenshot($browser, '01-progress-page-loaded');
            
            // Should show progress bars, timeline, or stats
            $browser->assertVisible('.progress, .timeline, .progress-bar');
            $this->screenshot($browser, '02-progress-timeline-visible');
        });
    }

    /**
     * @group student
     * @group payments
     */
    public function test_296_payments_page_display(): void
    {
        $this->currentTestNumber = 296;
        $this->currentTestName = 'Student Payments Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/student/payments");
            $this->screenshot($browser, '02-navigating-to-payments');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-payments-page-loaded');
            
            $browser->assertSee('Payments');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group student
     * @group payments
     */
    public function test_297_view_payment_history(): void
    {
        $this->currentTestNumber = 297;
        $this->currentTestName = 'View Payment History';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            
            $browser->visit("/{$this->schoolSlug}/student/payments")
                    ->pause(2000);
            $this->screenshot($browser, '01-payments-page-loaded');
            
            // Should show payment history table or list
            $browser->assertVisible('table, .payment-item, .payment-card');
            $this->screenshot($browser, '02-payment-history-visible');
        });
    }

    /**
     * @group student
     * @group profile
     */
    public function test_301_student_profile_page_display(): void
    {
        $this->currentTestNumber = 301;
        $this->currentTestName = 'Student Profile Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/student/profile");
            $this->screenshot($browser, '02-navigating-to-profile');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-profile-page-loaded');
            
            $browser->assertSee('Profile');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group student
     * @group profile
     */
    public function test_302_update_profile_form(): void
    {
        $this->currentTestNumber = 302;
        $this->currentTestName = 'Student Update Profile Form';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            
            $browser->visit("/{$this->schoolSlug}/student/profile")
                    ->pause(2000);
            $this->screenshot($browser, '01-profile-page-loaded');
            
            $browser->assertVisible('input[name="first_name"], input[name="name"]');
            $this->screenshot($browser, '02-name-field-visible');
            
            $browser->assertVisible('input[name="email"]');
            $this->screenshot($browser, '03-email-field-visible');
            
            $browser->assertVisible('input[name="phone"]');
            $this->screenshot($browser, '04-phone-field-visible');
            
            $this->screenshot($browser, '05-all-profile-fields-visible');
        });
    }

    /**
     * @group student
     * @group my-course
     */
    public function test_270_my_course_page_display(): void
    {
        $this->currentTestNumber = 270;
        $this->currentTestName = 'Student My Course Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/student/my-course");
            $this->screenshot($browser, '02-navigating-to-my-course');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-my-course-page-loaded');
        });
    }

    /**
     * @group guest
     * @group student
     * @group guest-to-student-flow
     */
    public function test_350_guest_can_view_courses(): void
    {
        $this->currentTestNumber = 350;
        $this->currentTestName = 'Guest Can View Courses';
        $this->role = 'Guest';

        $this->browse(function (Browser $browser) {
            $this->loginAsGuest($browser);
            $this->screenshot($browser, '01-guest-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/guest/courses");
            $this->screenshot($browser, '02-navigating-to-courses');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-guest-courses-page-loaded');
            
            $browser->assertSee('Courses');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group guest
     * @group enrollment
     */
    public function test_351_guest_dashboard_display(): void
    {
        $this->currentTestNumber = 351;
        $this->currentTestName = 'Guest Dashboard Display';
        $this->role = 'Guest';

        $this->browse(function (Browser $browser) {
            $this->loginAsGuest($browser);
            $this->screenshot($browser, '01-guest-logged-in');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/guest");
            $this->screenshot($browser, '02-on-guest-dashboard');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-guest-dashboard-loaded');
        });
    }

    /**
     * @group guest
     * @group enrollment
     */
    public function test_352_guest_enrollment_requests_page(): void
    {
        $this->currentTestNumber = 352;
        $this->currentTestName = 'Guest Enrollment Requests Page';
        $this->role = 'Guest';

        $this->browse(function (Browser $browser) {
            $this->loginAsGuest($browser);
            $this->screenshot($browser, '01-guest-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/guest/enrollment-requests");
            $this->screenshot($browser, '02-navigating-to-enrollment-requests');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-enrollment-requests-page-loaded');
        });
    }
}
