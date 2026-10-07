<?php
require_once 'config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        total DECIMAL(10,2) NOT NULL,
        frete DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        tipo_entrega VARCHAR(50) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Em preparao',
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pedidos_itens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        carta_id INT NOT NULL,
        quantidade INT NOT NULL,
        preco_unitario DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
        FOREIGN KEY (carta_id) REFERENCES cartas(id) ON DELETE CASCADE
    )");

    echo "Tabelas pedidos e pedidos_itens criadas com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage();
}
?>
