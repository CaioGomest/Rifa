<?php
require_once("../functions/functions_clientes.php");
require_once("../conexao.php");

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = $_GET['id'];

if (excluirCliente($conn, $id)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir cliente']);
} 