<?php

/**
 * Admin Authentication Tests
 * Tests 001-005: Complete admin authentication flows
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminAuthTest extends DuskTestCase
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

    /**
     * @group auth
     * @group admin
     */
    public function test_001_admin_login_success(): void
    {
        $this->currentTestNumber = 1;
        $this->currentTestName = 'Admin Login Success';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page-loaded');
            
            $browser->assertSee('Log In')
                    ->waitFor('input[name="email"]');
            $this->screenshot($browser, '02-login-form-visible');
            
            $browser->type('input[name="email"]', $this->adminEmail);
            $this->screenshot($browser, '03-email-entered');
            
            $browser->type('input[name="password"]', $this->adminPassword);
            $this->screenshot($browser, '04-password-entered');
            
            $browser->click('button[type="submit"]');
            $this->screenshot($browser, '05-submit-clicked');
            
            $browser->pause(3000);
            $this->screenshot($browser, '06-processing-complete');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '07-admin-dashboard-loaded');
            
            $browser->assertSee('Dashboard');
            $this->screenshot($browser, '08-dashboard-content-verified');
        });
    }

    /**
     * @group auth
     * @group admin
     */
    public function test_002_admin_login_invalid_credentials(): void
    {
        $this->currentTestNumber = 2;
        $this->currentTestName = 'Admin Login Invalid Credentials';

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
            
            // Should stay on login page or show error
            $browser->assertPathIs("/{$this->schoolSlug}");
            $this->screenshot($browser, '06-still-on-login-page');
        });
    }

    /**
     * @group auth
     * @group admin
     */
    public function test_003_admin_logout(): void
    {
        $this->currentTestNumber = 3;
        $this->currentTestName = 'Admin Logout';

        $this->browse(function (Browser $browser) {
            // Login first
            $browser->visit("/{$this->schoolSlug}")
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->adminEmail)
                    ->type('input[name="password"]', $this->adminPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '01-logged-in');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '02-on-admin-dashboard');
            
            // Click logout
            $browser->waitFor('form[action*="logout"]', 10);
            $this->screenshot($browser, '03-logout-button-visible');
            
            $browser->click('form[action*="logout"] button');
            $this->screenshot($browser, '04-logout-clicked');
            
            $browser->pause(2000);
            $this->screenshot($browser, '05-redirecting');
            
            // Should redirect to login
            $browser->assertPathIs("/{$this->schoolSlug}");
            $this->screenshot($browser, '06-back-to-login');
        });
    }

    /**
     * @group auth
     * @group admin
     */
    public function test_004_admin_session_persistence(): void
    {
        $this->currentTestNumber = 4;
        $this->currentTestName = 'Admin Session Persistence';

        $this->browse(function (Browser $browser) {
            // Login
            $browser->visit("/{$this->schoolSlug}")
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->adminEmail)
                    ->type('input[name="password"]', $this->adminPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '01-logged-in');
            
            // Navigate away
            $browser->visit("/{$this->schoolSlug}/admin/user-management");
            $this->screenshot($browser, '02-navigated-to-user-management');
            
            $browser->pause(2000)
                    ->assertPathBeginsWith("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '03-still-authenticated');
            
            // Go back to dashboard
            $browser->visit("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '04-back-to-dashboard');
            
            $browser->pause(2000)
                    ->assertSee('Dashboard');
            $this->screenshot($browser, '05-session-persisted');
        });
    }

    /**
     * @group auth
     * @group admin
     */
    public function test_005_admin_redirect_after_login(): void
    {
        $this->currentTestNumber = 5;
        $this->currentTestName = 'Admin Redirect After Login';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page');
            
            $browser->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->adminEmail)
                    ->type('input[name="password"]', $this->adminPassword)
                    ->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '02-login-submitted');
            
            // Should redirect to admin dashboard
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '03-redirected-to-admin');
            
            // Verify it's the dashboard
            $browser->assertSee('Dashboard');
            $this->screenshot($browser, '04-dashboard-confirmed');
        });
    }
}
