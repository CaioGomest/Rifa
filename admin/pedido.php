<?php
require_once("header.php");
require_once("../functions/functions_pedidos.php");
require_once("../functions/functions_campanhas.php");

// Verificar se é edição
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pedido = null;

if ($id > 0) {
    $sql = "SELECT p.*, c.nome as campanha_nome, cl.nome as cliente_nome 
            FROM lista_pedidos p 
            LEFT JOIN campanhas c ON c.id = p.campanha_id 
            LEFT JOIN clientes cl ON cl.id = p.cliente_id
            WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedido = $result->fetch_assoc();
}

// Buscar todas as campanhas (removido o filtro de status)
$sql = "SELECT id, nome FROM campanhas WHERE flag_exclusao = 0";
$campanhas = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Buscar todos os clientes
$sql = "SELECT id, nome FROM clientes ORDER BY nome ASC";
$clientes = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campanha_id = $_POST['campanha_id'];
    $cliente_id = $_POST['cliente_id'];
    
    // Buscar nome da campanha para usar como nome do produto
    $sql = "SELECT nome FROM campanhas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $campanha_id);
    $stmt->execute();
    $campanha = $stmt->get_result()->fetch_assoc();
    $nome_produto = $campanha['nome'];
    
    $quantidade = $_POST['quantidade'];
    $numeros = $_POST['numeros'];
    $valor_total = floatval($_POST['valor_total']);
    $status = $_POST['status'];
    
    // Gerar token do pedido
    $token_pedido = uniqid();
    
    if ($id > 0) {
        // Atualizar pedido existente
        $sql = "UPDATE lista_pedidos SET 
                campanha_id = ?, 
                cliente_id = ?,
                nome_produto = ?,
                quantidade = ?,
                numeros_pedido = ?,
                valor_total = ?,
                status = ?,
                data_atualizacao = NOW()
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssdii", $campanha_id, $cliente_id, $nome_produto, $quantidade, $numeros, $valor_total, $status, $id);
    } else {
        // Inserir novo pedido
        $sql = "INSERT INTO lista_pedidos (campanha_id, cliente_id, nome_produto, quantidade, numeros_pedido, valor_total, status, token_pedido, data_criacao) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssdis", $campanha_id, $cliente_id, $nome_produto, $quantidade, $numeros, $valor_total, $status, $token_pedido);
    }
    
    if ($stmt->execute()) 
    {
        echo '<script>window.location.href = "pedidos.php?success=1";</script>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title><?php echo $id ? 'Editar' : 'Novo'; ?> Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php require_once("sidebar.php"); ?>
        
        <div class="flex-1">
            <div class="container mx-auto px-4 py-8">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold"><?php echo $id ? 'Editar' : 'Novo'; ?> Pedido</h1>
                        <a href="pedidos.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                            Voltar
                        </a>
                    </div>
                    
                    <form method="POST" class="space-y-6">
                        <!-- Campanha e Cliente -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Campanha</label>
                                <select name="campanha_id" required class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                                    <option value="">Selecione uma campanha</option>
                                    <?php foreach ($campanhas as $campanha): ?>
                                        <option value="<?php echo $campanha['id']; ?>" <?php echo ($pedido && $pedido['campanha_id'] == $campanha['id']) ? 'selected' : ''; ?>>
                                            <?php echo $campanha['nome']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cliente</label>
                                <select name="cliente_id" required class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                                    <option value="">Selecione um cliente</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?php echo $cliente['id']; ?>" <?php echo ($pedido && $pedido['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>>
                                            <?php echo $cliente['nome']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Quantidade e Números -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Quantidade</label>
                                <input type="number" name="quantidade" required min="1"
                                       value="<?php echo $pedido ? $pedido['quantidade'] : ''; ?>"
                                       class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Números</label>
                                <input type="text" name="numeros" required
                                       value="<?php echo $pedido ? $pedido['numeros_pedido'] : ''; ?>"
                                       placeholder="Ex: 1,2,3,4,5"
                                       class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                            </div>
                        </div>

                        <!-- Valor e Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Valor Total</label>
                                <input type="number" name="valor_total" required step="0.01" min="0"
                                       value="<?php echo $pedido ? $pedido['valor_total'] : ''; ?>"
                                       class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" required class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-purple-500">
                                    <option value="0" <?php echo ($pedido && $pedido['status'] == 0) ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="1" <?php echo ($pedido && $pedido['status'] == 1) ? 'selected' : ''; ?>>Pago</option>
                                    <option value="2" <?php echo ($pedido && $pedido['status'] == 2) ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Botão de Submit -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                                <?php echo $id ? 'Atualizar' : 'Cadastrar'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para calcular o valor total
        function calcularValorTotal() {
            let numeros = document.querySelector('input[name="numeros"]').value;
            let quantidade = numeros.split(',').filter(num => num.trim() !== '').length;
            document.querySelector('input[name="quantidade"]').value = quantidade;
            
            let campanhaSelect = document.querySelector('select[name="campanha_id"]');
            if (campanhaSelect.value) {
                let campanhaId = campanhaSelect.value;
                fetch(`ajax/ajax_campanha.php?id=${campanhaId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Resposta da API:', data); // Debug
                        if (data.preco) {
                            let valorPorNumero = parseFloat(data.preco);
                            let valorTotal = quantidade * valorPorNumero;
                            console.log('Valor por número:', valorPorNumero); // Debug
                            console.log('Quantidade:', quantidade); // Debug
                            console.log('Valor total:', valorTotal); // Debug
                            document.querySelector('input[name="valor_total"]').value = valorTotal.toFixed(2);
                        } else {
                            console.error('Preço não encontrado na resposta:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar valor da campanha:', error);
                    });
            }
        }

        // Formatar números e calcular valores
        document.querySelector('input[name="numeros"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9,]/g, '');
            e.target.value = value;
            calcularValorTotal();
        });

        // Recalcular quando mudar a campanha
        document.querySelector('select[name="campanha_id"]').addEventListener('change', function() {
            calcularValorTotal();
        });

        // Calcular valor inicial se já houver números preenchidos
        if (document.querySelector('input[name="numeros"]').value) {
            calcularValorTotal();
        }
    </script>
</body>
</html>
