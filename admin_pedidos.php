<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Atualizar status do pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $pedido_id = (int)$_POST['pedido_id'];
    $novo_status = $_POST['status'];
    
    $stmtUpdate = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id = ?");
    $stmtUpdate->execute([$novo_status, $pedido_id]);
    
    require_once 'email_helper.php';
    enviar_email_pedido($pdo, $pedido_id, 'atualizacao_status');
    
    header("Location: admin_pedidos.php?msg=StatusAtualizado");
    exit;
}

// Buscar todos os pedidos
$stmt = $pdo->query("SELECT p.*, u.nome as cliente_nome, u.email as cliente_email 
                     FROM pedidos p 
                     JOIN usuarios u ON p.usuario_id = u.id 
                     ORDER BY p.criado_em DESC");
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos | Admin RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        .dashboard-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: var(--card-bg); border-radius: 12px; overflow: hidden; border: 1px solid var(--glass-border); }
        .dashboard-table th, .dashboard-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--glass-border); color: #fff; }
        .dashboard-table th { background: rgba(0,0,0,0.3); color: var(--accent-color); font-weight: 600; }
        .status-select { background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border); color: #fff; padding: 5px; border-radius: 5px; outline: none; }
        .update-btn { background: var(--accent-color); color: #000; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .update-btn:hover { opacity: 0.8; }
        .pedido-itens-list { list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: #aaa; }
        .pedido-itens-list li { margin-bottom: 3px; }
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
            <a href="admin_eventos.php">Eventos</a>
            <a href="admin_pedidos.php" style="color: var(--accent-color);">Pedidos</a>
        </nav>
        <div class="nav-actions">
            <span style="font-weight:600; margin-right:15px; color:var(--accent-color);">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
            <a href="logout.php" class="login-link" style="background:var(--card-bg); border:1px solid var(--glass-border);"><ion-icon name="log-out-outline"></ion-icon> Sair</a>
        </div>
    </header>

    <main class="admin-container">
        <div class="header-actions">
            <div>
                <h1>Gerenciar Pedidos</h1>
                <p>Visualize e altere o status das compras dos clientes.</p>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'StatusAtualizado'): ?>
            <div style="background: rgba(76, 175, 80, 0.2); color: #4caf50; padding: 10px; border-radius: 5px; margin-top: 15px; border: 1px solid #4caf50;">
                Status do pedido atualizado com sucesso!
            </div>
        <?php endif; ?>

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Cliente</th>
                    <th>Itens</th>
                    <th>Total / Frete</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pedidos as $pedido): 
                    $stmtItens = $pdo->prepare("SELECT pi.*, c.nome FROM pedidos_itens pi JOIN cartas c ON pi.carta_id = c.id WHERE pi.pedido_id = ?");
                    $stmtItens->execute([$pedido['id']]);
                    $itens = $stmtItens->fetchAll();
                ?>
                <tr>
                    <td>#<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($pedido['cliente_nome']); ?></strong><br>
                        <small><?php echo htmlspecialchars($pedido['cliente_email']); ?></small>
                    </td>
                    <td>
                        <ul class="pedido-itens-list">
                            <?php foreach($itens as $item): ?>
                                <li><?php echo $item['quantidade']; ?>x <?php echo htmlspecialchars($item['nome']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>
                        Total: R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?><br>
                        <small style="color: #aaa;">Frete: R$ <?php echo number_format($pedido['frete'], 2, ',', '.'); ?> (<?php echo ucfirst($pedido['tipo_entrega']); ?>)</small>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['criado_em'])); ?></td>
                    <form action="admin_pedidos.php" method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                        <td>
                            <select name="status" class="status-select">
                                <option value="Em preparação" <?php echo $pedido['status'] === 'Em preparação' ? 'selected' : ''; ?>>Em preparação</option>
                                <option value="Enviado" <?php echo $pedido['status'] === 'Enviado' ? 'selected' : ''; ?>>Enviado</option>
                                <option value="Entregue" <?php echo $pedido['status'] === 'Entregue' ? 'selected' : ''; ?>>Entregue</option>
                                <option value="Cancelado" <?php echo $pedido['status'] === 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </td>
                        <td>
                            <button type="submit" class="update-btn">Salvar</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
                <?php if(count($pedidos) === 0): ?>
                    <tr><td colspan="7" style="text-align:center; padding: 30px;">Nenhum pedido encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
