<?php
require_once("../../conexao.php");
require_once("../../functions/functions_campanhas.php");

header('Content-Type: application/json');

if (!isset($_POST['campanha_id']) || !isset($_POST['quantidade']) || !isset($_POST['premio'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Parâmetros inválidos'
    ]);
    exit;
}

$campanha_id = intval($_POST['campanha_id']);
$quantidade = intval($_POST['quantidade']);
$premio = trim($_POST['premio']);

// Validar quantidade (removido limite máximo de 20)
if ($quantidade < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Quantidade deve ser maior que 0'
    ]);
    exit;
}

// Validar prêmio
if (empty($premio)) {
    echo json_encode([
        'success' => false,
        'message' => 'Prêmio é obrigatório'
    ]);
    exit;
}

try {
    // Obter números disponíveis usando a função existente
    $numeros_disponiveis = obterNumerosDisponiveis($conn, $campanha_id);
    
    if (empty($numeros_disponiveis)) {
        echo json_encode([
            'success' => false,
            'message' => 'Não há números disponíveis para esta campanha'
        ]);
        exit;
    }
    
    // Verificar se há números suficientes
    if (count($numeros_disponiveis) < $quantidade) {
        echo json_encode([
            'success' => false,
            'message' => 'Não há números suficientes disponíveis. Disponível: ' . count($numeros_disponiveis) . ', Solicitado: ' . $quantidade
        ]);
        exit;
    }
    
    // Obter cotas premiadas existentes
    $sql_campanha = "SELECT cotas_premiadas, premio_cotas_premiadas FROM campanhas WHERE id = ?";
    $stmt_campanha = $conn->prepare($sql_campanha);
    $stmt_campanha->bind_param("i", $campanha_id);
    $stmt_campanha->execute();
    $result_campanha = $stmt_campanha->get_result();
    $campanha = $result_campanha->fetch_assoc();
    
    // Estrutura para armazenar cotas com prêmios
    $cotas_com_premios = [];
    
    // Se já existem cotas, tentar decodificar a estrutura JSON
    if (!empty($campanha['cotas_premiadas'])) {
        $cotas_existentes = array_map('trim', explode(',', $campanha['cotas_premiadas']));
        
        // Verificar se premio_cotas_premiadas é um JSON válido (array de grupos)
        if (!empty($campanha['premio_cotas_premiadas'])) {
            $premios_existentes = json_decode($campanha['premio_cotas_premiadas'], true);
            
            if (is_array($premios_existentes)) {
                // É um array de grupos, validar cada grupo
                foreach ($premios_existentes as $grupo) {
                    if (isset($grupo['cotas']) && isset($grupo['premio']) && is_array($grupo['cotas'])) {
                        $cotas_com_premios[] = $grupo;
                    }
                }
                
                // Se não conseguiu extrair grupos válidos, criar um grupo único
                if (empty($cotas_com_premios)) {
                    $cotas_com_premios[] = [
                        'cotas' => $cotas_existentes,
                        'premio' => 'Prêmio não definido'
                    ];
                }
            } else {
                // É um prêmio único (formato antigo), criar estrutura
                $cotas_com_premios[] = [
                    'cotas' => $cotas_existentes,
                    'premio' => $campanha['premio_cotas_premiadas']
                ];
            }
        } else {
            // Se não há prêmio definido, usar cotas sem prêmio
            $cotas_com_premios[] = [
                'cotas' => $cotas_existentes,
                'premio' => 'Prêmio não definido'
            ];
        }
        
        // Remover cotas existentes dos números disponíveis
        $numeros_disponiveis = array_diff($numeros_disponiveis, $cotas_existentes);
    }
    
    // Verificar se ainda há números suficientes após remover os já premiados
    if (count($numeros_disponiveis) < $quantidade) {
        echo json_encode([
            'success' => false,
            'message' => 'Não há números suficientes disponíveis após considerar cotas já premiadas. Disponível: ' . count($numeros_disponiveis) . ', Solicitado: ' . $quantidade
        ]);
        exit;
    }
    
    // Selecionar números aleatoriamente
    $cotas_selecionadas = [];
    $numeros_temp = array_values($numeros_disponiveis);
    
    for ($i = 0; $i < $quantidade; $i++) {
        if (empty($numeros_temp)) break;
        
        $indice = array_rand($numeros_temp);
        $cotas_selecionadas[] = $numeros_temp[$indice];
        unset($numeros_temp[$indice]);
    }
    
    // Adicionar novo grupo de cotas com prêmio
    $cotas_com_premios[] = [
        'cotas' => $cotas_selecionadas,
        'premio' => $premio
    ];
    
    // Preparar dados para salvar
    $todas_cotas = [];
    $premios_json = [];
    
    foreach ($cotas_com_premios as $grupo) {
        // Validar estrutura do grupo
        if (!isset($grupo['cotas']) || !isset($grupo['premio'])) {
            continue; // Pular grupos inválidos
        }
        
        $todas_cotas = array_merge($todas_cotas, $grupo['cotas']);
        $premios_json[] = [
            'cotas' => $grupo['cotas'],
            'premio' => $grupo['premio']
        ];
    }
    
    // Ordenar as cotas para melhor visualização
    sort($todas_cotas);
    
    // Validar se temos dados válidos
    if (empty($premios_json)) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro: Nenhum grupo válido encontrado'
        ]);
        exit;
    }
    
    // Atualizar a campanha com todas as cotas premiadas e prêmios
    $cotas_string = implode(',', $todas_cotas);
    $premios_string = json_encode($premios_json);
    
    // Validar JSON antes de salvar
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao gerar JSON dos prêmios: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    $sql = "UPDATE campanhas SET cotas_premiadas = ?, premio_cotas_premiadas = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $cotas_string, $premios_string, $campanha_id);
    
    if ($stmt->execute()) {
        // Complemento: retornar também a largura para exibição padronizada no front
        // e as cotas formatadas com padding
        require_once('../../functions/functions_sistema.php');
        $largura = obterLarguraCotaPorCampanha($conn, $campanha_id);
        $todas_cotas_fmt = formatarArrayCotasComLargura($todas_cotas, $largura);
        $novas_cotas_fmt = formatarArrayCotasComLargura($cotas_selecionadas, $largura);
        $grupos_fmt = [];
        foreach ($premios_json as $grupo) {
            $grupos_fmt[] = [
                'cotas' => formatarArrayCotasComLargura($grupo['cotas'], $largura),
                'premio' => $grupo['premio'],
            ];
        }

        echo json_encode([
            'success' => true,
            'message' => 'Cotas premiadas geradas com sucesso',
            'cotas' => $todas_cotas,
            'novas_cotas' => $cotas_selecionadas,
            'total_cotas' => count($todas_cotas),
            'premio' => $premio,
            'grupos_premios' => $premios_json,
            // adicionais para exibição com padding
            'largura' => $largura,
            'cotas_formatadas' => $todas_cotas_fmt,
            'novas_cotas_formatadas' => $novas_cotas_fmt,
            'grupos_premios_formatados' => $grupos_fmt,
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao salvar cotas premiadas: ' . $stmt->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao gerar cotas premiadas: ' . $e->getMessage()
    ]);
}
?>
