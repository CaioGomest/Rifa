<?php
require_once '../conexao.php';
require_once 'functions_pedidos.php';
require_once 'functions_clientes.php';
require_once 'functions_sistema.php';

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Recebe os parâmetros
$data_inicio = isset($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
$data_final = isset($_POST['data_final']) ? $_POST['data_final'] : null;
$qtd_sortear = isset($_POST['qtd_sortear']) ? intval($_POST['qtd_sortear']) : 0;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$qtd_cotas = isset($_POST['qtd_cotas']) ? intval($_POST['qtd_cotas']) : 0;
$campanha_id = isset($_POST['campanha_id']) ? intval($_POST['campanha_id']) : 0;

// Validações
if (!$data_final || !$data_inicio || !$qtd_sortear || !$tipo || !$campanha_id) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

try {
    global $conn;
    
    // Usa a função existente para listar pedidos
    $pedidos = listaPedidos(
        $conn,         // conexão
        null,          // pedido_id
        null,          // cliente_id
        $campanha_id,  // campanha_id
        $data_inicio,  // data_inicio
        $data_final,          // data_fim
        1,            // status (1 = pago)
        null,          // metodo
        null           // numero_cota

    );

    // Filtra por quantidade mínima de cotas se especificado
    if ($qtd_cotas > 0) {
        $pedidos = array_filter($pedidos, function($pedido) use ($qtd_cotas) {
            return $pedido['quantidade'] >= $qtd_cotas;
        });
    }

    // Aplica a lógica de sorteio baseada no tipo
    switch ($tipo) {
        case 'soma_pedidos':
            // Agrupa por cliente e soma as quantidades
            $soma_por_cliente = [];
            foreach ($pedidos as $pedido) {
                $cliente_id = $pedido['cliente_id'];
                if (!isset($soma_por_cliente[$cliente_id])) {
                    $soma_por_cliente[$cliente_id] = $pedido;
                    $soma_por_cliente[$cliente_id]['quantidade_total'] = 0;
                }
                $soma_por_cliente[$cliente_id]['quantidade_total'] += $pedido['quantidade'];
            }
            // Ordena por quantidade total
            usort($soma_por_cliente, function($a, $b) {
                return $b['quantidade_total'] - $a['quantidade_total'];
            });
            $pedidos = array_slice($soma_por_cliente, 0, $qtd_sortear);
            break;

        case 'maior_cota':
            // Ordena os pedidos pela maior cota (número) dentro de cada pedido
            usort($pedidos, function($a, $b) {
                $numsA = array_map('intval', array_filter(explode(',', $a['numeros_pedido'])));
                $numsB = array_map('intval', array_filter(explode(',', $b['numeros_pedido'])));
                $maxA = !empty($numsA) ? max($numsA) : 0;
                $maxB = !empty($numsB) ? max($numsB) : 0;
                return $maxB - $maxA;
            });
            $pedidos = array_slice($pedidos, 0, $qtd_sortear);
            break;

        case 'menor_cota':
            // Ordena os pedidos pela menor cota (número) dentro de cada pedido
            usort($pedidos, function($a, $b) {
                $numsA = array_map('intval', array_filter(explode(',', $a['numeros_pedido'])));
                $numsB = array_map('intval', array_filter(explode(',', $b['numeros_pedido'])));
                $minA = !empty($numsA) ? min($numsA) : PHP_INT_MAX;
                $minB = !empty($numsB) ? min($numsB) : PHP_INT_MAX;
                return $minA - $minB;
            });
            $pedidos = array_slice($pedidos, 0, $qtd_sortear);
            break;

        case 'qtd_pedidos':
            // Conta quantidade de pedidos por cliente
            $pedidos_por_cliente = [];
            foreach ($pedidos as $pedido) {
                $cliente_id = $pedido['cliente_id'];
                if (!isset($pedidos_por_cliente[$cliente_id])) {
                    $pedidos_por_cliente[$cliente_id] = $pedido;
                    $pedidos_por_cliente[$cliente_id]['total_pedidos'] = 0;
                }
                $pedidos_por_cliente[$cliente_id]['total_pedidos']++;
            }
            // Ordena por quantidade de pedidos
            usort($pedidos_por_cliente, function($a, $b) {
                return $b['total_pedidos'] - $a['total_pedidos'];
            });
            $pedidos = array_slice($pedidos_por_cliente, 0, $qtd_sortear);
            break;

        default: // por_pedido
            shuffle($pedidos);
            $pedidos = array_slice($pedidos, 0, $qtd_sortear);
    }

    if (empty($pedidos)) {
        echo json_encode(['success' => false, 'message' => 'Nenhum resultado encontrado com os filtros selecionados']);
        exit;
    }

    // Formata o resultado
    $ganhadores = [];
    foreach ($pedidos as $pedido) {
        $cota_premiada = null;
        if ($tipo === 'maior_cota') {
            $nums = array_map('intval', array_filter(explode(',', $pedido['numeros_pedido'])));
            $cota_premiada = !empty($nums) ? max($nums) : null;
        } elseif ($tipo === 'menor_cota') {
            $nums = array_map('intval', array_filter(explode(',', $pedido['numeros_pedido'])));
            $cota_premiada = !empty($nums) ? min($nums) : null;
        }
        $ganhadores[] = [
            'nome' => $pedido['cliente_nome'],
            'telefone' => $pedido['cliente_telefone'],
            'cotas' => $pedido['quantidade'],
           'cota_premiada' => $cota_premiada !== null ? str_pad((string)$cota_premiada, 7, "0", STR_PAD_LEFT) : '',
            'data_compra' => date('d/m/Y', strtotime($pedido['data_criacao']))
        ];
    }

    // Registra o sorteio no histórico
    $dados_historico = [
        'data_sorteio' => date('Y-m-d H:i:s'),
        'tipo_sorteio' => $tipo,
        'qtd_sorteada' => $qtd_sortear,
        'filtro_data' => $data_inicio,
        'filtro_cotas' => $qtd_cotas,
        'campanha_id' => $campanha_id
    ];
    inserirHistoricoSorteio($conn, $dados_historico);

    echo json_encode(['success' => true, 'ganhadores' => $ganhadores]);

} catch (Exception $e) {
    error_log("Erro no sorteio: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao realizar o sorteio: ' . $e->getMessage()]);
    exit;
}

// Adicionar log de erro do MySQL
if (mysqli_error($conn)) {
    error_log("Erro MySQL: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Erro MySQL: ' . mysqli_error($conn)]);
    exit;
} 