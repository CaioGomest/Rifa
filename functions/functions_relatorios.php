<?php

function getTotalCotas($conn, $data_inicio, $data_fim, $campanha_id = null, $status = null, $metodo = null) {
    $sql = "SELECT COALESCE(SUM(quantidade), 0) as total 
    FROM lista_pedidos 
    WHERE 1 = 1"; // Apenas pedidos pagos

    // Filtros opcionais
    if ($data_inicio !== null) {
    $sql .= " AND data_criacao >= '" . mysqli_real_escape_string($conn, $data_inicio) . "'";
    }

    if ($data_fim !== null) {
    $sql .= " AND data_criacao <= '" . mysqli_real_escape_string($conn, $data_fim) . "'";
    }

    if (is_numeric($campanha_id)) {
    $sql .= " AND campanha_id = " . mysqli_real_escape_string($conn, $campanha_id);
    }

    if (is_numeric($status)) {
        $sql .= " AND status = " . mysqli_real_escape_string($conn, $status);
    }

    if (is_numeric($metodo)) {
    $sql .= " AND metodo_pagamento = " . mysqli_real_escape_string($conn, $metodo);
    }

    $result = mysqli_query($conn, $sql);

    if ($result) 
    {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    else
            return "ERRO: " . mysqli_error($conn);
    
}

function getNovosClientes($conn, $data_inicio, $data_fim, $campanha = null, $status = null, $metodo = null) {
    $sql = "SELECT COUNT(DISTINCT cliente_id) as total FROM lista_pedidos";
    $params = [];
    $types = "";
    $where = [];

    if ($data_inicio && $data_fim) {
        $where[] = "DATE(data_criacao) BETWEEN ? AND ?";
        $params[] = $data_inicio;
        $params[] = $data_fim;
        $types .= "ss";
    }

    if ($campanha) {
        $where[] = "campanha_id = ?";
        $params[] = $campanha;
        $types .= "i";
    }

    if ($status) {
        $where[] = "status = ?";
        $params[] = $status;
        $types .= "i";
    }

    if ($metodo) {
        $where[] = "metodo_pagamento = ?";
        $params[] = $metodo;
        $types .= "s";
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    try {
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return intval($row['total']);
    } catch (Exception $e) {
        error_log("Erro ao buscar novos clientes: " . $e->getMessage());
        return 0;
    }
}
function getFaturamento($conn, $data_inicio = null, $data_fim = null, $campanha_id = null, $metodo = null) {
    $sql = "SELECT COALESCE(SUM(valor_total), 0) as total 
            FROM lista_pedidos 
            WHERE status = 1"; // Apenas pedidos pagos

    // Filtros opcionais
    if ($data_inicio !== null) {
        $sql .= " AND data_criacao >= '" . mysqli_real_escape_string($conn, $data_inicio) . "'";
    }

    if ($data_fim !== null) {
        $sql .= " AND data_criacao <= '" . mysqli_real_escape_string($conn, $data_fim) . "'";
    }

    if (is_numeric($campanha_id)) {
        $sql .= " AND campanha_id = " . mysqli_real_escape_string($conn, $campanha_id);
    }

    if (is_numeric($metodo)) {
        $sql .= " AND metodo_pagamento = " . mysqli_real_escape_string($conn, $metodo);
    }

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    } else {
        return "ERRO: " . mysqli_error($conn);
    }
}
