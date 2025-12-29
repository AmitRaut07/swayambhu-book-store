<?php
// khalti_demo.php - Demo Khalti Payment Interface
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'functions.php';

$conn = db_connect();

// Get demo order details
$order_id = isset($_SESSION['demo_order_id']) ? (int)$_SESSION['demo_order_id'] : 0;
$order_total = isset($_SESSION['demo_order_total']) ? $_SESSION['demo_order_total'] : 0;

if ($order_id === 0) {
    redirect('cart.php');
    exit;
}

// Fetch order
$order_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = {$order_id} LIMIT 1");
$order = mysqli_fetch_assoc($order_query);

// Handle demo payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['demo_payment'])) {
    $action = $_POST['demo_payment'];
    
    if ($action === 'success') {
        // Simulate successful payment - order stays PENDING for admin approval
        $demo_transaction_id = 'DEMO_TXN_' . time() . rand(1000, 9999);
        
        mysqli_query($conn, 
            "UPDATE orders 
             SET status = 'pending', 
                 khalti_transaction_id = '{$demo_transaction_id}',
                 paid_at = NOW()
             WHERE id = {$order_id}"
        );
        
        // DO NOT reduce stock - admin will approve and stock will decrease then
        
        unset($_SESSION['cart']);
        unset($_SESSION['demo_order_id']);
        unset($_SESSION['demo_order_total']);
        
        flash_set('success', 'Demo payment successful! Your order is pending admin approval.');
        redirect('order_success.php?order_id=' . $order_id);
        exit;
        
    } else {
        // Simulate cancelled payment
        mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = {$order_id}");
        
        unset($_SESSION['demo_order_id']);
        unset($_SESSION['demo_order_total']);
        
        flash_set('error', 'Payment was cancelled.');
        redirect('checkout.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khalti Payment - Demo Mode</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #5C2D91 0%, #894FC4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .demo-banner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #ff9800;
            color: white;
            text-align: center;
            padding: 10px;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .khalti-container {
            background: white;
            border-radius: 16px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            margin-top: 50px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .khalti-header {
            background: linear-gradient(135deg, #5C2D91 0%, #894FC4 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }

        .khalti-logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            letter-spacing: 2px;
        }

        .khalti-tagline {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .payment-info {
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e5e7eb;
        }

        .merchant-name {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .merchant-value {
            font-size: 1.1rem;
            color: #1a1a1a;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .amount-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .amount-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .amount-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #5C2D91;
        }

        .payment-methods {
            padding: 30px;
        }

        .payment-methods h3 {
            font-size: 1rem;
            color: #1a1a1a;
            margin-bottom: 20px;
            text-align: center;
        }

        .method-card {
            background: #f8f9fa;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .method-card:hover {
            border-color: #5C2D91;
            background: #f0e6ff;
            transform: translateX(5px);
        }

        .method-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #5C2D91 0%, #894FC4 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .method-info {
            flex: 1;
        }

        .method-name {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .method-desc {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .demo-actions {
            padding: 30px;
            background: #f8f9fa;
            border-top: 1px solid #e5e7eb;
        }

        .demo-note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-cancel {
            background: white;
            color: #ef4444;
            border: 2px solid #ef4444;
        }

        .btn-cancel:hover {
            background: #fef2f2;
        }

        .security-badge {
            text-align: center;
            padding: 15px;
            color: #6c757d;
            font-size: 0.85rem;
        }

        .security-badge svg {
            width: 16px;
            height: 16px;
            vertical-align: middle;
            margin-right: 5px;
        }

        @media (max-width: 480px) {
            .amount-value {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="demo-banner">
        🧪 DEMO MODE - This is a simulated Khalti interface for testing
    </div>

    <div class="khalti-container">
        <div class="khalti-header">
            <div class="khalti-logo">KHALTI</div>
            <div class="khalti-tagline">Digital Payment Gateway</div>
        </div>

        <div class="payment-info">
            <div class="merchant-name">Merchant</div>
            <div class="merchant-value"><?php echo e(SITE_TITLE); ?></div>

            <div class="amount-section">
                <div class="amount-label">Amount to Pay</div>
                <div class="amount-value">
                    Rs. <?php echo number_format($order_total, 2); ?>
                </div>
            </div>
        </div>

        <div class="payment-methods">
            <h3>Choose Payment Method</h3>

            <div class="method-card">
                <div class="method-icon">💳</div>
                <div class="method-info">
                    <div class="method-name">Khalti Wallet</div>
                    <div class="method-desc">Pay using your Khalti balance</div>
                </div>
            </div>

            <div class="method-card">
                <div class="method-icon">🏦</div>
                <div class="method-info">
                    <div class="method-name">E-Banking</div>
                    <div class="method-desc">Connect Internet/Mobile Banking</div>
                </div>
            </div>

            <div class="method-card">
                <div class="method-icon">💰</div>
                <div class="method-info">
                    <div class="method-name">SCT/VISA Card</div>
                    <div class="method-desc">Pay with your credit/debit card</div>
                </div>
            </div>

            <div class="method-card">
                <div class="method-icon">📱</div>
                <div class="method-info">
                    <div class="method-name">Mobile Banking</div>
                    <div class="method-desc">Connect Bank, IME Pay, PrabhuPay</div>
                </div>
            </div>
        </div>

        <div class="demo-actions">
            <div class="demo-note">
                <strong>Demo Mode:</strong> Click "Complete Payment" to simulate successful payment, or "Cancel" to simulate cancelled payment.
            </div>

            <form method="post" style="margin: 0;">
                <div class="action-buttons">
                    <button type="submit" name="demo_payment" value="success" class="btn btn-success">
                        ✓ Complete Payment
                    </button>
                    <button type="submit" name="demo_payment" value="cancel" class="btn btn-cancel">
                        ✗ Cancel
                    </button>
                </div>
            </form>
        </div>

        <div class="security-badge">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            Secured by Khalti Payment Gateway
        </div>
    </div>
</body>
</html>