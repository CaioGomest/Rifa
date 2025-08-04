<?php
require_once('../../conexao.php');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Verifica se o caminho da imagem foi fornecido
if (!isset($_POST['caminho']) || empty($_POST['caminho'])) {
    echo json_encode(['erro' => 'Caminho da imagem não fornecido']);
    exit;
}

$caminho = $_POST['caminho'];
$caminho_completo = '../../' . $caminho;

// Verifica se o arquivo existe
if (!file_exists($caminho_completo)) {
    echo json_encode(['erro' => 'Arquivo não encontrado']);
    exit;
}

// Tenta remover o arquivo
if (unlink($caminho_completo)) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['erro' => 'Não foi possível remover o arquivo']);
} 