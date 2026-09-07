<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT c.*, f.id as fav_id FROM favoritos f JOIN cartas c ON f.carta_id = c.id WHERE f.usuario_id = ?");
$stmt->execute([$user_id]);
$itens = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Desejos - RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="category.css">
    <link rel="stylesheet" href="wishlist.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

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
            <a href="wishlist.php" class="icon-btn" style="color:var(--accent-color);"><ion-icon name="heart"></ion-icon></a>
            <a href="cart.php" class="icon-btn" style="position:relative;">
                <ion-icon name="cart-outline"></ion-icon>
                <span class="cart-badge" style="position:absolute; top:-5px; right:-5px; background:#ff0055; color:white; font-size:0.7rem; padding:2px 6px; border-radius:50%; font-weight:bold; display: <?php echo $total_cart_items > 0 ? 'flex' : 'none'; ?>; align-items:center; justify-content:center;"><?php echo $total_cart_items; ?></span>
            </a>
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <?php if($_SESSION['usuario_tipo'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="login-link" style="color:#ff5252; border-color:#ff5252;"><ion-icon name="settings-outline"></ion-icon> Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="login-link"><ion-icon name="log-out-outline"></ion-icon> Sair (<?php echo htmlspecialchars(explode(' ', trim($_SESSION['usuario_nome']))[0]); ?>)</a>
            <?php else: ?>
                <a href="login.php" class="login-link"><ion-icon name="person-circle-outline"></ion-icon> Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="category-header wishlist-header">
        <h1>Sua Lista de <span style="color:#ff0055;">Desejos</span></h1>
        <p>Cartas que você marcou como favoritas para comprar depois.</p>
    </div>

    <main class="catalog-layout">
        <section class="cards-grid" style="width: 100%; margin: 0 auto; max-width: 1400px;">
            
            <?php foreach($itens as $carta): ?>
            <div class="card-item wishlist-item">
                
                <!-- Botão de Remover Favorito -->
                <form action="cart_action.php" method="POST" style="position:absolute; top:10px; right:10px; z-index:10;">
                    <input type="hidden" name="action" value="remove_wishlist">
                    <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                    <button type="submit" class="remove-wishlist-btn" title="Remover dos favoritos" style="background:none; border:none; cursor:pointer;"><ion-icon name="close-circle"></ion-icon></button>
                </form>

                <a href="card.php?id=<?php echo $carta['id']; ?>" class="card-item-link" style="margin-bottom:15px; display:block;">
                    <div class="card-image"><img src="<?php echo htmlspecialchars($carta['imagem']); ?>" alt="Carta"></div>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($carta['nome']); ?></h3>
                        <p class="set-name"><?php echo htmlspecialchars($carta['edicao']); ?></p>
                        <div class="tags">
                            <span class="tag"><?php echo htmlspecialchars($carta['raridade']); ?></span>
                        </div>
                        <div class="price">R$ <?php echo number_format($carta['preco'], 2, ',', '.'); ?></div>
                    </div>
                </a>
                
                <!-- Botão de Adicionar ao Carrinho -->
                <form action="cart_action.php" method="POST" style="margin-top:auto; width:100%; padding: 0 15px 15px 15px; display:flex; justify-content:center;">
                    <input type="hidden" name="action" value="add_cart">
                    <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                    <button type="submit" class="add-to-cart" style="width:100%; display:flex; align-items:center; justify-content:center; gap:5px;"><ion-icon name="cart"></ion-icon> Mover p/ Carrinho</button>
                </form>

            </div>
            <?php endforeach; ?>

            <?php if(empty($itens)): ?>
                <div style="grid-column: 1 / -1; text-align:center; padding:50px; color:var(--text-muted);">
                    <h2>Sua lista de desejos está vazia.</h2>
                    <a href="category.php" class="btn ghost-btn" style="margin-top:20px; display:inline-block;">Navegar pelo Catálogo</a>
                </div>
            <?php endif; ?>

        </section>
    </main>
    <?php include 'footer.php'; ?>

    <script src="ajax_actions.js?v=2"></script>
</body>
</html>

