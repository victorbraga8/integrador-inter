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
        'scope' => 'extrato.read boleto-cobranca.read boleto-cobranca.write pagamento-boleto.write pagamento-boleto.read pagamento-darf.write cob.write cob.read cobv.write cobv.read pix.write pix.read webhook.read webhook.write payloadlocation.write payloadlocation.read pagamento-pix.write pagamento-pix.read webhook-banking.write webhook-banking.read',
        'grant_type' => 'client_credentials'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Erro ao obter token: ' . $error);
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
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Erro ao enviar cobrança: ' . $error.'<br>');
    }
    curl_close($ch);

    $data = json_decode($response);
    if (isset($data->codigoSolicitacao)) {
        return $data->codigoSolicitacao; // Retorna o código de solicitação para ser usado na função baixarPdf
    } else {
        throw new Exception('Cobrança não enviada. Resposta: ' . $response.'<br>');
    }
}

function listarCobrancas($token) {
    global $baseUrl, $certPath, $keyPath, $cc;

    // Definir parâmetros de consulta
    $queryString = http_build_query([
        'dataInicial' => '2024-01-01',
        'dataFinal' => '2024-12-31',
        'situacao' => 'VENCIDO',
        'tipoOrdenacao' => 'ASC',
        'itensPorPagina' => 100,
        'paginaAtual' => 2
    ]);

    $auth = 'Authorization: Bearer ' . $token;
    $json = 'Content-Type: application/json';

        // Construir a URL final
    $url = "$baseUrl/cobranca/v3/cobrancas?" . $queryString;

    // Exibir a URL para depuração
    echo "URL de requisição: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$auth, $cc, $json]);
    curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
    curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);

    curl_close($ch);

    if ($error !== '') {
        throw new Exception('Erro ao listar cobranças: ' . $error.'<br>');
    }

    return $result;
}

function baixarPdf($bearerToken, $codigoSolicitacao) {
    global $certPath, $keyPath, $cc;

    $auth = 'Authorization: Bearer ' . $bearerToken;
    $cc = 'x-conta-corrente: xpto';
    $json = 'Content-Type: application/json';
    
    $url = "https://cdpj-sandbox.partners.uatinter.co/cobranca/v3/cobrancas/$codigoSolicitacao/pdf";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$auth, $cc, $json]);

    // Certificado e chave privada
    curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
    curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('Erro ao baixar PDF: ' . $error);
    }

    curl_close($ch);

    $data = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Erro ao decodificar o JSON: ' . json_last_error_msg());
    }

    if (!isset($data['pdf'])) {
        throw new Exception('Resposta da API não contém o campo "pdf"');
    }

    $pdfContent = base64_decode($data['pdf']);
    if ($pdfContent === false) {
        throw new Exception('Erro ao decodificar o conteúdo base64');
    }

    $fileName = 'cobranca_' . $codigoSolicitacao . '.pdf';
    $filePath = __DIR__ . '/' . $fileName;
    
    file_put_contents($filePath, $pdfContent);
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($filePath));
    
    readfile($filePath);
    
    unlink($filePath);
    
    return "PDF baixado e forçado o download: $filePath";
}






// Supondo que $data seja o resultado da chamada listarCobrancas()
$token = "53d8e7c1-ee01-4327-9895-5160df45682c";

baixarPdf($token, "238ab1a9-5bac-4b9a-ad48-95cd3a11fdce");
// Chamar a função e obter o JSON como string
$jsonString = listarCobrancas($token);

// Decodificar o JSON para um array associativo
$dataArray = json_decode($jsonString, true);


// Verificar se a decodificação foi bem-sucedida
if (json_last_error() === JSON_ERROR_NONE) {
    // Iterar sobre cada cobrança
    foreach ($dataArray['cobrancas'] as $cobranca) {
        // Exibir detalhes da cobrança
        echo "<h2>Cobrança</h2>";
        echo "<p>Código de Solicitação: " . htmlspecialchars($cobranca['cobranca']['codigoSolicitacao']) . "</p>";
        echo "<p>Seu Número: " . htmlspecialchars($cobranca['cobranca']['seuNumero']) . "</p>";
        echo "<p>Situação: " . htmlspecialchars($cobranca['cobranca']['situacao']) . "</p>";
        echo "<p>Data da Situação: " . htmlspecialchars($cobranca['cobranca']['dataSituacao']) . "</p>";
        echo "<p>Data de Emissão: " . htmlspecialchars($cobranca['cobranca']['dataEmissao']) . "</p>";
        echo "<p>Data de Vencimento: " . htmlspecialchars($cobranca['cobranca']['dataVencimento']) . "</p>";
        echo "<p>Valor Nominal: " . htmlspecialchars($cobranca['cobranca']['valorNominal']) . "</p>";
        echo "<p>Tipo de Cobrança: " . htmlspecialchars($cobranca['cobranca']['tipoCobranca']) . "</p>";

        // Exibir detalhes do pagador
        echo "<h3>Pagador</h3>";
        echo "<p>Nome: " . htmlspecialchars($cobranca['cobranca']['pagador']['nome']) . "</p>";
        echo "<p>CPF/CNPJ: " . htmlspecialchars($cobranca['cobranca']['pagador']['cpfCnpj']) . "</p>";

        // Exibir detalhes do boleto
        echo "<h3>Boleto</h3>";
        echo "<p>Linha Digitável: " . htmlspecialchars($cobranca['boleto']['linhaDigitavel']) . "</p>";
        echo "<p>Código de Barras: " . htmlspecialchars($cobranca['boleto']['codigoBarras']) . "</p>";

        // Exibir detalhes do PIX
        echo "<h3>PIX</h3>";
        echo "<p>PIX Copia e Cola: " . htmlspecialchars($cobranca['pix']['pixCopiaECola']) . "</p>";
        echo "<p>TXID: " . htmlspecialchars($cobranca['pix']['txid']) . "</p>";

        echo "<hr>"; // Linha horizontal para separar as cobranças
    }
} else {
    echo "Erro ao decodificar JSON: " . json_last_error_msg();
}

?>
