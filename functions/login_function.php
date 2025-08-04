<?php
require_once('functions_usuarios.php');
require_once('../conexao.php');
if (session_status() == PHP_SESSION_NONE)
    session_start();


// ---Login--------------
if (isset($_POST["login_senha"]) && isset($_POST["login_email"])) {
    $email = $_POST["login_email"];
    $login_senha = $_POST["login_senha"];
    $login = loginUsuario($conn, $email, $login_senha);

    if ($login === true) {
        header("Location: ../home.php");
    } else {
        header("Location:../index.php?login_sucesso=false");
    }
}
//---cadastro--------------
if (isset($_POST["cadastro_senha"]) && isset($_POST["cadastro_email"]) && isset($_POST["cadastro_numero"])) {
    $cadastro_nome = $_POST["cadastro_nome"];
    $cadastro_senha = $_POST["cadastro_senha"];
    $cadastro_email = $_POST["cadastro_email"];
    $cadastro_numero = $_POST["cadastro_numero"];
    $cadastro = criarUsuario($conn, $cadastro_nome, $cadastro_email, $cadastro_senha, $cadastro_numero);
    if ($cadastro !== false) {
        header("Location:../index.php?cadastro_sucesso=true");

    } else {
        header("Location:../index.php?cadastro_sucesso=false");

    }

}
?>