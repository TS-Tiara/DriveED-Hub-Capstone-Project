<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$request = Illuminate\Http\Request::create('/antigravity-academy/admin/login', 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . PHP_EOL;
if ($response->getStatusCode() === 404) {
    echo "404 Not Found" . PHP_EOL;
} else {
    echo "Content Length: " . strlen($response->getContent()) . PHP_EOL;
}
$kernel->terminate($request, $response);
