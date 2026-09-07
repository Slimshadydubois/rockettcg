<?php
require 'config.php';
$_GET['id'] = 260; // Tangela
ob_start();
include 'card.php';
$html = ob_get_clean();
file_put_contents('test_card.html', $html);
echo "Done";
?>
