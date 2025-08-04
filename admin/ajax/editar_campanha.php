<?php
require_once("../../functions/functions_campanhas.php");
require_once("../../functions/functions_uploads.php");
require_once("../../conexao.php");

header('Content-Type: application/json');

$upload_dir = '../../uploads/campanhas/';

if (isset($_REQUEST['caminho_imagem']))
    $caminho_atual = isset($_POST['caminho_imagem']) ? $_POST['caminho_imagem'] : null;
else
    $caminho_atual = null;

if (isset($_REQUEST['caminho_imagem_atual']))
    $caminho_atual = isset($_POST['caminho_imagem_atual']) ? $_POST['caminho_imagem_atual'] : null;
else
    $caminho_atual = null;

if (isset($_REQUEST['imagem_capa']))
    $caminho_atual_capa = isset($_POST['imagem_capa']) ? $_POST['imagem_capa'] : null;
else
    $caminho_atual_capa = null;

if ($caminho_atual !== null) {
    $caminho_escapado = mysqli_real_escape_string($conn, $caminho_atual);
    $campos[] = "caminho_imagem = '$caminho_escapado'";
}


$caminho_imagem = editarImagemPrincipal('imagem_principal', $caminho_atual, $upload_dir);
$imagem_capa = editarImagemPrincipal('imagem_capa', $caminho_atual_capa, $upload_dir);


if (isset($_REQUEST['galeria_imagens_atual']))
    $galeria_imagens = isset($_POST['galeria_imagens_atual']) ? explode(',', $_POST['galeria_imagens_atual']) : [];
else
    $galeria_imagens = null;

$upload_dir = __DIR__ . '/../../uploads/campanhas/';
$galeria_imagens = editarGaleriaImagens('galeria', $galeria_imagens, $upload_dir);

$id = isset($_POST['id']) ? $_POST['id'] : null;

$nome = isset($_POST['nome']) ? $_POST['nome'] : null;
$subtitulo = isset($_POST['subtitulo']) ? $_POST['subtitulo'] : null;
$descricao = isset($_POST['descricao']) ? $_POST['descricao'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;

$data_sorteio = isset($_POST['data_sorteio']) ? $_POST['data_sorteio'] : null;

$preco = isset($_POST['preco']) ? $_POST['preco'] : Null;


$quantidade_numeros = isset($_POST['quantidade_numeros']) ? $_POST['quantidade_numeros'] : null;
$compra_minima = isset($_POST['compra_minima']) ? $_POST['compra_minima'] : null;
$compra_maxima = isset($_POST['compra_maxima']) ? $_POST['compra_maxima'] : null;
$numeros_pendentes = isset($_POST['numeros_pendentes']) ? $_POST['numeros_pendentes'] : null;
$numeros_pagos = isset($_POST['numeros_pagos']) ? $_POST['numeros_pagos'] : null;

$habilitar_pacote_padrao = isset($_POST['habilitar_pacote_padrao']) ? $_POST['habilitar_pacote_padrao'] : null;
$pacote_padrao = isset($_POST['pacote_padrao']) ? $_POST['pacote_padrao'] : null;

$habilitar_adicao_rapida = isset($_POST['habilitar_adicao_rapida']) ? $_POST['habilitar_adicao_rapida'] : null;
$adicao_rapida = isset($_POST['adicao_rapida']) ? $_POST['adicao_rapida'] : null;

$habilitar_pacote_promocional = isset($_POST['habilitar_pacote_promocional']) ? $_POST['habilitar_pacote_promocional'] : null;
$pacote_promocional = isset($_POST['pacote_promocional']) ? $_POST['pacote_promocional'] : null;

$habilitar_desconto_acumulativo = isset($_POST['habilitar_desconto_acumulativo']) ? $_POST['habilitar_desconto_acumulativo'] : null;

$habilita_pacote_promocional_exclusivo = isset($_POST['habilita_pacote_promocional_exclusivo']) ? $_POST['habilita_pacote_promocional_exclusivo'] : null;
$pacotes_exclusivos = isset($_POST['pacotes_exclusivos']) ? $_POST['pacotes_exclusivos'] : null;

$habilitar_ranking = isset($_POST['habilitar_ranking']) ? $_POST['habilitar_ranking'] : null;
$mensagem_ranking = isset($_POST['mensagem_ranking']) ? $_POST['mensagem_ranking'] : null;
$exibir_ranking = isset($_POST['exibir_ranking']) ? $_POST['exibir_ranking'] : null;
$quantidade_ranking = isset($_POST['quantidade_ranking']) ? $_POST['quantidade_ranking'] : null;

$selecionar_top_ganhadores = isset($_POST['selecionar_top_ganhadores']) ? $_POST['selecionar_top_ganhadores'] : null;
$filtro_periodo_top_ganhadores = isset($_POST['filtro_periodo_top_ganhadores']) ? $_POST['filtro_periodo_top_ganhadores'] : null;


$habilitar_barra_progresso 		= isset($_POST['habilitar_barra_progresso']) 	? $_POST['habilitar_barra_progresso'] : null;
$ativar_progresso_manual 		= isset($_POST['ativar_progresso_manual']) 		? $_POST['ativar_progresso_manual'] : null;
$porcentagem_barra_progresso 	= isset($_POST['porcentagem_barra_progresso']) 	? $_POST['porcentagem_barra_progresso'] : null;

$numero_sorteio = isset($_POST['numero_sorteio']) ? $_POST['numero_sorteio'] : null;
$tipo_sorteio = isset($_POST['tipo_sorteio']) ? $_POST['tipo_sorteio'] : null;
$layout = isset($_POST['layout']) ? $_POST['layout'] : null;
$campanha_destaque = isset($_POST['campanha_destaque']) ? $_POST['campanha_destaque'] : null;
$campanha_privada = isset($_POST['campanha_privada']) ? $_POST['campanha_privada'] : null;
$vencedor_sorteio = isset($_POST['vencedor_sorteio']) ? $_POST['vencedor_sorteio'] : null;

$cotas_premiadas 			= isset($_POST['cotas_premiadas']) 				? $_POST['cotas_premiadas'] 			: null;
$habilitar_cotas_em_dobro 	= isset($_POST['habilitar_cotas_em_dobro']) 	? $_POST['habilitar_cotas_em_dobro'] 	: null;
$descricao_cotas_premiadas 	= isset($_POST['descricao_cotas_premiadas']) 	? $_POST['descricao_cotas_premiadas'] 	: null;
$titulo_cotas_dobro         = isset($_POST['titulo_cotas_dobro'])          ? $_POST['titulo_cotas_dobro']          : null;
$subtitulo_cotas_dobro      = isset($_POST['subtitulo_cotas_dobro'])       ? $_POST['subtitulo_cotas_dobro']       : null;

$mostrar_cotas_premiadas = isset($_POST['mostrar_cotas_premiadas']) ? $_POST['mostrar_cotas_premiadas'] : null;
$status_cotas_premiadas = isset($_POST['status_cotas_premiadas']) ? $_POST['status_cotas_premiadas'] : null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    die(json_encode(['success' => false, 'message' => 'Método não permitido']));

if (!$id)
    die(json_encode(['success' => false, 'message' => 'ID da campanha não fornecido']));




// var_dump($preco);
$editar = editaCampanha(
    $conn,
    $id,

    $nome,
    $subtitulo,
    $descricao,
    $caminho_imagem,
    $galeria_imagens,
    $status,


    $data_sorteio,


    $preco,


    $quantidade_numeros,
    $compra_minima,
    $compra_maxima,
    $numeros_pendentes,
    $numeros_pagos,



    $habilitar_pacote_padrao,
    $pacote_padrao,

    $habilitar_adicao_rapida,
    $adicao_rapida,

    $habilitar_pacote_promocional,
    $pacote_promocional,

    $habilitar_desconto_acumulativo,

    $habilita_pacote_promocional_exclusivo,
    $pacotes_exclusivos,


    $habilitar_ranking,
    $mensagem_ranking,
    $exibir_ranking,
    $quantidade_ranking,

    $habilitar_barra_progresso,
	$ativar_progresso_manual,
    $porcentagem_barra_progresso,


    $numero_sorteio,
    $tipo_sorteio,
    $campanha_destaque,
    $campanha_privada,
    $vencedor_sorteio,

	$habilitar_cotas_em_dobro,
    $cotas_premiadas,
    $descricao_cotas_premiadas,
    
    $selecionar_top_ganhadores,
    $filtro_periodo_top_ganhadores,
    
    $mostrar_cotas_premiadas,
    $status_cotas_premiadas,
    
    $titulo_cotas_dobro,
    $subtitulo_cotas_dobro,
    
    $imagem_capa,
    $layout
);
if ($editar)
    echo json_encode([
        'success' => true,
        'message' => 'Campanha editada com sucesso',
    ]);
else
    echo json_encode(['success' => false, 'message' => 'Erro ao editar campanha']);
die();