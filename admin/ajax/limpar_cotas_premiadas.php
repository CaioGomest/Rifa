<?php
require_once("../../conexao.php");
require_once("../../functions/functions_campanhas.php");

header('Content-Type: application/json');

if (!isset($_POST['campanha_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID da campanha não fornecido'
    ]);
    exit;
}

$campanha_id = intval($_POST['campanha_id']);

try {
    // Limpar todas as cotas premiadas da campanha
    $sql = "UPDATE campanhas SET cotas_premiadas = '', premio_cotas_premiadas = '' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $campanha_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Todas as cotas premiadas foram removidas com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao limpar cotas premiadas: ' . $stmt->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao limpar cotas premiadas: ' . $e->getMessage()
    ]);
}
?>
