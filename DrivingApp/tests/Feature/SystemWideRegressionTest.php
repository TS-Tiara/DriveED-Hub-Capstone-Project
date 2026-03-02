<?php

use App\Models\Admin;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Instructor;
use App\Models\ModuleLesson;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

function buildRegressionContext(): array
{
    $school = School::factory()->create();
    SchoolSetting::factory()->create(['school_id' => $school->id]);

    $branch = Branch::create([
        'school_id' => $school->id,
        'name' => 'Main Branch',
        'slug' => 'main-branch',
        'address' => 'Main Street',
        'contact_number' => '09123456789',
        'email' => 'branch@example.com',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $admin = Admin::factory()->create([
        'school_id' => $school->id,
        'branch_id' => $branch->id,
        'role' => Admin::ROLE_SCHOOL_ADMIN,
        'is_active' => true,
    ]);

    $branchSecretary = Admin::factory()->create([
        'school_id' => $school->id,
        'branch_id' => $branch->id,
        'role' => Admin::ROLE_BRANCH_SECRETARY,
        'is_active' => true,
    ]);

    $instructor = Instructor::factory()->create([
        'school_id' => $school->id,
        'branch_id' => $branch->id,
        'status' => 'active',
    ]);

    $student = Student::factory()->create([
        'school_id' => $school->id,
        'branch_id' => $branch->id,
        'status' => 'active',
        'role' => 'student',
    ]);

    $guest = Student::factory()->create([
        'school_id' => $school->id,
        'branch_id' => $branch->id,
        'status' => 'active',
        'role' => 'guest',
    ]);

    $course = Course::factory()->create([
        'school_id' => $school->id,
        'status' => 'active',
    ]);

    return compact(
        'school',
        'branch',
        'admin',
        'branchSecretary',
        'instructor',
        'student',
        'guest',
        'course'
    );
}

describe('System-wide regression: route accessibility', function () {
    test('public school routes are accessible and unknown routes return 404', function () {
        ['school' => $school] = buildRegressionContext();

        get(route('schools.login', $school))->assertOk();
        get(route('schools.registration.form', $school))->assertOk();
        get(route('schools.password.request', $school))->assertOk();

        get("/{$school->slug}/this-page-does-not-exist")->assertNotFound();
    });
});

describe('System-wide regression: role-based access', function () {
    test('student and instructor core pages are accessible to correct roles', function () {
        ['school' => $school, 'student' => $student, 'instructor' => $instructor] = buildRegressionContext();

        actingAs($student, 'student');
        get(route('schools.student.progress.index', $school))->assertOk();

        actingAs($instructor, 'instructor');
        get(route('schools.instructor.sessions.index', $school))->assertOk();
    });

    test('admin and branch secretary can access admin dashboard', function () {
        ['school' => $school, 'admin' => $admin, 'branchSecretary' => $branchSecretary] = buildRegressionContext();

        actingAs($admin, 'admin');
        get(route('schools.admin.dashboard', $school))->assertOk();

        actingAs($branchSecretary, 'admin');
        get(route('schools.admin.dashboard', $school))->assertOk();
    });

    test('guest role can access guest dashboard while student role is redirected from guest dashboard', function () {
        ['school' => $school, 'guest' => $guest, 'student' => $student] = buildRegressionContext();

        actingAs($guest, 'student');
        get(route('schools.guest.dashboard', $school))->assertOk();

        actingAs($student, 'student');
        get(route('schools.guest.dashboard', $school))->assertStatus(302);
    });

    test('student is blocked from admin pages by auth middleware', function () {
        ['school' => $school, 'student' => $student] = buildRegressionContext();

        actingAs($student, 'student');
        get(route('schools.admin.userManagement', $school))->assertStatus(302);
    });

    test('branch secretary is forbidden from school-admin-only management page', function () {
        ['school' => $school, 'branchSecretary' => $branchSecretary] = buildRegressionContext();

        actingAs($branchSecretary, 'admin');
        getJson(route('schools.admin.admin-management.index', $school))->assertForbidden();
    });
});

describe('System-wide regression: LMS modules and lessons CRUD', function () {
    test('school admin can read, validate-update, and delete a module safely', function () {
        ['school' => $school, 'admin' => $admin, 'course' => $course] = buildRegressionContext();

        actingAs($admin, 'admin');

        $module = CourseModule::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'title' => 'Road Safety Basics',
            'description' => 'Safety-first learning module',
            'module_type' => 'theoretical',
            'sort_order' => 1,
        ]);

        get(route('schools.admin.courses.modules.index', [
            'school' => $school,
            'course' => $course,
        ]))->assertOk();

        get(route('schools.admin.courses.modules.show', [
            'school' => $school,
            'course' => $course,
            'module' => $module,
        ]))->assertOk();

        assertDatabaseHas('course_modules', [
            'id' => $module->id,
            'course_id' => $course->id,
            'title' => 'Road Safety Basics',
        ]);

        putJson(route('schools.admin.courses.modules.update', [
            'school' => $school,
            'course' => $course,
            'module' => $module,
        ]), [
            'title' => 'Road Safety Fundamentals',
            'description' => 'Updated module details',
            'module_type' => 'theoretical',
            'sort_order' => 2,
        ])
            ->assertUnprocessable();

        assertDatabaseHas('course_modules', [
            'id' => $module->id,
            'title' => 'Road Safety Basics',
            'module_type' => 'theoretical',
        ]);

        delete(route('schools.admin.courses.modules.destroy', [
            'school' => $school,
            'course' => $course,
            'module' => $module,
        ]))
            ->assertStatus(302);

        assertDatabaseMissing('course_modules', ['id' => $module->id]);
    });

    test('school admin can create, update, and delete a lesson safely', function () {
        ['school' => $school, 'admin' => $admin, 'course' => $course] = buildRegressionContext();

        actingAs($admin, 'admin');

        $module = CourseModule::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'title' => 'Initial Module',
            'description' => 'Initial module description',
            'module_type' => 'theoretical',
            'sort_order' => 1,
        ]);

        $createResponse = postJson(route('schools.admin.courses.modules.lessons.store', [
            'school' => $school,
            'course' => $course,
            'module' => $module,
        ]), [
            'title' => 'Lesson One',
            'content' => 'Lesson content body',
            'video_url' => 'https://example.com/video-1',
            'sort_order' => 1,
        ]);

        $createResponse->assertOk()->assertJson(['success' => true]);

        $lessonId = $createResponse->json('lesson.id');

        assertDatabaseHas('module_lessons', [
            'id' => $lessonId,
            'module_id' => $module->id,
            'title' => 'Lesson One',
        ]);

        putJson(route('schools.admin.courses.modules.lessons.update', [
            'school' => $school,
            'course' => $course,
            'module' => $module,
            'lesson' => $lessonId,
        ]), [
            'title' => 'Lesson One Updated',
            'content' => 'Updated lesson content',
            'video_url' => 'https://example.com/video-2',
            'sort_order' => 2,
        ])
            ->assertOk()
            ->assertJson(['success' => true]);

        assertDatabaseHas('module_lessons', [
            'id' => $lessonId,
            'title' => 'Lesson One Updated',
            'sort_order' => 2,
        ]);

        delete(route('schools.admin.courses.modules.lessons.destroy', [
            'school' => $school,
            'course' => $course,
            'module' => $module,
            'lesson' => $lessonId,
        ]))
            ->assertStatus(302);

        assertDatabaseMissing('module_lessons', ['id' => $lessonId]);
    });
});

describe('System-wide regression: middleware enforcement and status responses', function () {
    test('module route returns 404 for module-course mismatch', function () {
        ['school' => $school, 'admin' => $admin] = buildRegressionContext();

        actingAs($admin, 'admin');

        $firstCourse = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);
        $secondCourse = Course::factory()->create(['school_id' => $school->id, 'status' => 'active']);

        $moduleOnSecondCourse = CourseModule::create([
            'school_id' => $school->id,
            'course_id' => $secondCourse->id,
            'title' => 'Second Course Module',
            'description' => 'Mismatched module',
            'module_type' => 'theoretical',
            'sort_order' => 1,
        ]);

        get(route('schools.admin.courses.modules.show', [
            'school' => $school,
            'course' => $firstCourse,
            'module' => $moduleOnSecondCourse,
        ]))
            ->assertNotFound();
    });

    test('lesson route returns 404 for lesson-module mismatch', function () {
        ['school' => $school, 'admin' => $admin, 'course' => $course] = buildRegressionContext();

        actingAs($admin, 'admin');

        $moduleA = CourseModule::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'title' => 'Module A',
            'description' => 'Module A description',
            'module_type' => 'theoretical',
            'sort_order' => 1,
        ]);

        $moduleB = CourseModule::create([
            'school_id' => $school->id,
            'course_id' => $course->id,
            'title' => 'Module B',
            'description' => 'Module B description',
            'module_type' => 'reference',
            'sort_order' => 2,
        ]);

        $lessonOnModuleB = ModuleLesson::create([
            'school_id' => $school->id,
            'module_id' => $moduleB->id,
            'title' => 'Lesson B1',
            'content' => 'Belongs to module B',
            'sort_order' => 1,
        ]);

        get(route('schools.admin.courses.modules.lessons.show', [
            'school' => $school,
            'course' => $course,
            'module' => $moduleA,
            'lesson' => $lessonOnModuleB,
        ]))
            ->assertNotFound();
    });
});
