<?php
 ini_set('display_errors',1);
 ini_set('display_startup_erros',1);
 error_reporting(E_ALL);
// Verifica se a extensão mysqli está instalada
if (!extension_loaded('mysqli')) {
    die("A extensão mysqli não está instalada. Por favor, verifique sua instalação do PHP.");
}

$localhost = "127.0.0.1";
$usuario = "root";
$senha_db = '';
$nome_db = "rifa";
$porta_db = "3306";

// $localhost = "127.0.0.1";
// $usuario = "u214219698_rifas";
// $senha_db = 'lAlOIzOK1+b';
// $nome_db = "u214219698_rifas";
// $porta_db = "3306";

try {
    $conn = new mysqli($localhost, $usuario, $senha_db, $nome_db);

    // Verifique se a conexão foi bem-sucedida
    if ($conn->connect_error) {
        throw new Exception("Conexão falhou: " . $conn->connect_error);
    }
	
	$conn->set_charset("utf8");
} catch (Exception $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
