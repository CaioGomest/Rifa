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

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario']['cliente_id']) || isset($_SESSION['usuario']['usuario_id'])) {
    $acao = $_GET['acao'] ?? 'index';
    
    // Verifica se é um cliente ou usuário do sistema
    if (isset($_SESSION['usuario']['cliente_id'])) {
        // É um cliente
        if ($acao === 'comprar') {
            header('Location: campanha.php?id=' . ($_GET['campanha_id'] ?? ''));
        } elseif ($acao === 'titulos') {
            header('Location: meus_titulos.php?cliente_id=' . $_SESSION['usuario']['cliente_id']);
        } else {
            header('Location: index.php');
        }
    } else {
        // É um usuário do sistema (admin/afiliado)
        if ($acao === 'comprar' || $acao === 'titulos') {
            // Usuários do sistema não podem comprar nem ver títulos
            header('Location: index.php');
        } else {
            header('Location: index.php');
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR" class="<?php echo $isDarkMode ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $config['nome_site']; ?></title>
    <link rel="icon" type="image/png" href="<?php echo $config['logo']; ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      // Define o tema do banco para o gerenciador
      window.themeFromBank = '<?php echo $config['tema']; ?>';
    </script>
    <script src="assets/js/theme-manager.js"></script>
    <link rel="stylesheet" href="assets/css/dark-theme.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#22C55A',
                        'primary-dark': '#16A34A'
                    }
                }
            }
        }
    </script>
    <style>
        .bg-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .bg-gradient-dark {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        }
        .form-input {
            transition: all 0.3s ease;
        }
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #22C55A 0%, #16A34A 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(34, 197, 90, 0.3);
        }
        .loading {
            display: none;
        }
        .loading.active {
            display: inline-block;
        }
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        .success-message {
            color: #22C55A;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-[#18181B] min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo e Título -->
            <div class="text-center">
                <div class="mx-auto h-20 w-20 mb-4">
                    <img class="h-full w-full object-contain" src="<?php echo $config['logo']; ?>" alt="Logo">
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">
                    Bem-vindo de volta!
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Faça login ou crie sua conta para continuar
                </p>
            </div>

            <!-- Tabs -->
            <div class="flex rounded-lg dark:bg-[#27272A] bg-gray-100 p-1 mb-6">
                <button id="tab-login" class="tab-btn active flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                </button>
                <button id="tab-cadastro" class="tab-btn flex-1 py-2 px-4 rounded-md text-sm font-medium transition-all duration-200">
                    <i class="fas fa-user-plus mr-2"></i>Cadastrar
                </button>
            </div>

            <!-- Formulário de Login -->
            <div id="form-login" class="form-container">
                <form id="loginForm" class="space-y-6">
                    <div>
                        <label for="login-telefone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-phone mr-2"></i>Telefone
                        </label>
                        <input id="login-telefone" name="telefone" type="tel" required
                            class="form-input w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-[#27272A]  text-white dark:text-gray-900 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Digite seu telefone">
                        <div id="login-telefone-error" class="error-message"></div>
                    </div>

                    <div>
                        <button type="submit" id="login-btn" class="btn-primary w-full flex justify-center py-3 px-4 border border-transparent rounded-lg text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <span id="login-text">Entrar</span>
                            <i id="login-loading" class="loading fas fa-spinner fa-spin ml-2"></i>
                        </button>
                    </div>

                    <div id="login-message" class="text-center"></div>
                </form>
            </div>

            <!-- Formulário de Cadastro -->
            <div id="form-cadastro" class="form-container hidden">
                <form id="cadastroForm" class="space-y-6">
                    <div>
                        <label for="cadastro-telefone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-phone mr-2"></i>Telefone
                        </label>
                        <input id="cadastro-telefone" name="telefone" type="tel" required
                            class="form-input w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-[#27272A]  text-white dark:text-gray-900 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Digite seu telefone">
                        <div id="cadastro-telefone-error" class="error-message"></div>
                    </div>

                    <?php if (empty($campos_obrigatorios) || in_array('nome', $campos_obrigatorios)): ?>
                    <div>
                        <label for="cadastro-nome" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-user mr-2"></i>Nome Completo
                        </label>
                        <input id="cadastro-nome" name="nome" type="text" <?php echo in_array('nome', $campos_obrigatorios) ? 'required' : ''; ?>
                            class="form-input w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-[#27272A]  text-white dark:text-gray-900 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Digite seu nome completo">
                        <div id="cadastro-nome-error" class="error-message"></div>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($campos_obrigatorios) || in_array('email', $campos_obrigatorios)): ?>
                    <div>
                        <label for="cadastro-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-envelope mr-2"></i>E-mail
                        </label>
                        <input id="cadastro-email" name="email" type="email" <?php echo in_array('email', $campos_obrigatorios) ? 'required' : ''; ?>
                            class="form-input w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-[#27272A]  text-white dark:text-gray-900 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Digite seu e-mail">
                        <div id="cadastro-email-error" class="error-message"></div>
                    </div>
                    <?php endif; ?>

                    <?php if (in_array('cpf', $campos_obrigatorios)): ?>
                    <div>
                        <label for="cadastro-cpf" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-id-card mr-2"></i>CPF
                        </label>
                        <input id="cadastro-cpf" name="cpf" type="text" required
                            class="form-input w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-[#27272A]  text-white dark:text-gray-900 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="Digite seu CPF">
                        <div id="cadastro-cpf-error" class="error-message"></div>
                    </div>
                    <?php endif; ?>

                    <div>
                        <button type="submit" id="cadastro-btn" class="btn-primary w-full flex justify-center py-3 px-4 border border-transparent rounded-lg text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <span id="cadastro-text">Criar Conta</span>
                            <i id="cadastro-loading" class="loading fas fa-spinner fa-spin ml-2"></i>
                        </button>
                    </div>

                    <div id="cadastro-message" class="text-center"></div>
                </form>
            </div>

            <!-- Voltar ao site -->
            <div class="text-center mt-8">
                <a href="index.php" class="text-primary hover:text-primary-dark font-medium transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar ao site
                </a>
            </div>
        </div>
    </div>

    <script>
        // Controle das tabs
        const tabLogin = document.getElementById('tab-login');
        const tabCadastro = document.getElementById('tab-cadastro');
        const formLogin = document.getElementById('form-login');
        const formCadastro = document.getElementById('form-cadastro');

        function switchTab(tab) {
            // Remove active de todas as tabs
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-white', 'text-gray-900', 'shadow-sm');
                btn.classList.add('text-gray-600', 'dark:text-gray-400');
            });

            // Adiciona active na tab selecionada
            tab.classList.add('active', 'bg-white', 'text-gray-900', 'shadow-sm');
            tab.classList.remove('text-gray-600', 'dark:text-gray-400');

            // Mostra o formulário correspondente
            if (tab.id === 'tab-login') {
                formLogin.classList.remove('hidden');
                formCadastro.classList.add('hidden');
            } else {
                formCadastro.classList.remove('hidden');
                formLogin.classList.add('hidden');
            }
        }

        tabLogin.addEventListener('click', () => switchTab(tabLogin));
        tabCadastro.addEventListener('click', () => switchTab(tabCadastro));

        // Máscara para telefone
        function maskPhone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            input.value = value;
        }

        document.getElementById('login-telefone').addEventListener('input', function() {
            maskPhone(this);
        });

        document.getElementById('cadastro-telefone').addEventListener('input', function() {
            maskPhone(this);
        });

        // Máscara para CPF
        function maskCPF(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = value;
        }

        const cpfInput = document.getElementById('cadastro-cpf');
        if (cpfInput) {
            cpfInput.addEventListener('input', function() {
                maskCPF(this);
            });
        }

        // Função para mostrar/ocultar loading
        function setLoading(formId, loading) {
            const btn = document.getElementById(`${formId}-btn`);
            const text = document.getElementById(`${formId}-text`);
            const loadingIcon = document.getElementById(`${formId}-loading`);
            
            if (loading) {
                btn.disabled = true;
                text.style.display = 'none';
                loadingIcon.classList.add('active');
            } else {
                btn.disabled = false;
                text.style.display = 'inline';
                loadingIcon.classList.remove('active');
            }
        }

        // Função para mostrar mensagem
        function showMessage(formId, message, type = 'error') {
            const messageDiv = document.getElementById(`${formId}-message`);
            messageDiv.textContent = message;
            messageDiv.className = `text-center ${type === 'error' ? 'error-message' : 'success-message'}`;
        }

        // Função para limpar erros
        function clearErrors(formId) {
            const inputs = document.querySelectorAll(`#${formId}Form input`);
            inputs.forEach(input => {
                const errorDiv = document.getElementById(`${formId}-${input.name}-error`);
                if (errorDiv) {
                    errorDiv.textContent = '';
                }
            });
            showMessage(formId, '', '');
        }

        // Login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors('login');
            
            const formData = new FormData(this);
            const telefone = formData.get('telefone').replace(/\D/g, '');

            if (!telefone) {
                showMessage('login', 'Por favor, informe seu telefone.', 'error');
                return;
            }

            setLoading('login', true);

            fetch('verificar_cliente_telefone.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                setLoading('login', false);
                
                if (data.success) {
                    showMessage('login', 'Login realizado com sucesso! Redirecionando...', 'success');
                    setTimeout(() => {
                        // Verificar se há uma ação específica para redirecionar
                        const urlParams = new URLSearchParams(window.location.search);
                        const acao = urlParams.get('acao');
                        
                        // Verifica se é um cliente ou usuário do sistema
                        if (data.isCliente) {
                            // É um cliente
                            if (acao === 'comprar') {
                                window.location.href = 'campanha.php?id=' + (urlParams.get('campanha_id') || '');
                            } else if (acao === 'titulos') {
                                window.location.href = 'meus_titulos.php?cliente_id=' + data.cliente_id;
                            } else {
                                window.location.href = 'index.php';
                            }
                        } else {
                            // É um usuário do sistema (admin/afiliado)
                            if (acao === 'comprar' || acao === 'titulos') {
                                // Usuários do sistema não podem comprar nem ver títulos
                                alert('Usuários do sistema não podem comprar cotas ou visualizar títulos.');
                                window.location.href = 'index.php';
                            } else {
                                window.location.href = 'index.php';
                            }
                        }
                    }, 1500);
                } else if (data.need_register) {
                    showMessage('login', 'Telefone não encontrado. Faça seu cadastro!', 'error');
                    // Muda para a aba de cadastro
                    document.getElementById('cadastro-telefone').value = formData.get('telefone');
                    switchTab(tabCadastro);
                } else {
                    showMessage('login', data.message || 'Erro ao fazer login. Tente novamente.', 'error');
                }
            })
            .catch(error => {
                setLoading('login', false);
                console.error('Erro:', error);
                showMessage('login', 'Erro de conexão. Tente novamente.', 'error');
            });
        });

        // Cadastro
        document.getElementById('cadastroForm').addEventListener('submit', function(e) {
            e.preventDefault();
            clearErrors('cadastro');
            
            const formData = new FormData(this);
            const telefone = formData.get('telefone').replace(/\D/g, '');

            if (!telefone) {
                showMessage('cadastro', 'Por favor, informe seu telefone.', 'error');
                return;
            }

            setLoading('cadastro', true);

            fetch('verificar_cliente.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                setLoading('cadastro', false);
                
                if (data.success) {
                    showMessage('cadastro', 'Cadastro realizado com sucesso! Redirecionando...', 'success');
                    setTimeout(() => {
                        // Verificar se há uma ação específica para redirecionar
                        const urlParams = new URLSearchParams(window.location.search);
                        const acao = urlParams.get('acao');
                        
                        // Verifica se é um cliente ou usuário do sistema
                        if (data.isCliente) {
                            // É um cliente
                            if (acao === 'comprar') {
                                window.location.href = 'campanha.php?id=' + (urlParams.get('campanha_id') || '');
                            } else if (acao === 'titulos') {
                                window.location.href = 'meus_titulos.php?cliente_id=' + data.cliente_id;
                            } else {
                                window.location.href = 'index.php';
                            }
                        } else {
                            // É um usuário do sistema (admin/afiliado)
                            if (acao === 'comprar' || acao === 'titulos') {
                                // Usuários do sistema não podem comprar nem ver títulos
                                alert('Usuários do sistema não podem comprar cotas ou visualizar títulos.');
                                window.location.href = 'index.php';
                            } else {
                                window.location.href = 'index.php';
                            }
                        }
                    }, 1500);
                } else {
                    showMessage('cadastro', data.message || 'Erro ao fazer cadastro. Tente novamente.', 'error');
                }
            })
            .catch(error => {
                setLoading('cadastro', false);
                console.error('Erro:', error);
                showMessage('cadastro', 'Erro de conexão. Tente novamente.', 'error');
            });
        });

        // Inicializa a primeira tab
        switchTab(tabLogin);
    </script>
</body>
</html>
