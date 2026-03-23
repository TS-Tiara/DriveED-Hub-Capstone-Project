<?php

use App\Models\Admin;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

describe('Forensic Reporting: Route Unification', function () {
    test('reports view export links resolve to unified exports endpoints', function () {
        $this->withoutExceptionHandling();
        $school = School::factory()->create();
        SchoolSetting::factory()->create(['school_id' => $school->id]);
        $branch = \App\Models\Branch::create([
            'school_id' => $school->id,
            'name' => 'Main Branch',
            'slug' => 'main-branch',
            'address' => 'Test Address',
            'contact_number' => '09123456789',
            'email' => 'branch@example.com',
            'is_active' => true,
        ]);
        $admin = Admin::factory()->create([
            'school_id' => $school->id, 
            'branch_id' => $branch->id,
            'role' => Admin::ROLE_SCHOOL_ADMIN,
            'is_active' => true,
            'must_reset_password' => false,
        ]);

        $this->actingAs($admin, 'admin');
        
        // Verify the main reports page loads
        $this->get(route('schools.admin.reports.index', $school))->assertOk();

        // Verify that the route names used in the UI exist and are correct
        $this->assertTrue(Route::has('schools.admin.exports.payments.excel'));
        $this->assertTrue(Route::has('schools.admin.exports.bookings.excel'));
        $this->assertTrue(Route::has('schools.admin.exports.courses.excel'));
        
        // Verify they are within the exports namespace/group as claimed
        $this->assertStringContainsString('/admin/exports/', route('schools.admin.exports.payments.excel', $school));
    });
});

describe('Forensic Reporting: Chronological Integrity', function () {
    test('payment exports query syntax is valid for COALESCE ordering', function () {
        $school = School::factory()->create();
        $admin = Admin::factory()->create([
            'school_id' => $school->id, 
            'role' => Admin::ROLE_SCHOOL_ADMIN,
            'is_active' => true
        ]);
        $student = Student::factory()->create(['school_id' => $school->id, 'role' => 'student']);
        
        // 1. Oldest: paid_on early Jan
        Payment::factory()->create([
            'school_id' => $school->id,
            'payer_user_id' => $student->id,
            'paid_on' => Carbon::parse('2024-01-01'),
            'received_at' => null,
            'reference' => 'REF-001',
        ]);

        // 2. Middle: paid_on mid Jan, but received_at Late Jan
        Payment::factory()->create([
            'school_id' => $school->id,
            'payer_user_id' => $student->id,
            'paid_on' => Carbon::parse('2024-01-15'),
            'received_at' => Carbon::parse('2024-01-30'), 
            'reference' => null,
            'or_number' => 'OR-002',
        ]);

        actingAs($admin, 'admin');

        // Confirming the routes respond with 200 proves the orderByRaw syntax is valid across PDF and Excel
        $response = $this->get(route('schools.admin.exports.payments.excel', $school));
        $response->assertOk();
        
        // Note: Strict row ordering assertion requires parsing the Excel/PDF content. 
        // For now, we verify that the query doesn't crash and returns the expected status.
        // To truly verify ordering, we check if the response contains the references in the right order.
        $content = $response->getContent();
        
        // The Middle payment (received_at Jan 30) should be chronologically AFTER (above in DESC) the Oldest (paid_on Jan 1)
        // So 'OR-002' should appear before 'REF-001' in the output stream if it's descending.
        $posOR = strpos($content, 'OR-002');
        $posREF = strpos($content, 'REF-001');
        
        $this->assertNotFalse($posOR, 'OR-002 not found in export');
        $this->assertNotFalse($posREF, 'REF-001 not found in export');

        $this->assertLessThan(
            $posREF, 
            $posOR,
            'Chronological ordering failed: OR-002 (Jan 30) should appear BEFORE REF-001 (Jan 1) in DESC order'
        );

        $this->get(route('schools.admin.exports.payments.pdf', $school))->assertOk();
    });

    test('deterministic query-level ordering handles received_at fallback correctly', function () {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->id, 'role' => 'student']);
        
        // 1. Oldest by paid_on, no received_at
        $p1 = Payment::factory()->create([
            'school_id' => $school->id,
            'payer_user_id' => $student->id,
            'paid_on' => Carbon::parse('2024-01-01'),
            'received_at' => null,
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
        ]);

        // 2. Middle by paid_on, but latest by received_at
        $p2 = Payment::factory()->create([
            'school_id' => $school->id,
            'payer_user_id' => $student->id,
            'paid_on' => Carbon::parse('2024-01-15'),
            'received_at' => Carbon::parse('2024-01-30'), 
            'created_at' => Carbon::parse('2024-01-15 10:00:00'),
        ]);

        // 3. Newest by paid_on, but middle by received_at
        $p3 = Payment::factory()->create([
            'school_id' => $school->id,
            'payer_user_id' => $student->id,
            'paid_on' => Carbon::parse('2024-01-20'),
            'received_at' => Carbon::parse('2024-01-25'),
            'created_at' => Carbon::parse('2024-01-20 10:00:00'),
        ]);

        // Scenario:
        // P1 Forensic Date: 2024-01-01 (Oldest)
        // P3 Forensic Date: 2024-01-25 (Middle)
        // P2 Forensic Date: 2024-01-30 (Newest)

        // Query mirroring ExportController logic
        $orderedIds = Payment::where('school_id', $school->id)
            ->orderByRaw('COALESCE(received_at, paid_on) DESC, created_at DESC')
            ->pluck('id')
            ->toArray();

        // Expect: [P2, P3, P1] for DESC ordering
        $this->assertEquals([$p2->id, $p3->id, $p1->id], $orderedIds);
    });
});
