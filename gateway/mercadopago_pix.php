<?php
require_once __DIR__ . '/mercadopago/mercadopago_functions.php';

// Define a URL base do sistema
if (!defined('BASE_URL')) {
    // Detecta se está usando HTTPS
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    
    // Obtém o host atual
    $host = $_SERVER['HTTP_HOST'];
    
    // Obtém o diretório base
    $base_path = dirname(dirname($_SERVER['PHP_SELF']));
    $base_path = $base_path === '/' ? '' : $base_path;
    
    // Define a constante BASE_URL
    define('BASE_URL', $protocol . $host . $base_path);
}

/**
 * Função principal para gerar pagamento PIX via Mercado Pago
 * @param int $order_id ID do pedido
 * @param float $amount Valor do pagamento
 * @param string $customer_name Nome do cliente
 * @param string $customer_email Email do cliente
 * @param int $expiration_minutes Tempo de expiração em minutos
 * @return bool Retorna true se o pagamento foi gerado com sucesso
 */
function mercadopago_generate_pix($conn,$order_id, $amount, $client_name, $client_email, $order_expiration, $config)
{
	require_once 'gateway/mercadopago/vendor/autoload.php';

	$access_token = $config['mercadopago_token_acesso'];
	$minutes_pix_expiration = $order_expiration;
	$amount = number_format((float) $amount, 2, '.', '');

	if (!$client_email) {
		$client_email = 'no-reply@dropestore.com';
	}

	MercadoPago\SDK::setAccessToken($access_token);
	$payment = new MercadoPago\Payment();
	$payment->transaction_amount = $amount;
	$payment->description = 'Pedido #' . $order_id;
	$payment->payment_method_id = 'pix';
	$payment->payer = ['email' => $client_email, 'first_name' => $client_name];
	$payment->notification_url = BASE_URL . '/webhook.php?notify=mercadopago';
	$payment->external_reference = $order_id;


	if ($minutes_pix_expiration) {
		$payment->date_of_expiration = date_brazil('Y-m-d\\TH:i:s.vP', time() + ($minutes_pix_expiration * 60));
	}

	$payment->setCustomHeader('X-Idempotency-Key', uniqid());
	$payment->save();
	$pix_qrcode = $payment->point_of_interaction->transaction_data->qr_code_base64;
	$pix_code = $payment->point_of_interaction->transaction_data->qr_code;
	$id_mp = $payment->id;
	$payment_method = 'MercadoPago';
	$sql = "UPDATE lista_pedidos
        SET metodo_pagamento = '$payment_method',
            codigo_pix = '$pix_code',
            qrcode_pix = '$pix_qrcode',
            id_mp = '$id_mp',
            expiracao_pedido = '$order_expiration'
        WHERE id = $order_id";


	if ($conn->query($sql))
	{
	    	return [
			'success' => true,
			'order_id' => $order_id
		];

	    
	}
	else
	{
			return [
			'success' => false,
			'error' => $e->getMessage()
		];
	}
}
