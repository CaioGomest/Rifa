<?php
ini_set('max_execution_time', 60);
ini_set('memory_limit', '256M');

require_once('conexao.php');
require_once('functions/functions_sistema.php');

$config = listaInformacoes($conn);

// Verifica se o token foi passado via GET
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die('Token do pedido não informado.');
}

$token_pedido = $conn->real_escape_string($_GET['token']);

// Busca o pedido com o token informado
$qry = $conn->query("SELECT * FROM lista_pedidos WHERE token_pedido = '$token_pedido' LIMIT 1");

if (!$qry || $qry->num_rows === 0) {
    die('Pedido não encontrado.');
}

$pedido = $qry->fetch_assoc();

$order_id = $pedido["id"];
$reference_code = $pedido["id_mp"];
$metodo_pagamento = $pedido["metodo_pagamento"];
$produto_id = $pedido["campanha_id"];
$quantidade = (int)$pedido["quantidade"];
$valor_total = (int)$pedido["valor_total"];

if ($metodo_pagamento !== 'Pay2M') {
    die('Método de pagamento não é Pay2M.');
}

// Verifica status na Pay2M
check_order_pay2m($reference_code, $order_id, $produto_id, $quantidade, $config, $valor_total);

echo "Verificação finalizada para o pedido #$order_id (token: $token_pedido).";


// Funções reutilizadas abaixo

function gerar_token_acesso($base_url, $config)
{
    $client_id = $config['pay2m_client_key'];
    $client_secret = $config['pay2m_client_secret'];

    $credentials = base64_encode("$client_id:$client_secret");

    $headers = [
        "Authorization: Basic $credentials",
        "Content-Type: application/json"
    ];

    $body = json_encode(["grant_type" => "client_credentials"]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$base_url/api/auth/generate_token");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function check_order_pay2m($reference_code, $order_id, $product_id, $quantity, $config, $valor_total)
{
    global $conn;
    $base_url = "https://portal.pay2m.com.br";
    $token_data = gerar_token_acesso($base_url, $config);

    if (!$token_data || !isset($token_data['access_token'])) {
        echo "Erro ao gerar token de acesso.<br>";
        return;
    }

    $access_token = $token_data['access_token'];
    $url = "$base_url/api/v1/pix/qrcode/$reference_code";

    $headers = [
        "Authorization: Bearer $access_token",
        "Content-Type: application/json"
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    echo "<pre>Status HTTP: $http_code\nResposta:\n";
    print_r($data);
    echo "</pre>";

    if ($http_code !== 200 || isset($data['error'])) {
        echo "Erro na requisição ou status inválido.<br>";
        return;
    }

    if ($data['status'] == 'paid') {
        $conn->query("UPDATE lista_pedidos SET status = '1' WHERE id = '$order_id'");
        echo "Pedido marcado como PAGO.<br>";



        if ($config['habilitar_api_facebook'] == '1')
        {
            echo "
            <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '" . $config['pixel_facebook'] . "');
            fbq('track', 'Purchase', {
                value: " . number_format((float)$valor_total, 2, '.', '') . ",
                currency: 'BRL'
            });
            </script>";
        }
        





    } elseif ($data['status'] == 'expired') {
        $conn->query("UPDATE lista_pedidos SET status = '2' WHERE id = '$order_id'");
        echo "Pedido marcado como EXPIRADO.<br>";
    } else {
        echo "Status atual: " . $data['status'] . "<br>";
    }
}
