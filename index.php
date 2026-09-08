<?php require_once 'config.php'; 
$stmtPop = $pdo->query("SELECT * FROM cartas ORDER BY vezes_no_carrinho DESC, criado_em DESC LIMIT 8");
$populares = $stmtPop->fetchAll();

$stmtProm = $pdo->query("SELECT * FROM cartas WHERE promocao_porcentagem > 0 ORDER BY criado_em DESC LIMIT 8");
$promocoes = $stmtProm->fetchAll();

$stmtRec = $pdo->query("SELECT * FROM cartas ORDER BY criado_em DESC LIMIT 8");
$recentes = $stmtRec->fetchAll();

$stmtSingles = $pdo->query("SELECT * FROM cartas WHERE categoria = 'pokemon' ORDER BY vezes_no_carrinho DESC, criado_em DESC LIMIT 8");
$singles_pokemon = $stmtSingles->fetchAll();

// Contagem do carrinho (já feito no config mas para garantir o badge se necessário)
$total_cart_items = 0;
if (isset($_SESSION['usuario_id'])) {
    $stmtCount = $pdo->prepare("SELECT SUM(quantidade) FROM carrinho_itens WHERE usuario_id = ?");
    $stmtCount->execute([$_SESSION['usuario_id']]);
    $total_cart_items = $stmtCount->fetchColumn() ?: 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RocketTCG - Loja</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

    <!-- Navbar -->
    <header class="navbar" style="flex-direction: column; padding: 15px 50px 0 50px;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 15px;">
            <div class="logo">
                <img src="assets/Rocket_foto_de_perfil_png.png" alt="RocketTCG" style="height: 50px; width: auto; object-fit: contain;">
            </div>
            
            <div style="display:flex; flex: 1; justify-content: center; align-items: center; margin: 0 20px;">
                <form action="search.php" method="GET" class="search-bar" style="display:flex; flex: 1; max-width: 500px;">
                    <input type="text" name="q" placeholder="Buscar por nome ou coleção..." style="flex:1; padding:10px 15px; border-radius:8px 0 0 8px; border:1px solid var(--glass-border); font-size:1rem; background:var(--card-bg); color:var(--text-main); outline:none;">
                    <button type="submit" class="btn primary-btn" style="padding:10px 20px; border-radius:0 8px 8px 0; border:none; display:flex; align-items:center;"><ion-icon name="search-outline"></ion-icon></button>
                </form>
                <div style="display:flex; gap: 10px; margin-left: 15px;">
                    <a href="https://www.instagram.com/rocke_tcg/" target="_blank" class="icon-btn" style="font-size: 1.3rem; padding: 5px;" title="Instagram"><ion-icon name="logo-instagram"></ion-icon></a>
                    <a href="https://www.tiktok.com/@tcg.r0cket" target="_blank" class="icon-btn" style="font-size: 1.3rem; padding: 5px;" title="TikTok"><ion-icon name="logo-tiktok"></ion-icon></a>
                    <a href="https://wa.link/stslbm" target="_blank" class="icon-btn" style="font-size: 1.3rem; padding: 5px;" title="WhatsApp"><ion-icon name="logo-whatsapp"></ion-icon></a>
                </div>
            </div>
            
            <div class="nav-actions">
                <a href="eventos.php" class="icon-btn" title="Eventos"><ion-icon name="calendar-outline"></ion-icon></a>
                <a href="wishlist.php" class="icon-btn" title="Favoritos"><ion-icon name="heart-outline"></ion-icon></a>
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
        </div>
        
        <nav class="nav-links" style="width: 100%; justify-content: center; padding-bottom: 15px;">
            <a href="category.php?cat=pokemon"><img src="assets/pokemonlogo.png" alt="Pokémon" style="height: 18px; width: auto; object-fit: contain;">Pokémon</a>
            <a href="category.php?cat=magic"><img src="assets/magiclogo.png" alt="Magic" style="height: 18px; width: auto; object-fit: contain;">Magic</a>
            <a href="category.php?cat=yugioh"><img src="assets/yugiohlogo.png" alt="Yu-Gi-Oh!" style="height: 18px; width: auto; object-fit: contain;">Yu-Gi-Oh!</a>
            <a href="category.php?cat=onepiece"><img src="assets/onepiecelogo.png" alt="One Piece" style="height: 18px; width: auto; object-fit: contain;">One Piece</a>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Hero Slider Section -->
        <section class="hero-slider-wrapper">
            <div class="hero-slider" id="heroSlider">
                <!-- Slide 1: Principal -->
                <div class="hero-slide" style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(211, 47, 47, 0.2)), url('https://images.unsplash.com/photo-1605806616949-1e87b487bc2a?q=80&w=1920&auto=format&fit=crop');">
                    <div class="hero-content">
                        <h1>Expanda sua <span>Coleção</span></h1>
                        <p>Encontre as cartas mais raras e fortaleça seu deck para a próxima batalha.</p>
                        <a href="category.php" class="btn primary-btn" style="text-decoration:none; display:inline-block;">Explorar Loja</a>
                    </div>
                </div>

                <!-- Slide 2: Pokémon -->
                <a href="category.php?cat=pokemon" class="hero-slide" style="background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 100%), url('assets/expansaopokemon1.png'); text-decoration:none;">
                    <div class="hero-slide-content">
                        <img src="assets/pokemonexpansaologo.png" alt="Pokémon Logo" class="hero-slide-logo">
                        <span class="btn primary-btn slide-btn">Ver Coleção</span>
                    </div>
                </a>

                <!-- Slide 3: Magic -->
                <a href="category.php?cat=magic" class="hero-slide" style="background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 100%), url('assets/expansaomagic.png'); text-decoration:none;">
                    <div class="hero-slide-content">
                        <img src="assets/magicexpansaologo.png" alt="Magic Logo" class="hero-slide-logo">
                        <span class="btn primary-btn slide-btn">Ver Coleção</span>
                    </div>
                </a>

                <!-- Slide 4: Yu-Gi-Oh! -->
                <a href="category.php?cat=yugioh" class="hero-slide" style="background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 100%), url('assets/yugiohexpansao.jpg'); text-decoration:none;">
                    <div class="hero-slide-content">
                        <img src="assets/yugiohexpansaologo.png" alt="Yu-Gi-Oh! Logo" class="hero-slide-logo">
                        <span class="btn primary-btn slide-btn">Ver Coleção</span>
                    </div>
                </a>
            </div>
            
            <button class="hero-nav-btn prev-btn" onclick="slideHero(-1)"><ion-icon name="chevron-back-outline"></ion-icon></button>
            <button class="hero-nav-btn next-btn" onclick="slideHero(1)"><ion-icon name="chevron-forward-outline"></ion-icon></button>
            
            <div class="hero-indicators" id="heroIndicators">
                <span class="indicator active" onclick="goToHeroSlide(0)"></span>
                <span class="indicator" onclick="goToHeroSlide(1)"></span>
                <span class="indicator" onclick="goToHeroSlide(2)"></span>
                <span class="indicator" onclick="goToHeroSlide(3)"></span>
            </div>
        </section>

        <!-- Seção: Populares -->
        <section class="carousel-section">
            <div class="section-header">
                <h2><ion-icon name="flame"></ion-icon> Populares</h2>
                <div class="carousel-controls">
                    <button class="control-btn" onclick="slideCarousel('populares', -1)"><ion-icon name="chevron-back-outline"></ion-icon></button>
                    <button class="control-btn" onclick="slideCarousel('populares', 1)"><ion-icon name="chevron-forward-outline"></ion-icon></button>
                </div>
            </div>
            <div class="carousel-container" id="populares">
                <?php foreach($populares as $carta): 
                    $is_sale = $carta['promocao_porcentagem'] > 0;
                    $preco_base = $carta['preco'] > 0 ? $carta['preco'] : (isset($carta['preco_reverse']) ? $carta['preco_reverse'] : 0);
                    $preco_final = $preco_base;
                    if ($is_sale) {
                        $preco_final = $preco_base - ($preco_base * ($carta['promocao_porcentagem'] / 100));
                    }
                ?>
                <div class="card-item <?php echo $is_sale ? 'sale' : ''; ?>">
                    <?php if($is_sale): ?>
                        <div class="badge-sale">-<?php echo $carta['promocao_porcentagem']; ?>%</div>
                    <?php endif; ?>
                    <a href="card.php?id=<?php echo $carta['id']; ?>" style="text-decoration:none; color:inherit;">
                        <div class="card-image" style="position: relative;">
                            <?php if($carta['preco'] > 0 && isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0): ?>
                                <div style="position:absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 6px; border-radius: 6px; display:flex; align-items:center; justify-content:center; z-index: 2;" title="Possui opções"><ion-icon name="albums"></ion-icon></div>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($carta['imagem']); ?>" alt="Carta">
                        </div>
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($carta['nome']); ?></h3>
                            <p class="set-name"><?php echo htmlspecialchars($carta['edicao']); ?></p>
                            <div class="price">
                                <?php if($is_sale): ?>
                                    <span class="old-price">R$ <?php echo number_format($preco_base, 2, ',', '.'); ?></span>
                                <?php endif; ?>
                                R$ <?php echo number_format($preco_final, 2, ',', '.'); ?>
                            </div>
                        </div>
                    </a>
                    <form action="cart_action.php" method="POST" style="margin-top:10px; width:100%; padding: 0 15px 15px 15px;">
                        <input type="hidden" name="action" value="add_cart">
                        <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                        <button type="submit" class="add-to-cart" style="width:100%;">Adicionar</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Seção: Na Promoção -->
        <section class="carousel-section">
            <div class="section-header">
                <h2><ion-icon name="pricetag"></ion-icon> Na Promoção</h2>
                <div class="carousel-controls">
                    <button class="control-btn" onclick="slideCarousel('promocao', -1)"><ion-icon name="chevron-back-outline"></ion-icon></button>
                    <button class="control-btn" onclick="slideCarousel('promocao', 1)"><ion-icon name="chevron-forward-outline"></ion-icon></button>
                </div>
            </div>
            <div class="carousel-container" id="promocao">
                <?php if (count($promocoes) > 0): ?>
                    <?php foreach($promocoes as $carta): 
                        $preco_base = $carta['preco'] > 0 ? $carta['preco'] : (isset($carta['preco_reverse']) ? $carta['preco_reverse'] : 0);
                        $preco_final = $preco_base - ($preco_base * ($carta['promocao_porcentagem'] / 100));
                    ?>
                    <div class="card-item sale">
                        <div class="badge-sale">-<?php echo $carta['promocao_porcentagem']; ?>%</div>
                        <a href="card.php?id=<?php echo $carta['id']; ?>" style="text-decoration:none; color:inherit;">
                            <div class="card-image" style="position: relative;">
                                <?php if($carta['preco'] > 0 && isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0): ?>
                                    <div style="position:absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 6px; border-radius: 6px; display:flex; align-items:center; justify-content:center; title="Possui opções" z-index: 2;"><ion-icon name="albums"></ion-icon></div>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($carta['imagem']); ?>" alt="Carta">
                            </div>
                            <div class="card-info">
                                <h3><?php echo htmlspecialchars($carta['nome']); ?></h3>
                                <p class="set-name"><?php echo htmlspecialchars($carta['edicao']); ?></p>
                                <div class="price"><span class="old-price">R$ <?php echo number_format($preco_base, 2, ',', '.'); ?></span> R$ <?php echo number_format($preco_final, 2, ',', '.'); ?></div>
                            </div>
                        </a>
                        <form action="cart_action.php" method="POST" style="margin-top:10px; width:100%; padding: 0 15px 15px 15px;">
                            <input type="hidden" name="action" value="add_cart">
                            <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                            <button type="submit" class="add-to-cart" style="width:100%;">Adicionar</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="padding: 20px; color: var(--text-muted);">Nenhum produto em promoção no momento.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Seção: Recentes -->
        <section class="carousel-section">
            <div class="section-header">
                <h2><ion-icon name="time"></ion-icon> Recentes</h2>
                <div class="carousel-controls">
                    <button class="control-btn" onclick="slideCarousel('recentes', -1)"><ion-icon name="chevron-back-outline"></ion-icon></button>
                    <button class="control-btn" onclick="slideCarousel('recentes', 1)"><ion-icon name="chevron-forward-outline"></ion-icon></button>
                </div>
            </div>
            <div class="carousel-container" id="recentes">
                <?php foreach($recentes as $carta): 
                    $is_sale = $carta['promocao_porcentagem'] > 0;
                    $preco_base = $carta['preco'] > 0 ? $carta['preco'] : (isset($carta['preco_reverse']) ? $carta['preco_reverse'] : 0);
                    $preco_final = $preco_base;
                    if ($is_sale) {
                        $preco_final = $preco_base - ($preco_base * ($carta['promocao_porcentagem'] / 100));
                    }
                ?>
                <div class="card-item new <?php echo $is_sale ? 'sale' : ''; ?>">
                    <?php if($is_sale): ?>
                        <div class="badge-sale" style="top: 35px;">-<?php echo $carta['promocao_porcentagem']; ?>%</div>
                    <?php endif; ?>
                    <div class="badge-new">Novo</div>
                    <a href="card.php?id=<?php echo $carta['id']; ?>" style="text-decoration:none; color:inherit;">
                        <div class="card-image" style="position: relative;">
                            <?php if($carta['preco'] > 0 && isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0): ?>
                                <div style="position:absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 6px; border-radius: 6px; display:flex; align-items:center; justify-content:center; z-index: 2;" title="Possui opções"><ion-icon name="albums"></ion-icon></div>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($carta['imagem']); ?>" alt="Carta">
                        </div>
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($carta['nome']); ?></h3>
                            <p class="set-name"><?php echo htmlspecialchars($carta['edicao']); ?></p>
                            <div class="price">
                                <?php if($is_sale): ?>
                                    <span class="old-price">R$ <?php echo number_format($preco_base, 2, ',', '.'); ?></span>
                                <?php endif; ?>
                                R$ <?php echo number_format($preco_final, 2, ',', '.'); ?>
                            </div>
                        </div>
                    </a>
                    <form action="cart_action.php" method="POST" style="margin-top:10px; width:100%; padding: 0 15px 15px 15px;">
                        <input type="hidden" name="action" value="add_cart">
                        <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                        <button type="submit" class="add-to-cart" style="width:100%;">Adicionar</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Seção: Singles de Pokémon -->
        <section class="carousel-section">
            <div class="section-header">
                <h2><img src="assets/pokemonlogo.png" style="height: 24px; vertical-align: bottom; margin-right: 5px;" alt="Pokémon"> Singles de Pokémon</h2>
                <div class="carousel-controls">
                    <button class="control-btn" onclick="slideCarousel('singles_pokemon', -1)"><ion-icon name="chevron-back-outline"></ion-icon></button>
                    <button class="control-btn" onclick="slideCarousel('singles_pokemon', 1)"><ion-icon name="chevron-forward-outline"></ion-icon></button>
                </div>
            </div>
            <div class="carousel-container" id="singles_pokemon">
                <?php foreach($singles_pokemon as $carta): 
                    $is_sale = $carta['promocao_porcentagem'] > 0;
                    $preco_base = $carta['preco'] > 0 ? $carta['preco'] : (isset($carta['preco_reverse']) ? $carta['preco_reverse'] : 0);
                    $preco_final = $preco_base;
                    if ($is_sale) {
                        $preco_final = $preco_base - ($preco_base * ($carta['promocao_porcentagem'] / 100));
                    }
                ?>
                <div class="card-item <?php echo $is_sale ? 'sale' : ''; ?>">
                    <?php if($is_sale): ?>
                        <div class="badge-sale">-<?php echo $carta['promocao_porcentagem']; ?>%</div>
                    <?php endif; ?>
                    <a href="card.php?id=<?php echo $carta['id']; ?>" style="text-decoration:none; color:inherit;">
                        <div class="card-image" style="position: relative;">
                            <?php if($carta['preco'] > 0 && isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0): ?>
                                <div style="position:absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 6px; border-radius: 6px; display:flex; align-items:center; justify-content:center; z-index: 2;" title="Possui opções"><ion-icon name="albums"></ion-icon></div>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($carta['imagem']); ?>" alt="Carta">
                        </div>
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($carta['nome']); ?></h3>
                            <p class="set-name"><?php echo htmlspecialchars($carta['edicao']); ?></p>
                            <div class="price">
                                <?php if($is_sale): ?>
                                    <span class="old-price">R$ <?php echo number_format($preco_base, 2, ',', '.'); ?></span>
                                <?php endif; ?>
                                R$ <?php echo number_format($preco_final, 2, ',', '.'); ?>
                            </div>
                        </div>
                    </a>
                    <form action="cart_action.php" method="POST" style="margin-top:10px; width:100%; padding: 0 15px 15px 15px;">
                        <input type="hidden" name="action" value="add_cart">
                        <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
                        <button type="submit" class="add-to-cart" style="width:100%;">Adicionar</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>
    <?php include 'footer.php'; ?>

    <script src="home.js?v=<?php echo time(); ?>"></script>
    <script src="ajax_actions.js"></script>
</body>
</html>

