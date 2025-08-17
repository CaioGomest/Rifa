<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
// Postback: salvar cotas em dobro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habilitar = isset($_POST['habilitar_cotas_em_dobro']) ? 1 : 0;
    $titulo = $_POST['titulo_cotas_dobro'] ?? null;
    $subtitulo = $_POST['subtitulo_cotas_dobro'] ?? null;
    $resultado = editaCampanha(
        $conn, $id,
        null,null,null,
        null,null,
        null,
        null,
        null,null,null,
        null,null,null,null,null,null,null,null,null,null,null,
        null,null,null,null,null,null,
        null,null,null,null,null,
        $habilitar,
        null,
        null,
        null,null,null,null,null,null,null,null,null,
        $titulo,
        $subtitulo,
        null,
        null
    );
    if ($resultado === true) { $mensagem_sucesso = 'Configurações salvas com sucesso!'; }
    else { $mensagem_erro = is_string($resultado) ? $resultado : 'Erro ao salvar.'; }
    $campanha = listaCampanhas($conn, $id); $campanha = $campanha[0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><title>Editar Campanha - Cotas em Dobro</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Cotas em Dobro</h1>
                <a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
            </header>
            <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl ">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                </div>
                <?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
                <?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
                <form method="POST" class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">Cotas em Dobro</h3>
                    <div class="space-y-4">
                        <div class="mb-4">
                            <label for="habilitar_cotas_em_dobro" class="block mb-2 font-medium">Cotas em Dobro</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="habilitar_cotas_em_dobro" name="habilitar_cotas_em_dobro" value="1" <?= (int)$campanha['habilitar_cotas_em_dobro']==1?'checked':'' ?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div id="campos_cotas_dobro" class="space-y-4" style="display: <?= (int)$campanha['habilitar_cotas_em_dobro']==1?'block':'none' ?>">
                            <div>
                                <label class="block text-sm font-medium mb-1">Título do Alerta</label>
                                <input type="text" id="titulo_cotas_dobro" name="titulo_cotas_dobro" value="<?= htmlspecialchars($campanha['titulo_cotas_dobro'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Ex: COTAS EM DOBRO ATIVADAS!">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Subtítulo do Alerta</label>
                                <input type="text" id="subtitulo_cotas_dobro" name="subtitulo_cotas_dobro" value="<?= htmlspecialchars($campanha['subtitulo_cotas_dobro'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Ex: Aproveite! Todas as cotas estão valendo em dobro.">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl max-w-3xl mt-4 mx-6">
        <h3 class="text-lg font-semibold mb-4">Cotas em Dobro</h3>
        <div class="space-y-4">
            <div class="mb-4">
                <label for="habilitar_cotas_em_dobro" class="block mb-2 font-medium">Cotas em Dobro</label>
                <label class="toggle-switch">
                    <input type="checkbox" id="habilitar_cotas_em_dobro" name="habilitar_cotas_em_dobro" value="1" <?= (int)$campanha['habilitar_cotas_em_dobro']==1?'checked':'' ?>>
                    <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                </label>
            </div>
            <div id="campos_cotas_dobro" class="space-y-4" style="display: <?= (int)$campanha['habilitar_cotas_em_dobro']==1?'block':'none' ?>">
                <div>
                    <label class="block text-sm font-medium mb-1">Título do Alerta</label>
                    <input type="text" id="titulo_cotas_dobro" value="<?= htmlspecialchars($campanha['titulo_cotas_dobro'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Ex: COTAS EM DOBRO ATIVADAS!">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Subtítulo do Alerta</label>
                    <input type="text" id="subtitulo_cotas_dobro" value="<?= htmlspecialchars($campanha['subtitulo_cotas_dobro'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Ex: Aproveite! Todas as cotas estão valendo em dobro.">
                </div>
            </div>
            <button onclick="salvarCampo('cotas_dobro')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
        </div>
    </div>
    
    <script>
        document.getElementById('habilitar_cotas_em_dobro').addEventListener('change', function(){
            document.getElementById('campos_cotas_dobro').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>



