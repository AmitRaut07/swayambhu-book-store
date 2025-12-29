<?php
// khalti_callback.php - Handle Khalti payment callback
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'functions.php';
require_once 'khalti_config.php';

$conn = db_connect();

// Get callback parameters
$pidx = isset($_GET['pidx']) ? mysqli_real_escape_string($conn, $_GET['pidx']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$transaction_id = isset($_GET['transaction_id']) ? $_GET['transaction_id'] : '';
$amount = isset($_GET['amount']) ? (int)$_GET['amount'] : 0;
$purchase_order_id = isset($_GET['purchase_order_id']) ? $_GET['purchase_order_id'] : '';

// Validate pidx
if (empty($pidx)) {
    flash_set('error', 'Invalid payment response.');
    redirect('cart.php');
    exit;
}

// Find order by pidx
$result = mysqli_query($conn, "SELECT * FROM orders WHERE khalti_pidx = '{$pidx}' LIMIT 1");

if (!$result || mysqli_num_rows($result) === 0) {
    flash_set('error', 'Order not found.');
    redirect('cart.php');
    exit;
}

$order = mysqli_fetch_assoc($result);
$order_id = $order['id'];

// IMPORTANT: Verify payment with Khalti lookup API (mandatory for security)
$lookup_payload = ['pidx' => $pidx];
$verification = khalti_api_request('epayment/lookup/', $lookup_payload, 'POST');

if (!$verification['success']) {
    // Verification API call failed
    flash_set('error', 'Payment verification failed. Please contact support.');
    redirect('cart.php');
    exit;
}

$verified_data = $verification['data'];
$verified_status = isset($verified_data['status']) ? $verified_data['status'] : '';

// Process based on verified status
if ($verified_status === 'Completed') {
    // Payment successful
    $transaction_id = isset($verified_data['transaction_id']) ? $verified_data['transaction_id'] : '';
    $verified_amount = isset($verified_data['total_amount']) ? (int)$verified_data['total_amount'] : 0;
    
    // Convert paisa to rupees for comparison
    $order_total_paisa = (int)($order['total'] * 100);
    
    // Verify amount matches
    if ($verified_amount != $order_total_paisa) {
        mysqli_query($conn, "UPDATE orders SET status = 'failed' WHERE id = {$order_id}");
        flash_set('error', 'Payment amount mismatch. Please contact support.');
        redirect('cart.php');
        exit;
    }
    
    // Update order status to PENDING (awaiting admin approval)
    // Admin will approve and mark as 'completed' after verification
    mysqli_query($conn, 
        "UPDATE orders 
         SET status = 'pending', 
             khalti_transaction_id = '{$transaction_id}',
             paid_at = NOW()
         WHERE id = {$order_id}"
    );
    
    // DO NOT reduce stock here - stock will be reduced when admin approves the order
    // This prevents stock issues if orders are cancelled or refunded
    
    // Clear cart
    unset($_SESSION['cart']);
    unset($_SESSION['pending_order_id']);
    
    flash_set('success', 'Payment successful! Your order is pending admin approval. Order ID: ' . $order_id);
    redirect('order_success.php?order_id=' . $order_id);
    exit;
    
} elseif ($verified_status === 'Pending') {
    // Payment is pending - do not provide service yet
    mysqli_query($conn, "UPDATE orders SET status = 'pending' WHERE id = {$order_id}");
    flash_set('error', 'Payment is pending. Please wait or contact support.');
    redirect('profile.php');
    exit;
    
} elseif ($verified_status === 'User canceled') {
    // User canceled the payment
    mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = {$order_id}");
    flash_set('error', 'Payment was cancelled.');
    redirect('checkout.php');
    exit;
    
} elseif ($verified_status === 'Expired') {
    // Payment link expired
    mysqli_query($conn, "UPDATE orders SET status = 'expired' WHERE id = {$order_id}");
    flash_set('error', 'Payment link expired. Please try again.');
    redirect('checkout.php');
    exit;
    
} else {
    // Unknown status - mark as failed
    mysqli_query($conn, "UPDATE orders SET status = 'failed' WHERE id = {$order_id}");
    flash_set('error', 'Payment failed. Status: ' . $verified_status);
    redirect('cart.php');
    exit;
}