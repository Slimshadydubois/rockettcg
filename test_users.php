<?php
require 'D:\xampp\htdocs\rockettcg\config.php';
try {
    $stmt = $pdo->query('DESCRIBE cartas');
    print_r($stmt->fetchAll());
} catch(Exception $e) {
    echo $e->getMessage();
}
?>
