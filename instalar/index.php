<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação do Sistema de Rifas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(45deg, #0f172a, #1e293b);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 600;
        }

        .section-title {
            color: #fff;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-col {
            flex: 1;
        }

        label {
            display: block;
            color: #fff;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.2);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #0ea5e9;
        }

        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
        }

        button {
            background: #0ea5e9;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s ease;
        }

        button:hover {
            background: #0284c7;
            transform: translateY(-1px);
        }

        .error {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Instalação do Sistema de Rifas</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $host = $_POST['host'];
            $usuario = $_POST['usuario'];
            $senha = $_POST['senha'];
            $banco = $_POST['banco'];
            $porta = $_POST['porta'] ?? '3306';
            $limpar_banco = isset($_POST['limpar_banco']) ? true : false;

            try {
                // Conectar ao banco de dados
                $conn = new mysqli($host, $usuario, $senha);
                
                if ($conn->connect_error) {
                    throw new Exception("Erro na conexão: " . $conn->connect_error);
                }

                // Criar o banco de dados se não existir
                $sql = "CREATE DATABASE IF NOT EXISTS `$banco`";
                if (!$conn->query($sql)) {
                    throw new Exception("Erro ao criar banco de dados: " . $conn->error);
                }

                // Selecionar o banco de dados
                $conn->select_db($banco);

                // Limpar o banco se solicitado
                if ($limpar_banco) {
                    // Desabilitar verificação de chave estrangeira
                    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                    
                    // Pegar todas as tabelas
                    $tables = $conn->query("SHOW TABLES");
                    while ($table = $tables->fetch_array()) {
                        $table_name = $table[0];
                        // Dropar a tabela
                        $conn->query("DROP TABLE IF EXISTS `$table_name`");
                    }
                    
                    // Reabilitar verificação de chave estrangeira
                    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                }

                // Configurar o banco de dados para UTF8
                $conn->query("SET NAMES utf8");
                $conn->query("SET character_set_client = utf8");
                $conn->query("SET character_set_connection = utf8");

                // Ler o arquivo SQL
                $sql_file = file_get_contents('../banco.sql');

                // Executar o script SQL completo
                if ($conn->multi_query($sql_file)) {
                    do {
                        // Armazenar o primeiro resultado
                        if ($result = $conn->store_result()) {
                            $result->free();
                        }
                    } while ($conn->next_result()); // Mover para o próximo resultado
                } else {
                    throw new Exception("Erro ao executar script SQL: " . $conn->error);
                }

                // Atualizar o arquivo conexao.php
                $conexao_content = file_get_contents('../conexao.php');
                
                // Substituir as variáveis de conexão
                $conexao_content = preg_replace(
                    array(
                        '/\$localhost\s*=\s*"[^"]*"/',
                        '/\$usuario\s*=\s*"[^"]*"/',
                        '/\$senha_db\s*=\s*"[^"]*"/',
                        '/\$nome_db\s*=\s*"[^"]*"/',
                        '/\$porta_db\s*=\s*"[^"]*"/'
                    ),
                    array(
                        '$localhost = "' . $host . '"',
                        '$usuario = "' . $usuario . '"',
                        '$senha_db = "' . $senha . '"',
                        '$nome_db = "' . $banco . '"',
                        '$porta_db = "' . $porta . '"'
                    ),
                    $conexao_content
                );

                // Salvar as alterações no arquivo conexao.php
                if (file_put_contents('../conexao.php', $conexao_content) === false) {
                    throw new Exception("Erro ao atualizar o arquivo de conexão");
                }

                echo '<div class="success">Instalação concluída com sucesso!</div>';
                
            } catch (Exception $e) {
                echo '<div class="error">Erro: ' . $e->getMessage() . '</div>';
            }
        }
        ?>

        <form method="POST" action="">
            <h2 class="section-title">URL do site</h2>
            <div class="form-group">
                <input type="text" id="url" name="url" placeholder="https://seusite.com" value="<?php echo isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : ''; ?>" required>
            </div>

            <h2 class="section-title">Detalhes do banco</h2>
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="host">Servidor</label>
                        <input type="text" id="host" name="host" value="127.0.0.1" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="porta">Porta</label>
                        <input type="text" id="porta" name="porta" value="3306" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="banco">Nome do banco</label>
                        <input type="text" id="banco" name="banco" value="novosistemarifa" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="usuario">Usuário do banco</label>
                        <input type="text" id="usuario" name="usuario" value="root" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="senha">Senha do banco</label>
                        <input type="password" id="senha" name="senha">
                    </div>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="limpar_banco" name="limpar_banco" value="1">
                <label for="limpar_banco">Limpar banco de dados existente</label>
            </div>

            <div class="form-group" style="margin-top: 30px;">
                <button type="submit">FINALIZAR</button>
            </div>
        </form>
    </div>
</body>
</html> 