<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Lidar com a exclusão
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM eventos WHERE id = ?")->execute([$id]);
    header("Location: admin_eventos.php");
    exit;
}

// Lidar com a adição
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $banner = trim($_POST['banner']);
    $preco_ingresso = (float)$_POST['preco_ingresso'];
    $valor_premio = (float)$_POST['valor_premio'];
    $data_evento = $_POST['data_evento'];
    
    $stmt = $pdo->prepare("INSERT INTO eventos (titulo, descricao, banner, preco_ingresso, valor_premio, data_evento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $descricao, $banner, $preco_ingresso, $valor_premio, $data_evento]);
    
    header("Location: admin_eventos.php");
    exit;
}

$stmt = $pdo->query("
    SELECT e.*, (SELECT COUNT(*) FROM eventos_presencas WHERE evento_id = e.id) as total_presencas 
    FROM eventos e 
    ORDER BY data_evento DESC
");
$eventos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Eventos | RocketTCG Admin</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        .dashboard-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: var(--card-bg); border-radius: 12px; overflow: hidden; border: 1px solid var(--glass-border); }
        .dashboard-table th, .dashboard-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--glass-border); color: #fff; }
        .dashboard-table th { background: rgba(0,0,0,0.3); color: var(--accent-color); font-weight: 600; }
        .dashboard-table img { width: 100px; height: 50px; object-fit: cover; border-radius: 4px; }
        .action-btn { background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; margin-right: 10px; transition: 0.3s; }
        .action-btn.delete:hover { color: #ff0055; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); }
        .modal-content { background-color: var(--card-bg); margin: 5% auto; padding: 30px; border: 1px solid var(--glass-border); border-radius: 15px; width: 500px; max-width: 90%; position: relative; }
        .close-modal { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close-modal:hover { color: #fff; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #ccc; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #444; background: rgba(255,255,255,0.1); color: #fff; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--accent-color); }
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
        <div class="header-actions">
            <div>
                <h1>Gerenciar Eventos e Torneios</h1>
                <p>Crie ou remova eventos que aparecerão na página principal.</p>
            </div>
            
            <button onclick="document.getElementById('modalEvento').style.display='block'" class="btn primary-btn"><ion-icon name="add-outline"></ion-icon> Novo Evento</button>
        </div>

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Banner</th>
                    <th>Título</th>
                    <th>Data</th>
                    <th>Ingresso</th>
                    <th>Premiação</th>
                    <th>Presenças</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($eventos as $evento): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($evento['banner']); ?>" alt="Banner" onerror="this.src='https://via.placeholder.com/100x50'"></td>
                    <td><strong><?php echo htmlspecialchars($evento['titulo']); ?></strong></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($evento['data_evento'])); ?></td>
                    <td>R$ <?php echo number_format($evento['preco_ingresso'], 2, ',', '.'); ?></td>
                    <td>R$ <?php echo number_format($evento['valor_premio'], 2, ',', '.'); ?></td>
                    <td><span style="background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 12px; font-weight: bold;"><?php echo $evento['total_presencas']; ?></span></td>
                    <td>
                        <a href="admin_edit_evento.php?id=<?php echo $evento['id']; ?>" class="action-btn" title="Editar"><ion-icon name="create-outline"></ion-icon></a>
                        <a href="?delete=<?php echo $evento['id']; ?>" class="action-btn delete" title="Excluir" onclick="return confirm('Tem certeza?');"><ion-icon name="trash-outline"></ion-icon></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($eventos)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px;">Nenhum evento criado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <!-- Modal Novo Evento -->
    <div id="modalEvento" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="document.getElementById('modalEvento').style.display='none'">&times;</span>
            <h2>Criar Novo Evento</h2>
            <form action="admin_eventos.php" method="POST" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Título do Torneio / Evento</label>
                    <input type="text" name="titulo" required>
                </div>
                <div class="form-group">
                    <label>Banner (URL da Imagem)</label>
                    <input type="text" name="banner" placeholder="Ex: assets/torneio_banner.jpg" required>
                </div>
                <div class="form-group">
                    <label>Data e Hora</label>
                    <input type="datetime-local" name="data_evento" required>
                </div>
                <div class="form-group">
                    <label>Valor do Ingresso (R$)</label>
                    <input type="number" step="0.01" name="preco_ingresso" required>
                </div>
                <div class="form-group">
                    <label>Valor da Premiação (R$)</label>
                    <input type="number" step="0.01" name="valor_premio" required>
                </div>
                <div class="form-group">
                    <label>Descrição e Regras</label>
                    <textarea name="descricao" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn primary-btn" style="width: 100%; justify-content:center;">Criar Evento</button>
            </form>
        </div>
    </div>
    
    <script>
        window.onclick = function(event) {
            var modal = document.getElementById('modalEvento');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
