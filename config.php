<?php
// config.php
session_name('ROCKETTCG_SESSION'); // Isola a sessão deste projeto
session_start(); // Inicia a sessão para controle de usuários logados

// Geração do token CSRF para segurança de formulários
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'tcg_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: ''; // Padrão do XAMPP local
$port = getenv('DB_PORT') ?: '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostra erros como exceções
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna dados como array associativo
    PDO::ATTR_EMULATE_PREPARES   => false, // Mais segurança contra SQL Injection
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}

$total_cart_items = 0;
if(isset($_SESSION['usuario_id'])) {
    $stmtCount = $pdo->prepare("SELECT SUM(quantidade) FROM carrinho_itens WHERE usuario_id = ?");
    $stmtCount->execute([$_SESSION['usuario_id']]);
    $total_cart_items = (int)$stmtCount->fetchColumn();
}
?>
