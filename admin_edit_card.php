<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id === 0) {
    header("Location: admin_dashboard.php");
    exit;
}

$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erro de validação CSRF.");
    }
    $update_id = (int)$_POST['id'];
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
    $estoque = (int)$_POST['estoque'];
    $promocao_porcentagem = (int)$_POST['promocao_porcentagem'];
    $preco_reverse = !empty($_POST['preco_reverse']) ? (float)$_POST['preco_reverse'] : null;
    $estoque_reverse = isset($_POST['estoque_reverse']) ? (int)$_POST['estoque_reverse'] : 0;

    try {
        $stmt = $pdo->prepare("UPDATE cartas SET nome=?, preco=?, categoria=?, imagem=?, descricao=?, edicao=?, estado=?, raridade=?, data_lancamento=?, tipo_carta=?, tipo_energia=?, nacionalidade=?, estoque=?, promocao_porcentagem=?, preco_reverse=?, estoque_reverse=? WHERE id=?");
        $stmt->execute([$nome, $preco, $categoria, $imagem, $descricao, $edicao, $estado, $raridade, $data_lancamento, $tipo_carta, $tipo_energia, $nacionalidade, $estoque, $promocao_porcentagem, $preco_reverse, $estoque_reverse, $id]);
        $msg = "Carta atualizada com sucesso!";
        $msgType = "success";
    } catch(PDOException $e) {
        $msg = "Erro ao atualizar a carta: " . $e->getMessage();
        $msgType = "error";
    }
}

$stmt = $pdo->prepare("SELECT * FROM cartas WHERE id = ?");
$stmt->execute([$id]);
$carta = $stmt->fetch();

if (!$carta) {
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Editar Carta | RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

    <header class="navbar admin-navbar">
        <a href="index.php" class="logo" style="text-decoration: none;">
            <ion-icon name="rocket-outline"></ion-icon>
            <h2>Rocket<span>ADMIN</span></h2>
        </a>
        <div class="nav-actions">
            <a href="admin_dashboard.php" class="login-link" style="margin-right:15px; border:1px solid var(--accent-color); color:var(--accent-color);"><ion-icon name="arrow-back-outline"></ion-icon> Voltar ao Dashboard</a>
            <a href="logout.php" class="login-link" style="background:var(--card-bg); border:1px solid var(--glass-border);"><ion-icon name="log-out-outline"></ion-icon> Sair</a>
        </div>
    </header>

    <main class="admin-container">
        <div class="admin-header">
            <h1>Editar Carta: #<?php echo $carta['id']; ?></h1>
            <p>Atualize as informações no banco de dados.</p>
        </div>

        <?php if($msg): ?>
            <div style="text-align:center; padding:15px; margin-bottom:20px; border-radius:10px; font-weight:600; background: <?php echo $msgType == 'success' ? '#ff5252' : '#ff0055'; ?>; color: <?php echo $msgType == 'success' ? '#0b0f19' : '#fff'; ?>;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="admin_edit_card.php" method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="id" value="<?php echo $carta['id']; ?>">
            
            <div class="form-grid">
                <!-- Coluna Esquerda: Informações Básicas -->
                <div class="form-column">
                    <h3>Informações Principais</h3>
                    
                    <div class="input-group-admin">
                        <label>Nome da Carta</label>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($carta['nome']); ?>" required>
                    </div>

                    <div class="input-group-admin" style="display:flex; gap:10px;">
                        <div style="flex:1;">
                            <label>Preço (R$)</label>
                            <input type="number" step="0.01" name="preco" value="<?php echo $carta['preco']; ?>" required>
                        </div>
                        <div style="flex:1;">
                            <label>Estoque</label>
                            <input type="number" name="estoque" value="<?php echo $carta['estoque']; ?>" required>
                        </div>
                        <div style="flex:1;">
                            <label>Promoção (%)</label>
                            <input type="number" name="promocao_porcentagem" value="<?php echo $carta['promocao_porcentagem']; ?>" min="0" max="100">
                        </div>
                    </div>

                    <div class="input-group-admin" style="display:flex; gap:10px; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px;">
                        <div style="flex:1;">
                            <label>Preço Reverse Foil (R$)</label>
                            <input type="number" step="0.01" name="preco_reverse" value="<?php echo $carta['preco_reverse']; ?>" placeholder="0.00">
                        </div>
                        <div style="flex:1;">
                            <label>Estoque Reverse</label>
                            <input type="number" name="estoque_reverse" value="<?php echo $carta['estoque_reverse']; ?>" placeholder="0">
                        </div>
                    </div>

                    <div class="input-group-admin">
                        <label>Categoria (Jogo)</label>
                        <select name="categoria">
                            <option value="pokemon" <?php echo $carta['categoria'] == 'pokemon' ? 'selected' : ''; ?>>Pokémon TCG</option>
                            <option value="magic" <?php echo $carta['categoria'] == 'magic' ? 'selected' : ''; ?>>Magic: The Gathering</option>
                            <option value="yugioh" <?php echo $carta['categoria'] == 'yugioh' ? 'selected' : ''; ?>>Yu-Gi-Oh!</option>
                            <option value="onepiece" <?php echo $carta['categoria'] == 'onepiece' ? 'selected' : ''; ?>>One Piece Card Game</option>
                        </select>
                    </div>

                    <div class="input-group-admin">
                        <label>URL da Imagem</label>
                        <input type="url" name="imagem" value="<?php echo htmlspecialchars($carta['imagem']); ?>" required>
                    </div>
                    
                    <div class="input-group-admin">
                        <label>Descrição</label>
                        <textarea name="descricao" rows="4"><?php echo htmlspecialchars($carta['descricao']); ?></textarea>
                    </div>
                </div>

                <!-- Coluna Direita: Especificações Técnicas -->
                <div class="form-column">
                    <h3>Especificações Técnicas</h3>

                    <div class="input-group-admin">
                        <label>Edição / Coleção</label>
                        <input type="text" name="edicao" value="<?php echo htmlspecialchars($carta['edicao']); ?>">
                    </div>

                    <div class="input-group-admin">
                        <label>Estado da Carta (Condition)</label>
                        <select name="estado">
                            <option value="Mint" <?php echo $carta['estado'] == 'Mint' ? 'selected' : ''; ?>>Mint (M)</option>
                            <option value="Near Mint" <?php echo $carta['estado'] == 'Near Mint' ? 'selected' : ''; ?>>Near Mint (NM)</option>
                            <option value="Played" <?php echo $carta['estado'] == 'Played' ? 'selected' : ''; ?>>Played (P)</option>
                            <option value="Damaged" <?php echo $carta['estado'] == 'Damaged' ? 'selected' : ''; ?>>Damaged (D)</option>
                        </select>
                    </div>

                    <div class="input-group-admin">
                        <label>Raridade</label>
                        <select name="raridade">
                            <option value="Comum" <?php echo $carta['raridade'] == 'Comum' ? 'selected' : ''; ?>>Comum</option>
                            <option value="Incomum" <?php echo $carta['raridade'] == 'Incomum' ? 'selected' : ''; ?>>Incomum</option>
                            <option value="Rara" <?php echo $carta['raridade'] == 'Rara' ? 'selected' : ''; ?>>Rara</option>
                            <option value="Holográfica" <?php echo $carta['raridade'] == 'Holográfica' ? 'selected' : ''; ?>>Holográfica (Holo Rare)</option>
                            <option value="Secreta" <?php echo $carta['raridade'] == 'Secreta' ? 'selected' : ''; ?>>Secreta</option>
                        </select>
                    </div>

                    <div class="input-group-admin">
                        <label>Data de Lançamento</label>
                        <input type="date" name="data_lancamento" value="<?php echo $carta['data_lancamento']; ?>">
                    </div>

                    <div class="input-group-admin">
                        <label>Tipo de Carta</label>
                        <input type="text" name="tipo_carta" value="<?php echo htmlspecialchars($carta['tipo_carta']); ?>">
                    </div>

                    <div class="input-group-admin">
                        <label>Tipo de Energia / Atributo</label>
                        <input type="text" name="tipo_energia" value="<?php echo htmlspecialchars($carta['tipo_energia']); ?>">
                    </div>

                    <div class="input-group-admin">
                        <label>Nacionalidade (Idioma)</label>
                        <select name="nacionalidade">
                            <option value="PT-BR" <?php echo $carta['nacionalidade'] == 'PT-BR' ? 'selected' : ''; ?>>Português (PT-BR)</option>
                            <option value="EN" <?php echo $carta['nacionalidade'] == 'EN' ? 'selected' : ''; ?>>Inglês (EN)</option>
                            <option value="JP" <?php echo $carta['nacionalidade'] == 'JP' ? 'selected' : ''; ?>>Japonês (JP)</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="btn primary-btn admin-submit"><ion-icon name="save-outline"></ion-icon> Salvar Alterações</button>
            </div>

        </form>

    </main>

</body>
</html>
