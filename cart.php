<?php 
require_once 'config.php'; 

if(!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT c.*, ci.quantidade, ci.variante, ci.id as cart_id FROM carrinho_itens ci JOIN cartas c ON ci.carta_id = c.id WHERE ci.usuario_id = ?");
$stmt->execute([$user_id]);
$itens = $stmt->fetchAll();

$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="cart.css">
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
                <ion-icon name="cart"></ion-icon>
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

    <main class="cart-container">
        <h1>Seu Carrinho de Compras</h1>
        
        <div class="cart-layout">
            <!-- Tabela de Produtos -->
            <div class="cart-items">
                <?php foreach($itens as $item): ?>
                <?php 
                    $is_sale = isset($item['promocao_porcentagem']) && $item['promocao_porcentagem'] > 0;
                    
                    // Definir o preço base de acordo com a variante
                    $preco_base = $item['variante'] === 'reverse' && $item['preco_reverse'] > 0 ? $item['preco_reverse'] : $item['preco'];
                    
                    $preco_final = $preco_base;
                    if ($is_sale) {
                        $preco_final = $preco_base - ($preco_base * ($item['promocao_porcentagem'] / 100));
                    }
                    $itemTotal = $preco_final * $item['quantidade'];
                    $subtotal += $itemTotal;
                    
                    $variante_nome = 'Normal';
                    if ($item['variante'] === 'reverse') {
                        $variante_nome = !empty($item['nome_variante']) ? htmlspecialchars($item['nome_variante']) : 'Reverse Foil';
                    }
                ?>
                <div class="cart-item-row">
                    <div class="cart-item-image">
                        <img src="<?php echo htmlspecialchars($item['imagem']); ?>" alt="Carta">
                    </div>
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['nome']); ?></h3>
                        <p><?php echo htmlspecialchars($item['edicao']); ?> | <?php echo htmlspecialchars($item['estado']); ?> | <?php echo $variante_nome; ?></p>
                    </div>
                    <div class="cart-item-quantity">
                        <form action="cart_action.php" method="POST" style="display:flex; align-items:center;">
                            <input type="hidden" name="action" value="update_cart">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" name="qty" value="<?php echo $item['quantidade'] - 1; ?>" class="qty-btn">-</button>
                            <span style="margin:0 10px;"><?php echo $item['quantidade']; ?></span>
                            <button type="submit" name="qty" value="<?php echo $item['quantidade'] + 1; ?>" class="qty-btn">+</button>
                        </form>
                    </div>
                    <div class="cart-item-price">
                        R$ <?php echo number_format($itemTotal, 2, ',', '.'); ?>
                    </div>
                    <form action="cart_action.php" method="POST">
                        <input type="hidden" name="action" value="remove_cart">
                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                        <button type="submit" class="remove-btn" title="Remover Item"><ion-icon name="trash-outline"></ion-icon></button>
                    </form>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($itens)): ?>
                    <div style="text-align:center; padding:40px; color:var(--text-muted); width:100%;">
                        <h3>Seu carrinho está vazio.</h3>
                        <a href="category.php" class="btn ghost-btn" style="margin-top:20px; display:inline-block;">Voltar à Loja</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Resumo do Pedido -->
            <!-- Resumo do Pedido -->
            <div class="cart-summary">
                <h3>Resumo do Pedido</h3>
                
                <?php
                    $stmtU = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
                    $stmtU->execute([$user_id]);
                    $user = $stmtU->fetch();
                ?>

                <form action="checkout.php" method="POST" id="checkout-form">
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin-bottom:10px; color:var(--accent-color);">Opções de Entrega</h4>
                        <label style="display:block; margin: 10px 0; cursor:pointer;"><input type="radio" name="tipo_entrega" value="entrega" id="tipo_entrega_envio" checked onchange="calcFrete()"> Entrega</label>
                        <label style="display:block; margin: 10px 0; cursor:pointer;"><input type="radio" name="tipo_entrega" value="balcao" id="tipo_entrega_balcao" onchange="calcFrete()"> Retirar no Balcão (Frete Grátis)</label>
                    </div>

                    <div id="frete_cep_box" style="margin-bottom: 20px;">
                        <label style="font-weight:600; font-size:0.9rem;">Calcular Frete para seu CEP:</label>
                        <div style="display:flex; gap:10px; margin-top:5px;">
                            <input type="text" name="cep_frete" id="cep_frete" placeholder="Inserir CEP" style="flex:1; padding:10px; border-radius:5px; border:1px solid #444; background: rgba(255,255,255,0.05); color:#fff; font-size:1rem;" value="<?php echo htmlspecialchars($user['cep'] ?? ''); ?>">
                            <button type="button" class="btn primary-btn" style="padding:10px 15px;" onclick="calcFrete()">Calcular</button>
                        </div>
                    </div>

                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>R$ <span id="val_subtotal"><?php echo number_format($subtotal, 2, ',', '.'); ?></span></span>
                    </div>
                    <div class="summary-line">
                        <span>Frete</span>
                        <span id="val_frete">R$ 20,00</span>
                    </div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>R$ <span id="val_total"><?php echo number_format($subtotal + 20, 2, ',', '.'); ?></span></span>
                    </div>
                    
                    <input type="hidden" id="raw_subtotal" value="<?php echo $subtotal; ?>">
                    <input type="hidden" id="raw_frete" name="valor_frete" value="20">
                    
                    <button type="submit" class="btn primary-btn checkout-btn" <?php echo empty($itens) ? 'disabled' : ''; ?>>Finalizar Compra</button>
                </form>
                
                <div class="secure-checkout" style="margin-top:15px; text-align:center; color:#aaa;">
                    <ion-icon name="lock-closed-outline"></ion-icon> Pagamento 100% Seguro
                </div>
            </div>
        </div>
    </main>
    
    <script>
        let opcoes_frete_global = [];

        function renderFreteOptions() {
            var container = document.getElementById('frete_options_container');
            if(!container) {
                container = document.createElement('div');
                container.id = 'frete_options_container';
                container.style.marginTop = '10px';
                document.getElementById('frete_cep_box').appendChild(container);
            }
            
            if(opcoes_frete_global.length === 0) {
                container.innerHTML = '<p style="color:var(--text-muted); font-size:0.9rem;">Nenhuma opção de frete encontrada para este CEP.</p>';
                return;
            }

            let html = '<div style="display:flex; flex-direction:column; gap:8px; margin-top:10px;">';
            opcoes_frete_global.forEach((opcao, index) => {
                let checked = index === 0 ? 'checked' : '';
                html += `
                    <label style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.05); padding:10px; border-radius:5px; cursor:pointer;">
                        <input type="radio" name="opcao_frete_selecionada" value="${opcao.preco}" onchange="updateFreteValue(${opcao.preco})" ${checked}>
                        <div style="flex:1;">
                            <span style="font-weight:bold;">${opcao.nome}</span> 
                            <span style="font-size:0.8rem; color:#aaa;">(${opcao.empresa})</span>
                            <div style="font-size:0.8rem; color:#aaa;">Prazo: ${opcao.prazo} dias úteis</div>
                        </div>
                        <div style="font-weight:bold; color:var(--accent-color);">
                            R$ ${opcao.preco.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                        </div>
                    </label>
                `;
            });
            html += '</div>';
            container.innerHTML = html;

            // Update total with the first (cheapest) option
            updateFreteValue(opcoes_frete_global[0].preco);
        }

        function updateFreteValue(fretePrice) {
            var subtotal = parseFloat(document.getElementById('raw_subtotal').value);
            var freteText = document.getElementById('val_frete');
            var totalText = document.getElementById('val_total');
            var inputFrete = document.getElementById('raw_frete');
            
            freteText.innerHTML = 'R$ ' + fretePrice.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            inputFrete.value = fretePrice;
            
            var total = subtotal + fretePrice;
            totalText.innerHTML = total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function calcFrete() {
            var subtotal = parseFloat(document.getElementById('raw_subtotal').value);
            var isBalcao = document.getElementById('tipo_entrega_balcao').checked;
            var freteBox = document.getElementById('frete_cep_box');
            var freteText = document.getElementById('val_frete');
            var totalText = document.getElementById('val_total');
            var inputFrete = document.getElementById('raw_frete');
            var container = document.getElementById('frete_options_container');
            
            if(isBalcao) {
                freteBox.style.display = 'none';
                if(container) container.innerHTML = '';
                
                var frete = 0;
                freteText.innerHTML = 'Grátis (Balcão)';
                inputFrete.value = frete;
                var total = subtotal + frete;
                totalText.innerHTML = total.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                freteBox.style.display = 'block';
                var cep = document.getElementById('cep_frete').value.replace(/\D/g, '');
                
                if(cep.length === 8) {
                    freteText.innerHTML = 'Calculando...';
                    
                    fetch('calcular_frete_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({cep: cep})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            opcoes_frete_global = data.opcoes;
                            renderFreteOptions();
                        } else {
                            if(container) container.innerHTML = `<p style="color:#ff5252; font-size:0.9rem;">${data.error}</p>`;
                            updateFreteValue(0);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if(container) container.innerHTML = '<p style="color:#ff5252; font-size:0.9rem;">Erro ao calcular o frete.</p>';
                        updateFreteValue(0);
                    });
                } else if(cep.length > 0) {
                    if(!container) {
                        container = document.createElement('div');
                        container.id = 'frete_options_container';
                        container.style.marginTop = '10px';
                        document.getElementById('frete_cep_box').appendChild(container);
                    }
                    container.innerHTML = '<p style="color:#ff5252; font-size:0.9rem;">Por favor, insira um CEP válido.</p>';
                } else {
                    updateFreteValue(0);
                }
            }
        }
        
        // init
        document.addEventListener('DOMContentLoaded', () => {
            var cepInput = document.getElementById('cep_frete');
            if(cepInput && cepInput.value.length >= 8) {
                calcFrete();
            } else {
                updateFreteValue(0);
            }
        });
    </script>
    <?php include 'footer.php'; ?>

    <script src="ajax_actions.js?v=2"></script>
</body>
</html>

