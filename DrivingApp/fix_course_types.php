<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fix course_type for courses whose TITLES clearly indicate 'practical'
// Keywords: practical, PDC, motorcycle (riding = practical)
$practicalKeywords = ['practical', 'pdc', 'motorcycle riding', 'behind the wheel'];

// First show what we'll change
$courses = \App\Models\Course::all(['id', 'title', 'course_type']);
echo "=== PLANNED CHANGES ===\n";
foreach ($courses as $course) {
    $titleLower = strtolower($course->title);
    $isPractical = false;
    foreach ($practicalKeywords as $kw) {
        if (str_contains($titleLower, $kw)) {
            $isPractical = true;
            break;
        }
    }
    $newType = $isPractical ? 'practical' : 'theoretical';
    if ($newType !== $course->course_type) {
        echo "CHANGE id={$course->id}: '{$course->course_type}' -> '{$newType}' | {$course->title}\n";
    }
}

echo "\nApplying changes...\n";
foreach ($courses as $course) {
    $titleLower = strtolower($course->title);
    $isPractical = false;
    foreach ($practicalKeywords as $kw) {
        if (str_contains($titleLower, $kw)) {
            $isPractical = true;
            break;
        }
    }
    $newType = $isPractical ? 'practical' : 'theoretical';
    if ($newType !== $course->course_type) {
        $course->course_type = $newType;
        $course->save();
        echo "FIXED id={$course->id}: {$course->title}\n";
    }
}
echo "Done!\n";
