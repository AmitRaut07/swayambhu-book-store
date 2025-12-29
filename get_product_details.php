<?php
// get_product_details.php - Get product details for quick view
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$conn = db_connect();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($product_id <= 0){
    echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    exit;
}

$res = mysqli_query($conn, "SELECT * FROM products WHERE id={$product_id} LIMIT 1");

if($row = mysqli_fetch_assoc($res)){
    $product = [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'author' => $row['author'],
        'price' => $row['price'],
        'stock' => (int)$row['stock'],
        'description' => $row['description'] ?? '',
        'image' => !empty($row['image']) ? UPLOADS_URL . $row['image'] : BASE_URL . 'placeholder.png'
    ];
    
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}
?>
