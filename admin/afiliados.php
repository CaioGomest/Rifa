<?php
require("header.php");
require("../functions/functions_afiliados.php");


// Verificar se o usuário está logado e é um afiliado
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['usuario_tipo'] != 2) {
    $_SESSION['erro'] = "Acesso não autorizado.";
    header("Location: index.php");
    exit;
}
$usuario_id = intval($_SESSION['usuario']['usuario_id']);

// Tratamento de erros para a consulta principal
try {
    // Buscar dados do afiliado com informações bancárias
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
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $usuario_id);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Erro ao obter resultado: " . $stmt->error);
    }

    $afiliado = $result->fetch_assoc();
    if (!$afiliado) {
        throw new Exception("Afiliado não encontrado.");
    }

} catch (Exception $e) {
    $_SESSION['erro'] = "Erro ao carregar dados do afiliado: " . $e->getMessage();
    header("Location: logout.php");
    exit;
}

// Buscar comissões do afiliado com tratamento de erros
try {
    $comissoes = listaComissao($conn, $usuario_id);
    if ($comissoes === false) {
        throw new Exception("Erro ao buscar comissões");
    }

    // Calcular totais por status
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
} catch (Exception $e) {
    $comissoes = [];
    $totais = [
        'pendente' => ['valor' => 0, 'count' => 0],
        'pago' => ['valor' => 0, 'count' => 0],
        'cancelado' => ['valor' => 0, 'count' => 0]
    ];
    $_SESSION['erro'] = "Erro ao carregar comissões: " . $e->getMessage();
}

// Buscar pagamentos com tratamento de erros
try {
    $sql_pagamentos = "SELECT * FROM pagamentos_afiliados WHERE afiliado_id = ? ORDER BY data_pagamento DESC";
    $stmt = $conn->prepare($sql_pagamentos);
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta de pagamentos: " . $conn->error);
    }

    $stmt->bind_param("i", $usuario_id);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar consulta de pagamentos: " . $stmt->error);
    }

    $result_pagamentos = $stmt->get_result();
    if (!$result_pagamentos) {
        throw new Exception("Erro ao obter resultado dos pagamentos: " . $stmt->error);
    }

    $pagamentos = $result_pagamentos->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $pagamentos = [];
    $_SESSION['erro'] = "Erro ao carregar pagamentos: " . $e->getMessage();
}

// Função auxiliar para formatação segura de números
function formatarNumero($valor)
{
    return number_format(floatval($valor), 2, ',', '.');
}

// Função auxiliar para escapar strings
function escaparString($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Função para sanitizar strings de entrada
function sanitizarString($str)
{
    if (empty($str)) {
        return null;
    }
    // Remove caracteres especiais e HTML
    $str = strip_tags($str);
    $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    return trim($str);
}

// Função para validar data
function validarData($data)
{
    if (empty($data)) {
        return null;
    }
    $data = sanitizarString($data);
    // Verifica se a data está no formato correto (YYYY-MM-DD)
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $data)) {
        return $data;
    }
    return null;
}

// Sanitização dos parâmetros de filtro
$filtro_data_inicio = validarData($_GET['data_inicio'] ?? null);
$filtro_data_fim = validarData($_GET['data_fim'] ?? null);
$filtro_status = filter_var($_GET['status'] ?? null, FILTER_VALIDATE_INT);

// Buscar vendas do afiliado
$vendas =    listaVendasAfiliados($conn, null, null, null, $filtro_data_inicio, $filtro_data_fim,1,null,null,null,null,null,$_SESSION['usuario']['usuario_id'] );



// Calcular totais
$total_vendas = $vendas[0]['valor_total'] ?? 0;

$total_comissoes = 0;
$total_comissoes += ($vendas[0]['valor_total']  * ($afiliado['porcentagem_comissao'] / 100));

// Função para obter o status formatado
function getStatusClass($status)
{
    switch ($status) {
        case 'pendente':
            return 'bg-yellow-100 text-yellow-800';
        case 'confirmado':
            return 'bg-green-100 text-green-800';
        case 'rejeitado':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Dashboard do Afiliado</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-6 overflow-y-auto">
            <?php if (isset($_SESSION['erro'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?= escaparString($_SESSION['erro']) ?></span>
                    <?php unset($_SESSION['erro']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['sucesso'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6"
                    role="alert">
                    <span class="block sm:inline"><?= escaparString($_SESSION['sucesso']) ?></span>
                    <?php unset($_SESSION['sucesso']); ?>
                </div>
            <?php endif; ?>

            <header class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold">Dashboard do Afiliado</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        <?= escaparString($afiliado['usuario_nome'] . ' ' . $afiliado['usuario_sobrenome']) ?>
                    </p>
                </div>
                <button class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500"
                    onclick="window.location.href='configuracoes_afiliado.php'">
                    Configurações
                </button>
            </header>

            <!-- Cards de Resumo -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Total de Pedidos</h3>
                    <p class="text-2xl font-bold"><?= number_format($afiliado['total_pedidos']) ?></p>
                </div>
                <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Total de Vendas</h3>
                    <p class="text-2xl font-bold">R$ <?= number_format($total_vendas, 2, ',', '.') ?></p>
                </div>
                <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">Total de Comissões</h3>
                    <p class="text-2xl font-bold">R$ <?= number_format($total_comissoes, 2, ',', '.') ?></p>
                </div>
                <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2">% Comissão Atual</h3>
                    <p class="text-2xl font-bold"><?= number_format($afiliado['porcentagem_comissao'], 2) ?>%</p>
                </div>
            </div>
            <!-- Status das Comissões -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- <div class="bg-blue-50 dark:bg-blue-900 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2 text-blue-800 dark:text-blue-200">Valor Já Pago</h3>
                    <div class="flex justify-between items-center">
                        <p class="text-2xl font-bold text-blue-800 dark:text-blue-200">
                            R$ <?= formatarNumero($totais['pago']['valor']) ?>
                        </p>
                        <span class="text-sm text-blue-600 dark:text-blue-400">
                            <?= $totais['pago']['count'] ?> pedidos pagos
                        </span>
                    </div>
                </div> -->

                <!-- <div class="bg-green-50 dark:bg-green-900 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2 text-green-800 dark:text-green-200">Comissões Pagas</h3>
                    <div class="flex justify-between items-center">
                        <p class="text-2xl font-bold text-green-800 dark:text-green-200">
                            R$ <?= number_format($totais['pago']['valor'], 2, ',', '.') ?>
                        </p>
                        <span class="text-sm text-green-600 dark:text-green-400">
                            <?= $totais['pago']['count'] ?> pedidos
                        </span>
                    </div>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2 text-yellow-800 dark:text-yellow-200">Comissões Pendentes</h3>
                    <div class="flex justify-between items-center">
                        <p class="text-2xl font-bold text-yellow-800 dark:text-yellow-200">
                            R$ <?= number_format($totais['pendente']['valor'], 2, ',', '.') ?>
                        </p>
                        <span class="text-sm text-yellow-600 dark:text-yellow-400">
                            <?= $totais['pendente']['count'] ?> pedidos
                        </span>
                    </div>
                </div>

                <div class="bg-red-50 dark:bg-red-900 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-2 text-red-800 dark:text-red-200">Comissões Canceladas</h3>
                    <div class="flex justify-between items-center">
                        <p class="text-2xl font-bold text-red-800 dark:text-red-200">
                            R$ <?= number_format($totais['cancelado']['valor'], 2, ',', '.') ?>
                        </p>
                        <span class="text-sm text-red-600 dark:text-red-400">
                            <?= $totais['cancelado']['count'] ?> pedidos
                        </span>
                    </div>
                </div> -->
            </div>

            <!-- Filtros -->
            <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow-md mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Data Início</label>
                        <input type="date" name="data_inicio" value="<?= escaparString($filtro_data_inicio) ?>"
                            class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Data Fim</label>
                        <input type="date" name="data_fim" value="<?= escaparString($filtro_data_fim) ?>"
                            class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select name="status"
                            class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600">
                            <option value="">Todos</option>
                            <option value="1" <?= ($filtro_status === 1) ? 'selected' : ''; ?>>Pendente</option>
                            <option value="2" <?= ($filtro_status === 2) ? 'selected' : ''; ?>>Pago</option>
                            <option value="3" <?= ($filtro_status === 3) ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500">
                            Filtrar
                        </button>
                        <?php if (!empty($_GET)): ?>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>"
                                class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-400">
                                Limpar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Histórico de Comissões -->
            <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow-md overflow-x-auto mb-6">
                <h2 class="text-lg font-semibold mb-4 px-2">Histórico de Pedidos</h2>
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-[#3F3F46]">
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
                                <td class="p-2"><?= escaparString($comissao['token_pedido']) ?></td>
                                <td class="p-2"><?= escaparString($comissao['cliente_nome']) ?></td>
                                <td class="p-2"><?= escaparString($comissao['campanha_nome']) ?></td>
                                <td class="p-2 text-right">R$ <?= formatarNumero($comissao['valor_venda']) ?></td>
                                <td class="p-2 text-right"><?= formatarNumero($comissao['porcentagem']) ?>%</td>
                                <td class="p-2 text-right">R$ <?= formatarNumero($comissao['valor_comissao']) ?></td>
                                <td class="p-2 text-center">
                                    <?php
                                    $status_classes = classeStatusPedido($comissao);
                                    $status_textos = textoStatusPedido($comissao);
                                    ?>
                                    <span class="<?php echo $status_classes; ?> text-w  hite px-2 py-1 rounded text-sm text-white">
                                        <?php echo $status_textos; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($comissoes)): ?>
                            <tr>
                                <td colspan="8" class="p-4 text-center">Nenhum Pedido encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Histórico de Pagamentos -->
            <!-- <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow-md overflow-x-auto mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Histórico de Pagamentos</h2>
                    <?php if ($afiliado['total_comissoes'] > 0): ?>
                        <button onclick="abrirModalPagamento()"
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500">
                            Solicitar Pagamento
                        </button>
                    <?php endif; ?>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-[#3F3F46]">
                        <tr>
                            <th class="p-2 text-left">Data</th>
                            <th class="p-2 text-right">Valor Pago</th>
                            <th class="p-2 text-center">Status</th>
                            <th class="p-2 text-left">Observações</th>
                            <th class="p-2 text-center">Comprovante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pagamentos)): ?>
                            <?php foreach ($pagamentos as $pagamento): ?>
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="p-2"><?= date('d/m/Y H:i', strtotime($pagamento['data_pagamento'])) ?></td>
                                    <td class="p-2 text-right">R$ <?= formatarNumero($pagamento['valor_pago']) ?></td>
                                    <td class="p-2 text-center">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs <?= getStatusClass($pagamento['status']) ?>">
                                            <?= ucfirst(escaparString($pagamento['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="p-2"><?= escaparString($pagamento['observacoes']) ?></td>
                                    <td class="p-2 text-center">
                                        <?php if (!empty($pagamento['comprovante_path'])): ?>
                                            <a href="<?= escaparString($pagamento['comprovante_path']) ?>" target="_blank"
                                                class="text-purple-600 hover:text-purple-800">
                                                Ver Comprovante
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-4 text-center">Nenhum pagamento registrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div> -->
        </main>
    </div>

    <!-- Modal de Pagamento -->
    <script>
        function abrirModalPagamento() {
            // Implementar lógica do modal de pagamento
            alert('Funcionalidade em desenvolvimento');
        }
    </script>
</body>

</html>