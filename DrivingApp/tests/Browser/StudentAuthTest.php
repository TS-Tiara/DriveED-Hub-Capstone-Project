<?php

/**
 * Student Authentication Tests
 * Tests 011-015: Complete student authentication flows
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudentAuthTest extends DuskTestCase
{
    protected $schoolSlug = 'smart-driving';
    protected $studentEmail = 'maria.santos@gmail.com';
    protected $studentPassword = 'password123';
    protected $currentTestNumber = 0;
    protected $currentTestName = '';
    protected $role = 'Student';

    protected function screenshot(Browser $browser, string $stepName): void
    {
        $folderNumber = str_pad((string) $this->currentTestNumber, 3, '0', STR_PAD_LEFT);
        $scenario = "Test {$folderNumber} - {$this->currentTestName}";
        $this->captureRoleScreenshot($browser, $this->role, $scenario, $stepName);
    }

    /**
     * @group auth
     * @group student
     */
    public function test_011_student_login_success(): void
    {
        $this->currentTestNumber = 11;
        $this->currentTestName = 'Student Login Success';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page-loaded');
            
            $browser->assertSee('Log In')
                    ->waitFor('input[name="email"]');
            $this->screenshot($browser, '02-login-form-visible');
            
            $browser->type('input[name="email"]', $this->studentEmail);
            $this->screenshot($browser, '03-email-entered');
            
            $browser->type('input[name="password"]', $this->studentPassword);
            $this->screenshot($browser, '04-password-entered');
            
            $browser->click('button[type="submit"]');
            $this->screenshot($browser, '05-submit-clicked');
            
            $browser->pause(3000);
            $this->screenshot($browser, '06-processing-complete');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/student");
            $this->screenshot($browser, '07-student-dashboard-loaded');
        });
    }

    /**
     * @group auth
     * @group student
     */
    public function test_012_student_login_invalid_credentials(): void
    {
        $this->currentTestNumber = 12;
        $this->currentTestName = 'Student Login Invalid Credentials';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page-loaded');
            
            $browser->waitFor('input[name="email"]')
                    ->type('input[name="email"]', 'wrong@email.com');
            $this->screenshot($browser, '02-wrong-email-entered');
            
            $browser->type('input[name="password"]', 'wrongpassword');
            $this->screenshot($browser, '03-wrong-password-entered');
            
            $browser->click('button[type="submit"]');
            $this->screenshot($browser, '04-submit-clicked');
            
            $browser->pause(2000);
            $this->screenshot($browser, '05-error-displayed');
            
            $browser->assertPathIs("/{$this->schoolSlug}");
            $this->screenshot($browser, '06-still-on-login-page');
        });
    }

    /**
     * @group auth
     * @group student
     */
    public function test_013_student_logout(): void
    {
        $this->currentTestNumber = 13;
        $this->currentTestName = 'Student Logout';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}")
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->studentEmail)
                    ->type('input[name="password"]', $this->studentPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/student");
            $this->screenshot($browser, '02-on-student-dashboard');
            
            $browser->waitFor('form[action*="logout"]', 10);
            $this->screenshot($browser, '03-logout-button-visible');
            
            $browser->click('form[action*="logout"] button');
            $this->screenshot($browser, '04-logout-clicked');
            
            $browser->pause(2000);
            $this->screenshot($browser, '05-redirecting');
            
            $browser->assertPathIs("/{$this->schoolSlug}");
            $this->screenshot($browser, '06-back-to-login');
        });
    }

    /**
     * @group auth
     * @group student
     */
    public function test_014_student_session_persistence(): void
    {
        $this->currentTestNumber = 14;
        $this->currentTestName = 'Student Session Persistence';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}")
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->studentEmail)
                    ->type('input[name="password"]', $this->studentPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/student/courses");
            $this->screenshot($browser, '02-navigated-to-courses');
            
            $browser->pause(2000)
                    ->assertPathBeginsWith("/{$this->schoolSlug}/student");
            $this->screenshot($browser, '03-still-authenticated');
            
            $browser->visit("/{$this->schoolSlug}/student");
            $this->screenshot($browser, '04-back-to-dashboard');
            
            $browser->pause(2000);
            $this->screenshot($browser, '05-session-persisted');
        });
    }

    /**
     * @group auth
     * @group student
     */
    public function test_015_student_redirect_after_login(): void
    {
        $this->currentTestNumber = 15;
        $this->currentTestName = 'Student Redirect After Login';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page');
            
            $browser->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->studentEmail)
                    ->type('input[name="password"]', $this->studentPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '02-login-submitted');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/student");
            $this->screenshot($browser, '03-redirected-to-student');
        });
    }
}
