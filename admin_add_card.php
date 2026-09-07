<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erro de validação CSRF.");
    }
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $categoria = $_POST['categoria'];
    $imagem = $_POST['imagem'];
    $descricao = $_POST['descricao'];
    $edicao = $_POST['edicao'];
    $estado = $_POST['estado'];
    $raridade = $_POST['raridade'];
    $data_lancamento = !empty($_POST['data_lancamento']) ? $_POST['data_lancamento'] : null;
    $tipo_carta = $_POST['tipo_carta'];
    $tipo_energia = $_POST['tipo_energia'];
    $nacionalidade = $_POST['nacionalidade'];
    $estoque = isset($_POST['estoque']) ? (int)$_POST['estoque'] : 1;
    $promocao_porcentagem = isset($_POST['promocao_porcentagem']) ? (int)$_POST['promocao_porcentagem'] : 0;
    $preco_reverse = !empty($_POST['preco_reverse']) ? (float)$_POST['preco_reverse'] : null;
    $estoque_reverse = isset($_POST['estoque_reverse']) ? (int)$_POST['estoque_reverse'] : 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO cartas (nome, preco, categoria, imagem, descricao, edicao, estado, raridade, data_lancamento, tipo_carta, tipo_energia, nacionalidade, estoque, promocao_porcentagem, preco_reverse, estoque_reverse) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $preco, $categoria, $imagem, $descricao, $edicao, $estado, $raridade, $data_lancamento, $tipo_carta, $tipo_energia, $nacionalidade, $estoque, $promocao_porcentagem, $preco_reverse, $estoque_reverse]);
        $msg = "Carta adicionada com sucesso no catálogo!";
        $msgType = "success";
    } catch(PDOException $e) {
        $msg = "Erro ao adicionar a carta: " . $e->getMessage();
        $msgType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Adicionar Carta | RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

    <!-- Admin Navbar -->
    <header class="navbar admin-navbar">
        <a href="index.php" class="logo" style="text-decoration: none;">
            <ion-icon name="rocket-outline"></ion-icon>
            <h2>Rocket<span>ADMIN</span></h2>
        </a>
        <div class="nav-actions">
            <span style="font-weight:600; margin-right:15px; color:var(--accent-color);">Painel Administrativo</span>
            <a href="login.php" class="login-link" style="background:var(--card-bg); border:1px solid var(--glass-border);"><ion-icon name="log-out-outline"></ion-icon> Sair</a>
        </div>
    </header>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Adicionar Nova Carta ao Acervo</h1>
            <p>Preencha todas as especificações técnicas da carta.</p>
        </div>

        <?php if($msg): ?>
            <div style="text-align:center; padding:15px; margin-bottom:20px; border-radius:10px; font-weight:600; background: <?php echo $msgType == 'success' ? '#ff5252' : '#ff0055'; ?>; color: <?php echo $msgType == 'success' ? '#0b0f19' : '#fff'; ?>;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="admin_add_card.php" method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <div class="form-grid">
                <!-- Coluna Esquerda: Informações Básicas -->
                <div class="form-column">
                    <h3>Informações Principais</h3>
                    
                    <div class="input-group-admin">
                        <label>Nome da Carta</label>
                        <input type="text" name="nome" placeholder="Ex: Charizard Holográfico" required>
                    </div>

                    <div class="input-group-admin" style="display:flex; gap:10px;">
                        <div style="flex:1;">
                            <label>Preço (R$)</label>
                            <input type="number" step="0.01" name="preco" placeholder="0.00" required>
                        </div>
                        <div style="flex:1;">
                            <label>Estoque</label>
                            <input type="number" name="estoque" placeholder="1" value="1" required>
                        </div>
                        <div style="flex:1;">
                            <label>Promoção (%)</label>
                            <input type="number" name="promocao_porcentagem" placeholder="0" value="0" min="0" max="100">
                        </div>
                    </div>

                    <div class="input-group-admin" style="display:flex; gap:10px; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px;">
                        <div style="flex:1;">
                            <label>Preço Reverse Foil (R$)</label>
                            <input type="number" step="0.01" name="preco_reverse" placeholder="0.00">
                        </div>
                        <div style="flex:1;">
                            <label>Estoque Reverse</label>
                            <input type="number" name="estoque_reverse" placeholder="0" value="0">
                        </div>
                    </div>

                    <div class="input-group-admin">
                        <label>Categoria (Jogo)</label>
                        <select name="categoria">
                            <option value="pokemon">Pokémon TCG</option>
                            <option value="magic">Magic: The Gathering</option>
                            <option value="yugioh">Yu-Gi-Oh!</option>
                            <option value="onepiece">One Piece Card Game</option>
                        </select>
                    </div>

                    <div class="input-group-admin">
                        <label>URL da Imagem</label>
                        <input type="url" name="imagem" placeholder="https://..." required>
                    </div>
                    
                    <div class="input-group-admin">
                        <label>Descrição</label>
                        <textarea name="descricao" rows="4" placeholder="Detalhes adicionais sobre a carta..."></textarea>
                    </div>
                </div>

                <!-- Coluna Direita: Especificações Técnicas -->
                <div class="form-column">
                    <h3>Especificações Técnicas</h3>

                    <div class="input-group-admin">
                        <label>Edição / Coleção</label>
                        <input type="text" name="edicao" placeholder="Ex: Coleção Base 1ª Edição">
                    </div>

                    <div class="input-group-admin">
                        <label>Estado da Carta (Condition)</label>
                        <select name="estado">
                            <option value="Mint">Mint (M)</option>
                            <option value="Near Mint">Near Mint (NM)</option>
                            <option value="Played">Played (P)</option>
                            <option value="Damaged">Damaged (D)</option>
                        </select>
                    </div>

                    <div class="input-group-admin">
                        <label>Raridade</label>
                        <select name="raridade">
                            <option value="Comum">Comum</option>
                            <option value="Incomum">Incomum</option>
                            <option value="Rara">Rara</option>
                            <option value="Holografica">Holográfica (Holo Rare)</option>
                            <option value="Secreta">Secreta</option>
                        </select>
                    </div>

                    <div class="input-group-admin">
                        <label>Data de Lançamento</label>
                        <input type="date" name="data_lancamento">
                    </div>

                    <div class="input-group-admin">
                        <label>Tipo de Carta</label>
                        <input type="text" name="tipo_carta" placeholder="Ex: Pokémon Estágio 2, Mágica Instantânea...">
                    </div>

                    <div class="input-group-admin">
                        <label>Tipo de Energia / Atributo</label>
                        <input type="text" name="tipo_energia" placeholder="Ex: Fogo, Água, Trevas...">
                    </div>

                    <div class="input-group-admin">
                        <label>Nacionalidade (Idioma)</label>
                        <select name="nacionalidade">
                            <option value="PT-BR">Português (PT-BR)</option>
                            <option value="EN">Inglês (EN)</option>
                            <option value="JP">Japonês (JP)</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn primary-btn admin-submit"><ion-icon name="cloud-upload-outline"></ion-icon> Publicar Carta no Catálogo</button>
            </div>

        </form>

    </main>

</body>
</html>
