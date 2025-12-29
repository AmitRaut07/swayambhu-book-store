<?php
// wishlist.php - User wishlist page
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

require_login();

$conn = db_connect();
$page_title = "My Wishlist";
$user_id = (int)$_SESSION['user_id'];

// Get wishlist items
$sql = "SELECT w.id as wishlist_id, p.* FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = {$user_id} 
        ORDER BY w.created_at DESC";
$res = mysqli_query($conn, $sql);

include 'includes/header.php';
?>

<style>
.wishlist-section {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

.wishlist-header {
    margin-bottom: 40px;
}

.wishlist-header h2 {
    font-size: 2rem;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 10px 0;
}

.wishlist-count {
    color: #6c757d;
    font-size: 1rem;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.wishlist-item {
    position: relative;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.wishlist-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.remove-wishlist-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 35px;
    height: 35px;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 50%;
    color: #dc3545;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}

.remove-wishlist-btn:hover {
    background: #dc3545;
    color: #fff;
    transform: scale(1.1);
}

.wishlist-empty {
    text-align: center;
    padding: 80px 20px;
}

.wishlist-empty h3 {
    font-size: 1.5rem;
    color: #6c757d;
    margin-bottom: 20px;
}

.wishlist-empty a {
    display: inline-block;
    padding: 12px 30px;
    background: #667eea;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.wishlist-empty a:hover {
    background: #764ba2;
    transform: translateY(-2px);
}
</style>

<section class="wishlist-section">
    <div class="wishlist-header">
        <h2>My Wishlist</h2>
        <p class="wishlist-count">
            <?php 
            $count = mysqli_num_rows($res);
            echo $count . ' ' . ($count === 1 ? 'item' : 'items');
            ?>
        </p>
    </div>

    <?php if($count > 0): ?>
        <div class="wishlist-grid">
            <?php while($item = mysqli_fetch_assoc($res)): 
                $img = !empty($item['image']) ? UPLOADS_URL . e($item['image']) : BASE_URL . 'placeholder.png';
                $in_stock = (int)$item['stock'] > 0;
            ?>
                <div class="wishlist-item">
                    <button class="remove-wishlist-btn wishlist-btn in-wishlist" data-product-id="<?php echo $item['id']; ?>">
                        ×
                    </button>
                    
                    <div class="product-image-wrapper" style="height: 320px; background: #f8f9fa;">
                        <img src="<?php echo $img; ?>" alt="<?php echo e($item['title']); ?>" class="product-image" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    
                    <div class="product-info" style="padding: 20px;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 8px;">
                            <a href="product_detail.php?id=<?php echo $item['id']; ?>" style="color: #1a1a1a; text-decoration: none;">
                                <?php echo e($item['title']); ?>
                            </a>
                        </h3>
                        
                        <?php if(!empty($item['author'])): ?>
                            <p class="author" style="font-size: 0.9rem; color: #6c757d; font-style: italic; margin-bottom: 12px;">
                                By <?php echo e($item['author']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <p class="price" style="font-size: 1.5rem; font-weight: 700; color: #2563eb; margin-bottom: 8px;">
                            Rs. <?php echo number_format($item['price'], 2); ?>
                        </p>
                        
                        <p class="stock <?php echo $in_stock ? 'in-stock' : 'out-of-stock'; ?>" style="font-size: 0.85rem; margin-bottom: 16px; padding: 6px 12px; border-radius: 6px; display: inline-block;">
                            <?php echo $in_stock ? "In Stock: " . $item['stock'] : "Out of Stock"; ?>
                        </p>
                        
                        <?php if($in_stock): ?>
                            <form method="post" action="add_to_cart.php" style="margin-top: 12px;">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                    Add to Cart
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="wishlist-empty">
            <h3>Your wishlist is empty</h3>
            <p style="color: #6c757d; margin-bottom: 30px;">Start adding books you love!</p>
            <a href="<?php echo BASE_URL; ?>products.php">Browse Books</a>
        </div>
    <?php endif; ?>
</section>

<script>
// Remove from wishlist with animation
document.querySelectorAll('.remove-wishlist-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const item = this.closest('.wishlist-item');
        item.style.opacity = '0.5';
        item.style.pointerEvents = 'none';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
