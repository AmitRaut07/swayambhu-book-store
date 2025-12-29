<?php
// process_checkout.php - Process checkout and initiate Khalti payment
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'functions.php';
require_once 'khalti_config.php';

$conn = db_connect();
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
    exit;
}

// Get form data
$fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
$address = mysqli_real_escape_string($conn, trim($_POST['address']));
$phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, trim($_POST['email'])) : '';
$uid = (int)$_SESSION['user_id'];

// Validate phone number (must be exactly 10 digits)
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    flash_set('error', 'Please enter a valid 10-digit phone number.');
    redirect('checkout.php');
    exit;
}

// Validate fullname
if (strlen($fullname) < 3) {
    flash_set('error', 'Please enter a valid full name (minimum 3 characters).');
    redirect('checkout.php');
    exit;
}

// Validate address
if (strlen($address) < 10) {
    flash_set('error', 'Please enter a complete delivery address (minimum 10 characters).');
    redirect('checkout.php');
    exit;
}

// Check if this is a Buy Now purchase (from checkout.php)
$is_buy_now = isset($_SESSION['buy_now_product_id']) && isset($_SESSION['buy_now_qty']);

if($is_buy_now){
    // Buy Now purchase - get product from session
    $product_id = (int)$_SESSION['buy_now_product_id'];
    $qty = (int)$_SESSION['buy_now_qty'];
    
    // Fetch product
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id={$product_id} LIMIT 1");
    if(!$res || mysqli_num_rows($res) === 0){
        flash_set('error', 'Product not found.');
        redirect('products.php');
        exit;
    }
    
    $product = mysqli_fetch_assoc($res);
    $cart = [$product_id => $qty];
    $items = [$product];
    $total = $product['price'] * $qty;
    
    // buy_now session data will be cleared after successful payment init
    // to prevent losing state if payment init fails
    
} else {
    // Regular cart checkout
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if (empty($cart)) {
        flash_set('error', 'Cart is empty.');
        redirect('cart.php');
        exit;
    }
    
    // Fetch products and calculate total
    $ids = array_map('intval', array_keys($cart));
    $res = mysqli_query($conn, 'SELECT * FROM products WHERE id IN (' . implode(',', $ids) . ')');
    
    if (!$res) {
        die("Query failed: " . mysqli_error($conn));
    }
    
    $total = 0;
    $items = [];
    
    while ($p = mysqli_fetch_assoc($res)) {
        $items[] = $p;
        $qty = (int)$cart[$p['id']];
        $total += $p['price'] * $qty;
    }
}

// Create order with status 'pending'
$insert_order = mysqli_query($conn, 
    "INSERT INTO orders (user_id, fullname, address, phone, email, total, status, created_at) 
     VALUES ({$uid}, '{$fullname}', '{$address}', '{$phone}', '{$email}', {$total}, 'pending', NOW())"
);

if (!$insert_order) {
    die("Order creation failed: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

// Save order items
foreach ($items as $p) {
    $pid = (int)$p['id'];
    $qty = (int)$cart[$pid];
    $price = $p['price'];
    
    mysqli_query($conn, 
        "INSERT INTO order_items (order_id, product_id, qty, price) 
         VALUES ({$order_id}, {$pid}, {$qty}, {$price})"
    );
}

// Prepare Khalti payment initiation
$return_url = BASE_URL . 'khalti_callback.php';
$website_url = BASE_URL;
$purchase_order_id = 'ORDER_' . $order_id;
$purchase_order_name = 'Bookstore Order #' . $order_id;

// Amount must be in paisa (1 rupee = 100 paisa)
$amount_in_paisa = (int)($total * 100);

// Prepare customer info
$customer_info = [
    'name' => $fullname,
    'phone' => $phone
];

if (!empty($email)) {
    $customer_info['email'] = $email;
}

// Prepare Khalti API payload
$khalti_payload = [
    'return_url' => $return_url,
    'website_url' => $website_url,
    'amount' => $amount_in_paisa,
    'purchase_order_id' => $purchase_order_id,
    'purchase_order_name' => $purchase_order_name,
    'customer_info' => $customer_info
];

// Check if in DEMO mode
if (defined('KHALTI_DEMO_MODE') && KHALTI_DEMO_MODE === true) {
    // DEMO MODE - Redirect to demo Khalti interface
    $_SESSION['demo_order_id'] = $order_id;
    $_SESSION['demo_order_total'] = $total;
    header("Location: " . BASE_URL . "khalti_demo.php");
    exit;
}

// Initiate Khalti payment
$khalti_response = khalti_api_request('epayment/initiate/', $khalti_payload, 'POST');

// Debug: Log the response for troubleshooting
error_log("Khalti Response: " . print_r($khalti_response, true));

if ($khalti_response['success'] && isset($khalti_response['data']['pidx'])) {
    $pidx = $khalti_response['data']['pidx'];
    $payment_url = $khalti_response['data']['payment_url'];
    
    // Store pidx in database for verification later
    mysqli_query($conn, 
        "UPDATE orders SET khalti_pidx = '{$pidx}' WHERE id = {$order_id}"
    );
    
    // Store order_id in session for callback
    $_SESSION['pending_order_id'] = $order_id;
    
    // Clear Buy Now session data now that we are successfully redirecting
    if(isset($_SESSION['buy_now_product_id'])) unset($_SESSION['buy_now_product_id']);
    if(isset($_SESSION['buy_now_qty'])) unset($_SESSION['buy_now_qty']);
    
    // Redirect to Khalti payment page
    header("Location: " . $payment_url);
    exit;
    
} else {
    // Payment initiation failed - show detailed error
    $error_details = '';
    
    if (isset($khalti_response['data'])) {
        // Get specific error messages
        $error_data = $khalti_response['data'];
        
        if (is_array($error_data)) {
            foreach ($error_data as $key => $value) {
                if (is_array($value)) {
                    $error_details .= $key . ': ' . implode(', ', $value) . '; ';
                } else {
                    $error_details .= $key . ': ' . $value . '; ';
                }
            }
        } else {
            $error_details = json_encode($error_data);
        }
    }
    
    $error_msg = 'Payment initialization failed. ';
    if (!empty($error_details)) {
        $error_msg .= 'Details: ' . $error_details;
    }
    
    // Log for debugging
    error_log("Khalti Init Failed - Order ID: {$order_id}, Error: {$error_msg}");
    error_log("Khalti Payload: " . json_encode($khalti_payload));
    
    // Update order status to failed
    mysqli_query($conn, "UPDATE orders SET status = 'failed' WHERE id = {$order_id}");
    
    flash_set('error', $error_msg);
    redirect('checkout.php');
    exit;
}