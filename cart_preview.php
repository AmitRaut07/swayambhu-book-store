<?php
// cart_preview.php - Returns HTML for cart preview dropdown
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

$conn = db_connect();
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$cart_count = array_sum($cart);

if($cart_count === 0){
    echo '<div class="cart-preview-empty">Your cart is empty</div>';
    exit;
}

$total = 0;
$items_html = '';
$item_count = 0;

foreach($cart as $product_id => $qty){
    if($item_count >= 3) break; // Show max 3 items
    
    $product_id = (int)$product_id;
    $res = mysqli_query($conn, "SELECT title, price, image FROM products WHERE id={$product_id} LIMIT 1");
    
    if($row = mysqli_fetch_assoc($res)){
        $title = e($row['title']);
        $price = $row['price'];
        $subtotal = $price * $qty;
        $total += $subtotal;
        $img = !empty($row['image']) ? UPLOADS_URL . e($row['image']) : BASE_URL . 'placeholder.png';
        
        $items_html .= "
        <div class='cart-preview-item'>
            <img src='{$img}' alt='{$title}'>
            <div class='cart-preview-item-info'>
                <div class='cart-preview-item-title'>{$title}</div>
                <div class='cart-preview-item-price'>{$qty} x Rs. " . number_format($price, 2) . "</div>
            </div>
        </div>";
        
        $item_count++;
    }
}

// Calculate full total
$full_total = get_cart_total($conn);

echo $items_html;

if($cart_count > 3){
    echo "<div class='cart-preview-more'>+" . ($cart_count - 3) . " more items</div>";
}

echo "
<div class='cart-preview-total'>
    <strong>Total:</strong> Rs. " . number_format($full_total, 2) . "
</div>
<div class='cart-preview-actions'>
    <a href='" . BASE_URL . "cart.php' class='btn btn-secondary'>View Cart</a>
    <a href='" . BASE_URL . "checkout.php' class='btn btn-primary'>Checkout</a>
</div>";
?>
