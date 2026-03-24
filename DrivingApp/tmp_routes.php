<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$routes = Route::getRoutes();
foreach ($routes as $route) {
    if (strpos($route->getName(), 'enrollments') !== false) {
        echo $route->getName() . ' -> ' . $route->uri() . PHP_EOL;
    }
}
