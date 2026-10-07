<?php
require_once 'config.php';

try {
    // Adicionar colunas para suporte a cartas Reverse (Foil) e similares
    $pdo->exec("ALTER TABLE cartas ADD COLUMN IF NOT EXISTS preco_reverse DECIMAL(10,2) NULL;");
    $pdo->exec("ALTER TABLE cartas ADD COLUMN IF NOT EXISTS estoque_reverse INT DEFAULT 0;");
    $pdo->exec("ALTER TABLE cartas ADD COLUMN IF NOT EXISTS nome_variante VARCHAR(50) NULL;");

    echo "<h1>Sucesso!</h1>";
    echo "<p>As colunas 'preco_reverse', 'estoque_reverse' e 'nome_variante' foram adicionadas com sucesso à tabela de cartas.</p>";
    echo "<a href='admin_import_cards.php'>Voltar para importação</a>";

} catch (PDOException $e) {
    echo "<h1>Erro</h1>";
    echo "<p>Erro ao atualizar o banco de dados: " . $e->getMessage() . "</p>";
}
?>
