<?php
require_once("../conexao.php");

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do pedido não informado']);
    exit;
}

$id = intval($_GET['id']);

// Atualizar status do pedido para pago
$sql = "UPDATE lista_pedidos SET status = 1, data_atualizacao = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao aprovar pedido']);
} 