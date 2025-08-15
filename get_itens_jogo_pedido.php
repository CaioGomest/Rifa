<?php // arquivo renomeado logicamente para itens_jogo_pedido.php
require_once('conexao.php');
require_once('functions/functions_pedidos.php');

header('Content-Type: application/json');

$pedido_id = isset($_GET['pedido_id']) ? intval($_GET['pedido_id']) : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

if (!$pedido_id || !in_array($tipo, ['roleta', 'raspadinha'])) {
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit;
}

try {
    // Saldo SEMPRE dos contadores em 'jogos'
    $dados = obterJogosDoPedido($conn, $pedido_id);
    $registro = $dados['jogos'][$tipo] ?? null;
    $saldo = 0; $comprados = 0;
    if ($registro) {
        if ($tipo === 'roleta') {
            $saldo = intval($registro['giros_restantes'] ?? 0);
            $comprados = intval($registro['giros_comprados'] ?? 0);
        } else {
            $saldo = intval($registro['cartelas_restantes'] ?? 0);
            $comprados = intval($registro['cartelas_compradas'] ?? 0);
        }
    }

    // Itens SEMPRE da configuração atual da campanha
    $itens = [];
    $sql = "SELECT c.habilitar_roleta, c.habilitar_raspadinha, c.itens_roleta, c.itens_raspadinha
            FROM lista_pedidos p INNER JOIN campanhas c ON c.id = p.campanha_id WHERE p.id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if ($tipo === 'roleta' && $row['habilitar_roleta'] == '1') {
            $tmp = json_decode($row['itens_roleta'] ?? '[]', true);
            if (is_array($tmp)) { $itens = $tmp; }
        }
        if ($tipo === 'raspadinha' && $row['habilitar_raspadinha'] == '1') {
            $tmp = json_decode($row['itens_raspadinha'] ?? '[]', true);
            if (is_array($tmp)) { $itens = $tmp; }
        }
    }

// Sanitiza estrutura dos itens (nome/status)
    $itens = array_values(array_filter(array_map(function($i){
        return [
            'nome' => isset($i['nome']) ? (string)$i['nome'] : '',
            'status' => isset($i['status']) ? (string)$i['status'] : 'disponivel',
        ];
    }, is_array($itens) ? $itens : []), function($i){
        return $i['nome'] !== '';
    }));

    echo json_encode(['success' => true, 'itens' => $itens, 'saldo' => $saldo, 'comprados' => $comprados]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}


