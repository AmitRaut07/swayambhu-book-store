<?php
// Start session if not already started
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Include config and functions
require_once '../config.php';
require_once '../functions.php';

// Ensure user is admin
require_admin();

// DB connection
$conn = db_connect();

// Page title
$page_title = 'Manage Products';

// Fetch products
$res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

// Include header
include '../includes/header.php';
?>

<section class="container">
  <h2>Manage Products</h2>

  <!-- Add Product button -->
  <a href="add-product.php" class="btn add-product">+ Add New Product</a>

  <div class="products-grid admin-grid">
    <?php if ($res && mysqli_num_rows($res) > 0): ?>
      <?php while ($p = mysqli_fetch_assoc($res)): ?>
        <div class="product-card">
          <?php $img = !empty($p['image']) ? UPLOADS_URL . e($p['image']) : BASE_URL . 'placeholder.png'; ?>
          <img src="<?php echo e($img); ?>" alt="<?php echo e($p['title']); ?>" class="product-image">
          <h3><?php echo e($p['title']); ?></h3>
          <p class="muted">By <?php echo e($p['author']); ?></p>
          <p><strong>Rs. <?php echo number_format($p['price'], 2); ?></strong></p>
          <div class="card-buttons">
            <a href="edit-product.php?id=<?php echo (int) $p['id']; ?>" class="btn btn-edit">Edit</a>
            <a href="delete-product.php?id=<?php echo (int) $p['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure?');">Delete</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No products found.</p>
    <?php endif; ?>
  </div>
</section>

<style>
/* Admin products page */
.btn.add-product {
    display: inline-block;
    margin-bottom: 20px;
    padding: 10px 20px;
    background-color: #1e3d59;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background-color 0.3s;
}

.btn.add-product:hover {
    background-color: #ff6e40;
}

/* Product card styling */
.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.product-card {
    background-color: #fff;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    border: 1px solid #ddd;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.product-card img.product-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    margin-bottom: 10px;
    border-radius: 5px;
}

.card-buttons {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.card-buttons .btn-edit,
.card-buttons .btn-delete {
    flex: 1;
    padding: 8px;
    border-radius: 5px;
    text-decoration: none;
    color: #fff;
    font-weight: 500;
    text-align: center;
}

.btn-edit {
    background-color: #28a745;
}

.btn-edit:hover {
    background-color: #218838;
}

.btn-delete {
    background-color: #dc3545;
}

.btn-delete:hover {
    background-color: #c82333;
}
</style>

<?php include '../includes/footer.php'; ?>
