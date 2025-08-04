<?php
require_once 'functions/functions_campanhas.php';
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    // Receber dados do POST
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Dados inválidos');
    }

    $campanha_id = $data['campanha_id'];
    $numeros_solicitados = $data['numeros_solicitados'];
    $cliente_id = $data['cliente_id'];
    $quantidade = $data['quantidade'];
    $codigo_afiliado = $data['codigo_afiliado'] ?? NULL;

    // Validar quantidade
    $sql = "SELECT compra_minima, compra_maxima FROM campanhas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $campanha_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $campanha = $result->fetch_assoc();

    if ($quantidade < $campanha['compra_minima'] || $quantidade > $campanha['compra_maxima']) {
        throw new Exception("Quantidade inválida. Mínimo: {$campanha['compra_minima']}, Máximo: {$campanha['compra_maxima']}");
    }

    // Validar números solicitados
    $validacao = validarNumerosSolicitados($conn, $campanha_id, $numeros_solicitados);
    
    if (!$validacao['valido']) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => $validacao['mensagem']
        ]);
        exit;
    }

    // Processar a compra
    $resultado = processarCompraNumeros($conn, $campanha_id, $numeros_solicitados, $cliente_id);
    
    echo json_encode($resultado);

} catch (Exception $e) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao processar a compra: ' . $e->getMessage()
    ]);
} 