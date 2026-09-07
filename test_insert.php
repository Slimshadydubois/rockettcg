<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
require 'D:\xampp\htdocs\rockettcg\config.php';
try {
    $stmt = $pdo->prepare('INSERT INTO cartas (nome, preco, categoria, imagem, descricao, edicao, estado, raridade, data_lancamento, tipo_carta, tipo_energia, nacionalidade) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Teste', 10.50, 'pokemon', 'http://url.com', 'desc', 'edi', 'Mint', 'Comum', null, 'tipo', 'energia', 'PT-BR']);
    echo 'Insert OK.';
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
