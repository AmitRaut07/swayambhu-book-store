<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';

// DB connection
function db_connect(){
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if(!$conn) die("DB Connection Failed: " . mysqli_connect_error());
    return $conn;
}

// Escape output
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// Redirect helper
function redirect($url){
    // If URL starts with http/https, use as-is
    if(strpos($url,'http')===0){
        header("Location: $url");
        exit;
    }

    // If URL starts with '/', use as-is (root-relative path)
    if(strpos($url, '/') === 0){
        header("Location: $url");
        exit;
    }
    
    // If URL already starts with BASE_URL, use as-is
    $base_path = rtrim(BASE_URL, '/');
    if(strpos($url, $base_path) === 0){
        header("Location: $url");
        exit;
    }
    
    // Otherwise, prepend BASE_URL
    $url = $base_path . '/' . ltrim($url,'/');
    header("Location: $url");
    exit;
}

// Flash messages
function flash_set($key,$val){ $_SESSION['flash'][$key]=$val; }
function flash_get($key){
    if(isset($_SESSION['flash'][$key])){
        $v = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $v;
    }
    return null;
}

// User checks
function is_logged_in(){ return !empty($_SESSION['user_id']); }
function require_login(){
    if(!is_logged_in()){
        // Save the current URL to return after login
        $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
        flash_set('error', 'Please login');
        redirect('login.php');
    }
}

function admin_check(){ return !empty($_SESSION['is_admin']); }
function require_admin(){ if(!admin_check()){ flash_set('error','Admin only'); redirect('login.php'); } }
function is_admin(){ return admin_check(); } // Alias for consistency

// Cart total
function get_cart_total($conn){
    $total = 0;
    if(isset($_SESSION['cart'])){
        foreach($_SESSION['cart'] as $id=>$qty){
            $id=(int)$id;
            $res=mysqli_query($conn,"SELECT price FROM products WHERE id={$id}");
            if($row=mysqli_fetch_assoc($res)) $total+=$row['price']*$qty;
        }
    }
    return $total;
}

// Wishlist functions
function get_wishlist_count($conn, $user_id){
    $user_id = (int)$user_id;
    $res = mysqli_query($conn, "SELECT COUNT(*) as count FROM wishlist WHERE user_id={$user_id}");
    $row = mysqli_fetch_assoc($res);
    return $row ? (int)$row['count'] : 0;
}

function is_in_wishlist($conn, $user_id, $product_id){
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    $res = mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id={$user_id} AND product_id={$product_id} LIMIT 1");
    return mysqli_num_rows($res) > 0;
}

// Rating functions
function get_average_rating($conn, $product_id){
    $product_id = (int)$product_id;
    $res = mysqli_query($conn, "SELECT AVG(rating) as avg_rating FROM ratings WHERE product_id={$product_id}");
    $row = mysqli_fetch_assoc($res);
    return $row && $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
}

function get_review_count($conn, $product_id){
    $product_id = (int)$product_id;
    $res = mysqli_query($conn, "SELECT COUNT(*) as count FROM ratings WHERE product_id={$product_id}");
    $row = mysqli_fetch_assoc($res);
    return $row ? (int)$row['count'] : 0;
}

function has_user_rated($conn, $user_id, $product_id){
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    $res = mysqli_query($conn, "SELECT id FROM ratings WHERE user_id={$user_id} AND product_id={$product_id} LIMIT 1");
    return mysqli_num_rows($res) > 0;
}

// Discount functions
function calculate_discount_price($price, $discount_percentage){
    if($discount_percentage > 0){
        return $price - ($price * ($discount_percentage / 100));
    }
    return $price;
}

function validate_discount_code($conn, $code){
    $code = mysqli_real_escape_string($conn, $code);
    $query = "SELECT * FROM discount_codes WHERE code='{$code}' AND active=1 
              AND (valid_from IS NULL OR valid_from <= CURDATE()) 
              AND (valid_until IS NULL OR valid_until >= CURDATE())
              AND (max_uses IS NULL OR times_used < max_uses) LIMIT 1";
    $res = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($res);
}

function apply_discount_code($conn, $code_id){
    $code_id = (int)$code_id;
    mysqli_query($conn, "UPDATE discount_codes SET times_used = times_used + 1 WHERE id={$code_id}");
}

// Purchase verification
function has_purchased_product($conn, $user_id, $product_id){
    $user_id = (int)$user_id;
    $product_id = (int)$product_id;
    
    // Check if order_items table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'order_items'");
    if(!$table_check || mysqli_num_rows($table_check) === 0){
        // Table doesn't exist, return false for now
        return false;
    }
    
    $query = "SELECT COUNT(*) as count FROM order_items oi 
              JOIN orders o ON oi.order_id = o.id 
              WHERE o.user_id = {$user_id} 
              AND oi.product_id = {$product_id} 
              AND o.status IN ('completed', 'delivered')
              LIMIT 1";
    
    $res = mysqli_query($conn, $query);
    if($res && $row = mysqli_fetch_assoc($res)){
        return (int)$row['count'] > 0;
    }
    return false;
}

// Cart persistence functions
function save_cart_to_db($conn, $user_id) {
    if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        // If cart is empty, clear database cart
        $user_id = (int)$user_id;
        mysqli_query($conn, "DELETE FROM cart WHERE user_id={$user_id}");
        return;
    }
    
    $user_id = (int)$user_id;
    
    // Clear existing cart items for this user
    mysqli_query($conn, "DELETE FROM cart WHERE user_id={$user_id}");
    
    // Insert current cart items
    foreach($_SESSION['cart'] as $product_id => $qty) {
        $product_id = (int)$product_id;
        $qty = (int)$qty;
        if($qty > 0) {
            mysqli_query($conn, "INSERT INTO cart (user_id, product_id, qty) VALUES ({$user_id}, {$product_id}, {$qty})");
        }
    }
}

function load_cart_from_db($conn, $user_id) {
    $user_id = (int)$user_id;
    $res = mysqli_query($conn, "SELECT product_id, qty FROM cart WHERE user_id={$user_id}");
    
    $db_cart = [];
    while($row = mysqli_fetch_assoc($res)) {
        $db_cart[(int)$row['product_id']] = (int)$row['qty'];
    }
    
    // Merge with session cart (session takes priority for conflicts)
    if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach($db_cart as $pid => $qty) {
            if(!isset($_SESSION['cart'][$pid])) {
                $_SESSION['cart'][$pid] = $qty;
            }
        }
    } else {
        $_SESSION['cart'] = $db_cart;
    }
    
    // Save merged cart back to DB
    save_cart_to_db($conn, $user_id);
}
?>
