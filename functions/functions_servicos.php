<?php

if (session_status() == PHP_SESSION_NONE)
    session_start();

function criaServico($conn, $autor_id, $titulo, $descricao, $valor, $status, $orcamento_max = NULL, $tecnologias_usadas = NULL, $prazo_max = NULL, $contato = NULL)
{
    $query = "INSERT INTO servicos (servico_autor_id, servico_titulo, servico_descricao, servico_valor, servico_status, servico_orcamento_max, servico_tecnologias_usadas, servico_prazo_max) VALUES 
    ('" . mysqli_real_escape_string($conn, $autor_id) . "', 
     '" . mysqli_real_escape_string($conn, $titulo) . "', 
     '" . mysqli_real_escape_string($conn, $descricao) . "', 
     '" . mysqli_real_escape_string($conn, $valor) . "', 
     '" . mysqli_real_escape_string($conn, $status);

    // Adicionando os valores adicionais, se estiverem presentes
    if ($orcamento_max !== NULL) {
        $query .= "','" . mysqli_real_escape_string($conn, $orcamento_max);
    } else {
        $query .= ",0 ";
    }

    if ($tecnologias_usadas !== NULL) {
        $query .= "','" . mysqli_real_escape_string($conn, $tecnologias_usadas);
    } else {
        $query .= ",NULL ";
    }

    if ($prazo_max !== NULL) {
        $query .= "','" . mysqli_real_escape_string($conn, $prazo_max) . "'";
    } else {
        $query .= ",NULL ";
    }

    // Adicionando o valor para servico_contato
    // if ($contato !== NULL) {
    //     $query .= "'" . mysqli_real_escape_string($conn, $contato) . "'";
    // } else {
    //     $query .= "NULL";
    // }

    $query .= ")";
    //echo $query;
    $result = mysqli_query($conn, $query);

    if ($result !== FALSE) {
        return mysqli_insert_id($conn);
    } else {
        return "ERRO: " . mysqli_error($conn);
    }
}

function listaServicos($conn, $id = NULL, $servico_autor_id = NULL, $titulo = NULL, $tipo = NULL, $status = NULL, $order_by = NULL, $limite = NULL, $servico_ativo = null)
{
    $query = "SELECT servicos.*, usuarios.usuario_nome, servicos_status.servico_status_nome, servicos_status.servico_status_quantidade_desbloqueado FROM servicos 
              INNER JOIN servicos_status ON servicos.servico_status = servicos_status.servico_status_id
              INNER JOIN usuarios ON servicos.servico_autor_id = usuarios.usuario_id WHERE 1=1";

    // WHERE
    if (is_numeric($id))
        $query .= " AND servico_id = " . mysqli_real_escape_string($conn, $id);

    if (is_numeric($servico_autor_id))
        $query .= " AND servico_autor_id = " . mysqli_real_escape_string($conn, $servico_autor_id);

    if (!empty($titulo))
        $query .= " AND servico_nome LIKE '%" . mysqli_real_escape_string($conn, $titulo) . "%'";

    if (!empty($tipo))
        $query .= " AND servico_tipo LIKE '%" . mysqli_real_escape_string($conn, $tipo) . "%'"; // Corrigido para servico_tipo

    if (!empty($status))
        $query .= " AND servico_status_nome LIKE '%" . mysqli_real_escape_string($conn, $status) . "%'"; // Corrigido para servico_status_nome

    if (is_numeric($servico_ativo))
        $query .= " AND servico_ativo = " . mysqli_real_escape_string($conn, $servico_ativo);

    if (!empty($order_by))
        $query .= " ORDER BY servico_data $order_by";

    if (!empty($limite)) {
        $query .= " LIMIT " . mysqli_real_escape_string($conn, $limite);
    }
    //echo $query;

    $result = mysqli_query($conn, $query);

    if ($result !== FALSE) {
        return $result;
    } else {
        return "ERRO: " . mysqli_error($conn);
    }
}

function listaTiposServicos($conn)
{
    $query = "SELECT * FROM servico_tipo WHERE 1=1 ORDER BY servico_tipo_nome ASC";

    $result = mysqli_query($conn, $query);

    if ($result !== FALSE)
        return $result;
    else
        return "ERRO: " . mysqli_error($conn);
}

function totalServicos($conn, $titulo = NULL, $cliente_id = NULL, $tipo = NULL, $status = NULL)
{
    $query = "SELECT COUNT(1) FROM servico WHERE 1 = 1";

    if (!empty($titulo))
        $query .= " AND servico_nome LIKE '%" . mysqli_real_escape_string($conn, $titulo) . "%'";

    if (!empty($cliente_id) && is_numeric($cliente_id))
        $query .= sprintf(" AND servico_id = %d ", mysqli_real_escape_string($conn, $cliente_id));

    if (!empty($tipo) && is_numeric($tipo))
        $query .= sprintf(" AND servico_tipo = %d ", mysqli_real_escape_string($conn, $tipo));

    if (is_numeric($status))
        $query .= sprintf(" AND servico_bloqueado = %d ", mysqli_real_escape_string($conn, $status));

    $result = mysqli_query($conn, $query);

    if ($result !== FALSE) {
        $count = mysqli_fetch_all_mod($result, MYSQLI_BOTH);
        return $count[0][0];
    } else
        return "ERRO: " . mysqli_error($conn);
}
function alteraServicos($conn, $id, $titulo = null, $cancelar_servico = null)
{
    $query = "UPDATE servicos SET ";

    if ($titulo !== null) {
        $query .= "servico_nome = '" . mysqli_real_escape_string($conn, $titulo) . "'";
    }

    if (is_numeric($cancelar_servico)) {
        if ($titulo !== null) {
            $query .= ", ";
        }
        $query .= "servico_ativo = " . mysqli_real_escape_string($conn, $cancelar_servico);
    }

    $query .= " WHERE servico_id = " . mysqli_real_escape_string($conn, $id);

    //echo $query;
    if (mysqli_query($conn, $query)) {
        return true;
    } else {
        return "ERRO: " . mysqli_error($conn);
    }
}

function desbloquearServico($conn, $id_usuario, $id_servico, $pontos_necessarios, $pontos_usuario)
{
    $varfica_servico_desbloqueado = verificaServicoDesbloqueado($conn, $id_servico, $id_usuario);
    if ($varfica_servico_desbloqueado == 0) {
        if ($pontos_usuario >= $pontos_necessarios) {
            $pontos_restante_usuario = $pontos_usuario - $pontos_necessarios;

            $usuario_atualizado = atualizarUsuario($conn, $id_usuario, NULL, NULL, $pontos_restante_usuario, NULL);
            registraServicoDesbloqueado($conn, $id_servico, $id_usuario);
            return $usuario_atualizado;
        } else {
            return 'insuficiente';
        }
    }
}
function registraServicoDesbloqueado($conn, $id_servico, $id_usuario)
{
    $query = "INSERT INTO servicos_desbloqueados (servico_id, usuario_id) VALUES ($id_servico, $id_usuario)";

    //echo $query;
    $result = mysqli_query($conn, $query);

    if ($result !== FALSE) {

        return true;
    } else {
        return "ERRO: " . mysqli_error($conn);
    }
}
function verificaServicoDesbloqueado($conn, $id_servico, $id_usuario = null)
{
    // Constrói a consulta SQL básica
    $query = "SELECT COUNT(1) AS count FROM servicos_desbloqueados WHERE servico_id = " . mysqli_real_escape_string($conn, $id_servico);

    // Adiciona a condição para o ID do usuário, se fornecido
    if (is_numeric($id_usuario)) {
        $query .= " AND usuario_id = " . mysqli_real_escape_string($conn, $id_usuario);
    }
    // echo $query;
    // Executa a consulta SQL
    $result = mysqli_query($conn, $query);

    // Verifica se a consulta foi bem-sucedida
    if ($result !== FALSE) {
        // Obtém a linha do resultado
        $row = mysqli_fetch_assoc($result);
        // Retorna o valor da contagem
        return $row["count"];
    } else {
        // Retorna uma mensagem de erro caso ocorra algum problema com a consulta
        return "ERRO: " . mysqli_error($conn);
    }
}

