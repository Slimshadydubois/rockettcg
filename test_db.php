<?php
require 'config.php';
$stmt = $pdo->query("SELECT nome, preco, preco_reverse, tipo_energia, raridade FROM cartas LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
