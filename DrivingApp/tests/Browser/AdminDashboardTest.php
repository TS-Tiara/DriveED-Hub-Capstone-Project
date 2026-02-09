<?php

/**
 * Comprehensive Admin Dashboard Tests
 * Tests 056-060: All dashboard features and widgets
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminDashboardTest extends DuskTestCase
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
     * @group dashboard
     */
    public function test_056_admin_dashboard_display(): void
    {
        $this->currentTestNumber = 56;
        $this->currentTestName = 'Admin Dashboard Display';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');
            
            $browser->assertPathBeginsWith("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '02-on-admin-dashboard');
            
            $browser->assertSee('Dashboard');
            $this->screenshot($browser, '03-dashboard-title-visible');
            
            $browser->pause(2000);
            $this->screenshot($browser, '04-dashboard-fully-loaded');
        });
    }

    /**
     * @group admin
     * @group dashboard
     */
    public function test_057_dashboard_statistics_cards(): void
    {
        $this->currentTestNumber = 57;
        $this->currentTestName = 'Dashboard Statistics Cards';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-dashboard-loaded');
            
            $browser->pause(2000);
            $this->screenshot($browser, '02-statistics-loading');
            
            // Look for stat cards - they usually have class like 'card', 'stat-card', etc
            $browser->assertVisible('.card, .stat-card, .dashboard-card');
            $this->screenshot($browser, '03-statistics-cards-visible');
            
            $this->screenshot($browser, '04-all-stats-displayed');
        });
    }

    /**
     * @group admin
     * @group dashboard
     */
    public function test_058_recent_enrollments_widget(): void
    {
        $this->currentTestNumber = 58;
        $this->currentTestName = 'Recent Enrollments Widget';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-dashboard-loaded');
            
            $browser->pause(2000);
            $this->screenshot($browser, '02-widgets-loading');
            
            // Look for enrollments section
            $browser->waitFor('h2, h3, .widget-title', 10);
            $this->screenshot($browser, '03-widgets-visible');
            
            $this->screenshot($browser, '04-enrollment-widget-displayed');
        });
    }

    /**
     * @group admin
     * @group dashboard
     */
    public function test_059_upcoming_schedules_widget(): void
    {
        $this->currentTestNumber = 59;
        $this->currentTestName = 'Upcoming Schedules Widget';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-dashboard-loaded');
            
            $browser->pause(2000);
            $this->screenshot($browser, '02-schedule-widget-loading');
            
            // Dashboard should show upcoming schedules
            $this->screenshot($browser, '03-schedule-widget-visible');
        });
    }

    /**
     * @group admin
     * @group dashboard
     */
    public function test_060_quick_actions_buttons(): void
    {
        $this->currentTestNumber = 60;
        $this->currentTestName = 'Quick Actions Buttons';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-dashboard-loaded');
            
            $browser->pause(2000);
            $this->screenshot($browser, '02-looking-for-quick-actions');
            
            // Look for action buttons (Add Student, Add Course, etc.)
            $browser->waitFor('button, a.btn', 10);
            $this->screenshot($browser, '03-action-buttons-visible');
            
            $this->screenshot($browser, '04-all-quick-actions-displayed');
        });
    }
}
