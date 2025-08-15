<?php
require_once('conexao.php');
require_once('functions/functions_pedidos.php');

header('Content-Type: application/json');

// POST: pedido_id, tipo ('roleta'|'raspadinha'), token opcional para validar
$pedido_id = isset($_POST['pedido_id']) ? intval($_POST['pedido_id']) : 0;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$token = isset($_POST['token']) ? $_POST['token'] : '';

if (!$pedido_id || !in_array($tipo, ['roleta', 'raspadinha'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

// Opcional: valida token do pedido
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

// Garante que pedido existe e está pago
$res = mysqli_query($conn, "SELECT status FROM lista_pedidos WHERE id = " . intval($pedido_id) . " LIMIT 1");
if (!$res || mysqli_num_rows($res) === 0) {
    echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
    exit;
}
$pedidoRow = mysqli_fetch_assoc($res);
if (intval($pedidoRow['status']) !== 1) {
    echo json_encode(['success' => false, 'message' => 'Pedido não está pago']);
    exit;
}

$resultado = consumirJogoDoPedido($conn, $pedido_id, $tipo);
echo json_encode($resultado);


