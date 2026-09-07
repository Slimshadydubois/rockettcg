<?php
$_GET['cat'] = 'pokemon';
ob_start();
include 'category.php';
$html = ob_get_clean();

if (strpos($html, 'data-rarity="Comum,Holografica"') !== false) {
    echo "Holografica is present in HTML.\n";
} else {
    echo "Holografica is MISSING.\n";
}
?>
