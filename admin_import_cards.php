<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}
$msg = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['planilha'])) {
    $arquivo = $_FILES['planilha']['tmp_name'];
    
    if (empty($arquivo)) {
        $msg = "Selecione um arquivo CSV para importar.";
        $msgType = "error";
    } else {
        $extensao = pathinfo($_FILES['planilha']['name'], PATHINFO_EXTENSION);
        if (strtolower($extensao) != 'csv') {
            $msg = "Formato de arquivo inválido. Por favor, envie um arquivo CSV.";
            $msgType = "error";
        } else {
            if (($handle = fopen($arquivo, "r")) !== FALSE) {
                // Pular o cabeçalho se houver (opcional, verificando se a primeira linha tem 'Pokemon' ou 'Nome')
                $cabecalho = fgetcsv($handle, 1000, ";"); // tenta com ponto e vírgula
                if (count($cabecalho) <= 1) {
                    rewind($handle);
                    $cabecalho = fgetcsv($handle, 1000, ","); // tenta com vírgula
                }
                
                $linhas_importadas = 0;
                $linhas_com_erro = 0;
                
                // Se a primeira linha não parecer cabeçalho (não contém as palavras chaves), voltamos o ponteiro
                $primeira_col = strtolower($cabecalho[0]);
                if (strpos($primeira_col, 'pokemon') === false && strpos($primeira_col, 'nome') === false && strpos($primeira_col, 'item') === false) {
                    // Não é cabeçalho, volta para ler tudo
                    rewind($handle);
                }

                $tipo_planilha = $_POST['tipo_planilha'] ?? 'pokemon';

                $pdo->beginTransaction();

                try {
                    $stmtCheck = $pdo->prepare("SELECT id, preco, estoque, preco_reverse, estoque_reverse FROM cartas WHERE nome = ? AND descricao = ? AND edicao = ?");
                    $stmtUpdateNormal = $pdo->prepare("UPDATE cartas SET preco = ?, estoque = ?, tipo_energia = ? WHERE id = ?");
                    $stmtUpdateReverse = $pdo->prepare("UPDATE cartas SET preco_reverse = ?, estoque_reverse = ?, nome_variante = ? WHERE id = ?");
                    $stmtInsert = $pdo->prepare("INSERT INTO cartas (nome, preco, categoria, imagem, descricao, edicao, estado, raridade, data_lancamento, tipo_carta, tipo_energia, nacionalidade, estoque, promocao_porcentagem, preco_reverse, estoque_reverse, nome_variante) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    while (($dados = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        // Se o CSV foi salvo com ; em vez de , vamos tentar ajustar
                        if (count($dados) <= 1 && strpos($dados[0], ';') !== false) {
                            $dados = explode(';', $dados[0]);
                        }

                        if ($tipo_planilha === 'energia') {
                            if (count($dados) < 3) {
                                $linhas_com_erro++;
                                continue;
                            }
                            $nome = trim($dados[0]);
                            $codigo_edicao = trim($dados[1] ?? ''); // Vem da coluna "Tipo" na planilha
                            $valor = trim($dados[2] ?? '0');
                            $quantidade = isset($dados[3]) && trim($dados[3]) !== '' ? (int) trim($dados[3]) : 1;
                            if ($quantidade <= 0) $quantidade = 1;
                            $imagem = trim($dados[4] ?? '');
                            
                            $numero = '';
                            $edicao = $codigo_edicao;
                            $tipo_carta_csv = 'Pokémon'; // Jogo
                            $tipo = 'Normal'; // Será processado como tipo_energia
                            $descricao_csv = 'Energia';
                        } else {
                            if (count($dados) < 4) {
                                $linhas_com_erro++;
                                continue;
                            }
                            $nome = trim($dados[0]);
                            $numero = trim($dados[1] ?? '');
                            $valor = trim($dados[2] ?? '0');
                            $imagem = trim($dados[3] ?? '');
                            $tipo = trim($dados[4] ?? '');
                            $edicao = trim($dados[5] ?? '');
                            $quantidade = isset($dados[6]) && trim($dados[6]) !== '' ? (int) trim($dados[6]) : 1;
                            if ($quantidade <= 0) $quantidade = 1;
                            $tipo_carta_csv = 'Pokémon'; // Jogo
                            $descricao_csv = "Número da Carta: " . $numero;
                        }

                        // Validação: ignorar a linha de cabeçalho
                        if (strtolower($imagem) === 'imagem' || strtolower($nome) === 'pokemon' || strtolower($nome) === 'item') {
                            continue;
                        }

                        // Tratamento de dados
                        $valor = str_replace(['R$', ' ', '.'], '', $valor); // Remove R$ e pontos
                        $valor = str_replace(',', '.', $valor); // Troca vírgula por ponto para float
                        $preco = (float) $valor;
                        
                        $is_special = (strtolower($tipo) !== 'normal' && !empty($tipo));

                        $categoria = 'pokemon';
                        $descricao = $descricao_csv;
                        $estado = 'Near Mint'; // Padrão
                        $raridade = 'Comum'; // Padrão
                        $data_lancamento = $tipo_planilha === 'energia' ? '1970-01-01' : null;
                        $tipo_carta = $tipo_carta_csv;
                        $nacionalidade = 'PT-BR';
                        $promocao_porcentagem = 0;

                        $stmtCheck->execute([$nome, $descricao, $edicao]);
                        $existente = $stmtCheck->fetch();

                        if ($existente) {
                            if ($is_special) {
                                $nova_qtd = $existente['estoque_reverse'] + $quantidade;
                                $stmtUpdateReverse->execute([$preco, $nova_qtd, $tipo, $existente['id']]);
                            } else {
                                $nova_qtd = $existente['estoque'] + $quantidade;
                                $stmtUpdateNormal->execute([$preco, $nova_qtd, 'Normal', $existente['id']]);
                            }
                        } else {
                            if ($is_special) {
                                $preco_normal = 0;
                                $estoque_normal = 0;
                                $preco_rev = $preco;
                                $estoque_rev = $quantidade;
                                $nome_var = $tipo;
                                $tipo_en = $tipo;
                            } else {
                                $preco_normal = $preco;
                                $estoque_normal = $quantidade;
                                $preco_rev = null;
                                $estoque_rev = 0;
                                $nome_var = null;
                                $tipo_en = 'Normal';
                            }

                            $stmtInsert->execute([
                                $nome, 
                                $preco_normal, 
                                $categoria, 
                                $imagem, 
                                $descricao, 
                                $edicao, 
                                $estado, 
                                $raridade, 
                                $data_lancamento, 
                                $tipo_carta, 
                                $tipo_en, 
                                $nacionalidade, 
                                $estoque_normal, 
                                $promocao_porcentagem,
                                $preco_rev,
                                $estoque_rev,
                                $nome_var
                            ]);
                        }
                        $linhas_importadas++;
                    }
                    $pdo->commit();
                    $msg = "Importação concluída! $linhas_importadas cartas foram adicionadas. (" . $linhas_com_erro . " linhas ignoradas/erro)";
                    $msgType = "success";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $msg = "Erro durante a importação: " . $e->getMessage();
                    $msgType = "error";
                }

                fclose($handle);
            } else {
                $msg = "Não foi possível ler o arquivo enviado.";
                $msgType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Importar Planilha | RocketTCG</title>
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
            <h1>Importar Cartas via Planilha</h1>
            <p>Faça o upload de um arquivo CSV contendo as cartas.</p>
        </div>

        <?php if($msg): ?>
            <div style="text-align:center; padding:15px; margin-bottom:20px; border-radius:10px; font-weight:600; background: <?php echo $msgType == 'success' ? '#ff5252' : '#ff0055'; ?>; color: <?php echo $msgType == 'success' ? '#0b0f19' : '#fff'; ?>;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div style="background: var(--card-bg); border: 1px solid var(--glass-border); border-radius: 12px; padding: 30px; margin-bottom: 20px;">
            <h3>Instruções para o CSV</h3>
            <p style="margin-top: 10px; margin-bottom: 20px; color: #ccc;">
                A planilha deve ser salva no formato <strong>CSV (separado por vírgulas ou ponto e vírgula)</strong>.
            </p>
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1; background: rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; border: 1px solid var(--glass-border);">
                    <h4 style="color: #ff5252; margin-bottom: 10px;">Opção 1: Pokémons</h4>
                    <p style="font-size: 0.9rem; color: #ccc; margin-bottom: 10px;">Ordem exata das colunas:</p>
                    <ol style="color: #fff; margin-left: 20px; font-size: 0.9rem;">
                        <li><strong>Pokemon</strong> (Ex: Charizard)</li>
                        <li><strong>Numero</strong> (Ex: 006/165)</li>
                        <li><strong>Valor</strong> (Ex: 150.00 ou 150,00)</li>
                        <li><strong>Imagem</strong> (Link URL)</li>
                        <li><strong>Tipo</strong> (Ex: Fogo)</li>
                        <li><strong>Edição</strong> (Ex: Scarlet & Violet)</li>
                        <li><strong>Quantidade</strong> (Ex: 3)</li>
                    </ol>
                </div>
                <div style="flex: 1; background: rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; border: 1px solid var(--glass-border);">
                    <h4 style="color: #ff5252; margin-bottom: 10px;">Opção 2: Energias e Ajudas</h4>
                    <p style="font-size: 0.9rem; color: #ccc; margin-bottom: 10px;">Ordem exata das colunas:</p>
                    <ol style="color: #fff; margin-left: 20px; font-size: 0.9rem;">
                        <li><strong>Item</strong> (Ex: Energia de Fogo)</li>
                        <li><strong>Tipo (Código da Carta)</strong> (Ex: SVE001/032)</li>
                        <li><strong>Valor</strong> (Ex: 2.50 ou 2,50)</li>
                        <li><strong>Quantidade</strong> (Ex: 10)</li>
                        <li><strong>Imagem</strong> (Link URL)</li>
                    </ol>
                </div>
            </div>
            
            <form action="admin_import_cards.php" method="POST" enctype="multipart/form-data" class="admin-form" style="margin-top: 30px;">
                <div class="input-group-admin" style="margin-bottom: 20px;">
                    <label>Tipo de Planilha</label>
                    <select name="tipo_planilha" style="padding: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; width: 100%;">
                        <option value="pokemon">Pokémons (Pokemon, Numero, Valor, Imagem, Tipo, Edição, Quantidade)</option>
                        <option value="energia">Energias e Ajudas (Item, Tipo, Valor, Quantidade, Imagem)</option>
                    </select>
                </div>

                <div class="input-group-admin">
                    <label>Selecione o arquivo CSV</label>
                    <input type="file" name="planilha" accept=".csv" required style="padding: 10px; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; width: 100%;">
                </div>

                <div class="form-footer" style="margin-top: 20px; display: flex; gap: 15px;">
                    <button type="submit" class="btn primary-btn admin-submit"><ion-icon name="cloud-upload-outline"></ion-icon> Importar Cartas</button>
                    <a href="admin_dashboard.php" class="btn" style="background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; padding: 12px 25px; border-radius: 8px; border: 1px solid var(--glass-border);">Voltar ao Painel</a>
                </div>
            </form>
        </div>

    </main>

</body>
</html>
