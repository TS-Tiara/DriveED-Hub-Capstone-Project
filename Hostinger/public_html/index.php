<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Hostinger path configuration
|--------------------------------------------------------------------------
| Change this path if your private Laravel app directory has a different
| location or name.
*/
$basePath = realpath(__DIR__ . '/../laravel_app');

if ($basePath === false) {
    http_response_code(500);
    echo 'Laravel base path not found. Check Hostinger/public_html/index.php path.';
    exit;
}

if (file_exists($maintenance = $basePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath . '/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
