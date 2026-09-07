<?php
require_once 'config.php';
$stmt = $pdo->prepare("DELETE FROM cartas WHERE imagem = 'Imagem'");
$stmt->execute();
echo "Deletadas " . $stmt->rowCount() . " cartas com imagem inválida.\n";
?>
