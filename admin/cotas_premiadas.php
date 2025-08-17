<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mostrar = isset($_POST['mostrar_cotas_premiadas']) ? 1 : 0;
    $status = $_POST['status_cotas_premiadas'] ?? null;
    $quantidade = $_POST['quantidade_cotas_premiadas'] ?? null;
    $premio = $_POST['premio_cotas_premiadas'] ?? null;
    $descricao = $_POST['descricao_cotas_premiadas'] ?? null;
    $resultado = editaCampanha(
        $conn, $id,
        null,null,null,
        null,null,
        null,
        null,
        null,
        null,null,
        null,null,null,null,null,null,null,null,null,null,null,
        null,null,null,null,null,null,
        null,null,null,null,null,
        null,
        null,
        null,
        null,
        $quantidade,
        $premio,
        $descricao,
        null,
        null,
        $mostrar,
        $status,
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
<head><title>Editar Campanha - Cotas Premiadas</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Cotas Premiadas</h1>
                <a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
            </header>
            <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                </div>
                <?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
                <?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
                <form method="POST" class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">Cotas Premiadas</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="mostrar_cotas_premiadas" class="block mb-2 font-medium">Mostrar Cotas Premiadas no Site</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="mostrar_cotas_premiadas" name="mostrar_cotas_premiadas" value="1" <?= (int)$campanha['mostrar_cotas_premiadas']==1?'checked':'' ?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Status das Cotas Premiadas</label>
                            <select id="status_cotas_premiadas" name="status_cotas_premiadas" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                <option value="bloqueado" <?= $campanha['status_cotas_premiadas']==='bloqueado'?'selected':'' ?>>Bloqueado</option>
                                <option value="disponivel" <?= $campanha['status_cotas_premiadas']==='disponivel'?'selected':'' ?>>Disponível</option>
                                <option value="imediato" <?= $campanha['status_cotas_premiadas']==='imediato'?'selected':'' ?>>Imediato</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Quantidade de Cotas Premiadas</label>
                            <input type="number" id="quantidade_cotas_premiadas" name="quantidade_cotas_premiadas" min="1" value="<?= (int)($campanha['quantidade_cotas_premiadas'] ?? 0) ?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                            <p class="text-sm text-gray-500 mt-1">Quantidade de cotas que serão selecionadas automaticamente</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Prêmio para este Grupo</label>
                            <input type="text" id="premio_cotas_premiadas" name="premio_cotas_premiadas" value="<?= htmlspecialchars($campanha['premio_cotas_premiadas'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Ex: R$ 500 ou AUDI A3">
                            <p class="text-sm text-gray-500 mt-1">Prêmio que será associado às cotas deste grupo</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Descrição das Cotas Premiadas</label>
                            <textarea id="descricao_cotas_premiadas" name="descricao_cotas_premiadas" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" rows="4"><?= htmlspecialchars($campanha['descricao_cotas_premiadas'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Grupos de Cotas Premiadas</label>
                            <div id="grupos_premios" class="mt-3 space-y-2"><?php echo gerarHTMLGruposPremiosPHP($campanha); ?></div>
                            <p class="text-sm text-gray-500 mt-1">Total de cotas premiadas: <?= $campanha['cotas_premiadas'] ? count(explode(',', $campanha['cotas_premiadas'])) : 0 ?></p>
                            <p class="text-sm text-gray-500">Cotas selecionadas automaticamente pelo sistema</p>
                        </div>
                        <button onclick="gerarCotasPremiadas()" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 mb-2">Gerar Cotas Premiadas</button>
                        <button onclick="limparCotasPremiadas()" class="w-full bg-red-600 text-white p-2 rounded hover:bg-red-700 mb-2">Limpar Todas as Cotas</button>
                        <button onclick="corrigirGruposPremiados()" class="w-full bg-yellow-600 text-white p-2 rounded hover:bg-yellow-700 mb-2">Corrigir Grupos Corrompidos</button>
                        <button type="submit" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl mt-4 mx-6">
        <h3 class="text-lg font-semibold mb-4">Cotas Premiadas</h3>
        <div class="space-y-4">
            <div>
                <label for="mostrar_cotas_premiadas" class="block mb-2 font-medium">Mostrar Cotas Premiadas no Site</label>
                <label class="toggle-switch">
                    <input type="checkbox" id="mostrar_cotas_premiadas" name="mostrar_cotas_premiadas" value="1" <?= (int)$campanha['mostrar_cotas_premiadas']==1?'checked':'' ?>>
                    <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status das Cotas Premiadas</label>
                <select id="status_cotas_premiadas" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    <option value="bloqueado" <?= $campanha['status_cotas_premiadas']==='bloqueado'?'selected':'' ?>>Bloqueado</option>
                    <option value="disponivel" <?= $campanha['status_cotas_premiadas']==='disponivel'?'selected':'' ?>>Disponível</option>
                    <option value="imediato" <?= $campanha['status_cotas_premiadas']==='imediato'?'selected':'' ?>>Imediato</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Quantidade de Cotas Premiadas</label>
                <input type="number" id="quantidade_cotas_premiadas" min="1" value="<?= (int)($campanha['quantidade_cotas_premiadas'] ?? 0) ?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                <p class="text-sm text-gray-500 mt-1">Quantidade de cotas que serão selecionadas automaticamente</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Prêmio para este Grupo</label>
                <input type="text" id="premio_cotas_premiadas" value="<?= htmlspecialchars($campanha['premio_cotas_premiadas'] ?? '') ?>" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Ex: R$ 500 ou AUDI A3">
                <p class="text-sm text-gray-500 mt-1">Prêmio que será associado às cotas deste grupo</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Descrição das Cotas Premiadas</label>
                <textarea id="descricao_cotas_premiadas" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" rows="4"><?= htmlspecialchars($campanha['descricao_cotas_premiadas'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Grupos de Cotas Premiadas</label>
                <div id="grupos_premios" class="mt-3 space-y-2"><?php echo gerarHTMLGruposPremiosPHP($campanha); ?></div>
                <p class="text-sm text-gray-500 mt-1">Total de cotas premiadas: <?= $campanha['cotas_premiadas'] ? count(explode(',', $campanha['cotas_premiadas'])) : 0 ?></p>
                <p class="text-sm text-gray-500">Cotas selecionadas automaticamente pelo sistema</p>
            </div>
            <button onclick="gerarCotasPremiadas()" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 mb-2">Gerar Cotas Premiadas</button>
            <button onclick="limparCotasPremiadas()" class="w-full bg-red-600 text-white p-2 rounded hover:bg-red-700 mb-2">Limpar Todas as Cotas</button>
            <button onclick="corrigirGruposPremiados()" class="w-full bg-yellow-600 text-white p-2 rounded hover:bg-yellow-700 mb-2">Corrigir Grupos Corrompidos</button>
            <button onclick="salvarCampo('cotas_premiadas')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
        </div>
    </div>
    
</body>
</html>

<?php
// helper para server-side render dos grupos (mesmo visual do JS)
function gerarHTMLGruposPremiosPHP($campanha) {
    $premiosJson = $campanha['premio_cotas_premiadas'] ?? '';
    if (!$premiosJson) return '';
    $out = '';
    $grupos = json_decode($premiosJson, true);
    if (!is_array($grupos)) return '';
    $largura = strlen((string)($campanha['quantidade_numeros'] ?? 1));
    foreach ($grupos as $i => $grupo) {
        if (!isset($grupo['cotas']) || !is_array($grupo['cotas']) || !isset($grupo['premio'])) continue;
        $out .= '<div class="bg-blue-50 dark:bg-blue-900 p-3 rounded border">';
        $out .= '<div class="font-medium text-blue-800 dark:text-blue-200 mb-2">Grupo ' . ($i+1) . ': ' . htmlspecialchars($grupo['premio']) . '</div>';
        $out .= '<div class="flex flex-wrap gap-1">';
        foreach ($grupo['cotas'] as $cota) {
            $pad = str_pad((int)$cota, $largura, '0', STR_PAD_LEFT);
            $out .= '<span class="inline-block bg-blue-600 text-white px-2 py-1 rounded text-sm">' . $pad . '</span>';
        }
        $out .= '</div>';
        $out .= '<div class="text-xs text-blue-600 dark:text-blue-400 mt-1">' . count($grupo['cotas']) . ' cotas neste grupo</div>';
        $out .= '</div>';
    }
    return $out;
}
?>
</body>
</html>


