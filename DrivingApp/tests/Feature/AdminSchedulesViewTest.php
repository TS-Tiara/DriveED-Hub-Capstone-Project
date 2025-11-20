<?php

use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\School;
use App\Models\TimeSlot;
use Illuminate\Support\Carbon;

it('shows manual schedules and occupied time slots together', function (): void {
    $school = School::firstOrCreate(
        ['slug' => 'drivingschool1'],
        [
            'name' => 'Driving School 1',
            'timezone' => 'Asia/Manila',
        ]
    );

    $admin = Admin::create([
        'school_id' => $school->id,
        'name' => 'Site Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
        'role' => 'owner',
    ]);

    $instructor = Instructor::create([
        'school_id' => $school->id,
        'name' => 'Jane Instructor',
        'email' => 'jane@example.com',
        'contact' => '09171234567',
        'status' => 'active',
        'availability' => 'available',
        'password' => 'password',
    ]);

    $manualSchedule = Schedule::create([
        'school_id' => $school->id,
        'instructor_id' => $instructor->id,
        'date' => Carbon::now()->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '09:00:00',
        'status' => 'available',
        'created_by' => $admin->id,
    ]);

    $timeSlot = TimeSlot::create([
        'school_id' => $school->id,
        'date' => Carbon::now()->toDateString(),
        'start_time' => '10:00:00',
        'end_time' => '11:00:00',
        'status' => 'available',
        'max_instructors' => 2,
        'notes' => 'Morning session',
    ]);

    $timeSlot->instructors()->attach($instructor->id, [
        'school_id' => $school->id,
        'assignment_type' => 'admin_assigned',
    ]);

    $response = $this
        ->actingAs($admin, 'admin')
        ->get("/{$school->slug}/admin/schedules");

    $response->assertOk();

    $response->assertViewHas('scheduleEntries', function ($entries) use ($manualSchedule, $timeSlot, $instructor): bool {
        $manualKey = 'manual-' . $manualSchedule->id;
        $timeSlotKey = 'timeslot-' . $timeSlot->id . '-' . $instructor->id;

        return $entries->contains(fn ($entry) => $entry['key'] === $manualKey)
            && $entries->contains(fn ($entry) => $entry['key'] === $timeSlotKey && $entry['type'] === 'timeslot');
    });

    $response->assertSeeText('Time Slot');
    $response->assertSeeText('Assignment: Admin assigned');
});
