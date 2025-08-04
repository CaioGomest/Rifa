<?php
require_once('conexao.php');
require_once('functions/functions_clientes.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Função para validar telefone
function validarTelefone($telefone) {
    return preg_match('/^\d{10,11}$/', $telefone);
}

// Recebe o telefone
$telefone = preg_replace('/\D/', '', trim($_POST['telefone'] ?? ''));

// Validação do telefone
if (empty($telefone)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, informe o número de telefone.']);
    exit;
}

if (!validarTelefone($telefone)) {
    echo json_encode(['success' => false, 'message' => 'Número de telefone inválido.']);
    exit;
}

// Verifica se o usuário já existe
$sql = "SELECT 
  usuario_id AS id, 
  usuario_tipo AS tipo, 
  CONCAT(usuario_nome, ' ', usuario_sobrenome) COLLATE utf8mb4_general_ci AS nome 
FROM usuarios 
WHERE usuario_telefone = ? 

UNION 

SELECT 
  id, 
  NULL AS tipo,  -- NULL para clientes (não tem tipo)
  nome COLLATE utf8mb4_general_ci AS nome 
FROM clientes 
WHERE telefone = ? 

LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $telefone, $telefone);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($result);

session_unset(); // limpa todas as variáveis da sessão

if ($cliente) {
    // Verifica se é um usuário do sistema (tem tipo) ou cliente
    if ($cliente['tipo'] !== null) {
        // É um usuário do sistema (admin/afiliado)
        $_SESSION["usuario"]['usuario_id'] = $cliente['id'];
        $_SESSION["usuario"]['usuario_tipo'] = $cliente['tipo'];
        $_SESSION["usuario"]['usuario_nome'] = $cliente['nome'];
    } else {
        // É um cliente
        $_SESSION["usuario"]['cliente_id'] = $cliente['id'];
        $_SESSION["usuario"]['cliente_tipo'] = 0;
        $_SESSION["usuario"]['cliente_nome'] = $cliente['nome'];
    }

    // Verifica se é um usuário do sistema (tem tipo) ou cliente
    if ($cliente['tipo'] !== null) {
        // É um usuário do sistema (admin/afiliado)
        $_SESSION["usuario"]['usuario_id'] = $cliente['id'];
        $_SESSION["usuario"]['usuario_tipo'] = $cliente['tipo'];
        $_SESSION["usuario"]['usuario_nome'] = $cliente['nome'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'cliente_id' => $cliente['id'],
            'isCliente' => false,
            'isUsuarioSistema' => true
        ]);
    } else {
        // É um cliente
        $_SESSION["usuario"]['cliente_id'] = $cliente['id'];
        $_SESSION["usuario"]['cliente_tipo'] = 0;
        $_SESSION["usuario"]['cliente_nome'] = $cliente['nome'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'cliente_id' => $cliente['id'],
            'isCliente' => true,
            'isUsuarioSistema' => false
        ]);
    }
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Cliente não encontrado. Por favor, cadastre-se primeiro.',
    'need_register' => true
]);

mysqli_close($conn); 