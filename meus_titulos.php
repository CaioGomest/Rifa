<?php
require_once('conexao.php');
require_once('functions/functions_pedidos.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Verifica se o cliente está logado
if (!isset($_SESSION["usuario"]['cliente_id'])) {
    header('Location: index.php');
    exit;
}

$cliente_id = $_SESSION["usuario"]['cliente_id'];
$pedidos = listaPedidos($conn, null, $cliente_id);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Meus Títulos</title>
    <?php require_once('header.php'); ?>
</head>
<body class="bg-gray-100 text-black dark:bg-gray-900 dark:text-white">
    <main class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-8 text-center">Meus Títulos</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($pedidos as $pedido): 
                $campanha = listaCampanhas($conn, $pedido['campanha_id']);
                $campanha = isset($campanha[0]) ? $campanha[0] : null;
                ?>
                <div <?php if ($pedido['status'] == 0) { ?> onclick="window.location.href='pagamento.php?order_id=<?php echo $pedido['id']; ?>'" style="cursor: pointer;" <?php } ?>  class="bg-white dark:bg-gray-800  rounded-lg shadow-lg overflow-hidden">
                    <?php if ($campanha): ?>
                        <img src="<?php echo $campanha['caminho_imagem']; ?>" alt="<?php echo $campanha['nome']; ?>" 
                             class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 flex items-center justify-center">
                            <p class="text-gray-400">Campanha não encontrada</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2"><?php echo isset($campanha['nome']) ? $campanha['nome'] : 'Campanha não encontrada'; ?></h3>
                        
                        <div class="space-y-2 text-sm">
                            <p>
                                <span class="text-gray-400">Pedido:</span> 
                                #<?php echo str_pad($pedido['id'], 6, '0', STR_PAD_LEFT); ?>
                            </p>
                            <p>
                                <span class="text-gray-400">Data:</span> 
                                <?php echo date('d/m/Y H:i', strtotime($pedido['data_criacao'])); ?>
                            </p>
                            <p>
                                <span class="text-gray-400">Status:</span> 
                                <span class="<?php 
                                    echo classeStatusPedido($pedido);
                                ?> text-white px-2 py-1 rounded text-sm">
                                    <?php 
                                    echo textoStatusPedido($pedido);
                                    ?>
                                </span>
                            </p>
                            <p>
                                <span class="text-gray-400">Valor:</span> 
                                R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
                            </p>
                        </div>
                        
                        <?php if ($pedido['status'] == 1 && !empty($pedido['numeros_pedido'])): ?>
                        <div class="mt-4">
                            <p class="text-gray-400 mb-2">Números:</p>
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                $numeros = explode(',', $pedido['numeros_pedido']);
                                
                                // Verifica se a campanha tem cotas premiadas
                                $cotas_premiadas = [];
                                
                                if (!empty($campanha['cotas_premiadas'])) {
                                    $cotas_premiadas = array_map('trim', explode(',', $campanha['cotas_premiadas']));
                                    // Encontra as cotas premiadas do pedido
                                    $cotas_premiadas_pedido = array_intersect($numeros, $cotas_premiadas);
                                }
                                
                                foreach ($numeros as $numero): 
                                    $is_premiado = !empty($cotas_premiadas) && in_array($numero, $cotas_premiadas);
                                    $classe_numero = $is_premiado ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700';
                                ?>
                                    <span class="<?php echo $classe_numero; ?> px-2 py-1 rounded text-sm <?php echo $is_premiado ? 'relative group' : ''; ?>">
                                        <?php echo $numero; ?>
                                        <?php if ($is_premiado): ?>
                                            <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-black text-white text-xs rounded opacity-0 group-hover:opacity-100 whitespace-nowrap">
                                                Cota Premiada! 🎉
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                            <?php if (!empty($cotas_premiadas_pedido)): ?>
                            <div class="mt-4 p-4 bg-green-600 bg-opacity-20 rounded-lg">
                                <p class="text-green-500 font-semibold mb-2">🎉 Parabéns! Você tem cotas premiadas!</p>
                                <p class="text-sm text-gray-400"><?php echo $campanha['descricao_cotas_premiadas']; ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($pedidos)): ?>
            <div class="text-center py-8">
                <p class="text-gray-400">Você ainda não possui títulos.</p>
                <a href="index.php" class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                    Ver Campanhas Disponíveis
                </a>
            </div>
        <?php endif; ?>
    </main>
    
    <?php require_once('footer.php'); ?>
</body>
</html> 