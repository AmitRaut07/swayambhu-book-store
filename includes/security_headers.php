<?php
// security_headers.php - Include this at the top of protected pages
// Prevents browser caching and back button access after logout

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set cache control headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// Optional: Add security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
?>
