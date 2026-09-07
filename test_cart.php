<?php
require 'config.php';
$_POST['ajax'] = '1';
$_SESSION['usuario_id'] = 1;
$_POST['action'] = 'add_wishlist';
$_POST['id'] = 260;
include 'cart_action.php';
?>
