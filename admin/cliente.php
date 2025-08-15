<?php

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

require("header.php");
require_once("../functions/functions_clientes.php");

$erro = '';
$sucesso = '';
$cliente = null;
$nome = '';
$sobrenome = '';
$acao = $_GET['acao'] ?? '';

// Se tiver ID, é edição
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $cliente = getCliente($conn, $id);
    
    // Debug
    error_log("Dados do cliente: " . print_r($cliente, true));
    
    if (!$cliente) {
        header("Location: clientes.php");
        exit;
    }
    
    // Separar nome e sobrenome
    $nomes = explode(' ', $cliente['nome']);
    if (count($nomes) > 0) {
        $nome = $nomes[0];
        unset($nomes[0]);
        $sobrenome = implode(' ', $nomes);
    }
    
    // Debug
    error_log("Nome: " . $nome);
    error_log("Sobrenome: " . $sobrenome);
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug
    error_log("Dados do POST: " . print_r($_POST, true));
    
    $dados = [
        'nome' => trim($_POST['nome'] . ' ' . $_POST['sobrenome']),
        'email' => trim($_POST['email'] ?? ''),
        'whatsapp' => trim($_POST['telefone'] ?? ''),
        'cpf' => trim($_POST['cpf'] ?? '')
    ];
    
    // Debug
    error_log("Dados para atualização: " . print_r($dados, true));

    // Validações básicas
    if (empty($dados['nome'])) {
        $erro = "O nome é obrigatório";
    } elseif (empty($dados['whatsapp'])) {
        $erro = "O WhatsApp é obrigatório";
    } else {
        if (isset($_GET['id'])) {
            // Atualizar cliente existente
            if (atualizarCliente($conn, $_GET['id'], $dados)) {
                $sucesso = "Cliente atualizado com sucesso!";
                header("refresh:2;url=clientes.php");
            } else {
                $erro = "Erro ao atualizar cliente. Por favor, tente novamente.";
            }
        } else {
            // Cadastrar novo cliente
            if (cadastrarCliente($conn, $dados)) {
                $sucesso = "Cliente cadastrado com sucesso!";
                header("refresh:2;url=clientes.php");
            } else {
                $erro = "Erro ao cadastrar cliente. Por favor, tente novamente.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title><?php echo isset($_GET['id']) ? 'Editar' : 'Novo'; ?> Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800 dark:bg-[#18181B] dark:text-white min-h-screen">
    <div class="flex flex-col lg:flex-row min-h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-4 lg:p-8 overflow-auto">
            <div class="max-w-4xl mx-auto">
                <!-- Cabeçalho -->
                

                <?php if ($erro): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $erro; ?></span>
                </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $sucesso; ?></span>
                </div>
                <?php endif; ?>

                <!-- Formulário -->
                <div class="bg-white dark:bg-[#27272A] rounded-lg shadow-lg p-4 sm:p-6 lg:p-8">
                    <div class="flex flex-row justify-between items-center gap-4 mb-6">
                        <h1 class="text-2xl sm:text-3xl font-bold"><?php echo isset($_GET['id']) ? 'Editar' : 'Novo'; ?> Cliente</h1>
                        <a href="clientes.php" class="w-auto bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 text-center">
                            Voltar
                        </a>
                    </div>
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Nome e Sobrenome -->
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nome
                                </label>
                                <input type="text" name="nome" required
                                       value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>"
                                       class="w-full border rounded-lg p-3 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-purple-500 transition-all"
                                       placeholder="Nome">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Sobrenome
                                </label>
                                <input type="text" name="sobrenome"
                                       value="<?php echo isset($sobrenome) ? htmlspecialchars($sobrenome) : ''; ?>"
                                       class="w-full border rounded-lg p-3 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-purple-500 transition-all"
                                       placeholder="Sobrenome">
                            </div>

                            <!-- E-mail e WhatsApp -->
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    E-mail
                                </label>
                                
                                <input type="email" name="email"
                                       value="<?php echo isset($cliente['email']) ? htmlspecialchars($cliente['email']) : ''; ?>"
                                       class="w-full border rounded-lg p-3 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-purple-500 transition-all"
                                       placeholder="email@exemplo.com">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    WhatsApp
                                </label>
                                <input type="text" name="telefone" required
                                       value="<?php echo isset($cliente['telefone']) ? htmlspecialchars($cliente['telefone']) : ''; ?>"
                                       class="w-full border rounded-lg p-3 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-purple-500 transition-all"
                                       placeholder="(00) 00000-0000">
                            </div>

                            <!-- CPF -->
                            <div class="col-span-1 sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    CPF
                                </label>
                                <input type="text" name="cpf"
                                       value="<?php echo isset($cliente['cpf']) ? htmlspecialchars($cliente['cpf']) : ''; ?>"
                                       class="w-full border rounded-lg p-3 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-purple-500 transition-all"
                                       placeholder="000.000.000-00">
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="flex flex-col sm:flex-row justify-end gap-4 mt-8">
                            <button type="submit" class="w-full sm:w-auto bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors">
                                <?php echo isset($_GET['id']) ? 'Salvar Alterações' : 'Cadastrar Cliente'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
    // Máscara para WhatsApp
    document.querySelector('input[name="telefone"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        }
        e.target.value = value;
    });

    // Máscara para CPF
    document.querySelector('input[name="cpf"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }
        e.target.value = value;
    });
    </script>
</body>
</html>