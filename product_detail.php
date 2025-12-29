<?php
// product_detail.php - Detailed product page with ratings and reviews
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

$conn = db_connect();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($product_id <= 0){
    header('Location: ' . BASE_URL . 'products.php');
    exit;
}

// Get product details
$res = mysqli_query($conn, "SELECT * FROM products WHERE id={$product_id} LIMIT 1");
if(!$res || mysqli_num_rows($res) === 0){
    header('Location: ' . BASE_URL . 'products.php');
    exit;
}

$product = mysqli_fetch_assoc($res);
$page_title = $product['title'];

// Get ratings
$avg_rating = get_average_rating($conn, $product_id);
$review_count = get_review_count($conn, $product_id);

// Check if user has rated
$user_has_rated = false;
$user_has_purchased = false;
if(is_logged_in()){
    $user_has_rated = has_user_rated($conn, $_SESSION['user_id'], $product_id);
    $user_has_purchased = has_purchased_product($conn, $_SESSION['user_id'], $product_id);
}

// Get reviews
$reviews_res = mysqli_query($conn, "SELECT r.*, u.username FROM ratings r 
                                     JOIN users u ON r.user_id = u.id 
                                     WHERE r.product_id={$product_id} 
                                     ORDER BY r.created_at DESC LIMIT 10");

// Get total sales count
$sales_query = "SELECT SUM(oi.qty) as total_sold 
                FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE oi.product_id = {$product_id} AND o.status IN ('completed', 'delivered')";
$sales_res = mysqli_query($conn, $sales_query);
$total_sold = 0;
if($sales_res && $sales_row = mysqli_fetch_assoc($sales_res)){
    $total_sold = $sales_row['total_sold'] ? (int)$sales_row['total_sold'] : 0;
}

include 'includes/header.php';

$img = !empty($product['image']) ? UPLOADS_URL . e($product['image']) : BASE_URL . 'placeholder.png';
$in_stock = (int)$product['stock'] > 0;
?>

<style>
.product-detail-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.product-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    margin-bottom: 60px;
}

.product-detail-image {
    background: #f8f9fa;
    border-radius: 16px;
    padding: 30px;
    text-align: center;
}

.product-detail-image img {
    max-width: 100%;
    max-height: 500px;
    object-fit: contain;
    border-radius: 8px;
}

.product-detail-info h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 15px;
    line-height: 1.2;
}

.product-author {
    font-size: 1.2rem;
    color: #6c757d;
    font-style: italic;
    margin-bottom: 20px;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
}

.stars {
    color: #ffc107;
    font-size: 1.3rem;
    letter-spacing: 2px;
}

.rating-text {
    color: #6c757d;
    font-size: 0.95rem;
}

.product-price {
    font-size: 3rem;
    font-weight: 800;
    color: #667eea;
    margin-bottom: 15px;
}

.product-stock {
    font-size: 1rem;
    margin-bottom: 25px;
    padding: 10px 16px;
    border-radius: 8px;
    display: inline-block;
    font-weight: 600;
}

.product-stats {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #495057;
    border: 1px solid #dee2e6;
}

.stat-badge .icon {
    font-size: 1.2rem;
}

.stat-badge.sales {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border-color: #6ee7b7;
}

.stat-badge.reviews {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border-color: #fcd34d;
}

.product-description {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #4a5568;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.product-actions {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.product-actions form {
    flex: 1;
}

.qty-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.qty-selector label {
    font-weight: 600;
    color: #1a1a1a;
}

.qty-selector input {
    width: 80px;
    padding: 10px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-align: center;
}

.reviews-section {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 2px solid #e5e7eb;
}

.reviews-header {
    margin-bottom: 30px;
}

.reviews-header h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.review-form {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 40px;
}

.review-form h3 {
    font-size: 1.3rem;
    margin-bottom: 20px;
}

.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
    margin-bottom: 20px;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.star-rating input[type="radio"]:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label {
    color: #ffc107;
}

.review-item {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.review-author {
    font-weight: 700;
    font-size: 1.1rem;
    color: #1a1a1a;
}

.review-date {
    color: #6c757d;
    font-size: 0.9rem;
}

.review-stars {
    color: #ffc107;
    margin-bottom: 10px;
}

.review-text {
    line-height: 1.6;
    color: #4a5568;
}

@media (max-width: 768px) {
    .product-detail-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .product-detail-info h1 {
        font-size: 1.8rem;
    }
    
    .product-price {
        font-size: 2rem;
    }
    
    .product-actions {
        flex-direction: column;
    }
}
</style>

<section class="product-detail-section">
    <div class="product-detail-grid">
        <div class="product-detail-image">
            <img src="<?php echo $img; ?>" alt="<?php echo e($product['title']); ?>">
        </div>
        
        <div class="product-detail-info">
            <h1><?php echo e($product['title']); ?></h1>
            
            <?php if(!empty($product['author'])): ?>
                <p class="product-author">By <?php echo e($product['author']); ?></p>
            <?php endif; ?>
            
            <div class="rating-display">
                <div class="stars">
                    <?php
                    $full_stars = floor($avg_rating);
                    $empty_stars = 5 - $full_stars;
                    echo str_repeat('★', $full_stars) . str_repeat('☆', $empty_stars);
                    ?>
                </div>
                <span class="rating-text">
                    <?php echo $avg_rating > 0 ? number_format($avg_rating, 1) : 'No ratings'; ?> 
                    (<?php echo $review_count; ?> <?php echo $review_count === 1 ? 'review' : 'reviews'; ?>)
                </span>
            </div>
            
            <p class="product-price">Rs. <?php echo number_format($product['price'], 2); ?></p>
            
            <p class="product-stock <?php echo $in_stock ? 'in-stock' : 'out-of-stock'; ?>">
                <?php echo $in_stock ? "In Stock: " . $product['stock'] : "Out of Stock"; ?>
            </p>
            
            <!-- Product Stats -->
            <div class="product-stats">
                <div class="stat-badge sales">
                    <span class="icon">📦</span>
                    <span><?php echo $total_sold; ?> Sold</span>
                </div>
                <div class="stat-badge reviews">
                    <span class="icon">⭐</span>
                    <span><?php echo $review_count; ?> <?php echo $review_count === 1 ? 'Review' : 'Reviews'; ?></span>
                </div>
                <?php if($avg_rating > 0): ?>
                <div class="stat-badge">
                    <span class="icon">📊</span>
                    <span><?php echo number_format($avg_rating, 1); ?>/5.0 Rating</span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if(!empty($product['description'])): ?>
                <div class="product-description">
                    <h3 style="font-size: 1.2rem; margin-bottom: 12px; color: #1a1a1a;">📖 Description</h3>
                    <?php echo nl2br(e($product['description'])); ?>
                </div>
            <?php endif; ?>
            
            <?php if($in_stock): ?>
                <div class="qty-selector">
                    <label for="qty">Quantity:</label>
                    <input type="number" id="qty" value="1" min="1" max="<?php echo $product['stock']; ?>">
                </div>
                
                <div class="product-actions">
                    <form method="post" action="add_to_cart.php">
                        <input type="hidden" name="id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="qty" id="cart-qty" value="1">
                        <button type="submit" class="btn btn-secondary" style="padding: 14px 28px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; background: #fff; color: #2563eb; border: 2px solid #2563eb;">Add to Cart</button>
                    </form>
                    <form method="get" action="checkout.php">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="qty" id="buy-qty" value="1">
                        <button type="submit" class="btn btn-primary" style="padding: 14px 28px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">Buy Now</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if(is_logged_in()): ?>
                <button class="wishlist-btn <?php echo is_in_wishlist($conn, $_SESSION['user_id'], $product_id) ? 'in-wishlist' : ''; ?>" 
                        data-product-id="<?php echo $product_id; ?>"
                        style="position: static; width: auto; height: auto; padding: 12px 24px; border-radius: 8px; font-size: 1rem;">
                    <?php echo is_in_wishlist($conn, $_SESSION['user_id'], $product_id) ? '❤ In Wishlist' : '♡ Add to Wishlist'; ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="reviews-section">
        <div class="reviews-header">
            <h2>Customer Reviews</h2>
            <p style="color: #6c757d; font-size: 0.95rem;">Only verified purchasers can leave reviews</p>
        </div>
        
        <?php if(is_logged_in()): ?>
            <?php if($user_has_purchased && !$user_has_rated): ?>
                <!-- Review Form - Only for verified purchasers who haven't rated -->
                <div class="review-form">
                    <h3>✅ Write Your Review</h3>
                    <p style="color: #065f46; background: #d1fae5; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
                        You are a verified purchaser of this product
                    </p>
                    <form method="post" action="submit_rating.php">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        
                        <label style="display: block; margin-bottom: 10px; font-weight: 600;">Your Rating:</label>
                        <div class="star-rating" id="reviews">
                            <input type="radio" name="rating" value="5" id="star5" required>
                            <label for="star5">★</label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4">★</label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3">★</label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2">★</label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1">★</label>
                        </div>
                        
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Your Review:</label>
                        <textarea name="review" rows="4" placeholder="Share your experience with this book..." 
                                  style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; margin-bottom: 15px;" required></textarea>
                        
                        <button type="submit" class="btn btn-primary" style="padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            Submit Review
                        </button>
                    </form>
                </div>
            <?php elseif($user_has_rated): ?>
                <!-- Already reviewed -->
                <p style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
                    ✓ Thank you! You have already reviewed this product
                </p>
            <?php elseif(!$user_has_purchased): ?>
                <!-- Not a purchaser -->
                <div style="background: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <strong>📦 Purchase Required</strong>
                    <p style="margin: 10px 0 0 0;">You need to purchase this product before you can leave a review. This ensures all reviews are from verified customers.</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Not logged in -->
            <div style="background: #e7f3ff; border: 1px solid #2196F3; color: #0d47a1; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <strong>🔐 Login Required</strong>
                <p style="margin: 10px 0 0 0;">
                    Please <a href="<?php echo BASE_URL; ?>login.php" style="color: #0d47a1; text-decoration: underline;">login</a> to leave a review. 
                    Only verified purchasers can submit reviews.
                </p>
            </div>
        <?php endif; ?>
        
        <!-- Display All Reviews -->
        <div class="reviews-list">
            <?php if(mysqli_num_rows($reviews_res) > 0): ?>
                <?php while($review = mysqli_fetch_assoc($reviews_res)): 
                    // Check if this reviewer purchased the product
                    $reviewer_purchased = has_purchased_product($conn, $review['user_id'], $product_id);
                ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div>
                                <span class="review-author"><?php echo e($review['username']); ?></span>
                                <?php if($reviewer_purchased): ?>
                                    <span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; margin-left: 8px; font-weight: 600;">
                                        ✓ Verified Purchase
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <div class="review-stars">
                            <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                        </div>
                        <?php if(!empty($review['review'])): ?>
                            <p class="review-text"><?php echo nl2br(e($review['review'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #6c757d; padding: 40px;">No reviews yet. Be the first verified purchaser to review!</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Sync quantity inputs
document.getElementById('qty').addEventListener('input', function() {
    document.getElementById('cart-qty').value = this.value;
    document.getElementById('buy-qty').value = this.value;
});
</script>

<?php include 'includes/footer.php'; ?>
