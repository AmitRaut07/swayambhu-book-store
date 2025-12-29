<?php
define('BASE_URL', 'http://localhost/bookstore_project/');  // Change if folder is different
define('SITE_TITLE', 'Swayambhu Bookstore');
define('SITE_LOCATION', 'Swayambhu, Nepal');

// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bookstore');

// Upload folder for product images (server path)
define('UPLOADS_DIR', __DIR__ . '/uploads/');

// Upload folder for product images (URL path)
define('UPLOADS_URL', BASE_URL . 'uploads/');

// Khalti (test)
define('KHALTI_PUBLIC_KEY', 'test_public_key');
define('KHALTI_SECRET_KEY', 'test_secret_key');
?>
