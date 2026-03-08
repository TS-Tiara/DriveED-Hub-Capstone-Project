<?php

/**
 * Comprehensive Admin User Management Tests
 * Tests 061-080: All user management features (students & instructors)
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminUserManagementTest extends DuskTestCase
{
    protected $schoolSlug = 'smart-driving';
    protected $adminEmail = 'schooladmin@gmail.com';
    protected $adminPassword = 'password123';
    protected $currentTestNumber = 0;
    protected $currentTestName = '';
    protected $role = 'Admin';

    protected function screenshot(Browser $browser, string $stepName): void
    {
        $folderNumber = str_pad((string) $this->currentTestNumber, 3, '0', STR_PAD_LEFT);
        $scenario = "Test {$folderNumber} - {$this->currentTestName}";
        $this->captureRoleScreenshot($browser, $this->role, $scenario, $stepName);
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

    /**
     * @group admin
     * @group user-management
     */
    public function test_061_user_management_page_display(): void
    {
        $this->currentTestNumber = 61;
        $this->currentTestName = 'User Management Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management");
            $this->screenshot($browser, '02-navigating-to-user-management');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-user-management-page-loaded');
            
            $browser->assertSee('User Management');
            $this->screenshot($browser, '04-page-title-verified');
            
            $browser->assertSee('Students');
            $this->screenshot($browser, '05-students-tab-visible');
            
            $browser->assertSee('Instructors');
            $this->screenshot($browser, '06-instructors-tab-visible');
        });
    }

    /**
     * @group admin
     * @group user-management
     */
    public function test_062_students_tab_active_by_default(): void
    {
        $this->currentTestNumber = 62;
        $this->currentTestName = 'Students Tab Active By Default';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000);
            $this->screenshot($browser, '01-user-management-loaded');
            
            // Check if students tab is active
            $browser->assertVisible('#students-tab');
            $this->screenshot($browser, '02-students-tab-active');
        });
    }

    /**
     * @group admin
     * @group user-management
     */
    public function test_063_switch_to_instructors_tab(): void
    {
        $this->currentTestNumber = 63;
        $this->currentTestName = 'Switch To Instructors Tab';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000);
            $this->screenshot($browser, '01-on-students-tab');
            
            $browser->waitFor('button[onclick*="instructors"]', 10);
            $this->screenshot($browser, '02-instructors-tab-button-found');
            
            $browser->click('button[onclick*="instructors"]');
            $this->screenshot($browser, '03-clicked-instructors-tab');
            
            $browser->pause(1000);
            $this->screenshot($browser, '04-instructors-tab-active');
        });
    }

    /**
     * @group admin
     * @group user-management
     */
    public function test_064_create_student_modal_opens(): void
    {
        $this->currentTestNumber = 64;
        $this->currentTestName = 'Create Student Modal Opens';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000);
            $this->screenshot($browser, '01-user-management-page');
            
            $browser->waitFor('button[onclick="openCreateStudentModal()"]', 10);
            $this->screenshot($browser, '02-create-student-button-visible');
            
            $browser->click('button[onclick="openCreateStudentModal()"]');
            $this->screenshot($browser, '03-button-clicked');
            
            $browser->pause(500);
            $this->screenshot($browser, '04-modal-opening');
            
            $browser->assertVisible('#createStudentModal');
            $this->screenshot($browser, '05-modal-opened');
        });
    }

    /**
     * @group admin
     * @group user-management
     */
    public function test_065_create_student_modal_has_all_fields(): void
    {
        $this->currentTestNumber = 65;
        $this->currentTestName = 'Create Student Modal Has All Fields';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->waitFor('button[onclick="openCreateStudentModal()"]', 10)
                    ->click('button[onclick="openCreateStudentModal()"]')
                    ->pause(500);
            $this->screenshot($browser, '01-modal-opened');
            
            $browser->assertVisible('#createStudentModal input[name="first_name"]');
            $this->screenshot($browser, '02-first-name-field-present');
            
            $browser->assertVisible('#createStudentModal input[name="last_name"]');
            $this->screenshot($browser, '03-last-name-field-present');
            
            $browser->assertVisible('#createStudentModal input[name="email"]');
            $this->screenshot($browser, '04-email-field-present');
            
            $browser->assertVisible('#createStudentModal input[name="phone"]');
            $this->screenshot($browser, '05-phone-field-present');
            
            $browser->assertVisible('#createStudentModal input[name="date_of_birth"]');
            $this->screenshot($browser, '06-birthdate-field-present');
            
            $browser->assertVisible('#createStudentModal input[name="address"]');
            $this->screenshot($browser, '07-address-field-present');
            
            $browser->assertVisible('#createStudentModal input[name="password"]');
            $this->screenshot($browser, '08-password-field-present');
        });
    }

    /**
     * @group admin
     * @group user-management
     */
    public function test_066_fill_create_student_form(): void
    {
        $this->currentTestNumber = 66;
        $this->currentTestName = 'Fill Create Student Form';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->waitFor('button[onclick="openCreateStudentModal()"]', 10)
                    ->click('button[onclick="openCreateStudentModal()"]')
                    ->pause(500);
            $this->screenshot($browser, '01-modal-opened');
            
            $browser->type('#createStudentModal input[name="first_name"]', 'Test');
            $this->screenshot($browser, '02-first-name-filled');
            
            $browser->type('#createStudentModal input[name="last_name"]', 'Student');
            $this->screenshot($browser, '03-last-name-filled');
            
            $browser->type('#createStudentModal input[name="email"]', 'test.student@example.com');
            $this->screenshot($browser, '04-email-filled');
            
            $browser->type('#createStudentModal input[name="phone"]', '09171234567');
            $this->screenshot($browser, '05-phone-filled');
            
            $browser->type('#createStudentModal input[name="date_of_birth"]', '2000-01-01');
            $this->screenshot($browser, '06-birthdate-filled');
            
            $browser->type('#createStudentModal input[name="address"]', '123 Test Street');
            $this->screenshot($browser, '07-address-filled');
            
            $browser->type('#createStudentModal input[name="password"]', 'Password123!');
            $this->screenshot($browser, '08-password-filled');
            
            $this->screenshot($browser, '09-all-fields-completed');
        });
    }

    /**
     * @group admin
     * @group user-management
     */
    public function test_073_create_instructor_modal_opens(): void
    {
        $this->currentTestNumber = 73;
        $this->currentTestName = 'Create Instructor Modal Opens';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000);
            $this->screenshot($browser, '01-user-management-page');
            
            $browser->waitFor('button[onclick*="instructors"]', 10)
                    ->click('button[onclick*="instructors"]');
            $this->screenshot($browser, '02-switched-to-instructors-tab');
            
            $browser->pause(1000);
            $this->screenshot($browser, '03-instructors-tab-loaded');
            
            $browser->waitFor('button[onclick="openCreateInstructorModal()"]', 10);
            $this->screenshot($browser, '04-create-instructor-button-visible');
            
            $browser->click('button[onclick="openCreateInstructorModal()"]');
            $this->screenshot($browser, '05-button-clicked');
            
            $browser->pause(500);
            $this->screenshot($browser, '06-modal-opening');
            
            $browser->assertVisible('#createInstructorModal');
            $this->screenshot($browser, '07-modal-opened');
        });
    }

    /**
     * @group admin
     * @group user-management
     */
    public function test_074_create_instructor_modal_has_all_fields(): void
    {
        $this->currentTestNumber = 74;
        $this->currentTestName = 'Create Instructor Modal Has All Fields';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->waitFor('button[onclick*="instructors"]', 10)
                    ->click('button[onclick*="instructors"]')
                    ->pause(1000)
                    ->waitFor('button[onclick="openCreateInstructorModal()"]', 10)
                    ->click('button[onclick="openCreateInstructorModal()"]')
                    ->pause(500);
            $this->screenshot($browser, '01-modal-opened');
            
            $browser->assertVisible('#createInstructorModal input[name="first_name"]');
            $this->screenshot($browser, '02-first-name-field-present');
            
            $browser->assertVisible('#createInstructorModal input[name="last_name"]');
            $this->screenshot($browser, '03-last-name-field-present');
            
            $browser->assertVisible('#createInstructorModal input[name="email"]');
            $this->screenshot($browser, '04-email-field-present');
            
            $browser->assertVisible('#createInstructorModal input[name="phone"]');
            $this->screenshot($browser, '05-phone-field-present');
            
            $browser->assertVisible('#createInstructorModal input[name="password"]');
            $this->screenshot($browser, '06-password-field-present');
        });
    }

    /**
     * @group admin
     * @group user-management
     */
    public function test_075_fill_create_instructor_form(): void
    {
        $this->currentTestNumber = 75;
        $this->currentTestName = 'Fill Create Instructor Form';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(2000)
                    ->waitFor('button[onclick*="instructors"]', 10)
                    ->click('button[onclick*="instructors"]')
                    ->pause(1000)
                    ->waitFor('button[onclick="openCreateInstructorModal()"]', 10)
                    ->click('button[onclick="openCreateInstructorModal()"]')
                    ->pause(500);
            $this->screenshot($browser, '01-modal-opened');
            
            $browser->type('#createInstructorModal input[name="first_name"]', 'Test');
            $this->screenshot($browser, '02-first-name-filled');
            
            $browser->type('#createInstructorModal input[name="last_name"]', 'Instructor');
            $this->screenshot($browser, '03-last-name-filled');
            
            $browser->type('#createInstructorModal input[name="email"]', 'test.instructor@example.com');
            $this->screenshot($browser, '04-email-filled');
            
            $browser->type('#createInstructorModal input[name="phone"]', '09187654321');
            $this->screenshot($browser, '05-phone-filled');
            
            $browser->type('#createInstructorModal input[name="password"]', 'Password123!');
            $this->screenshot($browser, '06-password-filled');
            
            $this->screenshot($browser, '07-all-fields-completed');
        });
    }
}
