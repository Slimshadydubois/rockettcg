<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erro de validação CSRF.");
    }
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM cartas WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: admin_dashboard.php");
exit;
?>
