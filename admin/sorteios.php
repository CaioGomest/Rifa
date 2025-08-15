<?php
require_once("header.php");
require_once("../functions/functions_pedidos.php");
require_once("../functions/functions_clientes.php");


?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Sorteios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 text-gray-800 dark:bg-[#18181B] dark:text-white">
    <div class="flex min-h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-2 sm:p-6 overflow-y-auto max-h-screen">
            <div class="container mx-auto">
                <div class="flex flex-row justify-between items-center mb-6 gap-4">
                    <h1 class="text-2xl font-bold">Sorteios</h1>
                </div>

                <!-- Formulário de Sorteio -->
                <div class="bg-white dark:bg-[#27272A] rounded-lg shadow p-4 sm:p-6 mb-6">
                    <form id="formSorteio" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Período de Data
                            </label>
                            <input type="date" name="data_inicio" id="data_inicio" 
                                   class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Quantidade de Sorteados
                            </label>
                            <input type="number" name="qtd_sortear" id="qtd_sortear" min="1"
                                   class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tipo de Sorteio
                            </label>
                            <select name="tipo" id="tipo" 
                                    class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                <option value="">SELECIONE</option>
                                <option value="por_pedido">Por Pedido</option>
                                <option value="soma_pedidos">Soma dos Pedidos</option>
                                <option value="maior_cota">Maior Cota</option>
                                <option value="menor_cota">Menor Cota</option>
                                <option value="qtd_pedidos">Qtd. Pedidos</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Quantidade Mínima de Cotas
                            </label>
                            <input type="number" name="qtd_cotas" id="qtd_cotas" min="0"
                                   class="w-full border rounded-md p-2 dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                        </div>

                        <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                            <button type="button" id="btnSortear"
                                    class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 flex items-center">
                                <i class="fas fa-random mr-2"></i>
                                Realizar Sorteio
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Animação do Sorteio -->
                <div id="animacaoSorteio" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50" style="display: none;">
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
                <div id="resultadoSorteio" class="bg-white dark:bg-[#27272A] rounded-lg shadow overflow-hidden" style="display: none;">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold">Resultado do Sorteio</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-[#3F3F46]">
                                <tr>
                                    <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Nome</th>
                                    <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Telefone</th>
                                    <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Qts Cotas</th>
                                    <th class="px-4 py-2 text-left text-gray-700 dark:text-gray-300">Data da Compra</th>
                                </tr>
                            </thead>
                            <tbody id="resultadoSorteioBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
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
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
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

    <script>
    $(document).ready(function() {
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

        $('#btnSortear').click(function() {
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
                $('#infoSorteio').fadeOut(200, function() {
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
                    data: $('#formSorteio').serialize(),
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                // Última animação antes de mostrar resultados
                                $('#textoSorteio').text('Ganhadores Encontrados!');
                                setTimeout(() => {
                                    $('#animacaoSorteio').fadeOut();
                                    $('#resultadoSorteioBody').html('');
                                    data.ganhadores.forEach(function(ganhador) {
                                        $('#resultadoSorteioBody').append(`
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-4 py-2">${ganhador.nome}</td>
                                                <td class="px-4 py-2">
                                                    ${ganhador.telefone}
                                                    <a href="https://wa.me/${ganhador.telefone}" target="_blank" class="text-green-500 hover:text-green-700 ml-2">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                </td>
                                                <td class="px-4 py-2">${ganhador.cotas}</td>
                                                <td class="px-4 py-2">${ganhador.data_compra}</td>
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
                    error: function() {
                        $('#animacaoSorteio').fadeOut();
                        alert('Erro ao realizar o sorteio');
                    },
                    complete: function() {
                        clearInterval(intervaloTexto);
                        btn.html(originalText);
                        btn.prop('disabled', false);
                    }
                });
            }, 4000); // Aguarda 4 segundos antes de fazer a requisição real
        });
    });
    </script>

</body>
</html> 