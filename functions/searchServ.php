<?php
require_once("../conexao.php");

$sql = "SELECT servicos.*, usuarios.usuario_nome, servicos_status.servico_status_nome, servicos_status.servico_status_quantidade_desbloqueado FROM servicos 
INNER JOIN servicos_status ON servicos.servico_status = servicos_status.servico_status_id
INNER JOIN usuarios ON servicos.servico_autor_id = usuarios.usuario_id WHERE 1=1 ";

if (isset($_POST['filter'])) {
    $sql .= sprintf(" AND servico_status = %d ", mysqli_real_escape_string($conn, $_POST['filter']));
} // usei sua ideia pra aplicar os filtros ;)

$stmt = $conn->prepare($sql);


if ($stmt->execute()) {
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        foreach ($result as $row) {
            $res[] = array(
                'servico_id' => $row['servico_id'],
                'servico_autor' => $row['usuario_nome'],
                'servico_titulo' => $row['servico_titulo'],
                'servico_descricao' => $row['servico_descricao'],
                'servico_valor' => $row['servico_valor'],
                'servico_status' => $row['servico_status_nome'],
                'servico_data' => $row['servico_data']
            );
        }
        echo json_encode($res); // A RESPOSTA DA SQL TEM Q VOLTAR EM FORMATO JSON PRA SER LIDO
    } else {
        echo json_encode('0');
    }
}
