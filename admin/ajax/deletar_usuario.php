<?php
require_once("../../conexao.php");
require_once("../../functions/functions_usuarios.php");

header('Content-Type: application/json');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não fornecido']);
    exit;
}

$id = intval($_POST['id']);

if (deletarUsuario($conn, $id)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao deletar usuário']);
} 