<?php
// admin/order_details.php - View detailed order information
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$conn = db_connect();

// Check if user is admin
if (!is_logged_in() || !is_admin()) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($order_id <= 0){
    flash_set('error', 'Invalid order ID');
    header("Location: " . BASE_URL . "admin/orders.php");
    exit;
}

// Fetch order details
$order_query = "SELECT o.*, u.username, u.email as user_email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = {$order_id} LIMIT 1";
$order_res = mysqli_query($conn, $order_query);

if(!$order_res || mysqli_num_rows($order_res) === 0){
    flash_set('error', 'Order not found');
    header("Location: " . BASE_URL . "admin/orders.php");
    exit;
}

$order = mysqli_fetch_assoc($order_res);

// Fetch order items
$items_query = "SELECT oi.*, p.title, p.author, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = {$order_id}";
$items_res = mysqli_query($conn, $items_query);

$page_title = "Order #" . $order_id . " Details";

include __DIR__ . '/../includes/header.php';
?>

<style>
.order-details-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #2563eb;
    text-decoration: none;
    font-weight: 600;
}

.back-link:hover {
    text-decoration: underline;
}

.order-header {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.order-header h1 {
    font-size: 2rem;
    margin-bottom: 10px;
}

.order-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.meta-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.meta-label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.meta-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1a1a1a;
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-delivered { background: #cfe2ff; color: #084298; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.info-section {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.info-section h2 {
    font-size: 1.3rem;
    margin-bottom: 20px;
    color: #1a1a1a;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.info-item {
    padding: 10px 0;
}

.info-item strong {
    display: block;
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.order-items-table {
    width: 100%;
    border-collapse: collapse;
}

.order-items-table thead {
    background: #f8f9fa;
}

.order-items-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #e5e7eb;
}

.order-items-table td {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.product-image {
    width: 60px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
}

.product-details {
    flex: 1;
}

.product-title {
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 4px;
}

.product-author {
    font-size: 0.9rem;
    color: #6c757d;
    font-style: italic;
}

.order-total {
    text-align: right;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 20px;
}

.order-total h3 {
    font-size: 1.5rem;
    color: #2563eb;
}

.delivery-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    margin-bottom: 30px;
}

.delivery-section h2 {
    color: #fff;
    border-bottom-color: rgba(255,255,255,0.3);
}

.delivery-section .info-item strong {
    color: rgba(255,255,255,0.8);
}

.delivery-section .info-item {
    color: #fff;
    font-size: 1.05rem;
}

.print-btn {
    background: #fff;
    color: #667eea;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 15px;
    transition: all 0.3s ease;
}

.print-btn:hover {
    background: #f0f4ff;
    transform: translateY(-2px);
}

@media print {
    .back-link, .order-header, .info-section:not(.delivery-section), .print-btn {
        display: none;
    }
    .delivery-section {
        background: #fff;
        color: #000;
        border: 2px solid #000;
    }
    .delivery-section h2 {
        color: #000;
    }
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="order-details-container">
    <a href="<?php echo BASE_URL; ?>admin/orders.php" class="back-link">← Back to Orders</a>
    
    <div class="order-header">
        <h1>Order #<?php echo $order['id']; ?></h1>
        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
            <?php echo ucfirst($order['status']); ?>
        </span>
        
        <div class="order-meta">
            <div class="meta-item">
                <div class="meta-label">Order Date</div>
                <div class="meta-value"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Order Total</div>
                <div class="meta-value">Rs. <?php echo number_format($order['total'], 2); ?></div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Payment Status</div>
                <div class="meta-value"><?php echo !empty($order['khalti_pidx']) ? 'Paid' : 'Pending'; ?></div>
            </div>
        </div>
    </div>
    
    
    <div class="info-section delivery-section">
        <h2>📦 Delivery Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Customer Name</strong>
                <?php echo e($order['fullname']); ?>
            </div>
            <div class="info-item">
                <strong>Contact Phone</strong>
                <a href="tel:<?php echo e($order['phone']); ?>" style="color: #fff; text-decoration: underline;"><?php echo e($order['phone']); ?></a>
            </div>
            <div class="info-item">
                <strong>Email</strong>
                <?php if(!empty($order['email']) || !empty($order['user_email'])): ?>
                    <a href="mailto:<?php echo e($order['email'] ?: $order['user_email']); ?>" style="color: #fff; text-decoration: underline;"><?php echo e($order['email'] ?: $order['user_email']); ?></a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </div>
            <div class="info-item">
                <strong>Username</strong>
                <?php echo e($order['username'] ?? 'Guest'); ?>
            </div>
            <div class="info-item" style="grid-column: 1 / -1;">
                <strong>📍 Delivery Address</strong>
                <div style="font-size: 1.1rem; margin-top: 8px; line-height: 1.6;">
                    <?php echo nl2br(e($order['address'])); ?>
                </div>
            </div>
        </div>
        <button onclick="window.print()" class="print-btn">🖨️ Print Delivery Label</button>
    </div>
    
    <div class="info-section">
        <h2>Customer Account</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Username</strong>
                <?php echo e($order['username'] ?? 'N/A'); ?>
            </div>
            <div class="info-item">
                <strong>User Email</strong>
                <?php echo e($order['user_email'] ?: 'N/A'); ?>
            </div>
        </div>
    </div>
    
    <div class="info-section">
        <h2>Order Items</h2>
        <?php if($items_res && mysqli_num_rows($items_res) > 0): ?>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = mysqli_fetch_assoc($items_res)): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <?php 
                                    $item_img = !empty($item['image']) ? UPLOADS_URL . e($item['image']) : BASE_URL . 'placeholder.png';
                                    ?>
                                    <img src="<?php echo $item_img; ?>" alt="<?php echo e($item['title']); ?>" class="product-image">
                                    <div class="product-details">
                                        <div class="product-title"><?php echo e($item['title']); ?></div>
                                        <?php if(!empty($item['author'])): ?>
                                            <div class="product-author">by <?php echo e($item['author']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo (int)$item['qty']; ?></td>
                            <td><strong>Rs. <?php echo number_format($item['price'] * $item['qty'], 2); ?></strong></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="order-total">
                <h3>Total: Rs. <?php echo number_format($order['total'], 2); ?></h3>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #6c757d; padding: 40px;">No items found for this order.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
