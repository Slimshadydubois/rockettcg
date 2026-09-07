<?php
require_once 'config.php';

try {
    $sql1 = "CREATE TABLE IF NOT EXISTS eventos (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        titulo VARCHAR(255) NOT NULL, 
        descricao TEXT, 
        banner VARCHAR(500), 
        preco_ingresso DECIMAL(10,2), 
        valor_premio DECIMAL(10,2), 
        data_evento DATETIME, 
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql1);
    
    $sql2 = "CREATE TABLE IF NOT EXISTS eventos_presencas (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        evento_id INT NOT NULL, 
        usuario_id INT NOT NULL, 
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
        FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE, 
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE, 
        UNIQUE(evento_id, usuario_id)
    )";
    $pdo->exec($sql2);
    
    echo "Tabelas criadas com sucesso!";
} catch(PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
