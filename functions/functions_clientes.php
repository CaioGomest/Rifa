<?php

function listaClientes($conn, $id = null, $nome = null, $email = null, $telefone = null, $cpf = null, $data_inicio = null, $data_fim = null, $order_by = null, $limite = null, $pular = null)
{
    // Início da query
    $query = "SELECT * FROM clientes WHERE 1=1";

    // Filtros aplicados
    if ($id !== null) {
        $query .= " AND id = " . mysqli_real_escape_string($conn, $id);
    }
    if ($nome !== null) {
        $query .= " AND nome LIKE '%" . mysqli_real_escape_string($conn, $nome) . "%'";
    }
    if ($email !== null) {
        $query .= " AND email LIKE '%" . mysqli_real_escape_string($conn, $email) . "%'";
    }
    if ($telefone !== null) {
        $query .= " AND telefone LIKE '%" . mysqli_real_escape_string($conn, $telefone) . "%'";
    }
    if ($cpf !== null) {
        $query .= " AND cpf LIKE '%" . mysqli_real_escape_string($conn, $cpf) . "%'";
    }
    if ($data_inicio !== null) {
        $query .= " AND data_criacao >= '" . mysqli_real_escape_string($conn, $data_inicio) . "'";
    }
    if ($data_fim !== null) {
        $query .= " AND data_criacao <= '" . mysqli_real_escape_string($conn, $data_fim) . "'";
    }

    // Ordenação
    if ($order_by !== null) {
        $query .= " ORDER BY " . mysqli_real_escape_string($conn, $order_by);
    } else {
        $query .= " ORDER BY data_criacao DESC";
    }

    // Limitação
    if ($limite !== null && $pular !== null ) {
        $query .= " LIMIT " . intval($limite) . " OFFSET " . intval($pular);
    }
    
    // Executa a query
    $result = mysqli_query($conn, $query);

    // Verifica o resultado
    if ($result) {
        $clientes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $clientes[] = $row;
        }
        return $clientes;
    } else {
        return "ERRO: " . mysqli_error($conn);
    }
}

function listaClientesTopCompradores($conn, $campanha_id, $data_inicio, $data_fim, $limite = 5) {
    // Garantindo que datas abranjam o dia inteiro
    $data_inicio = date('Y-m-d 00:00:00', strtotime($data_inicio));
    $data_fim = date('Y-m-d 23:59:59', strtotime($data_fim));

    // Escapando os dados
    $campanha_id = (int) $campanha_id;
    $data_inicio = mysqli_real_escape_string($conn, $data_inicio);
    $data_fim = mysqli_real_escape_string($conn, $data_fim);
    $limite = (int) $limite;

    $sql = "SELECT 
                c.nome AS cliente_nome,
                SUM(CAST(lp.quantidade AS DECIMAL)) AS total_comprado,
                COUNT(lp.id) AS total_pedidos,
                SUM(lp.valor_total) AS valor_total
            FROM lista_pedidos lp
            JOIN clientes c ON c.id = lp.cliente_id
            WHERE lp.campanha_id = $campanha_id
              AND lp.data_criacao BETWEEN '$data_inicio' AND '$data_fim'
              AND lp.status IN (1)
            GROUP BY lp.cliente_id, c.nome
            ORDER BY total_comprado DESC
            LIMIT $limite";

    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}



function buscarClientes($conn, $filtros = []) {
    $sql = "SELECT * FROM clientes WHERE 1=1";
    $params = [];
    $types = "";

    // Aplicar filtros
    if (!empty($filtros['nome'])) {
        $sql .= " AND nome LIKE ?";
        $params[] = "%" . $filtros['nome'] . "%";
        $types .= "s";
    }

    if (!empty($filtros['telefone'])) {
        $sql .= " AND telefone LIKE ?";
        $params[] = "%" . $filtros['telefone'] . "%";
        $types .= "s";
    }

    if (!empty($filtros['cpf'])) {
        $sql .= " AND cpf LIKE ?";
        $params[] = "%" . $filtros['cpf'] . "%";
        $types .= "s";
    }

    if (!empty($filtros['email'])) {
        $sql .= " AND email LIKE ?";
        $params[] = "%" . $filtros['email'] . "%";
        $types .= "s";
    }

    $sql .= " ORDER BY nome ASC";

    try {
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $clientes = [];
        
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }
        
        return $clientes;
    } catch (Exception $e) {
        error_log("Erro ao listar clientes: " . $e->getMessage());
        return false;
    }
}

function getCliente($conn, $id) {
    $sql = "SELECT * FROM clientes WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return false;
}

function excluirCliente($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Erro ao excluir cliente: " . $e->getMessage());
        return false;
    }
}

function atualizarCliente($conn, $id, $dados) {
    try {
        $sql = "UPDATE clientes SET 
                nome = ?, 
                email = ?, 
                telefone = ?, 
                cpf = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", 
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $dados['cpf'],
            $id
        );
        return $stmt->execute();
    } catch (Exception $e) {
        error_log("Erro ao atualizar cliente: " . $e->getMessage());
        return false;
    }
}

function cadastrarCliente($conn, $dados) {
    // Validar campos obrigatórios
    if (empty($dados['nome'])) {
        error_log("Erro: Nome é obrigatório");
        return false;
    }

    try {
        $sql = "INSERT INTO clientes (nome, email, telefone, cpf) VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Erro na preparação da query: " . $conn->error);
            return false;
        }

        $cpf = isset($dados['cpf']) ? $dados['cpf'] : '';
        $stmt->bind_param("ssss",
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $cpf
        );

        $resultado = $stmt->execute();
        if (!$resultado) {
            error_log("Erro ao executar query: " . $stmt->error);
            return false;
        }

        return true;
    } catch (Exception $e) {
        error_log("Erro ao cadastrar cliente: " . $e->getMessage());
        return false;
    }
} 


