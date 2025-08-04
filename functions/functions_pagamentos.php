<?php

function registrarPagamento($conn, $afiliado_id, $valor_pago, $comprovante_path = null, $observacoes = '') {
    $query = "INSERT INTO pagamentos_afiliados (afiliado_id, valor_pago, data_pagamento, comprovante_path, observacoes) 
              VALUES (?, ?, NOW(), ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idss", $afiliado_id, $valor_pago, $comprovante_path, $observacoes);
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function listarPagamentos($conn, $afiliado_id = null) {
    $query = "SELECT p.*, u.usuario_nome, u.usuario_sobrenome 
              FROM pagamentos_afiliados p 
              JOIN usuarios u ON u.usuario_id = p.afiliado_id";
    
    if ($afiliado_id) {
        $query .= " WHERE p.afiliado_id = ?";
    }
    
    $query .= " ORDER BY p.data_pagamento DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($afiliado_id) {
        $stmt->bind_param("i", $afiliado_id);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function atualizarStatusPagamento($conn, $pagamento_id, $status, $observacoes = '') {
    $query = "UPDATE pagamentos_afiliados 
              SET status = ?, observacoes = ? 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $status, $observacoes, $pagamento_id);
    
    return $stmt->execute();
}

function getTotalPagamentos($conn, $afiliado_id) {
    $query = "SELECT 
                SUM(CASE WHEN status = 'confirmado' THEN valor_pago ELSE 0 END) as total_pago,
                SUM(CASE WHEN status = 'pendente' THEN valor_pago ELSE 0 END) as total_pendente
              FROM pagamentos_afiliados 
              WHERE afiliado_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $afiliado_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
} 