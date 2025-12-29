<?php
require_once 'config.php';
require_once 'functions.php';

// DB connection
$conn = db_connect();

// Get product ID from query
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product
$res = mysqli_query($conn, "SELECT * FROM products WHERE id={$id}");
if(!$res || !$product = mysqli_fetch_assoc($res)){
    flash_set('error','Product not found.');
    redirect('products.php');
}

// Page title
$page_title = $product['title'];

include 'includes/header.php';
?>

<section>
  <div class="card">
    <?php 
    // Determine product image
    $img = !empty($product['image']) ? UPLOADS_URL . e($product['image']) : BASE_URL . 'placeholder.png'; 
    ?>
    <img class="product-image" src="<?php echo e($img); ?>" alt="<?php echo e($product['title']); ?>">

    <h2><?php echo e($product['title']); ?></h2>

    <?php if(!empty($product['author'])): ?>
        <p class="muted">By <?php echo e($product['author']); ?></p>
    <?php endif; ?>

    <?php if(!empty($product['description'])): ?>
        <p><?php echo nl2br(e($product['description'])); ?></p>
    <?php endif; ?>

    <p><strong>Rs. <?php echo number_format($product['price'],2); ?></strong></p>

    <?php if($product['stock'] > 0): ?>
    <form method="post" action="add_to_cart.php">
      <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>">
      <label>Quantity</label>
      <input class="input" type="number" name="qty" value="1" min="1" max="<?php echo (int)$product['stock']; ?>">
      <button class="input" type="submit">Add to cart</button>
    </form>
    <?php else: ?>
      <p style="color:red;">Out of stock</p>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
