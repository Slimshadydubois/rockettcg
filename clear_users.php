<?php
require_once 'config.php';

try {
    // Desativar a checagem de chaves estrangeiras temporariamente
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Deletar todas as contas e limpar IDs (truncate)
    // Se quiser apenas deletar e manter a numeração de IDs, use DELETE FROM usuarios
    $pdo->exec("TRUNCATE TABLE usuarios;");
    
    // Deletar os itens associados em outras tabelas caso truncate não faça o cascade automático
    $pdo->exec("TRUNCATE TABLE carrinho_itens;");
    $pdo->exec("TRUNCATE TABLE favoritos;");
    $pdo->exec("TRUNCATE TABLE pedidos;");
    $pdo->exec("TRUNCATE TABLE pedidos_itens;");

    // Reativar a checagem de chaves
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "<h1>Sucesso!</h1>";
    echo "<p>Todas as contas (e os dados atrelados a elas, como carrinho e pedidos) foram removidas com sucesso do banco de dados.</p>";
    echo "<a href='index.php'>Voltar para a Home</a>";

} catch (PDOException $e) {
    echo "Erro ao limpar o banco: " . $e->getMessage();
}
?>
