<?php
require("header.php");
require("../functions/functions_afiliados.php");

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['usuario_tipo'] != 1) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: gerenciar_afiliados.php");
    exit;
}

$usuario_id = intval($_GET['id']);

// Buscar dados do afiliado com informações bancárias (igual afiliados.php)
$query = "SELECT 
    u.*,
    ca.*,
    (SELECT COUNT(*) FROM lista_pedidos WHERE afiliado_id = u.usuario_id) as total_pedidos,
    COALESCE((SELECT SUM(valor_total) FROM lista_pedidos WHERE afiliado_id = u.usuario_id), 0) as total_vendas,
    COALESCE((SELECT SUM(valor_total * ca.porcentagem_comissao / 100) FROM lista_pedidos WHERE afiliado_id = u.usuario_id), 0) as total_comissoes
FROM usuarios u
LEFT JOIN configuracoes_afiliados ca ON ca.usuario_id = u.usuario_id
WHERE u.usuario_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$afiliado = $result->fetch_assoc();
if (!$afiliado) {
    header("Location: gerenciar_afiliados.php");
    exit;
}

// Buscar vendas do afiliado igual afiliados.php
$vendas = listaVendasAfiliados($conn, null, null, null, null, null, 1, null, null, null, null, null, $usuario_id);
$total_vendas = $vendas[0]['valor_total'] ?? 0;
$total_comissoes = 0;
$total_comissoes += ($vendas[0]['valor_total'] * ($afiliado['porcentagem_comissao'] / 100));

$comissoes = listaComissao($conn, $usuario_id);

// Buscar pagamentos do afiliado
$pagamentos = listaPagamentos($conn, $usuario_id);

$valor_ja_pago = 0;
foreach ($pagamentos as $pagamento) {
    if ($pagamento['status'] == 'confirmado' || $pagamento['status'] == 'pago') {
        $valor_ja_pago += floatval($pagamento['valor_pago']);
    }
}

// Calcular totais por status igual afiliados.php
$totais = [
    'pendente' => ['valor' => 0, 'count' => 0],
    'pago' => ['valor' => 0, 'count' => 0],
    'cancelado' => ['valor' => 0, 'count' => 0]
];

foreach ($comissoes as $comissao) {
    switch ($comissao['status']) {
        case 0: // Pendente
            $totais['pendente']['valor'] += floatval($comissao['valor_comissao']);
            $totais['pendente']['count']++;
            break;
        case 1: // Pago
            $totais['pago']['valor'] += floatval($comissao['valor_comissao']);
            $totais['pago']['count']++;
            break;
        case 2: // Cancelado
            $totais['cancelado']['valor'] += floatval($comissao['valor_comissao']);
            $totais['cancelado']['count']++;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Relatório do Afiliado</title>
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-6">
            <header class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Relatório do Afiliado</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        <?= htmlspecialchars($afiliado['usuario_nome'] . ' ' . $afiliado['usuario_sobrenome']) ?>
                    </p>
                </div>
                <button class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500"
                    onclick="window.location.href='gerenciar_afiliados.php'">
                    Voltar
                </button>
            </header>

            <!-- Cards de Resumo -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Total de Pedidos</h3>
                    <p class="text-2xl font-bold"><?= number_format($afiliado['total_pedidos']) ?></p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Total de Vendas</h3>
                    <p class="text-2xl font-bold">R$ <?= number_format($total_vendas, 2, ',', '.') ?></p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Total de Comissões</h3>
                    <p class="text-2xl font-bold">R$ <?= number_format($total_comissoes, 2, ',', '.') ?></p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">% Comissão Atual</h3>
                    <p class="text-2xl font-bold">
                        <?= isset($afiliado['porcentagem_comissao']) ? number_format($afiliado['porcentagem_comissao'], 2) . '%' : '-' ?>
                    </p>
                </div>
            </div>

            <!-- Status das Comissões -->
            <!--status pedidos 0 =pendente, 1=pago, 2=cancelado -->
            

            <!-- Dados Bancários -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-lg font-semibold mb-4">Dados Bancários</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-md font-medium mb-2">PIX</h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            <strong>Tipo:</strong> <?= ucfirst($afiliado['pix_tipo'] ?? 'Não informado') ?><br>
                            <strong>Chave:</strong> <?= $afiliado['pix_chave'] ?? 'Não informada' ?>
                        </p>
                    </div>
                 
                </div>
            </div>

            <!-- Histórico de Comissões -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md overflow-x-auto mb-6">
                <h2 class="text-lg font-semibold mb-4 px-2">Histórico de Comissões</h2>
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="p-2 text-left">Data</th>
                            <th class="p-2 text-left">Pedido</th>
                            <th class="p-2 text-left">Cliente</th>
                            <th class="p-2 text-left">Campanha</th>
                            <th class="p-2 text-right">Valor Venda</th>
                            <th class="p-2 text-right">% Comissão</th>
                            <th class="p-2 text-right">Valor Comissão</th>
                            <th class="p-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comissoes as $comissao): ?>
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="p-2"><?= date('d/m/Y H:i', strtotime($comissao['data_pedido'])) ?></td>
                                <td class="p-2"><?= htmlspecialchars($comissao['token_pedido']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($comissao['cliente_nome']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($comissao['campanha_nome']) ?></td>
                                <td class="p-2 text-right">R$ <?= number_format($comissao['valor_venda'], 2, ',', '.') ?>
                                </td>
                                <td class="p-2 text-right"><?= number_format($comissao['porcentagem'], 2) ?>%</td>
                                <td class="p-2 text-right">R$ <?= number_format($comissao['valor_comissao'], 2, ',', '.') ?>
                                </td>
                                <td class="p-2 text-center">
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($comissao['status']) {
                                        case 0:
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            $status_text = 'Pendente';
                                            break;
                                        case 1:
                                            $status_class = 'bg-green-100 text-green-800';
                                            $status_text = 'Pago';
                                            break;
                                        case 2:
                                            $status_class = 'bg-red-100 text-red-800';
                                            $status_text = 'Cancelado';
                                            break;
                                        default:
                                            $status_class = 'bg-gray-100 text-gray-800';
                                            $status_text = 'Desconhecido';
                                    }
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs <?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($comissoes)): ?>
                            <tr>
                                <td colspan="8" class="p-4 text-center">Nenhuma comissão encontrada.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Histórico de Pagamentos -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md overflow-x-auto mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Histórico de Pagamentos</h2>
                    <button onclick="abrirModalPagamento()"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500">
                        Adicionar Pagamento
                    </button>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="p-2 text-left">Data</th>
                            <th class="p-2 text-right">Valor Pago</th>
                            <th class="p-2 text-center">Status</th>
                            <th class="p-2 text-left">Observações</th>
                            <th class="p-2 text-center">Comprovante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pagamentos = listaPagamentos($conn, $usuario_id);
                        
                        foreach ($pagamentos as $pagamento): ?>
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="p-2"><?= date('d/m/Y H:i', strtotime($pagamento['data_pagamento'])) ?></td>
                                <td class="p-2 text-right">R$ <?= number_format($pagamento['valor_pago'], 2, ',', '.') ?>
                                </td>
                                <td class="p-2 text-center">
                                    <?php
                                    $status_class = '';
                                    switch ($pagamento['status']) {
                                        case 'pendente':
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'confirmado':
                                            $status_class = 'bg-green-100 text-green-800';
                                            break;
                                        case 'rejeitado':
                                            $status_class = 'bg-red-100 text-red-800';
                                            break;
                                    }
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs <?= $status_class ?>">
                                        <?= ucfirst($pagamento['status']) ?>
                                    </span>
                                </td>
                                <td class="p-2"><?= htmlspecialchars($pagamento['observacoes'] ?? '') ?></td>
                                <td class="p-2 text-center">
                                    <?php if ($pagamento['comprovante_path']): ?>
                                        <a href="<?= htmlspecialchars($pagamento['comprovante_path']) ?>" target="_blank"
                                            class="text-purple-600 hover:text-purple-800">
                                            Ver Comprovante
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pagamentos)): ?>
                            <tr>
                                <td colspan="5" class="p-4 text-center">Nenhum pagamento registrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal de Adicionar Pagamento -->
            <div id="modalPagamento" style="z-index: 50;" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden">
                <div class="flex items-center justify-center min-h-screen">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-md">
                        <h3 class="text-lg font-semibold mb-4">Adicionar Pagamento</h3>
                        <form action="processar_pagamento.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="afiliado_id" value="<?= $usuario_id ?>">

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Valor do Pagamento</label>
                                <input type="number" name="valor_pago" step="0.01" required
                                    class="w-full px-3 py-2 border rounded-md dark:bg-gray-700 dark:border-gray-600">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Data do Pagamento</label>
                                <input type="datetime-local" name="data_pagamento" required
                                    class="w-full px-3 py-2 border rounded-md dark:bg-gray-700 dark:border-gray-600">
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Observações</label>
                                <textarea name="observacoes" rows="3"
                                    class="w-full px-3 py-2 border rounded-md dark:bg-gray-700 dark:border-gray-600"></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium mb-2">Comprovante (opcional)</label>
                                <input type="file" name="comprovante" accept="image/*"
                                    class="w-full px-3 py-2 border rounded-md dark:bg-gray-700 dark:border-gray-600">
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="fecharModalPagamento()"
                                    class="px-4 py-2 border rounded-md hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-500">
                                    Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                function abrirModalPagamento() {
                    document.getElementById('modalPagamento').classList.remove('hidden');
                }

                function fecharModalPagamento() {
                    document.getElementById('modalPagamento').classList.add('hidden');
                }
            </script>
        </main>
    </div>
</body>

</html>