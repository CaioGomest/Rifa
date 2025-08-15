<?php
require_once('conexao.php');
require_once('functions/functions_campanhas.php');

header('Content-Type: application/json');

$campanha_id = isset($_GET['campanha_id']) ? intval($_GET['campanha_id']) : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

if (!$campanha_id || !in_array($tipo, ['roleta', 'raspadinha'])) {
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit;
}

try {
    $campanhas = listaCampanhas($conn, $campanha_id);
    
    if (empty($campanhas)) {
        echo json_encode(['error' => 'Campanha não encontrada']);
        exit;
    }
    
    $campanha = $campanhas[0];
    $itens = [];
    
    if ($tipo === 'roleta' && $campanha['habilitar_roleta'] == '1') {
        $itens_json = $campanha['itens_roleta'];
        if ($itens_json) {
            $itens = json_decode($itens_json, true);
        }
    } elseif ($tipo === 'raspadinha' && $campanha['habilitar_raspadinha'] == '1') {
        $itens_json = $campanha['itens_raspadinha'];
        if ($itens_json) {
            $itens = json_decode($itens_json, true);
        }
    }
    
    echo json_encode([
        'success' => true,
        'itens' => $itens ?: []
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
