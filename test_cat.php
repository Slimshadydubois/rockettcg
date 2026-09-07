<?php
require 'config.php';
$stmt = $pdo->query("SELECT * FROM cartas WHERE nome = 'Applin'");
$carta = $stmt->fetch();
$rarities = array($carta['raridade']);
$is_special = (isset($carta['preco_reverse']) && $carta['preco_reverse'] > 0) || (isset($carta['tipo_energia']) && strtolower(trim($carta['tipo_energia'])) !== 'normal' && trim($carta['tipo_energia']) !== '');
if ($is_special) {
    $rarities[] = 'Holografica';
}
echo "Rarities: " . implode(',', $rarities) . "\n";
echo "Preco reverse: " . $carta['preco_reverse'] . "\n";
echo "Tipo energia: " . $carta['tipo_energia'] . "\n";
?>
