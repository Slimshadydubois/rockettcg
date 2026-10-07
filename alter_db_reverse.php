<?php
require_once 'config.php';

try {
    $queries = [
        "ALTER TABLE cartas ADD COLUMN preco_reverse DECIMAL(10,2) NULL;",
        "ALTER TABLE cartas ADD COLUMN estoque_reverse INT DEFAULT 0;",
        "ALTER TABLE cartas ADD COLUMN nome_variante VARCHAR(50) NULL;"
    ];

    foreach ($queries as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Se o erro for 1060 (Coluna já existe), a gente ignora.
            if (strpos($e->getMessage(), '1060') === false && strpos($e->getMessage(), 'Duplicate column') === false) {
                throw $e;
            }
        }
    }

    echo "<h1>Sucesso!</h1>";
    echo "<p>As colunas 'preco_reverse', 'estoque_reverse' e 'nome_variante' foram adicionadas com sucesso à tabela de cartas.</p>";
    echo "<a href='admin_import_cards.php'>Voltar para importação</a>";

} catch (PDOException $e) {
    echo "<h1>Erro</h1>";
    echo "<p>Erro ao atualizar o banco de dados: " . $e->getMessage() . "</p>";
}
?>
