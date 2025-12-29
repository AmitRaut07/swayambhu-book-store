<?php
// Processing page for adding to cart (redirects back to products page)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'functions.php';
$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('products.php');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;

if ($id <= 0 || $qty <= 0) {
    flash_set('error', 'Invalid product or quantity.');
    redirect('product.php?id=' . $id);
}

// check stock
$res = mysqli_query($conn, "SELECT stock FROM products WHERE id={$id}");
if ($row = mysqli_fetch_assoc($res)) {
    if ($qty > $row['stock']) {
        flash_set('error', 'Requested quantity exceeds stock.');
        redirect('product.php?id=' . $id);
    }
} else {
    flash_set('error', 'Product not found.');
    redirect('products.php');
}

if (!isset($_SESSION['cart']))
    $_SESSION['cart'] = [];
if (isset($_SESSION['cart'][$id]))
    $_SESSION['cart'][$id] += $qty;
else
    $_SESSION['cart'][$id] = $qty;

// Save to database if user is logged in
if(is_logged_in()) {
    save_cart_to_db($conn, $_SESSION['user_id']);
}

flash_set('success', 'Item added to cart.');
redirect('products.php');
