<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if(!isset($_GET['id'])) {
    header("Location: admin_eventos.php");
    exit;
}

$id = (int)$_GET['id'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $banner = trim($_POST['banner']);
    $preco_ingresso = (float)$_POST['preco_ingresso'];
    $valor_premio = (float)$_POST['valor_premio'];
    $data_evento = $_POST['data_evento'];
    
    $stmt = $pdo->prepare("UPDATE eventos SET titulo = ?, descricao = ?, banner = ?, preco_ingresso = ?, valor_premio = ?, data_evento = ? WHERE id = ?");
    $stmt->execute([$titulo, $descricao, $banner, $preco_ingresso, $valor_premio, $data_evento, $id]);
    
    header("Location: admin_eventos.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM eventos WHERE id = ?");
$stmt->execute([$id]);
$evento = $stmt->fetch();

if(!$evento) {
    header("Location: admin_eventos.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento | RocketTCG Admin</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        .form-container { background: var(--card-bg); padding: 30px; border-radius: 15px; border: 1px solid var(--glass-border); max-width: 600px; margin: 40px auto; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #ccc; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #444; background: rgba(255,255,255,0.05); color: #fff; font-size: 1rem; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--accent-color); }
        .header-actions { margin-bottom: 30px; text-align: center; }
        .btn-group { display: flex; gap: 10px; margin-top: 30px; }
    </style>
</head>
<body>

    <header class="navbar admin-navbar">
        <a href="index.php" class="logo" style="text-decoration: none;">
            <ion-icon name="rocket-outline"></ion-icon>
            <h2>Rocket<span>ADMIN</span></h2>
        </a>
        <nav class="nav-links">
            <a href="admin_dashboard.php">Cartas</a>
            <a href="admin_eventos.php" style="color: var(--accent-color);">Eventos</a>
        </nav>
        <div class="nav-actions">
            <span style="font-weight:600; margin-right:15px; color:var(--accent-color);">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
            <a href="logout.php" class="login-link" style="background:var(--card-bg); border:1px solid var(--glass-border);"><ion-icon name="log-out-outline"></ion-icon> Sair</a>
        </div>
    </header>

    <main class="admin-container">
        <div class="form-container">
            <div class="header-actions">
                <h1>Editar Evento</h1>
                <p>Modifique as informações do torneio/evento.</p>
            </div>
            
            <form action="admin_edit_evento.php?id=<?php echo $id; ?>" method="POST">
                <div class="form-group">
                    <label>Título do Torneio / Evento</label>
                    <input type="text" name="titulo" value="<?php echo htmlspecialchars($evento['titulo']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Banner (URL da Imagem)</label>
                    <input type="text" name="banner" value="<?php echo htmlspecialchars($evento['banner']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Data e Hora</label>
                    <input type="datetime-local" name="data_evento" value="<?php echo date('Y-m-d\TH:i', strtotime($evento['data_evento'])); ?>" required>
                </div>
                <div class="form-group">
                    <label>Valor do Ingresso (R$)</label>
                    <input type="number" step="0.01" name="preco_ingresso" value="<?php echo $evento['preco_ingresso']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Valor da Premiação (R$)</label>
                    <input type="number" step="0.01" name="valor_premio" value="<?php echo $evento['valor_premio']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Descrição e Regras</label>
                    <textarea name="descricao" rows="6" required><?php echo htmlspecialchars($evento['descricao']); ?></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="submit" class="btn primary-btn" style="flex: 1; justify-content:center;">Salvar Alterações</button>
                    <a href="admin_eventos.php" class="btn" style="flex: 1; justify-content:center; text-align:center; background: rgba(255,255,255,0.1); color:#fff; text-decoration:none; border-radius:8px; display:flex; align-items:center;">Cancelar</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
