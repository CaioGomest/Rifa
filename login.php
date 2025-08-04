<?php
require 'conexao.php';
require 'functions/functions_sistema.php';
require 'functions/functions_usuarios.php';
$config = listaInformacoes($conn);

$mensagemErro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    if (loginUsuario($conn, $email, $senha))
    {
        echo '<script>window.location.href = "admin/index.php";</script>';
        exit;
    } else
        $mensagemErro = "Email ou senha incorretos.";
    
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo  $config['logo']; ?>">
    <title><?php echo $config['titulo']; ?> - Login</title> 
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Lado esquerdo - Imagem (visível apenas em desktop) -->
        <div class="hidden md:flex w-full md:w-1/2 min-h-screen justify-center items-center" 
             style="background-color: <?php echo isset($config['login_bg_color']) ? $config['login_bg_color'] : '#1F2937'; ?>">
            <img src="<?php echo isset($config['login_image']) && !empty($config['login_image']) ? $config['login_image'] : $config['logo']; ?>" 
                 alt="Logo" 
                 class="w-2/4 object-contain">
        </div>

        <!-- Lado direito - Formulário -->
        <div class="w-full md:w-1/2 min-h-screen flex flex-col justify-center items-center p-8 md:px-8"
             style="background-color: white">
            <div class="w-full max-w-md bg-white p-8 rounded-lg ">
                <h1 class="text-3xl md:text-4xl font-bold mb-6 text-gray-800 text-center">Login</h1>
                
                <?php if ($mensagemErro): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $mensagemErro; ?></span>
                    </div>
                <?php endif; ?>

                <form class="space-y-4" method="POST">
                    <div>
                        <label for="email">Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Endereço de Email" 
                            class="shadow-lg w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all text-gray-800"
                            required
                        >
                    </div>
                    <div>
                    <label for="email">Senha</label>
                        <input 
                            type="password" 
                            name="senha" 
                            placeholder="Senha" 
                            class="shadow-lg w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all text-gray-800"
                            required
                        >
                    </div>
                    <div>
                        <button 
                            type="submit" 
                            class="shadow-lg w-full bg-green-500 text-white py-3 rounded-lg text-lg hover:bg-green-600 transition-colors focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Acessar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
