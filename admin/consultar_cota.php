<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><title>Editar Campanha - Consultar Cota</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Consultar Cota</h1>
                <a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
            </header>
            <div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                </div>
                <div id="modalCampoConteudo" class="space-y-4">
                    <div class="bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100 p-4 rounded-lg mb-6">
                        <div class="flex flex-col items-center">
                            <div class="w-full max-w-md mb-4">
                                <img src="../<?= $campanha['caminho_imagem']?>" alt="<?= htmlspecialchars($campanha['nome']) ?>" class="w-full h-auto rounded-lg shadow-lg">
                            </div>
                            <h2 class="text-2xl font-bold mb-2">🎲 <?= htmlspecialchars($campanha['nome']) ?></h2>
                            <p class="text-gray-600 dark:text-gray-300 text-center"><?= htmlspecialchars($campanha['subtitulo'] ?? '') ?></p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Número da Cota</label>
                        <div class="flex space-x-2">
                            <input type="number" id="numero_cota" class="flex-1 p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Digite o número da cota">
                            <button id="btnConsultarCota" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500">Consultar</button>
                        </div>
                    </div>
                    <div id="resultado_consulta" class="mt-4 p-4 rounded-md hidden"></div>
                </div>
            </div>
        </main>
    </div>
    <script>
        function consultarCota() {
            const numeroCota = document.getElementById('numero_cota').value;
            const resultadoDiv = document.getElementById('resultado_consulta');
            if (!numeroCota) { alert('Por favor, digite um número de cota para consultar.'); return; }
            fetch('ajax/consultar_cota.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: `campanha_id=<?=$id?>&numero_cota=${numeroCota}` })
                .then(response => response.json())
                .then(data => {
                    resultadoDiv.classList.remove('hidden');
                    if (data.success) {
                        if (data.disponivel) { resultadoDiv.className = 'mt-4 p-4 rounded-md bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'; resultadoDiv.innerHTML = `✅ A cota ${numeroCota} está disponível!`; }
                        else { resultadoDiv.className = 'mt-4 p-4 rounded-md bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200'; resultadoDiv.innerHTML = `⚠️ A cota ${numeroCota} já foi Comprada<br><strong>Comprador:</strong> ${data.comprador || 'Não informado'}<br><strong>Data:</strong> ${data.data_compra || 'Não informada'}`; }
                    } else {
                        resultadoDiv.className = 'mt-4 p-4 rounded-md bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
                        resultadoDiv.innerHTML = `❌ Erro ao consultar a cota: ${data.message}`;
                    }
                })
                .catch(error => { resultadoDiv.classList.remove('hidden'); resultadoDiv.className = 'mt-4 p-4 rounded-md bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'; resultadoDiv.innerHTML = `❌ Erro ao consultar a cota: ${error.message}`; });
        }
        document.getElementById('btnConsultarCota').addEventListener('click', consultarCota);
    </script>
</body>
</html>


