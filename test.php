<?php
require 'config.php';
$_GET['cat'] = 'pokemon';
ob_start();
include 'category.php';
$html = ob_get_clean();
file_put_contents('test2.html', $html);
echo "HTML gerado em test2.html\n";
?>
