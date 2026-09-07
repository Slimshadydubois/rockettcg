<?php
require_once 'config.php';
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE carrinho_itens;");
$pdo->exec("TRUNCATE TABLE favoritos;");
$pdo->exec("TRUNCATE TABLE cartas;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
echo "Cartas deletadas com sucesso.";
?>
