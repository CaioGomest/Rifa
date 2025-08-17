<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
// Postback: salvar roletas e raspadinhas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habilitar_roleta = isset($_POST['habilitar_roleta']) ? 1 : 0;
    $titulo_roleta = $_POST['titulo_roleta'] ?? null;
    $descricao_roleta = $_POST['descricao_roleta'] ?? null;
    $itens_roleta = $_POST['itens_roleta'] ?? '[]';
    $habilitar_raspadinha = isset($_POST['habilitar_raspadinha']) ? 1 : 0;
    $titulo_raspadinha = $_POST['titulo_raspadinha'] ?? null;
    $descricao_raspadinha = $_POST['descricao_raspadinha'] ?? null;
    $itens_raspadinha = $_POST['itens_raspadinha'] ?? '[]';

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
        null,null,null,null,null,null,null,null,null,null,null,null,
        null,null,
        json_encode($itens_roleta),
        $habilitar_raspadinha,
        $titulo_raspadinha,
        $descricao_raspadinha,
        json_encode($itens_raspadinha)
    );
    if ($resultado === true) { $mensagem_sucesso = 'Configurações salvas com sucesso!'; }
    else { $mensagem_erro = is_string($resultado) ? $resultado : 'Erro ao salvar.'; }
    $campanha = listaCampanhas($conn, $id); $campanha = $campanha[0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><title>Editar Campanha - Roletas e Raspadinhas</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Roletas e Raspadinhas</h1>
                <a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
            </header>
            <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                </div>
                <?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
                <?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
                <form method="POST" class="space-y-4">
                    <div class="p-4 rounded-lg border">
                        <h3 class="text-lg font-semibold mb-4">🎰 Configuração da Roleta</h3>
                        <div class="mb-4">
                            <label for="habilitar_roleta" class="block mb-2 font-medium">Habilitar Roleta</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="habilitar_roleta" name="habilitar_roleta" value="1" <?= (int)$campanha['habilitar_roleta']==1?'checked':'' ?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div id="config_roleta" class="space-y-4" style="display: <?= (int)$campanha['habilitar_roleta']==1 ? 'block' : 'none' ?>">
                            <div>
                                <label for="titulo_roleta" class="block mb-2 font-medium">Título da Roleta</label>
                                <input type="text" id="titulo_roleta" name="titulo_roleta" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" value="<?= htmlspecialchars($campanha['titulo_roleta'] ?? '🎰 Roleta da Sorte') ?>">
                            </div>
                            <div>
                                <label for="descricao_roleta" class="block mb-2 font-medium">Descrição da Roleta</label>
                                <textarea id="descricao_roleta" name="descricao_roleta" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" placeholder="Ex: Gire a roleta e ganhe prêmios incríveis!"><?= htmlspecialchars($campanha['descricao_roleta'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label for="itens_roleta" class="block mb-2 font-medium">Itens da Roleta</label>
                                <div id="itens_roleta_container" class="space-y-3"></div>
                                <button type="button" onclick="adicionarItemRoleta()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">Adicionar Item da Roleta</button>
                                <input type="hidden" name="itens_roleta" id="itens_roleta_input">
                            </div>
                        </div>
                    </div>
                    <div class="p-4 rounded-lg border">
                        <h3 class="text-lg font-semibold mb-4">🎫 Configuração da Raspadinha</h3>
                        <div class="mb-4">
                            <label for="habilitar_raspadinha" class="block mb-2 font-medium">Habilitar Raspadinha</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="habilitar_raspadinha" name="habilitar_raspadinha" value="1" <?= (int)$campanha['habilitar_raspadinha']==1?'checked':'' ?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div id="config_raspadinha" class="space-y-4" style="display: <?= (int)$campanha['habilitar_raspadinha']==1 ? 'block' : 'none' ?>">
                            <div>
                                <label for="titulo_raspadinha" class="block mb-2 font-medium">Título da Raspadinha</label>
                                <input type="text" id="titulo_raspadinha" name="titulo_raspadinha" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" value="<?= htmlspecialchars($campanha['titulo_raspadinha'] ?? '🎫 Raspadinha da Sorte') ?>">
                            </div>
                            <div>
                                <label for="descricao_raspadinha" class="block mb-2 font-medium">Descrição da Raspadinha</label>
                                <textarea id="descricao_raspadinha" name="descricao_raspadinha" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" placeholder="Ex: Raspe e descubra prêmios incríveis!"><?= htmlspecialchars($campanha['descricao_raspadinha'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label for="itens_raspadinha" class="block mb-2 font-medium">Itens da Raspadinha</label>
                                <div id="itens_raspadinha_container" class="space-y-3"></div>
                                <button type="button" onclick="adicionarItemRaspadinha()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">Adicionar Item da Raspadinha</button>
                                <input type="hidden" name="itens_raspadinha" id="itens_raspadinha_input">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                </form>
                </div>
            </div>
        </main>
    </div>
    
    <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl mt-4 mx-6 space-y-6">
        <div class="p-4 rounded-lg border">
            <h3 class="text-lg font-semibold mb-4">🎰 Configuração da Roleta</h3>
            <div class="mb-4">
                <label for="habilitar_roleta" class="block mb-2 font-medium">Habilitar Roleta</label>
                <label class="toggle-switch">
                    <input type="checkbox" id="habilitar_roleta" name="habilitar_roleta" value="1" <?= (int)$campanha['habilitar_roleta']==1?'checked':'' ?>>
                    <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                </label>
            </div>
            <div id="config_roleta" class="space-y-4" style="display: <?= (int)$campanha['habilitar_roleta']==1 ? 'block' : 'none' ?>">
                <div>
                    <label for="titulo_roleta" class="block mb-2 font-medium">Título da Roleta</label>
                    <input type="text" id="titulo_roleta" name="titulo_roleta" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" value="<?= htmlspecialchars($campanha['titulo_roleta'] ?? '🎰 Roleta da Sorte') ?>">
                </div>
                <div>
                    <label for="descricao_roleta" class="block mb-2 font-medium">Descrição da Roleta</label>
                    <textarea id="descricao_roleta" name="descricao_roleta" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" placeholder="Ex: Gire a roleta e ganhe prêmios incríveis!"><?= htmlspecialchars($campanha['descricao_roleta'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="itens_roleta" class="block mb-2 font-medium">Itens da Roleta</label>
                    <div id="itens_roleta_container" class="space-y-3"></div>
                    <button type="button" onclick="adicionarItemRoleta()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">Adicionar Item da Roleta</button>
                </div>
            </div>
        </div>
        <div class="p-4 rounded-lg border">
            <h3 class="text-lg font-semibold mb-4">🎫 Configuração da Raspadinha</h3>
            <div class="mb-4">
                <label for="habilitar_raspadinha" class="block mb-2 font-medium">Habilitar Raspadinha</label>
                <label class="toggle-switch">
                    <input type="checkbox" id="habilitar_raspadinha" name="habilitar_raspadinha" value="1" <?= (int)$campanha['habilitar_raspadinha']==1?'checked':'' ?>>
                    <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                </label>
            </div>
            <div id="config_raspadinha" class="space-y-4" style="display: <?= (int)$campanha['habilitar_raspadinha']==1 ? 'block' : 'none' ?>">
                <div>
                    <label for="titulo_raspadinha" class="block mb-2 font-medium">Título da Raspadinha</label>
                    <input type="text" id="titulo_raspadinha" name="titulo_raspadinha" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" value="<?= htmlspecialchars($campanha['titulo_raspadinha'] ?? '🎫 Raspadinha da Sorte') ?>">
                </div>
                <div>
                    <label for="descricao_raspadinha" class="block mb-2 font-medium">Descrição da Raspadinha</label>
                    <textarea id="descricao_raspadinha" name="descricao_raspadinha" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" placeholder="Ex: Raspe e descubra prêmios incríveis!"><?= htmlspecialchars($campanha['descricao_raspadinha'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="itens_raspadinha" class="block mb-2 font-medium">Itens da Raspadinha</label>
                    <div id="itens_raspadinha_container" class="space-y-3"></div>
                    <button type="button" onclick="adicionarItemRaspadinha()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">Adicionar Item da Raspadinha</button>
                </div>
            </div>
        </div>
        <button onclick="salvarCampo('roletas_raspadinhas')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
    </div>
    
    <script>
        (function(){
            try {
                const roletas = <?= json_encode(json_decode($campanha['itens_roleta'] ?? '[]', true)) ?>;
                const rasp = <?= json_encode(json_decode($campanha['itens_raspadinha'] ?? '[]', true)) ?>;
                roletas.forEach(i=>adicionarItemRoleta(i));
                rasp.forEach(i=>adicionarItemRaspadinha(i));
                // Preenche valores dos itens já adicionados
                const setVals = (selector, arr) => {
                    const nodes = document.querySelectorAll(selector);
                    nodes.forEach((node, idx) => {
                        const nome = node.querySelector('input[name^="nome_"]');
                        const status = node.querySelector('select[name^="status_"]');
                        if (arr[idx]) { if (nome) nome.value = arr[idx].nome || ''; if (status) status.value = arr[idx].status || 'disponivel'; }
                    });
                };
                setTimeout(()=>{ setVals('#itens_roleta_container > div', roletas); setVals('#itens_raspadinha_container > div', rasp); }, 50);
            } catch(e) { console.warn(e); }
            document.getElementById('habilitar_roleta').addEventListener('change', function(){ document.getElementById('config_roleta').style.display = this.checked ? 'block' : 'none'; });
            document.getElementById('habilitar_raspadinha').addEventListener('change', function(){ document.getElementById('config_raspadinha').style.display = this.checked ? 'block' : 'none'; });
            // Antes de enviar, serializa os itens
            document.querySelector('form').addEventListener('submit', function(){
                const collect = (containerSel) => Array.from(document.querySelectorAll(containerSel + ' > div')).map(div => ({
                    nome: (div.querySelector('input[name^="nome_"]')||{}).value || '',
                    status: (div.querySelector('select[name^="status_"]')||{}).value || 'disponivel'
                }));
                document.getElementById('itens_roleta_input').value = JSON.stringify(collect('#itens_roleta_container'));
                document.getElementById('itens_raspadinha_input').value = JSON.stringify(collect('#itens_raspadinha_container'));
            });
        })();
    </script>
</body>
</html>


