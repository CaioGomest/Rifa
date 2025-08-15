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

// Processar cancelamento se solicitado
if (isset($_POST['cancelar_pedido']) && $_POST['cancelar_pedido'] == $order_id) {
    if (cancelarPedido($conn, $order_id)) {
        echo '<script>alert("Pedido cancelado com sucesso!"); window.location.href = "meus_titulos.php";</script>';
        exit;
    } else {
        echo '<script>alert("Erro ao cancelar pedido!");</script>';
    }
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
  <style>
    .status-cancelado {
      background-color: #dc2626 !important;
      color: white !important;
      padding: 2px 8px !important;
      border-radius: 4px !important;
      font-weight: bold !important;
      display: inline-block !important;
    }
    
      .status-pago {
        background-color: #059669 !important;
        color: white !important;
        padding: 2px 8px !important;
        border-radius: 4px !important;
        font-weight: bold !important;
      }
      .status-pendente {
        background-color: #f59e0b !important;
        color: white !important;
        padding: 2px 8px !important;
        border-radius: 4px !important;
        font-weight: bold !important;
      }       
      /* ===== Roleta - UI Melhorada ===== */
      .roleta-wrapper { position: relative; width: 420px; height: 420px; margin: 0 auto; }
      .roleta-canvas { width: 420px; height: 420px; border-radius: 9999px; transition: transform 0.3s ease-out; box-shadow: 0 20px 40px rgba(76, 29, 149, 0.35), inset 0 0 0 10px rgba(255,255,255,0.08); background: radial-gradient(ellipse at center, rgba(255,255,255,0.07), rgba(0,0,0,0.05)); }
      .roleta-canvas.win-flash { animation: winFlash .55s ease-in-out 3; }
      @keyframes winFlash {
        0%, 100% { box-shadow: 0 20px 40px rgba(76, 29, 149, 0.35), inset 0 0 0 10px rgba(255,255,255,0.08); }
        50% { box-shadow: 0 0 0 6px rgba(34,197,94,0.95), 0 0 40px 12px rgba(34,197,94,0.6); }
      }
      .roleta-counter { position: absolute; top: -18px; left: 50%; transform: translateX(-50%); font-size: 12px; color: #cbd5e1; }
      .roleta-pointer { position: absolute; top: -8px; left: 50%; transform: translateX(-50%) rotate(180deg); width: 0; height: 0; border-left: 12px solid transparent; border-right: 12px solid transparent; border-bottom: 22px solid #f59e0b; filter: drop-shadow(0 6px 6px rgba(0,0,0,0.45)); }
      .roleta-center-cap { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; }
      .roleta-center-cap::after { content: ""; width: 84px; height: 84px; border-radius: 9999px; background: radial-gradient(circle at 35% 35%, #ffffff, #e9d5ff 45%, #7c3aed 70%); box-shadow: 0 8px 18px rgba(124,58,237,0.45), inset 0 0 0 6px rgba(255,255,255,0.45); }
      @media (max-width: 1024px) {
        .roleta-wrapper { width: 360px; height: 360px; }
        .roleta-canvas { width: 360px; height: 360px; }
      }
      @media (max-width: 420px) {
        .roleta-wrapper { width: 300px; height: 300px; }
        .roleta-canvas { width: 300px; height: 300px; }
      }

      /* ===== Box de Prêmios vibrante ===== */
      .premios-box { background: linear-gradient(135deg, #22c55e 0%, #16a34a 30%, #7c3aed 100%); box-shadow: 0 10px 30px rgba(34,197,94,0.35); }
      .premios-bg { background: radial-gradient(ellipse at 20% -10%, rgba(255,255,255,0.35), transparent 40%), radial-gradient(ellipse at 120% 10%, rgba(255,255,255,0.2), transparent 40%); filter: blur(1px); }
      .premios-content { color: #fff; }
      .premio-chip { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.15); border: 2px solid rgba(255,255,255,0.35); color: #fff; padding: 10px 14px; border-radius: 12px; transition: transform .15s ease, background .15s ease, box-shadow .15s ease; box-shadow: 0 4px 14px rgba(0,0,0,0.15); }
      .premio-chip:hover { transform: translateY(-2px) scale(1.01); background: rgba(255,255,255,0.22); box-shadow: 0 8px 20px rgba(0,0,0,0.25); }

      /* ===== Modal do Prêmio ===== */
      .modal-premio-card { width: 100%; max-width: 640px; border-radius: 16px; overflow: hidden; box-shadow: 0 30px 80px rgba(0,0,0,0.55); background: radial-gradient(circle at 10% -10%, rgba(124,58,237,.25), transparent 35%), radial-gradient(circle at 110% 0%, rgba(34,197,94,.25), transparent 35%), #1f2937; border: 2px solid rgba(124,58,237,0.45); }
      .modal-premio-header { background: linear-gradient(135deg, rgba(124,58,237,.45), rgba(34,197,94,.45)); padding: 18px; text-align: center; color: #fff; }
      .modal-premio-title { font-size: 28px; font-weight: 800; letter-spacing: .3px; text-shadow: 0 2px 8px rgba(0,0,0,.3); }
      .modal-premio-body { padding: 22px; text-align: center; color: #e5e7eb; }
      .modal-premio-actions { display: flex; gap: 12px; justify-content: center; padding: 0 22px 22px; }
      .btn-whats { background: #22c55e; color: #fff; font-weight: 800; padding: 12px 18px; border-radius: 12px; box-shadow: 0 12px 24px rgba(34,197,94,.35); transition: transform .15s ease, box-shadow .15s ease; }
      .btn-whats:hover { transform: translateY(-2px); box-shadow: 0 18px 32px rgba(34,197,94,.45); }
      .btn-ghost { background: #6b7280; color: #fff; font-weight: 700; padding: 12px 18px; border-radius: 12px; }

      /* (removido confete para evitar pontos aleatórios) */

      /* ===== Raspadinha - UI ===== */
      .scratcher-wrap { width: 380px; max-width: 90vw; margin: 0 auto; }
      .scratcher-canvas { width: 100%; height: auto; border-radius: 14px; display: block; margin: 0 auto; box-shadow: 0 12px 32px rgba(0,0,0,0.35), inset 0 0 0 1px rgba(255,255,255,0.25); }
      @media (max-width: 420px) { .scratcher-wrap { width: 320px; } }
  </style>

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
          if (parentParagraph) {
            // Altera o texto completo do parágrafo para uma mensagem mais apropriada
            parentParagraph.innerHTML = "TEMPO EXPIRADO";
            parentParagraph.classList.remove("text-red-500"); 
            parentParagraph.classList.add("text-red-500");
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

    function confirmarCancelamento() {
      if (confirm('Tem certeza que deseja cancelar este pedido? Esta ação não pode ser desfeita.')) {
        document.getElementById('form_cancelar').submit();
      }
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

<body class="bg-gray-100 text-black dark:bg-[#18181B] dark:text-white transition-all">

  <main class="container mx-auto px-6 py-8">
    <section class="relative -top-12 w-full md:w-4/5 lg:w-3/5 mx-auto bg-white dark:bg-[#27272A]  rounded-lg shadow-lg p-6">
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
        <h1 class="text-center text-xl font-bold mb-4">Pedido Cancelado!</h1>
        <div class="bg-red-500 p-6 rounded-md mb-6 text-center">
          <svg class="w-12 h-12 mx-auto mb-2 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"></path>
          </svg>
          <p class="text-white text-lg font-semibold">Este pedido foi cancelado!</p>
          <p class="text-white text-sm mt-2">Os números voltaram a ficar disponíveis.</p>
        </div>
      <?php else: ?>
        <h1 class="text-center text-xl font-bold mb-4">Aguardando pagamento!</h1>
        <p class="text-center text-sm text-gray-400 mb-6">Finalize o pagamento via PIX</p>

        <div class="bg-gray-200 dark:bg-[#3F3F46] p-4 rounded-md mb-6">
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

        <!-- Botão de Cancelamento para Pedidos Pendentes -->
        <div class="text-center mb-6">
          <form id="form_cancelar" method="POST" style="display: inline;">
            <input type="hidden" name="cancelar_pedido" value="<?php echo $order_id; ?>">
            <button type="button" onclick="confirmarCancelamento()" 
                    class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg transition-colors">
              <i class="fas fa-times mr-2"></i>Cancelar Pedido
            </button>
          </form>
        </div>
      <?php endif; ?>

      <div class="bg-gray-200 dark:bg-[#3F3F46] p-4 rounded-md mb-6">
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
          <p><strong>Status:</strong> 
            <span id="status_pedido" class="status_pedido <?php 
              if ($pedido[0]['status'] == 2) echo 'status-cancelado';
              elseif ($pedido[0]['status'] == 1) echo 'status-pago';
              elseif ($pedido[0]['status'] == 0) echo 'status-pendente';
            ?>">
              <?php echo textoStatusPedido($pedido[0]); ?>
            </span>
          </p>
        </div>
      </div>

      <?php if ($pedido[0]['status'] == 1): ?>
        <?php 
          $dadosJogos = obterJogosDoPedido($conn, $pedido[0]['id']);
          $giros_disponiveis = $dadosJogos['jogos']['roleta']['giros_restantes'] ?? 0;
          $giros_comprados = $dadosJogos['jogos']['roleta']['giros_comprados'] ?? 0;
          $raspadinhas_disponiveis = $dadosJogos['jogos']['raspadinha']['cartelas_restantes'] ?? 0;
          $raspadinhas_compradas = $dadosJogos['jogos']['raspadinha']['cartelas_compradas'] ?? 0;

          // var_dump($meta);
        ?>
        <?php if ($giros_disponiveis > 0 || $raspadinhas_disponiveis > 0): ?>
          <div class="bg-gray-200 dark:bg-[#3F3F46] p-4 rounded-md mb-6">
            <h2 class="text-lg font-bold mb-4">Jogos disponíveis</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <?php if ($giros_disponiveis > 0): ?>
                <div class="flex items-center justify-between bg-white/50 dark:bg-black/20 rounded-md p-4">
                  <div>
                    <p class="font-semibold">🎰 Roleta da Sorte</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Giros: <strong id="spanGirosDisponiveis"><?php echo $giros_disponiveis; ?>/<?php echo $giros_comprados; ?></strong></p>
                  </div>
                  <button onclick="iniciarRoleta()" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition">Abrir</button>
                </div>
              <?php endif; ?>

              <?php if ($raspadinhas_disponiveis > 0): ?>
                <div class="flex items-center justify-between bg-white/50 dark:bg-black/20 rounded-md p-4">
                  <div>
                    <p class="font-semibold">🎫 Raspadinha da Sorte</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Raspadinhas: <strong id="spanRaspadinhasDisponiveis"><?php echo $raspadinhas_disponiveis; ?>/<?php echo $raspadinhas_compradas; ?></strong></p>
                  </div>
                  <button onclick="iniciarRaspadinha()" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition">Abrir</button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php 
          $premios_roleta = $dadosJogos['jogos']['roleta']['premios'] ?? [];
          $premios_rasp = $dadosJogos['jogos']['raspadinha']['premios'] ?? [];
          $todosPremios = array_merge($premios_roleta, $premios_rasp);
        ?>
        <?php if (!empty($todosPremios)): ?>
          <div class="premios-box relative rounded-xl mb-6 overflow-hidden">
            <div class="premios-bg absolute inset-0"></div>
            <div class="premios-content relative p-4">
              <h2 class="text-lg font-bold mb-1">🏆 Prêmios conquistados <span class="align-middle">🎉</span></h2>
              <p class="text-sm text-gray-100/80 dark:text-white/80 mb-3">Clique para ver detalhes e retirar seu prêmio.</p>
              <div id="listaPremios" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <?php foreach ($todosPremios as $p): $nomeP = is_array($p) ? ($p['nome'] ?? '') : (string)$p; if(!$nomeP) continue; ?>
                  <button type="button" class="premio-chip" onclick="abrirModalPremio('<?php echo htmlspecialchars($nomeP); ?>')">
                    <span class="text-xl">🏆</span>
                    <span class="font-semibold"><?php echo htmlspecialchars($nomeP); ?></span>
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Números Comprados -->
   <?php if ($pedido[0]['status'] == 1): ?>
          <div class="bg-gray-200 dark:bg-[#3F3F46] p-4 rounded-md mb-6">
    <h2 class="text-lg font-bold mb-4">Seus Números</h2>
    <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-6 lg:grid-cols-6 gap-2">
      <?php
      require_once('functions/functions_sistema.php');
      $largura_cota = obterLarguraCotaPorCampanha($conn, $pedido[0]['campanha_id']);
      $numeros = explode(',', $pedido[0]['numeros_pedido']);
      foreach ($numeros as $numero):
        if (!empty($numero)):
          $numero_fmt = formatarCotaComLargura($numero, $largura_cota);
      ?>
        <div class="bg-gray-300 dark:bg-gray-600 p-2 text-center rounded text-sm">
          <?php echo $numero_fmt; ?>
        </div>
      <?php 
        endif;
      endforeach; 
      ?>
    </div>
  </div>
          
<?php endif; ?>

      <!-- Seção dos Jogos da Sorte (apenas para pedidos pagos) -->
      <?php if ($pedido[0]['status'] == 1): ?>
        <?php 
        $giros_disponiveis = $pedido[0]['quantidade_giros_roleta'] ?? 0;
        $raspadinhas_disponiveis = $pedido[0]['quantidade_raspadinhas'] ?? 0;
        ?>
        
        <?php if ($giros_disponiveis > 0): ?>
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-6 rounded-lg text-white text-center mb-6">
          <h2 class="text-2xl font-bold mb-4">🎰 Roleta da Sorte</h2>
          <p class="mb-4">Você tem <strong><?php echo $giros_disponiveis; ?> giros</strong> disponíveis!</p>
          <button onclick="iniciarRoleta()" class="bg-white text-purple-600 px-8 py-3 rounded-lg font-bold text-lg hover:bg-purple-100 transition">
            🎰 Girar Roleta
          </button>
        </div>
        <?php endif; ?>

        <?php if ($raspadinhas_disponiveis > 0): ?>
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-6 rounded-lg text-white text-center mb-6">
          <h2 class="text-2xl font-bold mb-2">🎫 Raspadinha da Sorte</h2>
          <p id="contadorRaspadinhaTitulo" class="text-sm font-semibold mb-2"><?php echo intval($raspadinhas_disponiveis); ?>/<?php echo isset($raspadinhas_compradas) ? intval($raspadinhas_compradas) : intval($raspadinhas_disponiveis); ?></p>
          <p class="mb-4">Você tem <strong><?php echo $raspadinhas_disponiveis; ?> raspadinhas</strong> disponíveis!</p>
          <button onclick="iniciarRaspadinha()" class="bg-white text-yellow-600 px-8 py-3 rounded-lg font-bold text-lg hover:bg-yellow-100 transition">
            🎫 Raspar Cartela
          </button>
        </div>
      <?php endif; ?>

        <!-- Modal da Roleta -->
        <div id="modalRoleta" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
          <div class="bg-white dark:bg-[#27272A] p-6 md:p-8 rounded-lg w-full max-w-5xl mx-4 text-center">
            <h3 class="text-2xl font-bold mb-2">🎰 Roleta da Sorte</h3>
            <div class="block mb-4">
              <p class="text-sm text-gray-500 dark:text-gray-300">Gire e descubra seu prêmio!</p>
              <p id="contadorRoleta" class="inline-block text-md px-2 py-1 rounded font-bold hidden">Giros: 0/0</span>
            </div>
            <div id="roletaContainer" class="mb-4 md:mb-6">
              <div class="roleta-wrapper">
                <div class="roleta-pointer"></div>
                <canvas id="roletaCanvas" width="420" height="420" class="roleta-canvas"></canvas>
                <div class="roleta-center-cap"></div>
              </div>
            </div>
            <div id="resultadoRoleta" class="hidden mb-3 text-sm font-semibold"></div>
            <button id="btnGirarRoleta" onclick="girarRoleta()" class="bg-purple-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-purple-700 transition">
              Girar Roleta
            </button>
            <button onclick="fecharModalRoleta()" class="bg-gray-500 text-white px-6 py-3 rounded-lg font-bold hover:bg-gray-600 transition ml-2">
              Fechar
            </button>
          </div>
        </div>

        <!-- Modal de Detalhes do Prêmio -->
        <div id="modalPremio" class="fixed inset-0 bg-black bg-opacity-60 hidden z-50 flex items-center justify-center">
          <div class="modal-premio-card relative">
            <div class="modal-premio-header">
              <div class="modal-premio-title">🎉 Parabéns! 🎉</div>
            </div>
            <div class="modal-premio-body">
              <p id="textoPremio" class="text-xl leading-relaxed">Você ganhou!</p>
              <p class="mt-2 text-sm opacity-80">Resgate agora mesmo seu prêmio pelo WhatsApp e combine a retirada. 🏆</p>
            </div>
            <div class="modal-premio-actions">
              <a id="linkRetirarPremio" href="#" target="_blank" class="btn-whats">Retirar prêmio no WhatsApp</a>
              <button onclick="fecharModalPremio()" class="btn-ghost">Fechar</button>
            </div>
          </div>
        </div>

        <!-- Modal da Raspadinha -->
        <div id="modalRaspadinha" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
          <div class="bg-white dark:bg-[#27272A] p-8 rounded-lg max-w-md w-full mx-4 text-center">
            <h3 class="text-2xl font-bold mb-2">🎫 Raspadinha da Sorte</h3>
            <p id="contadorRaspadinha" class="inline-block text-md px-2 py-1 rounded font-bold mb-1">Raspadinhas: <?php echo intval($raspadinhas_disponiveis); ?>/<?php echo intval($raspadinhas_compradas); ?></p>
            <p class="text-sm text-gray-500 dark:text-gray-300 mb-2">Raspe para revelar</p>
            <div id="raspadinhaContainer" class="mb-6">
              <div class="scratcher-wrap">
                <canvas id="raspadinhaCanvas" width="420" height="260" class="scratcher-canvas"></canvas>
              </div>
            </div>
            <button onclick="fecharModalRaspadinha()" class="bg-gray-500 text-white px-6 py-3 rounded-lg font-bold hover:bg-gray-600 transition ml-2">
              Fechar
            </button>
          </div>
        </div>
      <?php endif; ?>

      <!-- Botão Voltar -->
      <div class="text-center mt-6">
        <a href="meus_titulos.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
          <i class="fas fa-arrow-left mr-2"></i>Voltar aos Meus Títulos
        </a>
      </div>

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

      // Garante que o status seja exibido corretamente
      document.addEventListener('DOMContentLoaded', function() {
        const statusPedido = document.getElementById('status_pedido');
        if (statusPedido) {
          const statusText = statusPedido.textContent.trim();
          if (statusText === 'Cancelado' && !statusPedido.classList.contains('status-cancelado')) {
            statusPedido.classList.add('status-cancelado');
          }
        }
      });

      // Variáveis globais para os jogos
      let itensRoleta = [];
      let itensRaspadinha = [];
      let roletaGirando = false;
      let roletaRotation = 0; // rotação acumulada da roda (em graus)
      let raspadinhaRaspada = false;
      let raspadinhaConsumida = false;
      let raspadinhaItemSelecionado = null;

      // Funções para a Roleta
      function iniciarRoleta() {
        // Buscar itens da roleta salvos na campanha e saldo do pedido
        fetch('get_itens_jogo_pedido.php?pedido_id=<?php echo $pedido[0]['id']; ?>&tipo=roleta')
          .then(response => response.json())
          .then(data => {
            const mensagensDerrotaFixas = [
              'Tente novamente! Mais sorte na próxima!',
              'Não foi dessa vez, mas não desista!',
              'O prêmio está quase aí! Continue tentando!',
              'Faltou pouco, vamos lá, tente mais uma vez!',
              'Não perca as esperanças, a vitória está próxima!'
            ];
            const itensCampanha = Array.isArray(data.itens) ? data.itens : [];
            const numBloqueados = itensCampanha.filter(i => String(i.status) !== 'disponivel').length;
            const mensagensParaAdd = Math.max(5, numBloqueados);
            const mensagens = [];
            for (let i = 0; i < mensagensParaAdd; i++) {
              mensagens.push({ nome: mensagensDerrotaFixas[i % mensagensDerrotaFixas.length], status: 'disponivel', _tipo: 'derrota' });
            }
            itensRoleta = [...itensCampanha.map(i => ({...i, _tipo: 'premio'})), ...mensagens];
            // contador roleta
            const cnt = document.getElementById('contadorRoleta');
            if (cnt) {
              const comprados = Number(data.comprados || 0);
              const saldo = Number(data.saldo || 0);
              cnt.textContent = `Giros: ${saldo}/${comprados}`;
              cnt.classList.remove('hidden');
            }
            document.getElementById('modalRoleta').classList.remove('hidden');
            desenharRoleta();
          })
          .catch(error => console.error('Erro ao carregar itens da roleta:', error));
      }

      function fecharModalRoleta() {
        document.getElementById('modalRoleta').classList.add('hidden');
      }

      function desenharRoleta() {
        const canvas = document.getElementById('roletaCanvas');
        const ctx = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = Math.min(centerX, centerY) - 14; // margem para borda

        // Limpar canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Fundo com glow suave
        const bgGrad = ctx.createRadialGradient(centerX, centerY, radius * 0.2, centerX, centerY, radius);
        bgGrad.addColorStop(0, 'rgba(124,58,237,0.15)');
        bgGrad.addColorStop(1, 'rgba(124,58,237,0.05)');
        ctx.fillStyle = bgGrad;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius + 10, 0, 2 * Math.PI);
        ctx.fill();

        // Exibir TODOS os itens (disponíveis e bloqueados). Apenas a seleção ignora os bloqueados
        const itensTodos = Array.isArray(itensRoleta) ? itensRoleta : [];
        if (itensTodos.length === 0) {
          ctx.fillStyle = '#666';
          ctx.font = '16px Arial';
          ctx.textAlign = 'center';
          ctx.fillText('Nenhum item disponível', centerX, centerY);
          return;
        }

        const anguloPorItem = (2 * Math.PI) / itensTodos.length;
        const cores = ['#8b5cf6', '#06b6d4', '#f97316', '#10b981', '#f43f5e', '#0ea5e9', '#a3e635', '#f59e0b'];

        itensTodos.forEach((item, index) => {
          const anguloInicio = index * anguloPorItem;
          const anguloFim = (index + 1) * anguloPorItem;

          // Fatia com leve gradiente
          const grad = ctx.createLinearGradient(centerX, centerY, centerX + Math.cos(anguloInicio) * radius, centerY + Math.sin(anguloInicio) * radius);
          grad.addColorStop(0, ctx.hexToRgba ? ctx.hexToRgba(cores[index % cores.length], 0.95) : cores[index % cores.length]);
          grad.addColorStop(1, 'rgba(255,255,255,0.12)');

          ctx.beginPath();
          ctx.moveTo(centerX, centerY);
          ctx.arc(centerX, centerY, radius, anguloInicio, anguloFim);
          ctx.closePath();
          ctx.fillStyle = grad;
          ctx.fill();
          ctx.strokeStyle = 'rgba(255,255,255,0.85)';
          ctx.lineWidth = 2;
          ctx.stroke();

          // Se estiver bloqueado, escurece o setor
          if (String(item.status) !== 'disponivel') {
            ctx.save();
            ctx.globalAlpha = 0.45;
            ctx.fillStyle = '#000';
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, anguloInicio, anguloFim);
            ctx.closePath();
            ctx.fill();
            ctx.restore();
          }

          // Divisória sutil
          ctx.beginPath();
          ctx.moveTo(centerX, centerY);
          ctx.lineTo(centerX + Math.cos(anguloFim) * radius, centerY + Math.sin(anguloFim) * radius);
          ctx.strokeStyle = 'rgba(255,255,255,0.35)';
          ctx.lineWidth = 1;
          ctx.stroke();

          // Texto (permitir quebra de linha e alinhamento mais próximo do centro)
          ctx.save();
          ctx.translate(centerX, centerY);
          ctx.rotate(anguloInicio + anguloPorItem / 2);
          ctx.textAlign = 'right';
          ctx.fillStyle = '#ffffff';
          ctx.font = '500 14px Arial';
          const raw = String(item.nome);
          let text = raw;
          if (raw.length > 22) text = raw.slice(0, 22) + '…';
          ctx.fillText(text, radius * 0.9, 4);
          ctx.restore();
        });

        // Anel externo
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius + 6, 0, 2 * Math.PI);
        ctx.strokeStyle = 'rgba(255,255,255,0.55)';
        ctx.lineWidth = 6;
        ctx.stroke();
      }

      function girarRoleta() {
        if (roletaGirando) return;
        roletaGirando = true;
        const btnGirar = document.getElementById('btnGirarRoleta');
        btnGirar.disabled = true;
        btnGirar.textContent = 'Girando...';

        const canvas = document.getElementById('roletaCanvas');
        const itensTodos = Array.isArray(itensRoleta) ? itensRoleta : [];
        const itensDisponiveis = itensTodos.filter(item => item.status === 'disponivel');
        if (itensTodos.length === 0 || itensDisponiveis.length === 0) {
          alert('Nenhum item disponível na roleta!');
          return;
        }

        // Selecionar item aleatório
        const itemSelecionado = itensDisponiveis[Math.floor(Math.random() * itensDisponiveis.length)];

        // Calcular ângulo alvo
        const index = itensTodos.findIndex(i => i === itemSelecionado);
        const anguloPorItem = 360 / itensTodos.length;
        // Centro do setor em graus relativo a 0° (3 horas)
        let anguloFinal = (index * anguloPorItem) + (anguloPorItem / 2);
        // Ajuste para o ponteiro no topo (12 horas). 0° do canvas é à direita, então somamos 90°
        const ponteiroOffset = 90;
        anguloFinal = (anguloFinal + ponteiroOffset) % 360;

        // Easing (easeOutCubic)
        const easeOut = (t) => 1 - Math.pow(1 - t, 3);

        const giros = 6; // voltas completas
        const duracao = 3500;
        const inicio = performance.now();

        // Rotação incremental necessária para alinhar o alvo, respeitando rotação atual
        const base = ((anguloFinal - (roletaRotation % 360)) + 360) % 360;
        const deltaTotal = giros * 360 + base;

        function animarGiro(agora) {
          const progressoLinear = Math.min((agora - inicio) / duracao, 1);
          const progresso = easeOut(progressoLinear);
          const anguloAtual = roletaRotation + deltaTotal * progresso;
          // Rotaciona a roda (negativo porque o ponteiro é fixo)
          canvas.style.transform = `rotate(${-anguloAtual}deg)`;

          if (progressoLinear < 1) {
            requestAnimationFrame(animarGiro);
          } else {
            roletaRotation = (roletaRotation + deltaTotal) % 360;
            setTimeout(() => {
              const canvasEl = document.getElementById('roletaCanvas');
              const boxResultado = document.getElementById('resultadoRoleta');
              const ganhou = itemSelecionado._tipo === 'premio';

              if (ganhou) {
                canvasEl.classList.add('win-flash');
                boxResultado.className = 'mb-3 text-green-600 font-bold';
                boxResultado.textContent = 'Parabéns! Você ganhou!!!';
              } else {
                boxResultado.className = 'mb-3 text-purple-600 font-semibold';
                boxResultado.textContent = itemSelecionado.nome;
              }
              boxResultado.classList.remove('hidden');

              const formData = new FormData();
              formData.append('pedido_id', '<?php echo $pedido[0]['id']; ?>');
              formData.append('tipo', 'roleta');
              formData.append('token', tokenPedido);
              fetch('consumir_jogo.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                  // Atualiza contador no modal após consumir
                  const cnt = document.getElementById('contadorRoleta');
                  if (cnt && res && typeof res.restantes !== 'undefined') {
                    const total = (Number(cnt.textContent.split('/')[1]) || 0);
                    cnt.textContent = `Giros: ${res.restantes}/${total}`;
                  }
                  // Atualiza também o card de jogos
                  const span = document.getElementById('spanGirosDisponiveis');
                  if (span) span.textContent = `${res.restantes}/${total}`;
                })
                .catch(() => {});

              const resultado = { tipo: ganhou ? 'premio' : 'derrota', nome: itemSelecionado.nome };
              const fd2 = new FormData();
              fd2.append('pedido_id', '<?php echo $pedido[0]['id']; ?>');
              fd2.append('tipo', 'roleta');
              fd2.append('token', tokenPedido);
              fd2.append('resultado', JSON.stringify(resultado));
              fetch('registrar_resultado_jogo.php', { method: 'POST', body: fd2 })
                .then(r => r.json())
                .then(() => {
                  // Atualiza box de prêmios em tempo real se ganhar
                  if (ganhou) {
                    const lista = document.getElementById('listaPremios');
                    if (lista) {
                      const btn = document.createElement('button');
                      btn.type = 'button';
                      btn.className = 'premio-chip';
                      btn.innerHTML = `<span class=\"text-xl\">🏆</span><span class=\"font-semibold\">${itemSelecionado.nome}</span>`;
                      btn.onclick = () => abrirModalPremio(itemSelecionado.nome);
                      lista.prepend(btn);
                    } else {
                      // Se o box não existe ainda, cria rapidamente abaixo do bloco de jogos
                      const section = document.querySelector('section .p-6') || document.querySelector('main');
                      const wrapper = document.createElement('div');
                      wrapper.className = 'premios-box relative rounded-xl mb-6 overflow-hidden';
                      wrapper.innerHTML = `
                        <div class=\"premios-bg absolute inset-0\"></div>
                        <div class=\"premios-content relative p-4\">
                          <h2 class=\"text-lg font-bold mb-1\">🏆 Prêmios conquistados <span class=\"align-middle\">🎉</span></h2>
                          <p class=\"text-sm text-gray-100/80 dark:text-white/80 mb-3\">Clique para ver detalhes e retirar seu prêmio.</p>
                          <div id=\"listaPremios\" class=\"grid grid-cols-1 sm:grid-cols-2 gap-3\">
                            <button type=\"button\" class=\"premio-chip\">\n                              <span class=\"text-xl\">🏆</span><span class=\"font-semibold\">${itemSelecionado.nome}</span>\n                            </button>
                          </div>
                        </div>`;
                      section.parentNode.insertBefore(wrapper, section.nextSibling);
                    }
                  }
                })
                .catch(() => {});

              roletaGirando = false;
              btnGirar.disabled = false;
              btnGirar.textContent = 'Girar Roleta';
            }, 300);
          }
        }
        requestAnimationFrame(animarGiro);
      }

      // Funções para a Raspadinha
      function iniciarRaspadinha() {
        // Buscar itens da raspadinha salvos na campanha e saldo do pedido
        fetch('get_itens_jogo_pedido.php?pedido_id=<?php echo $pedido[0]['id']; ?>&tipo=raspadinha')
          .then(response => response.json())
          .then(data => {
            itensRaspadinha = data.itens || [];
            const cnt = document.getElementById('contadorRaspadinha');
            if (cnt) {
              const comprados = Number(data.comprados || 0);
              const saldo = Number(data.saldo || 0);
              cnt.textContent = `${saldo}/${comprados}`;
              const tituloCnt = document.getElementById('contadorRaspadinhaTitulo');
              if (tituloCnt) tituloCnt.textContent = `${saldo}/${comprados}`;
            }
            document.getElementById('modalRaspadinha').classList.remove('hidden');
            raspadinhaRaspada = false;
            raspadinhaConsumida = false;
            raspadinhaItemSelecionado = null;
            desenharRaspadinha();
          })
          .catch(error => console.error('Erro ao carregar itens da raspadinha:', error));
      }

      function fecharModalRaspadinha() {
        document.getElementById('modalRaspadinha').classList.add('hidden');
      }

      function desenharRaspadinha() {
        const canvas = document.getElementById('raspadinhaCanvas');
        const ctx = canvas.getContext('2d');

        // Limpar e base
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const { width, height } = canvas;

        const itensDisponiveis = (Array.isArray(itensRaspadinha) ? itensRaspadinha : []).filter(i => String(i.status) === 'disponivel');
        if (itensDisponiveis.length === 0) {
          ctx.fillStyle = '#666';
          ctx.font = '16px Arial';
          ctx.textAlign = 'center';
          ctx.fillText('Nenhum item disponível', width / 2, height / 2);
          return;
        }

        raspadinhaItemSelecionado = itensDisponiveis[Math.floor(Math.random() * itensDisponiveis.length)];

        // Fundo revelado com reward central
        const bg = ctx.createLinearGradient(0, 0, 0, height);
        bg.addColorStop(0, '#f8fafc');
        bg.addColorStop(1, '#e2e8f0');
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, height);

        // Pattern de fundo (confetes sutis)
        const coresConfete = ['#fde68a', '#fca5a5', '#93c5fd', '#86efac', '#c4b5fd'];
        ctx.globalAlpha = 0.25;
        for (let i = 0; i < 60; i++) {
          ctx.fillStyle = coresConfete[i % coresConfete.length];
          const rx = Math.random() * width;
          const ry = Math.random() * height;
          const rr = 4 + Math.random() * 8;
          ctx.beginPath(); ctx.arc(rx, ry, rr, 0, 2*Math.PI); ctx.fill();
        }
        ctx.globalAlpha = 1;

        // medalhão
        const cx = width / 2; const cy = height / 2; const r = Math.min(width, height) * 0.28;
        const medal = ctx.createRadialGradient(cx - r*0.3, cy - r*0.3, r*0.2, cx, cy, r);
        medal.addColorStop(0, '#fff7ed');
        medal.addColorStop(1, '#fbbf24');
        ctx.fillStyle = medal; ctx.beginPath(); ctx.arc(cx, cy, r, 0, 2*Math.PI); ctx.fill();
        ctx.strokeStyle = 'rgba(0,0,0,0.15)'; ctx.lineWidth = 6; ctx.stroke();

        // texto do prêmio
        ctx.fillStyle = '#111827';
        ctx.font = 'bold 34px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(String(raspadinhaItemSelecionado.nome), cx, cy + 12);

        // Camada raspável (prata)
        const prata = ctx.createLinearGradient(0, 0, width, height);
        prata.addColorStop(0, '#bfbfbf');
        prata.addColorStop(1, '#9e9e9e');
        ctx.fillStyle = prata;
        ctx.fillRect(0, 0, width, height);

        // Textura sutil sobre a prata (listras) – mais leve para facilitar
        ctx.globalAlpha = 0.06; ctx.fillStyle = '#ffffff';
        for (let x = -height; x < width + height; x += 14) {
          ctx.save();
          ctx.translate(x, 0); ctx.rotate(-Math.PI/7);
          ctx.fillRect(0, 0, 6, height * 2);
          ctx.restore();
        }
        ctx.globalAlpha = 1;

        ctx.fillStyle = 'rgba(255,255,255,0.75)';
        ctx.font = '600 14px Arial';
        ctx.fillText('Raspe para revelar', width / 2, height - 12);

        let raspando = false;
        const start = () => { if (!raspadinhaRaspada) raspando = true; };
        const stop = () => { raspando = false; };
        const scratch = (x, y, r = 24) => {
          ctx.globalCompositeOperation = 'destination-out';
          ctx.beginPath(); ctx.arc(x, y, r, 0, 2 * Math.PI); ctx.fill();
          ctx.globalCompositeOperation = 'source-over';
        };
        const getPos = (e) => {
          const rect = canvas.getBoundingClientRect();
          const clientX = e.touches ? e.touches[0].clientX : e.clientX;
          const clientY = e.touches ? e.touches[0].clientY : e.clientY;
          return { x: clientX - rect.left, y: clientY - rect.top };
        };

        canvas.onmousedown = (e) => { start(); const p = getPos(e); scratch(p.x, p.y); };
        canvas.onmouseup = stop;
        canvas.onmouseleave = stop;
        // Só raspa enquanto o botão estiver pressionado
        canvas.onmousemove = (e) => {
          if (!raspando) return;
          const p = getPos(e);
          scratch(p.x, p.y);
          verificarRevelacao();
        };
        canvas.ontouchstart = (e) => { start(); const p = getPos(e); scratch(p.x, p.y); };
        canvas.ontouchend = stop;
        canvas.ontouchmove = (e) => {
          if (!raspando) return;
          const p = getPos(e);
          scratch(p.x, p.y);
          verificarRevelacao();
        };

        function verificarRevelacao() {
          if (raspadinhaRaspada) return;
          const sample = ctx.getImageData(0, 0, width, height).data;
          let transparent = 0;
          for (let i = 3; i < sample.length; i += 24) { if (sample[i] === 0) transparent++; }
          const perc = transparent / (sample.length / 24);
          if (perc > 0.25) finalizarRaspadinha();
        }

        function finalizarRaspadinha() {
          raspadinhaRaspada = true;
          setTimeout(() => {
            alert(`🎉 Parabéns! Você ganhou: ${raspadinhaItemSelecionado.nome}!`);
            if (!raspadinhaConsumida) {
              raspadinhaConsumida = true;
              const formData = new FormData();
              formData.append('pedido_id', '<?php echo $pedido[0]['id']; ?>');
              formData.append('tipo', 'raspadinha');
              formData.append('token', tokenPedido);
              fetch('consumir_jogo.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(res => {
                  if (!res.success) console.warn('Falha ao consumir raspadinha:', res.message);
                  const cnt = document.getElementById('contadorRaspadinha');
                  if (cnt && typeof res.restantes !== 'undefined') {
                    const total = (Number(cnt.textContent.split('/')[1]) || 0);
                    cnt.textContent = `Raspadinhas: ${res.restantes}/${total}`;
                    const span = document.getElementById('spanRaspadinhasDisponiveis');
                    if (span) span.textContent = `${res.restantes}/${total}`;
                    const tituloCnt = document.getElementById('contadorRaspadinhaTitulo');
                    if (tituloCnt) tituloCnt.textContent = `Raspadinhas: ${res.restantes}/${total}`;
                  }
                })
                .catch(err => console.error('Erro ao consumir raspadinha:', err));
            }
          }, 300);
        }
      }

      function rasparCartela() {
        if (raspadinhaRaspada) return;
        const canvas = document.getElementById('raspadinhaCanvas');
        const ctx = canvas.getContext('2d');
        const passos = 40;
        for (let i = 0; i < passos; i++) {
          setTimeout(() => {
            const t = i / (passos - 1);
            const x = 16 + t * (canvas.width - 32);
            const y = canvas.height * (0.25 + 0.5 * (1 - t));
            ctx.globalCompositeOperation = 'destination-out';
            ctx.beginPath(); ctx.arc(x, y, 32, 0, 2*Math.PI); ctx.fill();
            ctx.globalCompositeOperation = 'source-over';
          }, i * 28);
        }
      }

      // Box/Modal de Prêmios
      function abrirModalPremio(nomePremio) {
        const modal = document.getElementById('modalPremio');
        const txt = document.getElementById('textoPremio');
        const link = document.getElementById('linkRetirarPremio');
        txt.innerHTML = `🎉 Você ganhou <strong>${nomePremio}</strong>! 🎉`;
        // Usa link de fale conosco configurado
        link.href = '<?php echo isset($config['link_fale_conosco']) ? $config['link_fale_conosco'] : '#'; ?>';
        modal.classList.remove('hidden');
      }
      function fecharModalPremio() {
        document.getElementById('modalPremio').classList.add('hidden');
      }
  </script>

  <?php require_once('footer.php'); ?>

</body>

</html>