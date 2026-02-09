<?php

/**
 * Browser UI Test Suite using Laravel Dusk
 * 
 * Tests interactive UI elements for the Driving School Management System:
 * - Login page display and form elements
 * - Registration page display
 * - Forgot password page display
 * - Navigation and basic page loads
 * 
 * Note: Tests that require actual database authentication (login functionality)
 * require the Laravel server to be running with the same database as the tests.
 * For development testing, use `php artisan serve --env=dusk.local` in a separate terminal.
 * 
 * @version 2.1
 */

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\School;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\SchoolSetting;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class UIComponentsTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $school;
    protected $admin;
    protected $student;
    protected $instructor;

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
    }

    // ===========================================
    // LOGIN PAGE UI TESTS
    // ===========================================

    /** @test */
    public function login_page_displays_correctly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-login-page-display')
                    ->assertSee($this->school->name)
                    ->assertVisible('input[name="email"]')
                    ->assertVisible('input[name="password"]')
                    ->assertPresent('button[type="submit"]');
        });
    }

    /** @test */
    public function login_page_has_email_and_password_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-login-fields')
                    ->assertInputPresent('email')
                    ->assertInputPresent('password');
        });
    }

    /** @test */
    public function login_page_shows_register_link()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-login-register-link')
                    ->assertSee("Don't have an account")
                    ->assertSee('Register');
        });
    }

    /** @test */
    public function login_page_shows_forgot_password_link()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-login-forgot-password')
                    ->assertSee('Forgot Password');
        });
    }

    /** @test */
    public function login_page_has_remember_me_checkbox()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-login-remember-me')
                    ->assertInputPresent('remember')
                    ->assertSee('Remember me');
        });
    }

    /** @test */
    public function login_page_displays_school_branding()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-login-branding')
                    ->assertSee($this->school->name);
        });
    }

    /** @test */
    public function login_button_is_visible_and_clickable()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-login-button')
                    ->assertVisible('button[type="submit"]')
                    ->assertSeeIn('button[type="submit"]', 'Log In');
        });
    }

    // ===========================================
    // REGISTRATION PAGE UI TESTS
    // ===========================================

    /** @test */
    public function registration_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}/register")
                    ->screenshot('ui-registration-page')
                    ->assertSee('Register')
                    ->assertInputPresent('name')
                    ->assertInputPresent('email')
                    ->assertInputPresent('password');
        });
    }

    /** @test */
    public function registration_page_has_all_required_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}/register")
                    ->screenshot('ui-registration-fields')
                    ->assertInputPresent('name')
                    ->assertInputPresent('email')
                    ->assertInputPresent('password')
                    ->assertInputPresent('password_confirmation')
                    ->assertInputPresent('contact');
        });
    }

    /** @test */
    public function registration_page_has_login_link()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}/register")
                    ->screenshot('ui-registration-login-link')
                    ->assertSee('Back to Login');
        });
    }

    // ===========================================
    // PASSWORD RESET PAGE UI TESTS
    // ===========================================

    /** @test */
    public function forgot_password_page_loads()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}/forgot-password")
                    ->screenshot('ui-forgot-password-page')
                    ->assertInputPresent('email')
                    ->assertSee('Reset');
        });
    }

    /** @test */
    public function forgot_password_page_has_back_to_login_link()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}/forgot-password")
                    ->screenshot('ui-forgot-password-back-link')
                    ->assertSee('Back to Login');
        });
    }

    // ===========================================
    // NAVIGATION TESTS (using register link)
    // ===========================================

    /** @test */
    public function can_navigate_from_login_to_register()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-nav-01-login-page')
                    ->clickLink('Register')
                    ->screenshot('ui-nav-02-register-page')
                    ->assertPathIs("/{$this->school->slug}/register");
        });
    }

    /** @test */
    public function can_navigate_from_login_to_forgot_password()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-nav-03-login-page-before')
                    ->clickLink('Forgot Password')
                    ->screenshot('ui-nav-04-forgot-password-page')
                    ->assertPathIs("/{$this->school->slug}/forgot-password");
        });
    }

    // ===========================================
    // FORM INPUT TESTS
    // ===========================================

    /** @test */
    public function can_type_in_email_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-input-01-before-email')
                    ->type('email', 'test@example.com')
                    ->screenshot('ui-input-02-after-email')
                    ->assertInputValue('email', 'test@example.com');
        });
    }

    /** @test */
    public function can_type_in_password_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-input-03-before-password')
                    ->type('password', 'secretpassword')
                    ->screenshot('ui-input-04-after-password')
                    ->assertInputValue('password', 'secretpassword');
        });
    }

    /** @test */
    public function can_check_remember_me_checkbox()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit("/{$this->school->slug}")
                    ->screenshot('ui-input-05-before-checkbox')
                    ->check('remember')
                    ->screenshot('ui-input-06-after-checkbox')
                    ->assertChecked('remember');
        });
    }
}
