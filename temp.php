<?php

// Configurações globais
$baseUrl = 'https://cdpj-sandbox.partners.uatinter.co';
$certPath = __DIR__ . '/certificados/Sandbox_InterAPI_Certificado.crt';
$keyPath = __DIR__ . '/certificados/Sandbox_InterAPI_Chave.key';
$clientId = '9cdbd5c4-9558-4b3b-b2e1-f0adec36c19c';
$clientSecret = '65eb947b-5385-45dd-b9c3-3270d2afb55e';
$cc = 'x-conta-corrente: xpto';

// Função para obter o token
function obterToken() {
    global $baseUrl, $certPath, $keyPath, $clientId, $clientSecret;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/oauth/v2/token");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
    curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'scope' => 'boleto-cobranca.write',
        'grant_type' => 'client_credentials'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        throw new Exception('Erro cURL: ' . curl_error($ch));
    }
    curl_close($ch);

    $data = json_decode($response);
    if (isset($data->access_token)) {
        return $data->access_token;
    } else {
        throw new Exception('Token não obtido. Resposta: ' . $response);
    }
}

// Função para enviar a cobrança
function enviarCobranca($token) {
    global $baseUrl, $certPath, $keyPath, $cc;

    $data = json_encode([
        "seuNumero" => "051667",
        "valorNominal" => 566,
        "valorAbatimento" => 0,
        "dataVencimento" => "2024-12-21",
        "numDiasAgenda" => 30,
        "atualizarPagador" => false,
        "pagador" => [
            "cpfCnpj" => "07799085460",
            "tipoPessoa" => "FISICA",
            "nome" => "Teste telefone",
            "endereco" => "Rua Hemogenes da Costa Carvalho",
            "cidade" => "Ouro Branco",
            "uf" => "MG",
            "cep" => "36420000",
            "email" => "alissonvla@gmail.com",
            "ddd" => "31",
            "telefone" => "997803008",
            "numero" => "301",
            "complemento" => "Casa",
            "bairro" => "Centro"
        ],
        "desconto1" => [
            "codigoDesconto" => "PERCENTUALDATAINFORMADA",
            "taxa" => 4,
            "valor" => 0,
            "data" => "2023-03-15"
        ],
        "desconto2" => [
            "codigoDesconto" => "PERCENTUALDATAINFORMADA",
            "taxa" => 2,
            "valor" => 0,
            "data" => "2023-03-20"
        ],
        "mensagem" => [
            "linha1" => "mensagem na linha 1",
            "linha2" => "mensagem na linha 2",
            "linha4" => "",
            "linha5" => "mensagem na linha 5"
        ]
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/cobranca/v3/cobrancas");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        $cc,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
    curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        throw new Exception('Erro cURL: ' . curl_error($ch));
    }
    curl_close($ch);

    $data = json_decode($response);
    if (isset($data->codigoSolicitacao)) {
        return $data->codigoSolicitacao; // Retorna o código de solicitação para ser usado na função baixarPdf
    } else {
        throw new Exception('Cobrança não enviada. Resposta: ' . $response);
    }
}

// Função para baixar o PDF
// function baixarPdf($token, $codigoSolicitacao) {
//     global $baseUrl, $certPath, $keyPath, $cc;

//     $url = "$baseUrl/cobranca/v3/cobrancas/$codigoSolicitacao/pdf";

//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
//     curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, [
//         "Authorization: Bearer $token",
//         'Content-Type: application/json',
//         $cc
//     ]);
//     curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
//     curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//     $response = curl_exec($ch);
//     if ($response === false) {
//         throw new Exception('Erro cURL: ' . curl_error($ch));
//     }
//     curl_close($ch);

//     return $response; // Retorna o conteúdo do PDF
// }

function baixarPdf($token, $codigoSolicitacao) {
    echo "Baixar PDF <br>";
    global $baseUrl, $certPath, $keyPath, $contaCorrente;

    $auth = 'Authorization: Bearer ' . $token;
    $json = 'Content-Type: application/json';
    $urlPdf = "$baseUrl/cobranca/v3/cobrancas/$codigoSolicitacao/pdf";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlPdf);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$auth, $contaCorrente, $json]);
    curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
    curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $pdfContent = curl_exec($ch);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error !== '') {
        throw new Exception("Erro no cURL: $error");
    }

    if (empty($pdfContent)) {
        throw new Exception("PDF não encontrado.");
    }

    // Salvar o PDF no servidor
    $filePath = __DIR__ . "/cobranca_$codigoSolicitacao.pdf";
    file_put_contents($filePath, $pdfContent);

    return $filePath;
}

// Fluxo principal
try {
    $token = obterToken();
    echo "Token obtido: $token\n";

    $codigoSolicitacao = enviarCobranca($token);
    echo "Código da solicitação: $codigoSolicitacao\n";

    baixarPdf($token, $codigoSolicitacao);

    // Baixa o PDF usando o código da solicitação
    $pdf = baixarPdf($token, $codigoSolicitacao);
    file_put_contents('cobranca.pdf', $pdf); // Salva o PDF no arquivo
    echo "PDF baixado e salvo como cobranca.pdf\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}

?>
