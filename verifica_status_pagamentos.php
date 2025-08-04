<?php
ini_set('max_execution_time', 300); // 5 minutos
ini_set('memory_limit', '512M');
set_time_limit(300);
require_once('conexao.php');
require_once('functions/functions_sistema.php');

$config = listaInformacoes($conn);


function check_order_status($config)
{
    global $conn;
    $qry = $conn->query("SELECT * FROM lista_pedidos WHERE status = 0 ORDER BY data_criacao ASC LIMIT 15;"); // 0 = pendente
    $resp = [];

    

    if ($qry && $qry->num_rows > 0) {
        while ($row = $qry->fetch_assoc()) {
            $order_id = $row["id"];
            $cliente_id = $row["cliente_id"];
            $data_criacao = $row["data_criacao"];
            $expiracao_pedido = $row["expiracao_pedido"];
            $metodo_pagamento = $row["metodo_pagamento"];
            $id_mp = $row["id_mp"];
            $token_pedido = $row["token_pedido"];
            $produto_id = $row["campanha_id"]; // ou ajuste conforme sua lógica
            $quantidade = (int)$row["quantidade"];

            $currentDateTime= date(
                "Y-m-d H:i:s",
                strtotime($data_criacao . " + " . (int)$expiracao_pedido . " minutes")
            );
            $expirationTime = date("Y-m-d H:i:s");

// echo  "Expiration Time: ";
// var_dump($expirationTime);
// echo  "---------</br>";
// echo  "---------<br>";

// echo  "currentDateTime: ";

// var_dump($currentDateTime);
// die;

            if ($expirationTime < $currentDateTime && (int)$expiracao_pedido > 0) {
                $conn->query("UPDATE lista_pedidos SET status = '2' WHERE token_pedido = '$token_pedido'"); // 2 = cancelado
            } else {
                switch ($metodo_pagamento) {
                    // case 'MercadoPago':
                    //     check_order_mp($order_id, $id_mp);
                    //     break;
                    // case 'Paggue':
                    //     check_order_pg($order_id, $id_mp);
                    //     break;
                    case 'Pay2M':
                        check_order_pay2m($id_mp, $order_id, $produto_id, $quantidade,$config);
                        break;
                }
            }
        }
    }
 

    return json_encode(['status' => 'checked']);
}

// PAY2M
function gerar_token_acesso($base_url,$config)
{
    $client_id = $config['pay2m_client_key'];
    $client_secret = $config['pay2m_client_secret'];

    if (empty($client_id) || empty($client_secret)) {
        die('Erro: CLIENT_ID ou CLIENT_SECRET não definidos.');
    }

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

    $token_data = json_decode($response, true);

    if (isset($token_data['access_token'])) {
        return $token_data;
    } else {
        echo 'Erro ao gerar token: ' . $response;
        return false;
    }
}


function check_order_pay2m($reference_code, $order_id, $product_id, $quantity,$config)
{
    global $conn;
    $base_url = "https://portal.pay2m.com.br";
    $token_data = gerar_token_acesso($base_url, $config);

    if (!$token_data) {
        $conn->query("UPDATE lista_pedidos SET status = '2' WHERE id = '$order_id'"); // 2 = cancelado
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


    if ($http_code !== 200) {
        $conn->query("UPDATE lista_pedidos SET status = '2' WHERE id = '$order_id'");
        return;
    }

    $data = json_decode($response, true);

    if (isset($data['error'])) {
        $conn->query("UPDATE lista_pedidos SET status = '2' WHERE id = '$order_id'");
        return;
    }

    if ($data['status'] == 'paid') {
        $conn->query("UPDATE lista_pedidos SET status = '1' WHERE id = '$order_id'"); // 1 = pago
        // Aqui você pode adicionar lógica para reduzir quantidade, se houver.
    } elseif ($data['status'] == 'expired') {
        $conn->query("UPDATE lista_pedidos SET status = '2' WHERE id = '$order_id'"); // 2 = cancelado
    }
}

check_order_status( $config);
