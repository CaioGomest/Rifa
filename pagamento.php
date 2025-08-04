<?php
require_once('header.php');
require_once('functions/functions_pedidos.php');
//ob_clean();
//error_reporting(0); // Evita que erros sejam impressos no buffer

// Recebe o ID do pedido
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if (!$order_id) 
{
  echo '<script>window.location.href = "index.php";</script>';
  exit;
}


$pedido = listaPedidos($conn, $order_id);


if (!$pedido[0]) {
  echo "<script>window.location.href = 'campanha.php?id=" . $pedido[0]['campanha_id'] . "';</script>";
  exit;
}

$campanhas = listaCampanhas($conn, $pedido[0]['campanha_id']);

// Formata o valor total
$valor_total = number_format($pedido[0]['valor_total'], 2, ',', '.');

// Tempo limite para pagamento (30 minutos)
date_default_timezone_set('America/Sao_Paulo');
$tempo_limite = date('c', strtotime('+' . $pedido[0]['expiracao_pedido'] . ' minutes', strtotime($pedido[0]['data_criacao'])));

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagamento - Rifa</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    class Contador {
      constructor(tempoLimite) {
        this.tempoLimite = new Date(tempoLimite);
        this.elementoContador = document.getElementById("contador");
        this.animationFrameId = null;

        if (!this.elementoContador) {
          console.error("Elemento contador não encontrado.");
          return;
        }
      }

      formatarTempo(tempo) {
        const minutos = Math.floor(tempo / 60000);
        const segundos = Math.floor((tempo % 60000) / 1000);
        return `${minutos.toString().padStart(2, '0')}:${segundos.toString().padStart(2, '0')}`;
      }

      atualizar() {
        const agora = new Date();
        const tempoRestante = this.tempoLimite.getTime() - agora.getTime();

        if (!this.elementoContador) return;

        if (tempoRestante <= 0) {
          // Obtém o elemento pai (o parágrafo <p>)
          const parentParagraph = this.elementoContador.parentElement;
          const status_pedido = document.querySelector('.status_pedido');
          if (parentParagraph) {
            // Altera o texto completo do parágrafo para uma mensagem mais apropriada
            parentParagraph.innerHTML = "CANCELADO";
            parentParagraph.classList.remove("text-red-500"); 
            parentParagraph.classList.add("text-red-500");
            
            status_pedido.innerHTML = "CANCELADO";
            status_pedido.classList.remove("text-red-500"); 
            status_pedido.classList.add("font-bold");
            status_pedido.style.backgroundColor = "red";
            status_pedido.style.padding = "2px";
            status_pedido.style.borderRadius = "5px";  // Corrigido

            status_pedido.classList.add("text-white");  
          }
          const btnCopiar = document.querySelector('button[onclick="copyPix()"]');
          if (btnCopiar) btnCopiar.disabled = true;

          this.parar();
          return;
        }

        this.elementoContador.innerHTML = this.formatarTempo(tempoRestante);
        this.animationFrameId = requestAnimationFrame(() => this.atualizar());
      }

      iniciar() {
        if (!this.elementoContador || this.animationFrameId) return;
        this.atualizar();
      }

      parar() {
        if (this.animationFrameId) {
          cancelAnimationFrame(this.animationFrameId);
          this.animationFrameId = null;
        }
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const contadorElemento = document.getElementById("contador");
      if (!contadorElemento) return;

      const contador = new Contador("<?php echo $tempo_limite; ?>");
      contador.iniciar();

      window.addEventListener('unload', () => contador.parar());
    });

    function copyPix() {
      const pixCode = document.getElementById('pix-code');
      if (!pixCode) return;

      pixCode.select();
      pixCode.setSelectionRange(0, 99999);
      document.execCommand("copy");
      alert("Código PIX copiado!");
    }
  </script>


 
<?php 

if ($config['habilitar_api_facebook'] == '1'): ?>
  <!-- Pixel do Facebook -->
  <script>
    !function(f,b,e,v,n,t,s)
    {
      if(f.fbq)return;
      n=f.fbq=function(){
        n.callMethod ?
        n.callMethod.apply(n,arguments) : n.queue.push(arguments)
      };
      if(!f._fbq)f._fbq=n;
      n.push=n;
      n.loaded=!0;
      n.version='2.0';
      n.queue=[];
      t=b.createElement(e);t.async=!0;
      t.src=v;
      s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)
    }(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');

    fbq('init', '<?php echo $config['pixel_facebook'];?>'); // ID do seu Pixel
    fbq('track', 'PixGerado');
  </script>

  <noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id=<?php echo $config['pixel_facebook'];?>&ev=PageView&noscript=1"/>
  </noscript>
  <!-- Fim do Pixel -->
<?php endif; ?>
</head>

<body class="bg-gray-100 text-black dark:bg-gray-900 dark:text-white transition-all">

  <main class="container mx-auto px-6 py-8">
    <section class="relative -top-12 w-full md:w-4/5 lg:w-3/5 mx-auto bg-white dark:bg-gray-800  rounded-lg shadow-lg p-6">
      <?php if ($pedido[0]['status'] == 1): ?>
        <h1 class="text-center text-xl font-bold mb-4">Pagamento Confirmado!</h1>
        <div class="bg-green-500 p-6 rounded-md mb-6 text-center">
          <svg class="w-12 h-12 mx-auto mb-2 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"></path>
          </svg>
          <p class="text-white text-lg font-semibold">Seu pagamento foi processado com sucesso!</p>
          <p class="text-white text-sm mt-2">Obrigado pela sua compra.</p>
        </div>

      
      <?php elseif ($pedido[0]['status'] == 2): ?>
        <h1 class="text-center text-xl font-bold mb-4">Pedido Cancelado</h1>
        <div class="bg-red-500 p-6 rounded-md mb-6 text-center">
          <svg class="w-12 h-12 mx-auto mb-2 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"></path>
          </svg>
          <p class="text-white text-lg font-semibold">Este pedido foi cancelado</p>
          <p class="text-white text-sm mt-2">Os números voltaram a ficar disponíveis</p>
        </div>
      <?php else: ?>
        <h1 class="text-center text-xl font-bold mb-4">Aguardando pagamento!</h1>
        <p class="text-center text-sm text-gray-400 mb-6">Finalize o pagamento via PIX</p>

        <div class="bg-gray-200 dark:bg-gray-700 p-4 rounded-md mb-6">
          <p class="text-center font-bold text-lg mb-2">
            Tempo restante para pagar: <span id="contador" class="text-red-500">30:00</span>
          </p>

          <?php
          $codigo_pix = gerarQRCodePIX($conn, $pedido[0]['id'], $pedido[0]['codigo_pix']);

          if (isset($codigo_pix) && $codigo_pix != ''): ?>
            <div class="flex justify-center mb-4">
              <img src="data:image/png;base64,<?= $codigo_pix ?>" />
            </div>
          <?php endif; ?>

          <p class="text-sm mb-2">1. Copie o código PIX abaixo:</p>
          <div class="flex items-center bg-gray-300 dark:bg-gray-600 p-3 rounded-md">
            <input id="pix-code" type="text" class="w-full bg-transparent text-black dark:text-white"
              value="<?php echo htmlspecialchars($pedido[0]['codigo_pix'] ?? ''); ?>" readonly>
            <button onclick="copyPix()"
              class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 dark:hover:bg-green-400 ml-2">
              Copiar
            </button>
          </div>
          <p class="text-sm mt-4">2. Abra o app do seu banco e escolha a opção PIX</p>
          <p class="text-sm mt-2">3. Cole a chave copiada e confirme o pagamento</p>
          <p class="text-xs text-gray-400 mt-4">
            Este pagamento só pode ser realizado dentro do tempo, caso o pagamento não for confirmado os números voltam a
            ficar disponíveis.
          </p>
        </div>
      <?php endif; ?>

      <div class="bg-gray-200 dark:bg-gray-700 p-4 rounded-md mb-6">
        <h2 class="text-lg font-bold mb-4">Detalhes da sua compra</h2>
        <div class="flex items-center mb-4">
          <?php if (!empty($campanhas[0]['caminho_imagem'])): ?>
            <img src="<?php echo $campanhas[0]['caminho_imagem']; ?>" alt="Imagem da Campanha"
              class="w-8 h-8 rounded-full object-cover">
          <?php else: ?>
            <div class="w-8 h-8 bg-gray-700 dark:bg-gray-200 rounded-full flex items-center justify-center">
              <span class="text-sm">👤</span>
            </div>
          <?php endif; ?>
          <div class="ml-4">
            <p class="font-bold"><?php echo htmlspecialchars($pedido[0]['campanha_nome']); ?></p>
            <p class="text-xs text-gray-400"><?php echo htmlspecialchars($pedido[0]['campanha_descricao']); ?></p>
          </div>
        </div>
        <div class="text-sm">
          <p><strong>Pedido:</strong> #<?php echo $pedido[0]['id']; ?></p>
          <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido[0]['cliente_nome']); ?></p>
          <p><strong>Telefone:</strong> <?php echo htmlspecialchars($pedido[0]['cliente_telefone']); ?></p>
          <p><strong>Realizado em:</strong> <?php echo date('d/m/Y \à\s H:i', strtotime($pedido[0]['data_criacao'])); ?>
          </p>
          <p><strong>Quantidade:</strong> <?php echo htmlspecialchars($pedido[0]['quantidade']); ?></p>
          <p><strong>Valor Total:</strong> R$ <?php echo $valor_total; ?></p>
          <p><strong>Status:</strong> <span class="status_pedido"><?php echo textoStatusPedido($pedido[0]); ?></span></p>
        </div>
      </div>

      <!-- Números Comprados -->
   <?php if ($pedido[0]['status'] == 1): ?>
          <div class="bg-gray-200 dark:bg-gray-700 p-4 rounded-md mb-6">
    <h2 class="text-lg font-bold mb-4">Seus Números</h2>
    <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-6 lg:grid-cols-6 gap-2">
      <?php
      $numeros = explode(',', $pedido[0]['numeros_pedido']);
      foreach ($numeros as $numero):
        if (!empty($numero)):
      ?>
        <div class="bg-gray-300 dark:bg-gray-600 p-2 text-center rounded text-sm">
          <?php echo $numero; ?>
        </div>
      <?php 
        endif;
      endforeach; 
      ?>
    </div>
  </div>
<?php endif; ?>

    </section>
  </main>

  <script>
      const tokenPedido = '<?php echo $pedido[0]['token_pedido']?>';

      function verificarStatus() {
          fetch(`verifica_status_pagamentos_unico.php?token=${tokenPedido}`)
              .then(response => response.text())
              .then(texto => {
                  //console.log("Resposta recebida:\n", texto);

                  // Checa se no texto da resposta contém 'Status atual: paid'
                  if (texto.includes("Status atual: paid")) {
                      //console.log("Pagamento confirmado. Recarregando página...");
                      location.reload(); // Recarrega a página
                  }
              })
              .catch(error => console.error('Erro ao verificar status:', error));
      }

      // Verifica assim que a página carregar
      verificarStatus();

      // Continua verificando a cada 10 segundos
      setInterval(verificarStatus, 10000);
  </script>

  <?php require_once('footer.php'); ?>

</body>

</html>