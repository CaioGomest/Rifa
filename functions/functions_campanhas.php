<?php

function listaCampanhas($conn, $id = NULL, $autor_id = NULL, $status = NULL, $titulo = NULL, $order_by = NULL, $limite = NULL, $data_inicio = NULL, $data_fim = NULL, $flag_exclusao = 0, $campanha_privada = NULL) {
    // Início da query
   
    $query = "SELECT * FROM campanhas WHERE 1=1";
    
    // Condições adicionais
    if (is_numeric($id))
        $query .= " AND id = " . mysqli_real_escape_string($conn, $id);

    if ($autor_id !== NULL)
        $query .= " AND autor_id = " . mysqli_real_escape_string($conn, $autor_id);

    if (isset($status) && $status >= 0)
        $query .= " AND status = '" . mysqli_real_escape_string($conn, $status) . "'";

    if ($titulo !== NULL)
        $query .= " AND titulo LIKE '%" . mysqli_real_escape_string($conn, $titulo) . "%'";

    if ($data_inicio !== NULL)
        $query .= " AND data_criacao >= '" . mysqli_real_escape_string($conn, $data_inicio) . "'";

    if ($data_fim !== NULL)
        $query .= " AND data_criacao <= '" . mysqli_real_escape_string($conn, $data_fim) . "'";

    if ($campanha_privada !== NULL)
        $query .= " AND campanha_privada = " . mysqli_real_escape_string($conn, $campanha_privada);

    if ($flag_exclusao !== NULL)
        $query .= " AND flag_exclusao = " . mysqli_real_escape_string($conn, $flag_exclusao);

    if ($order_by !== NULL)
        $query .= " ORDER BY " . mysqli_real_escape_string($conn, $order_by);

    if ($limite !== NULL)
        $query .= " LIMIT " . mysqli_real_escape_string($conn, $limite);

    // echo $query;
    // Executa a query
    $result = mysqli_query($conn, $query);

    if ($result) {
        $campanhas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $campanhas[] = $row;
        }
        return $campanhas; // Retorna o array de campanhas
    } else {
        return "ERRO: " . mysqli_error($conn); // Retorna o erro da query
    }
}


function criaCampanha(
    $conn,
    $autor_id,
    
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
    $premio_cotas_premiadas,
    $descricao_cotas_premiadas,
    
    $selecionar_top_ganhadores,
    $filtro_periodo_top_ganhadores,
    
    $mostrar_cotas_premiadas,
    $status_cotas_premiadas,
   
    $titulo_cotas_dobro,
    $subtitulo_cotas_dobro,
    $layout,
    
    // Novos parâmetros para Roletas e Raspadinhas
    $habilitar_roleta,
    $titulo_roleta,
    $descricao_roleta,
    $itens_roleta,
    $habilitar_raspadinha,
    $titulo_raspadinha,
    $descricao_raspadinha,
    $itens_raspadinha,
    
    ) {

    // Trata arrays convertendo para JSON string
    if (is_array($pacote_promocional))
        $pacote_promocional = json_encode($pacote_promocional);
    
    if (is_array($pacotes_exclusivos))
        $pacotes_exclusivos = json_encode($pacotes_exclusivos);
    
    if (is_array($galeria_imagens))
        $galeria_imagens = implode(',', $galeria_imagens);
    
    // Trata arrays/JSON para Roletas e Raspadinhas garantindo JSON válido
    $jsonNormalize = function($value) {
        if ($value === null) {
            return '[]';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (!is_string($value)) {
            return '[]';
        }
        $trimmed = trim($value);
        if ($trimmed === '' || strtolower($trimmed) === 'null') {
            return '[]';
        }
        $decoded = json_decode($trimmed, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '[]';
        }
        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    };

    if (is_array($itens_roleta))
        $itens_roleta = json_encode($itens_roleta, JSON_UNESCAPED_UNICODE);
    $itens_roleta = $jsonNormalize($itens_roleta);

    if (is_array($itens_raspadinha))
        $itens_raspadinha = json_encode($itens_raspadinha, JSON_UNESCAPED_UNICODE);
    $itens_raspadinha = $jsonNormalize($itens_raspadinha);

    // Também normaliza pacotes já existentes que são JSON
    if (is_array($pacote_promocional))
        $pacote_promocional = json_encode($pacote_promocional, JSON_UNESCAPED_UNICODE);
    $pacote_promocional = $jsonNormalize($pacote_promocional);

    if (is_array($pacotes_exclusivos))
        $pacotes_exclusivos = json_encode($pacotes_exclusivos, JSON_UNESCAPED_UNICODE);
    $pacotes_exclusivos = $jsonNormalize($pacotes_exclusivos);

    // Garante que flags não sejam arrays (pode ocorrer se o mesmo name vier duplicado no form)
    $habilitar_roleta = is_array($habilitar_roleta) ? (string)reset($habilitar_roleta) : (string)($habilitar_roleta ?? '0');
    $habilitar_raspadinha = is_array($habilitar_raspadinha) ? (string)reset($habilitar_raspadinha) : (string)($habilitar_raspadinha ?? '0');

    $query = "INSERT INTO campanhas (
        autor_id,

        nome,
        subtitulo,
        descricao,
        caminho_imagem,
        galeria_imagens,
        status,

        data_criacao,
        data_atualizacao,
        data_sorteio,

        preco,

        quantidade_numeros,
        compra_minima,
        compra_maxima,
        numeros_pendentes,
        numeros_pagos,

        habilitar_pacote_padrao,
        pacote_padrao,

        habilitar_adicao_rapida,
        adicao_rapida,

        habilitar_pacote_promocional,
        pacote_promocional,

        habilitar_desconto_acumulativo,

        habilita_pacote_promocional_exclusivo,
        pacotes_exclusivos,

        habilitar_ranking,
        mensagem_ranking,
        exibir_ranking,
        quantidade_ranking,

        habilitar_barra_progresso,
		ativar_progresso_manual,
        porcentagem_barra_progresso,

        numero_sorteio,
        tipo_sorteio,
        campanha_destaque,
        campanha_privada,
        vencedor_sorteio,
		habilitar_cotas_em_dobro,
        cotas_premiadas,
        premio_cotas_premiadas,
        descricao_cotas_premiadas,
        titulo_cotas_dobro,
        subtitulo_cotas_dobro,

        selecionar_top_ganhadores,
        filtro_periodo_top_ganhadores,

        mostrar_cotas_premiadas,
        status_cotas_premiadas,

        layout,
        
        habilitar_roleta,
        titulo_roleta,
        descricao_roleta,
        itens_roleta,
        habilitar_raspadinha,
        titulo_raspadinha,
        descricao_raspadinha,
        itens_raspadinha
    ) VALUES (
        $autor_id,

        '" . mysqli_real_escape_string($conn, $nome) . "',
        '" . mysqli_real_escape_string($conn, $subtitulo) . "',
        '" . mysqli_real_escape_string($conn, $descricao) . "',
        '" . mysqli_real_escape_string($conn, $caminho_imagem) . "',
        '" . mysqli_real_escape_string($conn, $galeria_imagens) . "',
        '" . mysqli_real_escape_string($conn, $status) . "',

        now(),
        now(),

        '" . mysqli_real_escape_string($conn, $data_sorteio) . "',
        '" . mysqli_real_escape_string($conn, $preco) . "',
        '" . mysqli_real_escape_string($conn, $quantidade_numeros) . "',
        '" . mysqli_real_escape_string($conn, $compra_minima) . "',
        '" . mysqli_real_escape_string($conn, $compra_maxima) . "',
        '" . mysqli_real_escape_string($conn, $numeros_pendentes) . "',
        '" . mysqli_real_escape_string($conn, $numeros_pagos) . "',
        '" . mysqli_real_escape_string($conn, $habilitar_pacote_padrao) . "',
        '" . mysqli_real_escape_string($conn, $pacote_padrao) . "',
        '" . mysqli_real_escape_string($conn, $habilitar_adicao_rapida) . "',
        '" . mysqli_real_escape_string($conn, $adicao_rapida) . "',
        '" . mysqli_real_escape_string($conn, $habilitar_pacote_promocional) . "',
        '" . mysqli_real_escape_string($conn, $pacote_promocional) . "',
        '" . mysqli_real_escape_string($conn, $habilitar_desconto_acumulativo) . "',
        '" . mysqli_real_escape_string($conn, $habilita_pacote_promocional_exclusivo) . "',
        '" . mysqli_real_escape_string($conn, $pacotes_exclusivos) . "',
        '" . mysqli_real_escape_string($conn, $habilitar_ranking) . "',
        '" . mysqli_real_escape_string($conn, $mensagem_ranking) . "',
        '" . mysqli_real_escape_string($conn, $exibir_ranking) . "',
        '" . mysqli_real_escape_string($conn, $quantidade_ranking) . "',

        '" . mysqli_real_escape_string($conn, $habilitar_barra_progresso) . "',
        '" . mysqli_real_escape_string($conn, $ativar_progresso_manual) . "',
        '" . mysqli_real_escape_string($conn, $porcentagem_barra_progresso) . "',

        '" . mysqli_real_escape_string($conn, $numero_sorteio) . "',
        '" . mysqli_real_escape_string($conn, $tipo_sorteio) . "',
        '" . mysqli_real_escape_string($conn, $campanha_destaque) . "',
        '" . mysqli_real_escape_string($conn, $campanha_privada) . "',
        '" . mysqli_real_escape_string($conn, $vencedor_sorteio) . "',
        '" . mysqli_real_escape_string($conn, $habilitar_cotas_em_dobro) . "',
        '" . mysqli_real_escape_string($conn, $cotas_premiadas) . "',
        '" . mysqli_real_escape_string($conn, $premio_cotas_premiadas) . "',
        '" . mysqli_real_escape_string($conn, $descricao_cotas_premiadas) . "',
        '" . mysqli_real_escape_string($conn, $titulo_cotas_dobro) . "',
        '" . mysqli_real_escape_string($conn, $subtitulo_cotas_dobro) . "',

        '" . mysqli_real_escape_string($conn, $selecionar_top_ganhadores) . "',
        '" . mysqli_real_escape_string($conn, $filtro_periodo_top_ganhadores) . "',

        '" . mysqli_real_escape_string($conn, $mostrar_cotas_premiadas) . "',
        '" . mysqli_real_escape_string($conn, $status_cotas_premiadas) . "',
        '" . mysqli_real_escape_string($conn, $layout) . "',

        '" . mysqli_real_escape_string($conn, $habilitar_roleta) . "',
        '" . mysqli_real_escape_string($conn, $titulo_roleta) . "',
        '" . mysqli_real_escape_string($conn, $descricao_roleta) . "',
        '" . mysqli_real_escape_string($conn, $itens_roleta) . "',
        '" . mysqli_real_escape_string($conn, $habilitar_raspadinha) . "',
        '" . mysqli_real_escape_string($conn, $titulo_raspadinha) . "',
        '" . mysqli_real_escape_string($conn, $descricao_raspadinha) . "',
        '" . mysqli_real_escape_string($conn, $itens_raspadinha) . "'
    )";
    echo "<br>";
    echo $query;
    echo "<br>";
    // die();
    $result = mysqli_query($conn, $query);
    return $result ? mysqli_insert_id($conn) : "ERRO: " . mysqli_error($conn);
}


function editaCampanha(
    $conn,
    $id,
   
    $nome = null,
    $subtitulo = null,
    $descricao = null,
    $caminho_imagem = null,
    $galeria_imagens = null,
    $status = null,
    
    
    $data_sorteio = null,
    
   
    $preco = null,
    
    
    $quantidade_numeros = null,
    $compra_minima = null,
    $compra_maxima = null,
    $numeros_pendentes = null,
    $numeros_pagos = null,
    
    
    
        $habilitar_pacote_padrao = null,
    $pacote_padrao = null,
    
    $habilitar_adicao_rapida = null,
    $adicao_rapida = null,
    
    $habilitar_pacote_promocional = null,
    $pacote_promocional = null,
    
    $habilitar_desconto_acumulativo = null,
    
    $habilita_pacote_promocional_exclusivo = null,
    $pacotes_exclusivos = null,
    
   
    $habilitar_ranking = null,
    $mensagem_ranking = null,
    $exibir_ranking = null,
    $quantidade_ranking = null,

    $habilitar_barra_progresso = null,
	$ativar_progresso_manual = null,
    $porcentagem_barra_progresso = null,
    
    
    $numero_sorteio = null,
    $tipo_sorteio = null,
    $campanha_destaque = null,
    $campanha_privada = null,
    $vencedor_sorteio = null,

	$habilitar_cotas_em_dobro = null,
    $cotas_premiadas = null,
        
        $premio_cotas_premiadas = null,
    $descricao_cotas_premiadas = null,
  
    $selecionar_top_ganhadores = null,
    $filtro_periodo_top_ganhadores = null,

    $mostrar_cotas_premiadas = null,
    $status_cotas_premiadas = null,

    $titulo_cotas_dobro = null,
    $subtitulo_cotas_dobro = null,
    
    $imagem_capa = null,
    $layout = null,
    
    // Novos parâmetros para Roletas e Raspadinhas
    $habilitar_roleta = null,
    $titulo_roleta = null,
    $descricao_roleta = null,
    $itens_roleta = null,
    $habilitar_raspadinha = null,
    $titulo_raspadinha = null,
    $descricao_raspadinha = null,
    $itens_raspadinha = null
    
) {
    if (!is_numeric($id))
        return "ERRO: ID inválido.";

    // Inicializa o array de campos
    $campos = array();

    // Normaliza campos JSON para sempre gravar JSON válido
    // Evita violar constraints do banco (JSON_VALID, NOT NULL etc.)
    $jsonNormalize = function($value) {
        if ($value === null) {
            return null; // não atualiza o campo quando null
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (!is_string($value)) {
            return '[]';
        }
        $trimmed = trim($value);
        if ($trimmed === '' || strtolower($trimmed) === 'null') {
            return '[]';
        }
        $decoded = json_decode($trimmed, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '[]';
        }
        return json_encode($decoded, JSON_UNESCAPED_UNICODE);
    };

    if($nome != null)
        $campos[] = "nome = '" . mysqli_real_escape_string($conn, $nome) . "'";
    if($subtitulo != null)
        $campos[] = "subtitulo = '" . mysqli_real_escape_string($conn, $subtitulo) . "'";
    if($descricao != null)
        $campos[] = "descricao = '" . mysqli_real_escape_string($conn, $descricao) . "'";
    
    if($caminho_imagem !== NULL)
        $campos[] = "caminho_imagem = '" . mysqli_real_escape_string($conn, $caminho_imagem) . "'";

    if($imagem_capa!== NULL)
        $campos[] = "imagem_capa = '" . mysqli_real_escape_string($conn, $imagem_capa) . "'";

    if($galeria_imagens !== NULL) {
        if(is_array($galeria_imagens)) {
            $galeria_imagens = implode(',', $galeria_imagens);
        }
        $campos[] = "galeria_imagens = '" . mysqli_real_escape_string($conn, $galeria_imagens) . "'";
    }
    if($status != null)
        $campos[] = "status = '" . mysqli_real_escape_string($conn, $status) . "'";
    
    
    if($data_sorteio != null)
        $campos[] = "data_sorteio = '" . mysqli_real_escape_string($conn, $data_sorteio) . "'";
    
    if($preco != null)
        $campos[] = "preco = '" . mysqli_real_escape_string($conn, $preco) . "'";
    
    
    if($quantidade_numeros != null)
        $campos[] = "quantidade_numeros = '" . mysqli_real_escape_string($conn, $quantidade_numeros) . "'";
    if($compra_minima != null)
        $campos[] = "compra_minima = '" . mysqli_real_escape_string($conn, $compra_minima) . "'";
    if($compra_maxima != null)
        $campos[] = "compra_maxima = '" . mysqli_real_escape_string($conn, $compra_maxima) . "'";
    if($numeros_pendentes != null)
        $campos[] = "numeros_pendentes = '" . mysqli_real_escape_string($conn, $numeros_pendentes) . "'";
    if($numeros_pagos != null)
        $campos[] = "numeros_pagos = '" . mysqli_real_escape_string($conn, $numeros_pagos) . "'";
    
    
    
    if($habilitar_pacote_padrao != null)
        $campos[] = "habilitar_pacote_padrao = '" . mysqli_real_escape_string($conn, $habilitar_pacote_padrao) . "'";
    if($pacote_padrao != null)
        $campos[] = "pacote_padrao = '" . mysqli_real_escape_string($conn, $pacote_padrao) . "'";
    
    if($habilitar_adicao_rapida != null)
        $campos[] = "habilitar_adicao_rapida = '" . mysqli_real_escape_string($conn, $habilitar_adicao_rapida) . "'";
    if($adicao_rapida != null)
        $campos[] = "adicao_rapida = '" . mysqli_real_escape_string($conn, $adicao_rapida) . "'";
    
    if($habilitar_pacote_promocional != null)
        $campos[] = "habilitar_pacote_promocional = '" . mysqli_real_escape_string($conn, $habilitar_pacote_promocional) . "'";
    if($pacote_promocional != null)
        $campos[] = "pacote_promocional = '" . mysqli_real_escape_string($conn, $pacote_promocional) . "'";
    
    if($habilitar_desconto_acumulativo != null)
        $campos[] = "habilitar_desconto_acumulativo = '" . mysqli_real_escape_string($conn, $habilitar_desconto_acumulativo) . "'";
    
    if($habilita_pacote_promocional_exclusivo != null)
        $campos[] = "habilita_pacote_promocional_exclusivo = '" . mysqli_real_escape_string($conn, $habilita_pacote_promocional_exclusivo) . "'";
    
    if($pacotes_exclusivos != null)
        $campos[] = "pacotes_exclusivos = '" . mysqli_real_escape_string($conn, $pacotes_exclusivos) . "'";
    
    // Trata arrays para Roletas e Raspadinhas
    if($itens_roleta !== null) {
        $itens_roleta = $jsonNormalize($itens_roleta);
        $campos[] = "itens_roleta = '" . mysqli_real_escape_string($conn, $itens_roleta) . "'";
    }
    
    
    if($itens_raspadinha !== null) {
        $itens_raspadinha = $jsonNormalize($itens_raspadinha);
        $campos[] = "itens_raspadinha = '" . mysqli_real_escape_string($conn, $itens_raspadinha) . "'";
    }
    
    
    
    
    if($habilitar_ranking != null)
        $campos[] = "habilitar_ranking = '" . mysqli_real_escape_string($conn, $habilitar_ranking) . "'";
    if($mensagem_ranking != null)
        $campos[] = "mensagem_ranking = '" . mysqli_real_escape_string($conn, $mensagem_ranking) . "'";
    if($exibir_ranking != null)
        $campos[] = "exibir_ranking = '" . mysqli_real_escape_string($conn, $exibir_ranking) . "'";
    if($quantidade_ranking != null)
        $campos[] = "quantidade_ranking = '" . mysqli_real_escape_string($conn, $quantidade_ranking) . "'";

    if($habilitar_barra_progresso != null)
        $campos[] = "habilitar_barra_progresso = '" . mysqli_real_escape_string($conn, $habilitar_barra_progresso) . "'";
    if($ativar_progresso_manual != null)
        $campos[] = "ativar_progresso_manual = '" . mysqli_real_escape_string($conn, $ativar_progresso_manual) . "'";
    if($porcentagem_barra_progresso != null)
        $campos[] = "porcentagem_barra_progresso = '" . mysqli_real_escape_string($conn, $porcentagem_barra_progresso) . "'";
    
    
    if($numero_sorteio != null)
        $campos[] = "numero_sorteio = '" . mysqli_real_escape_string($conn, $numero_sorteio) . "'";
    if($tipo_sorteio != null)
        $campos[] = "tipo_sorteio = '" . mysqli_real_escape_string($conn, $tipo_sorteio) . "'";
    if($campanha_destaque != null)
        $campos[] = "campanha_destaque = '" . mysqli_real_escape_string($conn, $campanha_destaque) . "'";
    if($campanha_privada != null)
        $campos[] = "campanha_privada = '" . mysqli_real_escape_string($conn, $campanha_privada) . "'";
    if($vencedor_sorteio != null)
        $campos[] = "vencedor_sorteio = '" . mysqli_real_escape_string($conn, $vencedor_sorteio) . "'";
		
    if($habilitar_cotas_em_dobro != null)
        $campos[] = "habilitar_cotas_em_dobro = '" . mysqli_real_escape_string($conn, $habilitar_cotas_em_dobro) . "'";
    if($titulo_cotas_dobro != null)
        $campos[] = "titulo_cotas_dobro = '" . mysqli_real_escape_string($conn, $titulo_cotas_dobro) . "'";
    if($subtitulo_cotas_dobro != null)
        $campos[] = "subtitulo_cotas_dobro = '" . mysqli_real_escape_string($conn, $subtitulo_cotas_dobro) . "'";
    
    if($cotas_premiadas !== null)
        $campos[] = "cotas_premiadas = '" . mysqli_real_escape_string($conn, $cotas_premiadas) . "'";

    if($premio_cotas_premiadas !== null)
        $campos[] = "premio_cotas_premiadas = '" . mysqli_real_escape_string($conn, $premio_cotas_premiadas) . "'";
    if($descricao_cotas_premiadas != null)
        $campos[] = "descricao_cotas_premiadas = '" . mysqli_real_escape_string($conn, $descricao_cotas_premiadas) . "'";

    if($selecionar_top_ganhadores != null)
        $campos[] = "selecionar_top_ganhadores = '" . mysqli_real_escape_string($conn, $selecionar_top_ganhadores) . "'";
    if($filtro_periodo_top_ganhadores != null)
        $campos[] = "filtro_periodo_top_ganhadores = '" . mysqli_real_escape_string($conn, $filtro_periodo_top_ganhadores) . "'";

    if($mostrar_cotas_premiadas != null)
        $campos[] = "mostrar_cotas_premiadas = '" . mysqli_real_escape_string($conn, $mostrar_cotas_premiadas) . "'";
    if($status_cotas_premiadas != null)
        $campos[] = "status_cotas_premiadas = '" . mysqli_real_escape_string($conn, $status_cotas_premiadas) . "'";

    if($layout != null)
        $campos[] = "layout = '" . mysqli_real_escape_string($conn, $layout) . "'";

    // Campos para Roletas e Raspadinhas
    if($habilitar_roleta != null)
        $campos[] = "habilitar_roleta = '" . mysqli_real_escape_string($conn, $habilitar_roleta) . "'";
    if($titulo_roleta != null)
        $campos[] = "titulo_roleta = '" . mysqli_real_escape_string($conn, $titulo_roleta) . "'";
    if($descricao_roleta != null)
        $campos[] = "descricao_roleta = '" . mysqli_real_escape_string($conn, $descricao_roleta) . "'";
    if($habilitar_raspadinha != null)
        $campos[] = "habilitar_raspadinha = '" . mysqli_real_escape_string($conn, $habilitar_raspadinha) . "'";
    if($titulo_raspadinha != null)
        $campos[] = "titulo_raspadinha = '" . mysqli_real_escape_string($conn, $titulo_raspadinha) . "'";
    if($descricao_raspadinha != null)
        $campos[] = "descricao_raspadinha = '" . mysqli_real_escape_string($conn, $descricao_raspadinha) . "'";


    // Trata arrays para Itens da Roleta e Raspadinha
    if($itens_roleta != null) {
        if(is_array($itens_roleta)) {
            $itens_roleta = json_encode($itens_roleta);
        }
        $campos[] = "itens_roleta = '" . mysqli_real_escape_string($conn, $itens_roleta) . "'";
    }
    
    if($itens_raspadinha != null) {
        if(is_array($itens_raspadinha)) {
            $itens_raspadinha = json_encode($itens_raspadinha);
        }
        $campos[] = "itens_raspadinha = '" . mysqli_real_escape_string($conn, $itens_raspadinha) . "'";
    }

    $query = "UPDATE campanhas SET " . implode(", ", $campos) . " WHERE id = " . (int)$id;
    
    // echo $query;
    // die();

    $result = mysqli_query($conn, $query);

    if ($result)
        return true;
    else
        return "ERRO: " . mysqli_error($conn);
    
}

function deletaCampanha($conn, $id) {
    if (!is_numeric($id)) {
        return "ERRO: O ID fornecido não é válido.";
    }

    $query = "UPDATE campanhas SET flag_exclusao = 1 WHERE id = " . mysqli_real_escape_string($conn, $id);

    $result = mysqli_query($conn, $query);
    if ($result !== FALSE) {
        return true;
    } else {
        return "ERRO: " . mysqli_error($conn);
    }
}

function getTotalNumerosVendidos($conn, $campanha_id) {
    $sql = "SELECT SUM(CAST(quantidade as UNSIGNED)) as total 
            FROM lista_pedidos 
            WHERE campanha_id = ? AND status = 2";
            
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Erro na preparação da query: " . $conn->error);
            return 0;
        }

        $stmt->bind_param("i", $campanha_id);

        if (!$stmt->execute()) {
            error_log("Erro ao executar query: " . $stmt->error);
            return 0;
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return intval($row['total']) ?? 0;
    } catch (Exception $e) {
        error_log("Erro ao buscar total de números vendidos: " . $e->getMessage());
        return 0;
    }
}

function obterNumerosDisponiveis($conn, $campanha_id) {
    $sql = "SELECT numeros_pedido FROM lista_pedidos 
            WHERE campanha_id = ? AND (status = 0 OR status = 1)";
            
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Erro na preparação da query: " . $conn->error);
            return [];
        }

        $stmt->bind_param("i", $campanha_id);
        
        if (!$stmt->execute()) {
            error_log("Erro ao executar query: " . $stmt->error);
            return [];
        }

        $result = $stmt->get_result();
        $numeros_comprados = [];
        
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['numeros_pedido'])) {
                $numeros = explode(',', $row['numeros_pedido']);
                $numeros_comprados = array_merge($numeros_comprados, $numeros);
            }
        }

        $sql_campanha = "SELECT quantidade_numeros FROM campanhas WHERE id = ?";
        $stmt_campanha = $conn->prepare($sql_campanha);
        $stmt_campanha->bind_param("i", $campanha_id);
        $stmt_campanha->execute();
        $result_campanha = $stmt_campanha->get_result();
        $campanha = $result_campanha->fetch_assoc();
        
        $quantidade_total = intval($campanha['quantidade_numeros']);
        
        $todos_numeros = range(1, $quantidade_total);
        
        $numeros_disponiveis = array_diff($todos_numeros, $numeros_comprados);
        
        return array_values($numeros_disponiveis);
        
    } catch (Exception $e) {
        error_log("Erro ao buscar números disponíveis: " . $e->getMessage());
        return [];
    }
}


function count_obterNumerosDisponiveis($conn, $campanha_id) {
    try {
        // Obtem a string concatenada com todos os numeros_pedido já ocupados
        $sql = "SELECT GROUP_CONCAT(numeros_pedido SEPARATOR ',') AS todos_numeros 
                FROM lista_pedidos 
                WHERE campanha_id = ? AND (status = 0 OR status = 1)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Erro na preparação da query: " . $conn->error);
            return [];
        }

        $stmt->bind_param("i", $campanha_id);
        if (!$stmt->execute()) {
            error_log("Erro ao executar query: " . $stmt->error);
            return [];
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $todos_numeros_str = $row['todos_numeros'] ?? '';
        
        // Converte para array, remove espaços e valores vazios
        $numeros_comprados = array_filter(array_map('trim', explode(',', $todos_numeros_str)));

        // Busca o total de números da campanha
        $sql_campanha = "SELECT quantidade_numeros FROM campanhas WHERE id = ?";
        $stmt_campanha = $conn->prepare($sql_campanha);
        $stmt_campanha->bind_param("i", $campanha_id);
        $stmt_campanha->execute();
        $result_campanha = $stmt_campanha->get_result();
        $campanha = $result_campanha->fetch_assoc();
        
        $quantidade_total = intval($campanha['quantidade_numeros']);
        $todos_numeros = range(1, $quantidade_total);

        // Subtrai os já comprados
        $numeros_disponiveis = array_diff($todos_numeros, $numeros_comprados);

        return array_values($numeros_disponiveis);

    } catch (Exception $e) {
        error_log("Erro ao buscar números disponíveis: " . $e->getMessage());
        return [];
    }
}


function validarNumerosSolicitados($conn, $campanha_id, $numeros_solicitados) {
    // Obtém os números disponíveis
    $numeros_disponiveis = obterNumerosDisponiveis($conn, $campanha_id);
    
    // Verifica se todos os números solicitados estão disponíveis
    foreach ($numeros_solicitados as $numero) {
        if (!in_array($numero, $numeros_disponiveis)) {
            return [
                'valido' => false,
                'mensagem' => "O bilhete $numero não está disponível para compra."
            ];
        }
    }
    
    return [
        'valido' => true,
        'mensagem' => "Todos os bilhetes estão disponíveis para compra."
    ];
}

function processarCompraNumeros($conn, $campanha_id, $numeros_solicitados, $cliente_id) {
    // Valida os números solicitados
    $validacao = validarNumerosSolicitados($conn, $campanha_id, $numeros_solicitados);
    if (!$validacao['valido']) {
        return $validacao;
    }

    try {
        // Inicia a transação
        $conn->beginTransaction();

        // Insere os números na tabela lista_pedidos
        $sql = "INSERT INTO lista_pedidos (campanha_id, numero, cliente_id, status) VALUES (?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);

        foreach ($numeros_solicitados as $numero) {
            $stmt->execute([$campanha_id, $numero, $cliente_id]);
        }

        // Confirma a transação
        $conn->commit();

        return [
            'sucesso' => true,
            'mensagem' => "Compra realizada com sucesso!"
        ];

    } catch (Exception $e) {
        // Desfaz a transação em caso de erro
        $conn->rollBack();
        
        return [
            'sucesso' => false,
            'mensagem' => "Erro ao processar a compra: " . $e->getMessage()
        ];
    }
} 

function editaVencedorCampanha($conn, $id_pedido, $cotas_premiadas)
{
       

    $sql = "UPDATE lista_pedidos SET cota_premiada = '{$cotas_premiadas}' WHERE id = '{$id_pedido}'";
    $result = $conn->query($sql);



    if ($result)
        return true;
    else
        return "ERRO: " . mysqli_error($conn);
}
