<?php
// admin/dashboard.php - Admin Dashboard with Analytics
require_once '../config.php';
require_once '../functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Require admin login
if (!is_logged_in() || !is_admin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$conn = db_connect();
$page_title = 'Admin Dashboard';

// Get total revenue
$revenue_query = "SELECT SUM(total) as total_revenue FROM orders WHERE status IN ('completed', 'delivered')";
$revenue_res = mysqli_query($conn, $revenue_query);
$total_revenue = 0;
if($revenue_res && $row = mysqli_fetch_assoc($revenue_res)){
    $total_revenue = $row['total_revenue'] ? (float)$row['total_revenue'] : 0;
}

// Get monthly revenue (current month)
$monthly_query = "SELECT SUM(total) as monthly_revenue FROM orders 
                  WHERE status IN ('completed', 'delivered') 
                  AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                  AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$monthly_res = mysqli_query($conn, $monthly_query);
$monthly_revenue = 0;
if($monthly_res && $row = mysqli_fetch_assoc($monthly_res)){
    $monthly_revenue = $row['monthly_revenue'] ? (float)$row['monthly_revenue'] : 0;
}

// Get today's revenue
$today_query = "SELECT SUM(total) as today_revenue FROM orders 
                WHERE status IN ('completed', 'delivered') 
                AND DATE(created_at) = CURDATE()";
$today_res = mysqli_query($conn, $today_query);
$today_revenue = 0;
if($today_res && $row = mysqli_fetch_assoc($today_res)){
    $today_revenue = $row['today_revenue'] ? (float)$row['today_revenue'] : 0;
}

// Order statistics
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='pending'"))['count'];
$completed_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status IN ('completed', 'delivered')"))['count'];
$cancelled_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='cancelled'"))['count'];

// Product statistics
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE stock < 10 AND stock > 0"))['count'];
$out_of_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE stock = 0"))['count'];

// User statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE is_admin=0 OR is_admin IS NULL"))['count'];
$new_users_month = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())"))['count'];

// Recent orders
$recent_orders_query = "SELECT o.*, u.username FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC LIMIT 10";
$recent_orders = mysqli_query($conn, $recent_orders_query);

// Top selling products
$top_products_query = "SELECT p.title, p.price, SUM(oi.qty) as total_sold, SUM(oi.qty * oi.price) as revenue
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id
                       JOIN orders o ON oi.order_id = o.id
                       WHERE o.status IN ('completed', 'delivered')
                       GROUP BY oi.product_id
                       ORDER BY total_sold DESC
                       LIMIT 5";
$top_products = mysqli_query($conn, $top_products_query);

include '../includes/header.php';
?>

<style>
.admin-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.dashboard-header {
    margin-bottom: 30px;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    color: #1a1a1a;
    margin-bottom: 10px;
}

.dashboard-header p {
    color: #6c757d;
    font-size: 1.1rem;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.revenue {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card.orders {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card.products {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-card.users {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
    opacity: 0.9;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.95rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-sublabel {
    font-size: 0.85rem;
    opacity: 0.8;
    margin-top: 8px;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.dashboard-section {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.dashboard-section h2 {
    font-size: 1.5rem;
    margin-bottom: 20px;
    color: #1a1a1a;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}

/* Tables */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8f9fa;
}

.data-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-delivered { background: #cfe2ff; color: #084298; }
.status-cancelled { background: #f8d7da; color: #842029; }

.quick-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.action-btn {
    flex: 1;
    padding: 15px 20px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
}

.action-btn.primary {
    background: #667eea;
    color: #fff;
}

.action-btn.primary:hover {
    background: #5568d3;
    transform: translateY(-2px);
}

.action-btn.secondary {
    background: #f8f9fa;
    color: #495057;
    border: 2px solid #e5e7eb;
}

.action-btn.secondary:hover {
    background: #e9ecef;
}

@media (max-width: 992px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-direction: column;
    }
}
</style>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>📊 Admin Dashboard</h1>
        <p>Welcome back! Here's what's happening with your store today.</p>
    </div>

    <!-- Revenue Stats -->
    <div class="stats-grid">
        <div class="stat-card revenue">
            <div class="stat-icon">💰</div>
            <div class="stat-value">Rs. <?php echo number_format($total_revenue, 2); ?></div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-sublabel">All time earnings</div>
        </div>

        <div class="stat-card revenue">
            <div class="stat-icon">📅</div>
            <div class="stat-value">Rs. <?php echo number_format($monthly_revenue, 2); ?></div>
            <div class="stat-label">This Month</div>
            <div class="stat-sublabel"><?php echo date('F Y'); ?></div>
        </div>

        <div class="stat-card revenue">
            <div class="stat-icon">📈</div>
            <div class="stat-value">Rs. <?php echo number_format($today_revenue, 2); ?></div>
            <div class="stat-label">Today's Revenue</div>
            <div class="stat-sublabel"><?php echo date('M d, Y'); ?></div>
        </div>

        <div class="stat-card orders">
            <div class="stat-icon">🛒</div>
            <div class="stat-value"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-sublabel"><?php echo $pending_orders; ?> pending</div>
        </div>

        <div class="stat-card products">
            <div class="stat-icon">📚</div>
            <div class="stat-value"><?php echo $total_products; ?></div>
            <div class="stat-label">Products</div>
            <div class="stat-sublabel"><?php echo $low_stock; ?> low stock</div>
        </div>

        <div class="stat-card users">
            <div class="stat-icon">👥</div>
            <div class="stat-value"><?php echo $total_users; ?></div>
            <div class="stat-label">Total Users</div>
            <div class="stat-sublabel"><?php echo $new_users_month; ?> new this month</div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Orders -->
        <div class="dashboard-section">
            <h2>Recent Orders</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($recent_orders) > 0): ?>
                        <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo e($order['username']); ?></td>
                                <td>Rs. <?php echo number_format($order['total'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #6c757d;">No orders yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Top Selling Products -->
        <div class="dashboard-section">
            <h2>Top Selling Products</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($top_products && mysqli_num_rows($top_products) > 0): ?>
                        <?php while($product = mysqli_fetch_assoc($top_products)): ?>
                            <tr>
                                <td><?php echo e($product['title']); ?></td>
                                <td><?php echo $product['total_sold']; ?> units</td>
                                <td>Rs. <?php echo number_format($product['revenue'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center; color: #6c757d;">No sales data yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="<?php echo BASE_URL; ?>admin/pending-orders.php" class="action-btn primary">⏳ Pending Orders (<?php echo $pending_orders; ?>)</a>
        <a href="<?php echo BASE_URL; ?>admin/products.php" class="action-btn primary">📦 Manage Products</a>
        <a href="<?php echo BASE_URL; ?>admin/orders.php" class="action-btn secondary">🛒 All Orders</a>
        <a href="<?php echo BASE_URL; ?>admin/users.php" class="action-btn secondary">👥 Users</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
