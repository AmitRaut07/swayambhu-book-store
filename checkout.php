<?php
require_once 'includes/security_headers.php'; // Prevent caching
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'functions.php';

// Establish database connection
$conn = db_connect();

// Require user to be logged in
require_login();

$page_title = 'Checkout';

// Check if this is a "Buy Now" purchase
// We check GET params first (fresh request), then Session (redirected request)
$is_buy_now = (isset($_GET['product_id']) && isset($_GET['qty'])) || 
              (isset($_SESSION['buy_now_product_id']) && isset($_SESSION['buy_now_qty']));

if($is_buy_now){
    // Determine source of data
    if(isset($_GET['product_id']) && isset($_GET['qty'])) {
        // Fresh "Buy Now" click
        $product_id = (int)$_GET['product_id'];
        $qty = (int)$_GET['qty'];
        
        // Update session for persistence across redirects (e.g. validation errors)
        $_SESSION['buy_now_product_id'] = $product_id;
        $_SESSION['buy_now_qty'] = $qty;
    } else {
        // Recovered from session (e.g. after validation error redirect)
        $product_id = (int)$_SESSION['buy_now_product_id'];
        $qty = (int)$_SESSION['buy_now_qty'];
    }
    
    // Add safety check for valid ID
    if($product_id <= 0 || $qty <= 0){
        // cleanup invalid state
        unset($_SESSION['buy_now_product_id']);
        unset($_SESSION['buy_now_qty']);
        
        flash_set('error', 'Invalid product or quantity');
        redirect('products.php');
        exit;
    }
    
    // Fetch product details
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id={$product_id} LIMIT 1");
    if(!$res || mysqli_num_rows($res) === 0){
        // cleanup invalid state
        unset($_SESSION['buy_now_product_id']);
        unset($_SESSION['buy_now_qty']);
        
        flash_set('error', 'Product not found');
        redirect('products.php');
        exit;
    }
    
    $product = mysqli_fetch_assoc($res);
    
    // Check stock
    if($product['stock'] < $qty){
        // We generally keep the session here so they can try again or we can redirect back to product
        // But if we redirect to product_detail, we might as well keep it simple.
        flash_set('error', 'Insufficient stock');
        redirect("product_detail.php?id={$product_id}");
        exit;
    }
    
    // Setup summary variables
    $products = [$product_id => $product];
    $cart = [$product_id => $qty];
    $total = $product['price'] * $qty;
    
} else {
    // Regular cart checkout
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    
    if (empty($cart)) {
        flash_set('error', 'Your cart is empty.');
        redirect('cart.php');
        exit;
    }
    
    // Fetch product details for items in cart
    $ids = array_map('intval', array_keys($cart));
    $res = mysqli_query($conn, 'SELECT * FROM products WHERE id IN (' . implode(',', $ids) . ')');
    
    if (!$res) {
        die("Query failed: " . mysqli_error($conn));
    }
    
    $products = [];
    $total = 0;
    
    while ($p = mysqli_fetch_assoc($res)) {
        $products[$p['id']] = $p;
        $qty = (int)$cart[$p['id']];
        $total += $p['price'] * $qty;
    }
}

include 'includes/header.php';
?>

<style>
.checkout-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.checkout-header {
    text-align: center;
    margin-bottom: 40px;
}

.checkout-header h2 {
    font-size: 2rem;
    color: #1a1a1a;
    margin-bottom: 10px;
}

.checkout-section {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.order-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}

.order-summary h3 {
    font-size: 1.3rem;
    color: #1a1a1a;
    margin-bottom: 20px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
}

.order-item:last-child {
    border-bottom: none;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 4px;
}

.item-qty {
    font-size: 0.9rem;
    color: #6c757d;
}

.item-price {
    font-weight: 600;
    color: #2563eb;
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

.order-total .total-label {
    color: #1a1a1a;
}

.order-total .total-amount {
    color: #2563eb;
}

.checkout-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-group input,
.form-group textarea {
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.btn-checkout {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 16px 32px;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-checkout:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 5px;
    display: none;
}

.form-group.error input,
.form-group.error textarea {
    border-color: #dc3545;
}

.form-group.error .error-message {
    display: block;
}

@media (max-width: 768px) {
    .checkout-section {
        padding: 20px;
    }
    
    .order-item {
        flex-direction: column;
        gap: 8px;
    }
}
</style>

<div class="checkout-container">
    <div class="checkout-header">
        <h2>Checkout</h2>
        <p>Complete your order</p>
    </div>

    <div class="checkout-section">
        <div class="order-summary">
            <h3>Order Summary</h3>
            <?php foreach ($products as $id => $p): ?>
                <?php $qty = (int)$cart[$id]; ?>
                <div class="order-item">
                    <div class="item-details">
                        <div class="item-name"><?php echo e($p['title']); ?></div>
                        <div class="item-qty">Quantity: <?php echo $qty; ?></div>
                    </div>
                    <div class="item-price">
                        Rs. <?php echo number_format($p['price'] * $qty, 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="order-total">
                <span class="total-label">Total Amount:</span>
                <span class="total-amount">Rs. <?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <form method="post" action="process_checkout.php" class="checkout-form">
            <input type="hidden" name="total" value="<?php echo $total; ?>">
            
            <div class="form-group">
                <label for="fullname">Full Name *</label>
                <input 
                    type="text" 
                    id="fullname" 
                    name="fullname" 
                    placeholder="Enter your full name"
                    minlength="3"
                    required
                >
                <span class="error-message" id="fullname-error">Name must be at least 3 characters</span>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    placeholder="Enter your 10-digit phone number"
                    pattern="[0-9]{10}"
                    minlength="10"
                    maxlength="10"
                    title="Please enter a valid 10-digit phone number"
                    required
                >
                <small style="color: #6c757d; font-size: 0.85rem; margin-top: 5px; display: block;">
                    Enter 10-digit mobile number (e.g., 9841234567)
                </small>
                <span class="error-message" id="phone-error">Please enter exactly 10 digits</span>
            </div>

            <div class="form-group">
                <label for="address">Delivery Address *</label>
                <textarea 
                    id="address" 
                    name="address" 
                    placeholder="Enter your complete delivery address"
                    minlength="10"
                    required
                ></textarea>
                <span class="error-message" id="address-error">Address must be at least 10 characters</span>
            </div>

            <div class="form-group">
                <label for="email">Email (Optional)</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email for order confirmation"
                >
            </div>

            <button type="submit" class="btn-checkout">
                Place Order (Pay with Khalti)
            </button>
        </form>
    </div>
</div>

<script>
// Full name validation
const fullnameInput = document.getElementById('fullname');
const fullnameError = document.getElementById('fullname-error');
const fullnameGroup = fullnameInput.closest('.form-group');

fullnameInput.addEventListener('input', function() {
    // Remove extra spaces
    this.value = this.value.replace(/\s+/g, ' ');
    
    // Validate length
    if (this.value.trim().length >= 3) {
        fullnameGroup.classList.remove('error');
    } else if (this.value.length > 0) {
        fullnameGroup.classList.add('error');
    }
});

fullnameInput.addEventListener('blur', function() {
    // Trim whitespace on blur
    this.value = this.value.trim();
    
    if (this.value.length > 0 && this.value.length < 3) {
        fullnameGroup.classList.add('error');
    }
});

// Phone number validation
const phoneInput = document.getElementById('phone');
const phoneError = document.getElementById('phone-error');
const phoneGroup = phoneInput.closest('.form-group');

phoneInput.addEventListener('input', function(e) {
    // Remove non-numeric characters
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Limit to 10 digits
    if (this.value.length > 10) {
        this.value = this.value.slice(0, 10);
    }
    
    // Validate
    if (this.value.length === 10) {
        phoneGroup.classList.remove('error');
    } else if (this.value.length > 0) {
        phoneGroup.classList.add('error');
    }
});

phoneInput.addEventListener('blur', function() {
    if (this.value.length > 0 && this.value.length !== 10) {
        phoneGroup.classList.add('error');
    }
});

// Address validation
const addressInput = document.getElementById('address');
const addressError = document.getElementById('address-error');
const addressGroup = addressInput.closest('.form-group');

addressInput.addEventListener('input', function() {
    if (this.value.length >= 10) {
        addressGroup.classList.remove('error');
    } else if (this.value.length > 0) {
        addressGroup.classList.add('error');
    }
});

// Form submission validation
document.querySelector('.checkout-form').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Validate fullname
    if (fullnameInput.value.trim().length < 3) {
        fullnameGroup.classList.add('error');
        isValid = false;
    }
    
    // Validate phone
    if (phoneInput.value.length !== 10) {
        phoneGroup.classList.add('error');
        isValid = false;
    }
    
    // Validate address
    if (addressInput.value.length < 10) {
        addressGroup.classList.add('error');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fix the errors before submitting.');
        
        // Scroll to first error
        const firstError = document.querySelector('.form-group.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>