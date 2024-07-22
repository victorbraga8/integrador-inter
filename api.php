<?php

$baseUrl = 'https://cdpj-sandbox.partners.uatinter.co';
$certPath = '/certificados/Sandbox_InterAPI_Certificado.crt';
$keyPath = '/certificados/Sandbox_InterAPI_Chave.key';
$clientId = '9cdbd5c4-9558-4b3b-b2e1-f0adec36c19c';
$clientSecret = '65eb947b-5385-45dd-b9c3-3270d2afb55e';

// Função para obter o token de acesso
function getAccessToken()
{
    $url = "https://cdpj-sandbox.partners.uatinter.co/oauth/v2/token";
    $client_id = "9cdbd5c4-9558-4b3b-b2e1-f0adec36c19c";
    $client_secret = "65eb947b-5385-45dd-b9c3-3270d2afb55e";
    $grant_type = "client_credentials";

    // Configuração dos dados do corpo da requisição
    $data = array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => $grant_type,
    );

    // Certificados
    $cert_path = __DIR__ . "/certificados/Sandbox_InterAPI_Certificado.crt";
    $key_path = __DIR__ . "/certificados/Sandbox_InterAPI_Chave.key";
    $key_password = "1234";

    if (!file_exists($cert_path)) {
        echo "Certificado não encontrado: $cert_path\n";
        return;
    }

    if (!file_exists($key_path)) {
        echo "Chave não encontrada: $key_path\n";
        return;
    }

    // Inicializa o cURL
    $ch = curl_init();

    // Configurações do cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSLCERT, $cert_path);
    curl_setopt($ch, CURLOPT_SSLKEY, $key_path);
    curl_setopt($ch, CURLOPT_KEYPASSWD, $key_password);

    // Executa a requisição
    $response = curl_exec($ch);

    // Verifica por erros
    if (curl_errno($ch)) {
        echo 'Erro no cURL: ' . curl_error($ch);
    } else {
        // Obtém o código de resposta HTTP
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 200) {
            // echo 'Sucesso: ';
            // echo $response;

            // Decodifica a resposta JSON
            $response_data = json_decode($response, true);

            // Verifica se a decodificação foi bem-sucedida e se o access_token está presente
            if (json_last_error() === JSON_ERROR_NONE && isset($response_data['access_token'])) {
                $access_token = $response_data['access_token'];
                return $access_token;
                echo "\nAccess Token: $access_token\n";
            } else {
                echo "\nErro ao decodificar a resposta JSON ou access_token não encontrado.\n";
            }
        } else {
            echo 'Erro: HTTP Código ' . $http_code . ' - ' . $response;
        }
    }

    // Fecha a sessão cURL
    curl_close($ch);
}

// Chama a função para obter o token
// $token = getAccessToken();
// echo $token;

// Função para criar uma cobrança
function createCobranca()
{
            
    $client_id = "9cdbd5c4-9558-4b3b-b2e1-f0adec36c19c";
    $client_secret = "65eb947b-5385-45dd-b9c3-3270d2afb55e";
    $grant_type = "client_credentials";

    // Configuração dos dados do corpo da requisição
    $data = array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => $grant_type,
    );

    // Certificados
    $cert_path = __DIR__ . "/certificados/Sandbox_InterAPI_Certificado.crt";
    $key_path = __DIR__ . "/certificados/Sandbox_InterAPI_Chave.key";
    $key_password = "1234";

// $bearerToken=$obj->{'access_token'};
$bearerToken = "dd2237a1-ce23-4c12-baa7-45b0426619ea";

$auth='Authorization: Bearer ' . $bearerToken;

$data=<<<DATA
{ 
  "seuNumero":"00667",
  "valorNominal":666,
  "valorAbatimento": 0,
  "dataVencimento":"2023-03-25",
  "numDiasAgenda":30,
  "atualizarPagador":false,
      "pagador":{
      "cpfCnpj":"07799085460",
      "tipoPessoa":"FISICA",
      "nome":"Teste telefone",
      "endereco":"Rua Hermogenes da Costa Carvalho",
      "cidade":"Ouro Branco",
      "uf":"MG",
      "cep":"36420000",
      "email":"alissonvla@gmail.com",
      "ddd":"31",
      "telefone": "997803008",
      "numero":"301",
      "complemento":"Casa",
      "bairro":"Centro"
  },
  "desconto1":{
      "codigoDesconto":"PERCENTUALDATAINFORMADA",
      "taxa":4,
      "valor":0,
      "data":"2023-03-15"
  },
  "desconto2":{
      "codigoDesconto":"PERCENTUALDATAINFORMADA",
      "taxa":2,
      "valor":0,
      "data":"2023-03-20"
  },
  "mensagem":{
      "linha1":"mensagem na linha 1",
      "linha2":"mensagem na linha 2",
      "linha4":"",
      "linha5":"mensagem na linha 5"
  } 
}
DATA;

$auth='Authorization: Bearer ' . $bearerToken;
$cc='x-conta-corrente: xpto';
$json='Content-Type: application/json';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://cdpj.partners.bancointer.com.br/cobranca/v3/cobrancas");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth,$cc,$json));
curl_setopt($ch, CURLOPT_SSLCERT, $cert_path);
curl_setopt($ch, CURLOPT_SSLKEY, $key_path);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = curl_exec($ch);
$error = curl_error($ch);
$errno = curl_errno($ch);

curl_close ($ch);

if ($error !== '') {
    throw new Exception($error);
}

print $result . "
";
}

createCobranca();

?>
