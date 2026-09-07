<?php
$access_token = "TEST-7323590744743693-081820-52966af5c49889da075e83f9bc978a29-3600611593";

$tests = [
    "http://localhost:80/success",
    "http://192.168.1.5/success",
    "http://teste.local/success",
    "http://rockettcg.test/success"
];

foreach ($tests as $url) {
    $preference_data = [
        "items" => [["title" => "Teste", "quantity" => 1, "currency_id" => "BRL", "unit_price" => 10.0]],
        "back_urls" => ["success" => $url, "failure" => $url, "pending" => $url],
        "auto_return" => "approved"
    ];

    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$access_token, 'Content-Type: application/json']);
    
    $res = json_decode(curl_exec($ch), true);
    echo "URL: $url -> " . (isset($res['id']) ? "OK" : $res['message']) . "\n";
}
