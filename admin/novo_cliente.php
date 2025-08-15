<?php
require_once("header.php");
require_once("../functions/functions_clientes.php");

$erro = '';
$sucesso = '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome' => trim($_POST['nome'] . ' ' . $_POST['sobrenome']),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'cpf' => trim($_POST['cpf'] ?? '')
    ];

    // Validações básicas
    if (empty($dados['nome'])) {
        $erro = "O nome é obrigatório";
    } elseif (empty($dados['telefone'])) {
        $erro = "O telefone é obrigatório";
    } else {
        if (cadastrarCliente($conn, $dados)) {
            $sucesso = "Cliente cadastrado com sucesso!";
            // Redirecionar após 2 segundos
            header("refresh:2;url=clientes.php");
        } else {
            $erro = "Erro ao cadastrar cliente. Por favor, tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Novo Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#18181B] dark:text-white">
    <div class="flex min-h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-2 sm:p-6 overflow-auto lg:ml-64">
            <div class="container mx-auto max-w-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Novo Cliente</h1>
                    <a href="clientes.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                        Voltar
                    </a>
                </div>

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

                <div class="bg-white dark:bg-[#27272A] rounded-lg shadow p-6">
                    <form action="clientes.php" method="POST" class="space-y-6">
                        <input type="hidden" name="acao" value="novo_cliente">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nome
                                </label>
                                <input type="text" name="nome" required
                                       class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                       placeholder="Nome">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Sobrenome
                                </label>
                                <input type="text" name="sobrenome" required
                                       class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                       placeholder="Sobrenome">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email
                            </label>
                            <input type="email" name="email" required
                                   class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                   placeholder="email@exemplo.com">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                CPF
                            </label>
                            <input type="text" name="cpf" required
                                   class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                   placeholder="000.000.000-00">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                WhatsApp
                            </label>
                            <input type="tel" name="telefone" required
                                   class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                   placeholder="(00) 00000-0000">
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Máscara para telefone
    const telefone = document.querySelector('input[name="telefone"]');
    telefone.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        
        if (value.length > 2) {
            value = '(' + value.slice(0, 2) + ') ' + value.slice(2);
        }
        if (value.length > 9) {
            value = value.slice(0, 10) + '-' + value.slice(10);
        }
        e.target.value = value;
    });

    // Máscara para CPF
    const cpf = document.querySelector('input[name="cpf"]');
    cpf.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        
        if (value.length > 3) {
            value = value.slice(0, 3) + '.' + value.slice(3);
        }
        if (value.length > 7) {
            value = value.slice(0, 7) + '.' + value.slice(7);
        }
        if (value.length > 11) {
            value = value.slice(0, 11) + '-' + value.slice(11);
        }
        e.target.value = value;
    });
    </script>
</body>
</html> 