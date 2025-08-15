<?php
require_once("header.php");
require_once("../functions/functions_relatorios.php");

// Filtros
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');
$campanha = $_GET['campanha'] ?? '';
$status = $_GET['status'] ?? '';
$metodo = $_GET['metodo'] ?? '';

// Inicializar variáveis com valores padrão
$total_cotas = 0;
$novos_clientes = 0;
$pedidos_efetuados = 0;
$faturamento = 0;

// Buscar dados para os cards usando as funções específicas
$total_cotas = getTotalCotas($conn, $data_inicio, $data_fim, $campanha, $status, $metodo);
$novos_clientes = getNovosClientes($conn, $data_inicio, $data_fim, $campanha, $status, $metodo);

//paginador
$limite     = 20;
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
$pular = ($pagina - 1) * $limite;


// Buscar dados para a tabela
$pedidos = listaPedidos($conn, null, null, $campanha, $data_inicio, $data_fim, $status, $metodo, null, $limite, $pular );

$campanhas = listaCampanhas($conn);


$faturamento_total = listaPedidos($conn, null, null, $campanha, $data_inicio, $data_fim, 1, $metodo);
$total = 0;
foreach($faturamento_total as $faturamento)
{
    $total += $faturamento['valor_total'];
}
$faturamento = $total;


$pedidos_total = listaPedidos($conn, null, null, $campanha, $data_inicio, $data_fim, $status, $metodo, null );
$total_registros = count($pedidos_total);
$total_paginas = ceil($total_registros / $limite);

$pedidos_efetuados = count($pedidos_total);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Relatórios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800 dark:bg-[#18181B] dark:text-white min-h-screen">
    <div class="flex flex-col lg:flex-row min-h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-4 lg:p-8 overflow-auto">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-2xl font-bold mb-6">Relatórios</h1>

                <!-- Filtros -->
                <div class="bg-white dark:bg-[#27272A] rounded-lg shadow-lg p-4 mb-6">
                    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="relative">
                            <select name="campanha"
                                class="w-full border rounded-lg p-2.5 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white appearance-none">
                                <option value="">Todas as campanhas</option>
                                <?php foreach ($campanhas as $camp): ?>
                                    <option value="<?php echo $camp['id']; ?>" <?php echo $campanha == $camp['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($camp['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <select name="status"
                                class="w-full border rounded-lg p-2.5 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white appearance-none">
                                <option value="">Todos os status</option>
                                <option value="0" <?php echo $status == '0' ? 'selected' : ''; ?>>Pendente</option>
                                <option value="1" <?php echo $status == '1' ? 'selected' : ''; ?>>Pago</option>
                                <option value="2" <?php echo $status == '2' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <select name="metodo"
                                class="w-full border rounded-lg p-2.5 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white appearance-none">
                                <option value="">Todos os métodos</option>
                                <option value="Pay2M" <?php echo $metodo == 'Pay2M' ? 'selected' : ''; ?>>Pay2M</option>
                                <option value="Manual" <?php echo $metodo == 'Manual' ? 'selected' : ''; ?>>Manual
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>

                        <input type="date" name="data_inicio" value="<?php echo $data_inicio; ?>"
                            class="border rounded-lg p-2.5 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">

                        <input type="date" name="data_fim" value="<?php echo $data_fim; ?>"
                            class="border rounded-lg p-2.5 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">

                        <div class="lg:col-span-5 flex justify-end">
                            <button type="submit"
                                class="bg-purple-600 text-white px-6 py-2.5 rounded-lg hover:bg-purple-700 transition-colors">
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Cotas vendidas -->
                    <div class="bg-white dark:bg-[#27272A] rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <i class="fas fa-ticket-alt text-green-500"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Cotas vendidas</p>
                                <p class="text-xl font-semibold"><?php echo number_format($total_cotas, 0, ',', '.'); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Novos clientes -->
                    <div class="bg-white dark:bg-[#27272A] rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900">
                                <i class="fas fa-users text-orange-500"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Novos clientes</p>
                                <p class="text-xl font-semibold">
                                    <?php echo number_format($novos_clientes, 0, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Pedidos efetuados -->
                    <div class="bg-white dark:bg-[#27272A] rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <i class="fas fa-shopping-cart text-blue-500"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pedidos efetuados</p>
                                <p class="text-xl font-semibold">
                                    <?php echo number_format($pedidos_efetuados, 0, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Faturamento -->
                    <div class="bg-white dark:bg-[#27272A] rounded-lg shadow-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-cyan-100 dark:bg-cyan-900">
                                <i class="fas fa-dollar-sign text-cyan-500"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Faturamento</p>
                                <p class="text-xl font-semibold">R$
                                    <?php echo number_format($faturamento, 2, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela -->
                <div class="bg-white dark:bg-[#27272A] rounded-lg shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <?php if (empty($pedidos)): ?>
                            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-search fa-3x mb-4"></i>
                                <p class="text-lg">Nenhum pedido encontrado para os filtros selecionados</p>
                            </div>
                        <?php else: ?>
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-[#3F3F46]">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Data</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Campanha</th>
                                            <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Cliente</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Gateway</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Qtd. Números</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#27272A] divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php foreach ($pedidos as $pedido):
                                            $campanha_pedido = listaCampanhas($conn, $pedido['campanha_id']);
                                        ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">#<?php echo $pedido['id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo date('d/m/Y', strtotime($pedido['data_criacao'])); ?></td>
                                            <td class="px-6 py-4">
                                                <?php echo isset($campanha_pedido[0]['nome']) ? $campanha_pedido[0]['nome'] : 'Campanha não encontrada'; ?>
                                                <?php if ($pedido['status'] == 1): ?>
                                                    <i class="fas fa-check-circle text-green-500 ml-2"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php echo $pedido['cliente_nome']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                    $status_classes = '';
                                                    $status_textos = '';
                                                    $status_classes = classeStatusPedido($pedido);
                                                    $status_textos = textoStatusPedido($pedido);   
                                                ?>
                                                <span class="<?php echo $status_classes; ?> text-white px-2 py-1 rounded text-sm">
                                                <?php echo $status_textos; ?>
                                            </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap"><?php echo $pedido['metodo_pagamento']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                if ($pedido['numeros_pedido'] != null) {
                                                    $cotas_pedido = explode(',', $pedido['numeros_pedido']);
                                                    echo count($cotas_pedido);
                                                } else
                                                    echo "0";

                                                ?>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap">R$
                                                <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
                <div class="flex justify-center items-center mt-6">
                    <nav class="inline-flex space-x-1">
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <?php
                            $query = $_GET;
                            $query['pagina'] = $i;
                            $query_string = http_build_query($query);
                            ?>
                            <a href="?<?php echo $query_string; ?>"
                                class="px-3 py-1 rounded-md text-sm font-medium <?php echo $i == $pagina ? 'bg-purple-600 text-white' : 'bg-white dark:bg-[#3F3F46] text-gray-800 dark:text-white '; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>