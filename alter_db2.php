<?php
require_once 'config.php';
try {
    $pdo->exec("ALTER TABLE cartas ADD COLUMN nome_variante VARCHAR(50) DEFAULT NULL");
    echo "Coluna nome_variante adicionada com sucesso.";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
