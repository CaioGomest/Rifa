<?php

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

require("header.php");

$status = isset($_GET['status']) ? $_GET['status'] : 1;
$campanhas = listaCampanhas($conn, NULL, NULL, $status, NULL, NULL, NULL, NULL, NULL, 0, NULL);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Campanhas</title>
    <style>
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .roleta-outer {
            animation: rotate 2s linear infinite;
            transform-origin: center center;
        }

        .roleta-inner {
            animation: pulse 1s ease-in-out infinite;
            transform-origin: center center;
        }

        @keyframes numberChange {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }

            50% {
                transform: scale(1.2);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .number-animation {
            animation: numberChange 0.3s ease-out;
        }

        .pointer {
            transition: transform 0.3s ease;
        }

        @media (max-width: 640px) {
            .roleta-container {
                width: 250px !important;
                height: 250px !important;
            }

            #numeroSorteio {
                font-size: 3rem;
            }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">

    <!-- Container principal -->
    <div class="flex h-screen">
        <!-- Inclui o arquivo de cabeçalho -->

        <!-- Sidebar -->
        <?php require("sidebar.php"); ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-6 overflow-y-auto max-h-screen">
            <?php require("mensagem.php"); ?>
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Campanhas</h1>
                <div class="flex items-center space-x-4">

                    <!-- Botão Criar novo -->
                    <button class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500"
                        onclick="window.location.href='campanha.php?acao=criar'">
                        Criar novo
                    </button>
                </div>
            </header>

            <section>
                <div class="bg-white dark:bg-[#27272A] p-4 rounded-md shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <select id="tipo_campanha" name="status" onchange="filtrarCampanhas()"
                            class="cursor-pointer bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded-md">
                            <option class="cursor-pointer" value="-1">Todas</option>
                            <option class="cursor-pointer" value="1" <?php echo $status == 1 ? 'selected' : ''; ?>>Ativas
                            </option>
                            <option class="cursor-pointer" value="0" <?php echo $status == 0 ? 'selected' : ''; ?>>
                                Inativas</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="min-w-[800px]">
                            <table class="w-full text-left">
                                <thead class="bg-gray-200 dark:bg-[#3F3F46] text-gray-800 dark:text-gray-100">
                                    <tr>
                                        <th class="p-2 text-gray-800 dark:text-gray-100">Campanha</th>
                                        <th class="p-2 text-gray-800 dark:text-gray-100">Progresso</th>
                                        <th class="p-2 text-gray-800 dark:text-gray-100">Valor</th>
                                        <th class="p-2 text-gray-800 dark:text-gray-100">Qtd. Números</th>
                                        <th class="p-2 text-gray-800 dark:text-gray-100">Status</th>
                                        <th class="p-2 text-gray-800 dark:text-gray-100">Data de Criação</th>
                                        <th class="p-2 text-gray-800 dark:text-gray-100">Data do Sorteio</th>
                                        <th class="p-2 text-gray-800 dark:text-gray-100">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($campanhas)): ?>
                                        <?php
                                        foreach ($campanhas as $campanha):
                                            $pedidos_campanha = listaPedidos($conn, NULL, NULL, $campanha['id'], NULL, NULL, 1);
                                            $soma_todos_numeros_vendidos = 0;
                                            $valor_total = 0;

                                            foreach ($pedidos_campanha as $pedido)
                                                $valor_total += $pedido['valor_total'];

                                            $numeros_disponiveis = count_obterNumerosDisponiveis($conn, $campanha['id']);
                                            $pedidos = listaPedidos($conn, NULL, NULL, $campanha['id'], NULL, NULL, 1);
                                            $soma_todos_numeros_vendidos = 0;
                                            foreach ($pedidos as $pedido) {
                                                $soma_todos_numeros_vendidos += $pedido['quantidade'];
                                            }
                                            $porcentagem_vendida = ($soma_todos_numeros_vendidos / $campanha['quantidade_numeros']) * 100;
                                            $porcentagem = ($soma_todos_numeros_vendidos / $campanha['quantidade_numeros']) * 100;
                                            ?>
                                            <tr
                                                class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="p-2 text-gray-800 dark:text-gray-300">
                                                    <?= htmlspecialchars($campanha['nome']) ?>
                                                </td>
                                                <td class="p-2 text-gray-800 dark:text-gray-300">
                                                    <?= number_format($porcentagem_vendida, 2) ?>%
                                                </td>
                                                <td class="p-2 inline-table text-gray-800 dark:text-gray-300">R$
                                                    <?= number_format($valor_total, 2, ',', '.') ?>
                                                </td>
                                                <td class="p-2">
                                                    <div class="w-full bg-gray-200 dark:bg-[#3F3F46] rounded-full h-2.5">

                                                        <div class="bg-green-500 h-2.5 rounded-full"
                                                            style="width: <?= $porcentagem_vendida ?>%;"></div>
                                                    </div>
                                                    <span
                                                        class="text-sm"><?= number_format($soma_todos_numeros_vendidos, 0, ',', '.') ?>/<?= htmlspecialchars(number_format($campanha['quantidade_numeros'], 0, ',', '.')) ?></span>
                                                </td>
                                                <td class="p-2">
                                                    <?php if ($campanha['status'] == 1): ?>
                                                        <span class="bg-green-500 text-white px-2 py-1 rounded-md">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="bg-red-500 text-white px-2 py-1 rounded-md">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="p-2"><?= date('d/m H:i', strtotime($campanha['data_criacao'])) ?>
                                                </td>
                                                <td class="p-2"><?= date('d/m H:i', strtotime($campanha['data_sorteio'])) ?>
                                                </td>
                                                <td class="p-2 flex space-x-2">
                                                    <button
                                                        class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white"
                                                        onclick="window.location.href='../campanha.php?id=<?= $campanha['id'] ?>'">👁</button>

                                                    <button
                                                        class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white"
                                                        onclick="abrirModalEdicao(<?= $campanha['id'] ?>, <?= htmlspecialchars(json_encode($campanha)) ?>)">✏️</button>

                                                    <button class="text-red-600 dark:text-red-400 hover:text-red-500"
                                                        onclick="confirmarDelecao(<?= $campanha['id'] ?>)">
                                                        🗑
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center p-4">Nenhuma campanha encontrada.</td>
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

    <!-- Modal de Edição -->
    <div id="modalEdicao"
        class="z-50 fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center overflow-y-auto py-6">
        <div
            class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl w-11/12 max-w-lg max-h-[90vh] overflow-y-auto m-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Editar Campanha</h2>
                <button onclick="fecharModalEdicao()" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <div class="space-y-4">
                <button onclick="window.location.href='dados.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    📝 Dados Gerais
                </button>
                <button onclick="window.location.href='imagens.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    🖼️ Imagens
                </button>
                <button onclick="window.location.href='consultar_cota.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    🔍 Consultar Cota
                </button>
                <button onclick="window.location.href='desconto.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    💰 Desconto
                </button>
                <button onclick="window.location.href='ranking.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    🏆 Top Compradores
                </button>
                <button onclick="window.location.href='barra.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    📊 Barra de Progresso
                </button>
                <button onclick="window.location.href='ganhadores.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    🎯 Ganhadores
                </button>
                <button onclick="window.location.href='cotas_premiadas.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    🎌 Cotas Premiadas
                </button>
                <button onclick="window.location.href='cotas_dobro.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    🔄 Cotas em Dobro
                </button>
                <button onclick="window.location.href='sorteios.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    🎲 Sorteios
                </button>
                <button onclick="window.location.href='roletas_raspadinhas.php?id=' + campanhaAtual.id"
                    class="w-full text-left p-3 bg-gray-100 dark:bg-[#3F3F46] rounded-md hover:bg-gray-200 dark:hover:bg-gray-600">
                    🎰 Roletas e Raspadinhas
                </button>

            </div>
        </div>
    </div>

    <!-- Modal de Campo -->
    <div id="modalCampo"
        class="z-50 fixed inset-0 bg-black bg-opacity-80 hidden items-center justify-center overflow-y-auto py-6">
        <div
            class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl max-h-[90vh] m-auto flex flex-col w-full max-w-6xl">

            <div class="flex justify-between items-center mb-4">
                <h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
                <button onclick="fecharModalCampo()" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <div id="modalCampoConteudo" class="space-y-4 overflow-y-auto flex-grow">
                <!-- O conteúdo será preenchido dinamicamente -->
            </div>
        </div>
    </div>

</body>

</html>
<script>
    let campanhaAtual = null;

    function confirmarDelecao(id) {
        if (confirm("Tem certeza de que deseja excluir esta campanha?")) {
            fetch('ajax/deletar_campanha.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(id)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Campanha excluída com sucesso!");
                        location.reload();
                    } else {
                        alert("Erro ao excluir a campanha: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Erro:", error);
                    alert("Ocorreu um erro ao tentar excluir a campanha.");
                });
        }
    }

    function filtrarCampanhas() {
        const status = document.getElementById('tipo_campanha').value;
        window.location.href = `campanhas.php?status=${status}`;
    }

    function abrirModalEdicao(id, campanha) {
        document.querySelector('body').classList.add('overflow-hidden');
        campanhaAtual = campanha;
        document.getElementById('modalEdicao').classList.remove('hidden');
        document.getElementById('modalEdicao').classList.add('flex');
        console.log(campanhaAtual);
    }

    function fecharModalEdicao() {
        document.querySelector('body').classList.remove('overflow-hidden');
        document.getElementById('modalEdicao').classList.add('hidden');
        document.getElementById('modalEdicao').classList.remove('flex');
    }
    function formatPrice(input) {
        let value = input.value.replace(/\D/g, '');  // Remove caracteres não numéricos
        value = (value / 100).toFixed(2);  // Divide por 100 e limita a 2 casas decimais
        value = value.replace('.', ',');  // Substitui ponto por vírgula para exibição

        // Adiciona o separador de milhar (ponto) no valor
        let parts = value.split(',');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');  // Adiciona ponto a cada 3 dígitos

        input.value = parts.join(',');
    }


    function abrirModalCampo(tipo) {
        const modal = document.getElementById('modalCampo');
        const titulo = document.getElementById('modalCampoTitulo');
        const conteudo = document.getElementById('modalCampoConteudo');

        // Configura o conteúdo baseado no tipo
        switch (tipo) {
            case 'dados':
                titulo.textContent = 'Editar Dados Gerais';
                conteudo.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nome</label>
                        <input type="text" id="nome" value="${campanhaAtual.nome}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Subtítulo</label>
                        <input type="text" id="subtitulo" value="${campanhaAtual.subtitulo}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Descrição</label>
                        <textarea id="descricao" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">${campanhaAtual.descricao}</textarea>
                    </div>
                   <div>
                        <label class="block text-sm font-medium mb-1">Preço</label>
                        <div class="relative">
                            <span class="absolute left-2 top-2 text-gray-500">$</span>
                            <input type="text" id="preco" name="preco" value="${campanhaAtual.preco ? campanhaAtual.preco : ''}" 
                                class="w-full p-2 pl-8 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                oninput="formatPrice(this)
                                onclick="formatPrice(this)
                                ">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Tipo de Sorteio</label>
                        <select id="tipo_sorteio" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                            <option value="1" ${campanhaAtual.tipo_sorteio == 1 ? 'selected' : ''}>Sorteio</option>
                            <option value="2" ${campanhaAtual.tipo_sorteio == 2 ? 'selected' : ''}>Rifa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Layout</label>
                        <select id="layout" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                            <option value="0" ${campanhaAtual.layout == 0 ? 'selected' : ''}>Rincon</option>
                            <option value="1" ${campanhaAtual.layout == 1 ? 'selected' : ''}>Buzeira</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Quantidade de Números</label>
                        <input type="number" id="quantidade_numeros" value="${campanhaAtual.quantidade_numeros}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Compra Mínima</label>
                        <input type="number" id="compra_minima" value="${campanhaAtual.compra_minima}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Compra Máxima</label>
                        <input type="number" id="compra_maxima" value="${campanhaAtual.compra_maxima}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select id="status" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                            <option value="0" ${campanhaAtual.status == 0 ? 'selected' : ''}>Inativo</option>
                            <option value="1" ${campanhaAtual.status == 1 ? 'selected' : ''}>Ativo</option>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label for="campanha_privada" class="block mb-2 font-medium">Campanha Privada</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="campanha_privada" name="campanha_privada" value="1"
                                ${campanhaAtual.campanha_privada == 1 ? 'checked' : ''}>
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    <div class="mb-6">
                        <label for="campanha_destaque" class="block mb-2 font-medium">Campanha em Destaque</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="campanha_destaque" name="campanha_destaque" value="1"
                                ${campanhaAtual.campanha_destaque == 1 ? 'checked' : ''}>
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    <div class="mb-6">
                        <label for="habilitar_ranking" class="block mb-2 font-medium">Habilitar Ranking</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="habilitar_ranking" name="habilitar_ranking" value="1"
                                ${campanhaAtual.habilitar_ranking == 1 ? 'checked' : ''}>
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    <div id="div_quantidade_ranking">
                        <label class="block text-sm font-medium mb-1">Quantidade no Ranking (1 a 10)</label>
                        <input type="number" id="quantidade_ranking" value="${campanhaAtual.quantidade_ranking}" min="1" max="10" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <button onclick="salvarCampo('dados')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                </div>
            `;
                break;
            // case 'roletas_raspadinhas':
            // A criação/edição de UI deste grupo está definida mais adiante neste mesmo switch
            // (case que seta `titulo` e `conteudo.innerHTML`). O envio dos dados acontece na
            // função `salvarCampo('roletas_raspadinhas')`. Mantido sem manipulação aqui para
            // evitar escopo incorreto da variável `dados`.
            // break;

            case 'imagens':
                titulo.textContent = 'Editar Imagens';
                conteudo.innerHTML = `
                <div class="space-y-6">

                    <!-- Imagem de Capa (listagem/index) -->
                    <div>
                        <label class="block mb-2 font-medium">Imagem de capa (listagem)</label>
                        <div class="flex flex-col space-y-4">
                            <div class="flex items-center justify-center w-full">
                                <label for="imagem_capa" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-[#3F3F46] hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600" onclick="document.getElementById('remover_imagem_capa').value='0'">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF (MAX. 2MB)</p>
                                    </div>
                                <input type="file" name="imagem_capa" id="imagem_capa" accept="image/*" class="hidden" onchange="previewImagem(this, 'preview-capa')" />
                                </label>
                            </div>
                            ${campanhaAtual.imagem_capa ? `
                                <div class="relative inline-block w-fit group imagemAtual">
                                    <img src="../${campanhaAtual.imagem_capa}" alt="Imagem de capa" class="max-w-[200px] rounded-lg shadow-md">
                                    <button type="button" onclick="removerImagemCapa(this)" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            ` : ''}
                            <div id="preview-capa" class="hidden mt-4">
                                <img src="" alt="Preview" class="max-w-[200px] rounded-lg shadow-md">
                            </div>
                            <input type="hidden" id="imagem_capa_atual" value="${campanhaAtual.imagem_capa || ''}">
                            <input type="hidden" id="remover_imagem_capa" value="0">
                        </div>
                    </div>

                    <!-- Imagem Principal -->
                    <div>
                        <label class="block mb-2 font-medium">Imagem principal</label>
                        <div class="flex flex-col space-y-4">
                            <div class="flex items-center justify-center w-full">
                                <label for="imagem_principal" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-[#3F3F46] hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600" onclick="document.getElementById('remover_imagem_principal').value='0'">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF (MAX. 2MB)</p>
                                    </div>
                                <input type="file" name="imagem_principal" id="imagem_principal" accept="image/*" class="hidden" onchange="previewImagem(this, 'preview-principal')" />
                                    </label>
                            </div>
                            ${campanhaAtual.caminho_imagem ? `
                                <div class="relative inline-block w-fit group imagemAtual">
                                    <img src="../${campanhaAtual.caminho_imagem}" alt="Imagem atual" class="max-w-[200px] rounded-lg shadow-md">
                                    <button type="button" onclick="removerImagemPrincipal(this)" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            ` : ''}
                            <div id="preview-principal" class="hidden mt-4">
                                <img src="" alt="Preview" class="max-w-[200px] rounded-lg shadow-md">
                            </div>
                            <input type="hidden" id="caminho_imagem_atual" value="${campanhaAtual.caminho_imagem || ''}">
                            <input type="hidden" id="remover_imagem_principal" value="0">
                        </div>
                    </div>

                    <!-- Galeria de Imagens -->
                    <div>
                        <label class="block mb-2 font-medium">Galeria de imagens</label>
                        <div class="flex items-center justify-center w-full mb-4">
                                <label for="galeria" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-[#3F3F46] hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF (MAX. 2MB)</p>
                                </div>
                                <input type="file" value="${campanhaAtual.galeria_imagens || ''}"  id="galeria" accept="image/*" multiple class="hidden" onchange="previewGaleria(this)" />
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="galeria-atual">
                            ${campanhaAtual.galeria_imagens ? campanhaAtual.galeria_imagens.split(',').map((img, index) => `
                                <div class="relative group">
                                    <img src="../${img}" alt="Imagem da galeria" class="w-full h-40 object-cover rounded-lg shadow-md">
                                    <button type="button" onclick="removerImagemGaleria(this, '${img}')" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            `).join('') : ''}
                        </div>
                        <input type="hidden" id="galeria_imagens_atual" name="galeria[]" value="${campanhaAtual.galeria_imagens || ''}">
                        <div id="preview-galeria" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4"></div>
                    </div>
                </div>
                <button onclick="salvarCampo('imagens')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700 mt-4">Salvar</button>
            `;
                break;

            case 'consultar_cota':
                titulo.textContent = 'Consultar Cota';
                conteudo.innerHTML = `
                <div class="space-y-4">
                <!-- Cabeçalho com imagem e título -->
                    <div class="bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100 p-4 rounded-lg mb-6">
                        <div class="flex flex-col items-center">
                            <div class="w-full max-w-md mb-4">
                                <img src="../${campanhaAtual.caminho_imagem}" alt="${campanhaAtual.nome}" class="w-full h-auto rounded-lg shadow-lg">
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">🎲 ${campanhaAtual.nome}</h2>
                            <p class="text-gray-600 dark:text-gray-300 text-center">${campanhaAtual.subtitulo || ''}</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Número da Cota</label>
                        <div class="flex space-x-2">
                            <input type="number" id="numero_cota" class="flex-1 p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Digite o número da cota">
                            <button onclick="consultarCota()" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-500">
                                Consultar
                            </button>
                        </div>
                    </div>
                    <div id="resultado_consulta" class="mt-4 p-4 rounded-md hidden">
                        <!-- Resultado será preenchido via JavaScript -->
                    </div>
                </div>
                `;
                break;

            case 'desconto':
                titulo.textContent = 'Editar Desconto';
                conteudo.innerHTML = `
                <div class="space-y-4">
                    
                    <!-- Adição rápida -->
                    <div>
                        <label for="habilitar_adicao_rapida" class="block mb-2 font-medium">Habilitar Adição Rápida</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="habilitar_adicao_rapida" name="habilitar_adicao_rapida" value="1"
                                ${campanhaAtual.habilitar_adicao_rapida == 1 ? 'checked' : ''} >
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                
                    <!-- Desconto Acumulativo comentado temporariamente
                    <div>
                        <label for="habilitar_desconto_acumulativo" class="block mb-2 font-medium">Habilitar Desconto Acumulativo</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="habilitar_desconto_acumulativo" name="habilitar_desconto_acumulativo" value="1"
                                ${campanhaAtual.habilitar_desconto_acumulativo == 1 ? 'checked' : ''} >
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    -->

                    <div>
                        <label for="habilitar_pacote_promocional" class="block mb-2 font-medium">Habilitar Pacote Promocional</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="habilitar_pacote_promocional" name="habilitar_pacote_promocional" class="habilitar_pacote_promocional" value="1"
                                ${campanhaAtual.habilitar_pacote_promocional == 1 ? 'checked' : ''}	>
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold mb-2">Pacote Exclusivo</h3>
                        <label class="toggle-switch">
                            <input type="checkbox" id="habilita_pacote_promocional_exclusivo" name="habilita_pacote_promocional_exclusivo" value="1"
                                ${campanhaAtual.habilita_pacote_promocional_exclusivo == 1 ? 'checked' : ''} >
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>

                    <div class="mb-4 pacote_promocional">
                        <h3 class="text-lg font-semibold mb-2">Pacote Promocional</h3>
                        <div id="descontos-container" class="space-y-3">
                            <!-- Pacotes normais serão carregados aqui -->
                        </div>

                        <button type="button" onclick="adicionarDescontoPromocional('normal')" class="mt-4 w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">
                            Adicionar Novo Pacote Normal
                        </button>
                    </div>

                
                    <div class="mb-4" id="pacote_exclusivo">
                        <h3 class="text-lg font-semibold mb-2">Pacotes Exclusivos</h3>
                        <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md mb-4">
                            <p class="text-purple-800 dark:text-purple-200">
                                <span class="font-bold">⭐ Oferta Exclusiva!</span><br>
                                Compre com desconto: Condição especial de pacotes por tempo LIMITADO! Não perca essa oportunidade de aumentar suas chances de ganhar!
                            </p>
                        </div>
                        <div id="descontos-exclusivos-container" class="space-y-3">
                            <!-- Pacotes exclusivos serão carregados aqui -->
                        </div>

                        <button type="button" onclick="adicionarDescontoExclusivo('exclusivo')" class="mt-4 w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">
                            Adicionar Novo Pacote Exclusivo
                        </button>
                    </div>
                    <button onclick="salvarCampo('desconto')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700 mt-4">Salvar</button>
                </div>
            `;

                // Carregar pacotes existentes
                setTimeout(() => {

                    validaDescontoPromocial(5);
                    validaDescontoExclusivo(5);
                    const containerNormal = document.getElementById('descontos-container');
                    const containerExclusivo = document.getElementById('descontos-exclusivos-container');
                    let pacotesNormais = [];
                    let pacotesExclusivos = [];

                    try {
                        if (campanhaAtual.pacote_promocional) {
                            pacotesNormais = JSON.parse(campanhaAtual.pacote_promocional);
                        }
                        if (campanhaAtual.pacotes_exclusivos) {
                            pacotesExclusivos = JSON.parse(campanhaAtual.pacotes_exclusivos);
                        }
                    }
                    catch (e) {
                        console.error('Erro ao carregar pacotes:', e);
                    }

                    if (Array.isArray(pacotesNormais) && pacotesNormais.length > 0) {
                        pacotesNormais.forEach((pacote) => {
                            adicionarPacoteExistentePromocional(pacote, 'normal');
                        });
                    } else {
                        adicionarDesconto('normal');
                    }

                    if (Array.isArray(pacotesExclusivos) && pacotesExclusivos.length > 0) {
                        pacotesExclusivos.forEach((pacote) => {
                            adicionarPacoteExistenteExclusivo(pacote, 'exclusivo');
                        });
                    } else {
                        adicionarDesconto('exclusivo');
                    }
                }, 100);
                break;

            case 'ranking':
                titulo.textContent = 'Top Compradores';

                // Parse do JSON salvo no banco
                let filtroSalvo = {};
                try {
                    if (campanhaAtual.filtro_periodo_top_ganhadores) {
                        filtroSalvo = JSON.parse(campanhaAtual.filtro_periodo_top_ganhadores);
                    }
                } catch (e) {
                    console.error('Erro ao parsear filtro:', e);
                }

                conteudo.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label for="selecionar_top_ganhadores" class="block mb-2 font-medium">Selecionar Top Ganhadores</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="selecionar_top_ganhadores" name="selecionar_top_ganhadores" value="1"
                                ${campanhaAtual.selecionar_top_ganhadores == 1 ? 'checked' : ''}>
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>

                    <div id="div_filtro_periodo_top_ganhadores" class="hidden">
                        <label class="block text-sm font-medium mb-1">Filtro de Período</label>
                        <select id="filtro_periodo_top_ganhadores" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                            <option value="hoje" ${filtroSalvo.filtro === 'hoje' ? 'selected' : ''}>Hoje</option>
                            <option value="ontem" ${filtroSalvo.filtro === 'ontem' ? 'selected' : ''}>Ontem</option>
                            <option value="ultimo_mes" ${filtroSalvo.filtro === 'ultimo_mes' ? 'selected' : ''}>Último Mês</option>
                            <option value="personalizado" ${filtroSalvo.filtro === 'personalizado' ? 'selected' : ''}>Personalizado</option>
                        </select>
                        
                        <div id="div_datas_personalizadas" class="hidden mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Data Inicial</label>
                                <input type="date" id="data_inicial_personalizada" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" value="${filtroSalvo.filtro === 'personalizado' ? filtroSalvo.valor.split(' até ')[0] : ''}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Data Final</label>
                                <input type="date" id="data_final_personalizada" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" value="${filtroSalvo.filtro === 'personalizado' ? filtroSalvo.valor.split(' até ')[1] : ''}">
                            </div>
                        </div>
                    </div>

                    <button onclick="salvarCampo('ranking')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                </div>
            `;

                // Adicionar evento para mostrar/esconder o filtro de período
                setTimeout(() => {
                    const toggleSwitch = document.getElementById('selecionar_top_ganhadores');
                    const divFiltro = document.getElementById('div_filtro_periodo_top_ganhadores');
                    const divDatasPersonalizadas = document.getElementById('div_datas_personalizadas');
                    const selectFiltro = document.getElementById('filtro_periodo_top_ganhadores');

                    if (toggleSwitch.checked) {
                        divFiltro.classList.remove('hidden');
                        if (selectFiltro.value === 'personalizado') {
                            divDatasPersonalizadas.classList.remove('hidden');
                        }
                    }

                    toggleSwitch.addEventListener('change', function () {
                        if (this.checked) {
                            divFiltro.classList.remove('hidden');
                            if (selectFiltro.value === 'personalizado') {
                                divDatasPersonalizadas.classList.remove('hidden');
                            }
                        } else {
                            divFiltro.classList.add('hidden');
                            divDatasPersonalizadas.classList.add('hidden');
                        }
                    });

                    selectFiltro.addEventListener('change', function () {
                        if (this.value === 'personalizado') {
                            divDatasPersonalizadas.classList.remove('hidden');
                        } else {
                            divDatasPersonalizadas.classList.add('hidden');
                        }
                    });
                }, 100);
                break;

            case 'barra':
                titulo.textContent = 'Editar Barra de Progresso';
                conteudo.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label for="habilitar_barra_progresso" class="block mb-2 font-medium">Habilitar Barra de Progresso</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="habilitar_barra_progresso"
                                name="habilitar_barra_progresso" value="1" 
                                ${campanhaAtual.habilitar_barra_progresso == 1 ? 'checked' : ''} >
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    <div id="barra_progresso_ativa" class="hidden">
                        <div>
                            <label for="ativar_progresso_manual" class="block mb-2 font-medium">Ativar
                                Progresso Manual</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="ativar_progresso_manual"
                                    name="ativar_progresso_manual" value="1" 
                                    ${campanhaAtual.ativar_progresso_manual == 1 ? 'checked' : ''} >
                                <div class="toggle-switch-background">
                                    <div class="toggle-switch-handle"></div>
                                </div>
                            </label>
                        </div>

                        <div id="div_progresso_manual" class="hidden">
                            <label for="porcentagem_barra_progresso" class="block mb-2 font-medium">Porcentagem da Barra</label>
                            <div class="flex items-center space-x-2">
                                <input type="text" id="porcentagem_barra_progresso"
                                    name="porcentagem_barra_progresso"
                                    class="w-24 bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                    value="${campanhaAtual.porcentagem_barra_progresso ? Number(campanhaAtual.porcentagem_barra_progresso).toFixed(1) : '0.0'}"
                                    oninput="this.value = this.value.replace(/[^0-9.]/g, ''); if(this.value > 100) this.value = '100.0'; if(parseFloat(this.value) < 0) this.value = '0.0';">
                                <span class="text-gray-500">%</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Define a porcentagem atual da barra de progresso (0-100)</p>
                        </div>
                    </div>
                    <button onclick="salvarCampo('barra')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                </div>
            `;
                setTimeout(() => {
                    validaProgresso();
                    validaProgressoManual();
                }, 100);
                break;

            case 'ganhadores':
                titulo.textContent = 'Editar Ganhadores';
                conteudo.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Vencedor do Sorteio</label>
                        <input type="text" id="vencedor_sorteio" value="${campanhaAtual.vencedor_sorteio || ''}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Bilhete do Sorteio</label>
                        <input type="text" id="numero_sorteio" value="${campanhaAtual.numero_sorteio || ''}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Data do Sorteio</label>
                        <input type="datetime-local" id="data_sorteio" value="${campanhaAtual.data_sorteio || ''}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                    </div>
                    <button onclick="salvarCampo('ganhadores')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                </div>
            `;
                break;

            case 'cotas_premiadas':
                const usuario_tipo = <?= $_SESSION['usuario']['usuario_tipo']; ?>;
                let opcaoImediato = '';
                if (usuario_tipo == 10)
                    opcaoImediato = `<option value="imediato" ${campanhaAtual.status_cotas_premiadas === 'imediato' ? 'selected' : ''}>Imediato</option>`;

                titulo.textContent = 'Editar Cotas Premiadas';
                conteudo.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <label for="mostrar_cotas_premiadas" class="block mb-2 font-medium">Mostrar Cotas Premiadas no Site</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="mostrar_cotas_premiadas" name="mostrar_cotas_premiadas" value="1"
                                ${campanhaAtual.mostrar_cotas_premiadas == 1 ? 'checked' : ''}>
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Status das Cotas Premiadas</label>
                        <select id="status_cotas_premiadas" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                            <option value="bloqueado" ${campanhaAtual.status_cotas_premiadas === 'bloqueado' ? 'selected' : ''}>Bloqueado</option>
                            <option value="disponivel" ${campanhaAtual.status_cotas_premiadas === 'disponivel' ? 'selected' : ''}>Disponível</option>
                            ${opcaoImediato}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantidade de Cotas Premiadas</label>
                        <input type="number" id="quantidade_cotas_premiadas" min="1" value="${campanhaAtual.quantidade_cotas_premiadas || 0}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        <p class="text-sm text-gray-500 mt-1">Quantidade de cotas que serão selecionadas automaticamente</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Prêmio para este Grupo</label>
                        <input type="text" id="premio_cotas_premiadas" value="${campanhaAtual.premio_cotas_premiadas || ''}" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" placeholder="Ex: R$ 500 ou AUDI A3">
                        <p class="text-sm text-gray-500 mt-1">Prêmio que será associado às cotas deste grupo</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Descrição das Cotas Premiadas</label>
                        <textarea id="descricao_cotas_premiadas" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" rows="4">${campanhaAtual.descricao_cotas_premiadas || ''}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Grupos de Cotas Premiadas</label>
                        <div id="grupos_premios" class="mt-3 space-y-2">
                            ${gerarHTMLGruposPremios(campanhaAtual.premio_cotas_premiadas)}
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            Total de cotas premiadas: ${campanhaAtual.cotas_premiadas ? campanhaAtual.cotas_premiadas.split(',').length : 0}
                        </p>
                        <p class="text-sm text-gray-500">Cotas selecionadas automaticamente pelo sistema</p>
                    </div>
                    <button onclick="gerarCotasPremiadas()" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 mb-2">Gerar Cotas Premiadas</button>
                    <button onclick="limparCotasPremiadas()" class="w-full bg-red-600 text-white p-2 rounded hover:bg-red-700 mb-2">Limpar Todas as Cotas</button>
                    <button onclick="corrigirGruposPremiados()" class="w-full bg-yellow-600 text-white p-2 rounded hover:bg-yellow-700 mb-2">Corrigir Grupos Corrompidos</button>
                    <button onclick="salvarCampo('cotas_premiadas')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                </div>
            `;
                break;

            case 'cotas_dobro':
                titulo.textContent = 'Editar Cotas em Dobro';
                conteudo.innerHTML = `
                <div class="space-y-4">
                    <div class="mb-4">
                        <label for="habilitar_cotas_em_dobro" class="block mb-2 font-medium">Cotas em Dobro</label>
                        <label class="toggle-switch">
                            <input type="checkbox" id="habilitar_cotas_em_dobro"
                                name="habilitar_cotas_em_dobro" value="1" ${campanhaAtual.habilitar_cotas_em_dobro == 1 ? 'checked' : ''} >
                            <div class="toggle-switch-background">
                                <div class="toggle-switch-handle"></div>
                            </div>
                        </label>
                    </div>
                    <div id="campos_cotas_dobro" class="space-y-4" style="display: ${campanhaAtual.habilitar_cotas_em_dobro == 1 ? 'block' : 'none'}">
                        <div>
                            <label class="block text-sm font-medium mb-1">Título do Alerta</label>
                            <input type="text" id="titulo_cotas_dobro" value="${campanhaAtual.titulo_cotas_dobro || ''}" 
                                class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                placeholder="Ex: COTAS EM DOBRO ATIVADAS!">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Subtítulo do Alerta</label>
                            <input type="text" id="subtitulo_cotas_dobro" value="${campanhaAtual.subtitulo_cotas_dobro || ''}" 
                                class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                placeholder="Ex: Aproveite! Todas as cotas estão valendo em dobro.">
                        </div>
                    </div>
                    <button onclick="salvarCampo('cotas_dobro')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                </div>
                `;

                // Adicionar evento para mostrar/esconder campos quando o checkbox mudar
                setTimeout(() => {
                    document.getElementById('habilitar_cotas_em_dobro').addEventListener('change', function () {
                        document.getElementById('campos_cotas_dobro').style.display = this.checked ? 'block' : 'none';
                    });
                }, 100);
                break;

            case 'sorteios':
                titulo.textContent = 'Realizar Sorteio';
                conteudo.innerHTML = `
                <div class="space-y-4 max-w-full">
                    <!-- Cabeçalho com imagem e título -->
                    <div class="bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100 p-4 rounded-lg mb-6">
                        <div class="flex flex-col items-center">
                            <div class="w-full max-w-md mb-4">
                                <img src="../${campanhaAtual.caminho_imagem}" alt="${campanhaAtual.nome}" class="w-full h-auto rounded-lg shadow-lg">
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">🎲 ${campanhaAtual.nome}</h2>
                            <p class="text-gray-600 dark:text-gray-300 text-center">${campanhaAtual.subtitulo || ''}</p>
                        </div>
                    </div>

                    <!-- Grid de formulário -->
                    <form id="formSorteio" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100 p-4 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">
                                Data Inicial
                            </label>
                            <input type="datetime-local" name="data_inicio" id="data_inicio" 
                                   class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">
                                Data Final
                            </label>
                            <input type="datetime-local" name="data_final" id="data_final" 
                                   class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">
                                Quantidade de Sorteados
                            </label>
                            <input type="number" name="qtd_sortear" id="qtd_sortear" min="1"
                                   class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">
                                Tipo de Sorteio
                            </label>
                            <select name="tipo" id="tipo" 
                                    class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                <option value="">SELECIONE</option>
                                <option value="por_pedido">Por Pedido</option>
                                <option value="soma_pedidos">Soma dos Pedidos</option>
                                <option value="maior_cota">Maior Cota</option>
                                <option value="menor_cota">Menor Cota</option>
                                <option value="qtd_pedidos">Qtd. Pedidos</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black dark:text-gray-300 mb-1">
                                Quantidade Mínima de Cotas
                            </label>
                            <input type="number" name="qtd_cotas" id="qtd_cotas" min="0"
                                   class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="md:col-span-4 flex justify-center mt-4">
                            <button type="button" id="btnSortear"
                                    class="bg-green-600 text-white px-8 py-3 rounded-md hover:bg-green-700 flex items-center text-lg font-semibold">
                                <i class="fas fa-random mr-2"></i>
                                SORTEAR E VEJA O RESULTADO!
                            </button>
                        </div>
                    </form>

                    <!-- Animação do Sorteio -->
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
                            <div class="space-y-2">
                                <div class="text-xl text-purple-400" id="infoSorteio"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Resultado do Sorteio -->
                    <div id="resultadoSorteio" class="bg-gray-100 dark:bg-[#27272A] text-gray-800 dark:text-gray-100" style="display: none;">
                        <div class="p-4 border-b border-gray-300 dark:border-gray-700">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Resultado do Sorteio</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-200 dark:bg-[#3F3F46]">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-gray-800 dark:text-gray-100">Nome</th>
                                        <th class="px-4 py-3 text-left text-gray-800 dark:text-gray-100">Telefone</th>
                                        <th class="px-4 py-3 text-left text-gray-800 dark:text-gray-100">Cota Premiada</th>
                                        <th class="px-4 py-3 text-left text-gray-800 dark:text-gray-100">Data da Compra</th>
                                    </tr>
                                </thead>
                                <tbody id="resultadoSorteioBody" class="divide-y divide-gray-300 dark:divide-gray-700">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                `;

                // Ajustar o tamanho do modal para ser mais largo
                document.querySelector('#modalCampo > div').classList.remove('w-96');
                document.querySelector('#modalCampo > div').classList.add('w-full', 'max-w-6xl');

                // Adicionar o evento de clique no botão de sorteio
                setTimeout(() => {
                    $('#btnSortear').click(function () {
                        const btn = $(this);
                        const originalText = btn.html();

                        // Validações
                        if (!$('#data_inicio').val()) {
                            alert('Por favor, selecione uma data!');
                            return;
                        }
                        if (!$('#qtd_sortear').val() || $('#qtd_sortear').val() < 1) {
                            alert('Por favor, informe a quantidade de sorteados!');
                            return;
                        }
                        if (!$('#tipo').val()) {
                            alert('Por favor, selecione um tipo de sorteio!');
                            return;
                        }

                        // Mostrar animação
                        $('#animacaoSorteio').fadeIn();
                        btn.prop('disabled', true);

                        // Textos informativos durante o sorteio
                        const textos = [
                            "Buscando participantes...",
                            "Verificando números...",
                            "Preparando sorteio...",
                            "Quase lá...",
                            "Selecionando ganhadores..."
                        ];

                        let textoAtual = 0;
                        const intervaloTexto = setInterval(() => {
                            $('#infoSorteio').fadeOut(200, function () {
                                $(this).text(textos[textoAtual]).fadeIn(200);
                                textoAtual = (textoAtual + 1) % textos.length;
                            });
                        }, 2000);

                        // Inicia a animação dos números
                        animarNumeros();

                        // Requisição AJAX para o sorteio
                        setTimeout(() => {
                            $.ajax({
                                url: '../functions/realizar_sorteio.php',
                                method: 'POST',
                                data: $('#formSorteio').serialize() + '&campanha_id=' + campanhaAtual.id,
                                success: function (response) {
                                    try {
                                        const data = JSON.parse(response);
                                        if (data.success) {
                                            // Última animação antes de mostrar resultados
                                            $('#textoSorteio').text('Ganhadores Encontrados!');
                                            setTimeout(() => {
                                                $('#animacaoSorteio').fadeOut();
                                                $('#resultadoSorteioBody').html('');
                                                data.ganhadores.forEach(function (ganhador) {
                                                    $('#resultadoSorteioBody').append(`
                                                        <tr class="hover:bg-gray-100 dark:hover:bg-gray-600">
                                                            <td class="px-4 py-2 text-gray-800 dark:text-gray-300">${ganhador.nome}</td>
                                                            <td class="px-4 py-2 text-gray-800 dark:text-gray-300">
                                                                ${ganhador.telefone}
                                                                <a href="https://wa.me/${ganhador.telefone}" target="_blank" class="text-green-600 hover:text-green-500 ml-2">
                                                                    <i class="fab fa-whatsapp"></i>
                                                                </a>
                                                            </td>
                                                            <td class="px-4 py-2 text-gray-800 dark:text-gray-300">${ganhador.cota_premiada}</td>
                                                            <td class="px-4 py-2 text-gray-800 dark:text-gray-300">${ganhador.data_compra}</td>
                                                        </tr>
                                                    `);
                                                });
                                                $('#resultadoSorteio').slideDown();
                                            }, 1000);
                                        } else {
                                            $('#animacaoSorteio').fadeOut();
                                            alert(data.message || 'Erro ao realizar o sorteio');
                                        }
                                    } catch (e) {
                                        $('#animacaoSorteio').fadeOut();
                                        alert('Erro ao processar resposta do servidor');
                                    }
                                },
                                error: function () {
                                    $('#animacaoSorteio').fadeOut();
                                    alert('Erro ao realizar o sorteio');
                                },
                                complete: function () {
                                    clearInterval(intervaloTexto);
                                    btn.html(originalText);
                                    btn.prop('disabled', false);
                                }
                            });
                        }, 4000);
                    });
                }, 100);
                break;

            case 'roletas_raspadinhas':
                titulo.textContent = 'Editar Roletas e Raspadinhas';
                conteudo.innerHTML = `
                <div class="space-y-6">
                    <!-- Configuração da Roleta -->
                    <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg border">
                        <h3 class="text-lg font-semibold mb-4">🎰 Configuração da Roleta</h3>
                        <div class="mb-4">
                            <label for="habilitar_roleta" class="block mb-2 font-medium">Habilitar Roleta</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="habilitar_roleta" name="habilitar_roleta" value="1"
                                    ${campanhaAtual.habilitar_roleta == 1 ? 'checked' : ''}>
                                <div class="toggle-switch-background">
                                    <div class="toggle-switch-handle"></div>
                                </div>
                            </label>
                        </div>
                        
                        <div id="config_roleta" class="space-y-4" style="display: ${campanhaAtual.habilitar_roleta == 1 ? 'block' : 'none'}">
                            <div>
                                <label for="titulo_roleta" class="block mb-2 font-medium">Título da Roleta</label>
                                <input type="text" id="titulo_roleta" name="titulo_roleta"
                                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                    value="${campanhaAtual.titulo_roleta || '🎰 Roleta da Sorte'}"
                                    placeholder="Ex: 🎰 Roleta da Sorte">
                            </div>
                            
                            <div>
                                <label for="descricao_roleta" class="block mb-2 font-medium">Descrição da Roleta</label>
                                <textarea id="descricao_roleta" name="descricao_roleta"
                                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                    placeholder="Ex: Gire a roleta e ganhe prêmios incríveis!">${campanhaAtual.descricao_roleta || ''}</textarea>
                            </div>
                            
                            <div>
                                <label for="itens_roleta" class="block mb-2 font-medium">Itens da Roleta</label>
                                <div id="itens_roleta_container" class="space-y-3">
                                    ${gerarHTMLItensRoleta(campanhaAtual.itens_roleta)}
                                </div>
                                <button type="button" onclick="adicionarItemRoleta()"
                                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    Adicionar Item da Roleta
                                </button>
                            </div>
                        </div>
                    </div>

                   

                    <!-- Configuração da Raspadinha -->
                    <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg border">
                        <h3 class="text-lg font-semibold mb-4">🎫 Configuração da Raspadinha</h3>
                        <div class="mb-4">
                            <label for="habilitar_raspadinha" class="block mb-2 font-medium">Habilitar Raspadinha</label>
                            <label class="toggle-switch">
                                <input type="checkbox" id="habilitar_raspadinha" name="habilitar_raspadinha" value="1"
                                    ${campanhaAtual.habilitar_raspadinha == 1 ? 'checked' : ''}>
                                <div class="toggle-switch-background">
                                    <div class="toggle-switch-handle"></div>
                                </div>
                            </label>
                        </div>
                        
                        <div id="config_raspadinha" class="space-y-4" style="display: ${campanhaAtual.habilitar_raspadinha == 1 ? 'block' : 'none'}">
                            <div>
                                <label for="titulo_raspadinha" class="block mb-2 font-medium">Título da Raspadinha</label>
                                <input type="text" id="titulo_raspadinha" name="titulo_raspadinha"
                                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                    value="${campanhaAtual.titulo_raspadinha || '🎫 Raspadinha da Sorte'}"
                                    placeholder="Ex: 🎫 Raspadinha da Sorte">
                            </div>
                            
                            <div>
                                <label for="descricao_raspadinha" class="block mb-2 font-medium">Descrição da Raspadinha</label>
                                <textarea id="descricao_raspadinha" name="descricao_raspadinha"
                                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                    placeholder="Ex: Raspe e descubra prêmios incríveis!">${campanhaAtual.descricao_raspadinha || ''}</textarea>
                            </div>
                            
                            <div>
                                <label for="itens_raspadinha" class="block mb-2 font-medium">Itens da Raspadinha</label>
                                <div id="itens_raspadinha_container" class="space-y-3">
                                    ${gerarHTMLItensRaspadinha(campanhaAtual.itens_raspadinha)}
                                </div>
                                <button type="button" onclick="adicionarItemRaspadinha()"
                                    class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    Adicionar Item da Raspadinha
                                </button>
                            </div>
                        </div>
                    </div>

                    <button onclick="salvarCampo('roletas_raspadinhas')" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
                </div>
                `;

                // Adicionar eventos para mostrar/esconder campos
                setTimeout(() => {
                    const elHabRoleta = document.getElementById('habilitar_roleta');
                    if (elHabRoleta) {
                        elHabRoleta.addEventListener('change', function () {
                            const cfg = document.getElementById('config_roleta');
                            if (cfg) cfg.style.display = this.checked ? 'block' : 'none';
                        });
                    }

                    // Removido: controle de habilitar pacotes de roleta

                    const elHabRasp = document.getElementById('habilitar_raspadinha');
                    if (elHabRasp) {
                        elHabRasp.addEventListener('change', function () {
                            const cfgR = document.getElementById('config_raspadinha');
                            if (cfgR) cfgR.style.display = this.checked ? 'block' : 'none';
                        });
                    }
                }, 100);
                break;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        fecharModalEdicao();
    }

    function fecharModalCampo() {
        document.getElementById('modalCampo').classList.add('hidden');
        document.getElementById('modalCampo').classList.remove('flex');
        document.getElementById('modalEdicao').classList.remove('hidden');
        document.getElementById('modalEdicao').classList.add('flex');
    }

    function salvarCampo(tipo) {
        let dados = new FormData();
        dados.append('id', campanhaAtual.id);

        switch (tipo) {
            case 'dados':
                dados.append('nome', document.getElementById('nome').value);
                dados.append('descricao', document.getElementById('descricao').value);
                dados.append('preco', document.getElementById('preco').value);
                dados.append('subtitulo', document.getElementById('subtitulo').value);
                dados.append('tipo_sorteio', document.getElementById('tipo_sorteio').value);
                dados.append('layout', document.getElementById('layout').value);
                dados.append('quantidade_numeros', document.getElementById('quantidade_numeros').value);
                dados.append('compra_minima', document.getElementById('compra_minima').value);
                dados.append('compra_maxima', document.getElementById('compra_maxima').value);
                dados.append('status', document.getElementById('status').value);
                dados.append('campanha_privada', document.getElementById('campanha_privada').checked ? 1 : 0);
                dados.append('campanha_destaque', document.getElementById('campanha_destaque').checked ? 1 : 0);
                dados.append('habilitar_ranking', document.getElementById('habilitar_ranking').checked ? 1 : 0);
                dados.append('quantidade_ranking', document.getElementById('quantidade_ranking').value);
                break;

            case 'ranking':
                dados.append('selecionar_top_ganhadores', document.getElementById('selecionar_top_ganhadores').checked ? 1 : 0);

                const filtroPeriodo = document.getElementById('filtro_periodo_top_ganhadores').value;
                let filtroData = {
                    filtro: filtroPeriodo,
                    valor: ''
                };

                if (filtroPeriodo === 'personalizado') {
                    const dataInicial = document.getElementById('data_inicial_personalizada').value;
                    const dataFinal = document.getElementById('data_final_personalizada').value;
                    filtroData.valor = `${dataInicial} até ${dataFinal}`;
                }

                dados.append('filtro_periodo_top_ganhadores', JSON.stringify(filtroData));
                break;

            case 'barra':
                dados.append('habilitar_barra_progresso', document.getElementById('habilitar_barra_progresso').checked ? 1 : 0);
                dados.append('ativar_progresso_manual', document.getElementById('ativar_progresso_manual').checked ? 1 : 0);
                dados.append('porcentagem_barra_progresso', document.getElementById('porcentagem_barra_progresso').value);
                break;

            case 'desconto':
                dados.append('habilitar_pacote_promocional', document.getElementById('habilitar_pacote_promocional').checked ? 1 : 0);

                // Coletar dados dos pacotes normais
                const pacotesNormais = [];
                const containersNormais = document.querySelectorAll('#descontos-container > div');

                containersNormais.forEach(container => {
                    const valorBilhete = container.querySelector('input[name="valor_bilhete_normal[]"]').value;
                    const quantidadeNumeros = container.querySelector('input[name="quantidade_desconto_normal[]"]').value;
                    const valorPacote = container.querySelector('input[name="valor_desconto_normal[]"]').value;
                    const beneficioTipo = (container.querySelector('select[name="beneficio_tipo_normal[]"]') || {}).value || '';
                    const beneficioQtd = (container.querySelector('input[name="beneficio_quantidade_normal[]"]') || {}).value || 0;

                    if (valorBilhete && quantidadeNumeros && valorPacote) {
                        pacotesNormais.push({
                            valor_bilhete: parseFloat(valorBilhete),
                            quantidade_numeros: parseInt(quantidadeNumeros),
                            valor_pacote: parseFloat(valorPacote),
                            beneficio_tipo: beneficioTipo,
                            beneficio_quantidade: parseInt(beneficioQtd) || 0,
                        });
                    }
                });

                // Coletar dados dos pacotes exclusivos
                const pacotesExclusivos = [];
                const containersExclusivos = document.querySelectorAll('#descontos-exclusivos-container > div');

                containersExclusivos.forEach(container => {
                    const valorBilhete = container.querySelector('input[name="valor_bilhete_exclusivo[]"]').value;
                    const quantidadeNumeros = container.querySelector('input[name="quantidade_desconto_exclusivo[]"]').value;
                    const valorPacote = container.querySelector('input[name="valor_desconto_exclusivo[]"]').value;
                    const codigoPacote = container.querySelector('input[name="codigo_desconto_exclusivo[]"]').value || gerarCodigoAleatorio();
                    const beneficioTipo = (container.querySelector('select[name="beneficio_tipo_exclusivo[]"]') || {}).value || '';
                    const beneficioQtd = (container.querySelector('input[name="beneficio_quantidade_exclusivo[]"]') || {}).value || 0;

                    if (valorBilhete && quantidadeNumeros && valorPacote) {
                        pacotesExclusivos.push({
                            valor_bilhete: parseFloat(valorBilhete),
                            quantidade_numeros: parseInt(quantidadeNumeros),
                            valor_pacote: parseFloat(valorPacote),
                            codigo_pacote: codigoPacote,
                            beneficio_tipo: beneficioTipo,
                            beneficio_quantidade: parseInt(beneficioQtd) || 0,
                        });
                    }
                });

                // Converter para JSON e adicionar aos dados
                // dados.append('habilitar_pacote_padrao', document.getElementById('habilitar_pacote_padrao').checked ? 1 : 0);
                dados.append('habilitar_adicao_rapida', document.getElementById('habilitar_adicao_rapida').checked ? 1 : 0);
                dados.append('habilitar_pacote_promocional', document.getElementById('habilitar_pacote_promocional').checked ? 1 : 0);
                dados.append('pacote_promocional', JSON.stringify(pacotesNormais));
                dados.append('habilita_pacote_promocional_exclusivo', document.getElementById('habilita_pacote_promocional_exclusivo').checked ? 1 : 0);
                dados.append('pacotes_exclusivos', JSON.stringify(pacotesExclusivos));
                break;

            case 'ganhadores':
                dados.append('vencedor_sorteio', document.getElementById('vencedor_sorteio').value);
                dados.append('numero_sorteio', document.getElementById('numero_sorteio').value);
                dados.append('data_sorteio', document.getElementById('data_sorteio').value);
                break;

            case 'cotas_premiadas':
                dados.append('quantidade_cotas_premiadas', document.getElementById('quantidade_cotas_premiadas').value);
                dados.append('descricao_cotas_premiadas', document.getElementById('descricao_cotas_premiadas').value);
                dados.append('mostrar_cotas_premiadas', document.getElementById('mostrar_cotas_premiadas').checked ? 1 : 0);
                dados.append('status_cotas_premiadas', document.getElementById('status_cotas_premiadas').value);
                break;

            case 'cotas_dobro':
                dados.append('habilitar_cotas_em_dobro', document.getElementById('habilitar_cotas_em_dobro').checked ? 1 : 0);
                if (document.getElementById('habilitar_cotas_em_dobro').checked) {
                    dados.append('titulo_cotas_dobro', document.getElementById('titulo_cotas_dobro').value);
                    dados.append('subtitulo_cotas_dobro', document.getElementById('subtitulo_cotas_dobro').value);
                }
                break;

            case 'roletas_raspadinhas':
                dados.append('habilitar_roleta', document.getElementById('habilitar_roleta').checked ? 1 : 0);
                dados.append('titulo_roleta', document.getElementById('titulo_roleta').value);
                dados.append('descricao_roleta', document.getElementById('descricao_roleta').value);

                // Coletar dados dos itens da roleta
                const itensRoleta = [];
                const containersItensRoleta = document.querySelectorAll('#itens_roleta_container > div');

                containersItensRoleta.forEach(container => {
                    const nome = container.querySelector('input[name="nome_item_roleta[]"]').value;
                    const status = container.querySelector('select[name="status_item_roleta[]"]').value;

                    if (nome) {
                        itensRoleta.push({
                            nome: nome,
                            status: status
                        });
                    }
                });

                dados.append('itens_roleta', JSON.stringify(itensRoleta));

                // Removido: coleta/envio de pacotes de roleta
                dados.append('habilitar_raspadinha', document.getElementById('habilitar_raspadinha').checked ? 1 : 0);
                dados.append('titulo_raspadinha', document.getElementById('titulo_raspadinha').value);
                dados.append('descricao_raspadinha', document.getElementById('descricao_raspadinha').value);

                // Coletar dados dos itens da raspadinha
                const itensRaspadinha = [];
                const containersItensRaspadinha = document.querySelectorAll('#itens_raspadinha_container > div');

                containersItensRaspadinha.forEach(container => {
                    const nome = container.querySelector('input[name="nome_item_raspadinha[]"]').value;
                    const status = container.querySelector('select[name="status_item_raspadinha[]"]').value;

                    if (nome) {
                        itensRaspadinha.push({
                            nome: nome,
                            status: status
                        });
                    }
                });

                dados.append('itens_raspadinha', JSON.stringify(itensRaspadinha));
                break;

            case 'imagens':
                const imagemPrincipalInput = document.getElementById('imagem_principal');
                const imagemPrincipalFile = imagemPrincipalInput.files[0];  // Pega o primeiro arquivo da imagem principal

                const galeriaFiles = document.getElementById('galeria').files;
                const galeriaAtual = document.getElementById('galeria_imagens_atual').value;
                const caminhoImagemAtual = document.getElementById('caminho_imagem_atual') ? document.getElementById('caminho_imagem_atual').value : '';
                const imagemCapaAtual = document.getElementById('imagem_capa_atual') ? document.getElementById('imagem_capa_atual').value : '';
                const removerPrincipal = document.getElementById('remover_imagem_principal') ? document.getElementById('remover_imagem_principal').value : '0';
                const removerCapa = document.getElementById('remover_imagem_capa') ? document.getElementById('remover_imagem_capa').value : '0';

                dados.append('id', campanhaAtual.id);
                dados.append('tipo', 'imagens');
                dados.append('galeria_imagens_atual', galeriaAtual);
                dados.append('caminho_imagem_atual', removerPrincipal === '1' ? '' : caminhoImagemAtual);
                dados.append('imagem_capa_atual', removerCapa === '1' ? '' : imagemCapaAtual);

                // Adiciona a imagem principal como um arquivo binário
                if (imagemPrincipalFile) {
                    dados.append('imagem_principal', imagemPrincipalFile);
                }

                // Adiciona as imagens da galeria
                for (let i = 0; i < galeriaFiles.length; i++) {
                    dados.append('galeria[]', galeriaFiles[i]);
                }

                // Capa
                const imagemCapaInput = document.getElementById('imagem_capa');
                if (imagemCapaInput && imagemCapaInput.files && imagemCapaInput.files[0]) {
                    dados.append('imagem_capa', imagemCapaInput.files[0]);
                }
                break;

        }

        passouValidacaoCampos = true;
        $("input, textarea, select").each(function (pos, input) {
            if ($(this).prop('required') && $(this).val().trim() == "") {
                texto_span = $(this).parent().find("label").text().replace("*", "").trim();
                console.log(texto_span);

                alert("O Campo " + texto_span + " é obrigatório");
                $(this).focus();
                passouValidacaoCampos = false;
                return passouValidacaoCampos;
            }
        });

        if (!passouValidacaoCampos)
            return false;

        fetch('ajax/editar_campanha.php', {
            method: 'POST',
            body: dados
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Dados atualizados com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao atualizar: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao salvar: ' + error.message);
            });
    }

    function gerarCodigoAleatorio(input) {
        const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let codigo = '';
        for (let i = 0; i < 8; i++) {
            codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
        }
        if (input) {
            input.value = codigo;
        }
        return codigo;
    }

    function calcularValorPacote(input) {
        const container = input.closest('.grid');
        const inputs = container.querySelectorAll('input');
        const valorBilhete = parseFloat(inputs[0].value) || 0;
        const quantidade = parseInt(inputs[1].value) || 0;
        const valorPacoteInput = inputs[2];
        const codigoInput = inputs[3];

        if (valorBilhete && quantidade) {
            const valorTotal = (valorBilhete * quantidade).toFixed(2);
            valorPacoteInput.value = valorTotal;
        }

        // Gera código se estiver vazio
        if (!codigoInput.value) {
            gerarCodigoAleatorio(codigoInput);
        }
    }

    function adicionarDescontoPromocional(tipo) {
        const container = tipo === 'normal' ? document.getElementById('descontos-container') : document.getElementById('descontos-exclusivos-container');
        const novoDesconto = document.createElement('div');
        novoDesconto.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg border border-gray-200';
        novoDesconto.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Valor do Bilhete</label>
                <input type="number" step="0.01" name="valor_bilhete_${tipo}[]" 
                    class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                    onkeyup="calcularValorPacote(this)">
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Quantidade de números</label>
                <input type="number" name="quantidade_desconto_${tipo}[]" 
                    class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                    onkeyup="calcularValorPacote(this)">
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Valor do Pacote</label>
                <input type="number" step="0.01" name="valor_desconto_${tipo}[]" 
                    class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                    readonly>
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Benefício</label>
                <select name="beneficio_tipo_${tipo}[]" class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600">
                    <option value="">Nenhum</option>
                    <option value="roleta">Roleta</option>
                    <option value="raspadinha">Raspadinha</option>
                </select>
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Qtd. Benefício</label>
                <input type="number" min="0" name="beneficio_quantidade_${tipo}[]" class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600">
            </div>
        </div>
        <button type="button" class="mt-3 text-red-500 hover:text-red-700 text-sm"
            onclick="removerDesconto(this)">
            Remover desconto
        </button>
    `;
        container.appendChild(novoDesconto);
    }

    function adicionarDescontoExclusivo(tipo) {
        const container = tipo === 'normal' ? document.getElementById('descontos-container') : document.getElementById('descontos-exclusivos-container');
        const novoDesconto = document.createElement('div');
        novoDesconto.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg border border-gray-200';
        novoDesconto.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="gap-3">
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Valor do Bilhete</label>
                    <input type="number" step="0.01" name="valor_bilhete_${tipo}[]" 
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        onkeyup="calcularValorPacote(this)">
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Quantidade de números</label>
                    <input type="number" name="quantidade_desconto_${tipo}[]" 
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        onkeyup="calcularValorPacote(this)">
                </div>
            </div>
            <div class="gap-3">
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Valor do Pacote</label>
                    <input type="number" step="0.01" name="valor_desconto_${tipo}[]" 
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        readonly>
                </div>
            </div>
             <div class="gap-3">
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Código do Pacote</label>
                    <input type="text" name="codigo_desconto_${tipo}[]" 
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        ">
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Benefício</label>
                <select name="beneficio_tipo_${tipo}[]" class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600">
                    <option value="">Nenhum</option>
                    <option value="roleta">Roleta</option>
                    <option value="raspadinha">Raspadinha</option>
                </select>
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Qtd. Benefício</label>
                <input type="number" min="0" name="beneficio_quantidade_${tipo}[]" class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600">
            </div>
        </div>
        <button type="button" class="mt-3 text-red-500 hover:text-red-700 text-sm"
            onclick="removerDesconto(this)">
            Remover desconto
        </button>
    `;
        container.appendChild(novoDesconto);
    }


    function removerDesconto(button) {
        const descontoElement = button.closest('.bg-white.dark\\:bg-gray-800');
        if (descontoElement) {
            descontoElement.remove();
        }
    }

    function adicionarPacoteExistentePromocional(pacote, tipo) {
        const container = tipo === 'normal' ? document.getElementById('descontos-container') : document.getElementById('descontos-exclusivos-container');
        const novoDesconto = document.createElement('div');
        novoDesconto.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg border border-gray-200';
        novoDesconto.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div class="gap-3">
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Valor do Bilhete</label>
                    <input type="number" step="0.01" name="valor_bilhete_${tipo}[]" 
                        value="${pacote.valor_bilhete || ''}"
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        onkeyup="calcularValorPacote(this)">
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Quantidade de números</label>
                    <input type="number" name="quantidade_desconto_${tipo}[]" 
                        value="${pacote.quantidade_numeros || ''}"
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        onkeyup="calcularValorPacote(this)">
                </div>
            </div>
            <div class="gap-3">
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Valor do Pacote</label>
                    <input type="number" step="0.01" name="valor_desconto_${tipo}[]" 
                        value="${pacote.valor_pacote || ''}"
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        readonly>
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Código do Pacote</label>
                <input type="text" name="codigo_desconto_${tipo}[]" 
                    value="${pacote.codigo_pacote || ''}"
                    class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600">
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Benefício</label>
                <select name="beneficio_tipo_${tipo}[]" class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600">
                    <option value="" ${!pacote.beneficio_tipo ? 'selected' : ''}>Nenhum</option>
                    <option value="roleta" ${pacote.beneficio_tipo === 'roleta' ? 'selected' : ''}>Roleta</option>
                    <option value="raspadinha" ${pacote.beneficio_tipo === 'raspadinha' ? 'selected' : ''}>Raspadinha</option>
                </select>
            </div>
            <div>
                <label class="text-sm text-gray-600 dark:text-gray-400">Qtd. Benefício</label>
                <input type="number" min="0" name="beneficio_quantidade_${tipo}[]" value="${pacote.beneficio_quantidade || 0}"
                    class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600">
            </div>
        </div>
        <button type="button" class="mt-3 text-red-500 hover:text-red-700 text-sm"
            onclick="removerDesconto(this)">
            Remover desconto
        </button>
    `;
        container.appendChild(novoDesconto);
    }

    function adicionarPacoteExistenteExclusivo(pacote, tipo) {
        const container = tipo === 'normal' ? document.getElementById('descontos-container') : document.getElementById('descontos-exclusivos-container');
        const novoDesconto = document.createElement('div');
        novoDesconto.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg border border-gray-200';
        novoDesconto.innerHTML = `
        <div class="grid space-y-3">
            <div class="gap-3">
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Valor do Bilhete</label>
                    <input type="number" step="0.01" name="valor_bilhete_${tipo}[]" 
                        value="${pacote.valor_bilhete || ''}"
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        onkeyup="calcularValorPacote(this)">
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Quantidade de números</label>
                    <input type="number" name="quantidade_desconto_${tipo}[]" 
                        value="${pacote.quantidade_numeros || ''}"
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        onkeyup="calcularValorPacote(this)">
                </div>
            </div>
            <div class="gap-3">
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Valor do Pacote</label>
                    <input type="number" step="0.01" name="valor_desconto_${tipo}[]" 
                        value="${pacote.valor_pacote || ''}"
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        readonly>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Código do Pacote</label>
                    <input type="text" name="codigo_desconto_${tipo}[]" 
                        value="${pacote.codigo_pacote || ''}"
                        class="mt-1 w-full bg-gray-50 dark:bg-[#3F3F46] text-gray-800 dark:text-white p-2 rounded border border-gray-300 dark:border-gray-600"
                        >
                </div>
            </div>
        </div>
        <button type="button" class="mt-3 text-red-500 hover:text-red-700 text-sm"
            onclick="removerDesconto(this)">
            Remover desconto
        </button>
    `;
        container.appendChild(novoDesconto);
    }

    $(document).ready(function () {
        $(document).on("click", "#habilitar_pacote_promocional", function () {
            validaDescontoPromocial();
        });

        $(document).on("click", "#habilita_pacote_promocional_exclusivo", function () {
            validaDescontoExclusivo();
        });

        $(document).on("click", "#habilitar_ranking", function () {
            validaRanking();
        });

        $(document).on("click", '#habilitar_barra_progresso', function () {
            validaProgresso();
        });

        $(document).on("click", "#ativar_progresso_manual", function () {
            validaProgressoManual();
        });
        validaProgressoManual();
    });

    function validaDescontoPromocial(velocidade) {
        if (velocidade == undefined) {
            if ($("#habilitar_pacote_promocional").is(":checked"))
                $(".pacote_promocional").show("fast");
            else
                $(".pacote_promocional").hide("slow");
        }
        else {
            if ($("#habilitar_pacote_promocional").is(":checked"))
                $(".pacote_promocional").show(velocidade);
            else
                $(".pacote_promocional").hide(velocidade);
        }
    }

    function validaDescontoExclusivo(velocidade) {
        if (velocidade == undefined) {
            if ($("#habilita_pacote_promocional_exclusivo").is(":checked"))
                $("#pacote_exclusivo").show("fast");
            else
                $("#pacote_exclusivo").hide("slow");
        }
        else {
            if ($("#habilita_pacote_promocional_exclusivo").is(":checked"))
                $("#pacote_exclusivo").show(velocidade);
            else
                $("#pacote_exclusivo").hide(velocidade);
        }
    }

    function validaRanking(velocidade) {
        if (velocidade == undefined) {
            if ($("#habilitar_ranking").is(":checked"))
                $("#div_quantidade_ranking").show("fast");
            else
                $("#div_quantidade_ranking").hide("slow");
        }
        else {
            if ($("#habilitar_ranking").is(":checked"))
                $("#div_quantidade_ranking").show(velocidade);
            else
                $("#div_quantidade_ranking").hide(velocidade);
        }
    }

    function validaProgresso() {
        if ($("#habilitar_barra_progresso").is(':checked')) {
            $('#barra_progresso_ativa').show("fast");
            validaProgressoManual();
        } else {
            $('#barra_progresso_ativa').hide("slow");
            $('#div_progresso_manual').hide("slow");
        }
    }

    function validaProgressoManual() {
        if ($("#ativar_progresso_manual").is(":checked") && $("#habilitar_barra_progresso").is(":checked")) {
            $("#div_progresso_manual").show("fast");
        } else {
            $("#div_progresso_manual").hide("slow");
        }
    }

    function consultarCota() {
        const numeroCota = document.getElementById('numero_cota').value;
        const resultadoDiv = document.getElementById('resultado_consulta');

        if (!numeroCota) {
            alert('Por favor, digite um número de cota para consultar.');
            return;
        }

        // Fazer a requisição para consultar a cota
        fetch('ajax/consultar_cota.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `campanha_id=${campanhaAtual.id}&numero_cota=${numeroCota}`
        })
            .then(response => response.json())
            .then(data => {
                resultadoDiv.classList.remove('hidden');

                if (data.success) {
                    if (data.disponivel) {
                        resultadoDiv.className = 'mt-4 p-4 rounded-md bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200';
                        resultadoDiv.innerHTML = `<p>✅ A cota ${numeroCota} está disponível!</p>`;
                    } else {
                        resultadoDiv.className = 'mt-4 p-4 rounded-md bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200';
                        resultadoDiv.innerHTML = `
                        <p>⚠️ A cota ${numeroCota} já foi Comprada</p>
                        <p class="mt-2"><strong>Comprador:</strong> ${data.comprador || 'Não informado'}</p>
                        <p><strong>Data da compra:</strong> ${data.data_compra || 'Não informada'}</p>
                        <p><strong>Status:</strong> ${data.status || 'Não informado'}</p>
                    `;
                    }
                } else {
                    resultadoDiv.className = 'mt-4 p-4 rounded-md bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
                    resultadoDiv.innerHTML = `<p>❌ Erro ao consultar a cota: ${data.message}</p>`;
                }
            })
            .catch(error => {
                resultadoDiv.classList.remove('hidden');
                resultadoDiv.className = 'mt-4 p-4 rounded-md bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200';
                resultadoDiv.innerHTML = `<p>❌ Erro ao consultar a cota: ${error.message}</p>`;
            });
    }

    function animarNumeros(callback) {
        let contador = 0;
        const maxIteracoes = 30;
        const intervalo = 100;

        function gerarNumeroAleatorio() {
            return Math.floor(Math.random() * 999) + 1;
        }

        function atualizarNumero() {
            const numero = gerarNumeroAleatorio();
            $('#numeroSorteio').text(numero.toString().padStart(3, '0'));
            $('#numeroSorteio').addClass('number-animation');

            setTimeout(() => {
                $('#numeroSorteio').removeClass('number-animation');
            }, 200);

            contador++;

            if (contador < maxIteracoes) {
                setTimeout(atualizarNumero, intervalo);
            } else {
                if (callback) callback();
            }
        }

        atualizarNumero();
    }

    function validarCotasPremiadas(input) {
        if (/\s/.test(input.value)) {
            input.value = input.value.replace(/\s+/g, ''); // opcional: remove depois do alerta
        }
    }

    function gerarHTMLGruposPremios(premiosJson) {
        if (!premiosJson) return '';

        try {
            const grupos = JSON.parse(premiosJson);
            if (!Array.isArray(grupos)) {
                console.warn('premiosJson não é um array válido:', premiosJson);
                return '';
            }

            return grupos.map((grupo, index) => {
                // Validar estrutura do grupo
                if (!grupo || typeof grupo !== 'object' || !Array.isArray(grupo.cotas) || !grupo.premio) {
                    console.warn('Grupo inválido encontrado:', grupo);
                    return '';
                }

                return `
                <div class="bg-blue-50 dark:bg-blue-900 p-3 rounded border">
                    <div class="font-medium text-blue-800 dark:text-blue-200 mb-2">
                        Grupo ${index + 1}: ${grupo.premio}
                    </div>
                    <div class="flex flex-wrap gap-1">
                            ${(() => {
                                const largura = String(campanhaAtual.quantidade_numeros || 1).length;
                                const pad = (n) => String(parseInt(String(n).trim()||'0',10)).padStart(largura, '0');
                                return grupo.cotas.map(cota => `<span class="inline-block bg-blue-600 text-white px-2 py-1 rounded text-sm">${pad(cota)}</span>`).join('');
                            })()}
                    </div>
                    <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                        ${grupo.cotas.length} cotas neste grupo
                    </div>
                </div>
            `;
            }).join('');
        } catch (e) {
            console.error('Erro ao processar grupos de prêmios:', e, 'JSON:', premiosJson);
            return '';
        }
    }

    function gerarCotasPremiadas() {
        const quantidade = document.getElementById('quantidade_cotas_premiadas').value;
        const premio = document.getElementById('premio_cotas_premiadas').value;
        const campanhaId = campanhaAtual.id;

        if (!quantidade || quantidade < 1) {
            alert('Por favor, insira uma quantidade válida maior que 0.');
            return;
        }

        if (!premio || premio.trim() === '') {
            alert('Por favor, insira um prêmio para este grupo de cotas.');
            return;
        }

        // Mostrar loading
        const btnGerar = document.querySelector('button[onclick="gerarCotasPremiadas()"]');
        const textoOriginal = btnGerar.textContent;
        btnGerar.textContent = 'Gerando...';
        btnGerar.disabled = true;

        // Fazer requisição para gerar cotas premiadas
        fetch('ajax/gerar_cotas_premiadas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `campanha_id=${campanhaId}&quantidade=${quantidade}&premio=${encodeURIComponent(premio)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar o contador de cotas
                    const contadorElement = document.querySelector('#grupos_premios').nextElementSibling;
                    if (contadorElement) {
                        contadorElement.innerHTML = `Total de cotas premiadas: ${data.total_cotas}`;
                    }

                    // Atualizar a visualização dos grupos de prêmios
                        const gruposElement = document.getElementById('grupos_premios');
                    if (gruposElement && data.grupos_premios) {
                        gruposElement.innerHTML = data.grupos_premios.map((grupo, index) => `
                    <div class="bg-blue-50 dark:bg-blue-900 p-3 rounded border">
                        <div class="font-medium text-blue-800 dark:text-blue-200 mb-2">
                            Grupo ${index + 1}: ${grupo.premio}
                        </div>
                        <div class="flex flex-wrap gap-1">
                            ${(() => {
                                const largura = String(campanhaAtual.quantidade_numeros || 1).length;
                                const pad = (n) => String(parseInt(String(n).trim()||'0',10)).padStart(largura, '0');
                                return grupo.cotas.map(cota => `<span class="inline-block bg-blue-600 text-white px-2 py-1 rounded text-sm">${pad(cota)}</span>`).join('');
                            })()}
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                            ${grupo.cotas.length} cotas neste grupo
                        </div>
                    </div>
                `).join('');
                    }

                    // Atualizar o valor no campo hidden para salvar
                    if (document.getElementById('cotas_premiadas_hidden')) {
                        document.getElementById('cotas_premiadas_hidden').value = data.cotas.join(',');
                    }

                    // Mostrar mensagem com informações detalhadas
                    let mensagem = `Cotas premiadas geradas com sucesso!\n\n`;
                    mensagem += `Novas cotas adicionadas: ${data.novas_cotas.length}\n`;
                    mensagem += `Total de cotas premiadas: ${data.total_cotas}\n`;
                    mensagem += `Prêmio associado: ${data.premio}\n`;
                    if (data.novas_cotas.length > 0) {
                        mensagem += `\nNovas cotas: ${data.novas_cotas.join(', ')}`;
                    }

                    alert(mensagem);
                } else {
                    alert('Erro ao gerar cotas premiadas: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erro ao gerar cotas premiadas: ' + error.message);
            })
            .finally(() => {
                // Restaurar botão
                btnGerar.textContent = textoOriginal;
                btnGerar.disabled = false;
            });
    }

    function limparCotasPremiadas() {
        const campanhaId = campanhaAtual.id;

        if (!confirm('Tem certeza que deseja limpar todas as cotas premiadas? Esta ação não pode ser desfeita.')) {
            return;
        }

        // Mostrar loading
        const btnLimpar = document.querySelector('button[onclick="limparCotasPremiadas()"]');
        const textoOriginal = btnLimpar.textContent;
        btnLimpar.textContent = 'Limpando...';
        btnLimpar.disabled = true;

        // Fazer requisição para limpar cotas premiadas
        fetch('ajax/limpar_cotas_premiadas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `campanha_id=${campanhaId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar o contador de cotas
                    const contadorElement = document.querySelector('#grupos_premios').nextElementSibling;
                    if (contadorElement) {
                        contadorElement.innerHTML = 'Total de cotas premiadas: 0';
                    }

                    // Limpar a visualização dos grupos de prêmios
                    const gruposElement = document.getElementById('grupos_premios');
                    if (gruposElement) {
                        gruposElement.innerHTML = '';
                    }

                    // Atualizar o valor no campo hidden para salvar
                    if (document.getElementById('cotas_premiadas_hidden')) {
                        document.getElementById('cotas_premiadas_hidden').value = '';
                    }

                    alert('Todas as cotas premiadas foram removidas com sucesso!');
                } else {
                    alert('Erro ao limpar cotas premiadas: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erro ao limpar cotas premiadas: ' + error.message);
            })
            .finally(() => {
                // Restaurar botão
                btnLimpar.textContent = textoOriginal;
                btnLimpar.disabled = false;
            });
    }

    function corrigirGruposPremiados() {
        const campanhaId = campanhaAtual.id;

        if (!confirm('Esta função irá tentar corrigir grupos corrompidos. Deseja continuar?')) {
            return;
        }

        // Mostrar loading
        const btnCorrigir = document.querySelector('button[onclick="corrigirGruposPremiados()"]');
        const textoOriginal = btnCorrigir.textContent;
        btnCorrigir.textContent = 'Corrigindo...';
        btnCorrigir.disabled = true;

        // Fazer requisição para corrigir grupos
        fetch('ajax/corrigir_grupos_premiados.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `campanha_id=${campanhaId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar a visualização dos grupos
                    const gruposElement = document.getElementById('grupos_premios');
                    if (gruposElement && data.grupos_premios) {
                        gruposElement.innerHTML = data.grupos_premios.map((grupo, index) => `
                    <div class="bg-blue-50 dark:bg-blue-900 p-3 rounded border">
                        <div class="font-medium text-blue-800 dark:text-blue-200 mb-2">
                            Grupo ${index + 1}: ${grupo.premio}
                        </div>
                        <div class="flex flex-wrap gap-1">
                            ${grupo.cotas.map(cota =>
                            `<span class="inline-block bg-blue-600 text-white px-2 py-1 rounded text-sm">${cota}</span>`
                        ).join('')}
                        </div>
                        <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                            ${grupo.cotas.length} cotas neste grupo
                        </div>
                    </div>
                `).join('');
                    }

                    // Atualizar o contador
                    const contadorElement = document.querySelector('#grupos_premios').nextElementSibling;
                    if (contadorElement) {
                        contadorElement.innerHTML = `Total de cotas premiadas: ${data.total_cotas}`;
                    }

                    alert('Grupos corrigidos com sucesso!');
                } else {
                    alert('Erro ao corrigir grupos: ' + data.message);
                }
            })
            .catch(error => {
                alert('Erro ao corrigir grupos: ' + error.message);
            })
            .finally(() => {
                // Restaurar botão
                btnCorrigir.textContent = textoOriginal;
                btnCorrigir.disabled = false;
            });
    }

    // Funções para gerenciar pacotes de roleta
    function gerarHTMLPacotesRoleta(pacotesJson) {
        if (!pacotesJson) return '';

        try {
            const pacotes = JSON.parse(pacotesJson);
            if (!Array.isArray(pacotes)) {
                return '';
            }

            return pacotes.map((pacote, index) => `
            <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow">
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Valor do Pacote
                        </label>
                        <input type="number" step="0.01" name="valor_pacote_roleta[]" 
                            value="${pacote.valor_pacote || ''}"
                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Quantidade de Giros
                        </label>
                        <input type="number" name="quantidade_giros_roleta[]" 
                            value="${pacote.quantidade_giros || ''}"
                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Código do Pacote
                        </label>
                        <input type="text" name="codigo_pacote_roleta[]" 
                            value="${pacote.codigo_pacote || ''}"
                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Destaque
                        </label>
                        <select name="destaque_pacote_roleta[]" 
                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                            <option value="0" ${pacote.destaque == '0' ? 'selected' : ''}>Normal</option>
                            <option value="1" ${pacote.destaque == '1' ? 'selected' : ''}>Mais Popular</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerPacoteRoleta(this)">
                    Remover Pacote
                </button>
            </div>
        `).join('');
        } catch (e) {
            console.error('Erro ao processar pacotes de roleta:', e);
            return '';
        }
    }

    function adicionarPacoteRoleta() {
        // Removido: pacotes_roleta_container
        const novoPacote = document.createElement('div');
        novoPacote.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg shadow';
        novoPacote.innerHTML = `
        <div class="grid grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Valor do Pacote
                </label>
                <input type="number" step="0.01" name="valor_pacote_roleta[]" 
                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Quantidade de Giros
                </label>
                <input type="number" name="quantidade_giros_roleta[]" 
                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Código do Pacote
                </label>
                <input type="text" name="codigo_pacote_roleta[]" 
                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Destaque
                </label>
                <select name="destaque_pacote_roleta[]" 
                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                    <option value="0">Normal</option>
                    <option value="1">Mais Popular</option>
                </select>
            </div>
        </div>
        <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerPacoteRoleta(this)">
            Remover Pacote
        </button>
    `;
        container.appendChild(novoPacote);
    }

    function removerPacoteRoleta(button) {
        const pacoteElement = button.closest('.bg-white.dark\\:bg-gray-800');
        if (pacoteElement) {
            pacoteElement.remove();
        }
    }

    // Funções para gerenciar itens da raspadinha
    function gerarHTMLItensRaspadinha(itensJson) {
        if (!itensJson) return '';

        try {
            const itens = JSON.parse(itensJson);
            if (!Array.isArray(itens)) {
                return '';
            }

            return itens.map((item, index) => `
            <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow border">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Nome do Item
                        </label>
                        <input type="text" name="nome_item_raspadinha[]" 
                            value="${item.nome || ''}"
                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                            placeholder="Ex: R$ 100,00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Status
                        </label>
                        <select name="status_item_raspadinha[]" 
                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                            <option value="disponivel" ${item.status == 'disponivel' ? 'selected' : ''}>Disponível</option>
                            <option value="bloqueado" ${item.status == 'bloqueado' ? 'selected' : ''}>Bloqueado</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerItemRaspadinha(this)">
                    Remover Item
                </button>
            </div>
        `).join('');
        } catch (e) {
            console.error('Erro ao processar itens da raspadinha:', e);
            return '';
        }
    }

    function adicionarItemRaspadinha() {
        const container = document.getElementById('itens_raspadinha_container');
        const novoItem = document.createElement('div');
        novoItem.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg shadow border';
        novoItem.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Nome do Item
                </label>
                <input type="text" name="nome_item_raspadinha[]" 
                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                    placeholder="Ex: R$ 100,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Status
                </label>
                <select name="status_item_raspadinha[]" 
                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                    <option value="disponivel">Disponível</option>
                    <option value="bloqueado">Bloqueado</option>
                </select>
            </div>
        </div>
        <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerItemRaspadinha(this)">
            Remover Item
        </button>
    `;
        container.appendChild(novoItem);
    }

    function removerItemRaspadinha(button) {
        const itemElement = button.closest('.bg-white.dark\\:bg-gray-800');
        if (itemElement) {
            itemElement.remove();
        }
    }

    // Funções para gerenciar itens da roleta
    function gerarHTMLItensRoleta(itensJson) {
        if (!itensJson) return '';

        try {
            const itens = JSON.parse(itensJson);
            if (!Array.isArray(itens)) {
                return '';
            }

            return itens.map((item, index) => `
            <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow border">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Nome do Item
                        </label>
                        <input type="text" name="nome_item_roleta[]" 
                            value="${item.nome || ''}"
                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                            placeholder="Ex: R$ 50,00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Status
                        </label>
                        <select name="status_item_roleta[]" 
                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                            <option value="disponivel" ${item.status == 'disponivel' ? 'selected' : ''}>Disponível</option>
                            <option value="bloqueado" ${item.status == 'bloqueado' ? 'selected' : ''}>Bloqueado</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerItemRoleta(this)">
                    Remover Item
                </button>
            </div>
        `).join('');
        } catch (e) {
            console.error('Erro ao processar itens da roleta:', e);
            return '';
        }
    }

    function adicionarItemRoleta() {
        const container = document.getElementById('itens_roleta_container');
        const novoItem = document.createElement('div');
        novoItem.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg shadow border';
        novoItem.innerHTML = `
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Nome do Item
                </label>
                <input type="text" name="nome_item_roleta[]" 
                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                    placeholder="Ex: R$ 50,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Status
                </label>
                <select name="status_item_roleta[]" 
                    class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                    <option value="disponivel">Disponível</option>
                    <option value="bloqueado">Bloqueado</option>
                </select>
            </div>
        </div>
        <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerItemRoleta(this)">
            Remover Item
        </button>
    `;
        container.appendChild(novoItem);
    }

    function removerItemRoleta(button) {
        const itemElement = button.closest('.bg-white.dark\\:bg-gray-800');
        if (itemElement) {
            itemElement.remove();
        }
    }

</script>
<script src="js/funcoes_imagens.js"></script>
</body>

</html>