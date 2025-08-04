<?php
require("header.php");
require("../functions/functions_afiliados.php");

// Verificar se o usuário é administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['usuario_tipo'] != 1) {
    header("Location: index.php");
    exit;
}

$afiliados = listaAfiliados($conn, NULL, NULL, NULL, NULL, NULL, NULL, 0);
    // Processar alteração de comissão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'], $_POST['porcentagem'])) {
    $usuario_id = intval($_POST['usuario_id']);
    $porcentagem = floatval($_POST['porcentagem']);

    $query = "UPDATE configuracoes_afiliados SET porcentagem = ? WHERE usuario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("di", $porcentagem, $usuario_id);
    
    if ($stmt->execute()) {
        $mensagem = "Comissão atualizada com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao atualizar comissão";
        $tipo_mensagem = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Gerenciar Afiliados</title>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Gerenciar Afiliados</h1>
                <div class="flex items-center space-x-4">
                    <button class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500" 
                            onclick="window.location.href='usuario.php?acao=criar&tipo=2'">
                        Novo Afiliado
                    </button>
                </div>
            </header>

            <?php if (isset($mensagem)): ?>
                <div class="mb-6 p-4 rounded-md <?= $tipo_mensagem === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                <div class="overflow-x-auto">
                    <div class="min-w-[800px]">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="p-2 text-center">Avatar</th>
                                    <th class="p-2 text-center">Afiliado</th>
                                    <th class="p-2 text-center">Código</th>
                                    <th class="p-2 text-center">Email</th>
                                    <th class="p-2 text-center">Total Pedidos</th>
                                    <th class="p-2 text-center">Total Vendas</th>
                                    <th class="p-2 text-center">Total Comissões</th>
                                    <th class="p-2 text-center">% Comissão</th>
                                    <th class="p-2 text-center">Status</th>
                                    <th class="p-2 text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($afiliados)):
                                    foreach ($afiliados as $afiliado): ?>
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="p-2 text-center">
                                        <?php if (!empty($afiliado['usuario_avatar'])): ?>
                                                    <img src="../<?= $afiliado['usuario_avatar'] ?>" 
                                                         alt="Avatar" 
                                                         class="w-8 h-8 rounded-full object-cover">
                                                <?php else: ?>
                                                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                                        <span class="text-sm">👤</span>
                                                    </div>
                                                <?php endif; ?>

                                        </td>
                                        <td class="p-2">
                                            <div class="flex items-center space-x-3">
                                                <span><?= $afiliado['usuario_nome'] . ' ' . $afiliado['usuario_sobrenome'] ?></span>
                                            </div>
                                        </td>
                                        <td class="p-2"><?= $afiliado['codigo_afiliado'] ?></td>
                                        <td class="p-2"><?= $afiliado['usuario_email'] ?></td>
                                        <td class="p-2 text-center"><?= number_format($afiliado['total_pedidos']) ?></td>
                                        <?php
                                            // Calcular total de vendas e comissões igual ao relatorio
                                            $vendas = listaVendasAfiliados($conn, null, null, null, null, null, 1, null, null, null, null, null, $afiliado['usuario_id']);
                                            $total_vendas = $vendas[0]['valor_total'] ?? 0;
                                            $total_comissoes = $total_vendas * ($afiliado['porcentagem_comissao'] / 100);
                                        ?>
                                        <td class="p-2 text-center">R$ <?= number_format($total_vendas, 2, ',', '.') ?></td>
                                        <td class="p-2 text-center">R$ <?= number_format($total_comissoes, 2, ',', '.') ?></td>
                                        <td class="p-2 text-center">
                                            <?= $afiliado['porcentagem_comissao'] ? $afiliado['porcentagem_comissao'] : '0%' ?>
                                        </td>
                                        <td class="p-2 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs <?= $afiliado['usuario_deletado'] == 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $afiliado['usuario_deletado'] == 0 ? 'Ativo' : 'Inativo' ?>
                                            </span>
                                        </td>
                                        <td class="p-2">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="usuario.php?id=<?= $afiliado['usuario_id'] ?>" 
                                                   class="text-purple-600 hover:text-purple-500"
                                                   title="Editar">
                                                    ✏️
                                                </a>
                                                <button onclick="confirmarDelecao(<?= $afiliado['usuario_id'] ?>)"
                                                        class="text-red-600 hover:text-red-500"
                                                        title="Excluir">
                                                    🗑
                                                </button>
                                                <a href="afiliado_relatorio.php?id=<?= $afiliado['usuario_id'] ?>"
                                                   class="text-blue-600 hover:text-blue-500"
                                                   title="Relatório Detalhado">
                                                    📊
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (empty($afiliados)): ?>
                                    <tr>
                                        <td colspan="9" class="p-4 text-center">Nenhum afiliado encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    function confirmarDelecao(id) {
        if (confirm("Tem certeza de que deseja excluir este afiliado?")) {
            fetch('ajax/deletar_usuario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(id)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Afiliado excluído com sucesso!");
                    location.reload();
                } else {
                    alert("Erro ao excluir o afiliado: " + data.message);
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                alert("Ocorreu um erro ao tentar excluir o afiliado.");
            });
        }
    }
    </script>
</body>
</html> 