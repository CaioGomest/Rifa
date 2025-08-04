    <?php
    require_once("header.php");
    require_once("../functions/functions_pedidos.php");
    require_once("../functions/functions_campanhas.php");
    require_once("../functions/functions_clientes.php");
    $pedido_id = isset($_GET['pedido_id']) ? $_GET['pedido_id'] : null;
    $nome_cliente = isset($_GET['nome_cliente']) ? $_GET['nome_cliente'] : null;
    $campanha_id = isset($_GET['campanha']) ? $_GET['campanha'] : null;
    $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
    $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $metodo = isset($_GET['metodo']) ? $_GET['metodo'] : null;
    $numero_cota = isset($_GET['numero_cota']) ? $_GET['numero_cota'] : null;

    $campanhas = listaCampanhas($conn, null, null, 1);
    $clientes = listaClientes($conn, null, $nome_cliente );

    $limite = 20;
    $pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
    $pular = ($pagina - 1) * $limite;

    $pedidos = listaPedidos($conn, $pedido_id, null, $campanha_id, $data_inicio, $data_fim, $status, $metodo, $numero_cota, $limite, $pular, $nome_cliente);
    $pedidos_total  = listaPedidos($conn, $pedido_id, null, $campanha_id, $data_inicio, $data_fim, $status, $metodo, $numero_cota, null,null, $nome_cliente);
    $total_registros = count($pedidos_total);
    $total_paginas = ceil($total_registros / $limite);
    
    ?>

    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <title>Pedidos</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            .filtros-container {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease-out, opacity 0.3s ease-out, margin 0.3s ease-out;
                opacity: 0;
                margin: 0;
            }

            .filtros-container.show {
                max-height: 1000px;
                opacity: 1;
                margin-bottom: 1.5rem;
            }
        </style>
    </head>

    <body class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-white">
        <div class="flex min-h-screen">
            <?php require("sidebar.php"); ?>

            <main class="flex-1 p-2 sm:p-6 overflow-y-auto">
                <div class="container mx-auto">
                    <div class="flex flex-row justify-between items-center mb-6 gap-4">
                        <h1 class="text-2xl font-bold">Pedidos</h1>
                        <div class="flex items-center space-x-4 sm:w-auto">
                            <a href="pedido.php" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                                Cadastrar novo
                            </a>
                        </div>
                    </div>

                    <!-- Botão de Filtro -->
                    <div class="flex justify-end mb-4">
                        <button onclick="toggleFiltros()"
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 flex items-center gap-2">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>

                    <!-- Filtros -->
                    <div id="secaoFiltros" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 sm:p-6 filtros-container">
                        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Campanha</label>
                                <select name="campanha"
                                    class="w-full border rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Todas as campanhas</option>
                                    <?php foreach ($campanhas as $campanha): ?>
                                        <option value="<?php echo $campanha['id']; ?>" <?php echo isset($_GET['campanha']) && $_GET['campanha'] == $campanha['id'] ? 'selected' : ''; ?>>
                                            <?php echo $campanha['nome']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select name="status"
                                    class="w-full border rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Todos</option>
                                    <option value="0" <?php echo isset($_GET['status']) && $_GET['status'] == '0' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="1" <?php echo isset($_GET['status']) && $_GET['status'] == '1' ? 'selected' : ''; ?>>Pago</option>
                                    <option value="2" <?php echo isset($_GET['status']) && $_GET['status'] == '2' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pedido</label>
                                <input type="text" name="pedido_id" value="<?php echo $_GET['pedido_id'] ?? ''; ?>"
                                    class="w-full border rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                                <input name="nome_cliente" type="text" value="<?php echo isset($nome_cliente)?$nome_cliente:'';?>"
                                    class="w-full border rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                    Início</label>
                                <input type="date" name="data_inicio" value="<?php echo $_GET['data_inicio'] ?? ''; ?>"
                                    class="w-full border rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data
                                    Fim</label>
                                <input type="date" name="data_fim" value="<?php echo $_GET['data_fim'] ?? ''; ?>"
                                    class="w-full border rounded-md p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="sm:col-span-2 lg:col-span-3 flex justify-end">
                                <button type="submit"
                                    class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700">
                                    Filtrar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tabela de Pedidos -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                        <div class="overflow-x-auto">
                            <div class="min-w-[800px]">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                                                ID</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base hidden sm:table-cell">
                                                DATA</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                                                CAMPANHA</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base hidden sm:table-cell">
                                                CLIENTE</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                                                QTD</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                                                NÚMEROS</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                                                TOTAL</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base hidden lg:table-cell">
                                                AFILIADO</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                                                STATUS</th>
                                            <th
                                                class="px-2 sm:px-4 py-2 text-left text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                                                AÇÃO</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php
                                        if ($pedidos):
                                            foreach ($pedidos as $pedido):

                                                $status_classes = classeStatusPedido($pedido);
                                                $status_textos = textoStatusPedido($pedido);

                                                if ($pedido['afiliado_id'] != NULL) {
                                                    $usuario = listaUsuarios($conn, $pedido['afiliado_id']);
                                                    if( isset($usuario[0]['usuario_nome']))
                                                        $usuario_nome = $usuario[0]['usuario_nome'];
                                                    else
                                                        $usuario_nome = '-';

                                                } else
                                                    $usuario_nome = '-';
                                                ?>
                                                <tr
                                                    class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base">#<?php echo $pedido['id']; ?>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base hidden sm:table-cell">
                                                        <?php echo date('d/m/Y H:i', strtotime($pedido['data_criacao'])); ?>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base">
                                                        <?php echo $pedido['campanha_nome']; ?>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base hidden sm:table-cell">
                                                        <?php if ($pedido['cliente_nome']): ?>
                                                            <?php echo $pedido['cliente_nome']; ?>
                                                            <?php if ($pedido['cliente_telefone']): ?>
                                                                <a href="https://wa.me/<?php echo $pedido['cliente_telefone']; ?>"
                                                                    target="_blank" class="text-green-500 hover:text-green-700 ml-2">
                                                                    <i class="fab fa-whatsapp"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <?php echo $pedido['cliente_id']; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base">
                                                        <?php echo $pedido['quantidade']; ?>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base">
                                                        <button
                                                            class="bg-blue-500 text-white px-2 py-1 rounded text-sm hover:bg-blue-600"
                                                            onclick="verNumeros('<?php echo $pedido['numeros_pedido']; ?>')">
                                                            Ver números
                                                        </button>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base">R$
                                                        <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base hidden lg:table-cell">
                                                        <?php echo $usuario_nome ?: '-'; ?>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base">

                                                        <span
                                                            class="<?php echo $status_classes; ?> text-white px-2 py-1 rounded text-sm">
                                                            <?php echo $status_textos; ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-2 sm:px-4 py-2 text-sm sm:text-base">
                                                        <div class="flex space-x-2">
                                                            <a href="pedido.php?id=<?php echo $pedido['id']; ?>"
                                                                class="text-blue-500 hover:text-blue-700" title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if ($pedido['status'] == 0): ?>
                                                                <button onclick="aprovarPedido(<?php echo $pedido['id']; ?>)"
                                                                    class="text-green-500 hover:text-green-700" title="Aprovar">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <button onclick="excluirPedido(<?php echo $pedido['id']; ?>)"
                                                                class="text-red-500 hover:text-red-700" title="Excluir">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                    Nenhum pedido encontrado
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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
                                echo '<a href="?' . http_build_query($query) . '" class="px-3 py-1 rounded-md text-sm font-medium bg-white dark:bg-gray-700 text-gray-800 dark:text-white">1</a>';
                                if ($start > 2) {
                                    echo '<span class="px-2">...</span>';
                                }
                            }

                            // Páginas intermediárias
                            for ($i = $start; $i <= $end; $i++) {
                                $query['pagina'] = $i;
                                echo '<a href="?' . http_build_query($query) . '" class="px-3 py-1 rounded-md text-sm font-medium ' . ($i == $pagina ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white') . '">' . $i . '</a>';
                            }

                            // Última página (só se não estiver no intervalo)
                            if ($end < $total_paginas) {
                                if ($end < $total_paginas - 1) {
                                    echo '<span class="px-2">...</span>';
                                }
                                $query['pagina'] = $total_paginas;
                                echo '<a href="?' . http_build_query($query) . '" class="px-3 py-1 rounded-md text-sm font-medium bg-white dark:bg-gray-700 text-gray-800 dark:text-white">' . $total_paginas . '</a>';
                            }
                            ?>
                        </nav>
                    </div>
                <?php endif; ?>


            </main>


        </div>

        <!-- Modal para visualizar números -->
        <div id="numerosModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg p-4 sm:p-6 max-w-2xl w-full mx-2 sm:mx-4 max-h-[80vh] flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium dark:text-white">Números do Pedido</h3>
                    <button onclick="fecharModal('numerosModal')"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1">
                    <div id="numerosContent" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-2 p-2">
                        <!-- Números serão inseridos aqui via JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleFiltros() {
                const secaoFiltros = document.getElementById('secaoFiltros');
                secaoFiltros.classList.toggle('show');
            }

            function verNumeros(numeros) {
                const numerosArray = numeros.split(',');
                const content = document.getElementById('numerosContent');
                content.innerHTML = '';

                numerosArray.forEach(numero => {
                    const div = document.createElement('div');
                    div.className = 'bg-gray-100 dark:bg-gray-700 p-2 text-center rounded dark:text-white text-sm sm:text-base';
                    div.textContent = numero;
                    content.appendChild(div);
                });

                document.getElementById('numerosModal').classList.remove('hidden');
            }

            function fecharModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }

            function aprovarPedido(id) {
                if (confirm('Deseja realmente aprovar este pedido?')) {
                    fetch('aprovar_pedido.php?id=' + id)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Erro ao aprovar pedido');
                            }
                        });
                }
            }

            function excluirPedido(id) {
                if (confirm('Deseja realmente excluir este pedido?')) {
                    fetch('excluir_pedido.php?id=' + id)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Erro ao excluir pedido');
                            }
                        });
                }
            }
        </script>
    </body>

    </html>