<?php
require_once("header.php");
require_once("../functions/functions_clientes.php");
require '../conexao.php';


$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'novo_cliente') {
    $dados = [
        'nome' => trim($_POST['nome'] . ' ' . $_POST['sobrenome']),
        'email' => trim($_POST['email'] ?? ''),
        'telefone' => trim($_POST['telefone'] ?? ''),
        'cpf' => trim($_POST['cpf'] ?? '')
    ];

    if (cadastrarCliente($conn, $dados)) {
        $sucesso = "Cliente cadastrado com sucesso!";
    } else {
        $erro = "Erro ao cadastrar cliente. Por favor, tente novamente.";
    }
}

$nome = isset($_GET['nome']) ? $_GET['nome'] : null;
$telefone = isset($_GET['telefone']) ? $_GET['telefone'] : null;
$cpf = isset($_GET['cpf']) ? $_GET['cpf'] : null;
$email = isset($_GET['email']) ? $_GET['email'] : null;
$limite = 10;
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$pular = ($pagina - 1) * $limite;


$clientes = listaClientes($conn, null, $nome, $email, $telefone, $cpf, null, null, null, $limite, $pular);
$clientes_total = listaClientes($conn, null, $nome, $email, $telefone, $cpf, null, null, null, $limite);
$total_registros = count($clientes_total);
$total_paginas = ceil($total_registros / $limite);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800 dark:bg-[#18181B] dark:text-white">
    <div class="flex min-h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-2 sm:p-6 overflow-auto">
            <div class="container mx-auto">
                <?php if ($erro): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $erro; ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <span class="block sm:inline"><?php echo $sucesso; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Cabeçalho com título e botão -->
                <div class="flex flex-row justify-between items-center mb-6 gap-4">
                    <h1 class="text-2xl font-bold">Clientes</h1>
                    <a href="cliente.php" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                        Cadastrar novo
                    </a>
                </div>

                <!-- Filtros -->
                <div class="bg-white dark:bg-[#27272A] rounded-lg shadow p-4 sm:p-6 mb-6">
                    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pesquisar por
                                nome</label>
                            <input type="text" name="nome" value="<?php echo $_GET['nome'] ?? ''; ?>"
                                class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                placeholder="Nome do cliente">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pesquisar por
                                telefone</label>
                            <input type="text" name="telefone" value="<?php echo $_GET['telefone'] ?? ''; ?>"
                                class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                placeholder="(00) 00000-0000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pesquisar por
                                CPF</label>
                            <input type="text" name="cpf" value="<?php echo $_GET['cpf'] ?? ''; ?>"
                                class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                placeholder="000.000.000-00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pesquisar por
                                email</label>
                            <input type="email" name="email" value="<?php echo $_GET['email'] ?? ''; ?>"
                                class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                placeholder="email@exemplo.com">
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                            <button type="submit"
                                class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700">
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>

                <!--<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">-->
                <!--    <button class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 w-full sm:w-auto">-->
                <!--        Exportar Clientes-->
                <!--    </button>-->
                <!--</div>-->

                <!-- Tabela de Clientes -->
                <div class="bg-white dark:bg-[#27272A] rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-[#3F3F46]">
                                <tr>
                                    <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Avatar</th>
                                    <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Nome</th>
                                    <th
                                        class="px-4 py-2 text-left text-gray-700 dark:text-gray-300 hidden sm:table-cell">
                                        E-mail</th>
                                    <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">WhatsApp</th>
                                    <th
                                        class="px-4 py-2 text-left text-gray-700 dark:text-gray-300 hidden sm:table-cell">
                                        Data de Cadastro</th>
                                    <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php if ($clientes):
                                    foreach ($clientes as $cliente): ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-4 py-2">
                                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($cliente['nome']); ?>&background=6D28D9&color=fff"
                                                    alt="<?php echo htmlspecialchars($cliente['nome']); ?>"
                                                    class="w-8 h-8 rounded-full">
                                            </td>
                                            <td class="px-4 py-2"><?php echo htmlspecialchars($cliente['nome']); ?></td>
                                            <td class="px-4 py-2 hidden sm:table-cell">
                                                <?php echo htmlspecialchars($cliente['email'] ?? '-'); ?></td>
                                            <td class="px-4 py-2">
                                                <?php if (!empty($cliente['whatsapp'])): ?>
                                                    <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $cliente['whatsapp']); ?>"
                                                        target="_blank" class="text-green-500 hover:text-green-700">
                                                        <?php echo htmlspecialchars($cliente['whatsapp']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-2 hidden sm:table-cell">
                                                <?php
                                                if (!empty($cliente['data_criacao'])) {
                                                    echo date('d/m/Y H:i', strtotime($cliente['data_criacao']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td class="px-4 py-2">
                                                <div class="flex space-x-2">

                                                    <button
                                                        class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white"
                                                        onclick="window.location.href='pedidos.php?nome_cliente=<?= $cliente['nome'] ?>'">
                                                        👁
                                                    </button>
                                                    <button onclick="editarCliente(<?php echo $cliente['id']; ?>)"
                                                        class="text-blue-500 hover:text-blue-700">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="excluirCliente(<?php echo $cliente['id']; ?>)"
                                                        class="text-red-500 hover:text-red-700">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                            Nenhum cliente encontrado
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



            <?php if ($total_paginas > 1): ?>
                <div class="flex justify-center items-center mt-6">
                    <nav class="inline-flex space-x-1">
                        <?php
                        $max_links = 2; // Quantas páginas antes e depois mostrar
                        $start = max(1, $pagina - $max_links);
                        $end = min($total_paginas, $pagina + $max_links);

                        $query = $_GET;

                        // Primeira página
                        if ($pagina > 1) {
                            $query['pagina'] = 1;
                            echo '<a href="?' . http_build_query($query) . '" class="px-3 py-1 rounded-md text-sm font-medium bg-white dark:bg-[#3F3F46] text-gray-800 dark:text-white">1</a>';
                            if ($start > 2) {
                                echo '<span class="px-2">...</span>';
                            }
                        }

                        // Páginas intermediárias
                        for ($i = $start; $i <= $end; $i++) {
                            $query['pagina'] = $i;
                            echo '<a href="?' . http_build_query($query) . '" class="px-3 py-1 rounded-md text-sm font-medium ' . ($i == $pagina ? 'bg-purple-600 text-white' : 'bg-white dark:bg-[#3F3F46] text-gray-800 dark:text-white') . '">' . $i . '</a>';
                        }

                        // Última página (só se não estiver no intervalo)
                        if ($end < $total_paginas) {
                            if ($end < $total_paginas - 1) {
                                echo '<span class="px-2">...</span>';
                            }
                            $query['pagina'] = $total_paginas;
                            echo '<a href="?' . http_build_query($query) . '" class="px-3 py-1 rounded-md text-sm font-medium bg-white dark:bg-[#3F3F46] text-gray-800 dark:text-white">' . $total_paginas . '</a>';
                        }
                        ?>
                    </nav>
                </div>
            <?php endif; ?>


        </main>
    </div>

    <script>
        function editarCliente(id) {
            window.location.href = 'cliente.php?id=' + id;
        }

        function excluirCliente(id) {
            if (confirm('Deseja realmente excluir este cliente?')) {
                fetch('excluir_cliente.php?id=' + id)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro ao excluir cliente');
                        }
                    });
            }
        }
    </script>
</body>

</html>