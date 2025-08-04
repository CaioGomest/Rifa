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
    <title>Debug do Tema</title>
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
        <h1 class="text-3xl font-bold mb-8 text-center">Debug do Tema</h1>
        
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Informações do banco -->
                    <div class="bg-white dark:bg-gray-800  p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-4">Informações do Banco</h2>
                <p><strong>Valor no banco:</strong> <?php echo $config['tema']; ?></p>
                <p><strong>isDarkMode:</strong> <?php echo $isDarkMode ? 'true' : 'false'; ?></p>
                <p><strong>themeFromBank:</strong> <span id="themeFromBank">Carregando...</span></p>
            </div>

            <!-- Teste de mapeamento -->
                    <div class="bg-white dark:bg-gray-800  p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-4">Teste de Mapeamento</h2>
                <div id="mappingTest">
                    <p><strong>Mapeamento:</strong></p>
                    <ul class="list-disc list-inside">
                        <li>escuro → dark</li>
                        <li>padrao → light</li>
                    </ul>
                </div>
            </div>

            <!-- Controles -->
                    <div class="bg-white dark:bg-gray-800  p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-4">Controles</h2>
                <div class="flex space-x-4">
                    <button onclick="testMapping()" class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Testar Mapeamento
                    </button>
                    <button onclick="forceSync()" class="bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 text-white px-4 py-2 rounded">
                        Forçar Sincronização
                    </button>
                </div>
            </div>

            <!-- Logs -->
                    <div class="bg-white dark:bg-gray-800  p-6 rounded-lg shadow-md">
          <h2 class="text-xl font-semibold mb-4">Logs</h2>
                <div id="logs" class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm font-mono max-h-64 overflow-y-auto">
                    <p>Logs aparecerão aqui...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Atualiza informações
        function updateInfo() {
            document.getElementById('themeFromBank').textContent = window.themeFromBank || 'não definido';
        }

        // Testa o mapeamento
        function testMapping() {
            const logs = document.getElementById('logs');
            logs.innerHTML = '';
            
            function addLog(message) {
                logs.innerHTML += '<p>' + new Date().toLocaleTimeString() + ': ' + message + '</p>';
                logs.scrollTop = logs.scrollHeight;
            }

            addLog('Testando mapeamento...');
            addLog('themeFromBank: ' + window.themeFromBank);
            
            if (window.themeManager) {
                const mappedTheme = window.themeManager.getThemeFromBank();
                addLog('Tema mapeado: ' + mappedTheme);
                addLog('localStorage atual: ' + localStorage.getItem('theme'));
            } else {
                addLog('ThemeManager não encontrado!');
            }
        }

        // Força sincronização
        function forceSync() {
            const logs = document.getElementById('logs');
            logs.innerHTML = '';
            
            function addLog(message) {
                logs.innerHTML += '<p>' + new Date().toLocaleTimeString() + ': ' + message + '</p>';
                logs.scrollTop = logs.scrollHeight;
            }

            addLog('Forçando sincronização...');
            
            if (window.themeManager) {
                window.themeManager.syncWithDatabase();
                window.themeManager.applyTheme();
                addLog('Sincronização concluída');
                addLog('Tema atual: ' + (document.documentElement.classList.contains('dark') ? 'dark' : 'light'));
            } else {
                addLog('ThemeManager não encontrado!');
            }
        }

        // Atualiza a cada segundo
        setInterval(updateInfo, 1000);
        updateInfo();
    </script>
</body>
</html> 