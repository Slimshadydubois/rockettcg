<?php
$content = file_get_contents('test_card.txt');
$pos = strpos($content, 'function updatePrice');
echo substr($content, $pos, 1000);
?>
