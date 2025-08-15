<?php
require("header.php");
require("functions/functions_afiliados.php");
require("functions/functions_pagamentos.php");

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['usuario_tipo'] != 2)
{
    echo '<script>window.location.href = "index.php";</script>';
    exit;
}

$usuario_id = $_SESSION['usuario']['usuario_id'];
$afiliado = listaAfiliados($conn, $usuario_id);
$comissoes = listaComissao($conn, $usuario_id);
$pagamentos = listarPagamentos($conn, $usuario_id);
$totais_pagamentos = getTotalPagamentos($conn, $usuario_id);
?>

            <!-- Histórico de Pagamentos -->
            <div class="bg-white dark:bg-[#27272A]  p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-lg font-semibold mb-4">Histórico de Pagamentos</h2>

                <!-- Resumo de Pagamentos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                        <h3 class="text-md font-medium text-green-800 dark:text-green-200">Total Pago</h3>
                        <p class="text-2xl font-bold text-green-800 dark:text-green-200">
                            R$ <?= number_format($totais_pagamentos['total_pago'] ?? 0, 2, ',', '.') ?>
                        </p>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded-lg">
                        <h3 class="text-md font-medium text-yellow-800 dark:text-yellow-200">Total Pendente</h3>
                        <p class="text-2xl font-bold text-yellow-800 dark:text-yellow-200">
                            R$ <?= number_format($totais_pagamentos['total_pendente'] ?? 0, 2, ',', '.') ?>
                        </p>
                    </div>
                </div>

                <!-- Tabela de Pagamentos -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-700 dark:bg-gray-50">
                            <tr>
                                <th class="p-2 text-left">Data</th>
                                <th class="p-2 text-right">Valor</th>
                                <th class="p-2 text-center">Status</th>
                                <th class="p-2 text-center">Comprovante</th>
                                <th class="p-2 text-left">Observações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagamentos as $pagamento): ?>
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="p-2"><?= date('d/m/Y H:i', strtotime($pagamento['data_pagamento'])) ?></td>
                                    <td class="p-2 text-right">R$ <?= number_format($pagamento['valor_pago'], 2, ',', '.') ?></td>
                                    <td class="p-2 text-center">
                                        <?php
                                        $status_class = '';
                                        switch ($pagamento['status']) {
                                            case 'confirmado':
                                                $status_class = 'bg-green-100 text-green-800';
                                                break;
                                            case 'pendente':
                                                $status_class = 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'rejeitado':
                                                $status_class = 'bg-red-100 text-red-800';
                                                break;
                                        }
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-xs <?= $status_class ?>">
                                            <?= ucfirst($pagamento['status']) ?>
                                        </span>
                                    </td>
                                    <td class="p-2 text-center">
                                        <?php if ($pagamento['comprovante_path']): ?>
                                            <button onclick="visualizarComprovante('<?php echo htmlspecialchars(str_replace('../', '', $pagamento['comprovante_path'])); ?>')" 
                                                    class="text-purple-600 hover:text-purple-800">
                                                Visualizar
                                            </button>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-2"><?= htmlspecialchars($pagamento['observacoes']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pagamentos)): ?>
                                <tr>
                                    <td colspan="5" class="p-4 text-center">Nenhum pagamento registrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal de Visualização do Comprovante -->
            <div id="modalComprovante" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
                <div class="bg-white dark:bg-[#27272A]  p-6 rounded-lg w-full max-w-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Comprovante de Pagamento</h3>
                        <button onclick="fecharModalComprovante()" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <img id="imagemComprovante" src="" alt="Comprovante" class="w-full rounded-lg">
                </div>
            </div>

            <script>
                function visualizarComprovante(path) {
                    document.getElementById('imagemComprovante').src = path;
                    document.getElementById('modalComprovante').classList.remove('hidden');
                    document.getElementById('modalComprovante').classList.add('flex');
                }

                function fecharModalComprovante() {
                    document.getElementById('modalComprovante').classList.add('hidden');
                    document.getElementById('modalComprovante').classList.remove('flex');
                }
            </script>

            <!-- ... existing code ... --> 