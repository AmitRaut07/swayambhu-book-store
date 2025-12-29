<?php
// remove_from_wishlist.php
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

// Remove from wishlist
$sql = "DELETE FROM wishlist WHERE user_id={$user_id} AND product_id={$product_id}";
if(mysqli_query($conn, $sql)){
    $wishlist_count = get_wishlist_count($conn, $user_id);
    echo json_encode([
        'success' => true, 
        'in_wishlist' => false,
        'wishlist_count' => $wishlist_count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
}
?>
