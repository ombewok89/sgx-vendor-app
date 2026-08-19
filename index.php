<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Always sync root .env into laravel-backend/.env if root .env exists
if (file_exists(__DIR__.'/.env')) {
    @copy(__DIR__.'/.env', __DIR__.'/laravel-backend/.env');
}

// Auto-clean stale compiled caches and OPcache so updates in routes/api.php take effect immediately
if (function_exists('opcache_reset')) {
    @opcache_reset();
}
foreach (glob(__DIR__.'/laravel-backend/bootstrap/cache/*.php') as $f) {
    if (basename($f) !== '.gitignore') {
        @unlink($f);
    }
}
foreach (glob(__DIR__.'/bootstrap/cache/*.php') as $f) {
    if (basename($f) !== '.gitignore') {
        @unlink($f);
    }
}

// Auto-sync routes & app from laravel-backend to root if legacy root folders exist
if (is_dir(__DIR__.'/routes') && file_exists(__DIR__.'/laravel-backend/routes/api.php')) {
    @copy(__DIR__.'/laravel-backend/routes/api.php', __DIR__.'/routes/api.php');
}
if (is_dir(__DIR__.'/app') && is_dir(__DIR__.'/laravel-backend/app')) {
    @mkdir(__DIR__.'/app/Http/Controllers/Api', 0755, true);
    @mkdir(__DIR__.'/app/Services', 0755, true);
    @mkdir(__DIR__.'/app/Jobs', 0755, true);
    @mkdir(__DIR__.'/app/Models', 0755, true);
    @copy(__DIR__.'/laravel-backend/app/Http/Controllers/Api/WhatsAppController.php', __DIR__.'/app/Http/Controllers/Api/WhatsAppController.php');
    @copy(__DIR__.'/laravel-backend/app/Services/FonnteService.php', __DIR__.'/app/Services/FonnteService.php');
    @copy(__DIR__.'/laravel-backend/app/Services/WhatsAppTemplateService.php', __DIR__.'/app/Services/WhatsAppTemplateService.php');
    @copy(__DIR__.'/laravel-backend/app/Services/WhatsAppNotificationService.php', __DIR__.'/app/Services/WhatsAppNotificationService.php');
    @copy(__DIR__.'/laravel-backend/app/Jobs/SendWhatsAppNotificationJob.php', __DIR__.'/app/Jobs/SendWhatsAppNotificationJob.php');
    @copy(__DIR__.'/laravel-backend/app/Models/NotificationLog.php', __DIR__.'/app/Models/NotificationLog.php');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/laravel-backend/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader and boot Laravel Application
if (file_exists(__DIR__.'/laravel-backend/vendor/autoload.php')) {
    require __DIR__.'/laravel-backend/vendor/autoload.php';
    /** @var Application $app */
    $app = require_once __DIR__.'/laravel-backend/bootstrap/app.php';
    $app->handleRequest(Request::capture());
} elseif (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
    /** @var Application $app */
    if (file_exists(__DIR__.'/laravel-backend/bootstrap/app.php')) {
        $app = require_once __DIR__.'/laravel-backend/bootstrap/app.php';
    } else {
        $app = require_once __DIR__.'/bootstrap/app.php';
    }
    $app->handleRequest(Request::capture());
}
