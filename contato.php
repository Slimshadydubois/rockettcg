<?php 
require_once 'config.php'; 

// Lógica para envio de formulário pode ser inserida aqui no futuro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensagem_sucesso = "Mensagem enviada com sucesso! Entraremos em contato em breve.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fale Conosco - RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="contato.css">
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
                <?php 
                $total_cart_items = 0;
                if (isset($_SESSION['usuario_id'])) {
                    $stmtCount = $pdo->prepare("SELECT SUM(quantidade) FROM carrinho_itens WHERE usuario_id = ?");
                    $stmtCount->execute([$_SESSION['usuario_id']]);
                    $total_cart_items = $stmtCount->fetchColumn() ?: 0;
                }
                ?>
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

    <!-- Main Content -->
    <main class="contato-container">
        <section class="contato-header">
            <h1>Fale <span>Conosco</span></h1>
            <p>Tem alguma dúvida, sugestão ou precisa de ajuda? Preencha o formulário ou entre em contato diretamente pelos nossos canais.</p>
        </section>

        <section class="contato-content">
            <!-- Formulário -->
            <div class="contato-form-area">
                <?php if(!empty($mensagem_sucesso)): ?>
                    <div class="alert-success">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                        <?php echo $mensagem_sucesso; ?>
                    </div>
                <?php endif; ?>
                
                <form action="contato.php" method="POST" class="contato-form">
                    <div class="form-group">
                        <label for="assunto">Assunto</label>
                        <input type="text" id="assunto" name="assunto" placeholder="Ex: Dúvida sobre pedido" required>
                    </div>

                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required>
                    </div>

                    <div class="form-group-row">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone (DDD + Número)</label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mensagem">Mensagem</label>
                        <textarea id="mensagem" name="mensagem" rows="5" placeholder="Escreva sua mensagem aqui..." required></textarea>
                    </div>

                    <button type="submit" class="btn primary-btn submit-btn">
                        <span>Enviar Mensagem</span>
                        <ion-icon name="send-outline"></ion-icon>
                    </button>
                </form>
            </div>

            <!-- Informações de Contato -->
            <div class="contato-info-area">
                <div class="info-card whatsapp-card">
                    <div class="info-icon">
                        <ion-icon name="logo-whatsapp"></ion-icon>
                    </div>
                    <div class="info-text">
                        <h3>Nosso WhatsApp</h3>
                        <p>Atendimento rápido e direto</p>
                        <span class="info-value">+55 51 8151-3325</span>
                    </div>
                </div>

                <div class="info-card email-card">
                    <div class="info-icon">
                        <ion-icon name="mail-outline"></ion-icon>
                    </div>
                    <div class="info-text">
                        <h3>Nosso E-mail</h3>
                        <p>Envie-nos uma mensagem</p>
                        <span class="info-value">rockettcgg@gmail.com</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <script src="ajax_actions.js"></script>
</body>
</html>
