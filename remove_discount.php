<?php
// remove_discount.php - Remove discount code from session
if(session_status()===PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'functions.php';

if(isset($_SESSION['discount_code'])){
    unset($_SESSION['discount_code']);
    flash_set('success', 'Discount code removed');
}

redirect('cart.php');
?>
