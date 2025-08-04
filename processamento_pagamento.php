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
    // set_time_limit(300);
    function date_brazil($format, $timestamp = NULL)
    {
        $timestamp = ($timestamp ? $timestamp : 'now');
        $timestamp = (is_numeric($timestamp) ? date('Y-m-d H:i:s', $timestamp) : $timestamp);
        $date = new DateTime($timestamp);
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $date->setTimezone($timezone);
        return $date->format($format);
    }
    function selecionar_numeros_disponiveis($quantidade, $numeros_disponiveis){
        
     
      
        $numeros_selecionados = []; 
        for ($i = 0; $i < $quantidade; $i++) 
        {
            if (empty($numeros_disponiveis)) break;

            $indice = array_rand($numeros_disponiveis);
            $numeros_selecionados[] = $numeros_disponiveis[$indice];
            unset($numeros_disponiveis[$indice]);
        }
        return $numeros_selecionados;
    }


    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    {
        header("Location: index.php");
        exit;
    }

    $campanha_id = isset($_POST['campanha_id']) ? intval($_POST['campanha_id']) : 0;
    $valor_total = isset($_POST['valor_total']) ? floatval($_POST['valor_total']) : 0;
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
    $codigo_afiliado = isset($_POST['codigo_afiliado']) ? $_POST['codigo_afiliado'] : NULL;
    $quantidade = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 0;
    $nome_produto = isset($_POST['nome_produto']) ? $_POST['nome_produto'] : '';
    $config = listaInformacoes($conn);

    if (!$campanha_id || !$valor_total || !$cliente_id)
        throw new Exception("Dados incompletos para processamento");

    $token_pedido = bin2hex(random_bytes(16));
    $data_criacao = date_brazil('Y-m-d H:i:s');
    $data_atualizacao = $data_criacao;
    $campanha = listaCampanhas($conn, $campanha_id);

    if($campanha[0]['habilitar_cotas_em_dobro'])
    $quantidade = $quantidade * 2;

    $lista_cotas_premiadas = explode(',', $campanha[0]['cotas_premiadas']);
    $numeros_disponiveis = obterNumerosDisponiveis($conn, $campanha_id);

    // if (empty($numeros_disponiveis)) {
    //     throw new Exception("Não há números disponíveis para esta campanha.");
    // }

    $numeros_selecionados = [];


    if ($campanha[0]['status_cotas_premiadas'] == 'imediato')
    {
        $numeros_selecionados = selecionar_numeros_disponiveis($quantidade, $numeros_disponiveis);
        if (!empty($lista_cotas_premiadas)) 
        {
            array_pop($numeros_selecionados);
            
            $index = array_rand($lista_cotas_premiadas);
            $numero_premiado = $lista_cotas_premiadas[$index];

            $numeros_selecionados[] = $numero_premiado;
        }
    }
    elseif ($campanha[0]['status_cotas_premiadas'] == 'bloqueado')
    {
        $reais_numeros_disponiveis = array_values(array_diff($numeros_disponiveis, $lista_cotas_premiadas));
        $numeros_selecionados = selecionar_numeros_disponiveis($quantidade, $reais_numeros_disponiveis);
    }
    elseif ($campanha[0]['status_cotas_premiadas'] == 'disponivel')
{
    $numeros_selecionados = selecionar_numeros_disponiveis($quantidade, $numeros_disponiveis);

    // Chance aleatória de incluir uma cota premiada
    if (!empty($lista_cotas_premiadas) && rand(0, 1) === 1) {
        // Substitui um número aleatório por uma cota premiada
        $index_substituir = array_rand($numeros_selecionados);
        $index_premiada = array_rand($lista_cotas_premiadas);
        $numeros_selecionados[$index_substituir] = $lista_cotas_premiadas[$index_premiada];
    }
}

    $numeros_pedido = implode(',', $numeros_selecionados);
     
  


    // if(empty($numeros_pedido) || $numeros_pedido > $quantidade)//tem mais numeros selecionados que a quantidade disponivel, seja por cotas premiadas estarem bloqueadas ou por quantidade de numeros disponiveis mesmo, entao ai tem que recontar o valor total
    // {
    //     $quantidade = count($numeros_selecionados);
    //     $valor_total = $quantidade * $campanha[0]['preco'];
    // }

    if(empty( $quantidade) || empty($valor_total))
    {
        header("Location: campanha.php?id=".$campanha_id."&error=Sem números disponíveis");
        exit;
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
        echo "Erro ao criar email";


    $cliente_nome = $cliente[0]['nome'];
    $cliente_email = $cliente[0]['email'];
    $expiration_minutes = 30;

    $pedido_id = criarPedido($conn, $cliente_id, $campanha_id, $afiliado_id, $quantidade, $valor_total, 0, $data_criacao, $data_criacao, $nome_produto, $token_pedido, $numeros_pedido, Null, $expiration_minutes, Null, Null, Null);


    if (($config['habilitar_mercadopago'] == 1) && $valor_total > 0 && $config['mercadopago_token_acesso'] != '')
    {
        $mercado_pago = mercadopago_generate_pix($conn, $pedido_id, $valor_total, $cliente_nome, $cliente_email, $expiration_minutes, $config);
        
        if ($mercado_pago)
        {
            $parametro = "order_id=" . $mercado_pago['order_id'];
            echo '<script>window.location.href = "pagamento.php' . $parametro . '";</script>';

            exit;
        } else 
            echo "Erro ao gerar o código PIX";
    }
    elseif (($config['habilitar_pay2m'] == 1) && $valor_total > 0 && ($config['pay2m_client_key'] != '' || $config['pay2m_client_secret'] != ''))
    {
        $expiration_minutes = 30;
        $pay2m = pay2m_generate_pix($conn, $pedido_id, $valor_total, $cliente_nome, $cliente_email, $expiration_minutes, $config);

        if ($pay2m)
        {
            $parametro = "order_id=" . $pay2m['order_id'];
            header("Location: pagamento.php?" . $parametro);
            exit;
        } else
            echo "Erro ao gerar o código PIX";
    }
    elseif (($config['habilitar_paggue'] == 1 && $config['paggue_client_key'] != '' && $config['paggue_client_secret'] != '') && $valor_total > 0)
    {
        $paggue = paggue_generate_pix($conn, $pedido_id, $valor_total, $cliente_nome, $cliente_email, $expiration_minutes, $config);

        if ($paggue)
        {
            $parametro = "order_id=" . $paggue['id_pedido'];
            header("Location: pagamento.php?" . $parametro);
            exit;
        } else
            echo "Erro ao gerar o código PIX";
    }


