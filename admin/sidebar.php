<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}

// Garantir que o tipo de usuário seja um número
$usuario_tipo = isset($_SESSION['usuario']['usuario_tipo']) ? intval($_SESSION['usuario']['usuario_tipo']) : 0;

// Se não houver tipo definido, fazer logout por segurança
if (!$usuario_tipo) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}   
?>

<!-- Botão do Menu Mobile -->
<button id="mobile-menu-button" class="absolute top-4 left-4 z-50 p-2 rounded-md bg-transparent lg:hidden">
    <i class="fas fa-bars text-gray-600 dark:text-gray-300"></i>
</button>

<!-- Overlay para fechar o menu em mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed lg:relative inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 shadow-md flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50 h-screen lg:h-auto">
    <!-- Logo e Botão Fechar -->

    <div class="p-4 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
        <img id="logo" src="../<?php echo $config['logo']; ?>" alt="Logo do Sistema" class="h-12">
        <button id="close-sidebar" class="lg:hidden text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    
    <nav class="flex-1 overflow-y-auto py-4">
        <!-- Menu Principal -->
        <div class="px-3">
            <h2 class="mb-2 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Principal</h2>
            <ul class="space-y-1">
                <?php if ($usuario_tipo == 2): // Menu para Afiliados ?>
                    <li>
                        <a href="afiliados.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-chart-line w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Dashboard</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    <li>
                        <a href="configuracoes_afiliado.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-cog w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Configurações</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    <li>
                        <a href="afiliado_links.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                        <i class="fas fa-link w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Links</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                <?php else: // Menu para Administradores ?>
                    <li>
                        <a href="index.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-home w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Dashboard</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    
                    <h2 class="mt-6 mb-2 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Gerenciamento</h2>
                    <li>
                        <a href="campanhas.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-bullhorn w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Campanhas</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    <li>
                        <a href="pedidos.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-shopping-cart w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Pedidos</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    
                    <h2 class="mt-6 mb-2 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Usuários</h2>
                    <li>
                        <a href="clientes.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-users w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Clientes</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    <li>
                        <a href="usuarios.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-user-cog w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Usuários</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    <li>
                        <a href="gerenciar_afiliados.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-handshake w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Afiliados</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    
                    <h2 class="mt-6 mb-2 px-4 text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Sistema</h2>
                    <li>
                        <a href="relatorios.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-chart-bar w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Relatórios</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                    <li>
                        <a href="configuracoes.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg group relative">
                            <i class="fas fa-cog w-5 h-5 mr-3 text-gray-400 group-hover:text-purple-500"></i>
                            <span>Configurações</span>
                            <span class="absolute left-0 w-1 h-8 bg-purple-500 rounded-r-full opacity-0 group-hover:opacity-100 transition-opacity"></span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Rodapé com botões de ação -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <button class="w-full mb-2 py-2 px-4 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200 flex items-center justify-center">
            <i class="fas fa-headset mr-2"></i>
            <span>Suporte</span>
        </button>
        
        <a href="logout.php" class="w-full py-2 px-4 bg-red-50 dark:bg-opacity-10 text-red-600 dark:text-red-500 hover:bg-red-500 hover:text-white rounded-lg transition-all duration-200 flex items-center justify-center">
            <i class="fas fa-sign-out-alt mr-2"></i>
            <span>Sair</span>
        </a>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const closeSidebarButton = document.getElementById('close-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    mobileMenuButton.addEventListener('click', openSidebar);
    closeSidebarButton.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);

    // Fechar sidebar ao clicar em um link em telas móveis
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) { // lg breakpoint
                closeSidebar();
            }
        });
    });

    // Ajustar sidebar ao redimensionar a janela
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) { // lg breakpoint
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    });
});
</script>