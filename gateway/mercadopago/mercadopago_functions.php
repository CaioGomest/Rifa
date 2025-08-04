<?php
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Configura o SDK do Mercado Pago com o token de acesso
 * @param string $access_token Token de acesso do Mercado Pago
 */
function configureMercadoPago($access_token) {
    MercadoPago\SDK::setAccessToken($access_token);
}

/**
 * Gera um pagamento PIX usando o Mercado Pago
 * @param float $amount Valor do pagamento
 * @param string $order_id ID do pedido
 * @param string $customer_name Nome do cliente
 * @param string $customer_email Email do cliente
 * @param int $expiration_minutes Tempo de expiração em minutos
 * @return array Array com os dados do pagamento PIX
 */
function generatePixPayment($amount, $order_id, $customer_name, $customer_email, $expiration_minutes = 30) {
    try {
        $payment = new MercadoPago\Payment();
        $payment->transaction_amount = (float)$amount;
        $payment->description = "Pedido #" . $order_id;
        $payment->payment_method_id = "pix";
        $payment->payer = array(
            "email" => $customer_email,
            "first_name" => $customer_name
        );

        // Configura a URL de notificação
        $payment->notification_url = BASE_URL . "/webhook.php?notify=mercadopago";
        $payment->external_reference = $order_id;

        // Define o tempo de expiração do PIX
        if ($expiration_minutes > 0) {
            $payment->date_of_expiration = date('Y-m-d\TH:i:s.vP', time() + ($expiration_minutes * 60));
        }

        // Adiciona um cabeçalho de idempotência para evitar duplicatas
        $payment->setCustomHeader('X-Idempotency-Key', uniqid());

        // Salva o pagamento
        if (!$payment->save()) {
            throw new Exception("Erro ao salvar o pagamento: " . implode(", ", $payment->error->causes));
        }

        // Verifica se o pagamento foi criado com sucesso
        if (!$payment->id) {
            throw new Exception("Erro: ID do pagamento não foi gerado");
        }

        // Verifica se os dados do PIX foram gerados
        if (!isset($payment->point_of_interaction) || 
            !isset($payment->point_of_interaction->transaction_data)) {
            throw new Exception("Erro: Dados do PIX não foram gerados corretamente");
        }

        $transaction_data = $payment->point_of_interaction->transaction_data;

        // Verifica se os códigos QR foram gerados
        if (!isset($transaction_data->qr_code) || !isset($transaction_data->qr_code_base64)) {
            throw new Exception("Erro: Códigos QR do PIX não foram gerados");
        }

        // Retorna os dados necessários
        return array(
            'success' => true,
            'payment_id' => $payment->id,
            'qr_code' => $transaction_data->qr_code,
            'qr_code_base64' => $transaction_data->qr_code_base64,
            'status' => $payment->status
        );

    } catch (Exception $e) {
        error_log("Erro Mercado Pago: " . $e->getMessage());
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
}

/**
 * Verifica o status de um pagamento
 * @param string $payment_id ID do pagamento no Mercado Pago
 * @return array Status do pagamento
 */
function checkPaymentStatus($payment_id) {
    try {
        $payment = MercadoPago\Payment::find_by_id($payment_id);
        return array(
            'success' => true,
            'status' => $payment->status,
            'status_detail' => $payment->status_detail,
            'external_reference' => $payment->external_reference
        );
    } catch (Exception $e) {
        return array(
            'success' => false,
            'error' => $e->getMessage()
        );
    }
} 