<?php

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

require("header.php");

$usuarios = listaUsuarios($conn, NULL, NULL, NULL, NULL, NULL, 0);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Usuários</title>
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Usuários</h1>
                <div class="flex items-center space-x-4">
                    <button class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500" onclick="window.location.href='usuario.php?acao=criar'">
                        Cadastrar novo
                    </button>
                </div>
            </header>

            <section>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-md shadow-md">
                    <div class="overflow-x-auto">
                        <div class="min-w-[800px]">
                            <table class="w-full text-left">
                                <thead class="bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                                    <tr>
                                        <th class="p-2">FOTO</th>
                                        <th class="p-2">NOME</th>
                                        <th class="p-2">TIPO</th>
                                        <th class="p-2">DATA</th>
                                        <th class="p-2">EMAIL</th>
                                        <th class="p-2">AÇÃO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($usuarios)): ?>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr class="border-b border-gray-300 dark:border-gray-700">
                                                <td class="p-2">
                                                <?php if (!empty($usuario['usuario_avatar'])): ?>
                                                    <img src="../<?= $usuario['usuario_avatar'] ?>" 
                                                         alt="Avatar" 
                                                         class="w-8 h-8 rounded-full object-cover">
                                                <?php else: ?>
                                                    <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                                        <span class="text-sm">👤</span>
                                                    </div>
                                                <?php endif; ?>
                                                </td>
                                                <td class="p-2"><?= htmlspecialchars($usuario['usuario_nome'] . ' ' . $usuario['usuario_sobrenome']) ?></td>
                                                <td class="p-2"><?= $usuario['usuario_tipo'] == 1 ? 'Administrador' : 'Afiliado' ?></td>
                                                <td class="p-2"><?= date('d-m-Y H:i', strtotime($usuario['usuario_data_criacao'])) ?></td>
                                                <td class="p-2"><?= htmlspecialchars($usuario['usuario_email']) ?></td>
                                                <td class="p-2 flex space-x-2">
                                                    <button class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white"
                                                            onclick="window.location.href='usuario.php?id=<?= $usuario['usuario_id'] ?>'">
                                                        ✏️
                                                    </button>
                                                    <button class="text-red-600 dark:text-red-400 hover:text-red-500"
                                                            onclick="confirmarDelecao(<?= $usuario['usuario_id'] ?>)">
                                                        🗑
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center p-4">Nenhum usuário encontrado.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
    function confirmarDelecao(id) {
        if (confirm("Tem certeza de que deseja excluir este usuário?")) {
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
                    alert("Usuário excluído com sucesso!");
                    location.reload();
                } else {
                    alert("Erro ao excluir o usuário: " + data.message);
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                alert("Ocorreu um erro ao tentar excluir o usuário.");
            });
        }
    }
    </script>
</body>
</html>