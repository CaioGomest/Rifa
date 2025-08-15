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

// Função para validar telefone
function validarTelefone($telefone) {
    return preg_match('/^\d{10,11}$/', $telefone);
}

// Função para validar CPF
function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Calcula os dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

// Recebe os dados do formulário
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = preg_replace('/\D/', '', trim($_POST['telefone'] ?? ''));
$cpf = preg_replace('/\D/', '', trim($_POST['cpf'] ?? ''));

// Busca configurações do sistema para campos obrigatórios
require_once('functions/functions_sistema.php');
$config = listaInformacoes($conn);
$campos_obrigatorios = explode(',', $config['campos_obrigatorios']);

// Validações - Telefone sempre obrigatório
if (empty($telefone)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha o telefone.']);
    exit;
}

// Validações dinâmicas baseadas nos campos obrigatórios
if (in_array('nome', $campos_obrigatorios) && empty($nome)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha o nome.']);
    exit;
}

if (in_array('email', $campos_obrigatorios) && empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha o email.']);
    exit;
}

if (!empty($email) && !validarEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido.']);
    exit;
}

if (!validarTelefone($telefone)) {
    echo json_encode(['success' => false, 'message' => 'Número de telefone inválido.']);
    exit;
}

// Se CPF foi fornecido, validar
if (!empty($cpf) && !validarCPF($cpf)) {
    echo json_encode(['success' => false, 'message' => 'CPF inválido.']);
    exit;
}

// Se CPF é obrigatório mas não foi fornecido
if (in_array('cpf', $campos_obrigatorios) && empty($cpf)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha o CPF.']);
    exit;
}

// Verifica se o cliente já existe
$filtros = ['telefone' => $telefone, 'email' => $email];
$clientes = buscarClientes($conn, $filtros);

if (!empty($clientes)) {
    echo json_encode(['success' => false, 'message' => 'Email ou telefone já cadastrado.']);
    exit;
}

// Se não encontrou, insere novo cliente
$dados = [
    'telefone' => $telefone
];

// Adiciona campos dinamicamente baseado nos campos obrigatórios
if (!empty($nome)) {
    $dados['nome'] = $nome;
}

if (!empty($email)) {
    $dados['email'] = $email;
}

// Adiciona CPF se fornecido
if (!empty($cpf)) {
    $dados['cpf'] = $cpf;
}

if (cadastrarCliente($conn, $dados)) {
    $cliente = buscarClientes($conn, ['telefone' => $telefone]);
    
    if (!empty($cliente)) {
        $cliente = $cliente[0];
        
        // Inicia sessão
        session_unset();
        $_SESSION['usuario']['cliente_id'] = $cliente['id'];
        $_SESSION['usuario']['cliente_tipo'] = 0;
        $_SESSION['usuario']['cliente_nome'] = $cliente['nome'];
        
                       echo json_encode([
                   'success' => true, 
                   'message' => 'Cadastro realizado com sucesso!', 
                   'cliente_id' => $cliente['id'],
                   'isCliente' => true,
                   'isUsuarioSistema' => false
               ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar cliente.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar cliente.']);
}

mysqli_close($conn); 