<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
// Postback: salvar ganhadores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vencedor = $_POST['vencedor_sorteio'] ?? null;
    $numero = $_POST['numero_sorteio'] ?? null;
    $data = $_POST['data_sorteio'] ?? null;
    $resultado = editaCampanha(
        $conn, $id,
        null,null,null,
        null,null,
        null,
        $data,
        null,
        null,null,null,
        null,null,null,null,null,null,null,null,null,null,null,
        null,null,null,null,null,null,
        $numero,
        null,null,
        $vencedor,
        null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,
        null,
        null
    );
    if ($resultado === true) { $mensagem_sucesso = 'Informações salvas com sucesso!'; }
    else { $mensagem_erro = is_string($resultado) ? $resultado : 'Erro ao salvar.'; }
    $campanha = listaCampanhas($conn, $id); $campanha = $campanha[0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><title>Editar Campanha - Ganhadores</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Ganhadores</h1>
                <a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
            </header>
            <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                </div>
                <?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
                <?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
                <form method="POST" class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">Ganhadores</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Vencedor do Sorteio</label>
                            <input type="text" id="vencedor_sorteio" name="vencedor_sorteio" value="<?=htmlspecialchars($campanha['vencedor_sorteio'] ?? '')?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Bilhete do Sorteio</label>
                            <input type="text" id="numero_sorteio" name="numero_sorteio" value="<?=htmlspecialchars($campanha['numero_sorteio'] ?? '')?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Data do Sorteio</label>
                            <input type="datetime-local" id="data_sorteio" name="data_sorteio" value="<?=htmlspecialchars($campanha['data_sorteio'] ?? '')?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <button type="submit" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
</body>
</html>


