<?php
// inter_api.php

$config = require 'config.php';

function getToken($config) {
    echo "Funcao GetToken";
    $url = $config['api_url'] . '/oauth/v2/token';
    $data = [
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'grant_type' => 'client_credentials',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSLCERT, $config['cert_path']);
    curl_setopt($ch, CURLOPT_SSLKEY, $config['key_path']);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Erro: ' . curl_error($ch);
        exit;
    }
    curl_close($ch);

    $response_data = json_decode($response, true);
    return $response_data['access_token'] ?? null;
}

// function pesquisaCobranca($config, $token) {
//     $url = $config['api_url'] . '/cobranca/v2/boletos';

//     $ch = curl_init($url);
//     curl_setopt($ch, CURLOPT_HTTPGET, true);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSLCERT, $config['cert_path']);
//     curl_setopt($ch, CURLOPT_SSLKEY, $config['key_path']);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, [
//         'Authorization: Bearer ' . $token,
//         'Content-Type: application/json',
//     ]);

//     $response = curl_exec($ch);
//     if (curl_errno($ch)) {
//         echo 'Erro: ' . curl_error($ch);
//         exit;
//     }
//     curl_close($ch);

//     return json_decode($response, true);
// }

$token = getToken($config);
echo $token.'<br>';
echo "Teste";
// if ($token) {
//     $cobrancas = pesquisaCobranca($config, $token);
//     print_r($cobrancas);
// } else {
//     echo 'Falha ao obter token de acesso';
// }
