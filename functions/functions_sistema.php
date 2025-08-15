<?php

function listaInformacoes($conn) {
    $sql = "SELECT * FROM configuracoes";
    $result = mysqli_query($conn, $sql);
   
    // Debug para verificar erros
    if (mysqli_error($conn)) {
        error_log("Erro MySQL em listaInformacoes: " . mysqli_error($conn));
        return [];
    }

    // Se não houver resultados, retorna array vazio
    if (!$result || mysqli_num_rows($result) == 0) {
        return [];
    }

    // Retorna a primeira linha como array associativo
    return mysqli_fetch_assoc($result);
}

function atualizaConfiguracoes($conn, $titulo = NULL, $email = NULL, $telefone = NULL, $tema = NULL,
$logo = NULL, $habilitar_compartilhamento = NULL, $habilitar_grupos = NULL, $campos_obrigatorios = NULL,
$termos_uso = NULL, $politica_privacidade = NULL, $texto_rodape = NULL, $copyright = NULL,
$cotas_ocultas = NULL, $mensagem_cota_oculta = NULL, $perguntas_frequentes = NULL, $termos_condicoes = NULL,

$habilitar_mercadopago = NULL, $mercadopago_token_acesso = NULL,
$habilitar_pay2m = NULL, $pay2m_client_key = NULL, $pay2m_client_secret = NULL,
$habilitar_paggue = NULL, $paggue_client_key = NULL, $paggue_client_secret = NULL,


$pixel_facebook = NULL, $pixel_google = NULL, $outros_pixels = NULL,
$habilitar_api_tiktok = NULL, $token_tiktok = NULL, $habilitar_api_kawai = NULL, $token_kawai = NULL,

$habilitar_api_facebook = NULL, $habilitar_google_analytics = NULL, $token_google_analytics = NULL,
$habilitar_utmify = NULL, $link_youtube = NULL, $link_facebook = NULL, $link_instagram = NULL,
$login_bg_color = NULL, $login_image = NULL, $token_utmify = NULL, $link_grupo = NULL, $habilitar_fale_conosco = NULL, $link_fale_conosco = NULL) 
{
    // Array para armazenar os campos a serem atualizados
    $campos = [];

    // Adiciona os campos ao array apenas se não forem NULL
    if($titulo !== NULL) $campos[] = "titulo = '" . mysqli_real_escape_string($conn, $titulo) . "'";
    if($email !== NULL) $campos[] = "email = '" . mysqli_real_escape_string($conn, $email) . "'";
    if($telefone !== NULL) $campos[] = "telefone = '" . mysqli_real_escape_string($conn, $telefone) . "'";
    if($tema !== NULL) $campos[] = "tema = '" . mysqli_real_escape_string($conn, $tema) . "'";
    if($logo !== NULL) $campos[] = "logo = '" . mysqli_real_escape_string($conn, $logo) . "'";
    if($habilitar_compartilhamento !== NULL) $campos[] = "habilitar_compartilhamento = " . ($habilitar_compartilhamento ? '1' : '0');

    if($habilitar_fale_conosco !== NULL) $campos[] = "habilitar_fale_conosco = " . ($habilitar_fale_conosco ? '1' : '0');

    if($link_fale_conosco !== NULL) $campos[] = "link_fale_conosco = '" . mysqli_real_escape_string($conn, $link_fale_conosco) . "'";

    
    if($habilitar_grupos !== NULL) $campos[] = "habilitar_grupos = " . ($habilitar_grupos ? '1' : '0');
    if($link_grupo !== NULL) $campos[] = "link_grupo = '" . mysqli_real_escape_string($conn, $link_grupo) . "'";
    if($campos_obrigatorios !== NULL) $campos[] = "campos_obrigatorios = '" . mysqli_real_escape_string($conn, $campos_obrigatorios) . "'";
    if($termos_uso !== NULL) $campos[] = "termos_uso = '" . mysqli_real_escape_string($conn, $termos_uso) . "'";
    if($politica_privacidade !== NULL) $campos[] = "politica_privacidade = '" . mysqli_real_escape_string($conn, $politica_privacidade) . "'";
    if($texto_rodape !== NULL) $campos[] = "texto_rodape = '" . mysqli_real_escape_string($conn, $texto_rodape) . "'";
    if($copyright !== NULL) $campos[] = "copyright = '" . mysqli_real_escape_string($conn, $copyright) . "'";
    if($cotas_ocultas !== NULL) $campos[] = "cotas_ocultas = '" . mysqli_real_escape_string($conn, $cotas_ocultas) . "'";
    if($mensagem_cota_oculta !== NULL) $campos[] = "mensagem_cota_oculta = '" . mysqli_real_escape_string($conn, $mensagem_cota_oculta) . "'";
    if($perguntas_frequentes !== NULL) $campos[] = "perguntas_frequentes = '" . mysqli_real_escape_string($conn, $perguntas_frequentes) . "'";
    if($termos_condicoes !== NULL) $campos[] = "termos_condicoes = '" . mysqli_real_escape_string($conn, $termos_condicoes) . "'";
    
    if($habilitar_mercadopago !== NULL) $campos[] = "habilitar_mercadopago = " . ($habilitar_mercadopago ? '1' : '0');      
    if($mercadopago_token_acesso !== NULL) $campos[] = "mercadopago_token_acesso = '" . mysqli_real_escape_string($conn, $mercadopago_token_acesso) . "'";
    
    if($habilitar_pay2m !== NULL) $campos[] = "habilitar_pay2m = " . ($habilitar_pay2m ? '1' : '0');
    if($pay2m_client_key !== NULL) $campos[] = "pay2m_client_key = '" . mysqli_real_escape_string($conn, $pay2m_client_key) . "'";
    if($pay2m_client_secret !== NULL) $campos[] = "pay2m_client_secret = '" . mysqli_real_escape_string($conn, $pay2m_client_secret) . "'";
    
    if($habilitar_paggue !== NULL) $campos[] = "habilitar_paggue = " . ($habilitar_paggue ? '1' : '0');
    if($paggue_client_key !== NULL) $campos[] = "paggue_client_key = '" . mysqli_real_escape_string($conn, $paggue_client_key) . "'";
    if($paggue_client_secret !== NULL) $campos[] = "paggue_client_secret = '" . mysqli_real_escape_string($conn, $paggue_client_secret) . "'";
    
    if($pixel_facebook !== NULL) $campos[] = "pixel_facebook = '" . mysqli_real_escape_string($conn, $pixel_facebook) . "'";
    if($pixel_google !== NULL) $campos[] = "pixel_google = '" . mysqli_real_escape_string($conn, $pixel_google) . "'";
    if($outros_pixels !== NULL) $campos[] = "outros_pixels = '" . mysqli_real_escape_string($conn, $outros_pixels) . "'";
    if($habilitar_api_tiktok !== NULL) $campos[] = "habilitar_api_tiktok = " . ($habilitar_api_tiktok ? '1' : '0');
    if($token_tiktok !== NULL) $campos[] = "token_tiktok = '" . mysqli_real_escape_string($conn, $token_tiktok) . "'";
    if($habilitar_api_kawai !== NULL) $campos[] = "habilitar_api_kawai = " . ($habilitar_api_kawai ? '1' : '0');
    if($token_kawai !== NULL) $campos[] = "token_kawai = '" . mysqli_real_escape_string($conn, $token_kawai) . "'";
    if($habilitar_api_facebook !== NULL) $campos[] = "habilitar_api_facebook = " . ($habilitar_api_facebook ? '1' : '0');
    if($habilitar_google_analytics !== NULL) $campos[] = "habilitar_google_analytics = " . ($habilitar_google_analytics ? '1' : '0');
    if($token_google_analytics !== NULL) $campos[] = "token_google_analytics = '" . mysqli_real_escape_string($conn, $token_google_analytics) . "'";
    if($habilitar_utmify !== NULL) $campos[] = "habilitar_utmify = " . ($habilitar_utmify ? '1' : '0');
    if($token_utmify !== NULL) $campos[] = "token_utmify = '" . mysqli_real_escape_string($conn, $token_utmify) . "'";
    
    if($link_youtube !== NULL) $campos[] = "link_youtube = '" . mysqli_real_escape_string($conn, $link_youtube) . "'";
    if($link_facebook !== NULL) $campos[] = "link_facebook = '" . mysqli_real_escape_string($conn, $link_facebook) . "'";
    if($link_instagram !== NULL) $campos[] = "link_instagram = '" . mysqli_real_escape_string($conn, $link_instagram) . "'";
    if($login_bg_color !== NULL) $campos[] = "login_bg_color = '" . mysqli_real_escape_string($conn, $login_bg_color) . "'";
    if($login_image !== NULL) $campos[] = "login_image = '" . mysqli_real_escape_string($conn, $login_image) . "'";

    // Verifica se há campos a serem atualizados
    if (!empty($campos)) {
        // Concatena os campos com vírgula e monta a query final
        $query = "UPDATE configuracoes SET " . implode(', ', $campos);

        // Se não houver registros, insere um novo
        $check = mysqli_query($conn, "SELECT id FROM configuracoes LIMIT 1");
        if (mysqli_num_rows($check) == 0) {
            $query = "INSERT INTO configuracoes SET " . implode(', ', $campos);
        }

        // Executa a consulta e retorna o resultado
        if (mysqli_query($conn, $query)) {
            return true;
        } else {
            return "ERRO: " . mysqli_error($conn);
        }
    } else {
        return "Nenhum campo foi atualizado.";
    }
}

function inserirHistoricoSorteio($conn, $dados)
{
    $sql = "INSERT INTO historico_sorteios (data_sorteio, tipo_sorteio, qtd_sorteada, filtro_data, filtro_cotas, campanha_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssissi', 
        $dados['data_sorteio'],
        $dados['tipo_sorteio'],
        $dados['qtd_sorteada'],
        $dados['filtro_data'],
        $dados['filtro_cotas'],
        $dados['campanha_id']
    );
    
    return mysqli_stmt_execute($stmt);
}

// Utilidades de formatação de cotas (exibição)
// Calcula a largura (nº de dígitos) a partir da quantidade total de números da campanha
function calcularLarguraCotaPorQuantidade($quantidade_total)
{
    $quantidade_total = intval($quantidade_total);
    if ($quantidade_total < 1) {
        $quantidade_total = 1;
    }
    return strlen((string) $quantidade_total);
}

// Obtém a largura (nº de dígitos) para uma campanha específica
function obterLarguraCotaPorCampanha($conn, $campanha_id)
{
    $campanha_id = intval($campanha_id);
    if ($campanha_id <= 0) {
        return 1;
    }

    $sql = "SELECT quantidade_numeros FROM campanhas WHERE id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return 1;
    }
    mysqli_stmt_bind_param($stmt, 'i', $campanha_id);
    if (!mysqli_stmt_execute($stmt)) {
        return 1;
    }
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    $quantidade_total = $row && isset($row['quantidade_numeros']) ? intval($row['quantidade_numeros']) : 0;
    return calcularLarguraCotaPorQuantidade($quantidade_total);
}

// Formata um número de cota com zeros à esquerda respeitando a largura
function formatarCotaComLargura($numero, $largura)
{
    $largura = max(1, intval($largura));
    $numero_limpo = trim((string) $numero);
    if ($numero_limpo === '') {
        return '';
    }
    $numero_int = intval($numero_limpo);
    return str_pad((string) $numero_int, $largura, '0', STR_PAD_LEFT);
}

// Formata um array de cotas com a mesma largura
function formatarArrayCotasComLargura($cotas, $largura)
{
    if (!is_array($cotas)) {
        return [];
    }
    $resultado = [];
    foreach ($cotas as $cota) {
        $resultado[] = formatarCotaComLargura($cota, $largura);
    }
    return $resultado;
}

// Formata uma string CSV de cotas com a mesma largura
function formatarCsvCotasComLargura($csv, $largura)
{
    $itens = array_filter(array_map('trim', explode(',', (string) $csv)), function ($v) {
        return $v !== '';
    });
    $itens_formatados = [];
    foreach ($itens as $cota) {
        $itens_formatados[] = formatarCotaComLargura($cota, $largura);
    }
    return implode(',', $itens_formatados);
}
