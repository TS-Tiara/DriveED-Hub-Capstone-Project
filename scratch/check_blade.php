<?php
$filepath = 'c:\Users\jcsdi\Documents\Driving School Management System\DrivingApp\resources\views\school\guest\dashboard.blade.php';
$content = file_get_contents($filepath);

preg_match_all('/(@if|@elseif|@else|@endif)/', $content, $matches);
$directives = $matches[0];

$level = 0;
$mismatches = [];

foreach ($directives as $i => $d) {
    if ($d === '@if') {
        $level++;
    } elseif ($d === '@endif') {
        $level--;
        if ($level < 0) {
            $mismatches[] = "Unexpected @endif at index $i";
            $level = 0;
        }
    } elseif ($d === '@else' || $d === '@elseif') {
        if ($level === 0) {
            $mismatches[] = "Unexpected $d at index $i (level 0)";
        }
    }
}

if ($level > 0) {
    $mismatches[] = "Unclosed @if (level $level at end)";
}

echo "Mismatches: " . implode(", ", $mismatches) . "\n";
echo "Directives count: " . count($directives) . "\n";
foreach ($directives as $i => $d) {
    echo "$i: $d\n";
}
?>
