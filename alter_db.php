<?php
require_once 'config.php';
try {
    $pdo->exec("ALTER TABLE cartas ADD COLUMN preco_reverse DECIMAL(10,2) DEFAULT NULL");
    $pdo->exec("ALTER TABLE cartas ADD COLUMN estoque_reverse INT DEFAULT 0");
    $pdo->exec("ALTER TABLE carrinho_itens ADD COLUMN variante VARCHAR(50) DEFAULT 'normal'");
    echo "Tabelas atualizadas com sucesso.";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
