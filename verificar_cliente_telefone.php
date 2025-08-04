<?php
require_once('conexao.php');
// session_start();

header('Content-Type: application/json');

// Função para validar telefone (Apenas números, 10 ou 11 dígitos para BR)
function validarTelefone($telefone)
{
    return preg_match('/^\d{10,11}$/', $telefone);
}

// Recebe o número de telefone
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

// Verifica se o cliente já existe
$sql = "SELECT 
  usuario_id AS id, 
  usuario_tipo AS tipo, 
  usuario_nome COLLATE utf8mb4_general_ci AS nome 
FROM usuarios 
WHERE usuario_telefone = ? 

UNION 

SELECT 
  id, 
  '' AS tipo,  -- já que clientes não tem tipo, passa string vazia para manter número de colunas
  nome COLLATE utf8mb4_general_ci AS nome 
FROM clientes 
WHERE telefone = ? 

LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $telefone, $telefone); // sem vírgula!
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($result);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

session_unset(); // limpa todas as variáveis da sessão
if ($cliente) {
    $_SESSION["usuario"]['cliente_id'] = $cliente['id'];
    $_SESSION["usuario"]['cliente_tipo'] = isset($cliente['tipo']) ? $cliente['tipo'] : 0;
    $_SESSION["usuario"]['cliente_nome'] = isset($cliente['nome']) ? $cliente['nome'] : (isset($cliente['usuario_nome']) ? $cliente['usuario_nome'] : '');

    // $_SESSION['cliente_email'] = $cliente['email'];
    // $_SESSION['cliente_telefone'] = $cliente['telefone'];



    echo json_encode([
        'success' => true,
        'message' => 'Cliente encontrado!',
        'cliente_id' => $cliente['id'],
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Cliente não encontrado. Por favor, cadastre-se.',
    'need_register' => true
]);

mysqli_close($conn);