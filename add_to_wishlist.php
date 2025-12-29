<?php
// add_to_wishlist.php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if(!is_logged_in()){
    echo json_encode(['success' => false, 'message' => 'not_logged_in']);
    exit;
}

$conn = db_connect();
$user_id = (int)$_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

if($product_id <= 0){
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if product exists
$res = mysqli_query($conn, "SELECT id FROM products WHERE id={$product_id} LIMIT 1");
if(mysqli_num_rows($res) === 0){
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check if already in wishlist
if(is_in_wishlist($conn, $user_id, $product_id)){
    echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
    exit;
}

// Add to wishlist
$sql = "INSERT INTO wishlist (user_id, product_id) VALUES ({$user_id}, {$product_id})";
if(mysqli_query($conn, $sql)){
    $wishlist_count = get_wishlist_count($conn, $user_id);
    echo json_encode([
        'success' => true, 
        'in_wishlist' => true,
        'wishlist_count' => $wishlist_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
}
?>
