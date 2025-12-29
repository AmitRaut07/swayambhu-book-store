<?php
// order_success.php - Order confirmation page
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'functions.php';

$conn = db_connect();
require_login();

$page_title = 'Order Success';

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$uid = (int)$_SESSION['user_id'];

if ($order_id === 0) {
    redirect('index.php');
    exit;
}

// Fetch order details
$order_query = mysqli_query($conn, 
    "SELECT * FROM orders 
     WHERE id = {$order_id} AND user_id = {$uid} 
     LIMIT 1"
);

if (!$order_query || mysqli_num_rows($order_query) === 0) {
    flash_set('error', 'Order not found.');
    redirect('profile.php');
    exit;
}

$order = mysqli_fetch_assoc($order_query);

// Fetch order items
$items_query = mysqli_query($conn, 
    "SELECT oi.*, p.title, p.author 
     FROM order_items oi 
     LEFT JOIN products p ON oi.product_id = p.id 
     WHERE oi.order_id = {$order_id}"
);

include 'includes/header.php';
?>

<style>
.success-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.success-header {
    text-align: center;
    margin-bottom: 40px;
}

.success-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: scaleIn 0.5s ease;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
    }
    to {
        transform: scale(1);
    }
}

.success-icon svg {
    width: 50px;
    height: 50px;
    stroke: white;
    stroke-width: 3;
    fill: none;
}

.success-header h1 {
    font-size: 2rem;
    color: #10b981;
    margin-bottom: 10px;
}

.success-header p {
    font-size: 1.1rem;
    color: #6c757d;
}

.order-details {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.order-details h2 {
    font-size: 1.5rem;
    color: #1a1a1a;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.order-info-item {
    display: flex;
    flex-direction: column;
}

.order-info-label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.order-info-value {
    font-size: 1rem;
    color: #1a1a1a;
    font-weight: 600;
}

.order-items h3 {
    font-size: 1.2rem;
    color: #1a1a1a;
    margin-bottom: 15px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #e5e7eb;
}

.order-item:last-child {
    border-bottom: none;
}

.item-details h4 {
    font-size: 1rem;
    color: #1a1a1a;
    margin-bottom: 4px;
}

.item-meta {
    font-size: 0.9rem;
    color: #6c757d;
}

.item-price {
    text-align: right;
}

.item-price .price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2563eb;
}

.item-price .qty {
    font-size: 0.85rem;
    color: #6c757d;
}

.order-total {
    display: flex;
    justify-content: space-between;
    padding-top: 20px;
    margin-top: 20px;
    border-top: 2px solid #e5e7eb;
    font-size: 1.3rem;
    font-weight: 700;
}

.order-total .label {
    color: #1a1a1a;
}

.order-total .amount {
    color: #10b981;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #fff;
    color: #667eea;
    border: 2px solid #667eea;
}

.btn-secondary:hover {
    background: #f0f4ff;
}

@media (max-width: 768px) {
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<div class="success-container">
    <div class="success-header">
        <div class="success-icon">
            <svg viewBox="0 0 52 52">
                <polyline points="14 27 22 35 38 17"/>
            </svg>
        </div>
        <h1>Order Placed Successfully!</h1>
        <p>Thank you for your purchase. Your order has been confirmed.</p>
        <?php if($order['status'] === 'pending'): ?>
        <div style="background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 8px; margin-top: 20px; text-align: left;">
            <strong>⏳ Order Status: Pending Admin Approval</strong>
            <p style="margin: 10px 0 0 0; font-size: 0.95rem;">Your payment has been received. Our team will review and approve your order shortly. You'll be able to track your order status in your profile.</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="order-details">
        <h2>Order Details</h2>
        
        <div class="order-info-grid">
            <div class="order-info-item">
                <span class="order-info-label">Order ID</span>
                <span class="order-info-value">#<?php echo $order['id']; ?></span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Order Date</span>
                <span class="order-info-value"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Status</span>
                <span class="order-info-value" style="color: #10b981; text-transform: uppercase;">
                    <?php echo e($order['status']); ?>
                </span>
            </div>
            <?php if (!empty($order['khalti_transaction_id'])): ?>
            <div class="order-info-item">
                <span class="order-info-label">Transaction ID</span>
                <span class="order-info-value"><?php echo e($order['khalti_transaction_id']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="order-info-grid">
            <div class="order-info-item">
                <span class="order-info-label">Delivery Address</span>
                <span class="order-info-value"><?php echo e($order['address']); ?></span>
            </div>
            <div class="order-info-item">
                <span class="order-info-label">Contact Number</span>
                <span class="order-info-value"><?php echo e($order['phone']); ?></span>
            </div>
        </div>

        <div class="order-items">
            <h3>Order Items</h3>
            <?php while ($item = mysqli_fetch_assoc($items_query)): ?>
                <div class="order-item">
                    <div class="item-details">
                        <h4><?php echo e($item['title']); ?></h4>
                        <?php if (!empty($item['author'])): ?>
                            <span class="item-meta">By <?php echo e($item['author']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="item-price">
                        <div class="price">Rs. <?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
                        <div class="qty">Qty: <?php echo $item['qty']; ?></div>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="order-total">
                <span class="label">Total Paid:</span>
                <span class="amount">Rs. <?php echo number_format($order['total'], 2); ?></span>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">Continue Shopping</a>
        <a href="<?php echo BASE_URL; ?>profile.php" class="btn btn-secondary">View All Orders</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>