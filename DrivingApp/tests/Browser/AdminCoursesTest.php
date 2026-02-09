<?php

/**
 * Comprehensive Admin Courses Tests
 * Tests 081-100: All course and package management features
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminCoursesTest extends DuskTestCase
{
    protected $schoolSlug = 'smart-driving';
    protected $adminEmail = 'schooladmin@gmail.com';
    protected $adminPassword = 'password123';
    protected $currentTestNumber = 0;
    protected $currentTestName = '';
    protected $role = 'Admin';

    protected function screenshot(Browser $browser, string $stepName): void
    {
        $folderNumber = str_pad($this->currentTestNumber, 3, '0', STR_PAD_LEFT);
        $folder = "Test {$folderNumber} - {$this->currentTestName}/{$this->role}";
        $browser->screenshot("{$folder}/{$stepName}");
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
     * @group courses
     */
    public function test_081_courses_page_display(): void
    {
        $this->currentTestNumber = 81;
        $this->currentTestName = 'Courses Page Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/admin/courses");
            $this->screenshot($browser, '02-navigating-to-courses');
            
            $browser->pause(2000);
            $this->screenshot($browser, '03-courses-page-loaded');
            
            $browser->assertSee('Courses');
            $this->screenshot($browser, '04-page-title-verified');
        });
    }

    /**
     * @group admin
     * @group courses
     */
    public function test_082_courses_list_shows_courses(): void
    {
        $this->currentTestNumber = 82;
        $this->currentTestName = 'Courses List Shows Courses';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000);
            $this->screenshot($browser, '01-courses-page-loaded');
            
            // Should have course cards or list
            $browser->assertVisible('.course-card, .course-item, table');
            $this->screenshot($browser, '02-courses-list-visible');
        });
    }

    /**
     * @group admin
     * @group courses
     */
    public function test_083_create_course_button_exists(): void
    {
        $this->currentTestNumber = 83;
        $this->currentTestName = 'Create Course Button Exists';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000);
            $this->screenshot($browser, '01-courses-page-loaded');
            
            $browser->waitFor('button[onclick*="create"], button[onclick*="Course"]', 10);
            $this->screenshot($browser, '02-create-button-found');
        });
    }

    /**
     * @group admin
     * @group courses
     */
    public function test_084_create_course_modal_opens(): void
    {
        $this->currentTestNumber = 84;
        $this->currentTestName = 'Create Course Modal Opens';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000);
            $this->screenshot($browser, '01-courses-page');
            
            $browser->waitFor('button[onclick="openCreateCourseModal()"]', 10);
            $this->screenshot($browser, '02-create-course-button-visible');
            
            $browser->click('button[onclick="openCreateCourseModal()"]');
            $this->screenshot($browser, '03-button-clicked');
            
            $browser->pause(500);
            $this->screenshot($browser, '04-modal-opening');
            
            $browser->assertVisible('#createCourseModal');
            $this->screenshot($browser, '05-modal-opened');
        });
    }

    /**
     * @group admin
     * @group courses
     */
    public function test_085_create_course_modal_has_all_fields(): void
    {
        $this->currentTestNumber = 85;
        $this->currentTestName = 'Create Course Modal Has All Fields';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000)
                    ->waitFor('button[onclick="openCreateCourseModal()"]', 10)
                    ->click('button[onclick="openCreateCourseModal()"]')
                    ->pause(500);
            $this->screenshot($browser, '01-modal-opened');
            
            $browser->assertVisible('#createCourseModal input[name="name"]');
            $this->screenshot($browser, '02-name-field-present');
            
            $browser->assertVisible('#createCourseModal textarea[name="description"]');
            $this->screenshot($browser, '03-description-field-present');
            
            $browser->assertVisible('#createCourseModal select[name="type"]');
            $this->screenshot($browser, '04-type-field-present');
            
            $browser->assertVisible('#createCourseModal input[name="duration"]');
            $this->screenshot($browser, '05-duration-field-present');
            
            $browser->assertVisible('#createCourseModal input[name="price"]');
            $this->screenshot($browser, '06-price-field-present');
        });
    }

    /**
     * @group admin
     * @group courses
     */
    public function test_086_fill_create_course_form(): void
    {
        $this->currentTestNumber = 86;
        $this->currentTestName = 'Fill Create Course Form';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000)
                    ->waitFor('button[onclick="openCreateCourseModal()"]', 10)
                    ->click('button[onclick="openCreateCourseModal()"]')
                    ->pause(500);
            $this->screenshot($browser, '01-modal-opened');
            
            $browser->type('#createCourseModal input[name="name"]', 'Test Course');
            $this->screenshot($browser, '02-name-filled');
            
            $browser->type('#createCourseModal textarea[name="description"]', 'This is a test course description');
            $this->screenshot($browser, '03-description-filled');
            
            $browser->select('#createCourseModal select[name="type"]', 'Practical');
            $this->screenshot($browser, '04-type-selected');
            
            $browser->type('#createCourseModal input[name="duration"]', '30');
            $this->screenshot($browser, '05-duration-filled');
            
            $browser->type('#createCourseModal input[name="price"]', '15000');
            $this->screenshot($browser, '06-price-filled');
            
            $this->screenshot($browser, '07-all-fields-completed');
        });
    }

    /**
     * @group admin
     * @group courses
     */
    public function test_093_view_course_packages(): void
    {
        $this->currentTestNumber = 93;
        $this->currentTestName = 'View Course Packages';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000);
            $this->screenshot($browser, '01-courses-page-loaded');
            
            // Look for view/manage packages button
            $browser->waitFor('button[onclick*="package"], a[href*="package"]', 10);
            $this->screenshot($browser, '02-packages-option-visible');
        });
    }

    /**
     * @group admin
     * @group courses
     */
    public function test_094_create_package_modal_opens(): void
    {
        $this->currentTestNumber = 94;
        $this->currentTestName = 'Create Package Modal Opens';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000);
            $this->screenshot($browser, '01-courses-page');
            
            // Click on first course's package button
            $browser->waitFor('button[onclick*="Package"]', 10);
            $this->screenshot($browser, '02-package-button-found');
            
            $browser->click('button[onclick*="Package"]');
            $this->screenshot($browser, '03-button-clicked');
            
            $browser->pause(500);
            $this->screenshot($browser, '04-modal-opening');
            
            $browser->assertVisible('#createPackageModal, #packageModal');
            $this->screenshot($browser, '05-package-modal-opened');
        });
    }

    /**
     * @group admin
     * @group courses
     */
    public function test_095_fill_create_package_form(): void
    {
        $this->currentTestNumber = 95;
        $this->currentTestName = 'Fill Create Package Form';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            
            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(2000)
                    ->waitFor('button[onclick*="Package"]', 10)
                    ->click('button[onclick*="Package"]')
                    ->pause(500);
            $this->screenshot($browser, '01-modal-opened');
            
            $selector = '#createPackageModal, #packageModal';
            
            $browser->type("{$selector} input[name='name']", 'Basic Package');
            $this->screenshot($browser, '02-package-name-filled');
            
            $browser->type("{$selector} input[name='price']", '12000');
            $this->screenshot($browser, '03-price-filled');
            
            $browser->type("{$selector} input[name='sessions']", '20');
            $this->screenshot($browser, '04-sessions-filled');
            
            $this->screenshot($browser, '05-all-fields-completed');
        });
    }
}
