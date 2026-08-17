<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/laravel-backend/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register Composer autoloader
if (file_exists(__DIR__.'/laravel-backend/vendor/autoload.php')) {
    require __DIR__.'/laravel-backend/vendor/autoload.php';
    /** @var Application $app */
    $app = require_once __DIR__.'/laravel-backend/bootstrap/app.php';
    $app->handleRequest(Request::capture());
} elseif (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
    /** @var Application $app */
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->handleRequest(Request::capture());
} else {
    echo "<h1>SGX Vendor Application</h1><p>Autoloader not found. Please run composer install.</p>";
}
