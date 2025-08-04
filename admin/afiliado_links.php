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

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <title>Links do Afiliado</title>
</head>

<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white min-h-screen">
    <div class="flex flex-col md:flex-row min-h-screen">
        <?php require("sidebar.php"); ?>

        <main class="flex-1 p-4 md:p-6 overflow-y-auto">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-xl md:text-2xl font-bold">Links do Afiliado</h1>
            </header>

          

            <div class="bg-white dark:bg-gray-800 p-4 md:p-8 rounded-lg shadow-md w-full max-w-5xl mx-auto mb-8">
                <form method="POST" enctype="multipart/form-data" class="space-y-8">
                 
                    <!-- Código de Afiliado (Somente Leitura) -->
                    <div class="w-full space-y-4">
                        <div>
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