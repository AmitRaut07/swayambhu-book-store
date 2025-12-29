<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Only use existing $conn
$user_name = '';
$user_email = '';
$is_admin = false;

if(is_logged_in() && isset($conn)) {
    $uid = (int)$_SESSION['user_id'];
    $res_user = mysqli_query($conn, "SELECT username, email FROM users WHERE id={$uid} LIMIT 1");
    if($row_user = mysqli_fetch_assoc($res_user)) {
        $user_name = $row_user['username'];
        $user_email = $row_user['email'];
        
        // Check if admin
        if($user_email === 'admin@example.com') {
            $is_admin = true;
        }
    }
}

// Cart count (only for non-admin users)
$cart_count = (!$is_admin && isset($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;

// Wishlist count (only for logged-in non-admin users)
$wishlist_count = 0;
if(!$is_admin && is_logged_in() && isset($conn)) {
    $wishlist_count = get_wishlist_count($conn, $_SESSION['user_id']);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo e(SITE_TITLE) . ' - ' . (isset($page_title)?e($page_title):'Online Bookstore'); ?></title>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>styles.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/override.css?v=<?php echo time(); ?>">
<script src="<?php echo BASE_URL; ?>js/main.js?v=<?php echo time(); ?>" defer></script>
<script src="<?php echo BASE_URL; ?>js/search.js?v=<?php echo time(); ?>" defer></script>
<script src="<?php echo BASE_URL; ?>js/products.js?v=<?php echo time(); ?>" defer></script>
</head>
<body>
<header class="site-header">
  <div class="header-container">
    <!-- Top Row: Brand, Search, Mobile Menu -->
    <div class="header-top">
      <h1 class="brand">
        <a href="<?php echo $is_admin ? BASE_URL . 'admin/dashboard.php' : BASE_URL . 'index.php'; ?>">
          <?php echo e(SITE_TITLE); ?>
        </a>
      </h1>
      
      <?php if(!$is_admin): ?>
      <!-- Search Bar -->
      <div class="search-container">
        <div class="search-wrapper">
          <svg class="search-icon" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <input type="text" id="searchInput" placeholder="Search books, authors..." autocomplete="off">
        </div>
        <div id="searchResults" class="search-results"></div>
      </div>
      <?php endif; ?>
      
      <!-- Mobile Menu Button -->
      <button id="mobileMenuBtn" class="mobile-menu-btn" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
    
    <!-- Bottom Row: Navigation Links -->
    <nav class="main-nav">
      <?php if($is_admin): ?>
        <?php
        // Get pending orders count for admin notification
        $pending_count_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='pending'");
        $pending_notification_count = 0;
        if($pending_count_res){
            $pending_row = mysqli_fetch_assoc($pending_count_res);
            $pending_notification_count = (int)$pending_row['count'];
        }
        ?>
        <!-- Admin Navigation -->
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a>
        <a href="<?php echo BASE_URL; ?>admin/pending-orders.php" style="position: relative;">
          Pending Orders
          <?php if($pending_notification_count > 0): ?>
            <span style="position: absolute; top: -8px; right: -10px; background: #dc3545; color: #fff; border-radius: 10px; padding: 2px 6px; font-size: 0.75rem; font-weight: 700; min-width: 18px; text-align: center;"><?php echo $pending_notification_count; ?></span>
          <?php endif; ?>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/products.php">Manage Products</a>
        <a href="<?php echo BASE_URL; ?>admin/orders.php">Orders</a>
        <a href="<?php echo BASE_URL; ?>admin/users.php">Users</a>
        <div class="nav-spacer"></div>
        <?php if($user_name): ?><span class="user-name">Admin: <?php echo e($user_name); ?></span><?php endif; ?>
        <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a>
      <?php else: ?>
        <!-- Regular User Navigation -->
        <a href="<?php echo BASE_URL; ?>index.php">Home</a>
        <a href="<?php echo BASE_URL; ?>products.php">Books</a>
        
        <div class="nav-spacer"></div>
        
        <?php if(is_logged_in()): ?>
          <a href="<?php echo BASE_URL; ?>wishlist.php" class="nav-icon-link">
            <span class="icon">♡</span>
            <span class="nav-text">Wishlist</span>
            <?php if($wishlist_count > 0): ?>
              <span class="badge" id="wishlistCount"><?php echo $wishlist_count; ?></span>
            <?php endif; ?>
          </a>
        <?php endif; ?>
        
        <div class="cart-dropdown-wrapper">
          <a href="<?php echo BASE_URL; ?>cart.php" class="nav-icon-link cart-link">
            <span class="icon">🛒</span>
            <span class="nav-text">Cart</span>
            <?php if($cart_count > 0): ?>
              <span class="badge" id="cartCount"><?php echo $cart_count; ?></span>
            <?php endif; ?>
          </a>
          <div class="cart-preview-dropdown" id="cartPreview"></div>
        </div>

        <?php if(is_logged_in()): ?>
          <a href="<?php echo BASE_URL; ?>profile.php">Account</a>
          <?php if($user_name): ?><span class="user-name">Hi, <?php echo e($user_name); ?></span><?php endif; ?>
          <a href="<?php echo BASE_URL; ?>logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
          <a href="<?php echo BASE_URL; ?>login.php" class="btn-login">Login</a>
          <a href="<?php echo BASE_URL; ?>register.php" class="btn-register">Register</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>
  </div>
</header>

<script>
// Cart preview on hover
document.addEventListener('DOMContentLoaded', function() {
    const cartLink = document.querySelector('.cart-link');
    const cartPreview = document.getElementById('cartPreview');
    const cartWrapper = document.querySelector('.cart-dropdown-wrapper');
    
    if(cartLink && cartPreview && cartWrapper) {
        let hideTimeout;
        
        cartWrapper.addEventListener('mouseenter', function() {
            clearTimeout(hideTimeout);
            loadCartPreview();
        });
        
        cartWrapper.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(() => {
                cartPreview.style.display = 'none';
            }, 300);
        });
        
        function loadCartPreview() {
            fetch('<?php echo BASE_URL; ?>cart_preview.php')
                .then(response => response.text())
                .then(html => {
                    cartPreview.innerHTML = html;
                    cartPreview.style.display = 'block';
                })
                .catch(error => {
                    console.error('Cart preview error:', error);
                });
        }
    }
});
</script>

<main class="container">
<?php
if(function_exists('flash_get')) {
    if($msg=flash_get('success')) echo "<div class='flash success'>".e($msg)."</div>";
    if($msg=flash_get('error')) echo "<div class='flash error'>".e($msg)."</div>";
}
?>