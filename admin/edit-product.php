<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
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
$page_title = 'Edit Product';

// Ensure UPLOADS_DIR exists
if (!defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', __DIR__ . '/../uploads/');
}
if (!is_dir(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}

// Get product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    flash_set('error', 'Invalid product ID.');
    redirect('products.php');
}

// Fetch existing product
$res = mysqli_query($conn, "SELECT * FROM products WHERE id={$id} LIMIT 1");
if (!$p = mysqli_fetch_assoc($res)) {
    flash_set('error', 'Product not found.');
    redirect('products.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    $author = mysqli_real_escape_string($conn, trim($_POST['author'] ?? ''));
    $desc = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $image_name = $p['image'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image']['tmp_name'];
        $basename = basename($_FILES['image']['name']);
        $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $basename);

        if (!move_uploaded_file($tmp, UPLOADS_DIR . $image_name)) {
            flash_set('error', 'Failed to upload image.');
            redirect("edit-product.php?id={$id}");
        }
    }

    // Update product in DB
    $query = "UPDATE products SET title='{$title}', author='{$author}', description='{$desc}', price={$price}, stock={$stock}, image='{$image_name}' WHERE id={$id}";
    if (mysqli_query($conn, $query)) {
        flash_set('success', 'Product updated successfully.');
        redirect('admin/products.php'); // Admin products page
    } else {
        flash_set('error', 'Failed to update product: ' . mysqli_error($conn));
        redirect("edit-product.php?id={$id}");
    }
}

// Include header
include '../includes/header.php';
?>

<section class="container">
  <h2>Edit Product</h2>
  <form method="post" enctype="multipart/form-data" class="admin-form">
    <label>Title</label>
    <input class="input" name="title" value="<?php echo e($p['title']); ?>" required>

    <label>Author</label>
    <input class="input" name="author" value="<?php echo e($p['author']); ?>" required>

    <label>Description</label>
    <textarea class="input" name="description"><?php echo e($p['description']); ?></textarea>

    <label>Price (Rs.)</label>
    <input class="input" name="price" value="<?php echo e($p['price']); ?>" type="number" step="0.01" min="0" required>

    <label>Stock</label>
    <input class="input" name="stock" value="<?php echo e($p['stock']); ?>" type="number" min="0" required>

    <label>Image (leave blank to keep current)</label>
    <input class="input" type="file" name="image" accept="image/*">

    <button class="input btn-add" type="submit">Save Product</button>
  </form>
</section>

<style>
/* Admin edit product form */
.admin-form {
    max-width: 500px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.admin-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.admin-form input.input,
.admin-form textarea.input {
    width: 100%;
    padding: 8px 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 1rem;
}

.admin-form textarea.input {
    min-height: 80px;
}

.admin-form button.btn-add {
    background-color: #1e3d59;
    color: #fff;
    padding: 10px 20px;
    font-size: 1rem;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.admin-form button.btn-add:hover {
    background-color: #ff6e40;
}
</style>

<?php include '../includes/footer.php'; ?>
