<?php
require '../../functions/functions_campanhas.php';
require '../../conexao.php';

$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
if ($id)
{
    if ($id !== null) {
        $resultado = deletaCampanha($conn, $id);
        if ($resultado === true) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $resultado]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não suportado.']);
}
?>
