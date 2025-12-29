<?php
require_once 'config.php';
require_once 'functions.php';
$conn = db_connect();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    redirect('cart.php');
}
$qtys = isset($_POST['qty'])?$_POST['qty']:[];
foreach($qtys as $id => $q){
    $id = (int)$id; $q = (int)$q;
    if($id <= 0 || $q <= 0) { unset($_SESSION['cart'][$id]); continue; }
    // check stock
    $res = mysqli_query($conn, "SELECT stock FROM products WHERE id={$id}");
    if($row = mysqli_fetch_assoc($res) && $q <= $row['stock']){
        $_SESSION['cart'][$id] = $q;
    } else {
        flash_set('error', 'Quantity for product ' . $id . ' exceeds stock or product missing.');
    }
}

// Sync with database if user is logged in
if(is_logged_in()) {
    save_cart_to_db($conn, $_SESSION['user_id']);
}

flash_set('success', 'Cart updated.');
redirect('cart.php');
