<?php
require_once 'config.php';
require_once 'functions.php';

$conn = db_connect();
$page_title = "Home";
include 'includes/header.php';
?>




<style>
/* Page Background */
body {
  position: relative;
  min-height: 100vh;
}

body::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: url('<?php echo UPLOADS_URL; ?>bookbackground.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  background-attachment: fixed;
  z-index: -2;
}

body::after {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  z-index: -1;
}

/* Hero Section */
.hero {
  background: rgba(102, 126, 234, 0.15);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  text-align: center;
  padding: 80px 20px;
  margin-bottom: 60px;
  position: relative;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
  opacity: 0.3;
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 800px;
  margin: 0 auto;
}

.hero h1 {
  font-size: 3rem;
  font-weight: 700;
  margin: 0 0 20px 0;
  letter-spacing: -1px;
}

.hero p {
  font-size: 1.25rem;
  margin: 0;
  opacity: 0.95;
  font-weight: 300;
}

/* Home Section */
.home-section {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px 60px 20px;
}

.home-section .section-header {
  margin-bottom: 40px;
  text-align: center;
}

.home-section .section-header h2 {
  font-size: 2.5rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 10px 0;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.home-section .section-subtitle {
  font-size: 1.1rem;
  color: #e5e7eb;
  margin: 0;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
}

.home-section .products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 30px;
  margin-bottom: 40px;
}

.home-section .product-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  height: 100%;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.home-section .product-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

.home-section .product-image-wrapper {
  position: relative;
  width: 100%;
  height: 320px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  overflow: hidden;
}

.home-section .product-card .product-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
  transition: transform 0.4s ease;
}

.home-section .product-card:hover .product-image {
  transform: scale(1.08);
}

.home-section .product-card .product-info {
  padding: 24px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.home-section .product-card .product-info h3 {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 10px 0;
  line-height: 1.4;
  min-height: 2.8em;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.home-section .product-card .author {
  font-size: 0.9rem;
  color: #6c757d;
  margin: 0 0 16px 0;
  font-style: italic;
}

.home-section .product-card .price {
  font-size: 1.6rem;
  font-weight: 800;
  color: #667eea;
  margin: 0 0 12px 0;
}

.home-section .product-card .stock {
  font-size: 0.85rem;
  margin: 0 0 20px 0;
  padding: 8px 14px;
  border-radius: 8px;
  display: inline-block;
  width: fit-content;
  font-weight: 600;
}

.home-section .product-card .stock.in-stock {
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  color: #065f46;
}

.home-section .product-card .stock.out-of-stock {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #991b1b;
}

.home-section .cart-form {
  display: flex;
  gap: 12px;
  align-items: center;
  margin-top: auto;
}

.home-section .cart-form .qty-input {
  width: 70px;
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  text-align: center;
  transition: all 0.2s ease;
}

.home-section .cart-form .qty-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.home-section .btn {
  flex: 1;
  padding: 12px 20px;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.home-section .add-cart {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: #fff;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.home-section .add-cart:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.home-section .buy-now {
  background: #fff;
  color: #667eea;
  border: 2px solid #667eea;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.home-section .buy-now:hover {
  background: #f0f4ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.home-section .out-stock {
  color: #991b1b;
  font-weight: 700;
  font-size: 0.95rem;
  text-align: center;
  padding: 12px;
  background: #fee2e2;
  border-radius: 10px;
}

.home-section .no-products {
  text-align: center;
  padding: 80px 20px;
  color: #6c757d;
  font-size: 1.2rem;
}

@media (max-width: 768px) {
  .hero h1 {
    font-size: 2rem;
  }

  .hero p {
    font-size: 1rem;
  }

  .home-section .section-header h2 {
    font-size: 2rem;
  }

  .home-section .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 20px;
  }

  .home-section .product-card .product-info {
    padding: 18px;
  }

  .home-section .product-card .product-info h3 {
    font-size: 1rem;
  }

  .home-section .product-card .price {
    font-size: 1.3rem;
  }

  .home-section .cart-form {
    flex-direction: column;
  }

  .home-section .cart-form .qty-input {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .hero {
    padding: 60px 20px;
  }

  .home-section .products-grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }
}
</style>

<section class="hero">
  <div class="hero-content">
    <h1>Discover Your Next Great Read</h1>
    <p>Explore our curated collection of timeless classics and contemporary bestsellers</p>
  </div>
</section>

<section class="home-section">
  <div class="section-header">
    <h2>Featured Collection</h2>
    <p class="section-subtitle">Handpicked books just for you</p>
  </div>

  <?php
  // Fetch products
  $res = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");

  if ($res && mysqli_num_rows($res) > 0):
    echo '<div class="products-grid">';
    while ($row = mysqli_fetch_assoc($res)):
      // Safe values
      $title = e($row['title']);
      $author = e($row['author']);
      $price = number_format($row['price'], 2);
      $stock = (int)$row['stock'];
      $in_stock = $stock > 0;

      // Image URL
      $img = !empty($row['image']) ? UPLOADS_URL . e($row['image']) : BASE_URL . 'placeholder.png';
      ?>
      <div class="product-card">
        <?php if(is_logged_in()): ?>
          <button class="wishlist-btn <?php echo is_in_wishlist($conn, $_SESSION['user_id'], $row['id']) ? 'in-wishlist' : ''; ?>" 
                  data-product-id="<?php echo (int)$row['id']; ?>">
            <?php echo is_in_wishlist($conn, $_SESSION['user_id'], $row['id']) ? '❤' : '♡'; ?>
          </button>
        <?php endif; ?>
        
        <div class="product-image-wrapper">
          <a href="<?php echo BASE_URL; ?>product_detail.php?id=<?php echo (int)$row['id']; ?>">
            <img src="<?php echo e($img); ?>" alt="<?php echo $title; ?>" class="product-image">
          </a>
        </div>
        <div class="product-info">
          <h3>
            <a href="<?php echo BASE_URL; ?>product_detail.php?id=<?php echo (int)$row['id']; ?>" style="color: inherit; text-decoration: none;">
              <?php echo $title; ?>
            </a>
          </h3>
          <?php if ($author): ?><p class="author">By <?php echo $author; ?></p><?php endif; ?>
          
          <?php 
          // Get rating, reviews and sales for this product
          $prod_rating = get_average_rating($conn, $row['id']);
          $prod_reviews = get_review_count($conn, $row['id']);
          
          // Get sales count
          $sales_q = "SELECT SUM(oi.qty) as sold FROM order_items oi 
                      JOIN orders o ON oi.order_id = o.id 
                      WHERE oi.product_id = {$row['id']} AND o.status IN ('completed', 'delivered')";
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
          
          <p class="price">Rs. <?php echo $price; ?></p>
          <p class="stock <?php echo $in_stock ? 'in-stock' : 'out-of-stock'; ?>">
            <?php echo $in_stock ? "In Stock: {$stock}" : "Out of Stock"; ?>
          </p>

          <?php if ($in_stock): ?>
            <form method="post" action="<?php echo BASE_URL; ?>add_to_cart.php" class="cart-form">
              <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
              <input type="number" name="qty" value="1" min="1" max="<?php echo $stock; ?>" class="qty-input" id="qty-<?php echo $row['id']; ?>">
              <button type="submit" class="btn add-cart">Add to Cart</button>
              <a href="#" onclick="buyNow(<?php echo $row['id']; ?>, <?php echo $stock; ?>); return false;" class="btn buy-now">Buy Now</a>
            </form>
          <?php else: ?>
            <p class="out-stock">Currently Unavailable</p>
          <?php endif; ?>

        </div>
      </div>
      <?php
    endwhile;
    echo '</div>';
  else:
    echo '<div class="no-products"><p>No products available at the moment.</p></div>';
  endif;
  ?>
</section>

<script>
function buyNow(productId, maxStock) {
    const qtyInput = document.getElementById('qty-' + productId);
    const qty = qtyInput ? parseInt(qtyInput.value) : 1;
    
    // Validate quantity
    if(qty < 1 || qty > maxStock) {
        alert('Please enter a valid quantity');
        return;
    }
    
    // Redirect to checkout with product and quantity
    window.location.href = '/bookstore_project/checkout.php?product_id=' + productId + '&qty=' + qty;
}
</script>

<?php include 'includes/footer.php'; ?>