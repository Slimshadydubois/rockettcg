<?php
require 'D:\xampp\htdocs\rockettcg\config.php';
try {
    print_r($pdo->query('SHOW CREATE TABLE carrinho_itens')->fetchAll());
} catch(Exception $e) {
    echo $e->getMessage();
}
?>
