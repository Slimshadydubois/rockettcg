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

$is_gravatai_success = false;
// Tratar retorno do Mercado Pago
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success' || $_GET['status'] === 'success_gravatai') {
        $tipo_entrega_final = (isset($_GET['entrega']) && $_GET['entrega'] === 'gravatai') || $_GET['status'] === 'success_gravatai' ? 'gravatai' : (isset($_GET['entrega']) ? $_GET['entrega'] : 'entrega');
        $is_gravatai_success = ($tipo_entrega_final === 'gravatai');
        $frete_final = isset($_GET['frete']) ? (float)$_GET['frete'] : (($tipo_entrega_final === 'entrega') ? 20 : 0);
        
        $stmtCart = $pdo->prepare("SELECT ci.carta_id, ci.quantidade, c.preco FROM carrinho_itens ci JOIN cartas c ON ci.carta_id = c.id WHERE ci.usuario_id = ?");
        $stmtCart->execute([$user_id]);
        $itens = $stmtCart->fetchAll();
        
        if (count($itens) > 0) {
            $total_pedido = $frete_final;
            foreach ($itens as $item) {
                $total_pedido += $item['quantidade'] * $item['preco'];
            }
            
            $stmtPedido = $pdo->prepare("INSERT INTO pedidos (usuario_id, total, frete, tipo_entrega, status) VALUES (?, ?, ?, ?, 'Em preparação')");
            $stmtPedido->execute([$user_id, $total_pedido, $frete_final, $tipo_entrega_final]);
            $pedido_id = $pdo->lastInsertId();
            
            $stmtItem = $pdo->prepare("INSERT INTO pedidos_itens (pedido_id, carta_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            foreach ($itens as $item) {
                $stmtItem->execute([$pedido_id, $item['carta_id'], $item['quantidade'], $item['preco']]);
            }
            
            // Limpar o carrinho
            $stmtLimpar = $pdo->prepare("DELETE FROM carrinho_itens WHERE usuario_id = ?");
            $stmtLimpar->execute([$user_id]);
            
            // Enviar email de novo pedido
            require_once 'email_helper.php';
            enviar_email_pedido($pdo, $pedido_id, 'novo_pedido');
        }
        
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
    
    $success_url = $base_url . "checkout.php?status=success";
    if ($tipo_entrega === 'gravatai') {
        $success_url .= "&entrega=gravatai";
    } else if ($tipo_entrega === 'balcao') {
        $success_url .= "&entrega=balcao";
    } else {
        $success_url .= "&entrega=entrega&frete=" . urlencode($valor_frete);
    }
    
    $preference_data["back_urls"] = [
        "success" => $success_url,
        "failure" => $base_url . "checkout.php?status=failure",
        "pending" => $base_url . "checkout.php?status=pending"
    ];
    
    if (!in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
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
                <h1 style="color: #4caf50;">Pedido Realizado com Sucesso!</h1>
                <?php if($is_gravatai_success): ?>
                    <p>Você escolheu a opção de envio por Uber Flash / 99 Entrega (Gravataí).</p>
                    <p>O seu pagamento dos produtos foi aprovado! Agora, por favor, nos chame no WhatsApp para combinarmos o valor do frete e os detalhes do envio.</p>
                    <a href="https://wa.link/stslbm" target="_blank" class="btn primary-btn" style="margin-top:20px; display:inline-block; text-decoration:none; background-color: #25D366; border-color: #25D366; color: white;">
                        <ion-icon name="logo-whatsapp" style="vertical-align: middle;"></ion-icon> Enviar Mensagem
                    </a>
                <?php else: ?>
                    <p>Obrigado por comprar na RocketTCG. Seu pedido foi pago e está sendo processado.</p>
                <?php endif; ?>
                <br>
                <a href="index.php" class="btn ghost-btn" style="margin-top:15px; display:inline-block; text-decoration:none;">Voltar para a Loja</a>
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
            <p><strong>Tipo de Entrega:</strong> 
                <?php 
                    if($tipo_entrega === 'balcao') echo 'Retirar no Balcão'; 
                    else if($tipo_entrega === 'gravatai') echo 'Gravataí - Envio por Uber/99';
                    else echo 'Entrega no Endereço'; 
                ?>
            </p>
            <p><strong>Subtotal:</strong> R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></p>
            <p><strong>Frete:</strong> <?php echo $tipo_entrega === 'gravatai' ? 'A Combinar' : 'R$ ' . number_format($valor_frete, 2, ',', '.'); ?></p>
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
                    <input type="text" name="cep" id="cep_input" value="<?php echo htmlspecialchars($user['cep'] ?? ''); ?>" required>
                </div>
                <div class="form-group col">
                    <label>Estado</label>
                    <input type="text" name="estado" id="estado_input" value="<?php echo htmlspecialchars($user['estado'] ?? 'RS'); ?>" required>
                </div>
                <div class="form-group col">
                    <label>Cidade</label>
                    <input type="text" name="cidade" id="cidade_input" value="<?php echo htmlspecialchars($user['cidade'] ?? 'Cachoeirinha'); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Endereço</label>
                <input type="text" name="endereco" id="endereco_input" value="<?php echo htmlspecialchars($user['endereco'] ?? ''); ?>" required>
            </div>
            
            <div class="row">
                <div class="form-group col">
                    <label>Bairro</label>
                    <input type="text" name="bairro" id="bairro_input" value="<?php echo htmlspecialchars($user['bairro'] ?? ''); ?>" required>
                </div>
                <div class="form-group col">
                    <label>Número</label>
                    <input type="text" name="numero" id="numero_input" value="<?php echo htmlspecialchars($user['numero'] ?? ''); ?>" required>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var cepInput = document.getElementById('cep_input');
        if (cepInput) {
            cepInput.addEventListener('blur', function() {
                var cep = this.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    fetch('https://viacep.com.br/ws/' + cep + '/json/')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('endereco_input').value = data.logradouro || '';
                            document.getElementById('bairro_input').value = data.bairro || '';
                            document.getElementById('cidade_input').value = data.localidade || '';
                            document.getElementById('estado_input').value = data.uf || '';
                            document.getElementById('numero_input').focus();
                        }
                    })
                    .catch(err => console.error('Erro ao buscar CEP:', err));
                }
            });
        }
    });
    </script>
</body>
</html>
