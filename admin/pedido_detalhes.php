<?php
require_once("header.php");
require_once("../functions/functions_pedidos.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pedidos.php');
    exit;
}

$pedido = getPedido($conn, $_GET['id']);
if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Detalhes do Pedido #<?php echo $pedido['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-6 overflow-auto">
            <div class="container mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">Pedido #<?php echo $pedido['id']; ?></h1>
                        <p class="text-gray-500 dark:text-gray-400">Criado em <?php echo date('d/m/Y H:i', strtotime($pedido['data_criacao'])); ?></p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="toggleTheme()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 dark:bg-[#27272A] dark:text-gray-200 dark:hover:bg-gray-700">
                            Alternar Tema
                        </button>
                        <button onclick="window.location.href='pedidos.php'" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                            Voltar
                        </button>
                        <?php if ($pedido['status'] == 1): ?>
                        <button onclick="aprovarPedido(<?php echo $pedido['id']; ?>)" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                            Aprovar Pedido
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informações do Cliente -->
                    <div class="bg-white dark:bg-[#27272A] rounded-lg shadow p-6">
                        <h2 class="text-lg font-medium mb-4">Informações do Cliente</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400">Nome</label>
                                <p class="font-medium"><?php echo $pedido['cliente_nome']; ?></p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400">WhatsApp</label>
                                <p class="font-medium">
                                    <a href="https://wa.me/<?php echo $pedido['cliente_whatsapp']; ?>" target="_blank" 
                                       class="text-green-500 hover:text-green-700">
                                        <?php echo $pedido['cliente_whatsapp']; ?>
                                    </a>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400">Email</label>
                                <p class="font-medium"><?php echo $pedido['cliente_email']; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Informações do Pedido -->
                    <div class="bg-white dark:bg-[#27272A] rounded-lg shadow p-6">
                        <h2 class="text-lg font-medium mb-4">Informações do Pedido</h2>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400">Campanha</label>
                                <p class="font-medium"><?php echo $pedido['campanha_nome']; ?></p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400">Quantidade</label>
                                <p class="font-medium"><?php echo $pedido['quantidade']; ?> números</p>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm text-gray-600 dark:text-gray-400">Status</label>
                                <p>
                                    <span class="<?php echo $pedido['status_classe']; ?> text-white px-2 py-1 rounded text-sm">
                                        <?php echo $pedido['status_texto']; ?>
                                    </span>
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400">Método de Pagamento</label>
                                <p class="font-medium"><?php echo $pedido['metodo_pagamento']; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Números do Pedido -->
                    <div class="bg-white dark:bg-[#27272A] rounded-lg shadow p-6 md:col-span-2">
                        <h2 class="text-lg font-medium mb-4">Números do Pedido</h2>
                        <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 lg:grid-cols-12 gap-2">
                            <?php
                            require_once('../functions/functions_sistema.php');
                            $largura_cota = obterLarguraCotaPorCampanha($conn, $pedido['campanha_id']);
                            $numeros = explode(',', $pedido['numeros_pedido']);
                            foreach ($numeros as $numero):
                                $numero_fmt = formatarCotaComLargura($numero, $largura_cota);
                            ?>
                            <div class="bg-gray-100 dark:bg-[#3F3F46] p-2 text-center rounded">
                                <?php echo $numero_fmt; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Valores -->
                    <div class="bg-white dark:bg-[#27272A] rounded-lg shadow p-6 md:col-span-2">
                        <h2 class="text-lg font-medium mb-4">Valores</h2>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                <span>R$ <?php echo number_format($pedido['valor_total'] + ($pedido['valor_desconto'] ?? 0), 2, ',', '.'); ?></span>
                            </div>
                            <?php if (!empty($pedido['valor_desconto'])): ?>
                            <div class="flex justify-between text-green-500">
                                <span>Desconto</span>
                                <span>- R$ <?php echo number_format($pedido['valor_desconto'], 2, ',', '.'); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="flex justify-between font-bold text-lg pt-2 border-t border-gray-200 dark:border-gray-700">
                                <span>Total</span>
                                <span>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    function aprovarPedido(id) {
        if (confirm('Deseja realmente aprovar este pedido?')) {
            fetch('aprovar_pedido.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erro ao aprovar pedido');
                    }
                });
        }
    }

    function toggleTheme() {
        document.documentElement.classList.toggle('dark');
        localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    }

    // Verificar e aplicar o tema salvo
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    </script>
</body>
</html> 