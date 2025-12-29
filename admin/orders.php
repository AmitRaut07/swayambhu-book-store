<?php
// admin/orders.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$conn = db_connect();

// Check if user is admin
if (!is_logged_in()) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];
$res_user = mysqli_query($conn, "SELECT email FROM users WHERE id={$uid} LIMIT 1");
$row_user = mysqli_fetch_assoc($res_user);

if (!$row_user || $row_user['email'] !== 'admin@example.com') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$page_title = "Orders Management";

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $old_status = mysqli_real_escape_string($conn, $_POST['old_status']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Update order status
    $update = mysqli_query($conn, "UPDATE orders SET status='{$new_status}' WHERE id={$order_id}");
    
    if ($update) {
        // If changing TO completed/delivered FROM any other status, decrease stock
        if(in_array($new_status, ['completed', 'delivered']) && !in_array($old_status, ['completed', 'delivered'])){
            // Decrease stock for all items in this order
            $items_result = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = {$order_id}");
            while ($item = mysqli_fetch_assoc($items_result)) {
                $product_id = (int)$item['product_id'];
                $qty = (int)$item['qty'];
                mysqli_query($conn, "UPDATE products SET stock = stock - {$qty} WHERE id = {$product_id}");
            }
            flash_set('success', 'Order approved and stock updated successfully!');
        } else {
            flash_set('success', 'Order status updated successfully!');
        }
    } else {
        flash_set('error', 'Failed to update order status.');
    }
    header("Location: " . BASE_URL . "admin/orders.php");
    exit;
}

// Fetch all orders with user details and calculate totals
$query = "
    SELECT o.*, u.username, u.email as user_email,
    o.total as order_total
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
";
$orders_result = mysqli_query($conn, $query);

if (!$orders_result) {
    die("Query failed: " . mysqli_error($conn));
}

include __DIR__ . '/../includes/header.php';
?>

<style>
.orders-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

.orders-header {
    margin-bottom: 30px;
}

.orders-header h2 {
    font-size: 2rem;
    color: #1a1a1a;
    margin-bottom: 10px;
}

.orders-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    text-align: center;
}

.stat-card h3 {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-card .stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2563eb;
}

.orders-table-container {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table thead {
    background: #f8f9fa;
}

.orders-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #1a1a1a;
    border-bottom: 2px solid #e5e7eb;
}

.orders-table td {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.orders-table tbody tr:hover {
    background: #f8f9fa;
}

.order-id {
    font-weight: 600;
    color: #2563eb;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-processing {
    background: #dbeafe;
    color: #1e40af;
}

.status-completed {
    background: #d1fae5;
    color: #065f46;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.status-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.status-select {
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.9rem;
}

.btn-update {
    padding: 8px 16px;
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-update:hover {
    background: #1d4ed8;
}

.btn-view {
    padding: 6px 12px;
    background: #6c757d;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.btn-view:hover {
    background: #5a6268;
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

@media (max-width: 768px) {
    .orders-table-container {
        overflow-x: auto;
    }
    
    .orders-table {
        min-width: 800px;
    }
    
    .status-form {
        flex-direction: column;
    }
}
</style>

<div class="orders-container">
    <div class="orders-header">
        <h2>Orders Management</h2>
        <p>Manage and track all customer orders</p>
    </div>

    <?php
    // Calculate statistics
    $total_orders = mysqli_num_rows($orders_result);
    $pending_count = 0;
    $completed_count = 0;
    $total_revenue = 0;

    mysqli_data_seek($orders_result, 0); // Reset pointer
    while ($row = mysqli_fetch_assoc($orders_result)) {
        if ($row['status'] === 'pending') $pending_count++;
        if ($row['status'] === 'completed') {
            $completed_count++;
            $total_revenue += floatval($row['order_total']);
        }
    }
    mysqli_data_seek($orders_result, 0); // Reset pointer again
    ?>

    <div class="orders-stats">
        <div class="stat-card">
            <h3>Total Orders</h3>
            <div class="stat-number"><?php echo $total_orders; ?></div>
        </div>
        <div class="stat-card">
            <h3>Pending Orders</h3>
            <div class="stat-number"><?php echo $pending_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>Completed Orders</h3>
            <div class="stat-number"><?php echo $completed_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <div class="stat-number">Rs. <?php echo number_format($total_revenue, 2); ?></div>
        </div>
    </div>

    <div class="orders-table-container">
        <?php if ($total_orders > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Update Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                        <tr>
                            <td class="order-id">#<?php echo $order['id']; ?></td>
                            <td>
                                <strong><?php echo e($order['username'] ?? 'Guest'); ?></strong><br>
                                <small><?php echo e($order['user_email'] ?? $order['email']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><strong>Rs. <?php echo number_format(floatval($order['order_total']), 2); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo e($order['status']); ?>">
                                    <?php echo e($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" class="status-form">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="old_status" value="<?php echo $order['status']; ?>">
                                    <select name="status" class="status-select">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update">Update</button>
                                </form>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>admin/order_details.php?id=<?php echo $order['id']; ?>" class="btn-view">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-orders">
                <p>No orders yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>