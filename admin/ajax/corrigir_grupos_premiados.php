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
    // Obter dados da campanha
    $sql_campanha = "SELECT cotas_premiadas, premio_cotas_premiadas FROM campanhas WHERE id = ?";
    $stmt_campanha = $conn->prepare($sql_campanha);
    $stmt_campanha->bind_param("i", $campanha_id);
    $stmt_campanha->execute();
    $result_campanha = $stmt_campanha->get_result();
    $campanha = $result_campanha->fetch_assoc();
    
    if (!$campanha) {
        echo json_encode([
            'success' => false,
            'message' => 'Campanha não encontrada'
        ]);
        exit;
    }
    
    // Verificar se há cotas premiadas
    if (empty($campanha['cotas_premiadas'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Não há cotas premiadas para corrigir'
        ]);
        exit;
    }
    
    $cotas_existentes = array_map('trim', explode(',', $campanha['cotas_premiadas']));
    $grupos_corrigidos = [];
    
    // Tentar corrigir os grupos
    if (!empty($campanha['premio_cotas_premiadas'])) {
        $premios_json = json_decode($campanha['premio_cotas_premiadas'], true);
        
        if (is_array($premios_json)) {
            // Validar e corrigir cada grupo
            foreach ($premios_json as $grupo) {
                if (isset($grupo['cotas']) && isset($grupo['premio']) && is_array($grupo['cotas'])) {
                    // Filtrar apenas cotas que realmente existem
                    $cotas_validas = array_intersect($grupo['cotas'], $cotas_existentes);
                    if (!empty($cotas_validas)) {
                        $grupos_corrigidos[] = [
                            'cotas' => array_values($cotas_validas),
                            'premio' => $grupo['premio']
                        ];
                    }
                }
            }
        }
    }
    
    // Se não conseguiu corrigir, criar um grupo único
    if (empty($grupos_corrigidos)) {
        $grupos_corrigidos[] = [
            'cotas' => $cotas_existentes,
            'premio' => 'Prêmio não definido'
        ];
    }
    
    // Preparar dados para salvar
    $todas_cotas = [];
    foreach ($grupos_corrigidos as $grupo) {
        $todas_cotas = array_merge($todas_cotas, $grupo['cotas']);
    }
    
    // Remover duplicatas e ordenar
    $todas_cotas = array_unique($todas_cotas);
    sort($todas_cotas);
    
    // Salvar dados corrigidos
    $cotas_string = implode(',', $todas_cotas);
    $premios_string = json_encode($grupos_corrigidos);
    
    $sql = "UPDATE campanhas SET cotas_premiadas = ?, premio_cotas_premiadas = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $cotas_string, $premios_string, $campanha_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Grupos corrigidos com sucesso',
            'grupos_premios' => $grupos_corrigidos,
            'total_cotas' => count($todas_cotas)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao salvar grupos corrigidos: ' . $stmt->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao corrigir grupos: ' . $e->getMessage()
    ]);
}
?>
