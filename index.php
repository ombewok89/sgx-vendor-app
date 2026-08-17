<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Always sync root .env into laravel-backend/.env if root .env exists
if (file_exists(__DIR__.'/.env')) {
    @copy(__DIR__.'/.env', __DIR__.'/laravel-backend/.env');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/laravel-backend/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
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
}
