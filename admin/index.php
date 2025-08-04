<!DOCTYPE html>
<html lang="pt-BR">
<?php
require("header.php");
require("../functions/functions_clientes.php");
date_default_timezone_set('America/Sao_Paulo');


// Função auxiliar para definir datas do filtro
function getDatasFiltro($filtro, $dataInicio = null, $dataFim = null)
{
    $hoje = date('Y-m-d');
    $agora = date('Y-m-d 23:59:59');

    
    switch($filtro) {
        case 'hoje':
            return [
                'inicio' => $hoje . ' 00:00:00',
                'fim' => $agora
            ];
        case 'ontem':
            $ontem = date('Y-m-d', strtotime('-1 day'));
            return [
                'inicio' => $ontem . ' 00:00:00',
                'fim' => $ontem . ' 23:59:59'
            ];
        case '7dias':
            return [
                'inicio' => date('Y-m-d', strtotime('-7 days')) . ' 00:00:00',
                'fim' => $agora
            ];
        case '30dias':
            return [
                'inicio' => date('Y-m-d', strtotime('-30 days')) . ' 00:00:00',
                'fim' => $agora
            ];
        case 'personalizado':
            if ($dataInicio && $dataFim) {
                return [
                    'inicio' => $dataInicio . ' 00:00:00',
                    'fim' => $dataFim . ' 23:59:59'
                ];
            }
            break;
        case 'geral':
        default:
            return [
                'inicio' => null,
                'fim' => null
            ];
    }
}

// Processar o filtro selecionado
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'hoje';
$dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-d', strtotime('-1 day'));
$dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');

$datas = getDatasFiltro($filtro, $dataInicio, $dataFim);

// Buscar dados com o filtro
$campanhas = listaCampanhas($conn, NULL, NULL, NULL, NULL, NULL, NULL, $datas['inicio'], $datas['fim']);
$clientes = listaClientes($conn, NULL, NULL, NULL, NULL, NULL, $datas['inicio'], $datas['fim']);
$pedidos = listaPedidos($conn, NULL, NULL, NULL, $datas['inicio'], $datas['fim']);

// Processar dados para os gráficos
$dadosCampanhas = [];
$dadosClientes = [];
$dadosPedidos = [];
$dadosFaturamento = [];
$dadosFaturamentoHora = [];

// Processar campanhas
$total_acumulado_campanhas = 0;
foreach ($campanhas as $campanha) {
    $data = date('Y-m-d', strtotime($campanha['data_criacao']));
    if (!isset($dadosCampanhas[$data])) {
        $dadosCampanhas[$data] = 0;
    }
    $dadosCampanhas[$data]++;
    $total_acumulado_campanhas += $dadosCampanhas[$data];
}

// Processar clientes
$total_acumulado_clientes = 0;
foreach ($clientes as $cliente) {
    $data = date('Y-m-d', strtotime($cliente['data_criacao']));
    if (!isset($dadosClientes[$data])) {
        $dadosClientes[$data] = 0;
    }
    $dadosClientes[$data]++;
    $total_acumulado_clientes += $dadosClientes[$data];
}

// Processar pedidos e faturamento
$total_faturamento = 0;
$total_pedidos = 0;
foreach ($pedidos as $pedido) {
    $data = date('Y-m-d', strtotime($pedido['data_criacao']));
    if (!isset($dadosPedidos[$data])) {
        $dadosPedidos[$data] = 0;
        $dadosFaturamento[$data] = 0;
    }
    $dadosPedidos[$data]++;
    $total_pedidos += $dadosPedidos[$data];

    if($pedido['status'] == 1)
        $dadosFaturamento[$data] += $pedido['valor_total'];

    if($pedido['status'] == 1)
        $total_faturamento += $pedido['valor_total'];

    $hora = date('H:00', strtotime($pedido['data_criacao']));
    if (!isset($dadosFaturamentoHora[$hora])) {
        $dadosFaturamentoHora[$hora] = 0;
    }

    if($pedido['status'] == 1)
        $dadosFaturamentoHora[$hora] += $pedido['valor_total'];
}

// Ordenar as datas
ksort($dadosCampanhas);
ksort($dadosClientes);
ksort($dadosPedidos);
ksort($dadosFaturamento);
ksort($dadosFaturamentoHora);

// Converter para o formato do gráfico com valores acumulados
$dadosCampanhasGrafico = [];
$dadosClientesGrafico = [];
$dadosPedidosGrafico = [];
$dadosFaturamentoGrafico = [];
$dadosFaturamentoHoraGrafico = [];

$acumulado_campanhas = 0;
foreach ($dadosCampanhas as $data => $total) {
    $dadosCampanhasGrafico[] = [
        'data' => $data,
        'total' => $total
    ];
}

$acumulado_clientes = 0;
foreach ($dadosClientes as $data => $total) {
    $dadosClientesGrafico[] = [
        'data' => $data,
        'total' => $total
    ];
}

$acumulado_pedidos = 0;
foreach ($dadosPedidos as $data => $total) {
    $dadosPedidosGrafico[] = [
        'data' => $data,
        'total' => $total
    ];
}

$acumulado_faturamento = 0;
foreach ($dadosFaturamento as $data => $total) {
    $dadosFaturamentoGrafico[] = [
        'data' => $data,
        'total' => $total
    ];
}

foreach ($dadosFaturamentoHora as $hora => $total) {
    $dadosFaturamentoHoraGrafico[] = [
        'hora' => $hora,
        'total' => $total
    ];
}
    
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- amCharts Scripts -->
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <style>
        .chart-container {
            width: 100%;
            height: 300px;
        }
        @media (min-width: 640px) {
            .chart-container {
                height: 400px;
            }
        }
    </style>
</head>
<style>
canvas a{
    opacity: 0 !important;
}
</style>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="flex h-screen">
        <?php require("sidebar.php") ?>

        <main class="flex-1 p-6">
            <div class="mb-6">
                <h2 class="text-2xl font-bold">Dashboard</h2>
            </div>

            <div class="mb-6 bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                <form id="filtroForm" class="flex flex-col sm:flex-row items-start sm:items-end space-y-4 sm:space-y-0 sm:space-x-6">
                    <div class="w-full sm:w-1/4">
                        <label class="block text-sm font-medium mb-2">Período</label>
                        <select name="filtro" id="filtroSelect" class="cursor-pointer w-full h-10 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 focus:ring-2 focus:ring-blue-500">
                            <option value="hoje" <?php echo $filtro == 'hoje' ? 'selected' : ''; ?>>Hoje</option>
                            <option value="ontem" <?php echo $filtro == 'ontem' ? 'selected' : ''; ?>>Ontem</option>
                            <option value="7dias" <?php echo $filtro == '7dias' ? 'selected' : ''; ?>>Últimos 7 dias</option>
                            <option value="30dias" <?php echo $filtro == '30dias' ? 'selected' : ''; ?>>Últimos 30 dias</option>
                            <option value="personalizado" <?php echo $filtro == 'personalizado' ? 'selected' : ''; ?>>Personalizado</option>
                            <option value="geral" <?php echo $filtro == 'geral' ? 'selected' : ''; ?>>Geral</option>
                        </select>
                    </div>
                    
                    <div id="dataPeriodo" class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 w-full" style="display: <?php echo $filtro == 'personalizado' ? 'flex' : 'none'; ?>">
                        <div class="w-full sm:w-1/3">
                            <label class="block text-sm font-medium mb-2">Data Início</label>
                            <input type="date" name="data_inicio" value="<?php echo $dataInicio; ?>" 
                                   class="w-full h-10 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="w-full sm:w-1/3">
                            <label class="block text-sm font-medium mb-2">Data Fim</label>
                            <input type="date" name="data_fim" value="<?php echo $dataFim; ?>" 
                                   class="w-full h-10 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="w-full sm:w-auto">
                        <button type="submit" class="w-full sm:w-auto h-10 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 sm:gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow transition-transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Faturamento Total</h3>
                    <p class="text-xl sm:text-2xl font-bold whitespace-nowrap overflow-hidden text-ellipsis">R$ <?php echo number_format($total_faturamento, 2, ',', '.'); ?></p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow transition-transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Pedidos Aprovados</h3>
                    <p class="text-xl sm:text-2xl font-bold whitespace-nowrap"><?php echo count(array_filter($pedidos, function($pedido) { return $pedido['status'] == 1; })); ?></p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow transition-transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Ticket Médio</h3>
                    <p class="text-xl sm:text-2xl font-bold whitespace-nowrap overflow-hidden text-ellipsis">R$ <?php echo count($pedidos) > 0 ? number_format($total_faturamento / count($pedidos), 2, ',', '.') : '0,00'; ?></p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow transition-transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Pedidos Pendentes</h3>
                    <p class="text-xl sm:text-2xl font-bold whitespace-nowrap"><?php echo count(array_filter($pedidos, function($pedido) { return $pedido['status'] == 0; })); ?></p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow transition-transform hover:scale-105">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Valor Pendente</h3>
                    <p class="text-xl sm:text-2xl font-bold whitespace-nowrap overflow-hidden text-ellipsis">R$ <?php 
                        $valor_pendente = array_reduce($pedidos, function($total, $pedido) {
                            return $total + ($pedido['status'] == 0 ? $pedido['valor_total'] : 0);
                        }, 0);
                        echo number_format($valor_pendente, 2, ',', '.');
                    ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium mb-4">Faturamento Diário</h3>
                    <div id="chartFaturamento" class="chart-container"></div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium mb-4">Faturamento por Hora</h3>
                    <div id="chartFaturamentoHora" class="chart-container"></div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium mb-4">Clientes</h3>
                    <div id="chartClientes" class="chart-container"></div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium mb-4">Pedidos</h3>
                    <div id="chartPedidos" class="chart-container"></div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dadosFaturamento = <?php echo json_encode($dadosFaturamentoGrafico); ?>;
            const dadosFaturamentoHora = <?php echo json_encode($dadosFaturamentoHoraGrafico); ?>;
            const dadosClientes = <?php echo json_encode($dadosClientesGrafico); ?>;
            const dadosPedidos = <?php echo json_encode($dadosPedidosGrafico); ?>;

            let charts = []; // Array para armazenar referências aos gráficos

            // Função para atualizar as cores dos gráficos
            function updateChartsTheme(isDark) {
                charts.forEach(chart => {
                    // Atualizar cor de fundo
                    chart.set("background", am5.Rectangle.new(chart.root, {
                        fill: am5.color(isDark ? "#1F2937" : "#FFFFFF"),
                        fillOpacity: 1
                    }));

                    // Atualizar cores dos eixos
                    chart.xAxes.each(function(axis) {
                        axis.get("renderer").labels.template.setAll({
                            fill: am5.color(isDark ? "#FFFFFF" : "#000000")
                        });
                    });

                    chart.yAxes.each(function(axis) {
                        axis.get("renderer").labels.template.setAll({
                            fill: am5.color(isDark ? "#FFFFFF" : "#000000")
                        });
                    });

                    // Atualizar cores das tooltips
                    chart.series.each(function(series) {
                        if (series.get("tooltip")) {
                            series.get("tooltip").set("background", am5.Rectangle.new(series.root, {
                                fill: am5.color(isDark ? "#374151" : "#FFFFFF")
                            }));
                            series.get("tooltip").label.setAll({
                                fill: am5.color(isDark ? "#FFFFFF" : "#000000")
                            });
                        }
                    });
                });
            }

            // Observador para mudanças no modo escuro
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === "class") {
                        const isDark = document.documentElement.classList.contains("dark");
                        updateChartsTheme(isDark);
                    }
                });
            });

            // Configurar o observador
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ["class"]
            });

            am5.ready(function() {
                function createRoot(containerId) {
                    const root = am5.Root.new(containerId);
                    const isDark = document.documentElement.classList.contains('dark');
                    
                    // Criar tema personalizado
                    const myTheme = am5.Theme.new(root);
                    myTheme.rule("Grid", ["base"]).setAll({
                        strokeOpacity: 0.1,
                        stroke: am5.color(isDark ? "#FFFFFF" : "#000000")
                    });
                    myTheme.rule("AxisLabel", ["base"]).setAll({
                        fill: am5.color(isDark ? "#FFFFFF" : "#000000")
                    });
                    
                    root.setThemes([
                        am5themes_Animated.new(root),
                        myTheme
                    ]);
                    
                    return root;
                }

                function createChart(root, data, title, isHourly = false, tooltipOptions = {}) {
                    const isDark = document.documentElement.classList.contains('dark');
                    
                    const chart = root.container.children.push(
                        am5xy.XYChart.new(root, {
                            panX: true,
                            panY: true,
                            wheelX: "panX",
                            wheelY: "zoomX",
                            background: am5.Rectangle.new(root, {
                                fill: am5.color(isDark ? "#1F2937" : "#FFFFFF"),
                                fillOpacity: 1
                            })
                        })
                    );

                    // Adicionar o gráfico ao array de gráficos
                    charts.push(chart);

                    // Create Y-axis
                    const yAxis = chart.yAxes.push(
                        am5xy.ValueAxis.new(root, {
                            renderer: am5xy.AxisRendererY.new(root, {
                                minGridDistance: 30,
                            })
                        })
                    );

                    // Aplicar cores do tema ao eixo Y
                    yAxis.get("renderer").labels.template.setAll({
                        fill: am5.color(isDark ? "#FFFFFF" : "#000000")
                    });

                    // Create X-axis
                    const xAxis = chart.xAxes.push(
                        am5xy.DateAxis.new(root, {
                            baseInterval: { timeUnit: isHourly ? "hour" : "day", count: 1 },
                            renderer: am5xy.AxisRendererX.new(root, {
                                minGridDistance: 50,
                            })
                        })
                    );

                    // Aplicar cores do tema ao eixo X
                    xAxis.get("renderer").labels.template.setAll({
                        fill: am5.color(isDark ? "#FFFFFF" : "#000000")
                    });

                    // Create series
                    const series = chart.series.push(
                        am5xy.ColumnSeries.new(root, {
                            name: title,
                            xAxis: xAxis,
                            yAxis: yAxis,
                            valueYField: "total",
                            valueXField: "timestamp",
                            fill: am5.color("#60A5FA"),
                            stroke: am5.color("#60A5FA"),
                            tooltip: am5.Tooltip.new(root, {
                                labelText: tooltipOptions.labelText,
                                getFillFromSprite: false,
                                autoTextColor: false,
                                fill: am5.color(isDark ? "#374151" : "#FFFFFF"),
                                labelFill: am5.color(isDark ? "#FFFFFF" : "#000000")
                            })
                        })
                    );

                    // Aplicar cores do tema à grade
                    chart.get("background").setAll({
                        fill: am5.color(isDark ? "#1F2937" : "#FFFFFF"),
                        fillOpacity: 1
                    });

                    // Set data
                    const chartData = data.map(item => {
                        let timestamp;
                        if (isHourly) {
                            timestamp = new Date(`2000-01-01T${item.hora}:00-03:00`).getTime();
                        } else {
                            const [year, month, day] = item.data.split('-');
                            timestamp = new Date(year, month - 1, day, 3, 0, 0).getTime();
                        }
                        
                        return {
                            timestamp: timestamp,
                            total: parseFloat(item.total),
                            dataFormatada: isHourly ? item.hora : new Date(item.data).toLocaleDateString('pt-BR'),
                            hora: item.hora,
                            valueY: parseFloat(item.total)
                        };
                    });

                    // Configurar formatação de data/hora para pt-BR
                    if (isHourly) {
                        xAxis.get("dateFormats")["hour"] = "HH:mm";
                        xAxis.get("periodChangeDateFormats")["hour"] = "HH:mm";
                    } else {
                        xAxis.get("dateFormats")["day"] = "dd/MM/yyyy";
                        xAxis.get("periodChangeDateFormats")["day"] = "dd/MM/yyyy";
                    }

                    xAxis.setAll({
                        timezone: "America/Sao_Paulo"
                    });

                    series.data.setAll(chartData);

                    // Add cursor
                    chart.set("cursor", am5xy.XYCursor.new(root, {
                        behavior: "none",
                        xAxis: xAxis
                    }));

                    // Animar o gráfico
                    series.appear(1000);
                    chart.appear(1000, 100);

                    return chart;
                }

                // Create charts
                const rootFat = createRoot("chartFaturamento");
                const chartFat = createChart(rootFat, dadosFaturamento, "Faturamento Diário", false, {
                    labelText: "{name}\nData: {dataFormatada}\nValor: R$ {valueY}"
                });

                const rootFatHora = createRoot("chartFaturamentoHora");
                const chartFatHora = createChart(rootFatHora, dadosFaturamentoHora, "Faturamento por Hora", true, {
                    labelText: "{name}\nHora: {hora}\nValor: R$ {valueY}"
                });

                const rootCli = createRoot("chartClientes");
                const chartCli = createChart(rootCli, dadosClientes, "Clientes", false, {
                    labelText: "{name}\nData: {dataFormatada}\nTotal: {valueY}"
                });

                const rootPed = createRoot("chartPedidos");
                const chartPed = createChart(rootPed, dadosPedidos, "Pedidos", false, {
                    labelText: "{name}\nData: {dataFormatada}\nTotal: {valueY}"
                });
            });
        });
    </script>
</body>
</html>
