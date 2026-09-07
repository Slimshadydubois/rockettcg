<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($searchQuery !== '') {
    $stmt = $pdo->prepare("SELECT * FROM cartas WHERE nome LIKE ? OR id = ? ORDER BY id DESC");
    $stmt->execute(['%' . $searchQuery . '%', (int)$searchQuery]);
    $cartas = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM cartas ORDER BY id DESC");
    $cartas = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        .dashboard-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: var(--card-bg); border-radius: 12px; overflow: hidden; border: 1px solid var(--glass-border); }
        .dashboard-table th, .dashboard-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--glass-border); color: #fff; }
        .dashboard-table th { background: rgba(0,0,0,0.3); color: var(--accent-color); font-weight: 600; }
        .dashboard-table img { width: 50px; height: 70px; object-fit: cover; border-radius: 4px; }
        .action-btn { background: none; border: none; color: #fff; font-size: 1.2rem; cursor: pointer; margin-right: 10px; transition: 0.3s; }
        .action-btn.edit:hover { color: #ff5252; }
        .action-btn.delete:hover { color: #ff0055; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>

    <header class="navbar admin-navbar">
        <a href="index.php" class="logo" style="text-decoration: none;">
            <ion-icon name="rocket-outline"></ion-icon>
            <h2>Rocket<span>ADMIN</span></h2>
        </a>
        <nav class="nav-links">
            <a href="admin_dashboard.php" style="color: var(--accent-color);">Cartas</a>
            <a href="admin_eventos.php">Eventos</a>
        </nav>
        <div class="nav-actions">
            <span style="font-weight:600; margin-right:15px; color:var(--accent-color);">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
            <a href="logout.php" class="login-link" style="background:var(--card-bg); border:1px solid var(--glass-border);"><ion-icon name="log-out-outline"></ion-icon> Sair</a>
        </div>
    </header>

    <main class="admin-container">
        <div class="header-actions">
            <div>
                <h1>Painel Administrativo</h1>
                <p>Gerencie as cartas do catálogo.</p>
            </div>
            
            <form action="admin_dashboard.php" method="GET" style="display: flex; gap: 10px; align-items: center; background: rgba(0,0,0,0.2); padding: 8px 15px; border-radius: 8px; border: 1px solid var(--glass-border);">
                <ion-icon name="search-outline" style="color: var(--text-muted); font-size: 1.2rem;"></ion-icon>
                <input type="text" name="q" placeholder="Buscar por Nome ou ID..." value="<?php echo htmlspecialchars($searchQuery); ?>" style="background: transparent; border: none; color: #fff; outline: none; font-family: 'Outfit'; font-size: 1rem; width: 250px;">
                <button type="submit" style="display:none;"></button>
            </form>
            <div style="display: flex; gap: 10px;">
                <a href="admin_import_cards.php" class="btn primary-btn" style="text-decoration: none; background: #4caf50; color: #fff;"><ion-icon name="document-text-outline"></ion-icon> Importar Planilha</a>
                <a href="admin_add_card.php" class="btn primary-btn" style="text-decoration: none;"><ion-icon name="add-outline"></ion-icon> Nova Carta</a>
            </div>
        </div>

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Imagem</th>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($cartas as $carta): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($carta['imagem']); ?>" alt="Imagem"></td>
                    <td>#<?php echo $carta['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($carta['nome']); ?></strong><br><small style="color:var(--text-muted);"><?php echo htmlspecialchars($carta['edicao']); ?></small></td>
                    <td style="text-transform: capitalize;"><?php echo htmlspecialchars($carta['categoria']); ?></td>
                    <?php 
                        $preco_exibicao = $carta['preco'] > 0 ? $carta['preco'] : (isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0 ? $carta['preco_reverse'] : 0);
                        $estoque_total = $carta['estoque'] + (isset($carta['estoque_reverse']) ? $carta['estoque_reverse'] : 0);
                    ?>
                    <td>R$ <?php echo number_format($preco_exibicao, 2, ',', '.'); ?></td>
                    <td><?php echo $estoque_total; ?></td>
                    <td>
                        <a href="admin_edit_card.php?id=<?php echo $carta['id']; ?>" class="action-btn edit" title="Editar"><ion-icon name="create-outline"></ion-icon></a>
                        <form action="admin_delete_card.php" method="POST" style="display:inline;" onsubmit="return confirm('Deseja realmente excluir esta carta?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                            <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                            <button type="submit" class="action-btn delete" title="Excluir"><ion-icon name="trash-outline"></ion-icon></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($cartas)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px;">Nenhuma carta no catálogo.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

</body>
</html>
