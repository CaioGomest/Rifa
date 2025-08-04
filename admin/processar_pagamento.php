<?php
require("header.php");
require("../functions/functions_afiliados.php");

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['usuario_tipo'] != 1) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $afiliado_id = intval($_POST['afiliado_id']);
    $valor_pago = floatval($_POST['valor_pago']);
    $data_pagamento = $_POST['data_pagamento'];
    $observacoes = $_POST['observacoes'] ?? '';
    $comprovante_path = null;

    // Processar upload do comprovante se existir
    if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/comprovantes/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['comprovante']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $target_path)) {
            $comprovante_path = '../uploads/comprovantes/' . $file_name;
        }
    }

    // Inserir pagamento no banco de dados
    $sql = "INSERT INTO pagamentos_afiliados (afiliado_id, valor_pago, data_pagamento, comprovante_path, status, observacoes) 
            VALUES (?, ?, ?, ?, 'confirmado', ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idsss", $afiliado_id, $valor_pago, $data_pagamento, $comprovante_path, $observacoes);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Pagamento registrado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "Erro ao registrar pagamento: " . $conn->error;
        $_SESSION['tipo_mensagem'] = "error";
    }

    header("Location: afiliado_relatorio.php?id=" . $afiliado_id);
    exit;
} else {
    header("Location: gerenciar_afiliados.php");
    exit;
} 