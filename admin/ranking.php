<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
// Postback: salvar ranking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selecionar = isset($_POST['selecionar_top_ganhadores']) ? 1 : 0;
    $filtro = $_POST['filtro_periodo_top_ganhadores'] ?? '';
    $dataIni = $_POST['data_inicial_personalizada'] ?? '';
    $dataFim = $_POST['data_final_personalizada'] ?? '';
    $payloadFiltro = [ 'filtro' => $filtro, 'valor' => ($filtro === 'personalizado') ? ($dataIni . ' até ' . $dataFim) : '' ];
    $resultado = editaCampanha(
        $conn, $id,
        null, null, null,
        null, null,
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
        $selecionar,
        json_encode($payloadFiltro),
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
        null,
        null,
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
<head><title>Editar Campanha - Top Compradores</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Top Compradores</h1>
                <a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
            </header>
            <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                </div>
                <?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
                <?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
                <form method="POST" class="space-y-4">
                    <h3 class="text-lg font-semibold mb-4">Top Compradores</h3>
                    <?php
                        $filtroSalvo = [];
                        if (!empty($campanha['filtro_periodo_top_ganhadores'])) {
                            $tmp = json_decode($campanha['filtro_periodo_top_ganhadores'], true);
                            if (is_array($tmp)) $filtroSalvo = $tmp;
                        }
                        $filtro = isset($filtroSalvo['filtro']) ? $filtroSalvo['filtro'] : '';
                        $valor = isset($filtroSalvo['valor']) ? $filtroSalvo['valor'] : '';
                        $di = '';$df = '';
                        if ($filtro === 'personalizado' && strpos($valor, ' até ') !== false) {
                            list($di,$df) = explode(' até ', $valor);
                        }
                    ?>
                    <div class="space-y-4">
                        <div>
                            <label for="selecionar_top_ganhadores" class="block mb-2 font-medium">Selecionar Top Ganhadores</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="selecionar_top_ganhadores" name="selecionar_top_ganhadores" value="1" <?php echo (int)$campanha['selecionar_top_ganhadores'] == 1 ? 'checked' : ''; ?>>
                                <div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
                            </label>
                        </div>
                        <div id="div_filtro_periodo_top_ganhadores" class="<?php echo (int)$campanha['selecionar_top_ganhadores'] == 1 ? '' : 'hidden'; ?>">
                            <label class="block text-sm font-medium mb-1">Filtro de Período</label>
                            <select id="filtro_periodo_top_ganhadores" name="filtro_periodo_top_ganhadores" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                <option value="hoje" <?php echo $filtro==='hoje'?'selected':''; ?>>Hoje</option>
                                <option value="ontem" <?php echo $filtro==='ontem'?'selected':''; ?>>Ontem</option>
                                <option value="ultimo_mes" <?php echo $filtro==='ultimo_mes'?'selected':''; ?>>Último Mês</option>
                                <option value="personalizado" <?php echo $filtro==='personalizado'?'selected':''; ?>>Personalizado</option>
                            </select>
                            <div id="div_datas_personalizadas" class="<?php echo $filtro==='personalizado'?'':'hidden'; ?> mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Data Inicial</label>
                                    <input type="date" id="data_inicial_personalizada" name="data_inicial_personalizada" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" value="<?php echo htmlspecialchars($di); ?>">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Data Final</label>
                                    <input type="date" id="data_final_personalizada" name="data_final_personalizada" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" value="<?php echo htmlspecialchars($df); ?>">
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
        document.getElementById('selecionar_top_ganhadores').addEventListener('change', function(){
            const on = this.checked;
            document.getElementById('div_filtro_periodo_top_ganhadores').classList.toggle('hidden', !on);
        });
        document.getElementById('filtro_periodo_top_ganhadores').addEventListener('change', function(){
            document.getElementById('div_datas_personalizadas').classList.toggle('hidden', this.value !== 'personalizado');
        });
    </script>
</body>
</html>



