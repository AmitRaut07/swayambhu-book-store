<?php
// admin/pending-orders.php - Manage Pending Orders
require_once '../config.php';
require_once '../functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Require admin login
if (!is_logged_in() || !is_admin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$conn = db_connect();
$page_title = 'Pending Orders';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE orders SET status = '{$new_status}' WHERE id = {$order_id}";
    if(mysqli_query($conn, $update_query)){
        flash_set('success', "Order #{$order_id} status updated to {$new_status}");
    } else {
        flash_set('error', 'Failed to update order status');
    }
    redirect('admin/pending-orders.php');
}

// Get pending orders with user details and items
$pending_query = "SELECT o.*, u.username, u.email 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.status = 'pending' 
                  ORDER BY o.created_at DESC";
$pending_orders = mysqli_query($conn, $pending_query);

include '../includes/header.php';
?>

<style>
.pending-orders-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 2rem;
    color: #1a1a1a;
}

.back-btn {
    padding: 10px 20px;
    background: #6c757d;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: background 0.3s ease;
}

.back-btn:hover {
    background: #5a6268;
}

.pending-count {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: #fff;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 30px;
}

.pending-count h2 {
    font-size: 3rem;
    margin-bottom: 5px;
}

.pending-count p {
    font-size: 1.1rem;
    opacity: 0.9;
}

.order-card {
    background: #fff;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.order-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
}

.order-id {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a1a1a;
}

.order-date {
    color: #6c757d;
    font-size: 0.9rem;
}

.customer-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #1a1a1a;
}

.order-items {
    margin-bottom: 20px;
}

.order-items h4 {
    font-size: 1rem;
    margin-bottom: 10px;
    color: #495057;
}

.items-list {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.item-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.item-row:last-child {
    border-bottom: none;
}

.order-total {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
    text-align: right;
    margin-bottom: 20px;
}

.status-update-form {
    display: flex;
    gap: 15px;
    align-items: center;
    padding: 15px;
    background: #fff3cd;
    border-radius: 8px;
    border: 1px solid #ffc107;
}

.status-update-form label {
    font-weight: 600;
    color: #856404;
}

.status-update-form select {
    flex: 1;
    padding: 10px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    background: #fff;
}

.status-update-form button {
    padding: 10px 25px;
    background: #28a745;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
}

.status-update-form button:hover {
    background: #218838;
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.no-orders h3 {
    font-size: 1.5rem;
    color: #6c757d;
    margin-bottom: 10px;
}

.no-orders p {
    color: #adb5bd;
}
</style>

<div class="pending-orders-page">
    <div class="page-header">
        <h1>⏳ Pending Orders</h1>
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>

    <?php $pending_count = mysqli_num_rows($pending_orders); ?>
    
    <div class="pending-count">
        <h2><?php echo $pending_count; ?></h2>
        <p>Orders Awaiting Processing</p>
    </div>

    <?php if($pending_count > 0): ?>
        <?php while($order = mysqli_fetch_assoc($pending_orders)): ?>
            <?php
            // Get order items
            $order_id = (int)$order['id'];
            $items_query = "SELECT oi.*, p.title FROM order_items oi 
                           JOIN products p ON oi.product_id = p.id 
                           WHERE oi.order_id = {$order_id}";
            $items_res = mysqli_query($conn, $items_query);
            ?>
            
            <div class="order-card">
                <div class="order-header">
                    <span class="order-id">Order #<?php echo $order['id']; ?></span>
                    <span class="order-date">📅 <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></span>
                </div>

                <div class="customer-info">
                    <div class="info-item">
                        <span class="info-label">Customer</span>
                        <span class="info-value">👤 <?php echo e($order['username']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value">📧 <?php echo e($order['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Method</span>
                        <span class="info-value">💳 <?php echo e($order['payment_method'] ?? 'N/A'); ?></span>
                    </div>
                </div>

                <?php if($items_res && mysqli_num_rows($items_res) > 0): ?>
                    <div class="order-items">
                        <h4>📦 Order Items:</h4>
                        <div class="items-list">
                            <?php while($item = mysqli_fetch_assoc($items_res)): ?>
                                <div class="item-row">
                                    <span><?php echo e($item['title']); ?> × <?php echo $item['qty']; ?></span>
                                    <span>Rs. <?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="order-total">
                    Total: Rs. <?php echo number_format($order['total'], 2); ?>
                </div>

                <form method="post" class="status-update-form">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <label>Update Status:</label>
                    <select name="status" required>
                        <option value="pending" selected>Pending</option>
                        <option value="completed">Completed</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="submit" name="update_status">✓ Update</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-orders">
            <h3>🎉 All Caught Up!</h3>
            <p>No pending orders at the moment</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
