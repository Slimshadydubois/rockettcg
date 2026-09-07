<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não logado']);
    exit;
}

$user_id = $_SESSION['usuario_id'];
$input = json_decode(file_get_contents('php://input'), true);
$cep_destino = isset($input['cep']) ? preg_replace('/[^0-9]/', '', $input['cep']) : '';

if (empty($cep_destino) || strlen($cep_destino) !== 8) {
    echo json_encode(['error' => 'CEP inválido']);
    exit;
}

// Obter itens do carrinho para calcular peso e valor
$stmt = $pdo->prepare("SELECT c.nome, c.categoria, c.preco, ci.quantidade, ci.variante, c.preco_reverse FROM carrinho_itens ci JOIN cartas c ON ci.carta_id = c.id WHERE ci.usuario_id = ?");
$stmt->execute([$user_id]);
$itens = $stmt->fetchAll();

if (empty($itens)) {
    echo json_encode(['error' => 'Carrinho vazio']);
    exit;
}

$num_cards = 0;
$num_boosters = 0;
$valor_total = 0;

foreach ($itens as $item) {
    $qty = (int)$item['quantidade'];
    $is_booster = (strtolower($item['categoria']) === 'booster' || stripos($item['nome'], 'booster') !== false);
    
    if ($is_booster) {
        $num_boosters += $qty;
    } else {
        $num_cards += $qty;
    }
    
    $preco = $item['variante'] === 'reverse' && $item['preco_reverse'] > 0 ? (float)$item['preco_reverse'] : (float)$item['preco'];
    $valor_total += $preco * $qty;
}

// 2 gramas a carta e 22 por booster
$peso_gramas = ($num_cards * 2) + ($num_boosters * 22);
$peso_kg = $peso_gramas / 1000;

// Se o peso for 0 (ex: produtos digitais? não deve acontecer, mas garantimos um mínimo)
if ($peso_kg <= 0) $peso_kg = 0.1;

// Dimensões fornecidas: 12 largura, 10 altura. Vamos assumir 16 de comprimento como padrão mínimo dos Correios.
$largura = 12;
$altura = 10;
$comprimento = 16; 

$cep_origem = "92425650";
$melhor_envio_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiOTliNjljMDkxMDI2YTU5OGZkN2IwNjY3YzJjMzJkNjZmNzRjODM5YTEyOGZiMDY5MDBlZjY2MjUwMWY2MTRkNWQ4M2JlNzZmMWE3NmE5MzgiLCJpYXQiOjE3ODcwOTkyOTMuMzQyMDY1LCJuYmYiOjE3ODcwOTkyOTMuMzQyMDY3LCJleHAiOjE4MTg2MzUyOTMuMzMwMjk0LCJzdWIiOiJhMjg5MjNhYS0zMGU2LTQ5MDEtOTgyOS02NmQ3MmM2YTI3NTgiLCJzY29wZXMiOlsiY2FydC1yZWFkIiwiY2FydC13cml0ZSIsImNvbXBhbmllcy1yZWFkIiwiY29tcGFuaWVzLXdyaXRlIiwiY291cG9ucy1yZWFkIiwiY291cG9ucy13cml0ZSIsIm5vdGlmaWNhdGlvbnMtcmVhZCIsIm9yZGVycy1yZWFkIiwicHJvZHVjdHMtcmVhZCIsInByb2R1Y3RzLWRlc3Ryb3kiLCJwcm9kdWN0cy13cml0ZSIsInB1cmNoYXNlcy1yZWFkIiwic2hpcHBpbmctY2FsY3VsYXRlIiwic2hpcHBpbmctY2FuY2VsIiwic2hpcHBpbmctY2hlY2tvdXQiLCJzaGlwcGluZy1jb21wYW5pZXMiLCJzaGlwcGluZy1nZW5lcmF0ZSIsInNoaXBwaW5nLXByZXZpZXciLCJzaGlwcGluZy1wcmludCIsInNoaXBwaW5nLXNoYXJlIiwic2hpcHBpbmctdHJhY2tpbmciLCJlY29tbWVyY2Utc2hpcHBpbmciLCJ0cmFuc2FjdGlvbnMtcmVhZCIsInVzZXJzLXJlYWQiLCJ1c2Vycy13cml0ZSIsIndlYmhvb2tzLXJlYWQiLCJ3ZWJob29rcy13cml0ZSIsIndlYmhvb2tzLWRlbGV0ZSIsInRkZWFsZXItd2ViaG9vayJdfQ.uoyI2fMfSzUK2UyyHUByrORC-uOHItZe1q5RnXI4FCfRRtzCXZZDI0_YWQYVwSYCnw84cstQMS0DAOi0zrXSS37gRrzk068JafV6sKyqojmhyq4O3AO2wa3OX18kJrgZJAxqC6laiLkYy07ZxgDO3WVuRIUkWAyjkdX6AH2QZCKkv614KYbrfebcYrcVyyO8LcMkazsvFr57FER6Hc3ZP-0zQgDUS13R7lcmwWc0TNcVcuu_J9NYYW5QqlVn_vyTFOUAeh5GB-_oRs_X9hHjXsl61Vv0DbcPpIpr_9_5A5xXSzspDhXXilLyJV7-ZDr6WcUo0wbCJkx2Fb1SxcjTOhr75cKxCLEOFdm7SIT9B7Uv3hBZMBYumSmT-XmfKvgyRgRJy0SIznlACkemTsLjn9SOYxLZ_xL-ehnP0Q04JloikM-5rl_h9xS7U33HScu3DZWfjTgm8DnrweOz1PYNGa9EWuC5XrJehCHTxkLbCSwy9YHOvM3LICsEPVZp5MCG0WJlWj86g4Xza7C4vnq1GMlc0kmfGr36GClyemaXxF9tGqrN-AL1AgBZCVu4LxxF4-JatzLx1qKuVcJiDfjdummTVRYarb1_8_DLxpF_anPvAs_TdZ4Qdq2oBBJWDF4ZjiwQgXv4myQ3fS8M79TN3AZ-Fbhc3dm9aRhPahiwJSc";

$payload = [
    "from" => [
        "postal_code" => $cep_origem
    ],
    "to" => [
        "postal_code" => $cep_destino
    ],
    "package" => [
        "weight" => $peso_kg,
        "width" => $largura,
        "height" => $altura,
        "length" => $comprimento
    ],
    "options" => [
        "insurance_value" => $valor_total,
        "receipt" => false,
        "own_hand" => false
    ]
];

$ch = curl_init('https://www.melhorenvio.com.br/api/v2/me/shipment/calculate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer ' . $melhor_envio_token,
    'User-Agent: Aplicação (contato@rockettcg.com)'
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200) {
    echo json_encode(['error' => 'Erro na API do Melhor Envio', 'details' => json_decode($response, true)]);
    exit;
}

$result = json_decode($response, true);
$opcoes_frete = [];

if (is_array($result)) {
    foreach ($result as $opcao) {
        if (isset($opcao['price']) && !isset($opcao['error'])) {
            $opcoes_frete[] = [
                'id' => $opcao['id'],
                'nome' => $opcao['name'],
                'empresa' => $opcao['company']['name'] ?? '',
                'preco' => (float)$opcao['price'],
                'prazo' => $opcao['delivery_time']
            ];
        }
    }
}

// Ordenar do mais barato para o mais caro
usort($opcoes_frete, function($a, $b) {
    return $a['preco'] <=> $b['preco'];
});

echo json_encode(['success' => true, 'opcoes' => $opcoes_frete]);
