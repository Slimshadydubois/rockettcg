<?php
require 'config.php';
$stmt = $pdo->query('SHOW COLUMNS FROM cartas');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
