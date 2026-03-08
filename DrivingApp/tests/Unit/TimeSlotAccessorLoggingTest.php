<?php

use App\Models\TimeSlot;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    resetTimeSlotInvalidTimeLogCache();
});

afterEach(function () {
    resetTimeSlotInvalidTimeLogCache();
});

test('formatted time accessor logs malformed value only once for repeated input', function () {
    Log::spy();

    foreach (range(1, 5) as $_) {
        $slot = new TimeSlot(['start_time' => 'not-a-time']);
        expect($slot->formatted_start_time)->toBeNull();
    }

    Log::shouldHaveReceived('warning')->once();
});

test('formatted time accessor warning logs are capped for distinct malformed values', function () {
    Log::spy();

    foreach (range(1, 550) as $index) {
        $slot = new TimeSlot(['start_time' => 'bad-time-' . $index]);
        expect($slot->formatted_start_time)->toBeNull();
    }

    Log::shouldHaveReceived('warning')->times(500);
});

function resetTimeSlotInvalidTimeLogCache(): void
{
    $reflection = new ReflectionClass(TimeSlot::class);
    $property = $reflection->getProperty('invalidTimeLogCache');
    $property->setAccessible(true);
    $property->setValue(null, []);
}
