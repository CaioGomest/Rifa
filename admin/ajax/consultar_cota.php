<?php
require_once("../../conexao.php");
require_once("../../functions/functions_pedidos.php");
require_once("../../functions/functions_clientes.php");

header('Content-Type: application/json');

if (!isset($_POST['campanha_id']) || !isset($_POST['numero_cota'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Parâmetros inválidos'
    ]);
    exit;
}

$campanha_id = $_POST['campanha_id'];
$numero_cota = $_POST['numero_cota'];
$resultado = listaPedidos($conn, null,null,$campanha_id, null, null, null, null, $numero_cota);

try {
    // Consulta para verificar se a cota está disponível
    if (!$resultado) {
       
        // Cota está disponível
        echo json_encode([
            'success' => true,
            'disponivel' => true
        ]);
    } else {
        // Cota já foi reservada
        $cliente = listaClientes($conn, $resultado[0]['cliente_id']);
        echo json_encode([
            'success' => true,
            'disponivel' => false,
            'comprador' => $cliente[0]['nome'],
            'data_compra' => date('d/m/Y H:i', strtotime($resultado[0]['data_criacao'])),
            'status' => textoStatusPedido($resultado[0])
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao consultar a cota: ' . $e->getMessage()
    ]);
}
