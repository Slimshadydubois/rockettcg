<?php
require_once 'config.php';
try {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, cpf, celular, email, senha, tipo) VALUES ('Pedro', '123', '123', 'pedro.brum69@gmail.com', 'test', 'admin')");
    $stmt->execute();
    echo "INSERIDO COM SUCESSO";
} catch (PDOException $e) {
    echo "ERRO PDO: " . $e->getMessage();
}
?>
