<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// DB connection
$conn = db_connect();

$page_title = 'Cart';
include 'includes/header.php';

// Clear "Buy Now" session state if user visits the regular cart
// This prevents "Buy Now" persisting if they abandon it and go to Cart
if(isset($_SESSION['buy_now_product_id'])) unset($_SESSION['buy_now_product_id']);
if(isset($_SESSION['buy_now_qty'])) unset($_SESSION['buy_now_qty']);

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    echo '<div style="text-align: center; padding: 80px 20px;">
            <h2 style="color: #6c757d; margin-bottom: 20px;">Your cart is empty</h2>
            <a href="products.php" class="btn btn-primary" style="display: inline-block; padding: 12px 30px; text-decoration: none; border-radius: 8px;">Continue Shopping</a>
          </div>';
    require_once 'includes/footer.php';
    exit;
}

// Fetch products from DB
$ids = array_map('intval', array_keys($cart));
$where = 'id IN (' . implode(',', $ids) . ')';
$res = mysqli_query($conn, "SELECT * FROM products WHERE {$where}");
$products = [];
while ($p = mysqli_fetch_assoc($res)) {
    $products[$p['id']] = $p;
}

// Calculate totals
$subtotal = 0;
foreach ($cart as $id => $qty) {
    if (isset($products[$id])) {
        $subtotal += $products[$id]['price'] * $qty;
    }
}

// Apply discount if exists
$discount_amount = 0;
$discount_code = null;
if(isset($_SESSION['discount_code'])){
    $discount_code = $_SESSION['discount_code'];
    $discount_amount = $subtotal * ($discount_code['percentage'] / 100);
}

$total = $subtotal - $discount_amount;
?>

<style>
.cart-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.cart-section h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 30px;
}

.cart-table {
    width: 100%;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.cart-table table {
    width: 100%;
    border-collapse: collapse;
}

.cart-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #1a1a1a;
    border-bottom: 2px solid #e5e7eb;
}

.cart-table td {
    padding: 20px 15px;
    border-bottom: 1px solid #e5e7eb;
}

.cart-table tr:last-child td {
    border-bottom: none;
}

.cart-item-title {
    font-weight: 600;
    color: #1a1a1a;
}

.cart-qty-input {
    width: 80px;
    padding: 8px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
}

.cart-summary {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    max-width: 400px;
    margin-left: auto;
}

.discount-section {
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 2px solid #e5e7eb;
}

.discount-form {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.discount-form input {
    flex: 1;
    padding: 10px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.95rem;
}

.discount-form button {
    padding: 10px 20px;
    background: #667eea;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.discount-form button:hover {
    background: #764ba2;
}

.discount-applied {
    background: #d1fae5;
    color: #065f46;
    padding: 12px;
    border-radius: 8px;
    margin-top: 10px;
    font-size: 0.9rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 1.05rem;
}

.summary-row.total {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
    padding-top: 20px;
    border-top: 2px solid #e5e7eb;
    margin-top: 20px;
}

.cart-actions {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.cart-actions button,
.cart-actions a {
    flex: 1;
    padding: 15px;
    text-align: center;
    border-radius: 10px;
    font-weight: 700;
    font-size: 1rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-remove {
    color: #dc3545;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.btn-remove:hover {
    color: #c82333;
}

@media (max-width: 768px) {
    .cart-table {
        overflow-x: auto;
    }
    
    .cart-actions {
        flex-direction: column;
    }
}
</style>

<section class="cart-section">
  <h2>Shopping Cart</h2>
  
  <form method="post" action="update_cart.php">
    <div class="cart-table">
      <table>
        <thead>
          <tr>
            <th>Book</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cart as $id => $qty): 
              if (!isset($products[$id])) continue;
              $p = $products[$id];
              $sub = $p['price'] * $qty;
          ?>
          <tr>
            <td class="cart-item-title"><?php echo e($p['title']); ?></td>
            <td>Rs. <?php echo number_format($p['price'], 2); ?></td>
            <td>
              <input class="cart-qty-input" type="number" name="qty[<?php echo (int)$id; ?>]" 
                     value="<?php echo (int)$qty; ?>" min="1" max="<?php echo (int)$p['stock']; ?>">
            </td>
            <td>Rs. <?php echo number_format($sub, 2); ?></td>
            <td><a href="remove_from_cart.php?id=<?php echo (int)$id; ?>" class="btn-remove">Remove</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <div class="cart-summary">
      <div class="discount-section">
        <h3 style="margin-bottom: 10px; font-size: 1.1rem;">Have a discount code?</h3>
        <?php if($discount_code): ?>
          <div class="discount-applied">
            ✓ Discount code "<?php echo e($discount_code['code']); ?>" applied (<?php echo $discount_code['percentage']; ?>% off)
            <a href="remove_discount.php" style="color: #065f46; text-decoration: underline; margin-left: 10px;">Remove</a>
          </div>
        <?php else: ?>
          <div class="discount-form" id="discountForm">
            <input type="text" id="discountCode" placeholder="Enter code">
            <button type="button" onclick="applyDiscount()">Apply</button>
          </div>
          <div id="discountMessage" style="margin-top: 10px; font-size: 0.9rem;"></div>
        <?php endif; ?>
      </div>
      
      <div class="summary-row">
        <span>Subtotal:</span>
        <span>Rs. <?php echo number_format($subtotal, 2); ?></span>
      </div>
      
      <?php if($discount_amount > 0): ?>
      <div class="summary-row" style="color: #28a745;">
        <span>Discount:</span>
        <span>- Rs. <?php echo number_format($discount_amount, 2); ?></span>
      </div>
      <?php endif; ?>
      
      <div class="summary-row total">
        <span>Total:</span>
        <span>Rs. <?php echo number_format($total, 2); ?></span>
      </div>
      
      <div class="cart-actions">
        <button class="btn btn-secondary" type="submit">Update Cart</button>
        <a class="btn btn-primary" href="checkout.php">Checkout</a>
      </div>
      
      <a href="products.php" style="display: block; text-align: center; margin-top: 20px; color: #667eea; text-decoration: none;">
        ← Continue Shopping
      </a>
    </div>
  </form>
</section>

<script>
function applyDiscount() {
    const code = document.getElementById('discountCode').value.trim();
    const messageDiv = document.getElementById('discountMessage');
    
    if(!code) {
        messageDiv.innerHTML = '<span style="color: #dc3545;">Please enter a discount code</span>';
        return;
    }
    
    window.showLoading();
    
    fetch('apply_discount.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'code=' + encodeURIComponent(code)
    })
    .then(response => response.json())
    .then(data => {
        window.hideLoading();
        if(data.success) {
            messageDiv.innerHTML = '<span style="color: #28a745;">✓ ' + data.message + '</span>';
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            messageDiv.innerHTML = '<span style="color: #dc3545;">✗ ' + data.message + '</span>';
        }
    })
    .catch(error => {
        window.hideLoading();
        messageDiv.innerHTML = '<span style="color: #dc3545;">Failed to apply discount</span>';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>

