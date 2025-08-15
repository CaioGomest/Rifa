<?php
ob_start();
require("header.php");
require("../functions/functions_afiliados.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
$usuario = null;

if ($id)
    $usuario = listaUsuarios($conn, $id, NULL, NULL, NULL, NULL, 0);


if(!empty($usuario[0]['usuario_tipo']) && $usuario[0]['usuario_tipo'] == 2)
{
    $afiliados = listaAfiliados($conn,  $id, NULL, NULL, NULL, NULL, NULL, 0);
}
function gerarCodigoAleatorio() {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $codigo = '';

    for ($i = 0; $i < 8; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }

    return $codigo;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'usuario_nome' => $_POST['nome'],
        'usuario_sobrenome' => $_POST['sobrenome'],
        'usuario_telefone' => $_POST['telefone'],   
        'usuario_email' => $_POST['email'],
        'usuario_tipo' => $_POST['tipo_usuario'],
        'usuario_avatar' => null
    ];

    // Se uma nova senha foi fornecida ou é um novo usuário
    if (!empty($_POST['nova_senha']) || !$id) {
        $dados['usuario_senha'] = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
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
            $dados['usuario_avatar'] = 'assets/img/usuarios/' . $fileName;
        }
    }

    if ($id) {
        // Atualizar usuário existente
        $resultado = atualizarUsuario($conn, $id, $dados['usuario_nome'], $dados['usuario_email'], $dados['usuario_telefone'], $dados['usuario_avatar'], $dados['usuario_tipo'], isset($dados['usuario_senha']) ? $dados['usuario_senha'] : null, $dados['usuario_sobrenome']);
        // Gerenciar configurações de afiliado apenas para atualização
        if ($_POST['tipo_usuario'] == '2') {
            // Verificar se já existe configuração para este usuário
            $sql = "SELECT id FROM configuracoes_afiliados WHERE usuario_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Atualizar configuração existente
                $sql = "UPDATE configuracoes_afiliados SET porcentagem_comissao = ? WHERE usuario_id = ?";
                $stmt = $conn->prepare($sql);
                $porcentagem = floatval($_POST['porcentagem_comissao']);
                $stmt->bind_param("di", $porcentagem, $id);
                $stmt->execute();
            } else {
                // Criar nova configuração
                $sql = "INSERT INTO configuracoes_afiliados (usuario_id, porcentagem_comissao, codigo_afiliado) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $porcentagem = floatval($_POST['porcentagem_comissao']);
                $codigo_afiliado = gerarCodigoAleatorio();
                $stmt->bind_param("ids", $id, $porcentagem, $codigo_afiliado);
                $stmt->execute();
            }
        }
    } else {
        // Criar novo usuário
        $resultado = criarUsuario($conn, $dados);
        
        // Se for um novo afiliado, criar configuração com porcentagem
        if (is_numeric($resultado) && $_POST['tipo_usuario'] == '2') {
            $codigo_afiliado = gerarCodigoAleatorio();
            $usuario_id = $resultado;
            $sql = "INSERT INTO configuracoes_afiliados (usuario_id, porcentagem_comissao, codigo_afiliado) VALUES (?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $porcentagem = floatval($_POST['porcentagem_comissao']);
            $stmt->bind_param("ids", $usuario_id, $porcentagem, $codigo_afiliado);
            $stmt->execute();
        }
    }

    if ($resultado) {
        header('Location: usuarios.php');
        exit;
    } else {
        $erro = $resultado;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">

    <div class="flex flex-col md:flex-row min-h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-4 md:p-6 w-full">
            <?php if (isset($erro)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline"><?= $erro ?></span>
            </div>
            <?php endif; ?>

            <section class="w-full">
                <div class="bg-white dark:bg-[#27272A] p-4 md:p-6 rounded-md shadow-md max-w-2xl mx-auto">
                    <header class="flex justify-between items-center mb-6">
                        <h1 class="text-xl md:text-2xl font-bold"><?= $id ? 'Editar' : 'Novo' ?> usuário</h1>
                        <a href="usuarios.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                            Voltar
                        </a>
                    </header>
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <!-- Grid para campos de nome e sobrenome -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" 
                                       class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600 text-sm md:text-base"
                                       value="<?= isset($usuario) ? $usuario[0]['usuario_nome'] : '' ?>" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="sobrenome">Sobrenome</label>
                                <input type="text" id="sobrenome" name="sobrenome" 
                                       class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600 text-sm md:text-base"
                                       value="<?= isset($usuario) ? $usuario[0]['usuario_sobrenome'] : '' ?>" required>
                            </div>
                        </div>

                        <!-- Grid para campos de email e telefone -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                       class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600 text-sm md:text-base"
                                       value="<?= isset($usuario) ? $usuario[0]['usuario_email'] : '' ?>" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="telefone">Telefone</label>
                                <input type="text" id="telefone" name="telefone" 
                                       class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600 text-sm md:text-base"
                                       value="<?= isset($usuario) ? $usuario[0]['usuario_telefone'] : '' ?>" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1" for="nova_senha">Nova Senha</label>
                            <input type="password" id="nova_senha" name="nova_senha" 
                                   class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600 text-sm md:text-base"
                                   <?= !$id ? 'required' : '' ?>>
                            <?php if ($id): ?>
                                <p class="text-xs md:text-sm text-gray-500 mt-1">Deixe em branco para manter a senha atual</p>
                            <?php endif; ?>
                        </div>

                        <!-- Grid para tipo de usuário e comissão -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="tipo_usuario">Tipo do usuário</label>
                                <select id="tipo_usuario" name="tipo_usuario" 
                                        class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600 text-sm md:text-base" required>
                                    <option value="1" <?= isset($usuario) && $usuario[0]['usuario_tipo'] == 1 ? 'selected' : '' ?>>Administrador</option>
                                    <option value="2" <?= isset($usuario) && $usuario[0]['usuario_tipo'] == 2 ? 'selected' : '' ?>>Afiliado</option>
                                </select>
                            </div>

                            <div id="porcentagem-div" style="display: none;">
                                <label class="block text-sm font-medium mb-1" for="porcentagem_comissao">Porcentagem de Comissão (%)</label>
                                <input type="number" id="porcentagem_comissao" name="porcentagem_comissao" 
                                       class="w-full p-2 border rounded-md dark:bg-[#3F3F46] dark:border-gray-600 text-sm md:text-base"
                                       value="<?= isset($afiliados) && !empty($afiliados[0]['porcentagem_comissao']) ? $afiliados[0]['porcentagem_comissao'] : '0' ?>"
                                       min="0" max="100" step="0.01">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium">Foto de Perfil</label>
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                <div class="flex flex-col items-center justify-center">
                                    <?php if (isset($usuario) && $usuario[0]['usuario_avatar']): ?>
                                        <img src="../<?= $usuario[0]['usuario_avatar'] ?>" 
                                             alt="Avatar atual" 
                                             class="w-24 h-24 md:w-32 md:h-32 object-cover mb-4 rounded-lg">
                                    <?php else: ?>
                                        <div class="w-24 h-24 md:w-32 md:h-32 flex items-center justify-center border border-gray-300 dark:border-gray-600 rounded-lg mb-4">
                                            <span class="text-3xl md:text-4xl text-gray-400">+</span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="block mb-2 text-sm">Adicionar logo</span>
                                    <input type="file" name="imagem" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors text-sm md:text-base">
                                <?= $id ? 'Atualizar' : 'Criar' ?> Usuário
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tipoUsuarioSelect = document.getElementById('tipo_usuario');
            const porcentagemDiv = document.getElementById('porcentagem-div');

            function toggleAfiliadoFields() {
                const isAfiliado = tipoUsuarioSelect.value === '2';
                porcentagemDiv.style.display = isAfiliado ? 'block' : 'none';
            }

            tipoUsuarioSelect.addEventListener('change', toggleAfiliadoFields);
            toggleAfiliadoFields(); // Execute on page load
        });
    </script>
</body>
</html>