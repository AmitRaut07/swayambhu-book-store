<?php
// Start session if not already started
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Include config and functions
require_once '../config.php';
require_once '../functions.php';

// Ensure user is admin
require_admin();

// DB connection
$conn = db_connect();

// Get product ID from query string
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id > 0){
    // Delete product from database
    mysqli_query($conn, "DELETE FROM products WHERE id={$id}");
    flash_set('success','Product deleted successfully.');
}

// Redirect back to admin products page
redirect( 'admin/products.php');
