<?php
require("header.php");

// Verificar se o usuário está logado e é um afiliado
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['usuario_tipo'] != 2) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario']['usuario_id'];
$campanhas = listaCampanhas($conn, null,null,1);

// Buscar dados do afiliado
$query = "SELECT u.*, ca.*, c.url_principal 
          FROM usuarios u
          LEFT JOIN configuracoes_afiliados ca ON ca.usuario_id = u.usuario_id
          LEFT JOIN configuracoes c ON 1=1
          WHERE u.usuario_id = ?";


$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$afiliado = $stmt->get_result()->fetch_assoc();
// var_dump($afiliado);

// Verificar se existem dados bancários
if (!isset($afiliado['pix_chave']))
    $afiliado['pix_chave'] = '';
if (!isset($afiliado['pix_tipo']))
    $afiliado['pix_tipo'] = '';

// Verificar se existe link de afiliado
if (!isset($afiliado['url_principal']))
    $afiliado['url_principal'] = '';
if (!isset($afiliado['codigo']))
    $afiliado['codigo'] = '';

$link_afiliado = $_SERVER['HTTP_HOST'] . '?id=' . $afiliado['campanha_id'] . '&ref=' . $afiliado['codigo_afiliado'];

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    $dados_usuario = [
        'usuario_nome' => $_POST['usuario_nome'],
        'usuario_sobrenome' => $_POST['usuario_sobrenome'],
        'usuario_email' => $_POST['usuario_email']
    ];

    $dados_bancarios = [
        'pix_chave' => $_POST['pix_chave'],
        'pix_tipo' => $_POST['pix_tipo'],
    ];

    // Se uma nova senha foi fornecida
    if (!empty($_POST['nova_senha'])) {
        $dados_usuario['usuario_senha'] = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
    }

    // Processar upload da imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/img/usuarios/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['imagem']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadFile)) {
            $dados_usuario['usuario_avatar'] = 'assets/img/usuarios/' . $fileName;
        }
    }

    try {
        // Atualizar usuário
        $resultado_usuario = atualizarAfiliado($conn, $usuario_id, $dados_usuario);

        // Verificar se já existe configuração para o afiliado
        $check_query = "SELECT COUNT(*) as count FROM configuracoes_afiliados WHERE usuario_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $usuario_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();

        if ($result['count'] > 0) {
            // Atualizar dados bancários existentes
            $query = "UPDATE configuracoes_afiliados SET 
                        pix_chave = ?,
                        pix_tipo = ?,
                        banco_nome = ?,
                        banco_agencia = ?,
                        banco_conta = ?,
                        banco_tipo = ?,
                        banco_titular = ?,
                        banco_documento = ?
                     WHERE usuario_id = ?";
        } else {
            // Inserir novos dados bancários
            $query = "INSERT INTO configuracoes_afiliados 
                        (pix_chave, pix_tipo, banco_nome, banco_agencia, banco_conta, 
                         banco_tipo, banco_titular, banco_documento, usuario_id)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        }

        $stmt = $conn->prepare($query);
        $stmt->bind_param(
            "ssssssssi",
            $dados_bancarios['pix_chave'],
            $dados_bancarios['pix_tipo'],
            $dados_bancarios['banco_nome'],
            $dados_bancarios['banco_agencia'],
            $dados_bancarios['banco_conta'],
            $dados_bancarios['banco_tipo'],
            $dados_bancarios['banco_titular'],
            $dados_bancarios['banco_documento'],
            $usuario_id
        );

        $resultado_bancario = $stmt->execute();

        if ($resultado_usuario === true && $resultado_bancario) {
            $conn->commit();
            $mensagem = "Dados atualizados com sucesso!";
            $tipo_mensagem = "success";

            // Atualizar dados da sessão
            $_SESSION['usuario']['usuario_nome'] = $dados_usuario['usuario_nome'];
            $_SESSION['usuario']['usuario_email'] = $dados_usuario['usuario_email'];
            if (isset($dados_usuario['usuario_avatar'])) {
                $_SESSION['usuario']['usuario_avatar'] = $dados_usuario['usuario_avatar'];
            }

            // Recarregar dados do afiliado
            $query_reload = "SELECT u.*, ca.*, c.url_principal 
                           FROM usuarios u
                           LEFT JOIN configuracoes_afiliados ca ON ca.usuario_id = u.usuario_id
                           LEFT JOIN configuracoes c ON 1=1
                           WHERE u.usuario_id = ?";

            $stmt = $conn->prepare($query_reload);
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $afiliado = $stmt->get_result()->fetch_assoc();
        } else {
            throw new Exception("Erro ao atualizar dados");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $mensagem = "Erro ao atualizar dados: " . $e->getMessage();
        $tipo_mensagem = "error";
    }
}

function atualizarAfiliado($conn, $usuario_id, $dados_usuario)
{
    $campos = [];
    $valores = [];
    $tipos = '';

    // Construir campos e valores dinamicamente
    foreach ($dados_usuario as $campo => $valor) {
        $campos[] = "$campo = ?";
        $valores[] = $valor;
        $tipos .= 's'; // assumindo que todos os campos são strings
    }

    // Adicionar o ID do usuário
    $valores[] = $usuario_id;
    $tipos .= 'i';

    $query = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE usuario_id = ?";

    $stmt = $conn->prepare($query);

    // Criar array com referências para bind_param
    $refs = array($tipos);
    foreach ($valores as $key => $value) {
        $refs[] = &$valores[$key];
    }

    call_user_func_array(array($stmt, 'bind_param'), $refs);

    return $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Configurações do Afiliado</title>
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white min-h-screen">
    <div class="flex flex-col md:flex-row min-h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-4 md:p-6 overflow-y-auto">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-xl md:text-2xl font-bold">Configurações do Afiliado</h1>
            </header>

            <?php if (isset($mensagem)): ?>
                <div id="mensagem-sucesso"
                    class="mb-6 p-4 rounded-md <?= $tipo_mensagem === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> transition-opacity duration-500">
                    <?= $mensagem ?>
                </div>
                <script>
                    setTimeout(() => {
                        const mensagem = document.getElementById('mensagem-sucesso');
                        if (mensagem) {
                            mensagem.style.opacity = '0';
                            setTimeout(() => {
                                mensagem.remove();
                            }, 500);
                        }
                    }, 4500);
                </script>
            <?php endif; ?>

            <div class="bg-white dark:bg-gray-800 p-4 md:p-8 rounded-lg shadow-md w-full max-w-5xl mx-auto mb-8">
                <form method="POST" enctype="multipart/form-data" class="space-y-8">
                    <!-- Avatar -->
                    <div class="mb-8">
                        <label class="block text-base font-medium mb-3">Avatar</label>
                        <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-6">
                            <?php if (!empty($afiliado['usuario_avatar'])): ?>
                                <img src="../<?= $afiliado['usuario_avatar'] ?>" alt="Avatar atual"
                                    class="w-32 h-32 md:w-40 md:h-40 object-cover rounded-full border-4 border-purple-600">
                            <?php else: ?>
                                <div
                                    class="w-32 h-32 md:w-40 md:h-40 bg-purple-600 rounded-full flex items-center justify-center text-white text-5xl">
                                    <?= strtoupper(substr($afiliado['usuario_nome'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="w-full md:w-auto">
                                <input type="file" name="imagem" accept="image/*" onchange="previewImage(this)" class="block w-full text-sm text-gray-500
                                              file:mr-4 file:py-3 file:px-6
                                              file:rounded-full file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-purple-50 file:text-purple-700
                                              hover:file:bg-purple-100
                                              focus:outline-none">
                                <p class="mt-2 text-sm text-gray-500">PNG, JPG ou GIF (MAX. 800x800px)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Dados Pessoais -->
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-8 space-y-6">
                        <h2 class="text-xl font-semibold">Dados Pessoais</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nome -->
                            <div class="w-full">
                                <label class="block text-sm font-medium mb-2" for="usuario_nome">Nome</label>
                                <input type="text" id="usuario_nome" name="usuario_nome"
                                    class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                    value="<?= htmlspecialchars($afiliado['usuario_nome']) ?>" required>
                            </div>

                            <!-- Sobrenome -->
                            <div class="w-full">
                                <label class="block text-sm font-medium mb-2" for="usuario_sobrenome">Sobrenome</label>
                                <input type="text" id="usuario_sobrenome" name="usuario_sobrenome"
                                    class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                    value="<?= htmlspecialchars($afiliado['usuario_sobrenome']) ?>" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="w-full">
                            <label class="block text-sm font-medium mb-2" for="usuario_email">E-mail</label>
                            <input type="email" id="usuario_email" name="usuario_email"
                                class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                value="<?= htmlspecialchars($afiliado['usuario_email']) ?>" required>
                        </div>
                    </div>

                    <!-- Dados Bancários -->
                    <div class="space-y-6">
                        <h2 class="text-lg font-semibold">Dados Bancários</h2>

                        <!-- PIX -->
                        <div class="space-y-4">
                            <h3 class="text-md font-medium">PIX</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-2" for="pix_tipo">Tipo da Chave</label>
                                    <select id="pix_tipo" name="pix_tipo"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                        <option value="">Selecione...</option>
                                        <option value="cpf" <?= $afiliado['pix_tipo'] == 'cpf' ? 'selected' : '' ?>>CPF
                                        </option>
                                        <option value="cnpj" <?= $afiliado['pix_tipo'] == 'cnpj' ? 'selected' : '' ?>>CNPJ
                                        </option>
                                        <option value="email" <?= $afiliado['pix_tipo'] == 'email' ? 'selected' : '' ?>>
                                            Email</option>
                                        <option value="telefone" <?= $afiliado['pix_tipo'] == 'telefone' ? 'selected' : '' ?>>Telefone</option>
                                        <option value="aleatoria" <?= $afiliado['pix_tipo'] == 'aleatoria' ? 'selected' : '' ?>>Chave Aleatória</option>
                                    </select>
                                </div>
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-2" for="pix_chave">Chave PIX</label>
                                    <input type="text" id="pix_chave" name="pix_chave"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                        value="<?= htmlspecialchars($afiliado['pix_chave']) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Dados Bancários Tradicionais -->
                        <!-- <div class="space-y-4">
                            <h3 class="text-md font-medium">Conta Bancária</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-2" for="banco_nome">Banco</label>
                                    <input type="text" id="banco_nome" name="banco_nome"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                        value="<?= htmlspecialchars($afiliado['banco_nome']) ?>">
                                </div>
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-2" for="banco_agencia">Agência</label>
                                    <input type="text" id="banco_agencia" name="banco_agencia"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                        value="<?= htmlspecialchars($afiliado['banco_agencia']) ?>">
                                </div>
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-2" for="banco_conta">Conta</label>
                                    <input type="text" id="banco_conta" name="banco_conta"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                        value="<?= htmlspecialchars($afiliado['banco_conta']) ?>">
                                </div>
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-2" for="banco_tipo">Tipo de Conta</label>
                                    <select id="banco_tipo" name="banco_tipo"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                                        <option value="">Selecione...</option>
                                        <option value="corrente" <?= $afiliado['banco_tipo'] == 'corrente' ? 'selected' : '' ?>>Corrente</option>
                                        <option value="poupanca" <?= $afiliado['banco_tipo'] == 'poupanca' ? 'selected' : '' ?>>Poupança</option>
                                    </select>
                                </div>
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-2" for="banco_titular">Nome do
                                        Titular</label>
                                    <input type="text" id="banco_titular" name="banco_titular"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                        value="<?= htmlspecialchars($afiliado['banco_titular']) ?>">
                                </div>
                                <div class="w-full">
                                    <label class="block text-sm font-medium mb-2" for="banco_documento">CPF/CNPJ do
                                        Titular</label>
                                    <input type="text" id="banco_documento" name="banco_documento"
                                        class="w-full p-3 border rounded-lg dark:bg-gray-700 dark:border-gray-600 focus:ring-2 focus:ring-purple-600 focus:border-transparent"
                                        value="<?= htmlspecialchars($afiliado['banco_documento']) ?>">
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <!-- Código de Afiliado (Somente Leitura) -->
                    <div class="w-full space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Links Afiliado</label>

                            <?php
                            function renderInputLink($id, $link)
                            {
                                ?>
                                <div class="flex mb-4">
                                    <input type="text" id="<?= $id ?>"
                                        class="w-full p-3 border rounded-l-lg bg-gray-100 dark:bg-gray-700 dark:border-gray-600 select-none focus:outline-none"
                                        value="<?= htmlspecialchars($link) ?>" readonly
                                        style="user-select: none; -webkit-user-select: none;">
                                    <button type="button" onclick="copiarCodigo('<?= $id ?>')"
                                        class="px-4 py-2 bg-purple-600 text-white rounded-r-lg hover:bg-purple-500 transition-colors">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <?php
                            }
                            ?>

                            <?php foreach ($campanhas as $index => $campanha): ?>
                                <?php
                                $baseLink = "https://" . $_SERVER['HTTP_HOST'] . '/campanha.php?id=' . urlencode($campanha['id']) . '&ref=' . urlencode($afiliado['codigo_afiliado']);
                                $inputId = 'link_afiliado_' . $index;
                                ?>

                                <label
                                    class="mt-8 block font-semibold mb-1"><?= htmlspecialchars($campanha['nome']) ?></label>

                                <!-- Link principal da campanha -->
                                <?php renderInputLink($inputId, $baseLink); ?>

                                <?php if (!empty($campanha["habilita_pacote_promocional_exclusivo"])): ?>
                                    <?php
                                    $pacotes_promocionais_exclusivos = json_decode($campanha['pacotes_exclusivos'], true);
                                    if (is_array($pacotes_promocionais_exclusivos)) {
                                        foreach ($pacotes_promocionais_exclusivos as $p_index => $pacote) {
                                            $link_pacote = $baseLink . '&cx=' . urlencode($pacote['codigo_pacote']);
                                            $inputPacoteId = "link_afiliado_{$index}_{$p_index}";
                                            ?>
                                            <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md mb-2">
                                                <p class="text-purple-800 dark:text-purple-200">
                                                    <span class="font-bold"><?= htmlspecialchars($campanha['nome']) ?> ⭐ Oferta
                                                        Exclusiva!</span><br>
                                                </p>
                                            </div>
                                            <?php renderInputLink($inputPacoteId, $link_pacote); ?>
                                            <?php
                                        }
                                    }
                                    ?>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </div>


                    </div>

                    <!-- Botões -->
                    <div class="flex justify-end pt-6">
                        <button type="submit"
                            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-500 transition-colors focus:ring-2 focus:ring-purple-600 focus:ring-offset-2">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const preview = input.parentElement.parentElement.querySelector('img');
                    const placeholder = input.parentElement.parentElement.querySelector('div.w-32');

                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        const newPreview = document.createElement('img');
                        newPreview.src = e.target.result;
                        newPreview.alt = 'Avatar preview';
                        newPreview.className = 'w-32 h-32 md:w-40 md:h-40 object-cover rounded-full border-4 border-purple-600';

                        if (placeholder) {
                            placeholder.replaceWith(newPreview);
                        }
                    }
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        function copiarCodigo(id) {
            const elemento = document.getElementById(id);
            if (elemento) {
                try {
                    // Tenta usar a nova API de clipboard diretamente com o valor
                    navigator.clipboard.writeText(elemento.value).then(() => {
                        mostrarMensagem('Link copiado com sucesso!');
                    }).catch(() => {
                        // Fallback para o método antigo, mas sem seleção visível
                        elemento.style.userSelect = 'text';
                        elemento.select();
                        document.execCommand('copy');
                        elemento.style.userSelect = 'none';
                        mostrarMensagem('Link copiado com sucesso!');
                    });
                } catch (err) {
                    console.error('Erro ao copiar:', err);
                    mostrarMensagem('Erro ao copiar o link. Por favor, tente novamente.');
                }
            }
        }

        function mostrarMensagem(texto) {
            const mensagem = document.createElement('div');
            mensagem.className = 'fixed bottom-4 right-4 bg-green-400 text-white px-6 py-3 rounded-lg shadow-lg';
            mensagem.textContent = texto;
            document.body.appendChild(mensagem);

            setTimeout(() => {
                mensagem.remove();
            }, 5000);
        }
    </script>
</body>

</html>