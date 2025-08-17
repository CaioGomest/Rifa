<!DOCTYPE html>
<html lang="pt-BR">
<?php
require("header.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Processa o upload da logo se houver
    $logo = isset($_FILES['logo']) ? $_FILES['logo'] : null;
    $logo_path = isset($_POST['logo_atual']) ? $_POST['logo_atual'] : '';

    if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/imgs/';
        
        // Cria o diretório se não existir
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Gera um nome único para o arquivo
        $ext = strtolower(pathinfo($logo['name'], PATHINFO_EXTENSION));
        $novo_nome = uniqid('logo_') . '.' . $ext;
        $caminho_completo = $upload_dir . $novo_nome;

        // Move o arquivo para o diretório de uploads
        if (move_uploaded_file($logo['tmp_name'], $caminho_completo)) {
            // Remove a logo antiga se existir
            if (!empty($logo_path) && file_exists('../' . $logo_path)) {
                unlink('../' . $logo_path);
            }
            $logo_path = 'assets/imgs/' . $novo_nome;
        }
    }

    // Processa o upload da imagem de login se houver
    $login_image = isset($_FILES['login_image']) ? $_FILES['login_image'] : null;
    $login_image_path = isset($_POST['login_image_atual']) ? $_POST['login_image_atual'] : '';

    if ($login_image && $login_image['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/imgs/';
        
        // Cria o diretório se não existir
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Gera um nome único para o arquivo
        $ext = strtolower(pathinfo($login_image['name'], PATHINFO_EXTENSION));
        $novo_nome = uniqid('login_') . '.' . $ext;
        $caminho_completo = $upload_dir . $novo_nome;

        // Move o arquivo para o diretório de uploads
        if (move_uploaded_file($login_image['tmp_name'], $caminho_completo)) {
            // Remove a imagem antiga se existir
            if (!empty($login_image_path) && file_exists('../' . $login_image_path)) {
                unlink('../' . $login_image_path);
            }
            $login_image_path = 'assets/imgs/' . $novo_nome;
        }
    }

    // Processa os campos obrigatórios do cadastro
    $campos_obrigatorios = isset($_POST['campos_obrigatorios']) ? implode(',', $_POST['campos_obrigatorios']) : '';

    // Coleta os dados do formulário
    $titulo = $_POST['titulo'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $tema = $_POST['tema'] ?? 'padrao';
    
    // Social
    $habilitar_compartilhamento = isset($_POST['habilitar_compartilhamento']) ? 1 : 0;
    $habilitar_fale_conosco = isset($_POST['habilitar_fale_conosco']) ? 1 : 0;
    $link_fale_conosco = $_POST['link_fale_conosco'] ?? '';
    $habilitar_grupos = isset($_POST['habilitar_grupos']) ? 1 : 0;
    $link_grupo = $_POST['link_grupo'] ?? '';
    $link_youtube = $_POST['link_youtube'] ?? '';
    $link_facebook = $_POST['link_facebook'] ?? '';
    $link_instagram = $_POST['link_instagram'] ?? '';
    // Cadastro
    $termos_uso = $_POST['termos_uso'] ?? '';
    $politica_privacidade = $_POST['politica_privacidade'] ?? '';
    
    // Rodapé
    $texto_rodape = $_POST['texto_rodape'] ?? '';
    $copyright = $_POST['copyright'] ?? '';
        
    // Cotas
    $cotas_ocultas = $_POST['cotas_ocultas'] ?? '';
    $mensagem_cota_oculta = $_POST['mensagem_cota_oculta'] ?? '';
    
    // FAQ
    $perguntas_frequentes = $_POST['perguntas_frequentes'] ?? '';
    
    // Termos
    $termos_condicoes = $_POST['termos_condicoes'] ?? '';
    
    // Pixels
    $pixel_facebook = $_POST['pixel_facebook'] ?? '';
    $pixel_google = $_POST['pixel_google'] ?? '';
    $outros_pixels = $_POST['outros_pixels'] ?? '';
    
    // Integrações
    $habilitar_api_tiktok = isset($_POST['habilitar_api_tiktok']) ? 1 : 0;
    $token_tiktok = $_POST['token_tiktok'] ?? '';
    $habilitar_api_kawai = isset($_POST['habilitar_api_kawai']) ? 1 : 0;
    $token_kawai = $_POST['token_kawai'] ?? '';
    $habilitar_api_facebook = isset($_POST['habilitar_api_facebook']) ? 1 : 0;
    $habilitar_google_analytics = isset($_POST['habilitar_google_analytics']) ? 1 : 0;
    $token_google_analytics = $_POST['token_google_analytics'] ?? '';
    $habilitar_utmify = isset($_POST['habilitar_utmify']) ? 1 : 0;
    $token_utmify = $_POST['token_utmify'] ?? '';
    
    // Gateways
    $habilitar_mercadopago = isset($_POST['habilitar_mercadopago']) ? 1 : 0;
    $mercadopago_token_acesso = $_POST['mercadopago_token_acesso'] ?? '';

    $habilitar_pay2m = isset($_POST['habilitar_pay2m']) ? 1 : 0;
    $pay2m_client_key = $_POST['pay2m_client_key'] ?? '';
    $pay2m_client_secret = $_POST['pay2m_client_secret'] ?? '';

    $habilitar_paggue = isset($_POST['habilitar_paggue']) ? 1 : 0;
    $paggue_client_key = $_POST['paggue_client_key'] ?? '';
    $paggue_client_secret = $_POST['paggue_client_secret'] ?? '';

    // Login
    $login_bg_color = $_POST['login_bg_color'] ?? '#1F2937';

    // Atualiza as configurações
    $resultado = atualizaConfiguracoes($conn, $titulo, $email, $telefone, $tema,
        $logo_path, $habilitar_compartilhamento, $habilitar_grupos, $campos_obrigatorios,
        $termos_uso, $politica_privacidade, $texto_rodape, $copyright,
        $cotas_ocultas, $mensagem_cota_oculta, $perguntas_frequentes, $termos_condicoes,

        $habilitar_mercadopago, $mercadopago_token_acesso, 
        
        $habilitar_pay2m, $pay2m_client_key, $pay2m_client_secret, 
        
        $habilitar_paggue, $paggue_client_key, $paggue_client_secret,
        
        $pixel_facebook, $pixel_google, $outros_pixels,
        $habilitar_api_tiktok, $token_tiktok, $habilitar_api_kawai, $token_kawai,
        $habilitar_api_facebook, $habilitar_google_analytics, $token_google_analytics,
        $habilitar_utmify, $link_youtube, $link_facebook, $link_instagram,
        $login_bg_color, $login_image_path, $token_utmify, $link_grupo, $habilitar_fale_conosco,    $link_fale_conosco);

    if ($resultado === true) {
        $mensagem = ['tipo' => 'sucesso', 'texto' => 'Configurações atualizadas com sucesso!'];
        
        // Força a sincronização do tema após salvar
        echo "<script>
            setTimeout(function() {
                if (window.themeManager) {
                    // Recarrega o tema do banco
                    const newTheme = '" . $tema . "';
                    window.themeFromBank = newTheme;
                    window.themeManager.syncWithDatabase();
                    window.themeManager.applyTheme();
                    console.log('Tema atualizado após salvar:', newTheme);
                }
            }, 100);
        </script>";
    } else {
        $mensagem = ['tipo' => 'erro', 'texto' => 'Erro ao atualizar configurações: ' . $resultado];
    }
}

// Busca as informações do sistema
$infos = listaInformacoes($conn);
?>

<body class="bg-gray-100 text-gray-800 dark:bg-[#18181B] dark:text-white">
    <!-- Container principal -->
    <div class="flex flex-col md:flex-row min-h-screen">
        <?php require("sidebar.php"); ?>
        
        <!-- Conteúdo principal -->
        <main class="flex-1 p-4 md:p-6 overflow-x-hidden">
            <header class="flex justify-between items-center mb-6">
                <h1 class="text-xl md:text-2xl font-bold">Configurações</h1>
            </header>

            <?php if (isset($mensagem)): ?>
                <?php require_once('../assets/template_alerta.php'); ?>
                <script>
                    showCustomAlert(<?php echo json_encode($mensagem['texto']); ?>, <?php echo $mensagem['tipo'] === 'sucesso' ? json_encode('success') : json_encode('error'); ?>);
                </script>
                <?php if ($mensagem['tipo'] === 'sucesso'): ?>
                    <script>
                        const sidebarLogo = document.getElementById('logo');
                        if (sidebarLogo) {
                            const logoPath = '<?php echo isset($logo_path) ? "../" . $logo_path : ""; ?>';
                            if (logoPath) { sidebarLogo.src = logoPath; }
                        }
                    </script>
                <?php endif; ?>
            <?php endif; ?>

            <style>
                /* Adiciona rolagem suave nas abas */
                .overflow-x-auto {
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: thin;
                    scrollbar-color: rgba(107, 114, 128, 0.5) transparent;
                }
                
                .overflow-x-auto::-webkit-scrollbar {
                    height: 6px;
                }
                
                .overflow-x-auto::-webkit-scrollbar-track {
                    background: transparent;
                }
                
                .overflow-x-auto::-webkit-scrollbar-thumb {
                    background-color: rgba(107, 114, 128, 0.5);
                    border-radius: 3px;
                }

                /* Ajusta o layout em telas pequenas */
                @media (max-width: 768px) {
                    .tab-content {
                        padding: 1rem;
                    }
                    
                    input[type="text"],
                    input[type="email"],
                    input[type="url"],
                    textarea {
                        font-size: 16px; /* Evita zoom em dispositivos móveis */
                    }
                }
            </style>

            <section class="w-full">
                <div class="bg-white dark:bg-[#27272A] p-4 md:p-6 rounded-md shadow">
                    <!-- Abas de navegação -->
                    <div class="overflow-x-auto">
                        <ul class="flex flex-nowrap space-x-4 mb-6 border-b border-gray-300 dark:border-gray-700 whitespace-nowrap">
                            <li class="p-2 border-b-2 border-purple-500">
                                <a href="#" class="text-purple-700 dark:text-purple-400 font-bold tab-link" data-tab="configuracoes">Configurações</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link" data-tab="cadastro">Cadastro</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link" data-tab="social">Social</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link" data-tab="rodape">Rodapé</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link" data-tab="integracoes">Integrações</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link" data-tab="faq">FAQ</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link" data-tab="termos">Termos</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link" data-tab="gateways">Gateways</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link" data-tab="login">Login</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Conteúdo das abas -->
                    <form method="POST" action="" enctype="multipart/form-data" class="w-full">
                        <!-- Aba Configurações -->
                        <div id="configuracoes" class="tab-content">
                            <h2 class="text-lg md:text-xl font-semibold mb-4">Configurações Gerais</h2>
                            <div>
                                <div>
                                    <label for="titulo" class="block mb-2 font-medium">Título do site</label>
                                    <input type="text" id="titulo" name="titulo" 
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['titulo']) ? htmlspecialchars($infos['titulo']) : ''; ?>">
                                </div>
                                <div>
                                    <label for="email" class="block mb-2 font-medium">E-mail</label>
                                    <input type="email" id="email" name="email"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['email']) ? htmlspecialchars($infos['email']) : ''; ?>">
                                </div>
                                <div>
                                    <label for="telefone" class="block mb-2 font-medium">Telefone</label>
                                    <input type="text" id="telefone" name="telefone"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['telefone']) ? htmlspecialchars($infos['telefone']) : ''; ?>">
                                </div>
                                <div>
                                    <label for="tema" class="block mb-2 font-medium">Tema</label>
                                    <select id="tema" name="tema"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                        <option value="claro" <?php echo (isset($infos['tema']) && $infos['tema']   == 'claro ') ? 'selected' : ''; ?>>Claro</option>
                                        <option value="escuro" <?php echo (isset($infos['tema']) && $infos['tema'] == 'escuro') ? 'selected' : ''; ?>>Escuro </option>
                                    </select>   
                                </div>
                                <div class="col-span-2">
                                    <label for="logo" class="block mb-2 font-medium">Logo</label>
                                    <div class="flex items-center space-x-4 logo-container">
                                        <div class="relative w-40 h-40 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                            <?php if (isset($infos['logo']) && !empty($infos['logo'])): ?>
                                                <img src="../<?php echo $infos['logo']; ?>" alt="Logo" class="w-full h-full object-contain">
                                                <button type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
                                                    onclick="removerLogo()">×</button>
                                            <?php else: ?>
                                                <div class="flex flex-col items-center justify-center h-full cursor-pointer" onclick="document.getElementById('logo_input').click()">
                                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    <span class="mt-2 text-sm text-gray-500">Adicionar logo</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" id="logo_input" name="logo" accept="image/*" class="hidden" onchange="previewLogo(this)">
                                        <input type="hidden" name="logo_atual" value="<?php echo isset($infos['logo']) ? $infos['logo'] : ''; ?>">
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Aba Cadastro -->
                        <div id="cadastro" class="tab-content hidden">
                            <h2 class="text-xl font-semibold mb-4">Configurações de Cadastro</h2>
                            <div class="space-y-6">
                                <div>
                                    <label class="block mb-2 font-medium">Campos Obrigatórios</label>
                                    <div class="space-y-2">
                                        <label for="nome" class="block mb-2 font-medium">Nome</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="campos_obrigatorios[]" value="nome"
                                                <?php echo isset($infos['campos_obrigatorios']) && strpos($infos['campos_obrigatorios'], 'nome') !== false ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>

                                        <br>
                                        <label for="email" class="block mb-2 font-medium">E-mail</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="campos_obrigatorios[]" value="email"
                                                <?php echo isset($infos['campos_obrigatorios']) && strpos($infos['campos_obrigatorios'], 'email') !== false ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>


                                        <br>
                                        <label for="cpf" class="block mb-2 font-medium">CPF</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="campos_obrigatorios[]" value="cpf"
                                                <?php echo isset($infos['campos_obrigatorios']) && strpos($infos['campos_obrigatorios'], 'cpf') !== false ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label for="termos_uso" class="block mb-2 font-medium">Termos de Uso</label>
                                    <textarea id="termos_uso" name="termos_uso" rows="4"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"><?php echo isset($infos['termos_uso']) ? $infos['termos_uso'] : ''; ?></textarea>
                                </div>
                                <div>
                                    <label for="politica_privacidade" class="block mb-2 font-medium">Política de Privacidade</label>
                                    <textarea id="politica_privacidade" name="politica_privacidade" rows="4"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"><?php echo isset($infos['politica_privacidade']) ? $infos['politica_privacidade'] : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Aba Social -->
                        <div id="social" class="tab-content hidden">
                            <h2 class="text-xl font-semibold mb-4">Redes Sociais</h2>
                            <div class="space-y-4">
                                <div>
                                        <label class="flex items-center space-x-2">
                                            <label class="toggle-switch">
                                                <input type="checkbox" name="habilitar_fale_conosco" id="habilitar_fale_conosco" value="1" 
                                                    <?php echo isset($infos['habilitar_fale_conosco']) && $infos['habilitar_fale_conosco'] ? 'checked' : ''; ?>>
                                                <div class="toggle-switch-background">
                                                    <div class="toggle-switch-handle"></div>
                                                </div>
                                            </label>
                                            <span>Habilitar botão fale conosco?</span>
                                        </label>
                                        <div id="link_fale_conosco">
                                            <label for="link_fale_conosco" class="block mb-2 font-medium">Link Fale Conosco</label>
                                            <div class="flex items-center space-x-2">
                                                <input type="text" name="link_fale_conosco" 
                                                    class="flex-1 bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                    value="<?php echo isset($infos['link_fale_conosco']) ? $infos['link_fale_conosco'] : ''; ?>">
                                            </div>
                                        </div>
                                    </div>    
                                <div>
                                    <label class="flex items-center space-x-2">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="habilitar_compartilhamento" value="1" 
                                                <?php echo isset($infos['habilitar_compartilhamento']) && $infos['habilitar_compartilhamento'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                        <span>Habilitar botões de compartilhamento?</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="flex items-center space-x-2">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_grupos" name="habilitar_grupos" value="1"
                                                <?php echo isset($infos['habilitar_grupos']) && $infos['habilitar_grupos'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                        <span>Habilitar botão para acessar o grupo?</span>
                                    </label>
                                    <div id="link_grupo">
                                        <label for="link_grupo" class="block mb-2 font-medium">Link do grupo</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="text" name="link_grupo"
                                                class="flex-1 bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                value="<?php echo isset($infos['link_grupo']) ? $infos['link_grupo'] : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <!-- Redes Sociais -->
                             <div class="space-y-4">
                                    <h3 class="text-lg font-medium">Redes Sociais</h3>
                                    
                                    <!-- YouTube -->
                                    <div>
                                        <label for="link_youtube" class="block mb-2 font-medium">YouTube</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="url" id="link_youtube" name="link_youtube" placeholder="https://youtube.com/seu-canal"
                                                class="flex-1 bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                value="<?php echo isset($infos['link_youtube']) ? $infos['link_youtube'] : ''; ?>">
                                        </div>
                                    </div>

                                    <!-- Facebook -->
                                    <div>
                                        <label for="link_facebook" class="block mb-2 font-medium">Facebook</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="url" id="link_facebook" name="link_facebook" placeholder="https://facebook.com/sua-pagina"
                                                class="flex-1 bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                value="<?php echo isset($infos['link_facebook']) ? $infos['link_facebook'] : ''; ?>">
                                        </div>
                                    </div>

                                    <!-- Instagram -->
                                    <div>
                                        <label for="link_instagram" class="block mb-2 font-medium">Instagram</label>
                                        <div class="flex items-center space-x-2">
                                            <input type="url" id="link_instagram" name="link_instagram" placeholder="https://instagram.com/seu-perfil"
                                                class="flex-1 bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                value="<?php echo isset($infos['link_instagram']) ? $infos['link_instagram'] : ''; ?>">
                                        </div>
                                    </div>

                                    <!-- WhatsApp -->
                            
                                </div>
                        </div>

                        <!-- Aba Rodapé -->
                        <div id="rodape" class="tab-content hidden">
                            <h2 class="text-xl font-semibold mb-4">Configurações do Rodapé</h2>
                            <div class="space-y-6">
                                <div>
                                    <label for="texto_rodape" class="block mb-2 font-medium">Texto do Rodapé</label>
                                    <textarea id="texto_rodape" name="texto_rodape" rows="3"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"><?php echo isset($infos['texto_rodape']) ? $infos['texto_rodape'] : ''; ?></textarea>
                                </div>
                                <div>
                                    <label for="copyright" class="block mb-2 font-medium">Copyright</label>
                                    <input type="text" id="copyright" name="copyright"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['copyright']) ? $infos['copyright'] : ''; ?>">
                                </div>
                                
                               
                            </div>
                        </div>

                        

                        <!-- Aba Integrações -->
                        <div id="integracoes" class="tab-content hidden">
                            <h2 class="text-xl font-semibold mb-4">Integrações</h2>
                            
                            <!-- TikTok -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium mb-4">TikTok</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="flex items-center space-x-2">
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="habilitar_api_tiktok" name="habilitar_api_tiktok" value="1"
                                                    <?php echo isset($infos['habilitar_api_tiktok']) && $infos['habilitar_api_tiktok'] ? 'checked' : ''; ?>>
                                                <div class="toggle-switch-background">
                                                    <div class="toggle-switch-handle"></div>
                                                </div>
                                            </label>
                                            <span>Habilitar API de conversão?</span>
                                        </label>
                                        <p class="text-sm text-gray-500 mt-1">Área destinada ao gestor do tráfego para implementação da API de conversão do tiktok.</p>
                                    </div>
                                    <div id="token_tiktok">
                                        <label for="token_tiktok" class="block mb-2 font-medium">Token TikTok</label>
                                        <input type="text" id="token_tiktok" name="token_tiktok"
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($infos['token_tiktok']) ? $infos['token_tiktok'] : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Kawai -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium mb-4">Kawai</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="flex items-center space-x-2">
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="habilitar_api_kawai" name="habilitar_api_kawai" value="1"
                                                    <?php echo isset($infos['habilitar_api_kawai']) && $infos['habilitar_api_kawai'] ? 'checked' : ''; ?>>
                                                <div class="toggle-switch-background">
                                                    <div class="toggle-switch-handle"></div>
                                                </div>
                                            </label>
                                            <span>Habilitar API de conversão?</span>
                                        </label>
                                        <p class="text-sm text-gray-500 mt-1">Área destinada ao gestor do tráfego para implementação da API de conversão do Kawai.</p>
                                    </div>
                                    <div id="token_kawai">
                                        <label for="token_kawai" class="block mb-2 font-medium">Token Kawai</label>
                                        <input type="text" id="token_kawai" name="token_kawai"
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($infos['token_kawai']) ? $infos['token_kawai'] : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Facebook -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium mb-4">Facebook</h3>
                                <div>
                                    <label class="flex items-center space-x-2">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_api_facebook" name="habilitar_api_facebook" value="1"
                                                <?php echo isset($infos['habilitar_api_facebook']) && $infos['habilitar_api_facebook'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                        <span>Habilitar API de conversão?</span>
                                    </label>
                                    <p class="text-sm text-gray-500 mt-1">Área destinada ao gestor do tráfego para implementação da API de conversão do Facebook ADS.</p>
                                </div>
                                <div id="token_facebook">
                                    <label for="pixel_facebook" class="block mb-2 font-medium">Token do Facebook</label>
                                    <input type="text" id="token_facebook" name="pixel_facebook"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['pixel_facebook']) ? $infos['pixel_facebook'] : ''; ?>">
                                </div>
                            </div>

                            <!-- Google -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium mb-4">Google</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="flex items-center space-x-2">
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="habilitar_google_analytics" name="habilitar_google_analytics" value="1"
                                                    <?php echo isset($infos['habilitar_google_analytics']) && $infos['habilitar_google_analytics'] ? 'checked' : ''; ?>>
                                                <div class="toggle-switch-background">
                                                    <div class="toggle-switch-handle"></div>
                                                </div>
                                            </label>
                                            <span>Habilitar Google Analytics?</span>
                                        </label>
                                        <p class="text-sm text-gray-500 mt-1">Área destinada ao gestor do tráfego para implementação do Google Analytics (GA4).</p>
                                    </div>
                                    <div id="token_google_analytics">
                                        <label for="token_google_analytics" class="block mb-2 font-medium">Token do Google Analytics</label>
                                        <input type="text" id="token_google_analytics" name="token_google_analytics"
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($infos['token_google_analytics']) ? $infos['token_google_analytics'] : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Utmify -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium mb-4">Utmify</h3>
                                <div>
                                    <label class="flex items-center space-x-2">
                                        <label class="toggle-switch">
                                            <input type="checkbox" name="habilitar_utmify" id="habilitar_utmify" value="1"
                                                <?php echo isset($infos['habilitar_utmify']) && $infos['habilitar_utmify'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                        <span>Habilitar integração com Utmify</span>
                                    </label>
                                    <p class="text-sm text-gray-500 mt-1">Área destinada ao gestor do tráfego para implementação do Utmify.</p>
                                </div>
                                <div id="token_utmify" class="space-y-6">
                                    <div>
                                        <label for="token_utmify" class="block mb-2 font-medium">Token do Utmify</label>
                                        <input type="text" id="token_utmify" name="token_utmify"
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($infos['token_utmify']) ? $infos['token_utmify'] : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aba FAQ -->
                        <div id="faq" class="tab-content hidden">
                            <h2 class="text-xl font-semibold mb-4">Perguntas Frequentes</h2>
                            <div class="space-y-6">
                                <div>
                                    <label for="perguntas_frequentes" class="block mb-2 font-medium">Perguntas e Respostas</label>
                                    <textarea id="perguntas_frequentes" name="perguntas_frequentes" rows="10" 
placeholder="
Pergunta1?/
Resposta1/
Pergunta2?/
Resposta2/"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"><?php echo isset($infos['perguntas_frequentes']) ? $infos['perguntas_frequentes'] : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Aba Termos -->
                        <div id="termos" class="tab-content hidden">
                            <h2 class="text-xl font-semibold mb-4">Termos e Condições</h2>
                            <div class="space-y-6">
                                <div>
                                    <label for="termos_condicoes" class="block mb-2 font-medium">Termos e Condições</label>
                                    <textarea id="termos_condicoes" name="termos_condicoes" rows="15"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"><?php echo isset($infos['termos_condicoes']) ? $infos['termos_condicoes'] : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Aba Pixels -->
                        <div id="gateways" class="tab-content hidden">
                            <h2 class="text-xl font-semibold mb-4">Configurações de Gateways</h2>
                          
                          <!-- Mercado Pago -->

                            <label for="mercadopago_token_acesso" class="block mb-2 font-medium">Habilitar Mercado Pago</label>
                            
                            <label class="toggle-switch">
                                <input type="checkbox" id="habilitar_mercadopago" name="habilitar_mercadopago" value="1"
                                    <?php echo isset($infos['habilitar_mercadopago']) && $infos['habilitar_mercadopago'] ? 'checked' : ''; ?>>
                                <div class="toggle-switch-background">
                                    <div class="toggle-switch-handle"></div>
                                </div>
                            </label>
                         

                          <div id="mercadopago_token_acesso" class="space-y-6">
                              <div>
                                  <label for="mercadopago_token_acesso" class="block mb-2 font-medium">Token Mercado Pago</label>
                                    <input type="text" id="mercadopago_token_acesso" name="mercadopago_token_acesso"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['mercadopago_token_acesso']) ? $infos['mercadopago_token_acesso'] : ''; ?>">
                                </div>
                            </div>

                            <!-- Pay2m -->
                          <label for="pay2m_token_acesso" class="block mb-2 font-medium">Habilitar Pay2m</label>
                          <div>
                              <label class="flex items-center space-x-2">
                                  <label class="toggle-switch">
                                      <input type="checkbox" id="habilitar_pay2m" name="habilitar_pay2m" value="1"
                                          <?php echo isset($infos['habilitar_pay2m']) && $infos['habilitar_pay2m'] ? 'checked' : ''; ?>>
                                      <div class="toggle-switch-background">
                                          <div class="toggle-switch-handle"></div>
                                      </div>
                                  </label>
                              </label>
                          </div>
                          
                          <div id="pay2m_client_key" class="space-y-6">
                              <div>
                                    <label for="pay2m_client_key" class="block mb-2 font-medium">Client Key</label>
                                    <input type="text" id="pay2m_client_key" name="pay2m_client_key"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['pay2m_client_key']) ? $infos['pay2m_client_key'] : ''; ?>">
                                </div>
                            </div>
                            <div id="pay2m_client_secret" class="space-y-6">
                              <div>
                                    <label for="pay2m_client_secret" class="block mb-2 font-medium">Client Secret</label>
                                    <input type="text" id="pay2m_client_secret" name="pay2m_client_secret"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['pay2m_client_secret']) ? $infos['pay2m_client_secret'] : ''; ?>">
                                </div>
                            </div>


                            <!-- Paggue -->
                          <label for="paggue_token_acesso" class="block mb-2 font-medium">Habilitar Paggue</label>
                          <div>
                              <label class="flex items-center space-x-2">
                                  <label class="toggle-switch">
                                      <input type="checkbox" id="habilitar_paggue" name="habilitar_paggue" value="1"
                                          <?php echo isset($infos['habilitar_paggue']) && $infos['habilitar_paggue'] ? 'checked' : ''; ?>>
                                      <div class="toggle-switch-background">
                                          <div class="toggle-switch-handle"></div>
                                      </div>
                                  </label>
                              </label>
                          </div>
                          
                          <div id="paggue_client_key" class="space-y-6">
                              <div>
                                  <label for="paggue_client_key" class="block mb-2 font-medium">Client Key</label>
                                    <input type="text" id="paggue_client_key" name="paggue_client_key"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['paggue_client_key']) ? $infos['paggue_client_key'] : ''; ?>">
                                </div>
                            </div>
                            <div id="paggue_client_secret" class="space-y-6">
                              <div>
                                  <label for="paggue_client_secret" class="block mb-2 font-medium">Client Secret</label>
                                    <input type="text" id="paggue_client_secret" name="paggue_client_secret"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['paggue_client_secret']) ? $infos['paggue_client_secret'] : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Aba Login -->
                        <div id="login" class="tab-content hidden">
                            <h2 class="text-xl font-semibold mb-4">Configurações da Tela de Login</h2>
                            <div class="space-y-6">
                                <div>
                                    <label for="login_bg_color" class="block mb-2 font-medium">Cor de Fundo do Login</label>
                                    <input type="color" id="login_bg_color" name="login_bg_color"
                                        class="w-full h-10 p-1 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($infos['login_bg_color']) ? $infos['login_bg_color'] : '#1F2937'; ?>">
                                </div>
                                <div>
                                    <label for="login_image" class="block mb-2 font-medium">Imagem do Login</label>
                                    <div class="flex items-center space-x-4">
                                        <div class="relative w-40 h-40 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                            <?php if (isset($infos['login_image']) && !empty($infos['login_image'])): ?>
                                                <img src="../<?php echo $infos['login_image']; ?>" alt="Imagem de Login" class="w-full h-full object-contain">
                                                <button type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
                                                    onclick="removerImagemLogin()">×</button>
                                            <?php else: ?>
                                                <div class="flex flex-col items-center justify-center h-full cursor-pointer" onclick="document.getElementById('login_image_input').click()">
                                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    <span class="mt-2 text-sm text-gray-500">Adicionar imagem</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="file" id="login_image_input" name="login_image" accept="image/*" class="hidden" onchange="previewLoginImage(this)">
                                        <input type="hidden" name="login_image_atual" value="<?php echo isset($infos['login_image']) ? $infos['login_image'] : ''; ?>">
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB</p>
                                </div>
                            </div>
                        </div>

                        <!-- Botão Salvar -->
                        <div class="mt-6">
                            <button type="submit" class="w-full md:w-auto bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-500 shadow">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Alternar entre abas
        const tabLinks = document.querySelectorAll(".tab-link");
        const tabContents = document.querySelectorAll(".tab-content");

        tabLinks.forEach(link => {
            link.addEventListener("click", (e) => {
                e.preventDefault();
                const targetTab = e.target.dataset.tab;

                // Esconde todas as abas
                tabContents.forEach(content => content.classList.add("hidden"));

                // Mostra a aba clicada
                document.getElementById(targetTab).classList.remove("hidden");

                // Remove estilos ativos de todas as abas
                tabLinks.forEach(link => {
                    link.classList.remove("text-purple-700", "dark:text-purple-400", "font-bold");
                    link.parentElement.classList.remove("border-b-2", "border-purple-500");
                });

                // Adiciona estilos na aba ativa
                e.target.classList.add("text-purple-700", "dark:text-purple-400", "font-bold");
                e.target.parentElement.classList.add("border-b-2", "border-purple-500");
            });
        });

        // Preview da logo
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = input.closest('.flex').querySelector('.relative');
                    const logo = document.getElementById('logo');
                    logo.src = e.target.result;
                    container.innerHTML = `
                        <img src="${e.target.result}" alt="Logo" class="w-full h-full object-contain">
                        <button type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
                            onclick="removerLogo()">×</button>
                    `;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Remover logo
        function removerLogo() {
            const container = document.querySelector('.logo-container .relative');
            const logo = document.getElementById('logo');
            if (logo) {
                logo.src = '';
            }
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full cursor-pointer" onclick="document.getElementById('logo_input').click()">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="mt-2 text-sm text-gray-500">Adicionar logo</span>
                </div>
            `;
            document.querySelector('input[name="logo_atual"]').value = '';
            document.getElementById('logo_input').value = '';
        }

        // Preview da imagem de login
        function previewLoginImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = input.closest('.flex').querySelector('.relative');
                    container.innerHTML = `
                        <img src="${e.target.result}" alt="Imagem de Login" class="w-full h-full object-contain">
                        <button type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
                            onclick="removerImagemLogin()">×</button>
                    `;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Remover imagem de login
        function removerImagemLogin() {
            const container = document.querySelector('#login .relative');
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full cursor-pointer" onclick="document.getElementById('login_image_input').click()">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="mt-2 text-sm text-gray-500">Adicionar imagem</span>
                </div>
            `;
            document.querySelector('input[name="login_image_atual"]').value = '';
            document.getElementById('login_image_input').value = '';
        }
    </script>
</body>
</html>
