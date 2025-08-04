<?php
	require_once('conexao.php');
	require_once('functions/functions_sistema.php');
	require_once('functions/functions_campanhas.php');
	require_once('functions/functions_pedidos.php');

	$config 		= listaInformacoes($conn);
	$enable_utmfy 	= $config['habilitar_utmfy'];
	$utmfy_token 	= $config['utmfy_token'];

	function writeLog($message)
	{
		$logFile = __DIR__ . '/VERIFICACOTASPREMIADAS.log';
		$currentTime = date('Y-m-d H:i:s');
		$logMessage = "[{$currentTime}] {$message}\n";
		file_put_contents($logFile, $logMessage, FILE_APPEND);
	}

	// writeLog("inicio do webhook.");


	if (isset($_GET['notify']) == 'mercadopago')
	{    
		$json_event = file_get_contents('php://input', true);
		$event = json_decode($json_event);
		
		if (!$event || !validateWebhook($config['mercadopago_token_acesso'], $json_event)) {
			exit;
		}

		$mercadopago_access_token = $config['mercadopago_token_acesso'];

		$facebook_access_token = $config['facebook_access_token'];
		$facebook_pixel_id = $config['facebook_pixel_id'];
		if (isset($event->type) == 'payment') {
			$url = 'https://api.mercadopago.com/v1/payments/' . $event->data->id;
			$headers = array(
				'Accept: application/json',
				'Authorization: Bearer ' . $mercadopago_access_token
			);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$resposta = curl_exec($ch);
			curl_close($ch);
			$payment_info = json_decode($resposta, true);        

			$payment_id = $payment_info['id'];
			$status = $payment_info['status'];
			$payment_type = $payment_info['payment_type_id'];
			$pedido_id = $payment_info['external_reference']; 
			
			$pedido_encontrado[0] = listaPedidos($conn, $pedido_id);
			if ($pedido_encontrado[0] != null)
			{
				$id_pedido = $pedido_encontrado[0][0]['id'];
				$cliente_id = $pedido_encontrado[0][0]['cliente_id'];
				$status_order = $pedido_encontrado[0][0]['status'];
				$campanha_id = $pedido_encontrado[0][0]['campanha_id'];
				$quantidade = $pedido_encontrado[0][0]['quantidade'];
				$numeros_pedidos = $pedido_encontrado[0][0]['numeros_pedido'];
				$valor_total = $pedido_encontrado[0][0]['valor_total'];
				$firstname = $pedido_encontrado[0][0]['nome'];
				$phone = $pedido_encontrado[0][0]['telefone'];
				$email = $pedido_encontrado[0][0]['email'];
				$vencedor_sorteio = $pedido_encontrado[0][0]['vencedor_sorteio'];
				$price = $pedido_encontrado[0][0]['valor_total'];
			}

			if ($status == 'approved')
			{    
				
					//writeLog("pagamento aprovado.");
				if ($status_order == '0') 
				{
					// writeLog("ver numeros que estao sendo pegos do lista_pedidos: " . json_encode($numeros_pedidos));
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
					# Define o pedido como pago
					date_default_timezone_set('America/Sao_Paulo');
					$payment_date = date('Y-m-d H:i:s');
					atualizaStatusPedido($conn, $pedido_id, $payment_date, $payment_type, $payment_id);
					$tem_cota_premiada = verificaSeTemCotaPremiada($conn, $campanha_id, $numeros_pedidos);
					
					if(!empty($tem_cota_premiada))
						editaVencedorCampanha($conn, $id_pedido, $tem_cota_premiada);
					
					if($tem_cota_premiada)
						atualizaVencedorCampanha($conn, $campanha_id, $payment_date);

					$lastname = '';
					# PIXEL AUTOMÁTICO
					if (!empty($facebook_pixel_id) && !empty($facebook_access_token)) 
						enviaEventoFacebook($conn, $facebook_pixel_id, $facebook_access_token, $pedido_id, $firstname, $lastname, $phone, $email, $valor_total);

					if($enable_utmfy)
					{
						enviaEventoUTMFY( $pedido_id, $payment_date, $firstname, $lastname, $phone, $email, $valor_total, $campanha_id, $quantidade, $utmfy_token);
					}
				}
			}
		}
	}










	if (isset($_GET['notify']) == 'paggue') {
		$paggue_notify = file_get_contents('php://input', true);
		$paggue_get = json_decode($paggue_notify, true);
		
		$enable_pixel = $config['enable_pixel'];
		$facebook_access_token = $config['facebook_access_token'];
		$facebook_pixel_id = $config['facebook_pixel_id'];
		
		$payment_id = $paggue_get['id'];
		$status = $paggue_get['status'];		
		$pedido_id = $paggue_get['external_id'];


		$pedido_encontrado[0] = listaPedidos($conn, $pedido_id);
		if ($pedido_encontrado[0] != null)
		{
			$id_pedido = $pedido_encontrado[0][0]['id'];
			$cliente_id = $pedido_encontrado[0][0]['cliente_id'];
			$status_order = $pedido_encontrado[0][0]['status'];
			$campanha_id = $pedido_encontrado[0][0]['campanha_id'];
			$quantidade = $pedido_encontrado[0][0]['quantidade'];
			$numeros_pedidos = $pedido_encontrado[0][0]['numeros_pedido'];
			$valor_total = $pedido_encontrado[0][0]['valor_total'];
			$firstname = $pedido_encontrado[0][0]['nome'];
			$phone = $pedido_encontrado[0][0]['telefone'];
			$email = $pedido_encontrado[0][0]['email'];
			$vencedor_sorteio = $pedido_encontrado[0][0]['vencedor_sorteio'];
			$price = $pedido_encontrado[0][0]['valor_total'];
		}


		if($status == '1')
		{	
			if($status_order == '0')
			{
				# Define o pedido como pago
				date_default_timezone_set('America/Sao_Paulo');
				$payment_date = date('Y-m-d H:i:s');
				atualizaStatusPedido($conn, $pedido_id, $payment_date, $payment_type, $payment_id);
				$tem_cota_premiada = verificaSeTemCotaPremiada($conn, $campanha_id, $numeros_pedidos);
				
				if(!empty($tem_cota_premiada))
					editaVencedorCampanha($conn, $id_pedido, $tem_cota_premiada);
				
				if($tem_cota_premiada)
					atualizaVencedorCampanha($conn, $campanha_id, $payment_date);

				# PIXEL AUTOMÁTICO
				if (!empty($facebook_pixel_id) && !empty($facebook_access_token)) 
					enviaEventoFacebook($conn, $facebook_pixel_id, $facebook_access_token, $pedido_id, $firstname, $lastname, $phone, $email, $valor_total);

				if($enable_utmfy)
				{
					enviaEventoUTMFY( $pedido_id, $payment_date, $firstname, $lastname, $phone, $email, $valor_total, $campanha_id, $quantidade, $utmfy_token);
				}

			}
		}

	}


	function validateWebhook($token, $input) {
		if (empty($token) || empty($input)) {
			return false;
		}
		return true;
	}

	// Função para sanitizar dados
	function sanitizeInput($data) {
		if (is_array($data)) {
			return array_map('sanitizeInput', $data);
		}
		return htmlspecialchars(strip_tags($data));
	}

	// Função para log de erros
	function logError($message, $data = null) {
		$logFile = __DIR__ . '/webhook_errors.log';
		$currentTime = date('Y-m-d H:i:s');
		$logMessage = "[{$currentTime}] ERROR: {$message}";
		if ($data) {
			$logMessage .= "\nData: " . json_encode($data);
		}
		$logMessage .= "\n";
		file_put_contents($logFile, $logMessage, FILE_APPEND);
	}

	function verificaSeTemCotaPremiada($conn, $campanha_id, $numeros_vendidos)
	{
		// Pega os dados da campanha
		$campanhas = listaCampanhas($conn, $campanha_id);

		// Se não houver campanhas ou cotas premiadas, retorna JSON vazio
		if (empty($campanhas[0]['cotas_premiadas'])) {
			return json_encode([]);
		}
	
		$cotas_premiadas = $campanhas[0]['cotas_premiadas'];

		// Transforma strings em arrays
		$cotas_array = array_map('trim', explode(',', $cotas_premiadas));
		$vendidos_array = array_map('trim', explode(',', $numeros_vendidos));
	
		// Verifica quais números vendidos são premiados
		$cotas_encontradas = array_intersect($vendidos_array, $cotas_array);
	
		// Retorna as cotas premiadas encontradas em formato JSON
		return json_encode(array_values($cotas_encontradas));
	}

	function atualizaVencedorCampanha($conn, $campanha_id, $payment_date)
	{
		$sql_ol = "UPDATE campanhas SET status = '2', data_atualizacao = '{$payment_date}' WHERE id = '{$campanha_id}'";
		$conn->query($sql_ol);
	}
	function atualizaStatusPedido($conn, $pedido_id, $payment_date, $payment_type, $payment_id)
	{
		$sql_ol = "UPDATE lista_pedidos SET status = '1', data_atualizacao = '{$payment_date}', metodo_pagamento = '{$payment_type}', id_mp = '{$payment_id}' WHERE id = '{$pedido_id}'";
		$conn->query($sql_ol);
	}

	function enviaEventoFacebook($conn, $facebook_pixel_id, $facebook_access_token, $pedido_id, $firstname, $lastname, $phone, $email, $valor_total)
	{
		$url = "https://graph.facebook.com/v14.0/{$facebook_pixel_id}/events?access_token={$facebook_access_token}";
		$fn = hash('sha256', $firstname);
		$ln = hash('sha256', $lastname);
		$ph = hash('sha256', $phone);
		$data = [
			[
				'event_name' => 'Purchase',                     
				'event_time' => time(),
				'user_data' => [
					'fn' => $fn,
					'ln' => $ln,
					'ph' => $ph,
					'external_id' => hash('sha256', $pedido_id),
				],
				'custom_data' => [
					'currency' => 'BRL',
					'value' => (float) number_format($valor_total, 2, '.', ''),                            
				],
			]
		];
		$options = [
			CURLOPT_URL => $url,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode([
				'data' => $data,
			]),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				'Content-Type: application/json',
			],
		];

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		curl_close($curl);

		if ($response) {
			$result = json_decode($response, true);
			echo "Evento enviado com sucesso. ID do evento: {$result['fbtrace_id']}";
		} else {
			echo "Ocorreu um erro ao enviar o evento: " . curl_error($curl);
		} 
}   

function enviaEventoUTMFY( $pedido_id, $payment_date, $firstname, $lastname, $phone, $email, $valor_total, $campanha_id, $quantidade, $utmfy_token)
{
	$utmfy_url = 'https://api.utmify.com.br/api-credentials/orders';
	$utmfy_data = [
		"orderId" => $pedido_id,
		"platform" => "web",
		"paymentMethod" => "pix",
		"status" => "paid",
		"createdAt" => $payment_date,
		"approvedDate" => $payment_date,
		"refundedAt" => null,
		"customer" => [
			"name" => $firstname . ' ' . $lastname,
			"email" => $email,
			"phone" => "+{$phone}",
			"document" => ""
		],
		"products" => [
			[
				"id" => $campanha_id,
				"name" => $campanha_id,
				"quantity" => $quantidade,
				"priceInCents" => (int) ($valor_total * 100),
				"planId" => "",
				"planName" => ""
			]
		],
		"trackingParameters" => [
			"utm_source" => "",
			"utm_medium" => "",
			"utm_campaign" => "",
			"utm_content" => null,
			"utm_term" => null
		],
		"commission" => [
			"totalPriceInCents" => (int) ($valor_total * 100),
			"gatewayFeeInCents" => (int) ($valor_total * 100),  # Ajustar se necessário
			"userCommissionInCents" => (int) ($valor_total * 100)  # Ajustar se necessário
		],
		"isTest" => false
	];
	
	$utmfy_options = [
		CURLOPT_URL => $utmfy_url,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode($utmfy_data),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => [
			'Content-Type: application/json',
			"x-api-token: $utmfy_token"
		],
	];

	$utmfy_curl = curl_init();
	curl_setopt_array($utmfy_curl, $utmfy_options);
	$utmfy_response = curl_exec($utmfy_curl);
	curl_close($utmfy_curl);

	if ($utmfy_response)
	{
		$utmfy_result = json_decode($utmfy_response, true);
		echo "Dados enviados para a UTMfy com sucesso.";
	} else 
	{
		echo "Erro ao enviar dados para a UTMfy.";
	}
}