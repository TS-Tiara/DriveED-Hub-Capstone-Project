<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $url = app('url')->route('schools.admin.reports.index', ['school' => 'lyspeed-driving']);
    echo "SUCCESS: " . $url . "\n";
}
catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
