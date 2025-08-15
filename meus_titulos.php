<?php
require_once('conexao.php');
require_once('functions/functions_pedidos.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Verifica se o cliente está logado
if (!isset($_SESSION["usuario"]['cliente_id'])) {
    header('Location: index.php');
    exit;
}

$cliente_id = $_SESSION["usuario"]['cliente_id'];

// Parâmetros de paginação e filtros
$limite = 10; // Itens por página
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$pular = ($pagina - 1) * $limite;

// Filtro de status
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';

// Buscar pedidos com filtros e paginação
$pedidos = listaPedidos($conn, null, $cliente_id, null, null, null, $status_filtro, null, null, $limite, $pular);

// Buscar total de pedidos para paginação
$pedidos_total = listaPedidos($conn, null, $cliente_id, null, null, null, $status_filtro, null, null, null, null);
$total_registros = count($pedidos_total);
$total_paginas = ceil($total_registros / $limite);

// Contadores por status
$pedidos_pendentes = listaPedidos($conn, null, $cliente_id, null, null, null, '0', null, null, null, null);
$pedidos_pagos = listaPedidos($conn, null, $cliente_id, null, null, null, '1', null, null, null, null);
$pedidos_cancelados = listaPedidos($conn, null, $cliente_id, null, null, null, '2', null, null, null, null);

$total_pendentes = count($pedidos_pendentes);
$total_pagos = count($pedidos_pagos);
$total_cancelados = count($pedidos_cancelados);
$total_geral = $total_pendentes + $total_pagos + $total_cancelados;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Meus Títulos</title>
    <?php require_once('header.php'); ?>
</head>
<body class="bg-gray-100 text-black dark:bg-[#18181B] dark:text-white">
    <main class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-8 text-center">Meus Títulos</h1>
        
        <!-- Cards de estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#27272A] rounded-lg p-4 shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <i class="fas fa-list text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $total_geral; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-[#27272A] rounded-lg p-4 shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pendentes</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $total_pendentes; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-[#27272A] rounded-lg p-4 shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pagos</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $total_pagos; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-[#27272A] rounded-lg p-4 shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
                        <i class="fas fa-times text-red-600 dark:text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Cancelados</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo $total_cancelados; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtros de Status -->
        <div class="flex justify-center mb-6">
            <div class="flex bg-gray-200 dark:bg-[#3F3F46] rounded-lg p-1">
                <a href="?status=" class="px-4 py-2 rounded-md <?php echo $status_filtro === '' ? 'bg-blue-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white'; ?> font-medium transition-colors">
                    Todos (<?php echo $total_geral; ?>)
                </a>
                <a href="?status=0" class="px-4 py-2 rounded-md <?php echo $status_filtro === '0' ? 'bg-yellow-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white'; ?> font-medium transition-colors">
                    Pendentes (<?php echo $total_pendentes; ?>)
                </a>
                <a href="?status=1" class="px-4 py-2 rounded-md <?php echo $status_filtro === '1' ? 'bg-green-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white'; ?> font-medium transition-colors">
                    Pagos (<?php echo $total_pagos; ?>)
                </a>
                <a href="?status=2" class="px-4 py-2 rounded-md <?php echo $status_filtro === '2' ? 'bg-red-500 text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white'; ?> font-medium transition-colors">
                    Cancelados (<?php echo $total_cancelados; ?>)
                </a>
            </div>
        </div>

        <!-- Paginação Superior -->
        <?php if ($total_paginas > 1): ?>
        <div class="flex justify-center mb-4">
            <div class="flex items-center space-x-2">
                <?php if ($pagina > 1): ?>
                    <a href="?status=<?php echo $status_filtro; ?>&pagina=<?php echo $pagina - 1; ?>" class="px-3 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                $max_links = 2;
                $start = max(1, $pagina - $max_links);
                $end = min($total_paginas, $pagina + $max_links);
                
                if ($start > 1) {
                    echo '<a href="?status=' . $status_filtro . '&pagina=1" class="px-3 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">1</a>';
                    if ($start > 2) {
                        echo '<span class="px-2 text-gray-400">...</span>';
                    }
                }
                
                for ($i = $start; $i <= $end; $i++) {
                    $active_class = $i == $pagina ? 'bg-blue-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
                    echo '<a href="?status=' . $status_filtro . '&pagina=' . $i . '" class="px-3 py-1 rounded ' . $active_class . '">' . $i . '</a>';
                }
                
                if ($end < $total_paginas) {
                    if ($end < $total_paginas - 1) {
                        echo '<span class="px-2 text-gray-400">...</span>';
                    }
                    echo '<a href="?status=' . $status_filtro . '&pagina=' . $total_paginas . '" class="px-3 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">' . $total_paginas . '</a>';
                }
                ?>
                
                <?php if ($pagina < $total_paginas): ?>
                    <a href="?status=<?php echo $status_filtro; ?>&pagina=<?php echo $pagina + 1; ?>" class="px-3 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabela de Títulos -->
        <div class="bg-white dark:bg-[#27272A] rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-[#3F3F46]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">COD.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Qtd. N</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dt. Pedido</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Situação</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ver</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#27272A] divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (!empty($pedidos)): ?>
                            <?php foreach ($pedidos as $pedido): 
                                $campanha = listaCampanhas($conn, $pedido['campanha_id']);
                                $campanha = isset($campanha[0]) ? $campanha[0] : null;
                                $numeros = !empty($pedido['numeros_pedido']) ? explode(',', $pedido['numeros_pedido']) : [];
                                $quantidade_numeros = count($numeros);
                                $largura_cota = obterLarguraCotaPorCampanha($conn, $pedido['campanha_id']);
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($campanha && !empty($campanha['caminho_imagem'])): ?>
                                            <img src="<?php echo $campanha['caminho_imagem']; ?>" 
                                                 alt="<?php echo $campanha['nome']; ?>" 
                                                 class="w-8 h-8 rounded object-cover mr-3">
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded mr-3 flex items-center justify-center">
                                                <i class="fas fa-image text-gray-500"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo str_pad($pedido['id'], 7, '0', STR_PAD_LEFT); ?>
                                            </div>
                                            <?php if ($campanha): ?>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    <?php echo $campanha['nome']; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                 <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                     <?php echo $quantidade_numeros; ?>
                                 </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?php echo date('d/m/Y, H:i', strtotime($pedido['data_criacao'])); ?>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="<?php echo classeStatusPedido($pedido); ?> text-white px-3 py-1 rounded-full text-xs font-medium">
                                        <?php echo textoStatusPedido($pedido); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    <?php if ($pedido['status'] == 0): ?>
                                        <button onclick="window.location.href='pagamento.php?order_id=<?php echo $pedido['id']; ?>'" 
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                            Pagar
                                        </button>
                                    <?php else: ?>
                                        <button onclick="window.location.href='pagamento.php?order_id=<?php echo $pedido['id']; ?>'" 
                                                class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-xs font-medium transition-colors">
                                            Ver
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <?php if ($status_filtro !== ''): ?>
                                        Nenhum título encontrado com o filtro selecionado.
                                    <?php else: ?>
                                        Você ainda não possui títulos.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginação Inferior -->
        <?php if ($total_paginas > 1): ?>
        <div class="flex justify-center mt-4">
            <div class="flex items-center space-x-2">
                <?php if ($pagina > 1): ?>
                    <a href="?status=<?php echo $status_filtro; ?>&pagina=<?php echo $pagina - 1; ?>" class="px-3 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php
                $max_links = 2;
                $start = max(1, $pagina - $max_links);
                $end = min($total_paginas, $pagina + $max_links);
                
                if ($start > 1) {
                    echo '<a href="?status=' . $status_filtro . '&pagina=1" class="px-3 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">1</a>';
                    if ($start > 2) {
                        echo '<span class="px-2 text-gray-400">...</span>';
                    }
                }
                
                for ($i = $start; $i <= $end; $i++) {
                    $active_class = $i == $pagina ? 'bg-blue-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200';
                    echo '<a href="?status=' . $status_filtro . '&pagina=' . $i . '" class="px-3 py-1 rounded ' . $active_class . '">' . $i . '</a>';
                }
                
                if ($end < $total_paginas) {
                    if ($end < $total_paginas - 1) {
                        echo '<span class="px-2 text-gray-400">...</span>';
                    }
                    echo '<a href="?status=' . $status_filtro . '&pagina=' . $total_paginas . '" class="px-3 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">' . $total_paginas . '</a>';
                }
                ?>
                
                <?php if ($pagina < $total_paginas): ?>
                    <a href="?status=<?php echo $status_filtro; ?>&pagina=<?php echo $pagina + 1; ?>" class="px-3 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (empty($pedidos) && $status_filtro === ''): ?>
            <div class="text-center py-8">
                <p class="text-gray-400">Você ainda não possui títulos.</p>
                <a href="index.php" class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors">
                    Ver Campanhas Disponíveis
                </a>
            </div>
        <?php endif; ?>
    </main>
    
    <?php require_once('footer.php'); ?>
</body>
</html> 