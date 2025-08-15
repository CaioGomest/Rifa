<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
date_default_timezone_set('America/Sao_Paulo');
require 'conexao.php';
require 'functions/functions_sistema.php';
require 'functions/functions_campanhas.php';
require 'functions/functions_clientes.php';
$config = listaInformacoes($conn);
$campos_obrigatorios = explode(',', $config['campos_obrigatorios']);
$isDarkMode = $config['tema'] == 'escuro';
// var_dump($config['tema']);  
?>


<!DOCTYPE html>
<html lang="pt-BR" class="<?php echo $isDarkMode ? 'dark' : ''; ?>">

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <script>
    // Define o tema do banco para o gerenciador
    window.themeFromBank = '<?php echo $config['tema']; ?>';
  </script>
  <script src="assets/js/theme-manager.js"></script>
  <link rel="icon" type="image/png" href="<?php echo $config['logo']; ?>">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/dark-theme.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {}
      }
    }
  </script>

  <style>
    /* Prevenir FOUC */
    html {
      visibility: hidden;
      opacity: 0;
    }

    html.visible {
      visibility: visible;
      opacity: 1;
      transition: opacity 0.2s ease-in-out;
    }

    /* Ajustar tamanho da logo */
    .logo-container img {
      max-height: 65px;
      width: auto;
      object-fit: contain;
    }

    /* efeito hover */
    /* Classe para o efeito hover com borda verde que expande */

    .borda-animada:hover {
      box-shadow: 0px 0px 0px 3px rgba(0, 255, 0, 0.5);
      transition: box-shadow 0.1s ease-in-out;
    }
  </style>
  <script>
    // Aplica o tema e torna a página visível
    (function () {
      const theme = localStorage.getItem('theme') || '<?php echo $isDarkMode ? "dark" : "light"; ?>';
      document.documentElement.classList.toggle('dark', theme === 'dark');
      document.documentElement.classList.add('visible');
    })();
  </script>
</head>

<header class="bg-black shadow-md">
  <title><?php echo $config['titulo']; ?></title>

  <div class="container mx-auto px-6 py-4 pb-6">
    <div class="w-full md:w-4/5 lg:w-3/5 mx-auto relative ">
      <div class="bg-black rounded-t-lg shadow-lg flex justify-between items-center">
        <a href="index.php">
          <div class="container mx-auto flex justify-between items-center logo-container">
            <?php if (!empty($config['logo'])): ?>
              <img src="<?php echo $config['logo']; ?>" alt="Logo" class="h-16">
            <?php else: ?>
              <h1 class="text-2xl font-bold text-green-500"><?php echo $config['titulo']; ?></h1>
            <?php endif; ?>
          </div>
        </a>
        <div class="flex items-center space-x-3">
          <?php
          $nomeUsuario = '';
          if (!empty($_SESSION)) {
            $nomeUsuario = $_SESSION["usuario"]["cliente_nome"] ?? $_SESSION["usuario"]["usuario_nome"] ?? '';
          }
          ?>
          <?php if (!empty($nomeUsuario)) { ?>
            <div class="flex items-center space-x-2">
              <span class="hidden sm:inline text-sm font-semibold text-green-500">Olá, <?php echo strtoupper($nomeUsuario); ?></span>
              <?php
              $inicial = function_exists('mb_substr') ? mb_substr($nomeUsuario, 0, 1, 'UTF-8') : substr($nomeUsuario, 0, 1);
              $inicial = strtoupper($inicial);
              ?>
              <div class="w-8 h-8 rounded-full bg-gray-700 text-white dark:bg-gray-200 dark:text-gray-800 flex items-center justify-center text-sm font-bold">
                <?php echo $inicial; ?>
              </div>
            </div>
          <?php } else { ?>
            <i class="fas fa-user text-green-500 text-xl"></i>
          <?php } ?>

          <button id="sidebarButton" class="text-green-500 hover:text-green-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- Sidebar -->
<div id="sidebar"
  class="fixed right-0 top-0 w-64 h-full bg-gray-200 dark:bg-[#27272A] text-gray-800 dark:text-white transform translate-x-full transition-transform duration-300 z-50">
  <!-- Cabeçalho da Sidebar -->
  <div class="p-4 flex justify-between items-center border-b border-gray-700 dark:border-gray-300">
    <h2 class="text-2xl font-bold">Menu</h2>
    <button id="closeSidebar" class="text-gray-400 hover:text-white dark:text-gray-600 dark:hover:text-gray-800">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>

  <!-- Links do Menu -->
  <ul class="mt-4 space-y-2">
    <?php
    if (!empty($_SESSION)) { ?>
      <li>
        <a class="flex items-center px-4 py-2  transition-all duration-300 font-bold">
          Olá <?php echo $_SESSION["usuario"]["cliente_nome"] ?? $_SESSION["usuario"]["usuario_nome"]; ?>
        </a>
      </li>
    <?php } ?>
    
    <li>
      <a href="index.php"
        class="flex items-center px-4 py-2 hover:bg-gray-700 dark:hover:bg-gray-300 transition-all duration-300">
        <i class="fas fa-home mr-3 text-gray-500 hover:text-white transition-colors duration-200"></i>
        Home
      </a>
    </li>

    <?php if (!empty($_SESSION)) { ?>
      <?php 
      // Verifica se é um cliente (tem cliente_id) ou usuário do sistema
      $isCliente = isset($_SESSION["usuario"]["cliente_id"]);
      $isUsuarioSistema = isset($_SESSION["usuario"]["usuario_id"]);
      ?>
      
      <?php if ($isCliente) { ?>
        <!-- Menu para Clientes -->
        <li>
          <a href="meus_titulos.php?cliente_id=<?php echo $_SESSION["usuario"]["cliente_id"]; ?>"
            class="flex items-center px-4 py-2 hover:bg-gray-700 dark:hover:bg-gray-300 transition-all duration-300">
            <i class="fas fa-certificate mr-3 text-gray-500 hover:text-white transition-colors duration-200"></i>
            Meus Títulos
          </a>
        </li>
      <?php } ?>

      <?php if ($isUsuarioSistema) { ?>
        <!-- Menu para Usuários do Sistema (Admin/Afiliado) -->
        <li>
          <a href="admin/"
            class="flex items-center px-4 py-2 hover:bg-gray-700 dark:hover:bg-gray-300 transition-all duration-300">
            <i class="fas fa-user mr-3 text-gray-500 hover:text-white transition-colors duration-200"></i>
            Painel
          </a>
        </li>
      <?php } ?>

      <li>
        <a href="logout.php"
          class="flex items-center px-4 py-2 hover:bg-red-600 transition-all duration-300 text-red-400 hover:text-white">
          <i class="fas fa-sign-out-alt mr-3"></i>
          Sair
        </a>
      </li>
    <?php } else { ?>
      <li>
        <a href="login_cliente.php"
          class="flex items-center px-4 py-2 hover:bg-gray-700 dark:hover:bg-gray-300 transition-all duration-300">
          <i class="fas fa-sign-in-alt mr-3 text-gray-500 hover:text-white transition-colors duration-200"></i>
          Entrar / Cadastrar
        </a>
      </li>
    <?php } ?>
  </ul>
</div>

<script>
  // Referências
  const sidebar = document.getElementById('sidebar');
  const sidebarButton = document.getElementById('sidebarButton');
  const closeSidebar = document.getElementById('closeSidebar');

  // Abrir Sidebar
  sidebarButton.addEventListener('click', () => {
    sidebar.classList.remove('translate-x-full');
    sidebar.classList.add('translate-x-0');
  });

  // Fechar Sidebar
  closeSidebar.addEventListener('click', () => {
    sidebar.classList.remove('translate-x-0');
    sidebar.classList.add('translate-x-full');
  });

  // Fechar Sidebar ao clicar fora
  document.addEventListener('click', (event) => {
    if (!sidebar.contains(event.target) && !sidebarButton.contains(event.target)) {
      sidebar.classList.add('translate-x-full');
      sidebar.classList.remove('translate-x-0');
    }
  });
</script>