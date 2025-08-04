<?php

if (session_status() == PHP_SESSION_NONE)
{
    session_start();
}
function listaAfiliados($conn, $usuario_id = NULL, $status = NULL, $nome = NULL, $order_by = NULL, $limite = NULL, $campanha_id = NULL, $deletado = NULL)
{
    $query = "SELECT 
            u.*,
            ca.*,
            (SELECT COUNT(*) FROM lista_pedidos WHERE afiliado_id = u.usuario_id) as total_pedidos,
            COALESCE((SELECT SUM(valor_total) FROM lista_pedidos WHERE afiliado_id = u.usuario_id), 0) as total_vendas,
            COALESCE((SELECT SUM(valor_total * ca.porcentagem_comissao / 100) FROM lista_pedidos WHERE afiliado_id = u.usuario_id), 0) as total_comissoes,
            COALESCE((SELECT SUM(valor_total * ca.porcentagem_comissao / 100) FROM lista_pedidos WHERE afiliado_id = u.usuario_id AND status = 1), 0) as comissoes_pendentes,
            COALESCE((SELECT SUM(valor_total * ca.porcentagem_comissao / 100) FROM lista_pedidos WHERE afiliado_id = u.usuario_id AND status = 2), 0) as comissoes_pagas,
            COALESCE((SELECT SUM(valor_total * ca.porcentagem_comissao / 100) FROM lista_pedidos WHERE afiliado_id = u.usuario_id AND status = 3), 0) as comissoes_canceladas,
            (SELECT COUNT(*) FROM lista_pedidos WHERE afiliado_id = u.usuario_id AND status = 1) as total_pendentes,
            (SELECT COUNT(*) FROM lista_pedidos WHERE afiliado_id = u.usuario_id AND status = 2) as total_pagas,
            (SELECT COUNT(*) FROM lista_pedidos WHERE afiliado_id = u.usuario_id AND status = 3) as total_canceladas,
            COALESCE((SELECT SUM(valor_pago) FROM pagamentos_afiliados WHERE afiliado_id = u.usuario_id AND status = 'confirmado'), 0) as total_pago
          FROM usuarios u
          LEFT JOIN configuracoes_afiliados ca ON ca.usuario_id = u.usuario_id
          WHERE u.usuario_tipo = 2";

    if ($usuario_id !== NULL) {
        $query .= " AND u.usuario_id = " . mysqli_real_escape_string($conn, $usuario_id);
    }
    if ($status !== NULL) {
        $query .= " AND u.status = '" . mysqli_real_escape_string($conn, $status) . "'";
    }
    if ($nome !== NULL) {
        $query .= " AND u.nome LIKE '%" . mysqli_real_escape_string($conn, $nome) . "%'";
    }
    if ($campanha_id !== NULL) {
        $query .= " AND ca.campanha_id = " . mysqli_real_escape_string($conn, $campanha_id);
    }
    if ($deletado !== NULL) {
        $query .= " AND u.usuario_deletado = " . mysqli_real_escape_string($conn, $deletado);
    }
    if ($order_by !== NULL) {
        $query .= " ORDER BY " . mysqli_real_escape_string($conn, $order_by);
    }

    if ($limite !== NULL) {
        $query .= " LIMIT " . mysqli_real_escape_string($conn, $limite);
    }
      $result = mysqli_query($conn, $query);

      if ($result) {
          $afiliados = [];
          while ($row = mysqli_fetch_assoc($result)) {
              $afiliados[] = $row;
          }
          return $afiliados;
      } else {
          return "ERRO: " . mysqli_error($conn);
      }
}

function listaPagamentos($conn, $usuario_id)
{
    
    $sql_pagamentos = "SELECT * FROM pagamentos_afiliados WHERE afiliado_id = ? ORDER BY data_pagamento DESC";
    $stmt = $conn->prepare($sql_pagamentos);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result_pagamentos = $stmt->get_result();
    $pagamentos = $result_pagamentos->fetch_all(MYSQLI_ASSOC);
    return $pagamentos;
}

function listaComissao($conn, $usuario_id)
{
    $query_comissoes = "SELECT 
                        p.*,
                        p.token_pedido,
                        p.data_criacao as data_pedido,
                        cl.nome as cliente_nome,
                        camp.nome as campanha_nome,
                        p.valor_total as valor_venda,
                        ca.porcentagem_comissao as porcentagem,
                        (p.valor_total * ca.porcentagem_comissao / 100) as valor_comissao,
                        CASE 
                            WHEN p.status = 1 THEN 'Pendente'
                            WHEN p.status = 2 THEN 'Pago'
                            WHEN p.status = 3 THEN 'Cancelado'
                            ELSE 'Desconhecido'
                        END as status_texto,
                        p.status
                    FROM lista_pedidos p
                    LEFT JOIN clientes cl ON cl.id = p.cliente_id
                    LEFT JOIN campanhas camp ON camp.id = p.campanha_id
                    LEFT JOIN configuracoes_afiliados ca ON ca.usuario_id = p.afiliado_id
                    WHERE p.afiliado_id = ?
                    ORDER BY p.data_criacao DESC";

    $stmt = $conn->prepare($query_comissoes);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function criarCodigoAfiliado($conn, $afiliado_id, $campanha_id, $porcentagem = 0.00) 
{
    // Gerar código único baseado no ID do afiliado e campanha
    $codigo = 'AFF' . $afiliado_id . 'CAMP' . $campanha_id . substr(md5(uniqid()), 0, 6);
    
    // Verificar se o código já existe
    $check_query = "SELECT id FROM configuracoes_afiliados WHERE codigo_afiliado = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $codigo);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        // Se o código já existe, gerar um novo
        return criarCodigoAfiliado($conn, $afiliado_id, $campanha_id, $porcentagem);
    }
    
    // Inserir novo código
    $query = "INSERT INTO configuracoes_afiliados (usuario_id, campanha_id, codigo_afiliado, porcentagem) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisd", $afiliado_id, $campanha_id, $codigo, $porcentagem);
    
    if ($stmt->execute()) {
        return $codigo;
    }
    
    return false;
}