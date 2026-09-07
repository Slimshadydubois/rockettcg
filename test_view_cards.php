<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM cartas ORDER BY id DESC LIMIT 5");
$cartas = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($cartas);
?>
