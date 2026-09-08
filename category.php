<?php
require_once 'config.php';

$categoriaAtiva = isset($_GET['cat']) ? $_GET['cat'] : 'pokemon';

$stmt = $pdo->prepare("SELECT * FROM cartas WHERE categoria = ?");
$stmt->execute([$categoriaAtiva]);
$cartas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RocketTCG - Categoria</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="category.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <?php
    $dynamic_css = "";
    if($categoriaAtiva == 'pokemon') {
        $dynamic_css = "--primary-color: #e3350d; --primary-hover: #b32a0a; --accent-color: #ffcb05;";
    } elseif($categoriaAtiva == 'yugioh') {
        $dynamic_css = "--primary-color: #4b0082; --primary-hover: #000080; --accent-color: #ffd700;";
    } elseif($categoriaAtiva == 'magic') {
        $dynamic_css = "--primary-color: #5c4033; --primary-hover: #3d2b22; --accent-color: #d4af37;";
    } elseif($categoriaAtiva == 'onepiece') {
        $dynamic_css = "--primary-color: #006994; --primary-hover: #004c6d; --accent-color: #e4d96f;";
    }
    if ($dynamic_css != "") {
        echo "<style>:root { $dynamic_css }</style>";
    }
    ?>
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
            <a href="eventos.php" class="icon-btn" title="Eventos"><ion-icon name="calendar-outline"></ion-icon></a>
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

    <?php
    $bannerImage = "";
    if($categoriaAtiva == 'pokemon') {
        $bannerImage = "assets/ButtonBannerPokemon.png";
    } elseif($categoriaAtiva == 'yugioh') {
        $bannerImage = "assets/YGO_TCG_NEWS-POST-1.png";
    } elseif($categoriaAtiva == 'magic') {
        $bannerImage = "assets/voyager-title-magic-gathering.jpg";
    } elseif($categoriaAtiva == 'onepiece') {
        $bannerImage = "assets/one-piece-card-game-kort.png";
    }
    ?>
    <?php if($bannerImage != ""): ?>
    <div class="category-banner">
        <img src="<?php echo $bannerImage; ?>" alt="<?php echo htmlspecialchars($categoriaAtiva); ?> Banner">
    </div>
    <?php endif; ?>
    <!-- Main Content Layout -->
    <main class="catalog-layout">
        
        <!-- Sidebar Filters -->
        <aside class="filters-sidebar" style="margin-top: 0;">
            <h3><ion-icon name="options-outline"></ion-icon> Filtros</h3>
            
            <div class="filter-group">
                <h4>Ordenar por</h4>
                <select id="sortOrder" style="width:100%; padding: 8px; border-radius: 5px; background: rgba(255,255,255,0.1); color: #fff; border: 1px solid #444;">
                    <option value="default">Relevância</option>
                    <option value="price_asc">Menor Preço (Mais barato ao mais caro)</option>
                    <option value="price_desc">Maior Preço (Mais caro ao mais barato)</option>
                </select>
            </div>
            
            <div class="filter-group">
                <h4>Preço Máximo</h4>
                <div class="price-slider">
                    <input type="range" id="priceRange" min="0" max="2000" value="2000">
                    <span id="priceValue">R$ 2000,00</span>
                </div>
            </div>

            <div class="filter-group">
                <h4>Raridade</h4>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="rarity" value="Comum"> Comum</label>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="rarity" value="Incomum"> Incomum</label>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="rarity" value="Rara"> Rara</label>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="rarity" value="Holografica"> Holográfica</label>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="rarity" value="Secreta"> Secreta</label>
            </div>

            <div class="filter-group">
                <h4>Estado da Carta</h4>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="condition" value="Mint"> Mint (M)</label>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="condition" value="Near Mint"> Near Mint (NM)</label>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="condition" value="Played"> Played (P)</label>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="condition" value="Damaged"> Damaged (D)</label>
            </div>

            <div class="filter-group">
                <h4>Tipo de Carta</h4>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="type" value="Pokémon"> Pokémon / Monstro</label>
                <label class="filter-option"><input type="checkbox" class="filter-cb" data-filter="type" value="Energia / Treinador"> Energia / Treinador</label>
            </div>
        </aside>

        <!-- Content Area Wrapper -->
        <div class="content-wrapper" style="flex: 1;">
            
            <div class="category-header" style="text-align: left; padding: 0 0 30px 0; background: none;">
                <h1>Explorar <span style="text-transform:capitalize;"><?php echo htmlspecialchars($categoriaAtiva); ?></span></h1>
                <p>Encontre as cartas perfeitas para o seu deck.</p>
                <?php if($categoriaAtiva == 'pokemon'): ?>
                <div style="margin-top: 20px; display: flex; justify-content: flex-start; gap: 15px;">
                    <a href="#" class="btn primary-btn" style="background: var(--card-bg); color: var(--text-main); border: 2px solid var(--primary-color); display:flex; align-items:center; gap:8px;"><ion-icon name="albums-outline"></ion-icon> Cartas</a>
                    <a href="#" class="btn primary-btn" style="background: var(--card-bg); color: var(--text-main); border: 2px solid var(--primary-color); display:flex; align-items:center; gap:8px;"><ion-icon name="cube-outline"></ion-icon> Boosters</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Cards Grid -->
            <section class="cards-grid" id="cardsGrid">
            <?php foreach($cartas as $carta): 
                $is_sale = isset($carta['promocao_porcentagem']) && $carta['promocao_porcentagem'] > 0;
                $preco_base = $carta['preco'] > 0 ? $carta['preco'] : (isset($carta['preco_reverse']) ? $carta['preco_reverse'] : 0);
                $preco_final = $preco_base;
                if ($is_sale) {
                    $preco_final = $preco_base - ($preco_base * ($carta['promocao_porcentagem'] / 100));
                }
                
                $rarities = array($carta['raridade']);
                $is_special = (isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0) || (isset($carta['tipo_energia']) && strtolower(trim($carta['tipo_energia'])) !== 'normal' && trim($carta['tipo_energia']) !== '');
                if ($is_special) {
                    $rarities[] = 'Holografica';
                }
                
                $filter_type = $carta['tipo_carta'];
                if (trim($carta['descricao']) === 'Energia' || stripos($carta['nome'], 'Energia') !== false || stripos($carta['nome'], 'Treinador') !== false || stripos($carta['nome'], 'Trainer') !== false) {
                    $filter_type = 'Energia / Treinador';
                }
            ?>
            <a href="card.php?id=<?php echo $carta['id']; ?>" class="card-item-link" 
               data-price="<?php echo $preco_final; ?>" 
               data-rarity="<?php echo htmlspecialchars(implode(',', $rarities)); ?>" 
               data-condition="<?php echo htmlspecialchars($carta['estado']); ?>" 
               data-type="<?php echo htmlspecialchars($filter_type); ?>">
                <div class="card-item <?php echo $is_sale ? 'sale' : ''; ?>" style="position: relative;">
                    <?php if($is_sale): ?>
                        <div class="badge-sale">-<?php echo $carta['promocao_porcentagem']; ?>%</div>
                    <?php endif; ?>
                    <div class="card-image" style="position: relative;">
                        <?php if($carta['preco'] > 0 && isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0): ?>
                            <div style="position:absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 6px; border-radius: 6px; display:flex; align-items:center; justify-content:center; z-index: 2;" title="Possui opções"><ion-icon name="albums"></ion-icon></div>
                        <?php endif; ?>
                        <img src="<?php echo htmlspecialchars($carta['imagem']); ?>" alt="Carta">
                    </div>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($carta['nome']); ?></h3>
                        <p class="set-name"><?php echo htmlspecialchars($carta['edicao']); ?></p>
                        <div class="tags">
                            <span class="tag"><?php echo htmlspecialchars($carta['raridade']); ?></span>
                            <?php if($is_special): ?>
                                <span class="tag" style="background: linear-gradient(45deg, #ffd700, #ff8c00); color: #000; border: none; font-weight: bold;">Holográfica</span>
                            <?php endif; ?>
                            <span class="tag"><?php echo htmlspecialchars($carta['estado']); ?></span>
                        </div>
                        <div class="price">
                            <?php if($is_sale): ?>
                                <span class="old-price" style="text-decoration: line-through; color: var(--text-muted); font-size: 0.9em; margin-right: 5px;">R$ <?php echo number_format($preco_base, 2, ',', '.'); ?></span>
                            <?php endif; ?>
                            R$ <?php echo number_format($preco_final, 2, ',', '.'); ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            
            <?php if(empty($cartas)): ?>
                <div style="grid-column: 1 / -1; text-align:center; padding:50px; color:var(--text-muted);">
                    <h2>Estamos trabalhando para trazer mais cartas em breve.</h2>
                </div>
            <?php endif; ?>
            </section>
        </div>
    </main>
    <?php include 'footer.php'; ?>

    <script src="category.js?v=<?php echo time(); ?>"></script>
    <script src="ajax_actions.js?v=<?php echo time(); ?>"></script>
</body>
</html>

