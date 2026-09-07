<?php
require 'config.php';
$_SESSION['usuario_id'] = 1;
$_POST['action'] = 'update_cart';
$_POST['cart_id'] = 1; // Assuming 1 exists, we just want to see if it syntax errors
$_POST['qty'] = 2;
$_SERVER['HTTP_REFERER'] = 'cart.php';
ob_start();
include 'cart_action.php';
$output = ob_get_clean();
echo "Success, Output: $output";
?>
