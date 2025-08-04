<?php

// Define a URL base do sistema
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $base_path = dirname(dirname($_SERVER['PHP_SELF']));
    $base_path = $base_path === '/' ? '' : $base_path;
    define('BASE_URL', $protocol . $host . $base_path);
}

/**
 * Função principal para gerar pagamento PIX via Pay2M
 * @param int $order_id ID do pedido
 * @param float $amount Valor do pagamento
 * @param string $customer_name Nome do cliente
 * @param string $customer_email Email do cliente
 * @param int $expiration_minutes Tempo de expiração em minutos
 * @return array Retorna array com status do pagamento e informações
 */
function pay2m_generate_pix($conn, $order_id, $amount, $client_name, $client_email, $order_expiration, $config) {
    try {
        // Validação dos parâmetros obrigatórios
        if (!$order_id || !$amount || !$client_name) {
            throw new Exception("Parâmetros obrigatórios não fornecidos");
        }

        if (!$client_email) {
            $client_email = 'no-reply@dropestore.com';
        }

        // Formatação do valor
        $amount = number_format((float)$amount, 2, '.', '');

        // Gerar o token de acesso
        $token_data = gerar_token_acesso(
            "https://portal.pay2m.com.br",
            $config['pay2m_client_key'],
            $config['pay2m_client_secret']
        );

        if (!$token_data || !isset($token_data['access_token'])) {
            throw new Exception("Falha ao gerar token de acesso");
        }

        $headers = [
            "Authorization: Bearer " . $token_data['access_token'],
            "Content-Type: application/json"
        ];

        // Preparar dados do pagamento
        $payment_data = [
            'value' => $amount,
            'generator_name' => $client_name,
            'external_reference' => (string)$order_id,
            'expiration_time' => $order_expiration * 60, // Convertendo minutos para segundos
            'payer_message' => 'Pagamento do Pedido #' . $order_id,
            'notification_url' => BASE_URL . 'webhook.php?notify=pay2m'
        ];

        // Inicializar cURL
        $ch = curl_init("https://portal.pay2m.com.br/api/v1/pix/qrcode");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Erro cURL: ' . curl_error($ch));
        }

        curl_close($ch);

        $pix_response = json_decode($response, true);

        if (!$pix_response || !isset($pix_response['content'])) {
            throw new Exception('Resposta inválida da API Pay2M');
        }

        // Atualizar informações no banco de dados
        $payment_method = 'Pay2M';
        $pix_code = $pix_response['content'];
        $reference_code = $pix_response['reference_code'];

        $sql = "UPDATE lista_pedidos
                SET metodo_pagamento = '$payment_method',
                    codigo_pix = '$pix_code',
                    id_mp = '$reference_code',
                    expiracao_pedido = '$order_expiration'
                WHERE id = $order_id";

        if (!$conn->query($sql)) {
            throw new Exception('Erro ao atualizar o banco de dados: ' . $conn->error);
        }

        return [
            'success' => true,
            'order_id' => $order_id,
            'pix_code' => $pix_code,
            'reference_code' => $reference_code
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function gerar_token_acesso($base_url, $client_id, $client_secret) {
    if (empty($client_id) || empty($client_secret)) {
        die('Erro: CLIENT_ID ou CLIENT_SECRET não foram definidos.');
    }


    $credentials = base64_encode("$client_id:$client_secret");


    $headers = [
        "Authorization: Basic $credentials",
        "Content-Type: application/json"
    ];


    $body = json_encode([
        "grant_type" => "client_credentials"
    ]);

    // Iniciar cURL para fazer a requisição POST
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$base_url/api/auth/generate_token");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Executar a requisição
    $response = curl_exec($ch);

    // Verificar se houve erros no cURL
    if (curl_errno($ch)) {
        echo 'Erro cURL: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }

    // Fechar o cURL
    curl_close($ch);

    // Decodificar a resposta
    $token_data = json_decode($response, true);

    // Verificar se houve resposta e se o token foi gerado
    if (isset($token_data['access_token'])) {
        return $token_data;
    } else {
        // Exibir a resposta de erro para depuração
        echo 'Erro ao gerar o token: ' . $response;
        return false;
    }
}

