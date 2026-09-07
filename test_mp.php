<?php
$access_token = "TEST-7323590744743693-081820-52966af5c49889da075e83f9bc978a29-3600611593";

$preference_data = [
    "items" => [
        [
            "title" => "Teste",
            "quantity" => 1,
            "currency_id" => "BRL",
            "unit_price" => 10.0
        ]
    ],
    "back_urls" => [
        "success" => "http://localhost/success",
        "failure" => "http://localhost/failure",
        "pending" => "http://localhost/pending"
    ]
];

$ch = curl_init('https://api.mercadopago.com/checkout/preferences');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
]);

echo curl_exec($ch);
