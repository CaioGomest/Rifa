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
<head><title>Editar Campanha - Sorteios</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
    <div class="flex h-screen">
        <?php require("sidebar.php"); ?>
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Editar Campanha - Sorteios</h1>
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
                    <form id="formSorteio" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100 p-4 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Data Inicial</label>
                            <input type="datetime-local" name="data_inicio" id="data_inicio" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Data Final</label>
                            <input type="datetime-local" name="data_final" id="data_final" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Quantidade de Sorteados</label>
                            <input type="number" name="qtd_sortear" id="qtd_sortear" min="1" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Tipo de Sorteio</label>
                            <select name="tipo" id="tipo" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                <option value="">SELECIONE</option>
                                <option value="por_pedido">Por Pedido</option>
                                <option value="soma_pedidos">Soma dos Pedidos</option>
                                <option value="maior_cota">Maior Cota</option>
                                <option value="menor_cota">Menor Cota</option>
                                <option value="qtd_pedidos">Qtd. Pedidos</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Quantidade Mínima de Cotas</label>
                            <input type="number" name="qtd_cotas" id="qtd_cotas" min="0" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="md:col-span-4 flex justify-center mt-4">
                            <button type="button" id="btnSortear" class="bg-green-600 text-white px-8 py-3 rounded-md hover:bg-green-700 flex items-center text-lg font-semibold">
                                <i class="fas fa-random mr-2"></i>
                                SORTEAR E VEJA O RESULTADO!
                            </button>
                        </div>
                    </form>
                    <div id="animacaoSorteio" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50" style="display: none;">
                        <div class="text-center w-full max-w-md mx-auto px-4">
                            <div class="roleta-container relative mx-auto mb-8" style="width: 300px; height: 300px;">
                                <div class="roleta-outer absolute inset-0 bg-white rounded-full p-4 shadow-lg">
                                    <div class="roleta-inner absolute inset-0 m-4 bg-purple-600 rounded-full flex items-center justify-center">
                                        <div id="numeroSorteio" class="text-6xl font-bold text-white transform-none"></div>
                                    </div>
                                </div>
                                <div class="pointer absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10">
                                    <div class="w-8 h-8 bg-yellow-500 transform rotate-45"></div>
                                </div>
                            </div>
                            <h2 class="text-4xl font-bold text-white mb-4" id="textoSorteio">Sorteando...</h2>
                            <div class="space-y-2"><div class="text-xl text-purple-400" id="infoSorteio"></div></div>
                        </div>
                    </div>
                    <div id="resultadoSorteio" class="bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100" style="display: none;">
                        <div class="p-4 border-b border-gray-300 dark:border-gray-700"><h2 class="text-xl font-semibold">Resultado do Sorteio</h2></div>
                        <div class="overflow-x-auto">
                            <table class="w-full"><thead class="bg-gray-200 dark:bg-[#3F3F46]"><tr>
                                <th class="px-4 py-3 text-left">Nome</th><th class="px-4 py-3 text-left">Telefone</th><th class="px-4 py-3 text-left">Cota Premiada</th><th class="px-4 py-3 text-left">Data da Compra</th>
                            </tr></thead><tbody id="resultadoSorteioBody" class="divide-y divide-gray-300 dark:divide-gray-700"></tbody></table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        window.campanhaAtual = <?php echo json_encode($campanha); ?>;
        window.campanhaAtual.id = <?php echo (int)$id; ?>;
    </script>
    <div class="space-y-4 max-w-full p-6">
        <div class="bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100 p-4 rounded-lg mb-6">
            <div class="flex flex-col items-center">
                <div class="w-full max-w-md mb-4">
                    <img src="../<?= $campanha['caminho_imagem']?>" alt="<?= htmlspecialchars($campanha['nome']) ?>" class="w-full h-auto rounded-lg shadow-lg">
                </div>
                <h2 class="text-2xl font-bold mb-2">🎲 <?= htmlspecialchars($campanha['nome']) ?></h2>
                <p class="text-gray-600 dark:text-gray-300 text-center"><?= htmlspecialchars($campanha['subtitulo'] ?? '') ?></p>
            </div>
        </div>

        <form id="formSorteio" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100 p-4 rounded-lg">
            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Data Inicial</label>
                <input type="datetime-local" name="data_inicio" id="data_inicio" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Data Final</label>
                <input type="datetime-local" name="data_final" id="data_final" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Quantidade de Sorteados</label>
                <input type="number" name="qtd_sortear" id="qtd_sortear" min="1" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Tipo de Sorteio</label>
                <select name="tipo" id="tipo" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    <option value="">SELECIONE</option>
                    <option value="por_pedido">Por Pedido</option>
                    <option value="soma_pedidos">Soma dos Pedidos</option>
                    <option value="maior_cota">Maior Cota</option>
                    <option value="menor_cota">Menor Cota</option>
                    <option value="qtd_pedidos">Qtd. Pedidos</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">Quantidade Mínima de Cotas</label>
                <input type="number" name="qtd_cotas" id="qtd_cotas" min="0" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
            </div>
            <div class="md:col-span-4 flex justify-center mt-4">
                <button type="button" id="btnSortear" class="bg-green-600 text-white px-8 py-3 rounded-md hover:bg-green-700 flex items-center text-lg font-semibold">
                    <i class="fas fa-random mr-2"></i>
                    SORTEAR E VEJA O RESULTADO!
                </button>
            </div>
        </form>

        <div id="animacaoSorteio" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50" style="display: none;">
            <div class="text-center w-full max-w-md mx-auto px-4">
                <div class="roleta-container relative mx-auto mb-8" style="width: 300px; height: 300px;">
                    <div class="roleta-outer absolute inset-0 bg-white rounded-full p-4 shadow-lg">
                        <div class="roleta-inner absolute inset-0 m-4 bg-purple-600 rounded-full flex items-center justify-center">
                            <div id="numeroSorteio" class="text-6xl font-bold text-white transform-none"></div>
                        </div>
                    </div>
                    <div class="pointer absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10">
                        <div class="w-8 h-8 bg-yellow-500 transform rotate-45"></div>
                    </div>
                </div>
                <h2 class="text-4xl font-bold text-white mb-4" id="textoSorteio">Sorteando...</h2>
                <div class="space-y-2"><div class="text-xl text-purple-400" id="infoSorteio"></div></div>
            </div>
        </div>

        <div id="resultadoSorteio" class="bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100" style="display: none;">
            <div class="p-4 border-b border-gray-300 dark:border-gray-700"><h2 class="text-xl font-semibold">Resultado do Sorteio</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-200 dark:bg-[#3F3F46]"><tr>
                        <th class="px-4 py-3 text-left">Nome</th>
                        <th class="px-4 py-3 text-left">Telefone</th>
                        <th class="px-4 py-3 text-left">Cota Premiada</th>
                        <th class="px-4 py-3 text-left">Data da Compra</th>
                    </tr></thead>
                    <tbody id="resultadoSorteioBody" class="divide-y divide-gray-300 dark:divide-gray-700"></tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../js/campanhas_editar.js"></script>
    <script>
        setTimeout(() => {
            $('#btnSortear').click(function () {
                const btn = $(this);
                const originalText = btn.html();
                if (!$('#data_inicio').val()) { alert('Por favor, selecione uma data!'); return; }
                if (!$('#qtd_sortear').val() || $('#qtd_sortear').val() < 1) { alert('Por favor, informe a quantidade de sorteados!'); return; }
                if (!$('#tipo').val()) { alert('Por favor, selecione um tipo de sorteio!'); return; }
                $('#animacaoSorteio').fadeIn();
                btn.prop('disabled', true);
                const textos = ["Buscando participantes...","Verificando números...","Preparando sorteio...","Quase lá...","Selecionando ganhadores..."];
                let textoAtual = 0; const intervaloTexto = setInterval(() => { $('#infoSorteio').fadeOut(200, function () { $(this).text(textos[textoAtual]).fadeIn(200); textoAtual = (textoAtual + 1) % textos.length; }); }, 2000);
                animarNumeros();
                setTimeout(() => {
                    $.ajax({ url: '../functions/realizar_sorteio.php', method: 'POST', data: $('#formSorteio').serialize() + '&campanha_id=' + campanhaAtual.id,
                        success: function (response) { try { const data = JSON.parse(response); if (data.success) { $('#textoSorteio').text('Ganhadores Encontrados!'); setTimeout(() => { $('#animacaoSorteio').fadeOut(); $('#resultadoSorteioBody').html(''); data.ganhadores.forEach(function (ganhador) { $('#resultadoSorteioBody').append(`<tr class="hover:bg-gray-100 dark:hover:bg-gray-600"><td class="px-4 py-2">${ganhador.nome}</td><td class="px-4 py-2">${ganhador.telefone}<a href="https://wa.me/${ganhador.telefone}" target="_blank" class="text-green-600 hover:text-green-500 ml-2"><i class="fab fa-whatsapp"></i></a></td><td class="px-4 py-2">${ganhador.cota_premiada}</td><td class="px-4 py-2">${ganhador.data_compra}</td></tr>`); }); $('#resultadoSorteio').slideDown(); }, 1000); } else { $('#animacaoSorteio').fadeOut(); alert(data.message || 'Erro ao realizar o sorteio'); } } catch (e) { $('#animacaoSorteio').fadeOut(); alert('Erro ao processar resposta do servidor'); } },
                        error: function () { $('#animacaoSorteio').fadeOut(); alert('Erro ao realizar o sorteio'); },
                        complete: function () { clearInterval(intervaloTexto); btn.html(originalText); btn.prop('disabled', false); }
                    });
                }, 4000);
            });
        }, 100);
    </script>
</body>
</html>


