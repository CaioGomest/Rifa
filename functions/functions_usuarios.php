<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function deslogar()
{
    session_destroy();
    header("Location: index.php");
    exit;
}

function loginUsuario($conn, $email, $senha)
{
    $query = "SELECT * FROM usuarios WHERE usuario_email = ?";
    // echo "Query executada: $query\n"; // Exibindo a query para debug
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // echo "Senha fornecida: $senha\n";
            // echo "Hash no banco: " . $row['usuario_senha'] . "\n";

            if (password_verify($senha, $row['usuario_senha'])) {
                echo "Senha verificada com sucesso!\n";
                $_SESSION["usuario"] = [
                    'usuario_id' => $row['usuario_id'],
                    'usuario_nome' => $row['usuario_nome'],
                    'usuario_email' => $row['usuario_email'],
                    'usuario_tipo' => intval($row['usuario_tipo']),
                    'usuario_sobrenome' => $row['usuario_sobrenome'],
                    'usuario_avatar' => $row['usuario_avatar']
                ];
                
                // Redirecionar baseado no tipo de usuário
                if ($_SESSION["usuario"]['usuario_tipo'] == 2) { // Afiliado
                    header("Location: admin/afiliados.php");
                } else { // Admin
                    header("Location: admin/index.php");
                }
                exit;
            } else {
                return false; // Senha incorreta
            }
        } else {
            return false; // Usuário não encontrado
        }
    } else {
        return false; // Erro na execução da consulta
    }
}


function criarUsuario($conn, $dados)
{
    $query = "SELECT COUNT(*) as total FROM usuarios WHERE usuario_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $dados['usuario_email']);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row['total'] > 0) {
            return "O email já existe no banco de dados.";
        }

        // Iniciar transação
        $conn->begin_transaction();

        try {
            $query = "INSERT INTO usuarios (
                usuario_nome, 
                usuario_sobrenome, 
                usuario_email, 
                usuario_senha, 
                usuario_tipo,
                usuario_avatar,
                usuario_telefone
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "sssssss",
                $dados['usuario_nome'],
                $dados['usuario_sobrenome'],
                $dados['usuario_email'],
                $dados['usuario_senha'],
                $dados['usuario_tipo'],
                $dados['usuario_avatar'],
                $dados['usuario_telefone']
            );

            if ($stmt->execute()) {
                $usuario_id = $conn->insert_id;
                $conn->commit();
                return $usuario_id; // Retorna o ID do usuário ao invés de true
            }
            
            $conn->rollback();
            return "Erro ao criar usuário: " . $stmt->error;
        } catch (Exception $e) {
            $conn->rollback();
            return "Erro: " . $e->getMessage();
        }
    }
    return "Erro: " . $stmt->error;
}

function listaUsuarios($conn, $id = NULL, $nome = NULL, $email = NULL, $telefone = NULL, $tipo = NULL, $deletado = NULL) {
    $query = "SELECT * FROM usuarios WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($id !== NULL) {
        $query .= " AND usuario_id = ?";
        $params[] = $id;
        $types .= "i";
    }
    if ($nome !== NULL) {
        $query .= " AND usuario_nome LIKE ?";
        $params[] = "%$nome%";
        $types .= "s";
    }
    if ($email !== NULL) {
        $query .= " AND usuario_email LIKE ?";
        $params[] = "%$email%";
        $types .= "s";
    }
    if ($tipo !== NULL) {
        $query .= " AND usuario_tipo = ?";
        $params[] = $tipo;
        $types .= "i";
    }
    if ($deletado !== NULL) {
        $query .= " AND usuario_deletado = ?";
        $params[] = $deletado;
        $types .= "i";
    }

    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        return $usuarios;
    } else {
        return "ERRO: " . $stmt->error;
    }
}
function listaCliente($conn, $id = NULL) {
    $query = "SELECT * FROM clientes WHERE 1=1";
    
    if ($id !== NULL) {
        $query .= " AND id = ?";
    }
    
    $query .= " ORDER BY id DESC"; 
    
    $stmt = $conn->prepare($query);
    
    if ($id !== NULL) {
        $stmt->bind_param("i", $id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return [];
}

function deletarUsuario($conn, $id) {
    $query = "UPDATE usuarios SET usuario_deletado = 1 WHERE usuario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    return $stmt->execute();
}

function obterUsuario($conn, $id) {
    $query = "SELECT * FROM usuarios WHERE usuario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}

function atualizarUsuario($conn, $id, $nome = NULL, $email = NULL, $telefone = NULL, $avatar = NULL, $tipo = NULL, $senha = NULL, $sobrenome = NULL) {
    $campos = [];
    
    // Verifica se os valores são arrays e converte para string se necessário
    if ($nome !== NULL) {
        $nome = is_array($nome) ? implode(', ', $nome) : $nome;
        $campos[] = "usuario_nome = '" . mysqli_real_escape_string($conn, $nome) . "'";
    }
    if ($email !== NULL) {
        $email = is_array($email) ? implode(', ', $email) : $email;
        $campos[] = "usuario_email = '" . mysqli_real_escape_string($conn, $email) . "'";
    }
    if ($telefone !== NULL) {
        $telefone = is_array($telefone) ? implode(', ', $telefone) : $telefone;
        $campos[] = "usuario_telefone = '" . mysqli_real_escape_string($conn, $telefone) . "'";
    }
    if ($avatar !== NULL) {
        $avatar = is_array($avatar) ? implode(', ', $avatar) : $avatar;
        $campos[] = "usuario_avatar = '" . mysqli_real_escape_string($conn, $avatar) . "'";
    }
    if ($tipo !== NULL) {
        $tipo = is_array($tipo) ? implode(', ', $tipo) : $tipo;
        $campos[] = "usuario_tipo = '" . mysqli_real_escape_string($conn, $tipo) . "'";
    }
    if ($senha !== NULL) {
        $senha = is_array($senha) ? implode(', ', $senha) : $senha;
        $campos[] = "usuario_senha = '" . mysqli_real_escape_string($conn, $senha) . "'";
    }
    if ($sobrenome !== NULL) {
        $sobrenome = is_array($sobrenome) ? implode(', ', $sobrenome) : $sobrenome;
        $campos[] = "usuario_sobrenome = '" . mysqli_real_escape_string($conn, $sobrenome) . "'";
    }

    // Verifica se há campos para atualizar
    if (!empty($campos)) {
        $query = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE usuario_id = " . intval($id);
    //    echo $query;die;
        if (mysqli_query($conn, $query)) {
            return true;
        } else {
            return "Erro ao atualizar usuário: " . mysqli_error($conn);
        }
    } else {
        return "Nenhum campo foi atualizado.";
    }
}

