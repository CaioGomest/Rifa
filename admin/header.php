<?php
ob_start();
require_once(__DIR__ . "/../functions/functions_pedidos.php");
require_once(__DIR__ . "/../functions/functions_campanhas.php");
require __DIR__ . '/../functions/functions_sistema.php';
require __DIR__ . '/../functions/functions_usuarios.php';
require __DIR__ . '/../functions/functions_uploads.php';
require __DIR__ . '/../conexao.php';

$config = listaInformacoes($conn);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit;
}

// Lista de páginas restritas apenas para administradores
$admin_pages = [
    'index.php',
    'campanhas.php',
    'campanha.php',
    'pedidos.php',
    'pedido.php',
    'clientes.php',
    'cliente.php',
    'usuarios.php',
    'usuario.php',
    'relatorios.php',
    'configuracoes.php'
];

// Lista de páginas restritas apenas para afiliados
$affiliate_pages = [
    'afiliados.php',
    'configuracoes_afiliado.php'
];

// pega o nome do arquivo atual
$current_page = basename($_SERVER['PHP_SELF']);

// Verificar permissões
if ($_SESSION['usuario']['usuario_tipo'] == 2) { // Afiliado
    if (in_array($current_page, $admin_pages)) {
        header("Location: afiliados.php");
        exit;
    }
} else { // Admin
    if (in_array($current_page, $affiliate_pages)) {
        header("Location: index.php");
        exit;
    }
}
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$page_title = '';
switch ($current_page) {
    case 'pedidos':
        $page_title = 'Pedidos';
        break;
    case 'pedido':
        $page_title = 'Detalhes do Pedido';
        break;
    case 'campanhas':
        $page_title = 'Campanhas';
        break;
    case 'campanha':
        $page_title = 'Detalhes da Campanha';
        break;
    case 'usuarios':
        $page_title = 'Usuários';
        break;
    case 'usuario':
        $page_title = 'Detalhes do Usuário';
        break;
    case 'clientes':
        $page_title = 'Clientes';
        break;
    case 'cliente':
        $page_title = 'Detalhes do Cliente';
        break;
    case 'configuracoes':
        $page_title = 'Configurações';
        break;
    case 'relatorios':
        $page_title = 'Relatórios';
        break;
    case 'gerenciar_afiliados':
        $page_title = 'Afiliados';
        break;
    case 'afiliado_relatorio':
        $page_title = 'Relatório de Afiliados';
        break;
    case 'configuracoes_afiliado':
        $page_title = 'Configurações do Afiliado';
        break;
    default:
        $page_title = 'Dashboard';
}

$config = listaInformacoes($conn);
$isDarkMode = $config['tema'] == 'escuro';

?>

<!DOCTYPE html>
<html lang="pt-BR" class="<?php echo $isDarkMode ? 'dark' : ''; ?>">

<head>
    <title><?=$page_title . ' - ' . $config['titulo'];?></title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="<?php echo "../" . $config['logo']; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
      // Define o tema do banco para o gerenciador
      window.themeFromBank = '<?php echo $config['tema']; ?>';
    </script>
    <script src="../assets/js/theme-manager.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/dark-theme.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="../assets/css/index.css">
</head>

<body class="bg-gray-100 dark:bg-[#18181B] text-gray-900 dark:text-gray-100">
   
    <!-- Header -->
    <div class="bg-white dark:bg-[#27272A] shadow-md p-4 mb-2  flex justify-between items-center">
        <div class="flex items-center">
            <!-- Espaço para o botão do menu em mobile -->
            <div class="w-8 lg:hidden"></div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white ml-2 lg:ml-0">
                <?php
                $current_page = basename($_SERVER['PHP_SELF'], '.php');
                switch ($current_page) {
                    case 'pedidos':
                        echo 'Pedidos';
                        break;
                    case 'campanhas':
                        echo 'Campanhas';
                        break;
                    case 'usuarios':
                        echo 'Usuários';
                        break;
                    case 'clientes':
                        echo 'Clientes';
                        break;
                    case 'configuracoes':
                        echo 'Configurações';
                        break;
                    default:
                        echo 'Dashboard';
                }
                ?>
            </h1>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Botão Dark Mode -->
            <button onclick="mudarModo()" class="text-white hover:text-green-500 p-2 rounded-full" title="Alternar Modo Escuro">
                
            <i class="fas fa-moon dark:text-yellow-300 text-gray-600 text-xl"></i>
            <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg> -->
            </button>
            <div class="flex items-center space-x-2">
                <span class="hidden md:inline text-sm font-medium text-gray-700 dark:text-gray-300">
                    <?= htmlspecialchars($_SESSION['usuario']['usuario_tipo'] == 2 ? 'Afiliado' : 'Admin') ?>
                </span>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    <?= htmlspecialchars($_SESSION['usuario']['usuario_nome']) ?>
                </span>
                <?php
                if (!empty($_SESSION['usuario']['usuario_avatar'])): ?>
                    <img src="../<?= htmlspecialchars($_SESSION['usuario']['usuario_avatar']) ?>" alt="Foto do usuário"
                        class="w-8 h-8 rounded-full object-cover border-2 border-purple-600">
                <?php else: ?>
                    <div class="w-8 h-8 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($_SESSION['usuario']['usuario_nome'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>
<script>
    function habilitaCampo(input, campo)
    {
        // Verifica e aplica o estado inicial
        const checkbox = $('#'+input);
        const targetField = $('#'+campo);
        
        if (!checkbox.is(':checked')) {
            targetField.hide();
        } else {
            targetField.show();
        }

        // Adiciona o listener para mudanças
        checkbox.on('change', function()
        {
            if ($(this).is(':checked')) {
                targetField.show('');
            } else {
                targetField.hide('');
            }
        });
    }
   
document.addEventListener('DOMContentLoaded', function() {

    habilitaCampo('habilitar_grupos', 'link_grupo');
    habilitaCampo('habilitar_fale_conosco', 'link_fale_conosco');

    // Inicializa os campos de gateway de pagamento
    habilitaCampo('habilitar_mercadopago', 'mercadopago_token_acesso');
    habilitaCampo('habilitar_pay2m', 'pay2m_client_key');
    habilitaCampo('habilitar_pay2m', 'pay2m_client_secret');
    habilitaCampo('habilitar_paggue', 'paggue_client_key');
    habilitaCampo('habilitar_paggue', 'paggue_client_secret');

    // Inicializa os campos de integração
    habilitaCampo('habilitar_api_facebook', 'token_facebook');
    habilitaCampo('habilitar_api_tiktok', 'token_tiktok');
    habilitaCampo('habilitar_api_kawai', 'token_kawai');
    habilitaCampo('habilitar_google_analytics', 'token_google_analytics');
    habilitaCampo('habilitar_utmify', 'token_utmify');

    // Força a verificação inicial de todos os campos
    $('input[type="checkbox"]').each(function() {
        const checkbox = $(this);
        const id = checkbox.attr('id');
        if (id) {
            const targetId = id.replace('habilitar_', '');
            if ($('#' + targetId).length) {
                if (checkbox.is(':checked')) {
                    $('#' + targetId).show();
                } else {
                    $('#' + targetId).hide();
                }
            }
        }
    });
});
</script>

