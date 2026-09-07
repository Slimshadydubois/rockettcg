<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['evento_id'])) {
    $evento_id = (int)$_POST['evento_id'];
    $usuario_id = $_SESSION['usuario_id'];
    
    // Verifica se já não marcou
    $stmt = $pdo->prepare("SELECT id FROM eventos_presencas WHERE evento_id = ? AND usuario_id = ?");
    $stmt->execute([$evento_id, $usuario_id]);
    
    if ($stmt->rowCount() == 0) {
        $stmtInsert = $pdo->prepare("INSERT INTO eventos_presencas (evento_id, usuario_id) VALUES (?, ?)");
        $stmtInsert->execute([$evento_id, $usuario_id]);
    }
}

header("Location: eventos.php");
exit;
?>
