<?php
// Config for SGX Vendor PHP Native API
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'sgx_vendor_jwt_secret_key_2026_sinargrafika_enterprise');
define('JWT_EXPIRY_DAYS', 7);

// Storage Directories
define('BASE_DIR', dirname(__DIR__));
define('DATA_DIR', BASE_DIR . '/data');
define('UPLOADS_DIR', BASE_DIR . '/uploads');
define('DB_FILE', DATA_DIR . '/sgx_vendor.sqlite');

// Ensure directories exist
if (!is_dir(DATA_DIR)) {
    @mkdir(DATA_DIR, 0755, true);
}
if (!is_dir(UPLOADS_DIR)) {
    @mkdir(UPLOADS_DIR, 0755, true);
}
