<?php
require_once('../../conexao.php');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Processa o upload da logo se houver
$logo = isset($_FILES['logo']) ? $_FILES['logo'] : null;
$logo_path = isset($_POST['logo_atual']) ? $_POST['logo_atual'] : '';

if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../../assets/imgs/';
    
    // Cria o diretório se não existir
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Gera um nome único para o arquivo
    $ext = strtolower(pathinfo($logo['name'], PATHINFO_EXTENSION));
    $novo_nome = uniqid('logo_') . '.' . $ext;
    $caminho_completo = $upload_dir . $novo_nome;

    // Move o arquivo para o diretório de uploads
    if (move_uploaded_file($logo['tmp_name'], $caminho_completo)) {
        // Remove a logo antiga se existir
        if (!empty($logo_path) && file_exists('../../' . $logo_path)) {
            unlink('../../' . $logo_path);
        }
        $logo_path = 'assets/imgs/' . $novo_nome;
    }
}

// Processa os campos obrigatórios do cadastro
$campos_obrigatorios = isset($_POST['campos_obrigatorios']) ? implode(',', $_POST['campos_obrigatorios']) : '';

// Coleta os dados do formulário
$dados = [
    // Configurações Gerais
    'titulo' => $_POST['titulo'] ?? '',
    'email' => $_POST['email'] ?? '',
    'telefone' => $_POST['telefone'] ?? '',
    'tema' => $_POST['tema'] ?? 'padrao',
    'logo' => $logo_path,
    
    // Social
    'habilitar_compartilhamento' => isset($_POST['habilitar_compartilhamento']) ? 1 : 0,
    'habilitar_grupos' => isset($_POST['habilitar_grupos']) ? 1 : 0,
    
    // Cadastro
    'campos_obrigatorios' => $campos_obrigatorios,
    'termos_uso' => $_POST['termos_uso'] ?? '',
    'politica_privacidade' => $_POST['politica_privacidade'] ?? '',
    
    // Rodapé
    'texto_rodape' => $_POST['texto_rodape'] ?? '',
    'copyright' => $_POST['copyright'] ?? '',
    'links_rodape' => $_POST['links_rodape'] ?? '',
    
    // Cotas
    'cotas_ocultas' => $_POST['cotas_ocultas'] ?? '',
    'mensagem_cota_oculta' => $_POST['mensagem_cota_oculta'] ?? '',
    
    // FAQ
    'perguntas_frequentes' => $_POST['perguntas_frequentes'] ?? '',
    
    // Termos
    'termos_condicoes' => $_POST['termos_condicoes'] ?? '',
    
    // Pixels
    'pixel_facebook' => $_POST['pixel_facebook'] ?? '',
    'pixel_google' => $_POST['pixel_google'] ?? '',
    'outros_pixels' => $_POST['outros_pixels'] ?? ''
];

// Monta a query de atualização
$campos = [];
foreach ($dados as $campo => $valor) {
    if (is_null($valor)) {
        $campos[] = "$campo = NULL";
    } else {
        $campos[] = "$campo = '" . mysqli_real_escape_string($conn, $valor) . "'";
    }
}

$query = "UPDATE configuracoes SET " . implode(", ", $campos) . ", data_atualizacao = NOW() WHERE id = 1";

$resultado = mysqli_query($conn, $query);

if ($resultado) {
    echo json_encode(['sucesso' => true, 'mensagem' => 'Configurações atualizadas com sucesso']);
} else {
    echo json_encode(['erro' => 'Erro ao atualizar configurações: ' . mysqli_error($conn)]);
} 