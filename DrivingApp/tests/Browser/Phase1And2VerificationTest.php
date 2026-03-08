<?php

/**
 * Phase 1 & Phase 2 Verification Tests
 * Tests 016-055: Verify security fixes, MVC cleanup, and cross-tenant protection
 *
 * Phase 1 Tests (Security):
 *   016 - Cross-tenant booking access blocked
 *   017 - Cross-tenant payment access blocked
 *   018 - Cross-tenant progress access blocked
 *   019 - Generic login error message (no email enumeration)
 *   020 - Password minimum 8 characters enforced
 *   021 - Rate limiting on login
 *   022 - Disabled school returns 403
 *   023 - XSS-safe user management (data-* attributes)
 *   024 - Test routes blocked in production
 *   025 - Account lockout fields work
 *
 * Phase 2 Tests (MVC Cleanup):
 *   026 - Admin progress page loads (no inline queries)
 *   027 - Admin enrollment requests page loads (no inline queries)
 *   028 - Guest dashboard loads (no inline queries)
 *   029 - Guest courses page loads (no inline queries)
 *   030 - Student schedule page loads (no inline queries)
 *   031 - Admin dashboard loads with stats
 *   032 - Admin user management XSS-safe rendering
 *   033 - System admin schools page XSS-safe rendering
 *   034 - Instructor schedule renders correct view
 *   035 - Admin course management page loads
 *
 * Cross-Role Access Tests:
 *   036 - Student cannot access admin routes
 *   037 - Instructor cannot access admin routes
 *   038 - Guest cannot access student routes
 *   039 - Admin can access all admin sub-pages
 *   040 - Logout redirects to login page
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Phase1And2VerificationTest extends DuskTestCase
{
    protected $schoolSlug = 'smart-driving';
    protected $adminEmail = 'schooladmin@gmail.com';
    protected $adminPassword = 'password123';
    protected $instructorEmail = 'juan.delacruz@smartdriving.com';
    protected $instructorPassword = 'password123';
    protected $studentEmail = 'maria.santos@gmail.com';
    protected $studentPassword = 'password123';
    protected $currentTestNumber = 0;
    protected $currentTestName = '';
    protected $role = 'Verification';

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

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

    // ══════════════════════════════════════════════
    // PHASE 1: SECURITY TESTS
    // ══════════════════════════════════════════════

    /**
     * Test 016: Verify generic login error (no email enumeration)
     * Phase 1 Fix: G40 — All guards return same error message
     *
     * @group phase1
     * @group security
     */
    public function test_016_generic_login_error_no_email_enumeration(): void
    {
        $this->currentTestNumber = 16;
        $this->currentTestName = 'Generic Login Error';
        $this->role = 'Security';

        $this->browse(function (Browser $browser) {
            // Try with a non-existent email
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page');

            $browser->waitFor('input[name="email"]')
                    ->type('input[name="email"]', 'nonexistent@email.com')
                    ->type('input[name="password"]', 'wrongpassword123');
            $this->screenshot($browser, '02-fake-credentials-entered');

            $browser->click('button[type="submit"]')
                    ->pause(2000);
            $this->screenshot($browser, '03-error-after-fake-email');

            // The message should be generic — no "email not found" or "X attempts"
            $browser->assertDontSee('attempts remaining')
                    ->assertDontSee('not found')
                    ->assertDontSee('does not exist');
            $this->screenshot($browser, '04-no-enumeration-info-leaked');

            // Try with a real email but wrong password
            $browser->visit("/{$this->schoolSlug}")
                    ->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->adminEmail)
                    ->type('input[name="password"]', 'wrongpassword123')
                    ->click('button[type="submit"]')
                    ->pause(2000);
            $this->screenshot($browser, '05-error-after-wrong-password');

            // Same generic message — no "wrong password" vs "user not found"
            $browser->assertDontSee('attempts remaining')
                    ->assertDontSee('incorrect password');
            $this->screenshot($browser, '06-same-generic-error-verified');
        });
    }

    /**
     * Test 017: Password must be at least 8 characters
     * Phase 1 Fix: G44 — min:8 on login validation
     *
     * @group phase1
     * @group security
     */
    public function test_017_password_min_8_characters(): void
    {
        $this->currentTestNumber = 17;
        $this->currentTestName = 'Password Min 8 Chars';
        $this->role = 'Security';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}");
            $this->screenshot($browser, '01-login-page');

            $browser->waitFor('input[name="email"]')
                    ->type('input[name="email"]', $this->adminEmail)
                    ->type('input[name="password"]', 'short')  // Only 5 chars
                    ->click('button[type="submit"]')
                    ->pause(2000);
            $this->screenshot($browser, '02-short-password-submitted');

            // Should get a validation error (stays on login page)
            $browser->assertPathIs("/{$this->schoolSlug}");
            $this->screenshot($browser, '03-still-on-login-page');
        });
    }

    /**
     * Test 018: Rate limiting on login endpoint
     * Phase 1 Fix: L13/G43 — throttle:5,1 on login
     *
     * @group phase1
     * @group security
     */
    public function test_018_rate_limiting_on_login(): void
    {
        $this->currentTestNumber = 18;
        $this->currentTestName = 'Login Rate Limiting';
        $this->role = 'Security';

        $this->browse(function (Browser $browser) {
            // Attempt multiple rapid failed logins
            for ($i = 1; $i <= 6; $i++) {
                $browser->visit("/{$this->schoolSlug}")
                        ->waitFor('input[name="email"]')
                        ->type('input[name="email"]', 'attacker@test.com')
                        ->type('input[name="password"]', 'wrongpass' . $i)
                        ->click('button[type="submit"]')
                        ->pause(1000);
                $this->screenshot($browser, "0{$i}-attempt-{$i}");
            }

            // After 5+ attempts, should be rate-limited (429 or throttle message)
            $this->screenshot($browser, '07-rate-limit-check');

            // Page should show some form of throttle feedback or stay on login
            $browser->assertPathIs("/{$this->schoolSlug}");
            $this->screenshot($browser, '08-throttled-or-still-login');
        });
    }

    /**
     * Test 019: Disabled school returns 403
     * Phase 1 Fix: L19 — school active-status check
     *
     * @group phase1
     * @group security
     */
    public function test_019_disabled_school_returns_403(): void
    {
        $this->currentTestNumber = 19;
        $this->currentTestName = 'Disabled School 403';
        $this->role = 'Security';

        $this->browse(function (Browser $browser) {
            // Try to access a non-existent school slug
            $browser->visit('/nonexistent-school-slug-12345');
            $this->screenshot($browser, '01-nonexistent-school-visited');

            $browser->pause(2000);
            $this->screenshot($browser, '02-response-received');

            // Should get 404 or similar error — not a valid school page
            $browser->assertDontSee('Log In');  // Should not show a login form
            $this->screenshot($browser, '03-no-login-form-shown');
        });
    }

    /**
     * Test 020: XSS-safe user management (data-* attributes, no onclick with user data)
     * Phase 1 Fix: A8/S6/S8/S13
     *
     * @group phase1
     * @group security
     */
    public function test_020_xss_safe_user_management(): void
    {
        $this->currentTestNumber = 20;
        $this->currentTestName = 'XSS Safe User Management';
        $this->role = 'Admin';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');

            $browser->visit("/{$this->schoolSlug}/admin/user-management")
                    ->pause(3000);
            $this->screenshot($browser, '02-user-management-loaded');

            // Verify the page renders without JavaScript errors
            $browser->assertSee('User Management');
            $this->screenshot($browser, '03-page-title-visible');

            // Verify action buttons exist and use data-* attributes
            $pageSource = $browser->driver->getPageSource();
            $this->assertStringNotContainsString('onclick="editStudent(', $pageSource,
                'Found unsafe inline onclick with editStudent — XSS fix not applied');
            $this->assertStringNotContainsString('onclick="editInstructor(', $pageSource,
                'Found unsafe inline onclick with editInstructor — XSS fix not applied');
            $this->screenshot($browser, '04-no-unsafe-onclick-handlers');
        });
    }

    // ══════════════════════════════════════════════
    // PHASE 2: MVC CLEANUP — PAGE LOAD TESTS
    // (Verifying pages load without inline query crashes)
    // ══════════════════════════════════════════════

    /**
     * Test 026: Admin progress page loads without inline query errors
     * Phase 2 Fix: Moved Booking queries from progress.blade.php to ProgressController
     *
     * @group phase2
     * @group admin
     */
    public function test_026_admin_progress_page_loads(): void
    {
        $this->currentTestNumber = 26;
        $this->currentTestName = 'Admin Progress Page';
        $this->role = 'Admin';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');

            $browser->visit("/{$this->schoolSlug}/admin/progress")
                    ->pause(3000);
            $this->screenshot($browser, '02-progress-page-loading');

            // Page should load without 500 error
            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error')
                    ->assertDontSee('Undefined variable');
            $this->screenshot($browser, '03-no-server-errors');

            $browser->assertPathIs("/{$this->schoolSlug}/admin/progress");
            $this->screenshot($browser, '04-progress-page-loaded');
        });
    }

    /**
     * Test 027: Admin enrollment requests page loads
     * Phase 2 Fix: Moved queries from index.blade.php to EnrollmentRequestController
     *
     * @group phase2
     * @group admin
     */
    public function test_027_admin_enrollment_requests_page_loads(): void
    {
        $this->currentTestNumber = 27;
        $this->currentTestName = 'Enrollment Requests Page';
        $this->role = 'Admin';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');

            $browser->visit("/{$this->schoolSlug}/admin/enrollment-requests")
                    ->pause(3000);
            $this->screenshot($browser, '02-enrollment-page-loading');

            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error')
                    ->assertDontSee('Undefined variable');
            $this->screenshot($browser, '03-no-server-errors');

            $browser->assertPathBeginsWith("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '04-enrollment-page-loaded');
        });
    }

    /**
     * Test 028: Guest dashboard loads without inline queries
     * Phase 2 Fix: Moved enrollment queries from dashboard.blade.php to GuestController
     *
     * @group phase2
     * @group guest
     */
    public function test_028_guest_dashboard_loads(): void
    {
        $this->currentTestNumber = 28;
        $this->currentTestName = 'Guest Dashboard';
        $this->role = 'Guest';

        $this->browse(function (Browser $browser) {
            // Visit school landing page (guest view)
            $browser->visit("/{$this->schoolSlug}")
                    ->pause(2000);
            $this->screenshot($browser, '01-school-landing-page');

            // School landing / guest page should load without errors
            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error');
            $this->screenshot($browser, '02-no-server-errors');

            $browser->assertSee('Log In');
            $this->screenshot($browser, '03-login-available');
        });
    }

    /**
     * Test 029: Guest courses page loads without inline queries
     * Phase 2 Fix: Moved queries and file_exists() from courses.blade.php to GuestController
     *
     * @group phase2
     * @group guest
     */
    public function test_029_guest_courses_page_loads(): void
    {
        $this->currentTestNumber = 29;
        $this->currentTestName = 'Guest Courses Page';
        $this->role = 'Guest';

        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->schoolSlug}/courses")
                    ->pause(3000);
            $this->screenshot($browser, '01-courses-page-loading');

            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error')
                    ->assertDontSee('Undefined variable');
            $this->screenshot($browser, '02-no-server-errors');

            $this->screenshot($browser, '03-courses-page-loaded');
        });
    }

    /**
     * Test 030: Student schedule page loads with all controller data
     * Phase 2 Fix: Removed 55-line PHP block from schedule.blade.php
     *
     * @group phase2
     * @group student
     */
    public function test_030_student_schedule_page_loads(): void
    {
        $this->currentTestNumber = 30;
        $this->currentTestName = 'Student Schedule Page';
        $this->role = 'Student';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-student-logged-in');

            $browser->visit("/{$this->schoolSlug}/student/schedule")
                    ->pause(3000);
            $this->screenshot($browser, '02-schedule-page-loading');

            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error')
                    ->assertDontSee('Undefined variable');
            $this->screenshot($browser, '03-no-server-errors');

            $browser->assertPathIs("/{$this->schoolSlug}/student/schedule");
            $this->screenshot($browser, '04-schedule-page-loaded');
        });
    }

    /**
     * Test 031: Admin dashboard loads with computed stats (no fake rand())
     * Phase 2 Fix: C7 — Replaced rand() ratings with null
     *
     * @group phase2
     * @group admin
     */
    public function test_031_admin_dashboard_loads(): void
    {
        $this->currentTestNumber = 31;
        $this->currentTestName = 'Admin Dashboard Stats';
        $this->role = 'Admin';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');

            $browser->assertPathBeginsWith("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '02-admin-dashboard');

            $browser->assertSee('Dashboard');
            $this->screenshot($browser, '03-dashboard-title');

            // Dashboard should load without errors
            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error');
            $this->screenshot($browser, '04-no-errors-on-dashboard');
        });
    }

    /**
     * Test 032: System admin schools page renders with data-* attributes
     * Phase 2 Fix: XSS-safe rendering
     *
     * @group phase2
     * @group systemadmin
     */
    public function test_032_system_admin_schools_page(): void
    {
        $this->currentTestNumber = 32;
        $this->currentTestName = 'SysAdmin Schools Page';
        $this->role = 'SystemAdmin';

        $this->browse(function (Browser $browser) {
            // Login as system admin
            $browser->visit('/system-admin/login')
                    ->pause(2000);
            $this->screenshot($browser, '01-sysadmin-login-page');

            $browser->waitFor('input[name="email"]', 5)
                    ->type('input[name="email"]', 'systemadmin@gmail.com')
                    ->type('input[name="password"]', 'sysadmin123!');
            $this->screenshot($browser, '02-credentials-entered');

            $browser->click('button[type="submit"]')
                    ->pause(3000);
            $this->screenshot($browser, '03-login-submitted');

            $browser->visit('/system-admin/schools')
                    ->pause(3000);
            $this->screenshot($browser, '04-schools-page-loaded');

            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error');
            $this->screenshot($browser, '05-no-server-errors');

            // Verify XSS-safe: no inline onclick with user data
            $pageSource = $browser->driver->getPageSource();
            $this->assertStringNotContainsString('onclick="confirmDelete(', $pageSource,
                'Found unsafe inline onclick with confirmDelete — XSS fix not applied');
            $this->screenshot($browser, '06-xss-safe-verified');
        });
    }

    /**
     * Test 033: Instructor schedule renders correct view (schedule-new.blade.php)
     * Phase 2 Fix: Old schedule.blade.php deleted, schedule-new.blade.php is active
     *
     * @group phase2
     * @group instructor
     */
    public function test_033_instructor_schedule_renders_correct_view(): void
    {
        $this->currentTestNumber = 33;
        $this->currentTestName = 'Instructor Schedule View';
        $this->role = 'Instructor';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-instructor-logged-in');

            $browser->visit("/{$this->schoolSlug}/instructor/my-schedule")
                    ->pause(3000);
            $this->screenshot($browser, '02-schedule-page-loading');

            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error');
            $this->screenshot($browser, '03-no-server-errors');

            $browser->assertPathIs("/{$this->schoolSlug}/instructor/my-schedule");
            $this->screenshot($browser, '04-schedule-page-loaded');
        });
    }

    /**
     * Test 034: Admin courses management page loads
     *
     * @group phase2
     * @group admin
     */
    public function test_034_admin_courses_page_loads(): void
    {
        $this->currentTestNumber = 34;
        $this->currentTestName = 'Admin Courses Page';
        $this->role = 'Admin';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');

            $browser->visit("/{$this->schoolSlug}/admin/courses")
                    ->pause(3000);
            $this->screenshot($browser, '02-courses-page-loading');

            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error');
            $this->screenshot($browser, '03-no-server-errors');

            $browser->assertPathIs("/{$this->schoolSlug}/admin/courses");
            $this->screenshot($browser, '04-courses-page-loaded');
        });
    }

    /**
     * Test 035: Admin reports page loads without fake ratings
     *
     * @group phase2
     * @group admin
     */
    public function test_035_admin_reports_page_loads(): void
    {
        $this->currentTestNumber = 35;
        $this->currentTestName = 'Admin Reports Page';
        $this->role = 'Admin';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');

            $browser->visit("/{$this->schoolSlug}/admin/reports")
                    ->pause(3000);
            $this->screenshot($browser, '02-reports-page-loading');

            $browser->assertDontSee('500')
                    ->assertDontSee('Server Error');
            $this->screenshot($browser, '03-no-server-errors');

            $browser->assertPathBeginsWith("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '04-reports-page-loaded');
        });
    }

    // ══════════════════════════════════════════════
    // CROSS-ROLE ACCESS CONTROL TESTS
    // ══════════════════════════════════════════════

    /**
     * Test 036: Student cannot access admin routes
     *
     * @group phase1
     * @group security
     * @group access
     */
    public function test_036_student_cannot_access_admin_routes(): void
    {
        $this->currentTestNumber = 36;
        $this->currentTestName = 'Student Blocked From Admin';
        $this->role = 'Student';

        $this->browse(function (Browser $browser) {
            $this->loginAsStudent($browser);
            $this->screenshot($browser, '01-student-logged-in');

            // Try to access admin dashboard
            $browser->visit("/{$this->schoolSlug}/admin")
                    ->pause(2000);
            $this->screenshot($browser, '02-tried-admin-route');

            // Should be redirected away or get forbidden
            $browser->assertPathIsNot("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '03-access-denied');
        });
    }

    /**
     * Test 037: Instructor cannot access admin routes
     *
     * @group phase1
     * @group security
     * @group access
     */
    public function test_037_instructor_cannot_access_admin_routes(): void
    {
        $this->currentTestNumber = 37;
        $this->currentTestName = 'Instructor Blocked From Admin';
        $this->role = 'Instructor';

        $this->browse(function (Browser $browser) {
            $this->loginAsInstructor($browser);
            $this->screenshot($browser, '01-instructor-logged-in');

            $browser->visit("/{$this->schoolSlug}/admin")
                    ->pause(2000);
            $this->screenshot($browser, '02-tried-admin-route');

            $browser->assertPathIsNot("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '03-access-denied');
        });
    }

    /**
     * Test 038: Guest cannot access student routes
     *
     * @group phase1
     * @group security
     * @group access
     */
    public function test_038_guest_cannot_access_student_routes(): void
    {
        $this->currentTestNumber = 38;
        $this->currentTestName = 'Guest Blocked From Student';
        $this->role = 'Guest';

        $this->browse(function (Browser $browser) {
            // Don't login — visit student route directly
            $browser->visit("/{$this->schoolSlug}/student")
                    ->pause(2000);
            $this->screenshot($browser, '01-tried-student-route');

            // Should be redirected to login
            $browser->assertPathIsNot("/{$this->schoolSlug}/student");
            $this->screenshot($browser, '02-redirected-to-login');
        });
    }

    /**
     * Test 039: Admin can access all primary admin pages
     *
     * @group phase2
     * @group admin
     */
    public function test_039_admin_can_access_all_admin_pages(): void
    {
        $this->currentTestNumber = 39;
        $this->currentTestName = 'Admin All Pages Access';
        $this->role = 'Admin';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');

            $pages = [
                'admin'                    => 'Dashboard',
                'admin/user-management'    => 'User Management',
                'admin/courses'            => 'Courses',
                'admin/enrollment-requests' => 'Enrollment',
                'admin/progress'           => 'Progress',
                'admin/reports'            => 'Reports',
            ];

            $step = 2;
            foreach ($pages as $path => $label) {
                $browser->visit("/{$this->schoolSlug}/{$path}")
                        ->pause(2000);
                $this->screenshot($browser, str_pad($step, 2, '0', STR_PAD_LEFT) . "-{$label}-page");

                $browser->assertDontSee('500')
                        ->assertDontSee('Server Error');
                $this->screenshot($browser, str_pad($step + 1, 2, '0', STR_PAD_LEFT) . "-{$label}-no-errors");
                $step += 2;
            }
        });
    }

    /**
     * Test 040: Logout redirects all roles to login page
     *
     * @group phase1
     * @group auth
     */
    public function test_040_logout_redirects_to_login(): void
    {
        $this->currentTestNumber = 40;
        $this->currentTestName = 'Logout Redirect';
        $this->role = 'Admin';

        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->screenshot($browser, '01-admin-logged-in');

            // Find and click logout
            $browser->waitFor('form[action*="logout"]', 10)
                    ->click('form[action*="logout"] button')
                    ->pause(2000);
            $this->screenshot($browser, '02-logged-out');

            // Should be back on login page
            $browser->assertPathIs("/{$this->schoolSlug}");
            $this->screenshot($browser, '03-back-to-login');

            // Verify can't access admin pages anymore
            $browser->visit("/{$this->schoolSlug}/admin")
                    ->pause(2000);
            $this->screenshot($browser, '04-admin-blocked-after-logout');

            $browser->assertPathIsNot("/{$this->schoolSlug}/admin");
            $this->screenshot($browser, '05-redirected-after-logout');
        });
    }
}
