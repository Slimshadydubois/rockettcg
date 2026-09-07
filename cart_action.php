<?php
require_once 'config.php';

$is_ajax = isset($_POST['ajax']) && $_POST['ajax'] == '1';

if (!isset($_SESSION['usuario_id'])) {
    if ($is_ajax) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$carta_id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$return_url = $_POST['return_url'] ?? $_SERVER['HTTP_REFERER'] ?? 'index.php';

try {
    if ($action === 'add_cart') {
        if ($carta_id === 0) throw new Exception("Invalid ID");
        $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;
        $variante = isset($_POST['variante']) && $_POST['variante'] === 'reverse' ? 'reverse' : 'normal';
        
        $stmt_stock = $pdo->prepare("SELECT estoque, estoque_reverse FROM cartas WHERE id = ?");
        $stmt_stock->execute([$carta_id]);
        $carta_estoque = $stmt_stock->fetch();
        
        if (!$carta_estoque) throw new Exception("Carta não encontrada");
        $estoque_disponivel = $variante === 'reverse' ? (int)$carta_estoque['estoque_reverse'] : (int)$carta_estoque['estoque'];

        $stmt = $pdo->prepare("SELECT id, quantidade FROM carrinho_itens WHERE usuario_id = ? AND carta_id = ? AND variante = ?");
        $stmt->execute([$user_id, $carta_id, $variante]);
        $item = $stmt->fetch();

        $qtd_atual = $item ? (int)$item['quantidade'] : 0;
        
        if ($qtd_atual + $qty > $estoque_disponivel) {
            $qty = $estoque_disponivel - $qtd_atual;
        }

        if ($qty > 0) {
            if ($item) {
                $nova_qty = $qtd_atual + $qty;
                $pdo->prepare("UPDATE carrinho_itens SET quantidade = ? WHERE id = ?")->execute([$nova_qty, $item['id']]);
            } else {
                $pdo->prepare("INSERT INTO carrinho_itens (usuario_id, carta_id, quantidade, variante) VALUES (?, ?, ?, ?)")->execute([$user_id, $carta_id, $qty, $variante]);
            }
        } else if ($is_ajax && $qty === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Estoque máximo atingido']);
            exit;
        }
        
        $pdo->prepare("UPDATE cartas SET vezes_no_carrinho = vezes_no_carrinho + 1 WHERE id = ?")->execute([$carta_id]);
    } elseif ($action === 'remove_cart' || $action === 'remove_cart_ajax') {
        $cart_item_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
        if($cart_item_id > 0) {
            $pdo->prepare("DELETE FROM carrinho_itens WHERE id = ? AND usuario_id = ?")->execute([$cart_item_id, $user_id]);
        } else {
            if ($carta_id === 0) throw new Exception("Invalid ID");
            // fallback para compatibilidade caso remova da wishlist ou do botão de toggle
            $variante = isset($_POST['variante']) && $_POST['variante'] === 'reverse' ? 'reverse' : 'normal';
            $pdo->prepare("DELETE FROM carrinho_itens WHERE usuario_id = ? AND carta_id = ? AND variante = ?")->execute([$user_id, $carta_id, $variante]);
        }
    } elseif ($action === 'update_cart') {
        $qty = max(0, (int)$_POST['qty']); // Prevent negative input
        $cart_item_id = (int)$_POST['cart_id'];
        
        $stmt_cart = $pdo->prepare("SELECT carta_id, variante FROM carrinho_itens WHERE id = ? AND usuario_id = ?");
        $stmt_cart->execute([$cart_item_id, $user_id]);
        $cart_item = $stmt_cart->fetch();
        
        if ($cart_item) {
            $stmt_stock = $pdo->prepare("SELECT estoque, estoque_reverse FROM cartas WHERE id = ?");
            $stmt_stock->execute([$cart_item['carta_id']]);
            $carta_estoque = $stmt_stock->fetch();
            
            $estoque_disponivel = $cart_item['variante'] === 'reverse' ? (int)$carta_estoque['estoque_reverse'] : (int)$carta_estoque['estoque'];
            if ($qty > $estoque_disponivel) $qty = $estoque_disponivel;

            if ($qty > 0) {
                $pdo->prepare("UPDATE carrinho_itens SET quantidade = ? WHERE id = ? AND usuario_id = ?")->execute([$qty, $cart_item_id, $user_id]);
            } else {
                $pdo->prepare("DELETE FROM carrinho_itens WHERE id = ? AND usuario_id = ?")->execute([$cart_item_id, $user_id]);
            }
        }
    } elseif ($action === 'add_wishlist') {
        if ($carta_id === 0) throw new Exception("Invalid ID");
        $pdo->prepare("INSERT IGNORE INTO favoritos (usuario_id, carta_id) VALUES (?, ?)")->execute([$user_id, $carta_id]);
    } elseif ($action === 'remove_wishlist') {
        if ($carta_id === 0) throw new Exception("Invalid ID");
        $pdo->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND carta_id = ?")->execute([$user_id, $carta_id]);
    }

    if ($is_ajax) {
        $stmtCount = $pdo->prepare("SELECT SUM(quantidade) as total FROM carrinho_itens WHERE usuario_id = ?");
        $stmtCount->execute([$user_id]);
        $total_items = $stmtCount->fetchColumn() ?: 0;
        
        echo json_encode(['status' => 'success', 'cart_count' => $total_items]);
        exit;
    }

} catch (PDOException $e) {
    if ($is_ajax) { echo json_encode(['status' => 'error', 'message' => $e->getMessage()]); exit; }
}

header("Location: $return_url");
exit;
?>
