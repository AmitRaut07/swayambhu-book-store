<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// Save cart to database before logout (if user is logged in)
if(isset($_SESSION['user_id'])) {
    $conn = db_connect();
    save_cart_to_db($conn, $_SESSION['user_id']);
    mysqli_close($conn);
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once 'config.php';
require_once 'functions.php';

flash_set('success', 'You have been logged out successfully.');
redirect('login.php');
?>
