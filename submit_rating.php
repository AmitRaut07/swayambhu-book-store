<?php
// submit_rating.php - Handle rating submission
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

require_login();

$conn = db_connect();
$user_id = (int)$_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review = isset($_POST['review']) ? trim($_POST['review']) : '';

// Validate
if($product_id <= 0 || $rating < 1 || $rating > 5){
    flash_set('error', 'Invalid rating data');
    redirect('products.php');
}

// Check if product exists
$res = mysqli_query($conn, "SELECT id FROM products WHERE id={$product_id} LIMIT 1");
if(mysqli_num_rows($res) === 0){
    flash_set('error', 'Product not found');
    redirect('products.php');
}

// Verify user has purchased this product
if(!has_purchased_product($conn, $user_id, $product_id)){
    flash_set('error', 'You must purchase this product before you can review it');
    redirect("product_detail.php?id={$product_id}");
}

// Check if user already rated
if(has_user_rated($conn, $user_id, $product_id)){
    flash_set('error', 'You have already rated this product');
    redirect("product_detail.php?id={$product_id}");
}

// Insert rating
$review = mysqli_real_escape_string($conn, $review);
$sql = "INSERT INTO ratings (user_id, product_id, rating, review) 
        VALUES ({$user_id}, {$product_id}, {$rating}, '{$review}')";

if(mysqli_query($conn, $sql)){
    flash_set('success', 'Thank you for your review!');
} else {
    flash_set('error', 'Failed to submit review');
}

redirect("product_detail.php?id={$product_id}");
?>
