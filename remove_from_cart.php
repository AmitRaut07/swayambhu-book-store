<?php
require_once 'config.php';
require_once 'functions.php';

$id = isset($_GET['id'])?(int)$_GET['id']:0;
if($id && isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);
    
    // Sync with database if user is logged in
    if(is_logged_in()) {
        $conn = db_connect();
        save_cart_to_db($conn, $_SESSION['user_id']);
        mysqli_close($conn);
    }
}

flash_set('success', 'Item removed.');
redirect('cart.php');
