<?php
    require_once('conexao.php');
    require_once('gateway/mercadopago_pix.php');
    require_once('gateway/pay2m_pix.php');
    require_once('gateway/paggue_pix.php');
    require 'functions/functions_sistema.php';
    require 'functions/functions_usuarios.php';
    require 'functions/functions_clientes.php';
    require 'functions/functions_campanhas.php';
    require 'functions/functions_pedidos.php';
    require 'functions/functions_sorteio.php';

    function date_brazil($format, $timestamp = NULL)
    {
        $timestamp = ($timestamp ? $timestamp : 'now');
        $timestamp = (is_numeric($timestamp) ? date('Y-m-d H:i:s', $timestamp) : $timestamp);
        $date = new DateTime($timestamp);
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $date->setTimezone($timezone);
        return $date->format($format);
    }

    function redirect_erro_campanha($campanha_id, $mensagem)
    {
        if ($campanha_id) {
            header("Location: campanha.php?id=" . $campanha_id . "&alert_message=" . urlencode($mensagem) . "&alert_type=error");
        } else {
            header("Location: index.php?alert_message=" . urlencode($mensagem) . "&alert_type=error");
        }
        exit;
    }

    function friendly_error_message($rawMessage)
    {
        $raw = strtolower((string)$rawMessage);
        // Erros técnicos comuns de gateway/rede/bibliotecas
        $patternsTecnicos = [
            'curl', 'ssl', 'timeout', 'timed out', 'could not resolve host', 'unable to connect',
            'http', 'connection', 'dns', 'undefined function', 'fatal error', 'exception', 'stack trace'
        ];
        foreach ($patternsTecnicos as $p) {
            if (strpos($raw, $p) !== false) {
                return 'Não foi possível finalizar o pagamento agora. Tente novamente em alguns minutos.';
            }
        }

        // Mensagens já amigáveis lançadas pelo sistema podem ser exibidas
        $whitelistInicios = [
            'roleta não está', 'raspadinha não está', 'pacotes de roleta', 'pacotes de raspadinha',
            'código de pacote', 'valor do pacote', 'quantidade de giros', 'quantidade de raspadinhas',
            'números insuficientes', 'não há números disponíveis', 'dados incompletos',
            'dados inválidos', 'não foi possível localizar o cliente', 'não foi possível gerar o pix'
        ];
        foreach ($whitelistInicios as $start) {
            if (strpos($raw, $start) !== false) {
                return $rawMessage; // já está amigável
            }
        }

        return 'Não foi possível concluir sua compra agora. Tente novamente em instantes.';
    }

    // Funções de sorteio movidas para functions/functions_sorteio.php
    

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php");
        exit;
    }

    try {
        $campanha_id = isset($_POST['campanha_id']) ? intval($_POST['campanha_id']) : 0;
        $valor_total = isset($_POST['valor_total']) ? floatval($_POST['valor_total']) : 0;
        $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
        $codigo_afiliado = isset($_POST['codigo_afiliado']) ? $_POST['codigo_afiliado'] : NULL;
        $quantidade = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 0;
        $nome_produto = isset($_POST['nome_produto']) ? $_POST['nome_produto'] : '';
        $codigo_pacote_roleta = isset($_POST['codigo_pacote_roleta']) ? $_POST['codigo_pacote_roleta'] : NULL;
        $quantidade_giros_roleta = isset($_POST['quantidade_giros_roleta']) ? intval($_POST['quantidade_giros_roleta']) : 0;
        $codigo_pacote_raspadinha = isset($_POST['codigo_pacote_raspadinha']) ? $_POST['codigo_pacote_raspadinha'] : NULL;
        $quantidade_raspadinhas = isset($_POST['quantidade_raspadinhas']) ? intval($_POST['quantidade_raspadinhas']) : 0;
        $tipo_jogo = isset($_POST['tipo_jogo']) ? $_POST['tipo_jogo'] : NULL;
        $config = listaInformacoes($conn);

        if (!$campanha_id || !$valor_total || !$cliente_id)
            throw new Exception("Dados incompletos para processamento.");

        // Se for um jogo (roleta ou raspadinha), processar de forma diferente
        if ($tipo_jogo && in_array($tipo_jogo, ['roleta', 'raspadinha'])) {
        $campanha = listaCampanhas($conn, $campanha_id);
        
        // Validar se o jogo está habilitado
        if ($tipo_jogo === 'roleta' && $campanha[0]['habilitar_roleta'] != '1') {
                throw new Exception("Roleta não está habilitada para esta campanha.");
        }
        if ($tipo_jogo === 'raspadinha' && $campanha[0]['habilitar_raspadinha'] != '1') {
                throw new Exception("Raspadinha não está habilitada para esta campanha.");
        }
        
        // Criar pedido para o jogo
        $token_pedido = bin2hex(random_bytes(16));
        $data_criacao = date_brazil('Y-m-d H:i:s');
        $data_atualizacao = $data_criacao;
        $expiration_minutes = 30;
        
        $pedido_id = criarPedido($conn, $cliente_id, $campanha_id, null, 1, $valor_total, 0, $data_criacao, $data_criacao, $nome_produto, $token_pedido, '', null, $expiration_minutes, null, null, null, null, null, $tipo_jogo);

        // Salvar somente contadores dos jogos no pedido (sem itens)
        $campanha_itens = listaCampanhas($conn, $campanha_id);
        $campData = $campanha_itens[0] ?? [];
        $metaJogos = [ 'jogos' => [] ];
        if (!empty($campData) && $campData['habilitar_roleta'] == '1') {
            $qtdGiros = $quantidade_giros_roleta > 0 ? $quantidade_giros_roleta : 0;
            $metaJogos['jogos']['roleta'] = [
                'giros_comprados' => $qtdGiros,
                'giros_restantes' => $qtdGiros,
            ];
        }
        if (!empty($campData) && $campData['habilitar_raspadinha'] == '1') {
            $qtdRasp = $quantidade_raspadinhas > 0 ? $quantidade_raspadinhas : 0;
            $metaJogos['jogos']['raspadinha'] = [
                'cartelas_compradas' => $qtdRasp,
                'cartelas_restantes' => $qtdRasp,
            ];
        }
        if (!empty($metaJogos['jogos'])) {
            salvarJogosDoPedido($conn, $pedido_id, $metaJogos);
        }
        
            // Redirecionar para pagamento (somente se gateway OK); caso contrário, voltar com alerta
            $cliente = listaClientes($conn, $cliente_id);
            if (!$cliente) throw new Exception("Não foi possível localizar o cliente.");
            $cliente_nome = $cliente[0]['nome'];
            $cliente_email = $cliente[0]['email'];

            $gerou = false;
            if (($config['habilitar_mercadopago'] == 1) && $valor_total > 0 && $config['mercadopago_token_acesso'] != '') {
                $mercado_pago = mercadopago_generate_pix($conn, $pedido_id, $valor_total, $cliente_nome, $cliente_email, $expiration_minutes, $config);
                if (is_array($mercado_pago) && isset($mercado_pago['order_id'])) {
                    $parametro = "order_id=" . $mercado_pago['order_id'];
                    echo '<script>window.location.href = "pagamento.php?' . $parametro . '";</script>';
                    exit;
                }
            }

            // Se não conseguir gerar PIX, voltar para a campanha com alerta
            redirect_erro_campanha($campanha_id, 'Não foi possível gerar o PIX. Tente novamente em alguns instantes.');
        }

        // Validações por pacotes de jogos agora são centralizadas nos pacotes da campanha (removido uso específico)

        $token_pedido = bin2hex(random_bytes(16));
        $data_criacao = date_brazil('Y-m-d H:i:s');
        $data_atualizacao = $data_criacao;
        $campanha = listaCampanhas($conn, $campanha_id);

        if($campanha[0]['habilitar_cotas_em_dobro'])
            $quantidade = $quantidade * 2;

        $numeros_disponiveis = obterNumerosDisponiveis($conn, $campanha_id);

        if (empty($numeros_disponiveis)) {
            throw new Exception("Não há números disponíveis para esta campanha.");
        }

        // Seleciona os números com base nas cotas premiadas
        $numeros_selecionados = obter_numeros_com_premiadas(
            $quantidade,
            $numeros_disponiveis,
            $campanha[0]['cotas_premiadas'] ?? '',
            $campanha[0]['status_cotas_premiadas'] ?? ''
        );

        // Se não houver números suficientes para completar o pacote, volta com alerta
        if (count($numeros_selecionados) < $quantidade) {
            redirect_erro_campanha($campanha_id, 'Números insuficientes para o pacote selecionado.');
        }

    $numeros_pedido = implode(',', $numeros_selecionados);

        if (empty($quantidade) || empty($valor_total)) {
            redirect_erro_campanha($campanha_id, 'Dados inválidos para pagamento.');
        }

    $afiliado_id = null;
    $porcentagem_comissao = 0;
    if (isset($_POST['codigo_afiliado']) && !empty($_POST['codigo_afiliado'])) {
        $query = "SELECT usuario_id, porcentagem_comissao 
                FROM configuracoes_afiliados 
                WHERE codigo_afiliado = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $_POST['codigo_afiliado']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $afiliado = $result->fetch_assoc();
            $afiliado_id = $afiliado['usuario_id'];
            $porcentagem_comissao = $afiliado['porcentagem_comissao'];
        }
    }

        $cliente = listaClientes($conn, $cliente_id);
        if (!$cliente)
            throw new Exception('Não foi possível localizar o cliente.');

    $cliente_nome = $cliente[0]['nome'];
    $cliente_email = $cliente[0]['email'];
    $expiration_minutes = 30;

    $pedido_id = criarPedido($conn, $cliente_id, $campanha_id, $afiliado_id, $quantidade, $valor_total, 0, $data_criacao, $data_criacao, $nome_produto, $token_pedido, $numeros_pedido, Null, $expiration_minutes, Null, Null, Null, $codigo_pacote_roleta, $quantidade_giros_roleta, $codigo_pacote_raspadinha, $quantidade_raspadinhas);

    // Salvar apenas contadores de jogos no pedido (sem itens)
    $metaJogos = [ 'jogos' => [] ];
    if ($campanha[0]['habilitar_roleta'] == '1') {
        $qtdGiros = $quantidade_giros_roleta > 0 ? $quantidade_giros_roleta : 0;
        $metaJogos['jogos']['roleta'] = [
            'giros_comprados' => $qtdGiros,
            'giros_restantes' => $qtdGiros,
        ];
    }
    if ($campanha[0]['habilitar_raspadinha'] == '1') {
        $qtdRasp = $quantidade_raspadinhas > 0 ? $quantidade_raspadinhas : 0;
        $metaJogos['jogos']['raspadinha'] = [
            'cartelas_compradas' => $qtdRasp,
            'cartelas_restantes' => $qtdRasp,
        ];
    }
    if (!empty($metaJogos['jogos'])) {
        salvarJogosDoPedido($conn, $pedido_id, $metaJogos);
    }

        // Pagamento via MercadoPago, Pay2M, Paggue
        $gateway_ok = false;
        if (($config['habilitar_mercadopago'] == 1) && $valor_total > 0 && $config['mercadopago_token_acesso'] != '') {
            $mercado_pago = mercadopago_generate_pix($conn, $pedido_id, $valor_total, $cliente_nome, $cliente_email, $expiration_minutes, $config);
            if (is_array($mercado_pago) && isset($mercado_pago['order_id'])) {
                $parametro = "order_id=" . $mercado_pago['order_id'];
                echo '<script>window.location.href = "pagamento.php?' . $parametro . '";</script>';
                exit;
            }
        }
        if (($config['habilitar_pay2m'] == 1) && $valor_total > 0 && ($config['pay2m_client_key'] != '' || $config['pay2m_client_secret'] != '')) {
            $pay2m = pay2m_generate_pix($conn, $pedido_id, $valor_total, $cliente_nome, $cliente_email, $expiration_minutes, $config);
            if (is_array($pay2m) && isset($pay2m['order_id'])) {
                $parametro = "order_id=" . $pay2m['order_id'];
                header("Location: pagamento.php?" . $parametro);
                exit;
            }
        }
        if (($config['habilitar_paggue'] == 1 && $config['paggue_client_key'] != '' && $config['paggue_client_secret'] != '') && $valor_total > 0) {
            $paggue = paggue_generate_pix($conn, $pedido_id, $valor_total, $cliente_nome, $cliente_email, $expiration_minutes, $config);
            if (is_array($paggue) && isset($paggue['id_pedido'])) {
                $parametro = "order_id=" . $paggue['id_pedido'];
                header("Location: pagamento.php?" . $parametro);
                exit;
            }
        }

        // Nenhum gateway conseguiu gerar PIX
        redirect_erro_campanha($campanha_id, 'Não foi possível gerar o PIX. Tente novamente em alguns instantes.');

    } catch (Throwable $e) {
        error_log('[processamento_pagamento] ' . $e->getMessage());
        $mensagemAmigavel = friendly_error_message($e->getMessage());
        redirect_erro_campanha(isset($campanha_id) ? $campanha_id : 0, $mensagemAmigavel);
    }

?>
