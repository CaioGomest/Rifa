    <!DOCTYPE html>
    <html lang="pt-BR">
    <?php
    require("header.php");

    $action 			= isset($_REQUEST['action']) 				? $_REQUEST['action'] : null;
    $id 				= isset($_REQUEST['id']) 					? $_REQUEST['id'] : 0;

    $nome = isset($_REQUEST['nome']) ? $_REQUEST['nome'] : '';
    $slug = '0'; // Sempre envia 0 para o slug
    $descricao = isset($_REQUEST['descricao']) ? $_REQUEST['descricao'] : '';
    $preco = isset($_REQUEST['preco']) ? $_REQUEST['preco'] : '';
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '0';
    $data_criacao = isset($_REQUEST['data_criacao']) ? $_REQUEST['data_criacao'] : '';
    $data_atualizacao = isset($_REQUEST['data_atualizacao']) ? $_REQUEST['data_atualizacao'] : '';
    $tipo_sorteio = isset($_REQUEST['tipo_sorteio']) ? $_REQUEST['tipo_sorteio'] : '1';
    $layout = isset($_REQUEST['layout']) ? $_REQUEST['layout'] : '';
    $quantidade_numeros = isset($_REQUEST['quantidade_numeros']) ? $_REQUEST['quantidade_numeros'] : '';
    $compra_minima = isset($_REQUEST['compra_minima']) ? $_REQUEST['compra_minima'] : '';
    $compra_maxima = isset($_REQUEST['compra_maxima']) ? $_REQUEST['compra_maxima'] : '';
    $numeros_pendentes = isset($_REQUEST['numeros_pendentes']) ? $_REQUEST['numeros_pendentes'] : '0';
    $numeros_pagos = isset($_REQUEST['numeros_pagos']) ? $_REQUEST['numeros_pagos'] : '0';
    $quantidade_ranking = isset($_REQUEST['quantidade_ranking']) ? $_REQUEST['quantidade_ranking'] : '';
    $habilitar_ranking = isset($_REQUEST['habilitar_ranking']) ? $_REQUEST['habilitar_ranking'] : '0';
    $habilitar_barra_progresso = isset($_REQUEST['habilitar_barra_progresso']) ? $_REQUEST['habilitar_barra_progresso'] : '0';
    $ativar_progresso_manual = isset($_REQUEST['ativar_progresso_manual']) ? $_REQUEST['ativar_progresso_manual'] : '0';
    $porcentagem_barra_progresso = isset($_REQUEST['porcentagem_barra_progresso']) ? $_REQUEST['porcentagem_barra_progresso'] : '0';
    $numero_sorteio = isset($_REQUEST['numero_sorteio']) ? $_REQUEST['numero_sorteio'] : '';
    $subtitulo = isset($_REQUEST['subtitulo']) ? $_REQUEST['subtitulo'] : '';
    $data_sorteio = isset($_REQUEST['data_sorteio']) ? $_REQUEST['data_sorteio'] : '';

    $quantidade_desconto = isset($_REQUEST['quantidade_desconto']) ? $_REQUEST['quantidade_desconto'] : '';
    if (is_string($quantidade_desconto)) {
        $quantidade_desconto = json_decode($quantidade_desconto, true) ?? '';
    }


    $habilitar_pacote_padrao = isset($_REQUEST['habilitar_pacote_padrao']) ? $_REQUEST['habilitar_pacote_padrao'] : '0';
    $pacote_padrao = isset($_REQUEST['pacote_padrao']) ? $_REQUEST['pacote_padrao'] : '';
    $habilitar_adicao_rapida = isset($_REQUEST['habilitar_adicao_rapida']) ? $_REQUEST['habilitar_adicao_rapida'] : '0';
    $adicao_rapida = isset($_REQUEST['adicao_rapida']) ? $_REQUEST['adicao_rapida'] : '';
    $habilitar_pacote_promocional = isset($_REQUEST['habilitar_pacote_promocional']) ? $_REQUEST['habilitar_pacote_promocional'] : '0';
    $pacote_promocional = isset($_REQUEST['pacote_promocional']) ? $_REQUEST['pacote_promocional'] : '';
    if (is_string($pacote_promocional)) {
        $pacote_promocional = json_decode($pacote_promocional, true) ?? '';
    }

    $habilitar_desconto_acumulativo = isset($_REQUEST['habilitar_desconto_acumulativo']) ? $_REQUEST['habilitar_desconto_acumulativo'] : '0';
    $habilita_pacote_promocional_exclusivo = isset($_REQUEST['habilita_pacote_promocional_exclusivo']) ? $_REQUEST['habilita_pacote_promocional_exclusivo'] : '0';
    $pacotes_exclusivos = isset($_REQUEST['pacotes_exclusivos']) ? $_REQUEST['pacotes_exclusivos'] : '';
    if (is_string($pacotes_exclusivos)) {
        $pacotes_exclusivos = json_decode($pacotes_exclusivos, true) ?? '';
    }

    $mensagem_ranking = isset($_REQUEST['mensagem_ranking']) ? $_REQUEST['mensagem_ranking'] : '';
    $exibir_ranking = isset($_REQUEST['exibir_ranking']) ? $_REQUEST['exibir_ranking'] : '0';
    $vencedor_sorteio = isset($_REQUEST['vencedor_sorteio']) ? $_REQUEST['vencedor_sorteio'] : '';
    $campanha_privada = isset($_REQUEST['campanha_privada']) ? $_REQUEST['campanha_privada'] : '0';
    $campanha_destaque 			= isset($_REQUEST['campanha_destaque']) 		? $_REQUEST['campanha_destaque'] : '0';
    $habilitar_cotas_em_dobro 	= isset($_REQUEST['habilitar_cotas_em_dobro']) 	? $_REQUEST['habilitar_cotas_em_dobro'] : '0';
    $cotas_premiadas 			= isset($_REQUEST['cotas_premiadas']) 			? $_REQUEST['cotas_premiadas'] : '';
    $quantidade_cotas_premiadas = isset($_REQUEST['quantidade_cotas_premiadas']) ? $_REQUEST['quantidade_cotas_premiadas'] : '';
    $premio_cotas_premiadas 	= isset($_REQUEST['premio_cotas_premiadas']) 	? $_REQUEST['premio_cotas_premiadas'] : '';
    $descricao_cotas_premiadas 	= isset($_REQUEST['descricao_cotas_premiadas']) ? $_REQUEST['descricao_cotas_premiadas'] : '';
    $titulo_cotas_dobro         = isset($_REQUEST['titulo_cotas_dobro'])          ? $_REQUEST['titulo_cotas_dobro']          : '';
    $subtitulo_cotas_dobro      = isset($_REQUEST['subtitulo_cotas_dobro'])       ? $_REQUEST['subtitulo_cotas_dobro']       : '';
    $limite_pedidos 			= isset($_REQUEST['limite_pedidos']) 			? $_REQUEST['limite_pedidos'] : '';

    $mostrar_cotas_premiadas = isset($_REQUEST['mostrar_cotas_premiadas']) ? $_REQUEST['mostrar_cotas_premiadas'] : '0';
    $status_cotas_premiadas = isset($_REQUEST['status_cotas_premiadas']) ? $_REQUEST['status_cotas_premiadas'] : '0';

    $selecionar_top_ganhadores = isset($_REQUEST['selecionar_top_ganhadores']) ? $_REQUEST['selecionar_top_ganhadores'] : '0';
    $filtro_periodo_top_ganhadores = isset($_REQUEST['filtro_periodo_top_ganhadores']) ? $_REQUEST['filtro_periodo_top_ganhadores'] : '{"filtro":"hoje","valor":""}';

    // Variáveis para Roletas e Raspadinhas
    $habilitar_roleta = isset($_REQUEST['habilitar_roleta']) ? $_REQUEST['habilitar_roleta'] : '0';
    $titulo_roleta = isset($_REQUEST['titulo_roleta']) ? $_REQUEST['titulo_roleta'] : '🎰 Roleta da Sorte';
    $descricao_roleta = isset($_REQUEST['descricao_roleta']) ? $_REQUEST['descricao_roleta'] : '';
    $itens_roleta = isset($_REQUEST['nome_item_roleta']) ? array_map(function($nome, $status) {
    return ['nome' => $nome, 'status' => $status];
}, $_REQUEST['nome_item_roleta'], $_REQUEST['status_item_roleta']) : [];
    
    // Removidos: pacotes de roleta agora são tratados pelos pacotes da campanha
    
    $habilitar_raspadinha = isset($_REQUEST['habilitar_raspadinha']) ? $_REQUEST['habilitar_raspadinha'] : '0';
    $titulo_raspadinha = isset($_REQUEST['titulo_raspadinha']) ? $_REQUEST['titulo_raspadinha'] : '🎫 Raspadinha da Sorte';
    $descricao_raspadinha = isset($_REQUEST['descricao_raspadinha']) ? $_REQUEST['descricao_raspadinha'] : '';
    $itens_raspadinha = isset($_REQUEST['nome_item_raspadinha']) ? array_map(function($nome, $status) {
    return ['nome' => $nome, 'status' => $status];
}, $_REQUEST['nome_item_raspadinha'], $_REQUEST['status_item_raspadinha']) : [];

    if ($id > 0) 
    {
        $action = 'editar';
        $campanhas = listaCampanhas($conn, $id);
        $data_sorteio = date("d/m/Y H:i:s", strtotime($campanhas[0]["data_sorteio"]));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $upload_dir = '../uploads/campanhas/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $caminho_imagem = isset($_POST['caminho_imagem_atual']) ? $_POST['caminho_imagem_atual'] : '';
        $caminho_imagem = salvarImagemPrincipal('imagem_principal', $caminho_imagem, $upload_dir);


        $galeria_imagens = isset($_POST['galeria_imagens_atual']) ? explode(',', $_POST['galeria_imagens_atual']) : [];
        $galeria_imagens = salvarGaleriaImagens('galeria', $galeria_imagens, $upload_dir);


        switch ($action) {
            case 'criar':
                // Validação dos campos obrigatórios
                $campos_obrigatorios = ['nome', 'descricao', 'preco', 'tipo_sorteio', 'quantidade_numeros', 'compra_minima', 'compra_maxima'];
                $campos_vazios = [];
                foreach ($campos_obrigatorios as $campo) {
                    if (empty($$campo)) {
                        $campos_vazios[] = $campo;
                    }
                }
                if (!empty($campos_vazios)) {
                    // echo "<script>alert('ERRO: Campo obrigatório " . implode(", ", $campos_vazios) . " não preenchido.');</script>";
                }
            
                $criar = criaCampanha(
                    $conn,
                    $_SESSION['usuario']['usuario_id'],
                    
                
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
                );
                
                if (is_numeric($criar) && $criar > 0)
                {
                    $mensagem = ['tipo' => 'sucesso', 'texto' => 'Campanha criada com sucesso!'];
                    // Usa o ID retornado pela função
                    $id = $criar;
                    $action = 'editar';
                    $campanhas = listaCampanhas($conn, $id);
                    echo "<script>window.location.href = 'campanhas.php?mensagem=Campanha criada com sucesso!';</script>";
                    exit;
                } 
                else
                {
                    $mensagem = ['tipo' => 'erro', 'texto' => 'Erro ao criar campanha: ' . $criar];
                }
                break;

            case 'editar':
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
                    $premio_cotas_premiadas,
                    $descricao_cotas_premiadas,
                    $titulo_cotas_dobro,
                    $subtitulo_cotas_dobro,
                    
                    $selecionar_top_ganhadores,
                    $filtro_periodo_top_ganhadores,

                    $mostrar_cotas_premiadas,
                    $status_cotas_premiadas,
                    
                    // Novos parâmetros para Roletas e Raspadinhas
                    $habilitar_roleta,
                    $titulo_roleta,
                    $descricao_roleta,
                    $itens_roleta,
                    $habilitar_raspadinha,
                    $titulo_raspadinha,
                    $descricao_raspadinha,
                    $itens_raspadinha,
                );

                if ($editar === true) 
                {
                    $mensagem = ['tipo' => 'sucesso', 'texto' => 'Campanha atualizada com sucesso!'];
                    // Recarrega os dados da campanha
                    $campanhas = listaCampanhas($conn, $id);
                    $data_sorteio = date("d/m/Y H:i:s", strtotime($campanhas[0]["data_sorteio"]));
                } 
                else
                {
                    $mensagem = ['tipo' => 'erro', 'texto' => 'Erro ao atualizar campanha: ' . $editar];
                }
                break;
        }
    }

	if (!$action && !$id) 
        $action = 'criar';

    if (empty($slug) && !empty($nome))
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nome)));

    function getUploadErrorMessage($error)
    {
        switch ($error) 
        {
            case UPLOAD_ERR_INI_SIZE:
                return 'O arquivo excede o tamanho permitido pelo php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'O arquivo excede o tamanho permitido pelo formulário HTML';
            case UPLOAD_ERR_PARTIAL:
                return 'O arquivo foi enviado parcialmente';
            case UPLOAD_ERR_NO_FILE:
                return 'Nenhum arquivo foi enviado';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Diretório temporário ausente';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Falha ao escrever o arquivo no disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Uma extensão PHP interrompeu o upload do arquivo';
            default:
                return 'Erro desconhecido';
        }
    }
    ?>

    <body class="bg-gray-100 text-gray-800 dark:bg-[#18181B] dark:text-white">
        <div class="flex h-screen">
            <?php
            require("sidebar.php");
            ?>
            <!-- Main Content -->
            <main class="flex-1 p-4 lg:p-8 overflow-auto">
                <section>

                    <div class="bg-white dark:bg-[#27272A] p-6 rounded-md shadow">
                        <header class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-bold">
                                <?php echo $action == "criar" || !isset($action) ? "Criar Campanha" : "Editar Campanha"; ?>
                            </h1>
                            <a href="campanhas.php"
                                class="w-auto bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 text-center">
                                Voltar
                            </a>
                        </header>

                        <ul class="flex justify-between space-x-4 mb-6 border-b border-gray-300 dark:border-gray-700 overflow-x-auto">
                            <li class="p-2 border-b-2 border-purple-500">
                                <a href="#" class="text-purple-700 dark:text-purple-400 font-bold tab-link"
                                    data-tab="dados">Dados</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link"
                                    data-tab="imagens">Imagens</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link"
                                    data-tab="desconto">Desconto</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link"
                                    data-tab="ranking_compradores">Ranking de Compradores</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link"
                                    data-tab="barra_progresso">Barra de Progresso</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link"
                                    data-tab="ganhadores">Ganhadores</a>
                            </li>
                            <li class="p-2">
                                <a href="#" class="hover:text-purple-700 dark:hover:text-purple-400 tab-link"
                                    data-tab="cotas_premiadas">Cotas Premiadas</a>
                            </li>
                            
                        </ul>

                        <form method="POST" action="" onsubmit="return valida();" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="caminho_imagem_atual"
                                value="<?php echo isset($campanhas[0]['caminho_imagem']) ? $campanhas[0]['caminho_imagem'] : ''; ?>">
                            <input type="hidden" name="galeria_imagens_atual"
                                value="<?php echo isset($campanhas[0]['galeria_imagens']) ? $campanhas[0]['galeria_imagens'] : ''; ?>">

                            <div id="dados" class="tab-content">
                                <h2 class="text-xl font-semibold mb-4">Configurações Gerais</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="nome" class="block mb-2 font-medium">Nome <span
                                                class="text-red-500">*</span></label>
                                        <input type="text" id="nome" name="nome" required
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['nome']) ? $campanhas[0]['nome'] : ''; ?>">
                                        <span class="text-red-500 text-sm hidden" id="erro-nome">Este campo é
                                            obrigatório</span>
                                    </div>
                                    <div>
                                        <label for="descricao" class="block mb-2 font-medium">Descrição <span
                                                class="text-red-500">*</span></label>
                                        <textarea id="descricao" name="descricao" required
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"><?php echo isset($campanhas[0]['descricao']) ? $campanhas[0]['descricao'] : ''; ?></textarea>
                                        <span class="text-red-500 text-sm hidden" id="erro-descricao">Este campo é
                                            obrigatório</span>
                                    </div>
                                    <div>
                                        <label for="data_sorteio" class="block mb-2 font-medium">Data Sorteio<span class="text-red-500">*</span></label>
                                        <input type="datetime-local" name="data_sorteio" id="data_sorteio" required value="<?php echo $data_sorteio ; ?>" 
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-60">
                                    </div>
                                    <div>
    <label for="preco" class="block mb-2 font-medium">Preço <span class="text-red-500">*</span></label>
    <div class="relative">
        <span class="absolute left-2 top-2 text-gray-500">$</span>
        <input type="text" id="preco" name="preco" required 
            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 pl-8 rounded-md border border-gray-300 dark:border-gray-600"
            value="<?php echo isset($campanhas[0]['preco']) ? number_format($campanhas[0]['preco'], 2, ',', '.') : ''; ?>"
            oninput="formatPrice(this)">
    </div>
    <span class="text-red-500 text-sm hidden" id="erro-preco">Este campo é obrigatório</span>
</div>

<script>
    function formatPrice(input) {
        let value = input.value.replace(/\D/g, '');  // Remove caracteres não numéricos
        value = (value / 100).toFixed(2);  // Divide por 100 e limita a 2 casas decimais
        value = value.replace('.', ',');  // Substitui ponto por vírgula para exibição (se necessário para seu país)
        
        // Adiciona o separador de milhar (ponto) no valor
        let parts = value.split(',');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');  // Adiciona ponto a cada 3 dígitos

        input.value = parts.join(',');
    }
</script>

                                    <div>
                                        <label for="subtitulo" class="block mb-2 font-medium">Subtítulo</label>
                                        <input type="text" id="subtitulo" name="subtitulo"
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['subtitulo']) ? $campanhas[0]['subtitulo'] : ''; ?>">
                                    </div>
                                    <div>
                                        <label for="tipo_sorteio" class="block mb-2 font-medium">Tipo de Sorteio</label>
                                        <select id="tipo_sorteio" name="tipo_sorteio" required
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                            <option value="0" <?php echo isset($campanhas[0]['tipo_sorteio']) && $campanhas[0]['tipo_sorteio'] == 0 ? 'selected' : ''; ?>>Automático</option>
                                            <option value="1" <?php echo isset($campanhas[0]['tipo_sorteio']) && $campanhas[0]['tipo_sorteio'] == 1 ? 'selected' : ''; ?>>Manual</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="layout" class="block mb-2 font-medium">Layout</label>
                                        <select id="layout" name="layout" required
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                            <option value="0" <?php echo isset($campanhas[0]['layout']) && $campanhas[0]['layout'] == 0 ? 'selected' : ''; ?>>Rincon</option>
                                            <option value="1" <?php echo isset($campanhas[0]['layout']) && $campanhas[0]['layout'] == 1 ? 'selected' : ''; ?>>Buzeira</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="compra_minima" class="block mb-2 font-medium">Compra Mínima<span class="text-red-500">*</span></label>
                                        <input type="number" id="compra_minima" name="compra_minima" required
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['compra_minima']) ? $campanhas[0]['compra_minima'] : ''; ?>">
                                    </div>
                                    <div>
                                        <label for="compra_maxima" class="block mb-2 font-medium">Compra Máxima <span
                                                class="text-red-500">*</span></label>
                                        <input type="number" id="compra_maxima" name="compra_maxima" required
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['compra_maxima']) ? $campanhas[0]['compra_maxima'] : ''; ?>">
                                        <span class="text-red-500 text-sm hidden" id="erro-compra_maxima">Este campo é
                                            obrigatório</span>
                                    </div>
                                    <div>
                                        <label for="quantidade_numeros" class="block mb-2 font-medium">Quantidade de Números
                                            <span class="text-red-500">*</span></label>
                                        <input type="number" id="quantidade_numeros" name="quantidade_numeros" required
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['quantidade_numeros']) ? $campanhas[0]['quantidade_numeros'] : ''; ?>">
                                        <span class="text-red-500 text-sm hidden" id="erro-quantidade_numeros">Este campo é
                                            obrigatório</span>
                                    </div>
                                    <div>
                                        <label for="status" class="block mb-2 font-medium">Status</label>
                                        <select id="status" name="status" required
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                            <option value="1" <?php echo isset($campanhas[0]['status']) && $campanhas[0]['status'] == 1 ? 'selected' : ''; ?>>Ativa</option>
                                            <option value="0" <?php echo isset($campanhas[0]['status']) && $campanhas[0]['status'] == 0 ? 'selected' : ''; ?>>Inativa</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div id="imagens" class="tab-content hidden">
                                <h2 class="text-xl font-semibold mb-4">Imagens</h2>
                                <div class="space-y-6">
                                    <!-- Imagem Principal -->
                                    <div>
                                        <label class="block mb-2 font-medium">Imagem principal</label>
                                        <div class="flex flex-col space-y-4">
                                            <?php if (isset($campanhas[0]['caminho_imagem']) && !empty($campanhas[0]['caminho_imagem'])): ?>
                                                <div class="relative group">
                                                    <img src="../<?php echo $campanhas[0]['caminho_imagem']; ?>"
                                                        alt="Imagem atual" class="max-w-[200px] rounded-lg shadow-md">
                                                    <button type="button" onclick="removerImagemPrincipal(this)"
                                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex items-center justify-center w-full">
                                                <label for="imagem_principal"
                                                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-[#3F3F46] hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400"
                                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                            fill="none" viewBox="0 0 20 16">
                                                            <path stroke="currentColor" stroke-linecap="round"
                                                                stroke-linejoin="round" stroke-width="2"
                                                                d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                                        </svg>
                                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span
                                                                class="font-semibold">Clique para enviar</span> ou arraste e
                                                            solte</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF
                                                            (MAX. 2MB)</p>
                                                    </div>
                                                    <input type="file" name="imagem_principal" id="imagem_principal" 
                                                        accept="image/*" class="hidden"
                                                        onchange="document.querySelector('input[name=\'caminho_imagem_atual\']').value = ''; previewImagem(this, 'preview-principal')" />
                                                </label>
                                            </div>
                                            <div id="preview-principal" class="hidden mt-4">
                                                <img src="" alt="Preview" class="max-w-[200px] rounded-lg shadow-md">
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block mb-2 font-medium">Galeria de imagens</label>
                                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                                            <?php
                                            if (isset($campanhas[0]['galeria_imagens']) && !empty($campanhas[0]['galeria_imagens'])):
                                                $imagens = explode(',', $campanhas[0]['galeria_imagens']);
                                                foreach ($imagens as $index => $img):
                                                    if (!empty($img)):
                                                        ?>
                                                        <div class="relative group">
                                                            <img src="../<?php echo $img; ?>" alt="Imagem da galeria"
                                                                class="w-full h-40 object-cover rounded-lg shadow-md">
                                                            <button type="button"
                                                                onclick="removerImagemGaleria(this, <?php echo $index; ?>)"
                                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    <?php
                                                    endif;
                                                endforeach;
                                            endif;
                                            ?>
                                        </div>
                                        <div class="flex items-center justify-center w-full">
                                            <label for="galeria"
                                                class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-[#3F3F46] hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                    <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 20 16">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                                    </svg>
                                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span
                                                            class="font-semibold">Clique para enviar</span> ou arraste e
                                                        solte</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF
                                                        (MAX. 2MB)</p>
                                                </div>
                                                <input type="file" name="galeria[]" id="galeria" accept="image/*" multiple
                                                    class="hidden" onchange="previewGaleria(this)" />
                                            </label>
                                        </div>
                                        <div id="preview-galeria"
                                            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="desconto" class="tab-content hidden">
                                <h2 class="text-xl font-semibold mb-4">Desconto</h2>
                                <div class="space-y-4">

                                    <!-- pacote padrão -->
                                    <div>
                                        <label for="habilitar_pacote_padrao" class="block mb-2 font-medium">Habilitar Pacote Padrão</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_pacote_padrao" name="habilitar_pacote_padrao" value="1"
                                                <?php echo isset($campanhas[0]['habilitar_pacote_padrao']) && $campanhas[0]['habilitar_pacote_padrao'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- adição rápida -->
                                    <div>
                                        <label for="habilitar_adicao_rapida" class="block mb-2 font-medium">Habilitar Adição Rápida</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_adicao_rapida" name="habilitar_adicao_rapida" value="1"
                                                <?php echo isset($campanhas[0]['habilitar_adicao_rapida']) && $campanhas[0]['habilitar_adicao_rapida'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>
                                
                                    <div>
                                        <label for="habilitar_desconto_acumulativo" class="block mb-2 font-medium">Habilitar Desconto Acumulativo</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_desconto_acumulativo" name="habilitar_desconto_acumulativo" value="1"
                                                <?php echo isset($campanhas[0]['habilitar_desconto_acumulativo']) && $campanhas[0]['habilitar_desconto_acumulativo'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>

                                    <div>
                                        <label for="habilitar_pacote_promocional" class="block mb-2 font-medium">Habilitar Pacote Promocional</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_pacote_promocional" name="habilitar_pacote_promocional" value="1"
                                                <?php echo isset($campanhas[0]['habilitar_pacote_promocional']) && $campanhas[0]['habilitar_pacote_promocional'] ? 'checked' : ''; ?>
                                                >
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- pacote promocional -->
                                    <div id="pacote_promocional" class="mb-4">
                                        <h3 class="text-lg font-semibold mb-2">Pacote Promocional</h3>
                                        <div id="descontos-container" class="space-y-4">
                                            <?php
                                            if (isset($campanhas[0]['pacote_promocional']) && !empty($campanhas[0]['pacote_promocional'])) {
                                                $pacotes_promocionais = json_decode($campanhas[0]['pacote_promocional'], true);
                                                if (is_array($pacotes_promocionais)) {
                                                    foreach ($pacotes_promocionais as $pacote_promocional) {
                                                        ?>
                                                        <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow">
                                                            <div class="grid grid-cols-6 gap-4">
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Valor do Bilhete
                                                                    </label>
                                                                    <input type="number" step="0.01" name="pacote_promocional[]" 
                                                                        value="<?php echo $pacote_promocional['valor_bilhete']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                                        onkeyup="calcularValorPacote(this)">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Quantidade de números
                                                                    </label>
                                                                    <input type="number" name="pacote_promocional[]" 
                                                                        value="<?php echo $pacote_promocional['quantidade_numeros']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                                        onkeyup="calcularValorPacote(this)">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Valor do Pacote</label>
                                                                    <input type="number" step="0.01" name="pacote_promocional[]" value="<?php echo $pacote_promocional['valor_pacote']; ?>" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600" readonly>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Benefício do Pacote</label>
                                                                    <?php $bt = isset($pacote_promocional['beneficio_tipo']) ? $pacote_promocional['beneficio_tipo'] : ''; ?>
                                                                    <select name="beneficio_tipo_promocional[]" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                        <option value="" <?php echo $bt==''?'selected':''; ?>>Nenhum</option>
                                                                        <option value="roleta" <?php echo $bt=='roleta'?'selected':''; ?>>Roleta</option>
                                                                        <option value="raspadinha" <?php echo $bt=='raspadinha'?'selected':''; ?>>Raspadinha</option>
                                                                    </select>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Qtd. Benefício</label>
                                                                    <input type="number" min="0" name="beneficio_quantidade_promocional[]" value="<?php echo isset($pacote_promocional['beneficio_quantidade']) ? (int)$pacote_promocional['beneficio_quantidade'] : 0; ?>" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Benefício do Pacote</label>
                                                                    <select name="beneficio_tipo_promocional[]" 
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                        <?php $bt = isset($pacote_promocional['beneficio_tipo']) ? $pacote_promocional['beneficio_tipo'] : ''; ?>
                                                                        <option value="" <?php echo $bt==''?'selected':''; ?>>Nenhum</option>
                                                                        <option value="roleta" <?php echo $bt=='roleta'?'selected':''; ?>>Roleta</option>
                                                                        <option value="raspadinha" <?php echo $bt=='raspadinha'?'selected':''; ?>>Raspadinha</option>
                                                                    </select>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Qtd. Benefício</label>
                                                                    <input type="number" min="0" name="beneficio_quantidade_promocional[]" 
                                                                        value="<?php echo isset($pacote_promocional['beneficio_quantidade']) ? (int)$pacote_promocional['beneficio_quantidade'] : 0; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                </div>
                                                            </div>
                                                            <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerDesconto(this)">
                                                                Remover desconto
                                                            </button>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                        <button type="button" onclick="adicionarDescontoPromocional('promocional')" 
                                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            Adicionar Pacote Promocional
                                        </button>
                                    </div>

                                    <!-- pacote exclusivo -->
                                    
                                    <div class="mb-4">
                                        <h3 class="text-lg font-semibold mb-2">Pacote Exclusivo</h3>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilita_pacote_promocional_exclusivo" name="habilita_pacote_promocional_exclusivo" value="1"
                                                <?php echo isset($campanhas[0]['habilita_pacote_promocional_exclusivo']) && $campanhas[0]['habilita_pacote_promocional_exclusivo'] ? 'checked' : ''; ?>
                                                >
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div id="pacote_exclusivo">

                                        <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md mb-4">
                                            <p class="text-sm text-purple-800 dark:text-purple-200">
                                                <span class="font-bold">⭐ Oferta Exclusiva!</span><br>
                                                Compre com desconto: Condição especial de pacotes por tempo LIMITADO! Não perca essa oportunidade de aumentar suas chances de ganhar!
                                            </p>
                                        </div>
                                        <div class="space-y-4">
                                            <?php
                                            if (isset($campanhas[0]['pacotes_exclusivos']) && !empty($campanhas[0]['pacotes_exclusivos'])) {
                                                $pacotes = json_decode($campanhas[0]['pacotes_exclusivos'], true);
                                                if (is_array($pacotes)) {
                                                    foreach ($pacotes as $pacote) {
                                                        ?>
                                                        <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow">
                                                            <div class="grid grid-cols-6 gap-4">
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Valor do Bilhete
                                                                    </label>
                                                                    <input type="number" step="0.01" name="valor_bilhete_exclusivo[]" 
                                                                        value="<?php echo $pacote['valor_bilhete']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                                        onkeyup="calcularValorPacote(this)">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Quantidade de números
                                                                    </label>
                                                                    <input type="number" name="quantidade_desconto_exclusivo[]" 
                                                                        value="<?php echo $pacote['quantidade_numeros']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                                        onkeyup="calcularValorPacote(this)">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Valor do Pacote
                                                                    </label>
                                                                    <input type="number" step="0.01" name="valor_desconto_exclusivo[]" 
                                                                        value="<?php echo $pacote['valor_pacote']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                                        readonly>
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Código do Pacote
                                                                    </label>
                                                                    <input type="text" name="codigo_desconto_exclusivo[]" 
                                                                        value="<?php echo $pacote['codigo_pacote']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                </div>
                                                            </div>
                                                            <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerDesconto(this)">
                                                                Remover desconto
                                                            </button>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                        <button type="button" onclick="adicionarDescontoExclusivo('exclusivo')"
                                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            Adicionar Pacote Exclusivo
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="ranking_compradores" class="tab-content hidden">
                                <h2 class="text-xl font-semibold mb-4">Ranking de Compradores</h2>
                                <div class="space-y-4">
                                    <div class="mb-4">
                                        <label for="habilitar_ranking" class="block mb-2 font-medium">Top Compradores</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_ranking" name="habilitar_ranking" value="1"
                                                <?php echo isset($campanhas[0]['habilitar_ranking']) && $campanhas[0]['habilitar_ranking'] == 1 ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>

                                    <div id="div_quantidade_ranking" class="mb-4 hidden">
                                        <label for="quantidade_ranking" class="block mb-2 font-medium">Quantidade no Ranking (1 a 10)</label>
                                        <input type="text" id="quantidade_ranking" name="quantidade_ranking" min="1" max="10"
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['quantidade_ranking']) ? $campanhas[0]['quantidade_ranking'] : ''; ?>">
                                    </div>

                                    <div class="mb-4">
                                        <label for="selecionar_top_ganhadores" class="block mb-2 font-medium">Selecionar Top Ganhadores</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="selecionar_top_ganhadores" name="selecionar_top_ganhadores" value="1"
                                                <?php echo isset($campanhas[0]['selecionar_top_ganhadores']) && $campanhas[0]['selecionar_top_ganhadores'] == 1 ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>

                                    <div id="div_filtro_periodo_top_ganhadores" class="mb-4 hidden">
                                        <label class="block text-sm font-medium mb-1">Filtro de Período</label>
                                        <select id="filtro_periodo_top_ganhadores" name="filtro_periodo_top_ganhadores" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white">
                                            <option value='{"filtro":"hoje","valor":""}' <?php 
                                                $filtro_salvo = isset($campanhas[0]['filtro_periodo_top_ganhadores']) ? json_decode($campanhas[0]['filtro_periodo_top_ganhadores'], true) : null;
                                                echo ($filtro_salvo && $filtro_salvo['filtro'] === 'hoje') ? 'selected' : ''; 
                                            ?>>Hoje</option>
                                            <option value='{"filtro":"ontem","valor":""}' <?php 
                                                echo ($filtro_salvo && $filtro_salvo['filtro'] === 'ontem') ? 'selected' : ''; 
                                            ?>>Ontem</option>
                                            <option value='{"filtro":"ultimo_mes","valor":""}' <?php 
                                                echo ($filtro_salvo && $filtro_salvo['filtro'] === 'ultimo_mes') ? 'selected' : ''; 
                                            ?>>Último Mês</option>
                                            <option value='{"filtro":"personalizado","valor":""}' <?php 
                                                echo ($filtro_salvo && $filtro_salvo['filtro'] === 'personalizado') ? 'selected' : ''; 
                                            ?>>Personalizado</option>
                                        </select>
                                        
                                        <div id="div_datas_personalizadas" class="hidden mt-4 space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Data Inicial</label>
                                                <input type="date" id="data_inicial_personalizada" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white" 
                                                    value="<?php 
                                                        if ($filtro_salvo && $filtro_salvo['filtro'] === 'personalizado' && !empty($filtro_salvo['valor'])) {
                                                            $datas = explode(' até ', $filtro_salvo['valor']);
                                                            echo $datas[0];
                                                        }
                                                    ?>">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Data Final</label>
                                                <input type="date" id="data_final_personalizada" class="w-full p-2 border rounded dark:bg-[#3F3F46] dark:border-gray-600 dark:text-white"
                                                    value="<?php 
                                                        if ($filtro_salvo && $filtro_salvo['filtro'] === 'personalizado' && !empty($filtro_salvo['valor'])) {
                                                            $datas = explode(' até ', $filtro_salvo['valor']);
                                                            echo $datas[1];
                                                        }
                                                    ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="ganhadores" class="tab-content hidden">
                                <h2 class="text-xl font-semibold mb-4">Ganhadores</h2>
                                <div class="space-y-4">
                                    <div>
                                        <label for="vencedor_sorteio" class="block mb-2 font-medium">Telefone do ganhador do
                                            sorteio</label>
                                        <input type="text" id="vencedor_sorteio" name="vencedor_sorteio"
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['vencedor_sorteio']) ? $campanhas[0]['vencedor_sorteio'] : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <div id="cotas_premiadas" class="tab-content hidden">
                                <h2 class="text-xl font-semibold mb-4">Cotas Premiadas</h2>
                                <div class="mb-4">
                                    <label for="habilitar_cotas_em_dobro" class="block mb-2 font-medium">Cotas em Dobro</label>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="habilitar_cotas_em_dobro"
                                            name="habilitar_cotas_em_dobro" value="1" <?php echo isset($campanhas[0]['habilitar_cotas_em_dobro']) && $campanhas[0]['habilitar_cotas_em_dobro'] ? 'checked' : ''; ?>>
                                        <div class="toggle-switch-background">
                                            <div class="toggle-switch-handle"></div>
                                        </div>
                                    </label>
                                </div>
                                <div id="campos_cotas_dobro" class="space-y-4 mb-4" style="display: <?php echo isset($campanhas[0]['habilitar_cotas_em_dobro']) && $campanhas[0]['habilitar_cotas_em_dobro'] ? 'block' : 'none'; ?>">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Título do Alerta</label>
                                        <input type="text" id="titulo_cotas_dobro" name="titulo_cotas_dobro" 
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['titulo_cotas_dobro']) ? $campanhas[0]['titulo_cotas_dobro'] : ''; ?>"
                                            placeholder="Ex: COTAS EM DOBRO ATIVADAS!">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Subtítulo do Alerta</label>
                                        <input type="text" id="subtitulo_cotas_dobro" name="subtitulo_cotas_dobro" 
                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                            value="<?php echo isset($campanhas[0]['subtitulo_cotas_dobro']) ? $campanhas[0]['subtitulo_cotas_dobro'] : ''; ?>"
                                            placeholder="Ex: Aproveite! Todas as cotas estão valendo em dobro.">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="quantidade_cotas_premiadas" class="block mb-2 font-medium">Quantidade de Cotas Premiadas (MAX: 20)</label>
                                    <input type="number" id="quantidade_cotas_premiadas" name="quantidade_cotas_premiadas" min="1" max="20"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($campanhas[0]['quantidade_cotas_premiadas']) ? $campanhas[0]['quantidade_cotas_premiadas'] : ''; ?>"
                                        placeholder="Quantidade de cotas que serão selecionadas automaticamente">
                                    <p class="text-sm text-gray-500 mt-1">Quantidade de cotas que serão selecionadas automaticamente</p>
                                </div>
                                <div class="mb-4">
                                    <label for="premio_cotas_premiadas" class="block mb-2 font-medium">Prêmio das Cotas Premiadas</label>
                                    <input type="text" id="premio_cotas_premiadas" name="premio_cotas_premiadas"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        value="<?php echo isset($campanhas[0]['premio_cotas_premiadas']) ? $campanhas[0]['premio_cotas_premiadas'] : ''; ?>"
                                        placeholder="Ex: R$ 500 ou AUDI A3">
                                    <p class="text-sm text-gray-500 mt-1">Prêmio que será associado às cotas premiadas</p>
                                </div>
                                <div class="mb-4">
                                    <label for="cotas_premiadas" class="block mb-2 font-medium">Cotas Premiadas Atuais</label>
                                    <div class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-3 rounded-md border border-gray-300 dark:border-gray-600 min-h-[60px]">
                                        <?php 
                                        require_once('../functions/functions_sistema.php');
                                        if (isset($campanhas[0]['cotas_premiadas']) && !empty($campanhas[0]['cotas_premiadas'])) {
                                            $cotas = explode(',', $campanhas[0]['cotas_premiadas']);
                                            $largura_cota = obterLarguraCotaPorCampanha($conn, $campanhas[0]['id']);
                                            foreach ($cotas as $cota) {
                                                $cota_fmt = formatarCotaComLargura($cota, $largura_cota);
                                                echo '<span class="inline-block bg-green-600 text-white px-2 py-1 rounded mr-2 mb-2">' . $cota_fmt . '</span>';
                                            }
                                        } else {
                                            echo 'Nenhuma cota premiada definida';
                                        }
                                        ?>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Cotas selecionadas automaticamente pelo sistema</p>
                                </div>
                                <div>
                                    <label for="descricao_cotas_premiadas" class="block mb-2 font-medium">Descrição</label>
                                    <textarea id="descricao_cotas_premiadas" name="descricao_cotas_premiadas"
                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                        placeholder="ex: A premiação da(s) cota(s) premiada(s) serão efetuadas no dia da campanha, fique esperto!"><?php echo isset($campanhas[0]['descricao_cotas_premiadas']) ? $campanhas[0]['descricao_cotas_premiadas'] : ''; ?></textarea>
                                    <p class="text-sm text-gray-500 mt-1">ex: A premiação da(s) cota(s) premiada(s) serão
                                        efetuadas no dia da campanha, fique esperto!</p>
                                </div>
                            </div>

                            <div id="roletas_raspadinhas" class="tab-content hidden">
                                <h2 class="text-xl font-semibold mb-4">Roletas e Raspadinhas</h2>
                                
                                <!-- Configuração da Roleta -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-4">🎰 Configuração da Roleta</h3>
                                    <div class="mb-4">
                                        <label for="habilitar_roleta" class="block mb-2 font-medium">Habilitar Roleta</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_roleta" name="habilitar_roleta" value="1"
                                                <?php echo isset($campanhas[0]['habilitar_roleta']) && $campanhas[0]['habilitar_roleta'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div id="config_roleta" class="space-y-4" style="display: <?php echo isset($campanhas[0]['habilitar_roleta']) && $campanhas[0]['habilitar_roleta'] ? 'block' : 'none'; ?>">
                                        <div>
                                            <label for="titulo_roleta" class="block mb-2 font-medium">Título da Roleta</label>
                                            <input type="text" id="titulo_roleta" name="titulo_roleta"
                                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                value="<?php echo isset($campanhas[0]['titulo_roleta']) ? $campanhas[0]['titulo_roleta'] : '🎰 Roleta da Sorte'; ?>"
                                                placeholder="Ex: 🎰 Roleta da Sorte">
                                        </div>
                                        
                                        <div>
                                            <label for="descricao_roleta" class="block mb-2 font-medium">Descrição da Roleta</label>
                                            <textarea id="descricao_roleta" name="descricao_roleta"
                                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                placeholder="Ex: Gire a roleta e ganhe prêmios incríveis!"><?php echo isset($campanhas[0]['descricao_roleta']) ? $campanhas[0]['descricao_roleta'] : ''; ?></textarea>
                                        </div>
                                        
                                        <div>
                                            <label for="itens_roleta" class="block mb-2 font-medium">Itens da Roleta</label>
                                            <div id="itens_roleta_container" class="space-y-3">
                                                <?php
                                                if (isset($campanhas[0]['itens_roleta']) && !empty($campanhas[0]['itens_roleta'])) {
                                                    $itens = json_decode($campanhas[0]['itens_roleta'], true);
                                                    if (is_array($itens)) {
                                                        foreach ($itens as $index => $item) {
                                                            ?>
                                                            <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow border">
                                                                <div class="grid grid-cols-2 gap-4">
                                                                    <div>
                                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                            Nome do Item
                                                                        </label>
                                                                        <input type="text" name="nome_item_roleta[]" 
                                                                            value="<?php echo $item['nome']; ?>"
                                                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                                            placeholder="Ex: R$ 50,00">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                            Status
                                                                        </label>
                                                                        <select name="status_item_roleta[]" 
                                                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                            <option value="disponivel" <?php echo $item['status'] == 'disponivel' ? 'selected' : ''; ?>>Disponível</option>
                                                                            <option value="bloqueado" <?php echo $item['status'] == 'bloqueado' ? 'selected' : ''; ?>>Bloqueado</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerItemRoleta(this)">
                                                                    Remover Item
                                                                </button>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <button type="button" onclick="adicionarItemRoleta()"
                                                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                                Adicionar Item da Roleta
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configuração dos Pacotes de Roleta -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-4">📦 Pacotes de Roleta (removidos)</h3>
                                    <div id="pacotes_roleta" class="space-y-4" style="display:none;">
                                        <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md mb-4">
                                            <p class="text-sm text-purple-800 dark:text-purple-200">
                                                <span class="font-bold">🎰 Pacotes de Roleta!</span><br>
                                                Configure pacotes com diferentes quantidades de giros na roleta. Cada pacote gera um código único para validação.
                                            </p>
                                        </div>
                                        <div id="pacotes_roleta_container" class="space-y-4">
                                            <?php
                                            if (false) {
                                                $pacotes = [];
                                                if (is_array($pacotes)) {
                                                    foreach ($pacotes as $pacote) {
                                                        ?>
                                                        <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow">
                                                            <div class="grid grid-cols-4 gap-4">
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Valor do Pacote
                                                                    </label>
                                                                    <input type="number" step="0.01" name="valor_pacote_roleta[]" 
                                                                        value="<?php echo $pacote['valor_pacote']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Quantidade de Giros
                                                                    </label>
                                                                    <input type="number" name="quantidade_giros_roleta[]" 
                                                                        value="<?php echo $pacote['quantidade_giros']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Código do Pacote
                                                                    </label>
                                                                    <input type="text" name="codigo_pacote_roleta[]" 
                                                                        value="<?php echo $pacote['codigo_pacote']; ?>"
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                        Destaque
                                                                    </label>
                                                                    <select name="destaque_pacote_roleta[]" 
                                                                        class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                        <option value="0" <?php echo $pacote['destaque'] == '0' ? 'selected' : ''; ?>>Normal</option>
                                                                        <option value="1" <?php echo $pacote['destaque'] == '1' ? 'selected' : ''; ?>>Mais Popular</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerPacoteRoleta(this)">
                                                                Remover Pacote
                                                            </button>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                        <button type="button" onclick="adicionarPacoteRoleta()"
                                            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            Adicionar Pacote de Roleta
                                        </button>
                                    </div>
                                </div>

                                <!-- Configuração da Raspadinha -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-4">🎫 Configuração da Raspadinha</h3>
                                    <div class="mb-4">
                                        <label for="habilitar_raspadinha" class="block mb-2 font-medium">Habilitar Raspadinha</label>
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="habilitar_raspadinha" name="habilitar_raspadinha" value="1"
                                                <?php echo isset($campanhas[0]['habilitar_raspadinha']) && $campanhas[0]['habilitar_raspadinha'] ? 'checked' : ''; ?>>
                                            <div class="toggle-switch-background">
                                                <div class="toggle-switch-handle"></div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div id="config_raspadinha" class="space-y-4" style="display: <?php echo isset($campanhas[0]['habilitar_raspadinha']) && $campanhas[0]['habilitar_raspadinha'] ? 'block' : 'none'; ?>">
                                        <div>
                                            <label for="titulo_raspadinha" class="block mb-2 font-medium">Título da Raspadinha</label>
                                            <input type="text" id="titulo_raspadinha" name="titulo_raspadinha"
                                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                value="<?php echo isset($campanhas[0]['titulo_raspadinha']) ? $campanhas[0]['titulo_raspadinha'] : '🎫 Raspadinha da Sorte'; ?>"
                                                placeholder="Ex: 🎫 Raspadinha da Sorte">
                                        </div>
                                        
                                        <div>
                                            <label for="descricao_raspadinha" class="block mb-2 font-medium">Descrição da Raspadinha</label>
                                            <textarea id="descricao_raspadinha" name="descricao_raspadinha"
                                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                placeholder="Ex: Raspe e descubra prêmios incríveis!"><?php echo isset($campanhas[0]['descricao_raspadinha']) ? $campanhas[0]['descricao_raspadinha'] : ''; ?></textarea>
                                        </div>
                                        
                                        <div>
                                            <label for="itens_raspadinha" class="block mb-2 font-medium">Itens da Raspadinha</label>
                                            <div id="itens_raspadinha_container" class="space-y-3">
                                                <?php
                                                if (isset($campanhas[0]['itens_raspadinha']) && !empty($campanhas[0]['itens_raspadinha'])) {
                                                    $itens = json_decode($campanhas[0]['itens_raspadinha'], true);
                                                    if (is_array($itens)) {
                                                        foreach ($itens as $index => $item) {
                                                            ?>
                                                            <div class="bg-white dark:bg-[#27272A] p-4 rounded-lg shadow border">
                                                                <div class="grid grid-cols-2 gap-4">
                                                                    <div>
                                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                            Nome do Item
                                                                        </label>
                                                                        <input type="text" name="nome_item_raspadinha[]" 
                                                                            value="<?php echo $item['nome']; ?>"
                                                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                                                            placeholder="Ex: R$ 100,00">
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                                                            Status
                                                                        </label>
                                                                        <select name="status_item_raspadinha[]" 
                                                                            class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                                                            <option value="disponivel" <?php echo $item['status'] == 'disponivel' ? 'selected' : ''; ?>>Disponível</option>
                                                                            <option value="bloqueado" <?php echo $item['status'] == 'bloqueado' ? 'selected' : ''; ?>>Bloqueado</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerItemRaspadinha(this)">
                                                                    Remover Item
                                                                </button>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <button type="button" onclick="adicionarItemRaspadinha()"
                                                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                                Adicionar Item da Raspadinha
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="w-100 flex justify-end mt-10">
                                <button type="submit" name="action" id="submit"
                                    value="<?php echo isset($action) ? $action : 'criar'; ?>"
                                    class="bg-purple-600 dark:bg-purple-500 text-white px-4 py-2 rounded-md hover:bg-purple-500 dark:hover:bg-purple-400">
                                    <?php echo $action == "criar" ? "Criar Campanha" : "Salvar Alterações"; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </main>
        </div>

        <script>
            $(document).ready(function ()
            {
                validaDescontoPromocial();
                $("#habilitar_pacote_promocional").click(function ()
                {
                    validaDescontoPromocial();
                });

                function validaDescontoPromocial()
                {
                    if($("#habilitar_pacote_promocional").is(":checked"))
                        $("#pacote_promocional").show("fast");
                    else
                        $("#pacote_promocional").hide("slow");
                }

                validaDescontoExclusivo();
                $("#habilita_pacote_promocional_exclusivo").click(function ()
                {
                    validaDescontoExclusivo();
                });

                function validaDescontoExclusivo()
                {
                    if($("#habilita_pacote_promocional_exclusivo").is(":checked"))
                        $("#pacote_exclusivo").show("fast");
                    else
                        $("#pacote_exclusivo").hide("slow");
                }

                validaRanking();
                $("#habilitar_ranking").click(function ()
                {
                    validaRanking();
                });

                function validaRanking()
                {
                    if($("#habilitar_ranking").is(":checked"))
                        $("#div_quantidade_ranking").show("fast");
                    else
                        $("#div_quantidade_ranking").hide("slow");
                }

                validaProgressoManual();
                $("#ativar_progresso_manual").click(function ()
                {
                    validaProgressoManual();
                });

                function validaProgressoManual()
                {
                    if($("#ativar_progresso_manual").is(":checked"))
                        $("#div_progresso_manual").show("fast");
                    else
                        $("#div_progresso_manual").hide("slow");
                }

                // Event listeners para Roletas e Raspadinhas
                validaRoleta();
                $("#habilitar_roleta").click(function ()
                {
                    validaRoleta();
                });

                function validaRoleta()
                {
                    if($("#habilitar_roleta").is(":checked"))
                        $("#config_roleta").show("fast");
                    else
                        $("#config_roleta").hide("slow");
                }

                validaPacotesRoleta();
                // Removido: validação de exibição de pacotes de roleta

                validaRaspadinha();
                $("#habilitar_raspadinha").click(function ()
                {
                    validaRaspadinha();
                });

                function validaRaspadinha()
                {
                    if($("#habilitar_raspadinha").is(":checked"))
                        $("#config_raspadinha").show("fast");
                    else
                        $("#config_raspadinha").hide("slow");
                }

                $("#submit").click(function()
                {
                    $("input, textarea, select").each(function (pos, input)
                    {
                        if($(this).prop('required') && $(this).val().trim() == "")
                        {
                            texto_span = $(this).parent().find("label").text().replace("*", "").trim();
                            console.log(texto_span);
                            
                            alert("O Campo " + texto_span + " é obrigatório");
                            return false;
                        }
                    });
                });
            });
            // Função para troca de abas
            document.addEventListener('DOMContentLoaded', function () {
                function switchTab(targetTab) {
                    // Remove a classe active de todas as abas
                    document.querySelectorAll('.tab-link').forEach(l => {
                        l.classList.remove('text-purple-700', 'dark:text-purple-400', 'font-bold');
                        l.parentElement.classList.remove('border-b-2', 'border-purple-500');
                    });

                    // Esconde todos os conteúdos
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Ativa a aba selecionada
                    const selectedTab = document.querySelector(`[data-tab="${targetTab}"]`);
                    if (selectedTab) {
                        selectedTab.classList.add('text-purple-700', 'dark:text-purple-400', 'font-bold');
                        selectedTab.parentElement.classList.add('border-b-2', 'border-purple-500');
                        document.getElementById(targetTab).classList.remove('hidden');
                    }
                }

                // Adiciona evento de clique em todas as abas
                document.querySelectorAll('.tab-link').forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        const targetTab = this.getAttribute('data-tab');
                        switchTab(targetTab);
                    });
                });

                // Ativa a primeira aba por padrão
                const firstTab = document.querySelector('.tab-link');
                if (firstTab) {
                    const firstTabId = firstTab.getAttribute('data-tab');
                    switchTab(firstTabId);
                }
            });

            // Função para inicializar os códigos dos pacotes existentes
            document.addEventListener('DOMContentLoaded', function () {
                // Gera código para o primeiro campo de desconto se estiver vazio
                const primeiroCodigoInput = document.querySelector('input[name="codigo_desconto[]"]');
                if (primeiroCodigoInput && !primeiroCodigoInput.value) {
                    gerarCodigoAleatorio(primeiroCodigoInput);
                }

                // Gera códigos para todos os campos de desconto vazios
                document.querySelectorAll('input[name="codigo_desconto[]"]').forEach(input => {
                    if (!input.value) {
                        gerarCodigoAleatorio(input);
                    }
                });
            });

            // Função para calcular valor do pacote
            function calcularValorPacote(input) {
                const container = input.closest('.grid');
                const inputs = container.querySelectorAll('input');
                const valorBilhete = parseFloat(inputs[0].value) || 0;
                const quantidade = parseInt(inputs[1].value) || 0;
                const valorPacoteInput = inputs[2];
                const codigoInput = inputs[3];

                if (valorBilhete && quantidade) {
                    const valorTotal = (valorBilhete * quantidade).toFixed(2);
                    valorPacoteInput.value = valorTotal;
                }

                // Gera código se estiver vazio
                if (!codigoInput.value) {
                    gerarCodigoAleatorio(codigoInput);
                }
            }

            // Função para gerar código aleatório
            function gerarCodigoAleatorio(input) {
                const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let codigo = '';
                for (let i = 0; i < 8; i++) {
                    codigo += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
                }
                input.value = codigo;
            }

            // Função para adicionar desconto
            function adicionarDescontoPromocional(tipo) {
                const container = tipo === 'promocional' ? 
                    document.getElementById('pacote_promocional').querySelector('#descontos-container') : 
                    document.getElementById('pacote_exclusivo').querySelector('.space-y-4');
                    
                const novoDesconto = document.createElement('div');
                novoDesconto.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg shadow';
                
                const sufixo = tipo === 'promocional' ? 'pacote_promocional' : 'pacotes_exclusivos';
                
                novoDesconto.innerHTML = `
                    <div class="grid grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Valor do Bilhete
                            </label>
                            <input type="number" step="0.01" name="${sufixo}[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                onchange="calcularValorPacote(this)" onkeyup="calcularValorPacote(this)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Quantidade de números
                            </label>
                            <input type="number" name="${sufixo}[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                onchange="calcularValorPacote(this)" onkeyup="calcularValorPacote(this)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Valor do Pacote
                            </label>
                            <input type="number" step="0.01" name="${sufixo}[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Benefício do Pacote</label>
                            <select name="beneficio_tipo_promocional[]" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                <option value="">Nenhum</option>
                                <option value="roleta">Roleta</option>
                                <option value="raspadinha">Raspadinha</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Qtd. Benefício</label>
                            <input type="number" min="0" name="beneficio_quantidade_promocional[]" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerDesconto(this)">
                        Remover desconto
                    </button>
                `;
                container.appendChild(novoDesconto);

                // Gera código para o novo campo de desconto
                const novoCodigoInput = novoDesconto.querySelector(`input[name="${sufixo}[]"]:last-of-type`);
                gerarCodigoAleatorio(novoCodigoInput);
            }

            // Função para adicionar desconto
            function adicionarDescontoExclusivo(tipo) {
                const container = tipo === 'promocional' ? 
                    document.getElementById('pacote_promocional').querySelector('#descontos-container') : 
                    document.getElementById('pacote_exclusivo').querySelector('.space-y-4');
                    
                const novoDesconto = document.createElement('div');
                novoDesconto.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg shadow';
                
                const sufixo = tipo === 'promocional' ? 'pacote_promocional' : 'pacotes_exclusivos';
                
                novoDesconto.innerHTML = `
                    <div class="grid grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Valor do Bilhete
                            </label>
                            <input type="number" step="0.01" name="${sufixo}[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                onchange="calcularValorPacote(this)" onkeyup="calcularValorPacote(this)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Quantidade de números
                            </label>
                            <input type="number" name="${sufixo}[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                onchange="calcularValorPacote(this)" onkeyup="calcularValorPacote(this)">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Valor do Pacote
                            </label>
                            <input type="number" step="0.01" name="${sufixo}[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Código do Pacote
                            </label>
                            <input type="text" name="${sufixo}[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Benefício do Pacote</label>
                            <select name="beneficio_tipo_exclusivo[]" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                <option value="">Nenhum</option>
                                <option value="roleta">Roleta</option>
                                <option value="raspadinha">Raspadinha</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Qtd. Benefício</label>
                            <input type="number" min="0" name="beneficio_quantidade_exclusivo[]" class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerDesconto(this)">
                        Remover desconto
                    </button>
                `;
                container.appendChild(novoDesconto);

                // Gera código para o novo campo de desconto
                const novoCodigoInput = novoDesconto.querySelector(`input[name="${sufixo}[]"]:last-of-type`);
                gerarCodigoAleatorio(novoCodigoInput);
            }

            // Função para remover desconto
            function removerDesconto(button) {
                const descontoDiv = button.closest('.bg-white');
                descontoDiv.remove();
            }

            // Funções para preview e remoção de imagens
            function previewImagem(input, previewId) {
                const preview = document.getElementById(previewId);
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.classList.remove('hidden');
                        preview.querySelector('img').src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function previewGaleria(input) {
                const preview = document.getElementById('preview-galeria');
                preview.innerHTML = '';

                if (input.files) {
                    Array.from(input.files).forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const div = document.createElement('div');
                            div.className = 'relative group';
                            div.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="w-full h-40 object-cover rounded-lg shadow-md">
                                <button type="button" onclick="this.parentElement.remove()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            `;
                            preview.appendChild(div);
                        }
                        reader.readAsDataURL(file);
                    });
                }
            }

            function removerImagemPrincipal(button) {
                if (confirm('Tem certeza que deseja remover esta imagem?')) {
                    const container = button.closest('.relative');
                    container.remove();
                    document.querySelector('input[name="caminho_imagem_atual"]').value = '';
                }
            }

            function removerImagemGaleria(button, index) {
                if (confirm('Tem certeza que deseja remover esta imagem?')) {
                    const container = button.closest('.relative');
                    container.remove();

                    // Atualiza o campo hidden com as imagens restantes
                    const galeriaAtual = document.querySelector('input[name="galeria_imagens_atual"]');
                    if (galeriaAtual) {
                        const imagens = galeriaAtual.value.split(',');
                        imagens.splice(index, 1);
                        galeriaAtual.value = imagens.filter(img => img).join(',');
                    }
                }
            }

            // Funções para Roletas e Raspadinhas
            function adicionarItemRoleta() {
                const container = document.getElementById('itens_roleta_container');
                const novoItem = document.createElement('div');
                novoItem.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg shadow border';
                
                novoItem.innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Nome do Item
                            </label>
                            <input type="text" name="nome_item_roleta[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                placeholder="Ex: R$ 50,00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Status
                            </label>
                            <select name="status_item_roleta[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                <option value="disponivel">Disponível</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerItemRoleta(this)">
                        Remover Item
                    </button>
                `;
                container.appendChild(novoItem);
            }

            function removerItemRoleta(button) {
                const itemDiv = button.closest('.bg-white');
                itemDiv.remove();
            }

            function adicionarPacoteRoleta()
			{
                novoPacote.innerHTML = `
                    <div class="grid grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Valor do Pacote
                            </label>
                            <input type="number" step="0.01" name="valor_pacote_roleta[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Quantidade de Giros
                            </label>
                            <input type="number" name="quantidade_giros_roleta[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Código do Pacote
                            </label>
                            <input type="text" name="codigo_pacote_roleta[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Destaque
                            </label>
                            <select name="destaque_pacote_roleta[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                <option value="0">Normal</option>
                                <option value="1">Mais Popular</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerPacoteRoleta(this)">
                        Remover Pacote
                    </button>
                `;
                container.appendChild(novoPacote);

                const novoCodigoInput = novoPacote.querySelector('input[name="codigo_pacote_roleta[]"]');
                gerarCodigoAleatorio(novoCodigoInput);
            }

            function removerPacoteRoleta(button) {
                const pacoteDiv = button.closest('.bg-white');
                pacoteDiv.remove();
            }

            function adicionarItemRaspadinha() {
                const container = document.getElementById('itens_raspadinha_container');
                const novoItem = document.createElement('div');
                novoItem.className = 'bg-white dark:bg-[#27272A] p-4 rounded-lg shadow border';
                
                novoItem.innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Nome do Item
                            </label>
                            <input type="text" name="nome_item_raspadinha[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600"
                                placeholder="Ex: R$ 100,00">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                Status
                            </label>
                            <select name="status_item_raspadinha[]" 
                                class="w-full bg-gray-50 text-gray-800 dark:bg-[#3F3F46] dark:text-white p-2 rounded-md border border-gray-300 dark:border-gray-600">
                                <option value="disponivel">Disponível</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="mt-2 text-red-600 hover:text-red-800 text-sm" onclick="removerItemRaspadinha(this)">
                        Remover Item
                    </button>
                `;
                container.appendChild(novoItem);
            }

            function removerItemRaspadinha(button) {
                const itemDiv = button.closest('.bg-white');
                itemDiv.remove();
            }

            function valida()
            {
                const form = document.querySelector('form');

                // Coleta os dados dos pacotes promocionais
                const pacotesPromocionais = [];
                if (document.getElementById('habilitar_pacote_promocional') && document.getElementById('habilitar_pacote_promocional').checked) {
                    const containersPromocionais = document.querySelectorAll('#pacote_promocional #descontos-container > div');
                    
                    containersPromocionais.forEach(container => {
                        const valores = container.querySelectorAll('input[type="number"]');
                        const selectBeneficio = container.querySelector('select[name="beneficio_tipo_promocional[]"]');
                        const valorBilhete = parseFloat(valores[0]?.value || 0);
                        const quantidadeNumeros = parseInt(valores[1]?.value || 0);
                        const valorPacote = parseFloat(valores[2]?.value || 0);
                        const beneficioQtd = parseInt(container.querySelector('input[name="beneficio_quantidade_promocional[]"]')?.value || 0);
                        const beneficioTipo = selectBeneficio ? selectBeneficio.value : '';
                        if (!isNaN(valorBilhete) && !isNaN(quantidadeNumeros) && !isNaN(valorPacote)) {
                            pacotesPromocionais.push({
                                valor_bilhete: valorBilhete,
                                quantidade_numeros: quantidadeNumeros,
                                valor_pacote: valorPacote,
                                beneficio_tipo: beneficioTipo,
                                beneficio_quantidade: beneficioQtd
                            });
                        }
                    });
                }

                const pacotesExclusivos = [];
                if (document.getElementById('habilita_pacote_promocional_exclusivo') && document.getElementById('habilita_pacote_promocional_exclusivo').checked) {
                    const containersExclusivos = document.querySelectorAll('#pacote_exclusivo .space-y-4 > div');
                    
                    containersExclusivos.forEach(container => {
                        const valorBilhete = parseFloat(container.querySelector('input[name="valor_bilhete_exclusivo[]"]')?.value || 0);
                        const quantidadeNumeros = parseInt(container.querySelector('input[name="quantidade_desconto_exclusivo[]"]')?.value || 0);
                        const valorPacote = parseFloat(container.querySelector('input[name="valor_desconto_exclusivo[]"]')?.value || 0);
                        const codigoPacote = container.querySelector('input[name="codigo_desconto_exclusivo[]"]')?.value || '';
                        const beneficioTipo = (container.querySelector('select[name="beneficio_tipo_exclusivo[]"]') || {}).value || '';
                        const beneficioQtd = parseInt(container.querySelector('input[name="beneficio_quantidade_exclusivo[]"]')?.value || 0);
                        if (!isNaN(valorBilhete) && !isNaN(quantidadeNumeros) && !isNaN(valorPacote)) {
                            pacotesExclusivos.push({
                                valor_bilhete: valorBilhete,
                                quantidade_numeros: quantidadeNumeros,
                                valor_pacote: valorPacote,
                                codigo_pacote: codigoPacote,
                                beneficio_tipo: beneficioTipo,
                                beneficio_quantidade: beneficioQtd
                            });
                        }
                    });
                }

                const filtroPeriodo = document.getElementById('filtro_periodo_top_ganhadores');
                if (filtroPeriodo) {
                    let filtroData = {
                        filtro: 'hoje',
                        valor: ''
                    };

                    const opcaoSelecionada = filtroPeriodo.value;
                    try {
                        if (opcaoSelecionada.includes('personalizado')) {
                            const dataInicial = document.getElementById('data_inicial_personalizada').value;
                            const dataFinal = document.getElementById('data_final_personalizada').value;
                            if (dataInicial && dataFinal) {
                                filtroData = {
                                    filtro: 'personalizado',
                                    valor: `${dataInicial} até ${dataFinal}`
                                };
                            }
                        } else {
                            filtroData = JSON.parse(opcaoSelecionada);
                        }
                    } catch (e) {
                        console.error('Erro ao parsear filtro:', e);
                    }

                    // Remove campo hidden anterior se existir
                    const oldFiltroInput = form.querySelector('input[name="filtro_periodo_top_ganhadores"]');
                    if (oldFiltroInput) oldFiltroInput.remove();

                    // Adiciona campo hidden com o JSON do filtro
                    const filtroInput = document.createElement('input');
                    filtroInput.type = 'hidden';
                    filtroInput.name = 'filtro_periodo_top_ganhadores';
                    filtroInput.value = JSON.stringify(filtroData);
                    form.appendChild(filtroInput);
                }

                // Remove campos hidden anteriores se existirem
                const oldPromoInput = form.querySelector('input[name="pacote_promocional"]');
                if (oldPromoInput) oldPromoInput.remove();
                const oldExclInput = form.querySelector('input[name="pacotes_exclusivos"]');
                if (oldExclInput) oldExclInput.remove();

                // Adiciona campos hidden com os dados dos pacotes em JSON
                const pacotesPromocionaisInput = document.createElement('input');
                pacotesPromocionaisInput.type = 'hidden';
                pacotesPromocionaisInput.name = 'pacote_promocional';
                pacotesPromocionaisInput.value = JSON.stringify(pacotesPromocionais);
                form.appendChild(pacotesPromocionaisInput);

                const pacotesExclusivosInput = document.createElement('input');
                pacotesExclusivosInput.type = 'hidden';
                pacotesExclusivosInput.name = 'pacotes_exclusivos';
                pacotesExclusivosInput.value = JSON.stringify(pacotesExclusivos);
                form.appendChild(pacotesExclusivosInput);

                return true;
            }
        </script>

        <style>
            .toggle-switch {
                position: relative;
                display: inline-block;
                width: 45px;
                height: 25px;
                cursor: pointer;
            }

            .toggle-switch input {
                display: none;
            }

            .toggle-switch-background {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: #e2e8f0;
                border-radius: 25px;
                transition: background-color 0.3s ease;
            }

            .toggle-switch input:checked+.toggle-switch-background {
                background-color: #8b5cf6;
            }

            .toggle-switch-handle {
                position: absolute;
                top: 2px;
                left: 2px;
                width: 21px;
                height: 21px;
                background-color: white;
                border-radius: 50%;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                transition: transform 0.3s ease;
            }

            .toggle-switch input:checked+.toggle-switch-background .toggle-switch-handle {
                transform: translateX(20px);
            }
        </style>
    </body>

    </html>


    <script>
        function habilitaCampo(input, campo)
        {
            // Verifica o estado inicial do checkbox e esconde/mostra o campo
            if (!$('#'+input).is(':checked')) {
                $('#'+campo).hide();
            }

            $('#'+input).change(function()
            {
                if ($(this).is(':checked'))
                    $('#'+campo).show('');
                else 
                    $('#'+campo).hide('');
            });
        }

    </script>

    <script>
    $(document).ready(function() {
        // Validação do ranking
        $(document).on("click", "#habilitar_ranking", function() {
            if($(this).is(":checked")) {
                $("#div_quantidade_ranking").show("fast");
            } else {
                $("#div_quantidade_ranking").hide("slow");
            }
        });

        // Validação do top ganhadores
        $(document).on("click", "#selecionar_top_ganhadores", function() {
            if($(this).is(":checked")) {
                $("#div_filtro_periodo_top_ganhadores").show("fast");
                if($("#filtro_periodo_top_ganhadores").val().includes('personalizado')) {
                    $("#div_datas_personalizadas").show("fast");
                }
            } else {
                $("#div_filtro_periodo_top_ganhadores").hide("slow");
                $("#div_datas_personalizadas").hide("slow");
            }
        });

        // Validação do filtro personalizado
        $(document).on("change", "#filtro_periodo_top_ganhadores", function() {
            if($(this).val().includes('personalizado')) {
                $("#div_datas_personalizadas").show("fast");
            } else {
                $("#div_datas_personalizadas").hide("slow");
            }
        });

        // Validação das cotas em dobro
        $(document).on("click", "#habilitar_cotas_em_dobro", function() {
            if($(this).is(":checked")) {
                $("#campos_cotas_dobro").show("fast");
            } else {
                $("#campos_cotas_dobro").hide("slow");
            }
        });

        // Carregar valores iniciais
        if($("#habilitar_ranking").is(":checked")) {
            $("#div_quantidade_ranking").show();
        }

        if($("#selecionar_top_ganhadores").is(":checked")) {
            $("#div_filtro_periodo_top_ganhadores").show();
            if($("#filtro_periodo_top_ganhadores").val().includes('personalizado')) {
                $("#div_datas_personalizadas").show();
            }
        }

        if($("#habilitar_cotas_em_dobro").is(":checked")) {
            $("#campos_cotas_dobro").show();
        }
    });
    </script>