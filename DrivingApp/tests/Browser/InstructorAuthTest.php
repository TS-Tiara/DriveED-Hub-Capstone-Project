<?php

/**
 * Instructor Authentication Tests
 * Tests 006-010: Complete instructor authentication flows
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InstructorAuthTest extends DuskTestCase
{
    protected $schoolSlug = 'smart-driving';
    protected $instructorEmail = 'juan.delacruz@smartdriving.com';
    protected $instructorPassword = 'password123';
    protected $currentTestNumber = 0;
    protected $currentTestName = '';
    protected $role = 'Instructor';

    protected function screenshot(Browser $browser, string $stepName): void
    {
        $folderNumber = str_pad($this->currentTestNumber, 3, '0', STR_PAD_LEFT);
        $folder = "Test {$folderNumber} - {$this->currentTestName}/{$this->role}";
        $browser->screenshot("{$folder}/{$stepName}");
    }

    /**
     * @group auth
     * @group instructor
     */
    public function test_006_instructor_login_success(): void
    {
        $this->currentTestNumber = 6;
        $this->currentTestName = 'Instructor Login Success';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page-loaded');
            
            $browser->assertSee('Log In')
                    ->waitFor('input[name="email"]');
            $this->screenshot($browser, '02-login-form-visible');
            
            $browser->type('input[name="email"]', $this->instructorEmail);
            $this->screenshot($browser, '03-email-entered');
            
            $browser->type('input[name="password"]', $this->instructorPassword);
            $this->screenshot($browser, '04-password-entered');
            
            $browser->click('button[type="submit"]');
            $this->screenshot($browser, '05-submit-clicked');
            
            $browser->pause(3000);
            $this->screenshot($browser, '06-processing-complete');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
            $this->screenshot($browser, '07-instructor-dashboard-loaded');
        });
    }

    /**
     * @group auth
     * @group instructor
     */
    public function test_007_instructor_login_invalid_credentials(): void
    {
        $this->currentTestNumber = 7;
        $this->currentTestName = 'Instructor Login Invalid Credentials';

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
     * @group instructor
     */
    public function test_008_instructor_logout(): void
    {
        $this->currentTestNumber = 8;
        $this->currentTestName = 'Instructor Logout';

        $this->browse(function (Browser $browser) {
            // Login first
            $browser->visit("/{$this->schoolSlug}")
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->instructorEmail)
                    ->type('input[name="password"]', $this->instructorPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
            $this->screenshot($browser, '02-on-instructor-dashboard');
            
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
     * @group instructor
     */
    public function test_009_instructor_session_persistence(): void
    {
        $this->currentTestNumber = 9;
        $this->currentTestName = 'Instructor Session Persistence';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}")
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->instructorEmail)
                    ->type('input[name="password"]', $this->instructorPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->visit("/{$this->schoolSlug}/instructor/my-schedule");
            $this->screenshot($browser, '02-navigated-to-schedule');
            
            $browser->pause(2000)
                    ->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
            $this->screenshot($browser, '03-still-authenticated');
            
            $browser->visit("/{$this->schoolSlug}/instructor");
            $this->screenshot($browser, '04-back-to-dashboard');
            
            $browser->pause(2000);
            $this->screenshot($browser, '05-session-persisted');
        });
    }

    /**
     * @group auth
     * @group instructor
     */
    public function test_010_instructor_redirect_after_login(): void
    {
        $this->currentTestNumber = 10;
        $this->currentTestName = 'Instructor Redirect After Login';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page');
            
            $browser->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->instructorEmail)
                    ->type('input[name="password"]', $this->instructorPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '02-login-submitted');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/instructor");
            $this->screenshot($browser, '03-redirected-to-instructor');
        });
    }
}
