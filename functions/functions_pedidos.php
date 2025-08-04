<?php
function listaPedidos($conn, $pedido_id = null, $cliente_id = null, $campanha_id = null, $data_inicio = null, $data_fim = null, $status = null, $metodo = null, $numero_cota = null, $limite = null, $pular = null, $nome_cliente = null, $afiliado_id = null) {
    // Início da query

    $sql = "SELECT lista_pedidos.*, 
                   cliente.nome AS cliente_nome, cliente.email AS cliente_email, cliente.telefone AS cliente_telefone, 
                   campanha.nome AS campanha_nome, campanha.descricao AS campanha_descricao
            FROM lista_pedidos 
            LEFT JOIN clientes cliente ON lista_pedidos.cliente_id = cliente.id 
            LEFT JOIN campanhas campanha ON lista_pedidos.campanha_id = campanha.id 
            WHERE 1=1";

    // Aplicação dos filtros
    if (!empty($pedido_id)) {
        $sql .= " AND lista_pedidos.id = " . mysqli_real_escape_string($conn, $pedido_id);
    }
    if (!empty($cliente_id)) {
        $sql .= " AND lista_pedidos.cliente_id = " . mysqli_real_escape_string($conn, $cliente_id);
    }
    if (!empty($campanha_id) && is_numeric($campanha_id)) {
        $sql .= " AND lista_pedidos.campanha_id = " . mysqli_real_escape_string($conn, $campanha_id);
    }
    if (!empty($numero_cota) && is_numeric($numero_cota)) {
        $sql .= " AND lista_pedidos.numeros_pedido LIKE '%" . mysqli_real_escape_string($conn, $numero_cota) . "%'";
    }
    if (!empty($data_inicio)) {
        $sql .= " AND lista_pedidos.data_criacao >= '" . mysqli_real_escape_string($conn, $data_inicio) . " 00:00:00'";
    }
    if (!empty($data_fim)) {
        $sql .= " AND lista_pedidos.data_criacao <= '" . mysqli_real_escape_string($conn, $data_fim) . " 23:59:59'";
    }
    if (!empty($status) && is_numeric($status)) {
        $sql .= " AND lista_pedidos.status = " . mysqli_real_escape_string($conn, $status);
    }
    if (!empty($metodo)) {
        $metodo = trim($metodo);
        $sql .= " AND lista_pedidos.metodo_pagamento = '" . mysqli_real_escape_string($conn, $metodo) . "'";
    }
    

    if (!empty($nome_cliente)) {
        $sql .= " AND LOWER(cliente.nome) LIKE '%" . strtolower(mysqli_real_escape_string($conn, $nome_cliente)) . "%'";

    }

    if (!empty($afiliado_id)) {
        $sql .= " AND lista_pedidos.afiliado_id = " . mysqli_real_escape_string($conn, $afiliado_id);

    }
    $sql .= " ORDER BY lista_pedidos.data_criacao DESC";
    if ($limite !== null && $pular !== null ) {
        $sql .= " LIMIT " . intval($limite) . " OFFSET " . intval($pular);
    }
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $pedidos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pedidos[] = $row;
        }
        return $pedidos;
    } else {
        return [];
    }
}

function listaVendasAfiliados($conn, $pedido_id = null, $cliente_id = null, $campanha_id = null, $data_inicio = null, $data_fim = null, $status = null, $metodo = null, $numero_cota = null, $limite = null, $pular = null, $nome_cliente = null, $afiliado_id = null) {
    // Início da query

    $sql = "SELECT SUM(valor_total) AS valor_total
            FROM lista_pedidos 
            LEFT JOIN clientes cliente ON lista_pedidos.cliente_id = cliente.id 
            LEFT JOIN campanhas campanha ON lista_pedidos.campanha_id = campanha.id 
            WHERE 1=1";

    // Aplicação dos filtros
    if (!empty($pedido_id)) {
        $sql .= " AND lista_pedidos.id = " . mysqli_real_escape_string($conn, $pedido_id);
    }
    if (!empty($cliente_id)) {
        $sql .= " AND lista_pedidos.cliente_id = " . mysqli_real_escape_string($conn, $cliente_id);
    }
    if (!empty($campanha_id) && is_numeric($campanha_id)) {
        $sql .= " AND lista_pedidos.campanha_id = " . mysqli_real_escape_string($conn, $campanha_id);
    }
    if (!empty($numero_cota) && is_numeric($numero_cota)) {
        $sql .= " AND lista_pedidos.numeros_pedido LIKE '%" . mysqli_real_escape_string($conn, $numero_cota) . "%'";
    }
    if (!empty($data_inicio)) {
        $sql .= " AND lista_pedidos.data_criacao >= '" . mysqli_real_escape_string($conn, $data_inicio) . " 00:00:00'";
    }
    if (!empty($data_fim)) {
        $sql .= " AND lista_pedidos.data_criacao <= '" . mysqli_real_escape_string($conn, $data_fim) . " 23:59:59'";
    }
    if (!empty($status) && is_numeric($status)) {
        $sql .= " AND lista_pedidos.status = " . mysqli_real_escape_string($conn, $status);
    }
    if (!empty($metodo) && is_numeric($metodo)) {
        $sql .= " AND lista_pedidos.metodo_pagamento = " . mysqli_real_escape_string($conn, $metodo);
    }

    if (!empty($nome_cliente)) {
        $sql .= " AND LOWER(cliente.nome) LIKE '%" . strtolower(mysqli_real_escape_string($conn, $nome_cliente)) . "%'";

    }

    if (!empty($afiliado_id)) {
        $sql .= " AND lista_pedidos.afiliado_id = " . mysqli_real_escape_string($conn, $afiliado_id);

    }
    $sql .= " ORDER BY lista_pedidos.data_criacao DESC";
    // echo $sql;
    if ($limite !== null && $pular !== null ) {
        $sql .= " LIMIT " . intval($limite) . " OFFSET " . intval($pular);
    }
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $pedidos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $pedidos[] = $row;
        }
        return $pedidos;
    } else {
        return [];
    }
}

function criarPedido($conn, $cliente_id, $campanha_id, $afiliado_id, $quantidade, $valor_total, $status, $data_criacao, $data_atualizacao, $nome_produto, $token_pedido, $numeros_pedido, $metodo_pagamento = null, $expiracao_pedido = null, $codigo_pix = null, $qrcode_pix = null, $id_mp = null, $valor_desconto = null)
{
    // Inserir pedido
    $query = "INSERT INTO lista_pedidos (
        cliente_id,
        campanha_id,
        afiliado_id,
        quantidade,
        valor_total,
        status,
        data_criacao,
        data_atualizacao,
        nome_produto,
        token_pedido,
        numeros_pedido,
        metodo_pagamento,
        expiracao_pedido,
        codigo_pix,
        qrcode_pix,
        id_mp,
        valor_desconto
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $status = 0; // pendente
    $stmt->bind_param(
        "iiisdssssssssssss",
        $cliente_id,
        $campanha_id,
        $afiliado_id,
        $quantidade,
        $valor_total,
        $status,
        $data_criacao,
        $data_atualizacao,
        $nome_produto,
        $token_pedido,
        $numeros_pedido,
        $metodo_pagamento,
        $expiracao_pedido,
        $codigo_pix,
        $qrcode_pix,
        $id_mp,
        $valor_desconto
    );

    if ($stmt->execute()) 
    {
        $pedido_id = $stmt->insert_id; // Obtém o ID do pedido recém-criado
        return $pedido_id;
    } else
        die("Erro ao cadastrar pedido: " . $stmt->error);

}

	function classeStatusPedido($pedido)
    {
        $status_classes = '';
        if($pedido['status'] == 0)
            $status_classes = 'bg-yellow-500';
        else if($pedido['status'] == 1)
            $status_classes = 'bg-green-500';
        else if($pedido['status'] == 2)
            $status_classes = 'bg-red-500';

        return $status_classes;
    }

    function textoStatusPedido($pedido)
    {
        $status_textos = '';
        if($pedido['status'] == 0)
            $status_textos = 'Pendente';
        else if($pedido['status'] == 1)
            $status_textos = 'Pago';
        else if($pedido['status'] == 2)
            $status_textos = 'Cancelado';

        return $status_textos;
    }

    function aprovarPedido($conn, $pedido_id) {
        $query = "UPDATE lista_pedidos SET status = 2, data_atualizacao = NOW() WHERE id = " . mysqli_real_escape_string($conn, $pedido_id);
        return mysqli_query($conn, $query);
    }

    function cancelarPedido($conn, $pedido_id) {
        $query = "UPDATE lista_pedidos SET status = 3, data_atualizacao = NOW() WHERE id = " . mysqli_real_escape_string($conn, $pedido_id);
        return mysqli_query($conn, $query);
    }

    function getPedido($conn, $pedido_id) {
        $query = "SELECT 
                    p.*,
                    c.nome as cliente_nome,
                    c.whatsapp as cliente_whatsapp,
                    c.email as cliente_email,
                    u.usuario_nome as afiliado_nome,
                    camp.nome as campanha_nome
                FROM lista_pedidos p 
                LEFT JOIN clientes c ON p.cliente_id = c.id
                LEFT JOIN usuarios u ON p.afiliado_id = u.usuario_id
                LEFT JOIN campanhas camp ON p.produto_id = camp.id
                WHERE p.id = " . mysqli_real_escape_string($conn, $pedido_id);
                
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $pedido = mysqli_fetch_assoc($result);
            
            // Formatar o status
            switch($pedido['status']) {
                case 1:
                    $pedido['status_texto'] = 'Pendente';
                    $pedido['status_classe'] = 'bg-yellow-500';
                    break;
                case 2:
                    $pedido['status_texto'] = 'Pago';
                    $pedido['status_classe'] = 'bg-green-500';
                    break;
                case 3:
                    $pedido['status_texto'] = 'Cancelado';
                    $pedido['status_classe'] = 'bg-red-500';
                    break;
                default:
                    $pedido['status_texto'] = 'Desconhecido';
                    $pedido['status_classe'] = 'bg-gray-500';
            }
            
            return $pedido;
        }
        
        return false;
    }

    function gerarQRCodePIX($conn, $pedido_id, $codigo_pix) {
        // Verifica se a biblioteca QR Code está disponível
        if (!file_exists('gateway/phpqrcode/qrlib.php')) {
            error_log("Biblioteca QR Code não encontrada em: gateway/phpqrcode/qrlib.php");
            return false;
        }

        require_once('gateway/phpqrcode/qrlib.php');
        
        // Cria um diretório temporário se não existir
        $temp_dir = 'temp/qrcodes';
        if (!file_exists($temp_dir)) {
            mkdir($temp_dir, 0777, true);
        }
        
        // Gera um nome único para o arquivo
        $filename = $temp_dir . '/qrcode_' . $pedido_id . '.png';
        
        try {
            // Gera o QR Code e salva no arquivo
            QRCode::png($codigo_pix, $filename, 'M', 5);
            
            // Valida se o arquivo QR Code foi realmente criado
            if (!file_exists($filename)) {
                error_log("Falha ao criar o arquivo QR Code: " . $filename);
                return false;
            }

            // Lê o arquivo gerado
            $qr_code = file_get_contents($filename);
            
            if ($qr_code === false) {
                error_log("Falha ao ler o arquivo QR Code gerado");
                return false;
            }
            
            // Converte para base64
            $qr_code_base64 = base64_encode($qr_code);
            
            // Atualiza o banco de dados
            $sql = "UPDATE lista_pedidos SET qrcode_pix = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $qr_code_base64, $pedido_id);
            
            if ($stmt->execute()) {
                // Remove o arquivo temporário
                unlink($filename);
                return $qr_code_base64;
            } else {
                error_log("Erro ao atualizar o banco de dados: " . $stmt->error);
                return false;
            }
        } catch (Exception $e) {
            error_log("Erro ao gerar QR Code: " . $e->getMessage());
            return false;
        }
    } 