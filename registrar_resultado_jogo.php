<?php
require_once('conexao.php');
require_once('functions/functions_pedidos.php');

header('Content-Type: application/json');

$pedido_id = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : 0;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$token = isset($_POST['token']) ? $_POST['token'] : '';
$resultadoJson = isset($_POST['resultado']) ? $_POST['resultado'] : '';

if (!$pedido_id || !in_array($tipo, ['roleta','raspadinha'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

// valida token/pedido pago (se fornecido)
if (!empty($token)) {
    $pedido = getPedidoPorToken($conn, $token);
    if (!$pedido || intval($pedido['id']) !== $pedido_id) {
        echo json_encode(['success' => false, 'message' => 'Token inválido']);
        exit;
    }
    if (intval($pedido['status']) !== 1) {
        echo json_encode(['success' => false, 'message' => 'Pedido não está pago']);
        exit;
    }
}

// decodifica resultado
$resultado = json_decode($resultadoJson, true);
if (!is_array($resultado)) {
    echo json_encode(['success' => false, 'message' => 'Resultado inválido']);
    exit;
}

$resp = registrarResultadoJogo($conn, $pedido_id, $tipo, $resultado);
echo json_encode($resp);


