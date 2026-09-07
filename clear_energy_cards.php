<?php
require 'config.php';

$stmt = $pdo->prepare("DELETE FROM cartas WHERE edicao = 'Energias/Ajudas' OR tipo_carta = 'Energia' OR descricao = 'Carta de Energia/Ajuda'");
$stmt->execute();

echo "Cartas removidas: " . $stmt->rowCount() . "\n";
?>
