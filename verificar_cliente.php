<?php
require_once('conexao.php');
require_once('functions/functions_clientes.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Função para validar email
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para validar telefone (Apenas números, 10 ou 11 dígitos para BR)
function validarTelefone($telefone) {
    return preg_match('/^\d{10,11}$/', $telefone);
}

// Recebe os dados do formulário
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = preg_replace('/\D/', '', trim($_POST['telefone'] ?? ''));

// if(empty($email)) {
//     echo json_encode(['success' => false, 'message' => 'Por favor, preencha o email.']);
//     exit;
// }

// if(empty($telefone)) {
//     echo json_encode(['success' => false, 'message' => 'Por favor, preencha o telefone.']);
//     exit;
// }
// if (!validarEmail($email)) {
//     echo json_encode(['success' => false, 'message' => 'Email inválido.']);
//     exit;
// }

// if (!validarTelefone($telefone)) {
//     echo json_encode(['success' => false, 'message' => 'Número de telefone inválido.']);
//     exit;
// }

// Verifica se o cliente já existe
$filtros = ['telefone' => $telefone, 'email' => $email];
$clientes = buscarClientes($conn, $filtros);

if (!empty($clientes)) {
    $cliente = $clientes[0];
    if ($cliente['email'] === $email && $cliente['telefone'] === $telefone) {
        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nome'] = $cliente['nome'];
        $_SESSION['cliente_email'] = $cliente['email'];
        $_SESSION['cliente_telefone'] = $cliente['telefone'];

        echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso!', 'cliente_id' => $cliente['id']]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Email ou telefone já cadastrado com outro usuário.']);
    exit;
}

// Se não encontrou, insere novo cliente
$dados = [
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone
];

if (cadastrarCliente($conn, $dados)) 
{
    $cliente = buscarClientes($conn, ['telefone' => $telefone]);
    
    if (!empty($cliente)) 
    {
        $cliente = $cliente[0];
        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nome'] = $cliente['nome'];
        $_SESSION['cliente_email'] = $cliente['email'];
        $_SESSION['cliente_telefone'] = $cliente['telefone'];
    }
    else
    {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar cliente.']);
    }

    echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!', 'cliente_id' => $cliente['id']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar cliente.']);
}

mysqli_close($conn);
