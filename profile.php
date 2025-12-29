<?php
require_once 'config.php';
require_once 'functions.php';
require_once 'includes/security_headers.php'; // Prevent caching

$conn = db_connect(); // Ensure DB connection exists

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_login(); // Ensure user is logged in

$page_title = 'My Dashboard';
$uid = (int)$_SESSION['user_id'];

// Fetch user info
$user_res = mysqli_query($conn, "SELECT username, email FROM users WHERE id={$uid} LIMIT 1");
$user = mysqli_fetch_assoc($user_res);

// Fetch user orders with items
$order_res = mysqli_query($conn, "SELECT * FROM orders WHERE user_id={$uid} ORDER BY id DESC");
$orders = [];

// Check if order_items table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'order_items'");
$has_order_items = ($table_check && mysqli_num_rows($table_check) > 0);

while ($row = mysqli_fetch_assoc($order_res)) {
    $row['items'] = [];
    
    // Only fetch items if table exists
    if($has_order_items){
        $order_id = (int)$row['id'];
        $items_query = "SELECT oi.*, p.title, p.author, p.image 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = {$order_id}";
        $items_res = mysqli_query($conn, $items_query);
        if($items_res){
            while($item = mysqli_fetch_assoc($items_res)){
                $row['items'][] = $item;
            }
        }
    }
    
    $orders[] = $row;
}

include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <style>
    /* Dashboard section */
.dashboard-section {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 20px;
}

/* Profile card */
.profile-card, .orders-card {
    background-color: #fff;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.08);
}

/* Profile text */
.profile-card p {
    font-size: 1rem;
    margin-bottom: 10px;
}

/* Order card */
.order-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
}

.order-id {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a1a;
}

.order-status {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-completed { background: #d1fae5; color: #065f46; }
.status-delivered { background: #cfe2ff; color: #084298; }
.status-cancelled { background: #f8d7da; color: #842029; }

.order-items {
    margin: 15px 0;
}

.order-item {
    display: flex;
    gap: 15px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.order-item-image {
    width: 60px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
}

.order-item-details {
    flex: 1;
}

.order-item-title {
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 4px;
}

.order-item-author {
    font-size: 0.9rem;
    color: #6c757d;
    font-style: italic;
    margin-bottom: 4px;
}

.order-item-qty {
    font-size: 0.9rem;
    color: #495057;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.order-total {
    font-size: 1.2rem;
    font-weight: 700;
    color: #667eea;
}

.order-date {
    font-size: 0.9rem;
    color: #6c757d;
}

.review-btn {
    display: inline-block;
    padding: 6px 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-top: 8px;
}

.review-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.review-btn.reviewed {
    background: #6c757d;
    cursor: not-allowed;
}

.review-btn.reviewed:hover {
    transform: none;
    box-shadow: none;
}

  </style>
</head>
<body>
  <section class="dashboard-section">
  <h2>Welcome, <?php echo e($user['username']); ?></h2>

  <div class="profile-card">
    <h3>Profile</h3>
    <p><strong>Email:</strong> <?php echo e($user['email']); ?></p>
  </div>

  <div class="orders-card">
    <h3>Purchase History</h3>

    <?php if (count($orders) === 0): ?>
      <p style="text-align: center; color: #6c757d; padding: 40px;">No orders yet. Start shopping!</p>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="order-header">
            <span class="order-id">Order #<?php echo (int)$order['id']; ?></span>
            <span class="order-status status-<?php echo strtolower($order['status']); ?>">
              <?php echo ucfirst(e($order['status'])); ?>
            </span>
          </div>
          
          <div class="order-items">
            <?php if(!empty($order['items'])): ?>
              <?php foreach($order['items'] as $item): ?>
                <div class="order-item">
                  <?php 
                  $item_img = !empty($item['image']) ? UPLOADS_URL . e($item['image']) : BASE_URL . 'placeholder.png';
                  ?>
                  <img src="<?php echo $item_img; ?>" alt="<?php echo e($item['title']); ?>" class="order-item-image">
                  <div class="order-item-details">
                    <div class="order-item-title"><?php echo e($item['title']); ?></div>
                    <?php if(!empty($item['author'])): ?>
                      <div class="order-item-author">By <?php echo e($item['author']); ?></div>
                    <?php endif; ?>
                    <div class="order-item-qty">
                      Quantity: <?php echo (int)$item['qty']; ?> × Rs. <?php echo number_format($item['price'], 2); ?>
                    </div>
                    <?php 
                    // Show review button only for completed/delivered orders
                    if(in_array(strtolower($order['status']), ['completed', 'delivered'])):
                      $already_reviewed = has_user_rated($conn, $uid, $item['product_id']);
                    ?>
                      <?php if($already_reviewed): ?>
                        <span class="review-btn reviewed">✓ Reviewed</span>
                      <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>product_detail.php?id=<?php echo (int)$item['product_id']; ?>#reviews" class="review-btn">✍ Write Review</a>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          
          <div class="order-footer">
            <span class="order-date">📅 <?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
            <span class="order-total">Total: Rs. <?php echo number_format($order['total'], 2); ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

</body>
</html>

<?php require_once 'includes/footer.php'; ?>
