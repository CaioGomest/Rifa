<?php
require_once("../admin/conexao.php");
require_once("../../functions/functions_campanhas.php");

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$id = intval($_GET['id']);

// Buscar diretamente o preço da campanha
$sql = "SELECT preco FROM campanhas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$campanha = $result->fetch_assoc();

if (!$campanha) {
    echo json_encode(['error' => 'Campanha não encontrada']);
    exit;
}

echo json_encode([
    'preco' => $campanha['preco']
]);
?> 