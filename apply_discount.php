<?php
// apply_discount.php - Apply discount code to cart
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

$conn = db_connect();
$code = isset($_POST['code']) ? trim($_POST['code']) : '';

if(empty($code)){
    echo json_encode(['success' => false, 'message' => 'Please enter a discount code']);
    exit;
}

$discount = validate_discount_code($conn, $code);

if(!$discount){
    echo json_encode(['success' => false, 'message' => 'Invalid or expired discount code']);
    exit;
}

// Store discount in session
$_SESSION['discount_code'] = [
    'id' => $discount['id'],
    'code' => $discount['code'],
    'percentage' => $discount['discount_percentage']
];

// Calculate new total
$cart_total = get_cart_total($conn);
$discount_amount = $cart_total * ($discount['discount_percentage'] / 100);
$new_total = $cart_total - $discount_amount;

echo json_encode([
    'success' => true,
    'message' => 'Discount code applied successfully',
    'discount_percentage' => $discount['discount_percentage'],
    'discount_amount' => number_format($discount_amount, 2),
    'original_total' => number_format($cart_total, 2),
    'new_total' => number_format($new_total, 2)
]);
?>
