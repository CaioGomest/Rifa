<?php
require_once("header.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? null;
    $subtitulo = $_POST['subtitulo'] ?? null;
    $descricao = $_POST['descricao'] ?? null;
    $preco = $_POST['preco'] ?? null;
    $tipo_sorteio = $_POST['tipo_sorteio'] ?? null;
    $layout = $_POST['layout'] ?? null;
    $quantidade_numeros = $_POST['quantidade_numeros'] ?? null;
    $compra_minima = $_POST['compra_minima'] ?? null;
    $compra_maxima = $_POST['compra_maxima'] ?? null;
    $status = $_POST['status'] ?? null;
    $campanha_privada = isset($_POST['campanha_privada']) ? 1 : 0;
    $campanha_destaque = isset($_POST['campanha_destaque']) ? 1 : 0;
    $habilitar_ranking = isset($_POST['habilitar_ranking']) ? 1 : 0;
    $quantidade_ranking = $_POST['quantidade_ranking'] ?? null;

    $resultado = editaCampanha(
        $conn,
        $id,
        $nome,
        $subtitulo,
        $descricao,
        null,
        null,
        $status,
        null,
        $preco,
        $quantidade_numeros,
        $compra_minima,
        $compra_maxima,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        $habilitar_ranking,
        null,
        null,
        $quantidade_ranking,
        null,
        null,
        null,
        null,
        null,
        $tipo_sorteio,
        $campanha_destaque,
        $campanha_privada,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        $layout,
        null,
        null,
        null,
        null,
        null,
        null,
        null
    );
    if ($resultado === true) { $mensagem_sucesso = 'Dados salvos com sucesso!'; }
    else { $mensagem_erro = is_string($resultado) ? $resultado : 'Erro ao salvar os dados.'; }
    // Recarrega os dados atualizados para refletir imediatamente no formulário
    $campanha = listaCampanhas($conn, $id);
    if ($campanha && is_array($campanha)) { $campanha = $campanha[0]; }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Editar Campanha - Dados Gerais</title>
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Dados Gerais</h1>
                <a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
            </header>
            <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                </div>
                <?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
                <?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
                <form method="POST" class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">Dados Gerais</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nome</label>
                            <input type="text" id="nome" name="nome" value="<?=htmlspecialchars($campanha['nome'])?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Subtítulo</label>
                            <input type="text" id="subtitulo" name="subtitulo" value="<?=htmlspecialchars($campanha['subtitulo'])?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Descrição</label>
                            <textarea id="descricao" name="descricao" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"><?=htmlspecialchars($campanha['descricao'])?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Preço</label>
                            <div class="relative">
                                <span class="absolute left-2 top-2 text-gray-500">$</span>
                                <input type="text" id="preco" name="preco" value="<?=htmlspecialchars($campanha['preco'])?>" class="w-full p-2 pl-8 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tipo de Sorteio</label>
                            <select id="tipo_sorteio" name="tipo_sorteio" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                <option value="1" <?=$campanha['tipo_sorteio']==1?'selected':''?>>Sorteio</option>
                                <option value="2" <?=$campanha['tipo_sorteio']==2?'selected':''?>>Rifa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Layout</label>
                            <select id="layout" name="layout" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                <option value="0" <?=$campanha['layout']==0?'selected':''?>>Rincon</option>
                                <option value="1" <?=$campanha['layout']==1?'selected':''?>>Buzeira</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Quantidade de Números</label>
                            <input type="number" id="quantidade_numeros" name="quantidade_numeros" value="<?=htmlspecialchars($campanha['quantidade_numeros'])?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Compra Mínima</label>
                            <input type="number" id="compra_minima" name="compra_minima" value="<?=htmlspecialchars($campanha['compra_minima'])?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Compra Máxima</label>
                            <input type="number" id="compra_maxima" name="compra_maxima" value="<?=htmlspecialchars($campanha['compra_maxima'])?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select id="status" name="status" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                <option value="0" <?=$campanha['status']==0?'selected':''?>>Inativo</option>
                                <option value="1" <?=$campanha['status']==1?'selected':''?>>Ativo</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="campanha_privada" class="block mb-2 font-medium">Campanha Privada</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="campanha_privada" name="campanha_privada" value="1" <?=$campanha['campanha_privada']==1?'checked':''?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div class="mb-6">
                            <label for="campanha_destaque" class="block mb-2 font-medium">Campanha em Destaque</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="campanha_destaque" name="campanha_destaque" value="1" <?=$campanha['campanha_destaque']==1?'checked':''?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div class="mb-6">
                            <label for="habilitar_ranking" class="block mb-2 font-medium">Habilitar Ranking</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="habilitar_ranking" name="habilitar_ranking" value="1" <?=$campanha['habilitar_ranking']==1?'checked':''?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div id="div_quantidade_ranking">
                            <label class="block text-sm font-medium mb-1">Quantidade no Ranking (1 a 10)</label>
                            <input type="number" id="quantidade_ranking" name="quantidade_ranking" value="<?=htmlspecialchars($campanha['quantidade_ranking'])?>" min="1" max="10" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <button type="submit" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    
</body>
</html>


