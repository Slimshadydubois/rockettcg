<?php
require_once 'config.php';

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];
$tipo_entrega = $_POST['tipo_entrega'] ?? 'entrega';
$valor_frete = (float)($_POST['valor_frete'] ?? 20);
if ($tipo_entrega === 'balcao') $valor_frete = 0;

$stmtU = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmtU->execute([$user_id]);
$user = $stmtU->fetch();

$mensagem_sucesso = false;
$erro_pagamento = false;

// Tratar retorno do Mercado Pago
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        // Limpar o carrinho (simulando compra com sucesso)
        $stmtLimpar = $pdo->prepare("DELETE FROM carrinho_itens WHERE usuario_id = ?");
        $stmtLimpar->execute([$user_id]);
        
        $mensagem_sucesso = true;
    } else if ($_GET['status'] === 'failure') {
        $erro_pagamento = "O pagamento falhou ou foi recusado. Tente novamente.";
    } else if ($_GET['status'] === 'pending') {
        $erro_pagamento = "Seu pagamento está pendente. Você será notificado quando for aprovado.";
    }
}

// Tratar submissão do formulário de checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_order') {
    if ($tipo_entrega === 'entrega') {
        // Salvar os dados de endereço do usuário
        $cep = $_POST['cep'];
        $estado = $_POST['estado'];
        $cidade = $_POST['cidade'];
        $endereco = $_POST['endereco'];
        $bairro = $_POST['bairro'];
        $numero = $_POST['numero'];
        $complemento = $_POST['complemento'];
        
        $stmtUpdate = $pdo->prepare("UPDATE usuarios SET cep=?, estado=?, cidade=?, endereco=?, bairro=?, numero=?, complemento=? WHERE id=?");
        $stmtUpdate->execute([$cep, $estado, $cidade, $endereco, $bairro, $numero, $complemento, $user_id]);
        
        // Atualiza os dados do user na variavel
        $user['cep'] = $cep; $user['estado'] = $estado; $user['cidade'] = $cidade; $user['endereco'] = $endereco; $user['bairro'] = $bairro; $user['numero'] = $numero; $user['complemento'] = $complemento;
    }
    
    // Obter itens do carrinho para o Mercado Pago
    $stmtCartMP = $pdo->prepare("SELECT c.nome, c.preco, ci.quantidade FROM carrinho_itens ci JOIN cartas c ON ci.carta_id = c.id WHERE ci.usuario_id = ?");
    $stmtCartMP->execute([$user_id]);
    $itens_carrinho = $stmtCartMP->fetchAll();
    
    $items_mp = [];
    foreach ($itens_carrinho as $item) {
        $items_mp[] = [
            "title" => $item['nome'],
            "quantity" => (int)$item['quantidade'],
            "currency_id" => "BRL",
            "unit_price" => (float)$item['preco']
        ];
    }
    
    if ($tipo_entrega === 'entrega' && $valor_frete > 0) {
        $items_mp[] = [
            "title" => "Frete",
            "quantity" => 1,
            "currency_id" => "BRL",
            "unit_price" => (float)$valor_frete
        ];
    }
    
    $access_token = "TEST-7323590744743693-081820-52966af5c49889da075e83f9bc978a29-3600611593";
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $dir = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
    $base_url = $protocol . $_SERVER['HTTP_HOST'] . $dir;
    if (substr($base_url, -1) !== '/') {
        $base_url .= '/';
    }
    $preference_data = [
        "items" => $items_mp
    ];
    
    // O Mercado Pago não aceita localhost em URLs de retorno automáticas.
    if (!in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
        $preference_data["back_urls"] = [
            "success" => $base_url . "checkout.php?status=success",
            "failure" => $base_url . "checkout.php?status=failure",
            "pending" => $base_url . "checkout.php?status=pending"
        ];
        $preference_data["auto_return"] = "approved";
    }

    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    
    $preference = json_decode($response, true);
    
    if (isset($preference['init_point'])) {
        header("Location: " . $preference['init_point']);
        exit;
    } else {
        $erro_pagamento = "Erro ao conectar com Mercado Pago. Detalhes: " . (isset($preference['message']) ? $preference['message'] : "Erro desconhecido.");
    }
}

// Calcular total do carrinho para exibir
$stmtCart = $pdo->prepare("SELECT SUM(c.preco * ci.quantidade) FROM carrinho_itens ci JOIN cartas c ON ci.carta_id = c.id WHERE ci.usuario_id = ?");
$stmtCart->execute([$user_id]);
$subtotal = (float)$stmtCart->fetchColumn();
$total = $subtotal + $valor_frete;

if ($subtotal == 0 && !$mensagem_sucesso) {
    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        .checkout-container { max-width: 800px; margin: 40px auto; padding: 30px; background: var(--card-bg); border-radius: 15px; border: 1px solid var(--glass-border); }
        h1 { margin-bottom: 20px; color: var(--accent-color); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #ccc; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #444; background: rgba(255,255,255,0.05); color: #fff; }
        .row { display: flex; gap: 15px; }
        .col { flex: 1; }
        
        .payment-methods { display: flex; gap: 15px; margin-top: 15px; }
        .payment-method { flex: 1; text-align: center; padding: 20px; border: 2px solid #444; border-radius: 10px; cursor: pointer; transition: 0.3s; }
        .payment-method:hover, .payment-method.active { border-color: var(--accent-color); background: rgba(255,82,82,0.1); }
        .payment-method ion-icon { font-size: 2rem; margin-bottom: 10px; color: var(--accent-color); }
        
        .summary-box { background: rgba(0,0,0,0.2); padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .summary-box h3 { margin-bottom: 15px; }
        
        .error-message { background: rgba(244, 67, 54, 0.1); color: #f44336; padding: 15px; border: 1px solid #f44336; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <header class="navbar">
        <a href="index.php" class="logo" style="text-decoration: none;">
            <img src="assets/Rocket_foto_de_perfil_png.png" alt="RocketTCG" style="height: 50px; width: auto; object-fit: contain;">
        </a>
        <div class="nav-actions">
            <a href="cart.php" class="login-link"><ion-icon name="arrow-back-outline"></ion-icon> Voltar ao Carrinho</a>
        </div>
    </header>

    <main class="checkout-container">
        <?php if($mensagem_sucesso): ?>
            <div style="text-align: center; padding: 50px 0;">
                <ion-icon name="checkmark-circle" style="font-size: 5rem; color: #4caf50;"></ion-icon>
                <h1 style="color: #4caf50;">Compra Finalizada com Sucesso!</h1>
                <p>Obrigado por comprar na RocketTCG. Seu pedido foi pago e está sendo processado.</p>
                <a href="index.php" class="btn primary-btn" style="margin-top:20px; display:inline-block; text-decoration:none;">Voltar para a Loja</a>
            </div>
        <?php else: ?>
        
        <h1>Finalizar Compra</h1>
        
        <?php if($erro_pagamento): ?>
            <div class="error-message">
                <ion-icon name="alert-circle-outline" style="vertical-align: middle; font-size: 1.2rem;"></ion-icon> 
                <?php echo $erro_pagamento; ?>
            </div>
        <?php endif; ?>
        
        <div class="summary-box">
            <h3>Resumo da Compra</h3>
            <p><strong>Tipo de Entrega:</strong> <?php echo $tipo_entrega === 'balcao' ? 'Retirar no Balcão' : 'Entrega no Endereço'; ?></p>
            <p><strong>Subtotal:</strong> R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></p>
            <p><strong>Frete:</strong> R$ <?php echo number_format($valor_frete, 2, ',', '.'); ?></p>
            <h3 style="margin-top:10px; color:var(--accent-color);">Total a Pagar: R$ <?php echo number_format($total, 2, ',', '.'); ?></h3>
        </div>

        <form action="checkout.php" method="POST">
            <input type="hidden" name="action" value="confirm_order">
            <input type="hidden" name="tipo_entrega" value="<?php echo $tipo_entrega; ?>">
            <input type="hidden" name="valor_frete" value="<?php echo $valor_frete; ?>">
            
            <?php if($tipo_entrega === 'entrega'): ?>
            <h2 style="margin-bottom:20px; border-bottom:1px solid #444; padding-bottom:10px;">Endereço para Recebimento de Compras</h2>
            
            <div class="row">
                <div class="form-group col">
                    <label>CEP</label>
                    <input type="text" name="cep" value="<?php echo htmlspecialchars($user['cep'] ?? ''); ?>" required>
                </div>
                <div class="form-group col">
                    <label>Estado</label>
                    <input type="text" name="estado" value="<?php echo htmlspecialchars($user['estado'] ?? 'RS'); ?>" required>
                </div>
                <div class="form-group col">
                    <label>Cidade</label>
                    <input type="text" name="cidade" value="<?php echo htmlspecialchars($user['cidade'] ?? 'Cachoeirinha'); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Endereço</label>
                <input type="text" name="endereco" value="<?php echo htmlspecialchars($user['endereco'] ?? ''); ?>" required>
            </div>
            
            <div class="row">
                <div class="form-group col">
                    <label>Bairro</label>
                    <input type="text" name="bairro" value="<?php echo htmlspecialchars($user['bairro'] ?? ''); ?>" required>
                </div>
                <div class="form-group col">
                    <label>Número</label>
                    <input type="text" name="numero" value="<?php echo htmlspecialchars($user['numero'] ?? ''); ?>" required>
                </div>
                <div class="form-group col">
                    <label>Complemento</label>
                    <input type="text" name="complemento" value="<?php echo htmlspecialchars($user['complemento'] ?? ''); ?>">
                </div>
            </div>
            <?php endif; ?>

            <h2 style="margin-bottom:10px; margin-top:30px; border-bottom:1px solid #444; padding-bottom:10px;">Pagar com Mercado Pago</h2>
            <p style="color: #ccc; margin-bottom: 20px;">Você será redirecionado para o ambiente seguro do Mercado Pago para escolher entre Cartão de Crédito, PIX ou Boleto.</p>
            
            <div style="text-align: center; padding: 15px; background: rgba(0, 158, 227, 0.1); border: 1px solid #009ee3; border-radius: 10px; margin-bottom: 20px;">
                <img src="https://logospng.org/download/mercado-pago/logo-mercado-pago-icone-1024.png" alt="Mercado Pago" style="height: 40px; vertical-align: middle;">
                <span style="display: inline-block; margin-left: 10px; font-weight: 600; color: #fff;">Pagamento Seguro</span>
            </div>
            
            <button type="submit" class="btn primary-btn" style="width:100%; margin-top:10px; padding: 15px; font-size: 1.2rem; justify-content:center; background-color: #009ee3; color: white; border: none;">Ir para Pagamento</button>
        </form>
        
        <?php endif; ?>
    </main>
    
</body>
</html>
