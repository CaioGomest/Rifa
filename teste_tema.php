<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
date_default_timezone_set('America/Sao_Paulo');
require 'conexao.php';
require 'functions/functions_sistema.php';

$config = listaInformacoes($conn);
$isDarkMode = $config['tema'] == 'escuro';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="<?php echo $isDarkMode ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste do Tema Escuro</title>
    <script>
        window.themeFromBank = '<?php echo $config['tema']; ?>';
    </script>
    <script src="assets/js/theme-manager.js"></script>
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
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-center">Teste do Tema Escuro</h1>
        
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Informações do banco -->
            <div class="bg-white dark:bg-gray-800  p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Informações do Banco</h2>
                <p><strong>Tema no banco:</strong> <?php echo $config['tema']; ?></p>
                <p><strong>isDarkMode:</strong> <?php echo $isDarkMode ? 'true' : 'false'; ?></p>
                <p><strong>Classe HTML:</strong> <?php echo $isDarkMode ? 'dark' : 'claro'; ?></p>
            </div>

            <!-- Teste de classes Tailwind -->
            <div class="bg-white dark:bg-gray-800  p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Teste de Classes Tailwind</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded border border-blue-200 dark:border-blue-700">
                        <h3 class="font-semibold text-blue-800 dark:text-blue-200">Card Azul</h3>
                        <p class="text-blue-600 dark:text-blue-300">Este card deve mudar de cor com o tema.</p>
                    </div>
                    
                    <div class="bg-green-100 dark:bg-green-900 p-4 rounded border border-green-200 dark:border-green-700">
                        <h3 class="font-semibold text-green-800 dark:text-green-200">Card Verde</h3>
                        <p class="text-green-600 dark:text-green-300">Este card também deve mudar de cor.</p>
                    </div>
                </div>
            </div>

            <!-- Botão de teste -->
            <div class="bg-white dark:bg-gray-800  p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Controles</h2>
                <div class="flex space-x-4">
                    <button onclick="mudarModo()" class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Alternar Tema
                    </button>
                    <button onclick="localStorage.removeItem('theme'); location.reload();" class="bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 text-white px-4 py-2 rounded">
                        Limpar localStorage
                    </button>
                </div>
            </div>

            <!-- Informações do localStorage -->
            <div class="bg-white dark:bg-gray-800  p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-4">Informações do localStorage</h2>
                <div id="localStorageInfo">
                    <p><strong>Tema salvo:</strong> <span id="savedTheme">Carregando...</span></p>
                    <p><strong>Classe dark ativa:</strong> <span id="darkClass">Carregando...</span></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Atualiza informações do localStorage
        function updateInfo() {
            const savedTheme = localStorage.getItem('theme') || 'não definido';
            const hasDarkClass = document.documentElement.classList.contains('dark');
            
            document.getElementById('savedTheme').textContent = savedTheme;
            document.getElementById('darkClass').textContent = hasDarkClass ? 'Sim' : 'Não';
        }

        // Atualiza a cada segundo
        setInterval(updateInfo, 1000);
        updateInfo();
    </script>
</body>
</html> 