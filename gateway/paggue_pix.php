<?php

// Define a URL base do sistema se ainda não estiver definida
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $base_path = dirname(dirname($_SERVER['PHP_SELF']));
    $base_path = $base_path === '/' ? '' : $base_path;
    define('BASE_URL', $protocol . $host . $base_path);
}

/**
 * Função principal para gerar pagamento PIX via Paggue
 * @param int $order_id ID do pedido
 * @param float $amount Valor do pagamento
 * @param string $customer_name Nome do cliente
 * @param string $customer_email Email do cliente
 * @param int $expiration_minutes Tempo de expiração em minutos
 * @return array Retorna array com status do pagamento e informações
 */
function paggue_generate_pix($conn, $id_pedido, $valor_total, $nome_cliente, $email_cliente, $tempo_expiracao, $config) {
    try {
        // Carrega dependências necessárias
        require_once('gateway/phpqrcode/qrlib.php');
        require_once('gateway/funcoes_pix.php');

        // Validação dos parâmetros obrigatórios
        if (!$id_pedido || !$valor_total || !$nome_cliente) {
            throw new Exception("Parâmetros obrigatórios não fornecidos");
        }

        if (!$email_cliente) {
            $email_cliente = 'no-reply@dropestore.com';
        }

        // Formatação e cálculo do valor com taxa
        $valor_total = drope_normalize_price($valor_total);
        $valor_total = number_format($valor_total, 2, '.', '');
        
        // Aplicar taxa se configurada
        $tax = isset($config['paggue_tax']) ? $config['paggue_tax'] : 0;
        $final_amount = $valor_total;
        
        if ($tax > 0) {
            $tax_amount = $valor_total * ($tax / 100);
            $final_amount = $valor_total + $tax_amount;
        }

        // Converter para centavos (inteiro)
        $amount_cents = (int)($final_amount * 100);

        // Gerar PIX via Paggue
        $paggue_response = drope_paggue_create_order(
            $nome_cliente,
            $id_pedido,
            $amount_cents,
            $id_pedido,
            $config
        );

        if (!$paggue_response || !isset($paggue_response['pix'])) {
            throw new Exception("Erro ao gerar PIX no Paggue");
        }

        // Processar resposta e gerar QR Code
        $pix_code = $paggue_response['pix'];
        $hash = $paggue_response['hash'];
        
        // Decodificar e montar o PIX
        $px = decode_brcode($pix_code);
        $pix_montado = montaPix($px);
        
        // Gerar QR Code em base64
        ob_start();
        QRCode::png($pix_montado, null, 'M', 5);
        $pix_qrcode = base64_encode(ob_get_contents());
        ob_end_clean();

        // Atualizar informações no banco de dados
        $payment_method = 'Paggue';
        $sql = "UPDATE lista_pedidos 
                SET metodo_pagamento = '$payment_method',
                    codigo_pix = '$pix_code',
                    qrcode_pix = '$pix_qrcode',
                    id_mp = '$hash',
                    expiracao_pedido = '$tempo_expiracao'
                WHERE id = $id_pedido";

        if (!$conn->query($sql)) {
            throw new Exception('Erro ao atualizar o banco de dados: ' . $conn->error);
        }

        return [
            'success' => true,
            'id_pedido' => $id_pedido,
            'pix_code' => $pix_code,
            'qrcode_pix' => $pix_qrcode,
            'reference_code' => $hash
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function drope_paggue_create_order($order_user, $order_item, $order_amount, $order_id, $config)
    {
        $client_key = $config['paggue_client_key'];
        $client_secret = $config['paggue_client_secret'];

        $curl = curl_init();
        $data = ['payer_name' => $order_user, 'amount' => $order_amount, 'external_id' => $order_id, 'description' => $order_item];

        $signature = hash_hmac('sha256', json_encode($data), $client_secret);
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . drope_paggue_get_info('access_token', $config),
            'X-Company-ID: ' . drope_paggue_get_info('company_id', $config),
            'Signature: ' . $signature
        ];
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://ms.paggue.io/cashin/api/billing_order',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);  // Get HTTP status code
        curl_close($curl);

        $get = json_decode($response, true);
        $data = [];
        if (isset($get['payment']) && isset($get['hash'])) {
            $data = ['pix' => $get['payment'], 'hash' => $get['hash']];
        } else {
            $data = ['ERRO - PIX INDISPONÍVEL'];
        }
        return $data;
    }


// Função para obter token de acesso
function paggue_get_token($client_key, $client_secret, $config) {
    $curl = curl_init();
    $data = [
        'client_key' => $client_key,
        'client_secret' => $client_secret
    ];
    
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://ms.paggue.io/auth/v1/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($data)
    ]);
    
    $response = curl_exec($curl);
    $result = json_decode($response, true);
    curl_close($curl);
    
    return $result;
}

// Função para criar ordem de pagamento
function paggue_create_order($order_data) {
    // Dados necessários
    $data = [
        'payer_name' => $order_data['payer_name'],
        'amount' => $order_data['amount'],
        'external_id' => $order_data['external_id'],
        'description' => $order_data['description']
    ];
    
    // Gerar assinatura
    $signature = hash_hmac('sha256', json_encode($data), $order_data['client_secret']);
    
    // Headers da requisição
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $order_data['access_token'],
        'X-Company-ID: ' . $order_data['company_id'],
        'Signature: ' . $signature
    ];
    
    // Fazer requisição
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://ms.paggue.io/cashin/api/billing_order',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers
    ]);
    
    $response = curl_exec($curl);
    $result = json_decode($response, true);
    curl_close($curl);
    
    return $result;
}

// Função para verificar status do pagamento
function paggue_check_payment($id_pedido, $access_token, $company_id) {
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token,
        'X-Company-ID: ' . $company_id
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://ms.paggue.io/cashin/api/billing_order',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers
    ]);
    
    $response = curl_exec($curl);
    $result = json_decode($response, true);
    curl_close($curl);
    
    return $result;
}

function drope_normalize_price($price)
{
	$price = trim(preg_replace('`(R|\\$|\\x20)`i', '', $price));

	if (preg_match('`^([0-9]+(?:\\.[0-9]+)+)\\,([0-9]+)$`', $price, $match)) {
		return str_replace('.', '', $match[1]) . '.' . $match[2];
	}

	if (preg_match('`^([0-9]+)\\,([0-9]+)$`', $price, $match)) {
		return $match[1] . '.' . $match[2];
	}

	if (preg_match('`^([0-9]+(?:\\,[0-9]+)+)\\.([0-9]+)$`', $price, $match)) {
		return str_replace(',', '', $match[1]) . '.' . $match[2];
	}

	if (preg_match('`^([0-9]+)\\.([0-9]+)$`', $price, $match)) {
		return $match[1] . '.' . $match[2];
	}

	if (preg_match('`^([0-9]+)$`', $price, $match)) {
		return $match[1];
	}

	$price = preg_replace('`(\\.|\\,)`', '', $price);

	if (preg_match('`^([0-9]+)$`', $price, $match)) {
		return $match[1];
	}

	return false;
}
function decode_brcode($brcode)
{
	$n = 0;

	while ($n < strlen($brcode)) {
		$codigo = substr($brcode, $n, 2);
		$n += 2;
		$tamanho = (int) substr($brcode, $n, 2);

		if (!is_numeric($tamanho)) {
			return false;
		}

		$n += 2;
		$valor = substr($brcode, $n, $tamanho);
		$n += $tamanho;
		if (preg_match('/^[0-9]{4}.+$/', $valor) && $codigo != 54) {
			$bug_fix = (isset($retorno[26]['01']) ? $retorno[26]['01'] : '');

			if (is_array($bug_fix)) {
				$extrai = strstr($brcode, 'PIX');
				$extrai = substr($extrai, 7);
				$extrai = substr($extrai, 0, 36);
				$retorno[26]['01'] = $extrai;
				unset($retorno[26][26]);
			}

			$retorno[$codigo] = decode_brcode($valor);
			continue;
		}

		$retorno[$codigo] = (string) $valor;
	}

	return $retorno;
}

function drope_paggue_get_info($info, $config)
{

    $client_key = $config['paggue_client_key'];
    $client_secret = $config['paggue_client_secret'];

    $access_token = '';
    $curl = curl_init();
    $data = ['client_key' => $client_key, 'client_secret' => $client_secret];
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://ms.paggue.io/auth/v1/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($data)
    ]);
    $response = curl_exec($curl);
    $get = json_decode($response, true);
    curl_close($curl);

    if ($info == 'access_token') {
        $info = $get['access_token'];
    }

    if ($info == 'company_id') {
        $info = $get['user']['companies'][0]['id'];
    }

    return $info;
}