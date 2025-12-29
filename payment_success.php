<?php
require_once 'includes/header.php';
require_once 'config.php';
require_once 'functions.php';
$page_title = 'Payment Success';
$order_id = isset($_GET['order_id'])?(int)$_GET['order_id']:0;
?>
<section>
  <h2>Payment / Order Success</h2>
  <p>Thank you. Your order #<?php echo (int)$order_id; ?> has been received. We are located in <?php echo e(SITE_LOCATION); ?>. You will receive updates in your dashboard.</p>
  <p><a href="profile.php">Go to dashboard</a></p>
</section>
<?php require_once 'includes/footer.php'; ?>