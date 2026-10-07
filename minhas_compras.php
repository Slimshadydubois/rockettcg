<?php
require_once 'config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];

// Buscar pedidos
$stmtPedidos = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY criado_em DESC");
$stmtPedidos->execute([$user_id]);
$pedidos = $stmtPedidos->fetchAll();

$total_cart_items = 0;
$stmtCount = $pdo->prepare("SELECT SUM(quantidade) FROM carrinho_itens WHERE usuario_id = ?");
$stmtCount->execute([$user_id]);
$total_cart_items = $stmtCount->fetchColumn() ?: 0;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Compras - RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        .compras-container { max-width: 900px; margin: 40px auto; padding: 20px; }
        .compras-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .compras-header h1 { color: var(--accent-color); }
        .pedido-card { background: var(--card-bg); border: 1px solid var(--glass-border); border-radius: 10px; margin-bottom: 20px; padding: 20px; }
        .pedido-header { display: flex; justify-content: space-between; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .pedido-header h3 { margin: 0; color: #fff; }
        .pedido-info { font-size: 0.9rem; color: #ccc; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; }
        .status-preparacao { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
        .status-enviado { background: rgba(33, 150, 243, 0.2); color: #2196f3; border: 1px solid #2196f3; }
        .status-entregue { background: rgba(76, 175, 80, 0.2); color: #4caf50; border: 1px solid #4caf50; }
        .status-cancelado { background: rgba(244, 67, 54, 0.2); color: #f44336; border: 1px solid #f44336; }
        
        .pedido-itens { margin-top: 15px; }
        .item-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #333; }
        .item-row:last-child { border-bottom: none; }
        .item-info { display: flex; align-items: center; gap: 15px; }
        .item-img { width: 50px; height: 70px; object-fit: cover; border-radius: 5px; }
        .item-details h4 { margin: 0; font-size: 1rem; color: #eee; }
        .item-details p { margin: 5px 0 0; font-size: 0.85rem; color: #aaa; }
        .item-price { font-weight: bold; color: var(--accent-color); }
        
        .pedido-footer { margin-top: 15px; text-align: right; border-top: 1px solid #333; padding-top: 15px; }
        .total-price { font-size: 1.2rem; font-weight: bold; color: var(--accent-color); }
        .empty-state { text-align: center; padding: 50px; background: var(--card-bg); border-radius: 10px; color: #ccc; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <header class="navbar">
        <a href="index.php" class="logo" style="text-decoration: none;">
            <img src="assets/Rocket_foto_de_perfil_png.png" alt="RocketTCG" style="height: 50px; width: auto; object-fit: contain;">
        </a>
        
        <nav class="nav-links">
            <a href="category.php?cat=pokemon"><img src="assets/pokemonlogo.png" alt="Pokémon" style="height: 18px; width: auto; object-fit: contain;">Pokémon</a>
            <a href="category.php?cat=magic"><img src="assets/magiclogo.png" alt="Magic" style="height: 18px; width: auto; object-fit: contain;">Magic</a>
            <a href="category.php?cat=yugioh"><img src="assets/yugiohlogo.png" alt="Yu-Gi-Oh!" style="height: 18px; width: auto; object-fit: contain;">Yu-Gi-Oh!</a>
            <a href="category.php?cat=onepiece"><img src="assets/onepiecelogo.png" alt="One Piece" style="height: 18px; width: auto; object-fit: contain;">One Piece</a>
        </nav>
        
        <div class="nav-actions">
            <a href="search.php" class="icon-btn"><ion-icon name="search-outline"></ion-icon></a>
            <a href="wishlist.php" class="icon-btn"><ion-icon name="heart-outline"></ion-icon></a>
            <a href="cart.php" class="icon-btn" style="position:relative;">
                <ion-icon name="cart"></ion-icon>
                <span class="cart-badge" style="position:absolute; top:-5px; right:-5px; background:#ff0055; color:white; font-size:0.7rem; padding:2px 6px; border-radius:50%; font-weight:bold; display: <?php echo $total_cart_items > 0 ? 'flex' : 'none'; ?>; align-items:center; justify-content:center;"><?php echo $total_cart_items; ?></span>
            </a>
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <?php if($_SESSION['usuario_tipo'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="login-link" style="color:#ff5252; border-color:#ff5252;"><ion-icon name="settings-outline"></ion-icon> Admin</a>
                <?php endif; ?>
                <a href="minhas_compras.php" class="login-link" style="color: #4caf50; border-color: #4caf50;"><ion-icon name="cube-outline"></ion-icon> Pedidos</a>
                <a href="logout.php" class="login-link"><ion-icon name="log-out-outline"></ion-icon> Sair (<?php echo htmlspecialchars(explode(' ', trim($_SESSION['usuario_nome']))[0]); ?>)</a>
            <?php else: ?>
                <a href="login.php" class="login-link"><ion-icon name="person-circle-outline"></ion-icon> Login</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="compras-container">
        <div class="compras-header">
            <h1>Minhas Compras</h1>
            <a href="index.php" class="btn ghost-btn">Continuar Comprando</a>
        </div>

        <?php if (count($pedidos) === 0): ?>
            <div class="empty-state">
                <ion-icon name="bag-handle-outline" style="font-size: 4rem; color: #666; margin-bottom: 15px;"></ion-icon>
                <h2>Você ainda não fez nenhuma compra.</h2>
                <p>Navegue pela nossa loja e descubra cartas incríveis!</p>
                <a href="index.php" class="btn primary-btn" style="margin-top: 20px; display: inline-block;">Ir para a Loja</a>
            </div>
        <?php else: ?>
            <?php foreach ($pedidos as $pedido): 
                $stmtItens = $pdo->prepare("SELECT pi.*, c.nome, c.imagem, c.categoria FROM pedidos_itens pi JOIN cartas c ON pi.carta_id = c.id WHERE pi.pedido_id = ?");
                $stmtItens->execute([$pedido['id']]);
                $itens = $stmtItens->fetchAll();
                
                $data = date('d/m/Y H:i', strtotime($pedido['criado_em']));
                $status_class = 'status-preparacao';
                if ($pedido['status'] === 'Enviado') $status_class = 'status-enviado';
                else if ($pedido['status'] === 'Entregue') $status_class = 'status-entregue';
                else if ($pedido['status'] === 'Cancelado') $status_class = 'status-cancelado';
            ?>
                <div class="pedido-card">
                    <div class="pedido-header">
                        <div>
                            <h3>Pedido #<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></h3>
                            <div class="pedido-info">Realizado em <?php echo $data; ?> | Entrega: <?php echo ucfirst($pedido['tipo_entrega']); ?></div>
                        </div>
                        <div>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($pedido['status']); ?></span>
                        </div>
                    </div>
                    
                    <div class="pedido-itens">
                        <?php foreach ($itens as $item): ?>
                            <div class="item-row">
                                <div class="item-info">
                                    <img src="<?php echo htmlspecialchars($item['imagem']); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>" class="item-img">
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($item['nome']); ?> (<?php echo htmlspecialchars(ucfirst($item['categoria'])); ?>)</h4>
                                        <p>Quantidade: <?php echo $item['quantidade']; ?>x</p>
                                    </div>
                                </div>
                                <div class="item-price">
                                    R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="pedido-footer">
                        <?php if ($pedido['frete'] > 0): ?>
                            <div style="font-size: 0.9rem; color: #aaa; margin-bottom: 5px;">Frete: R$ <?php echo number_format($pedido['frete'], 2, ',', '.'); ?></div>
                        <?php endif; ?>
                        <div class="total-price">Total: R$ <?php echo number_format($pedido['total'], 2, ',', '.'); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>
