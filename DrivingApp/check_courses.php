<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Print all courses to file
$lines = [];
$courses = \App\Models\Course::all(['id', 'title', 'course_type']);
foreach ($courses as $course) {
    $lines[] = $course->id . " | " . $course->course_type . " | " . $course->title;
}
file_put_contents(__DIR__ . '/courses_list.txt', implode("\n", $lines));
echo "Done. See courses_list.txt\n";
