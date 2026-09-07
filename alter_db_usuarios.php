<?php
require_once 'config.php';

try {
    $sql = "ALTER TABLE usuarios 
        ADD COLUMN cpf VARCHAR(20) NULL,
        ADD COLUMN celular VARCHAR(20) NULL,
        ADD COLUMN cep VARCHAR(20) NULL,
        ADD COLUMN estado VARCHAR(2) DEFAULT 'RS',
        ADD COLUMN cidade VARCHAR(100) DEFAULT 'Cachoeirinha',
        ADD COLUMN endereco VARCHAR(255) NULL,
        ADD COLUMN bairro VARCHAR(100) NULL,
        ADD COLUMN numero VARCHAR(20) NULL,
        ADD COLUMN complemento VARCHAR(100) NULL";
        
    $pdo->exec($sql);
    echo "Tabela de usuarios atualizada com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
