<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
// Postback: salvar barra de progresso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habilitar = isset($_POST['habilitar_barra_progresso']) ? 1 : 0;
    $manual = isset($_POST['ativar_progresso_manual']) ? 1 : 0;
    $percent = $_POST['porcentagem_barra_progresso'] ?? null;
    $resultado = editaCampanha(
        $conn, $id,
        null,null,null,
        null,null,
        null,
        null,null,null,null,
        null,null,null,null,null,null,null,null,null,null,null,
        null,null,null,
        $habilitar,
        $manual,
        $percent,
        null,null,null,null,null,
        null,null,null,null,null,null,null,null,null,
        null,null,
        null,null,
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
<head><title>Editar Campanha - Barra de Progresso</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Barra de Progresso</h1>
                <a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
            </header>
            <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                </div>
                <?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
                <?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
                <form method="POST" class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">Barra de Progresso</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="habilitar_barra_progresso" class="block mb-2 font-medium">Habilitar Barra de Progresso</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="habilitar_barra_progresso" name="habilitar_barra_progresso" value="1" <?=(int)$campanha['habilitar_barra_progresso']==1?'checked':''?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div id="barra_progresso_ativa" class="<?= (int)$campanha['habilitar_barra_progresso']==1?'':'hidden' ?>">
                            <div>
                                <label for="ativar_progresso_manual" class="block mb-2 font-medium">Ativar Progresso Manual</label>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="ativar_progresso_manual" name="ativar_progresso_manual" value="1" <?=(int)$campanha['ativar_progresso_manual']==1?'checked':''?>>
                                    <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                                </label>
                            </div>
                            <div id="div_progresso_manual" class="<?= (int)$campanha['ativar_progresso_manual']==1 ? '' : 'hidden' ?>">
                                <label for="porcentagem_barra_progresso" class="block mb-2 font-medium">Porcentagem da Barra</label>
                                <div class="flex items-center space-x-2">
                                    <input type="text" id="porcentagem_barra_progresso" name="porcentagem_barra_progresso" class="w-24 bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" value="<?= $campanha['porcentagem_barra_progresso'] ? number_format($campanha['porcentagem_barra_progresso'],1,'.','') : '0.0' ?>" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); if(this.value > 100) this.value = '100.0'; if(parseFloat(this.value) < 0) this.value = '0.0';">
                                    <span class="text-gray-500">%</span>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        document.getElementById('habilitar_barra_progresso').addEventListener('change', function(){
            const on = this.checked; document.getElementById('barra_progresso_ativa').classList.toggle('hidden', !on); if (!on) document.getElementById('div_progresso_manual').classList.add('hidden');
        });
        document.getElementById('ativar_progresso_manual').addEventListener('change', function(){
            document.getElementById('div_progresso_manual').classList.toggle('hidden', !this.checked || !document.getElementById('habilitar_barra_progresso').checked);
        });
    </script>
</body>
</html>


