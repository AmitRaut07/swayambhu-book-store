<?php
// products.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once 'config.php';
require_once 'functions.php';

// Ensure DB connection
$conn = db_connect();

// Page title
$page_title = "Books";

// Get user name if logged in
$user_name = '';
if (is_logged_in()) {
    $uid = (int)$_SESSION['user_id'];
    $res_user = mysqli_query($conn, "SELECT username FROM users WHERE id={$uid} LIMIT 1");
    if ($row_user = mysqli_fetch_assoc($res_user)) {
        $user_name = $row_user['username'];
    }
}

// Cart count
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// Handle sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order_by = "ORDER BY id DESC"; // default

switch($sort) {
    case 'price_low':
        $order_by = "ORDER BY price ASC";
        break;
    case 'price_high':
        $order_by = "ORDER BY price DESC";
        break;
    case 'name':
        $order_by = "ORDER BY title ASC";
        break;
    case 'newest':
    default:
        $order_by = "ORDER BY id DESC";
        break;
}

// Fetch products
$res = mysqli_query($conn, "SELECT * FROM products {$order_by}");
if (!$res) die("Query failed: " . mysqli_error($conn));

include 'includes/header.php';
?>

<style>
/* Products Page Specific Styles */
.products-section {
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 20px;
}

.products-section .section-header {
  margin-bottom: 40px;
}

.products-section .section-header h2 {
  font-size: 2rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
}

.products-section .products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 30px;
  margin-bottom: 40px;
}

.products-section .product-card {
  background: #fff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
  border: 1px solid #e5e7eb;
}

.products-section .product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.products-section .product-image-wrapper {
  position: relative;
  width: 100%;
  height: 320px;
  background: #f8f9fa;
  overflow: hidden;
}

.products-section .product-card .product-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.products-section .product-card:hover .product-image {
  transform: scale(1.05);
}

.products-section .product-card .product-info {
  padding: 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.products-section .product-card .product-info h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 8px 0;
  line-height: 1.4;
  min-height: 2.8em;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.products-section .product-card .author {
  font-size: 0.9rem;
  color: #6c757d;
  margin: 0 0 12px 0;
  font-style: italic;
}

.products-section .product-card .price {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2563eb;
  margin: 0 0 8px 0;
}

.products-section .product-card .stock {
  font-size: 0.85rem;
  margin: 0 0 16px 0;
  padding: 6px 12px;
  border-radius: 6px;
  display: inline-block;
  width: fit-content;
}

.products-section .product-card .stock.in-stock {
  background: #d1fae5;
  color: #065f46;
}

.products-section .product-card .stock.out-of-stock {
  background: #fee2e2;
  color: #991b1b;
}

.products-section .card-buttons {
  display: flex;
  gap: 10px;
  margin-top: auto;
}

.products-section .card-buttons form {
  flex: 1;
  margin: 0;
}

.products-section .card-buttons button {
  width: 100%;
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.products-section .btn-cart {
  background: #fff;
  color: #2563eb;
  border: 2px solid #2563eb !important;
}

.products-section .btn-cart:hover {
  background: #eff6ff;
  transform: translateY(-1px);
}

.products-section .btn-buy {
  background: #2563eb;
  color: #fff;
}

.products-section .btn-buy:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.products-section .no-products {
  text-align: center;
  padding: 60px 20px;
  color: #6c757d;
  font-size: 1.1rem;
}

@media (max-width: 768px) {
  .products-section .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
  }

  .products-section .product-card .product-info {
    padding: 16px;
  }

  .products-section .product-card .product-info h3 {
    font-size: 0.95rem;
  }

  .products-section .product-card .price {
    font-size: 1.25rem;
  }

  .products-section .card-buttons {
    flex-direction: column;
  }

  .products-section .card-buttons button {
    padding: 10px 14px;
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .products-section .products-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
}
</style>

<section class="products-section">
  <div class="section-header">
    <h2>Books</h2>
    <div style="margin-top: 15px;">
      <form method="get" action="products.php" style="display: inline;">
        <label for="sortSelect" style="margin-right: 10px; font-weight: 600;">Sort by:</label>
        <select name="sort" id="sortSelect" onchange="this.form.submit()" style="padding: 8px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.95rem; cursor: pointer;">
          <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest First</option>
          <option value="price_low" <?php echo ($sort === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
          <option value="price_high" <?php echo ($sort === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
          <option value="name" <?php echo ($sort === 'name') ? 'selected' : ''; ?>>Name: A-Z</option>
        </select>
      </form>
    </div>
  </div>

  <div class="products-grid">
    <?php
    if (mysqli_num_rows($res) > 0):
        while ($p = mysqli_fetch_assoc($res)):
            $img = !empty($p['image']) ? UPLOADS_URL . e($p['image']) : BASE_URL . 'placeholder.png';
            $in_stock = (int)$p['stock'] > 0;
    ?>
      <div class="product-card">
        <?php if(is_logged_in()): ?>
          <button class="wishlist-btn <?php echo is_in_wishlist($conn, $_SESSION['user_id'], $p['id']) ? 'in-wishlist' : ''; ?>" 
                  data-product-id="<?php echo (int)$p['id']; ?>">
            <?php echo is_in_wishlist($conn, $_SESSION['user_id'], $p['id']) ? '❤' : '♡'; ?>
          </button>
        <?php endif; ?>
        
        <div class="product-image-wrapper">
          <a href="<?php echo BASE_URL; ?>product_detail.php?id=<?php echo (int)$p['id']; ?>">
            <img class="product-image" src="<?php echo e($img); ?>" alt="<?php echo e($p['title']); ?>">
          </a>
        </div>

        <div class="product-info">
          <h3>
            <a href="<?php echo BASE_URL; ?>product_detail.php?id=<?php echo (int)$p['id']; ?>" style="color: inherit; text-decoration: none;">
              <?php echo e($p['title']); ?>
            </a>
          </h3>
          
          <?php if(!empty($p['author'])): ?>
            <p class="author">By <?php echo e($p['author']); ?></p>
          <?php endif; ?>
          
          <?php 
          // Get rating, reviews and sales for this product
          $prod_rating = get_average_rating($conn, $p['id']);
          $prod_reviews = get_review_count($conn, $p['id']);
          
          // Get sales count
          $sales_q = "SELECT SUM(oi.qty) as sold FROM order_items oi 
                      JOIN orders o ON oi.order_id = o.id 
                      WHERE oi.product_id = {$p['id']} AND o.status IN ('completed', 'delivered')";
          $sales_r = mysqli_query($conn, $sales_q);
          $prod_sold = 0;
          if($sales_r && $sr = mysqli_fetch_assoc($sales_r)){
              $prod_sold = $sr['sold'] ? (int)$sr['sold'] : 0;
          }
          ?>
          
          <!-- Product Stats -->
          <div style="display: flex; gap: 8px; margin: 10px 0; flex-wrap: wrap;">
            <?php if($prod_rating > 0): ?>
            <span style="font-size: 0.85rem; color: #ffc107; font-weight: 600;">
              ⭐ <?php echo number_format($prod_rating, 1); ?>
            </span>
            <?php endif; ?>
            <?php if($prod_reviews > 0): ?>
            <span style="font-size: 0.85rem; color: #6c757d;">
              (<?php echo $prod_reviews; ?> <?php echo $prod_reviews === 1 ? 'review' : 'reviews'; ?>)
            </span>
            <?php endif; ?>
            <?php if($prod_sold > 0): ?>
            <span style="font-size: 0.85rem; color: #28a745; font-weight: 600;">
              📦 <?php echo $prod_sold; ?> sold
            </span>
            <?php endif; ?>
          </div>
          
          <p class="price">Rs. <?php echo number_format($p['price'],2); ?></p>
          
          <p class="stock <?php echo $in_stock ? 'in-stock' : 'out-of-stock'; ?>">
            <?php echo $in_stock ? "In stock: " . (int)$p['stock'] : "Out of stock"; ?>
          </p>
          
          <?php if ($in_stock): ?>
            <div class="card-buttons">
              <form method="post" action="add_to_cart.php">
                <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                <input type="hidden" name="qty" value="1">
                <button class="btn-cart" type="submit">Add to Cart</button>
              </form>
              <form method="get" action="checkout.php">
                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                <input type="hidden" name="qty" value="1">
                <button class="btn-buy" type="submit">Buy Now</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php
        endwhile;
    else:
        echo '<div class="no-products"><p>No products found!</p></div>';
    endif;
    ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>