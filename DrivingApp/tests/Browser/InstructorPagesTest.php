<?php

/**
 * Comprehensive Instructor Pages Tests
 * Tests 198-259: All instructor features
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InstructorPagesTest extends DuskTestCase
{
    protected $schoolSlug = 'smart-driving';
    protected $instructorEmail = 'juan.delacruz@smartdriving.com';
    protected $instructorPassword = 'password123';
    protected $currentTestNumber = 0;
    protected $currentTestName = '';
    protected $role = 'Instructor';

    protected function screenshot(Browser $browser, string $stepName): void
    {
        $folderNumber = str_pad((string) $this->currentTestNumber, 3, '0', STR_PAD_LEFT);
        $scenario = "Test {$folderNumber} - {$this->currentTestName}";
        $this->captureRoleScreenshot($browser, $this->role, $scenario, $stepName);
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
     * @group instructor
     * @group dashboard
     */
    public function test_198_instructor_dashboard_display(): void
    {
        $this->currentTestNumber = 198;
        $this->currentTestName = 'Instructor Dashboard Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-instructor-logged-in');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
            $this->screenshot($browser, '02-on-instructor-dashboard');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-dashboard-fully-loaded');
        });
    }

    /**
     * @group instructor
     * @group dashboard
     */
    public function test_199_dashboard_statistics(): void
    {
        $this->currentTestNumber = 199;
        $this->currentTestName = 'Instructor Dashboard Statistics';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-dashboard-loaded');
            
            $browser->pause(2000);
            $this->screenshot($browser, '02-statistics-visible');
            
            $browser->assertVisible('.card, .stat-card');
            $this->screenshot($browser, '03-statistics-cards-displayed');
        });
    }

    /**
     * @group instructor
     * @group schedule
     */
    public function test_202_my_schedule_page_display(): void
    {
        $this->currentTestNumber = 202;
        $this->currentTestName = 'Instructor My Schedule Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/instructor/my-schedule");
            $this->screenshot($browser, '02-navigating-to-schedule');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-schedule-page-loaded');
            
            $browser->assertSee('Schedule');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group instructor
     * @group schedule
     */
    public function test_203_view_timeslots_calendar(): void
    {
        $this->currentTestNumber = 203;
        $this->currentTestName = 'View Timeslots Calendar';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            
            $browser->visit("/{$this->schoolSlug}/instructor/my-schedule")
                    ->pause(2000);
            $this->screenshot($browser, '01-schedule-page-loaded');
            
            // Should show timeslots or calendar
            $browser->assertVisible('.timeslot, .schedule-item, table');
            $this->screenshot($browser, '02-timeslots-visible');
        });
    }

    /**
     * @group instructor
     * @group students
     */
    public function test_210_my_students_page_display(): void
    {
        $this->currentTestNumber = 210;
        $this->currentTestName = 'Instructor My Students Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/instructor/students");
            $this->screenshot($browser, '02-navigating-to-students');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-students-page-loaded');
            
            $browser->assertSee('Students');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group instructor
     * @group students
     */
    public function test_211_students_list_view(): void
    {
        $this->currentTestNumber = 211;
        $this->currentTestName = 'Instructor Students List View';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            
            $browser->visit("/{$this->schoolSlug}/instructor/students")
                    ->pause(2000);
            $this->screenshot($browser, '01-students-page-loaded');
            
            // Should show students table or list
            $browser->assertVisible('table, .student-item, .student-card');
            $this->screenshot($browser, '02-students-list-visible');
        });
    }

    /**
     * @group instructor
     * @group progress
     */
    public function test_215_progress_page_display(): void
    {
        $this->currentTestNumber = 215;
        $this->currentTestName = 'Instructor Progress Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/instructor/progress");
            $this->screenshot($browser, '02-navigating-to-progress');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-progress-page-loaded');
            
            $browser->assertSee('Progress');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group instructor
     * @group reports
     */
    public function test_248_reports_page_display(): void
    {
        $this->currentTestNumber = 248;
        $this->currentTestName = 'Instructor Reports Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/instructor/reports");
            $this->screenshot($browser, '02-navigating-to-reports');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-reports-page-loaded');
            
            $browser->assertSee('Reports');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group instructor
     * @group grades
     */
    public function test_251_grades_page_display(): void
    {
        $this->currentTestNumber = 251;
        $this->currentTestName = 'Instructor Grades Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/instructor/grades");
            $this->screenshot($browser, '02-navigating-to-grades');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-grades-page-loaded');
            
            $browser->assertSee('Grades');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group instructor
     * @group profile
     */
    public function test_255_instructor_profile_page_display(): void
    {
        $this->currentTestNumber = 255;
        $this->currentTestName = 'Instructor Profile Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/instructor/profile");
            $this->screenshot($browser, '02-navigating-to-profile');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-profile-page-loaded');
            
            $browser->assertSee('Profile');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group instructor
     * @group profile
     */
    public function test_256_update_profile_form(): void
    {
        $this->currentTestNumber = 256;
        $this->currentTestName = 'Instructor Update Profile Form';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            
            $browser->visit("/{$this->schoolSlug}/instructor/profile")
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
}
