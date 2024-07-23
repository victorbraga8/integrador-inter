// Baixar o PDF usando a função


// Chamar a função para listar cobranças e obter o JSON como string
// try {
//     $jsonString = listarCobrancas($token);
//     // Decodificar o JSON para um array associativo
//     $dataArray = json_decode($jsonString, true);

//     // Verificar se a decodificação foi bem-sucedida
//     if (json_last_error() === JSON_ERROR_NONE) {
//         // Iterar sobre cada cobrança
//         foreach ($dataArray['cobrancas'] as $cobranca) {
//             // Exibir detalhes da cobrança
//             echo "<h2>Cobrança</h2>";
//             echo "<p>Código de Solicitação: " . htmlspecialchars($cobranca['cobranca']['codigoSolicitacao']) . "</p>";
//             echo "<p>Seu Número: " . htmlspecialchars($cobranca['cobranca']['seuNumero']) . "</p>";
//             echo "<p>Situação: " . htmlspecialchars($cobranca['cobranca']['situacao']) . "</p>";
//             echo "<p>Data da Situação: " . htmlspecialchars($cobranca['cobranca']['dataSituacao']) . "</p>";
//             echo "<p>Data de Emissão: " . htmlspecialchars($cobranca['cobranca']['dataEmissao']) . "</p>";
//             echo "<p>Data de Vencimento: " . htmlspecialchars($cobranca['cobranca']['dataVencimento']) . "</p>";
//             echo "<p>Valor Nominal: " . htmlspecialchars($cobranca['cobranca']['valorNominal']) . "</p>";
//             echo "<p>Tipo de Cobrança: " . htmlspecialchars($cobranca['cobranca']['tipoCobranca']) . "</p>";

//             // Exibir detalhes do pagador
//             echo "<h3>Pagador</h3>";
//             echo "<p>Nome: " . htmlspecialchars($cobranca['cobranca']['pagador']['nome']) . "</p>";
//             echo "<p>CPF/CNPJ: " . htmlspecialchars($cobranca['cobranca']['pagador']['cpfCnpj']) . "</p>";

//             // Exibir detalhes do boleto
//             echo "<h3>Boleto</h3>";
//             echo "<p>Linha Digitável: " . htmlspecialchars($cobranca['boleto']['linhaDigitavel']) . "</p>";
//             echo "<p>Código de Barras: " . htmlspecialchars($cobranca['boleto']['codigoBarras']) . "</p>";

//             // Exibir detalhes do PIX
//             echo "<h3>PIX</h3>";
//             echo "<p>PIX Copia e Cola: " . htmlspecialchars($cobranca['pix']['pixCopiaECola']) . "</p>";
//             echo "<p>TXID: " . htmlspecialchars($cobranca['pix']['txid']) . "</p>";

//             echo "<hr>"; // Linha horizontal para separar as cobranças
//         }
//     } else {
//         echo "Erro ao decodificar JSON: " . json_last_error_msg();
//     }
// } catch (Exception $e) {
//     echo 'Erro ao listar cobranças: ' . $e->getMessage();
// }



function baixarPdf2($bearerToken, $codigoSolicitacao) {
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