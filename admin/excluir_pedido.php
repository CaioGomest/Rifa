<?php
require_once("header.php");

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do pedido não informado']);
    exit;
}

$id = intval($_GET['id']);

// Excluir pedido
$sql = "DELETE FROM lista_pedidos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir pedido']);
} 