<?php
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM cartas WHERE id = ?");
$stmt->execute([$id]);
$carta = $stmt->fetch();

if (!$carta) {
    die("Carta não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($carta['nome']); ?> - RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="card.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
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

    <main class="card-details-container">
        
        <div class="breadcrumb">
            <a href="index.php">Início</a> > <a href="category.php?cat=<?php echo urlencode($carta['categoria']); ?>"><?php echo ucfirst(htmlspecialchars($carta['categoria'])); ?></a> > <span><?php echo htmlspecialchars($carta['nome']); ?></span>
        </div>

        <div class="card-content-wrapper">
            <!-- Coluna Esquerda: Imagem -->
            <div class="card-image-large">
                <img src="<?php echo htmlspecialchars($carta['imagem']); ?>" alt="<?php echo htmlspecialchars($carta['nome']); ?>">
            </div>

            <!-- Coluna Direita: Detalhes -->
            <div class="card-info-large">
                <h1><?php echo htmlspecialchars($carta['nome']); ?></h1>
                <?php 
                    $is_sale = isset($carta['promocao_porcentagem']) && $carta['promocao_porcentagem'] > 0;
                    
                    $has_normal = $carta['preco'] > 0;
                    $has_special = isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0;
                    
                    $nome_special = !empty($carta['nome_variante']) ? htmlspecialchars($carta['nome_variante']) : 'Reverse Foil';
                    
                    $preco_normal = $carta['preco'];
                    $preco_final_normal = $is_sale ? $preco_normal - ($preco_normal * ($carta['promocao_porcentagem'] / 100)) : $preco_normal;
                    
                    $preco_special = $has_special ? $carta['preco_reverse'] : 0;
                    $preco_final_special = $is_sale && $has_special ? $preco_special - ($preco_special * ($carta['promocao_porcentagem'] / 100)) : $preco_special;
                    
                    if ($has_normal) {
                        $display_old = $preco_normal;
                        $display_final = $preco_final_normal;
                        $default_variant = 'normal';
                    } else {
                        $display_old = $preco_special;
                        $display_final = $preco_final_special;
                        $default_variant = 'reverse';
                    }
                    
                    $in_wishlist = false;
                    $cart_variants = [];
                    if (isset($_SESSION['usuario_id'])) {
                        $uid = $_SESSION['usuario_id'];
                        $stmtW = $pdo->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND carta_id = ?");
                        $stmtW->execute([$uid, $carta['id']]);
                        $in_wishlist = $stmtW->fetchColumn() ? true : false;
                        
                        $stmtC = $pdo->prepare("SELECT variante FROM carrinho_itens WHERE usuario_id = ? AND carta_id = ?");
                        $stmtC->execute([$uid, $carta['id']]);
                        $cart_variants = $stmtC->fetchAll(PDO::FETCH_COLUMN);
                    }
                ?>
                <h2 class="card-price" id="display-price">
                    <?php if($is_sale): ?>
                        <span class="old-price" id="display-old-price" style="text-decoration: line-through; color: var(--text-muted); font-size: 1.2rem; margin-right: 10px;">R$ <?php echo number_format($display_old, 2, ',', '.'); ?></span>
                        <span style="background: #ff0055; color: white; font-size: 1rem; padding: 3px 8px; border-radius: 5px; vertical-align: middle; margin-right: 10px;">-<?php echo $carta['promocao_porcentagem']; ?>%</span>
                    <?php endif; ?>
                    <span id="display-final-price">R$ <?php echo number_format($display_final, 2, ',', '.'); ?></span>
                </h2>

                <?php 
                    $btn_cart_action = in_array($default_variant, $cart_variants) ? 'remove_cart_ajax' : 'add_cart';
                    $btn_cart_text = in_array($default_variant, $cart_variants) ? '<ion-icon name="checkmark-outline"></ion-icon> Tirar do carrinho' : '<ion-icon name="cart"></ion-icon> Adicionar ao Carrinho';
                    $btn_cart_class = in_array($default_variant, $cart_variants) ? 'btn ghost-btn add-to-cart-large' : 'btn primary-btn add-to-cart-large';
                    $btn_cart_style = in_array($default_variant, $cart_variants) ? 'width:100%; color:#ff0055; border-color:#ff0055;' : 'width:100%;';
                ?>
                <form action="cart_action.php" method="POST" style="display:inline-block; width:100%; margin-bottom:10px;">
                    <input type="hidden" name="action" id="cart-action-input" value="<?php echo $btn_cart_action; ?>">
                    <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                    
                    <?php if($has_normal && $has_special): ?>
                    <div class="variant-selector" style="margin-bottom: 20px; display: flex; gap: 10px;">
                        <div style="flex: 1; cursor: pointer;" onclick="updatePrice('normal')" id="wrapper-normal">
                            <input type="radio" name="variante" id="radio-normal" value="normal" checked style="position: absolute; opacity: 0; pointer-events: none;">
                            <div class="variant-btn" id="btn-normal" style="border: 2px solid var(--glass-border); padding: 10px; border-radius: 8px; text-align: center; background: rgba(255,255,255,0.15); transform: scale(1.05); transition: 0.3s; height: 100%;">
                                <strong>Normal</strong><br>
                                <small>R$ <?php echo number_format($preco_final_normal, 2, ',', '.'); ?></small>
                            </div>
                        </div>
                        <div style="flex: 1; cursor: pointer;" onclick="updatePrice('reverse')" id="wrapper-reverse">
                            <input type="radio" name="variante" id="radio-reverse" value="reverse" style="position: absolute; opacity: 0; pointer-events: none;">
                            <div class="variant-btn" id="btn-reverse" style="border: 2px solid var(--glass-border); padding: 10px; border-radius: 8px; text-align: center; background: rgba(0,0,0,0.2); transform: scale(1); transition: 0.3s; height: 100%;">
                                <strong><?php echo $nome_special; ?></strong><br>
                                <small>R$ <?php echo number_format($preco_final_special, 2, ',', '.'); ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        const cartVariants = <?php echo json_encode($cart_variants); ?>;
                        
                        function updatePrice(variant) {
                            const btnNormal = document.getElementById('btn-normal');
                            const btnReverse = document.getElementById('btn-reverse');
                            const radioNormal = document.getElementById('radio-normal');
                            const radioReverse = document.getElementById('radio-reverse');
                            const finalPrice = document.getElementById('display-final-price');
                            const oldPrice = document.getElementById('display-old-price');
                            const cartActionInput = document.getElementById('cart-action-input');
                            const cartBtn = document.getElementById('cart-submit-btn');
                            const specTipoCarta = document.getElementById('spec-tipo-carta');
                            
                            const prices = {
                                normal: {
                                    final: 'R$ <?php echo number_format($preco_final_normal, 2, ',', '.'); ?>',
                                    old: 'R$ <?php echo number_format($preco_normal, 2, ',', '.'); ?>',
                                    nome: '<?php echo addslashes(trim(preg_replace('/\s+/', ' ', $carta['tipo_energia'] ?? "Normal"))); ?>'
                                },
                                reverse: {
                                    final: 'R$ <?php echo number_format($preco_final_special, 2, ',', '.'); ?>',
                                    old: 'R$ <?php echo number_format($preco_special, 2, ',', '.'); ?>',
                                    nome: '<?php echo addslashes(trim(preg_replace('/\s+/', ' ', $nome_special))); ?>'
                                }
                            };
                            
                            if (variant === 'normal') {
                                if (radioNormal) radioNormal.checked = true;
                                if (btnNormal) {
                                    btnNormal.style.transform = 'scale(1.05)';
                                    btnNormal.style.background = 'rgba(255,255,255,0.15)';
                                }
                                if (btnReverse) {
                                    btnReverse.style.transform = 'scale(1)';
                                    btnReverse.style.background = 'rgba(0,0,0,0.2)';
                                }
                            } else {
                                if (radioReverse) radioReverse.checked = true;
                                if (btnReverse) {
                                    btnReverse.style.transform = 'scale(1.05)';
                                    btnReverse.style.background = 'rgba(255,255,255,0.15)';
                                }
                                if (btnNormal) {
                                    btnNormal.style.transform = 'scale(1)';
                                    btnNormal.style.background = 'rgba(0,0,0,0.2)';
                                }
                            }
                            
                            if (finalPrice && prices[variant]) {
                                finalPrice.innerHTML = prices[variant].final;
                            }
                            if (oldPrice && prices[variant]) {
                                oldPrice.innerHTML = prices[variant].old;
                            }
                            if (specTipoCarta && prices[variant]) {
                                specTipoCarta.innerHTML = prices[variant].nome;
                            }
                            
                            if (cartVariants.includes(variant)) {
                                cartActionInput.value = 'remove_cart_ajax';
                                cartBtn.innerHTML = '<ion-icon name="checkmark-outline"></ion-icon> Tirar do carrinho';
                                cartBtn.classList.remove('primary-btn');
                                cartBtn.classList.add('ghost-btn');
                                cartBtn.style.color = '#ff0055';
                                cartBtn.style.borderColor = '#ff0055';
                            } else {
                                cartActionInput.value = 'add_cart';
                                cartBtn.innerHTML = '<ion-icon name="cart"></ion-icon> Adicionar ao Carrinho';
                                cartBtn.classList.remove('ghost-btn');
                                cartBtn.classList.add('primary-btn');
                                cartBtn.style.color = '';
                                cartBtn.style.borderColor = '';
                            }
                        }
                    </script>
                    <?php else: ?>
                    <input type="hidden" name="variante" value="<?php echo $default_variant; ?>">
                    <?php endif; ?>

                    <button type="submit" id="cart-submit-btn" class="<?php echo $btn_cart_class; ?>" style="<?php echo $btn_cart_style; ?>"><?php echo $btn_cart_text; ?></button>
                </form>

                <form action="cart_action.php" method="POST" style="display:inline-block; width:100%; margin-bottom:20px;">
                    <?php 
                        $btn_wish_action = $in_wishlist ? 'remove_wishlist' : 'add_wishlist';
                        $btn_wish_icon = $in_wishlist ? '<ion-icon name="heart" style="color:#ff0055;"></ion-icon>' : '<ion-icon name="heart-outline"></ion-icon>';
                        $btn_wish_style = $in_wishlist ? 'width:100%; color:#ff0055; border-color:#ff0055;' : 'width:100%;';
                    ?>
                    <input type="hidden" name="action" value="<?php echo $btn_wish_action; ?>">
                    <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                    <input type="hidden" name="return_url" value="card.php?id=<?php echo $carta['id']; ?>">
                    <button type="submit" class="btn ghost-btn wishlist-btn" style="<?php echo $btn_wish_style; ?>"><?php echo $btn_wish_icon; ?> Lista de Desejos</button>
                </form>

                <div class="technical-specs">
                    <h3>Especificações da Carta</h3>
                    <table>
                        <tr>
                            <th>Edição</th>
                            <td><?php echo htmlspecialchars($carta['edicao']); ?></td>
                        </tr>
                        <tr>
                            <th>Estado da Carta</th>
                            <td><?php echo htmlspecialchars($carta['estado']); ?></td>
                        </tr>
                        <tr>
                            <th>Raridade</th>
                            <td><?php echo htmlspecialchars($carta['raridade']); ?></td>
                        </tr>
                        <tr>
                            <th>Data de Lançamento</th>
                            <td><?php echo date('d/m/Y', strtotime($carta['data_lancamento'])); ?></td>
                        </tr>
                        <tr>
                            <th>Jogo</th>
                            <td><?php echo htmlspecialchars($carta['tipo_carta']); ?></td>
                        </tr>
                        <tr>
                            <th>Tipo de Carta</th>
                            <td id="spec-tipo-carta"><?php echo htmlspecialchars($carta['tipo_energia']); ?></td>
                        </tr>
                        <tr>
                            <th>Nacionalidade</th>
                            <td><?php echo htmlspecialchars($carta['nacionalidade']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="description-section">
                    <h3>Descrição</h3>
                    <p><?php echo nl2br(htmlspecialchars($carta['descricao'])); ?></p>
                </div>
            </div>
        </div>

    </main>
    <?php include 'footer.php'; ?>

    <script src="ajax_actions.js?v=2"></script>
</body>
</html>

