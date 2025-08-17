<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <?php
  require_once('header.php');
  require_once('functions/functions_pedidos.php');
  require_once('functions/functions_clientes.php');
  require_once('functions/functions_sistema.php');
  $config = listaInformacoes($conn);
  $campos_obrigatorios = explode(',', $config['campos_obrigatorios']);
  $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
  $campanhas = listaCampanhas($conn, $id, NULL, 1);
  $numeros_disponiveis = obterNumerosDisponiveis($conn, $id);
  $largura_cota = obterLarguraCotaPorCampanha($conn, $id);

  if ($campanhas[0]['cotas_premiadas'])
    $cotas_premiadas = explode(',', $campanhas[0]['cotas_premiadas']);
  else
    $cotas_premiadas = [];



  // print_r($campanhas[0]);
  
  // if ($campanhas[0]['status_cotas_premiadas'] == 'bloqueado')
  //     $reais_numeros_disponiveis = array_values(array_diff($numeros_disponiveis, $cotas_premiadas));
  // else
  //   $reais_numeros_disponiveis =  $numeros_disponiveis;
  
  ?>

  <style>
    .carousel {
      position: relative;
      width: 100%;
      height: 300px;
      overflow: hidden;
    }

    .carousel-inner {
      position: relative;
      width: 100%;
      height: 100%;
    }

    .carousel-slide {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
      display: none;
    }

    .carousel-slide.active {
      opacity: 1;
      display: block;
    }

    .carousel-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(0, 0, 0, 0.5);
      color: white;
      padding: 1rem;
      cursor: pointer;
      border-radius: 50%;
      z-index: 10;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .carousel-button:hover {
      background: rgba(0, 0, 0, 0.7);
    }

    .carousel-button.prev {
      left: 1rem;
    }

    .carousel-button.next {
      right: 1rem;
    }

    .carousel-indicators {
      position: absolute;
      bottom: 1rem;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 0.5rem;
      z-index: 10;
    }

    .carousel-indicator {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.5);
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .carousel-indicator.active {
      background: white;
    }

    /* Adicionando estilos para a barra de progresso animada */
    @keyframes moveStripes {
      0% {
        background-position: 0 0;
      }

      100% {
        background-position: 50px 0;
      }
    }

    .progress-bar-animated {
      position: relative;
      background-image: linear-gradient(45deg,
          rgba(255, 255, 255, 0.15) 25%,
          transparent 25%,
          transparent 50%,
          rgba(255, 255, 255, 0.15) 50%,
          rgba(255, 255, 255, 0.15) 75%,
          transparent 75%,
          transparent);
      background-size: 50px 50px;
      animation: moveStripes 2s linear infinite;
    }

    .img-campanha-16-9 {
      width: 100%;
      aspect-ratio: 16 / 9;
      max-width: 1920px;
      max-height: 1080px;
      object-fit: cover;
      display: block;
      margin: 0 auto;
      background: #222;
    }

    /* Estilos para transições do sistema de login */
    .secao-compra {
      transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
    }

    .secao-compra.slide-out {
      transform: translateX(-50%);
      opacity: 0;
    }

    .login-container {
      transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
      transform: translateX(50%);
      opacity: 0;
    }

    .login-container.slide-in {
      transform: translateX(0);
      opacity: 1;
    }

    .login-container.slide-out {
      transform: translateX(-50%);
      opacity: 0;
    }

    /* Animações para os formulários */
    .form-fade-in {
      animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Transições para os formulários */
    #formLoginInline,
    #formCadastroInline {
      transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
    }

    /* Transições para os containers de formulário */
    .bg-white.dark\\:bg-gray-800.rounded-lg.shadow-lg.p-6.mb-6 {
      transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
    }

    /* Estilos para os inputs com ícones */
    .input-with-icon {
      position: relative;
    }

    .input-with-icon input {
      padding-left: 2.5rem;
    }

    .input-with-icon i {
      position: absolute;
      left: 0.75rem;
      top: 50%;
      transform: translateY(-50%);
      color: #6b7280;
      z-index: 10;
    }

    /* Estilos para os botões */
    .btn-primary {
      background: linear-gradient(135deg, #10b981, #059669);
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #059669, #047857);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    /* Efeito de borda amarela nos botões de quantidade */
    .borda-animada-amarela {
      transition: box-shadow .12s ease-in-out, outline-color .12s ease-in-out;
    }

    .borda-animada-amarela:hover {
      box-shadow: 0 0 0 3px #FACC15 !important;
      outline: none;
    }

    /* Estilos para o checkbox personalizado */
    .custom-checkbox {
      appearance: none;
      width: 18px;
      height: 18px;
      border: 2px solid #fff;
      border-radius: 3px;
      background: transparent;
      cursor: pointer;
      position: relative;
    }

    .custom-checkbox:checked {
      background: #fff;
    }

    .custom-checkbox:checked::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: #f59e0b;
      font-size: 12px;
      font-weight: bold;
    }

    /* Animação de atenção para a mensagem de erro dos Termos de Uso */
    @keyframes attentionShake {
      0% {
        transform: translateX(0);
      }

      15% {
        transform: translateX(-10px);
      }

      30% {
        transform: translateX(10px);
      }

      45% {
        transform: translateX(-8px);
      }

      60% {
        transform: translateX(8px);
      }

      75% {
        transform: translateX(-4px);
      }

      90% {
        transform: translateX(4px);
      }

      100% {
        transform: translateX(0);
      }
    }

    .attention-shake {
      animation: attentionShake 600ms cubic-bezier(.36, .07, .19, .97) both;
    }
  </style>


  <?php
  // var_dump($_SESSION);die;
  if ($config['habilitar_api_facebook'] == '1'): ?>
    <!-- Pixel do Facebook -->
    <script>
      !function (f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function () {
          n.callMethod ?
            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = !0;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e); t.async = !0;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s)
      }(window, document, 'script',
        'https://connect.facebook.net/en_US/fbevents.js');

      fbq('init', '<?php echo $config['pixel_facebook']; ?>'); // ID do seu Pixel
      fbq('track', 'initiateCheckout');
    </script>

    <noscript>
      <img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=<?php echo $config['pixel_facebook']; ?>&ev=PageView&noscript=1" />
    </noscript>
    <!-- Fim do Pixel -->
  <?php endif; ?>
</head>
<?php
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
$codigo_exclusivo = isset($_REQUEST['cx']) ? $_REQUEST['cx'] : '';
$codigo_afiliado = isset($_REQUEST['ref']) ? $_REQUEST['ref'] : '';
;
$campanhas = listaCampanhas($conn, $id, NULL, 1);
$modo_exclusivo = isset($_REQUEST['cx']) ? true : false;


if ($id == 0) {
  header('location: index.php');
  die();
}

if (!$campanhas) {
  echo '<script>window.location.href = "index.php";</script>';
  die();
}

// Verificar código de afiliado
if (isset($_GET['ref'])) {
  $ref = $_GET['ref'];

  // Buscar código do afiliado vinculado à campanha atual
  $query = "SELECT ca.*, u.usuario_nome 
              FROM configuracoes_afiliados ca
              LEFT JOIN usuarios u ON u.usuario_id = ca.usuario_id
              WHERE ca.codigo_afiliado = ?";

  $stmt = $conn->prepare($query);
  $stmt->bind_param("s", $ref);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $afiliado = $result->fetch_assoc();
    $codigo_afiliado = $afiliado['codigo_afiliado'];
    $nome_afiliado = $afiliado['usuario_nome'];
  }
}

// Validações de limite
$numeros_disponiveis = count_obterNumerosDisponiveis($conn, $id);
$compra_minima = intval($campanhas[0]['compra_minima']);
$quantidade_total = intval($campanhas[0]['quantidade_numeros']);
$numeros_vendidos = getTotalNumerosVendidos($conn, $id);
$pedidos = listaPedidos($conn, NULL, NULL, $id, NULL, NULL, 1);

$soma_todos_numeros_vendidos = 0;
foreach ($pedidos as $pedido) {
  $soma_todos_numeros_vendidos += $pedido['quantidade'];
}

$desconto_acumulativo = $campanhas[0]['habilitar_desconto_acumulativo'] == 1 ? true : false;

if ($numeros_disponiveis < $campanhas[0]['compra_maxima'])
  $compra_maxima = $numeros_disponiveis;
else
  $compra_maxima = $campanhas[0]['compra_maxima'];

if ($campanhas[0]['ativar_progresso_manual'] == '1')
  $porcentagem_vendida = $campanhas[0]['porcentagem_barra_progresso'];
else
  $porcentagem_vendida = ($soma_todos_numeros_vendidos / $quantidade_total) * 100;

$imagens = array($campanhas[0]['imagem_capa']);


if ($imagens) {
  $imagens = array($campanhas[0]['caminho_imagem']);
  if (!empty($campanhas[0]['galeria_imagens'])) {
    $galeria = explode(',', $campanhas[0]['galeria_imagens']);
    $imagens = array_merge($imagens, $galeria);
  }
}

?>

<body class="bg-gray-100 text-black dark:bg-[#18181B] dark:text-white transition-all">


















































  <?php





















  if ($campanhas[0]["layout"] == 0) {
    ?>
    <!-- Main Content -->
    <main class="container mx-auto px-0 py-8">
      <section class="mb-10 w-full md:w-4/5 lg:w-3/5 mx-auto relative -top-12" style="margin-bottom: 0px;">
        <div class="bg-gray-100 dark:bg-[#27272A] rounded-lg shadow-lg p-[18px]">
          <!-- Título -->
          <h2 class="text-[15px] font-bold text-green-500 uppercase text-center mb-[18px] w-full" style="float: left;">
            <span style=" display: flex; width: 100%; align-items: center; justify-content: center;">
              <img src="assets/css/estrela.svg" alt="estrela"
                style="width: 30px;fill: #22C55A;margin-right:5px;"><?php echo $campanhas[0]['nome']; ?>

            </span>
          </h2>

          <div class="relative w-full max-w-3xl mx-auto overflow-hidden rounded-lg">
            <div id="carousel" class="flex transition-transform duration-500 ease-in-out">
              <?php
              foreach ($imagens as $key => $imagem): ?>
                <div class="min-w-full">
                  <img src="<?php echo $imagem; ?>" alt="<?php echo $campanhas[0]['nome']; ?>" class="img-campanha-16-9">
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Botões de navegação -->
            <!--<?php if (count($imagens) > 1): ?>-->
              <!--  <button id="prev"-->
              <!--    class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full shadow-md">-->
              <!--    &#10094;-->
              <!--  </button>-->
              <!--  <button id="next"-->
              <!--    class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full shadow-md">-->
              <!--    &#10095;-->
              <!--  </button>-->
              <!--<?php endif; ?>-->

            <!-- Indicadores -->
            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
              <?php foreach ($imagens as $key => $imagem): ?>
                <button class="indicator w-3 h-3 bg-white rounded-full opacity-50"
                  data-index="<?php echo $key; ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>

          <script>
            (function () {
              const carousel = document.getElementById("carousel");
              if (!carousel) return;
              const prev = document.getElementById("prev");
              const next = document.getElementById("next");
              const indicators = document.querySelectorAll(".indicator");
              let index = 0;

              function updateCarousel() {
                carousel.style.transform = `translateX(-${index * 100}%)`;
                indicators.forEach((dot, i) => {
                  dot.classList.toggle("opacity-100", i === index);
                });
              }

              if (prev) {
                prev.addEventListener("click", () => {
                  index = index > 0 ? index - 1 : indicators.length - 1;
                  updateCarousel();
                });
              }

              if (next) {
                next.addEventListener("click", () => {
                  index = index < indicators.length - 1 ? index + 1 : 0;
                  updateCarousel();
                });
              }

              indicators.forEach(dot => {
                dot.addEventListener("click", (e) => {
                  index = parseInt(e.target.dataset.index);
                  updateCarousel();
                });
              });

              let isMouseDown = false;
              let startX;
              let scrollLeft;

              carousel.addEventListener('mousedown', (e) => {
                isMouseDown = true;
                startX = e.pageX - carousel.offsetLeft;
                scrollLeft = carousel.scrollLeft;
              });

              carousel.addEventListener('mouseleave', () => {
                isMouseDown = false;
              });

              carousel.addEventListener('mouseup', () => {
                isMouseDown = false;
              });

              carousel.addEventListener('mousemove', (e) => {
                if (!isMouseDown) return;
                e.preventDefault();
                const x = e.pageX - carousel.offsetLeft;
                const walk = (x - startX) * 3; // Quanto mais alto o valor, mais rápido o movimento
                carousel.scrollLeft = scrollLeft - walk;
              });

              // Função para swipe no toque
              carousel.addEventListener('touchstart', (e) => {
                isMouseDown = true;
                startX = e.touches[0].pageX - carousel.offsetLeft;
                scrollLeft = carousel.scrollLeft;
              });

              carousel.addEventListener('touchend', () => {
                isMouseDown = false;
              });

              carousel.addEventListener('touchmove', (e) => {
                if (!isMouseDown) return;
                e.preventDefault();
                const x = e.touches[0].pageX - carousel.offsetLeft;
                const walk = (x - startX) * 3;
                carousel.scrollLeft = scrollLeft - walk;
              });
            })();
          </script>


          <!-- Barra de Progresso -->
          <?php if ($campanhas[0]['habilitar_barra_progresso'] == '1'): ?>
            <div class="mt-4 secao-compra">
              <div class="w-full bg-gray-300 dark:bg-[#3F3F46] rounded-full h-6 mb-2 relative overflow-hidden">
                <div class="bg-green-600 h-6 rounded-full progress-bar-animated relative"
                  style="width: <?php echo $porcentagem_vendida; ?>%"></div>
                <div class="absolute inset-0 flex items-center justify-center text-white text-sm w-full">
                  <span><?php echo number_format($porcentagem_vendida, 1); ?>% vendido</span>
                  <?php if ($campanhas[0]['ativar_progresso_manual'] != '1'): ?>
                    <span class="ml-2"><?php echo $soma_todos_numeros_vendidos; ?>/<?php echo $quantidade_total; ?>
                      números</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- Compartilhar e Grupo -->
          <div class="flex justify-center sm:justify-start gap-1 sm:gap-2 mb-6 mt-2 px-2 sm:px-0 secao-compra">
            <?php if (!$modo_exclusivo && $config['habilitar_compartilhamento'] == '1'): ?>
              <button onclick="compartilharCampanha()"
                class="flex-1 sm:flex-none sm:w-[35%] md:w-[30%] bg-green-600 hover:bg-green-700 text-white py-1.5 rounded-lg flex items-center justify-center gap-1 sm:gap-2">
                <i class="fas fa-share text-xs sm:text-sm"></i>
                <span class="text-[11px] sm:text-sm whitespace-nowrap">Compartilhar</span>
              </button>
            <?php endif; ?>

            <!-- Botão de Grupo -->
            <?php if ($config['habilitar_grupos'] == '1'): ?>
              <a href="<?php echo $config['link_grupo']; ?>" target="_blank"
                class="flex-1 sm:flex-none sm:w-[35%] md:w-[30%]">
                <div
                  class="cursor-pointer bg-green-600 hover:bg-green-700 rounded-lg py-1.5 px-2 flex items-center justify-center">
                  <div class="bg-white rounded-full p-0.5 sm:p-1 mr-1 sm:mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-green-600" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                  </div>
                  <span class="text-white font-medium text-[11px] sm:text-sm whitespace-nowrap">Entrar no Grupo</span>
                </div>
              </a>
            <?php endif; ?>
          </div>

          <!-- Descrição -->
          <div class="mb-6 secao-compra">
            <p class="bg-white dark:bg-[#3F3F46] p-2 rounded-lg text-[15px] font-semibold mb-2">
              <?php echo $campanhas[0]['descricao']; ?>
            </p>
            <?php if (!empty($campanhas[0]['subtitulo'])): ?>
              <p class="text-sm text-gray-400 mt-2"><?php echo $campanhas[0]['subtitulo']; ?></p>
            <?php endif; ?>
          </div>

          <!-- Notificação de Cotas em Dobro -->
          <?php if ($campanhas[0]['habilitar_cotas_em_dobro'] == '1'): ?>
            <div class="secao-compra mb-6">
              <div style="display: flex;justify-content: center;padding: 10px;"
                class=" bg-yellow-600 text-whi rounded-lg flex items-center gap-2">
                <span class="text-3xl" style="padding: 0px 5px 0px 10px;">🎉</span>
                <div>
                  <p class="font-bold">
                    <?php echo !empty($campanhas[0]['titulo_cotas_dobro']) ? $campanhas[0]['titulo_cotas_dobro'] : 'COTAS EM DOBRO ATIVADAS!'; ?>
                  </p>
                  <p class="text-md">
                    <?php echo !empty($campanhas[0]['subtitulo_cotas_dobro']) ? $campanhas[0]['subtitulo_cotas_dobro'] : 'Aproveite! Todas as cotas estão valendo em dobro.'; ?>
                  </p>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- Oferta Exclusiva -->
          <?php if ($modo_exclusivo): ?>
            <div class="secao-compra mb-6">
              <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md">
                <p class="text-sm text-purple-800 dark:text-purple-200">
                  <span class="font-bold">⭐ Oferta Exclusiva!1</span><br>
                  Compre com desconto: Condição especial de pacotes por tempo LIMITADO! Não perca essa oportunidade de
                  aumentar suas chances de ganhar!
                </p>
              </div>
            </div>
          <?php endif; ?>

          <!-- Adição Rápida -->
          <?php if (!$modo_exclusivo && $campanhas[0]['habilitar_adicao_rapida'] == '1'): ?>
            <div class="mb-6 secao-compra">
              <p class="text-sm font-medium text-black dark:text-white">Adição Rápida</p>
              <div class="grid grid-cols-3 gap-4 mt-2">
                <?php
                $incrementos = [25, 50, 100, 250, 500, 1000];
                foreach ($incrementos as $valor) {
                  echo "<button onclick=\"alterarQuantidade($valor, true)\"
                      class=\"bg-green-600 hover:bg-green-700 text-white py-2 rounded\">
                        +$valor
                    </button>";
                }
                ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Pacotes padrão -->
          <?php if ($campanhas[0]['habilitar_pacote_promocional'] == '1' && !isset($_REQUEST['cx'])): ?>
            <div class="mb-6 secao-compra">
              <p class="text-sm font-medium text-black dark:text-white">Pacotes padrão</p>

              <?php
              $pacotes_promocionais = $campanhas[0]['pacote_promocional'];
              $pacotes_promocionais = json_decode($pacotes_promocionais, true);
              echo "<div class='grid " . (count($pacotes_promocionais) <= 1 ? 'grid-cols-1' : 'grid-cols-2') . " gap-2 mt-2'>";
              if (is_array($pacotes_promocionais) && !empty($pacotes_promocionais)) {
                foreach ($pacotes_promocionais as $pacote) {
                  $qtd = intval($pacote['quantidade_numeros']);
                  $valor_original = $qtd * $campanhas[0]['preco'];
                  $beneficio_tipo = isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '';
                  $beneficio_qtd = isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0;
                  $beneficio_html = '';
                  if (!empty($beneficio_tipo) && $beneficio_qtd > 0) {
                    $badge_start = "<div class='mt-2 flex justify-center'><span class='inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-yellow-400/15 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-300 ring-1 ring-yellow-400/40 dark:ring-yellow-500/40'>";
                    $badge_end = "</span></div>";
                    if ($beneficio_tipo === 'roleta') {
                      $beneficio_html = $badge_start . "<i class='fa-solid fa-coins'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Giros' : 'Giro') . $badge_end;
                    } elseif ($beneficio_tipo === 'raspadinha') {
                      $beneficio_html = $badge_start . "<i class='fa-solid fa-ticket'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Raspadinhas' : 'Raspadinha') . $badge_end;
                    }
                  }
                  echo "<button onclick=\"alterarQuantidadePromocional($qtd, " . $pacote['valor_pacote'] . ", false, '" . ($beneficio_tipo ?? '') . "', " . ($beneficio_qtd ?? 0) . ")\" class='bg-white dark:bg-[#3F3F46] text-center p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all'>
                        <div class='text-black dark:text-white text-lg font-bold mb-1'>$qtd números</div>
                        <div class='text-red-500 dark:text-red-600 text-sm mb-1 line-through'>R$ " . number_format($valor_original, 2, ',', '.') . "</div>
                        <div class='text-green-500 dark:text-green-600 text-xl font-bold'>R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . "</div>" .
                    $beneficio_html .
                    "</button>";
                }
              } else {
                echo "<p class='text-sm text-gray-400'>Nenhum pacote promocional disponível no momento.</p>";
              }
              ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Pacotes exclusivos -->
        <?php
        if ($campanhas[0]['habilita_pacote_promocional_exclusivo'] == '1' && $modo_exclusivo): ?>
          <div class="secao-compra mb-6">
            <p class="text-sm font-medium text-black dark:text-white">Pacotes Exclusivos</p>
            <?php
            $pacotes_promocionais_exclusivos = $campanhas[0]['pacotes_exclusivos'];
            $pacotes_promocionais_exclusivos = json_decode($pacotes_promocionais_exclusivos, true);

            if (is_array($pacotes_promocionais_exclusivos) && !empty($pacotes_promocionais_exclusivos && $codigo_exclusivo != 0)) {
              $grid_cols = count($pacotes_promocionais_exclusivos) > 1 ? 'grid-cols-2' : 'grid-cols-1';
              echo '<div class="grid mt-2 text-center ' . $grid_cols . ' gap-2">';

              foreach ($pacotes_promocionais_exclusivos as $pacote) {
                if ($codigo_exclusivo == $pacote['codigo_pacote']) {
                  $qtd = intval($pacote['quantidade_numeros']);
                  $valor_original = $qtd * $campanhas[0]['preco'];
                  $beneficio_tipo = isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '';
                  $beneficio_qtd = isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0;
                  $beneficio_html = '';
                  if (!empty($beneficio_tipo) && $beneficio_qtd > 0) {
                    $badge_start = "<div class='mt-2 flex justify-center'><span class='inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-yellow-300/20 dark:bg-yellow-500/20 text-yellow-100 dark:text-yellow-200 ring-1 ring-yellow-300/40 dark:ring-yellow-500/40'>";
                    $badge_end = "</span></div>";
                    if ($beneficio_tipo === 'roleta') {
                      $beneficio_html = $badge_start . "<i class='fa-solid fa-coins'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Giros' : 'Giro') . $badge_end;
                    } elseif ($beneficio_tipo === 'raspadinha') {
                      $beneficio_html = $badge_start . "<i class='fa-solid fa-ticket'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Raspadinhas' : 'Raspadinha') . $badge_end;
                    }
                  }
                  echo "<button onclick=\"alterarQuantidadePromocional($qtd, " . $pacote['valor_pacote'] . ", false, '" . ($beneficio_tipo ?? '') . "', " . ($beneficio_qtd ?? 0) . ")\" class='bg-purple-600 text-white py-2 rounded text-center'>
                            <span class='block text-lg font-bold'>$qtd números</span>
                            <span class='block text-red-300 text-sm mb-1 line-through'>R$ " . number_format($valor_original, 2, ',', '.') . "</span>
                            <span class='block text-green-300 text-lg font-bold'>R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . "</span>" .
                    $beneficio_html .
                    "</button>";
                }
              }
              echo '</div>';
            } else {
              echo "<p class='text-sm text-gray-400'>Nenhum pacote exclusivo disponível no momento.</p>";
            }
            ?>
          </div>
        <?php endif; ?>

        <!-- Pacotes de Roleta removidos: uso passa a ser pacotes gerais da campanha -->
        <?php if (false): ?>
          <div class="mb-8 px-4 mt-2">
            <div class="max-w-7xl mx-auto">
              <!-- Header com título e descrição -->
              <div class="text-center mb-8 pacote-roleta-header">
                <div class="flex items-center justify-center mb-4">
                  <div
                    class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3">
                    <span class="text-2xl">🎰</span>
                  </div>
                  <h3 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white">Pacotes de Roleta</h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300 text-lg max-w-2xl mx-auto">
                  Escolha o pacote ideal para suas rodadas na roleta
                </p>
              </div>

              <!-- Seletor de quantidade rápida -->
              <div class="mb-8">
                <div
                  class="bg-white dark:bg-[#27272A] rounded-2xl p-4 shadow-lg border border-gray-200 dark:border-gray-700">
                  <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 text-center">Quantidade Rápida</h4>
                  <div class="flex flex-wrap justify-center gap-3 quantidade-rapida-container">
                    <button onclick="quantidadeRapida(25)"
                      class="quantidade-rapida-btn bg-gray-100 dark:bg-[#3F3F46] hover:bg-green-100 dark:hover:bg-green-800 text-gray-700 dark:text-gray-300 hover:text-green-700 dark:hover:text-green-300 px-6 py-3 rounded-xl font-medium transition-all duration-200 border-2 border-transparent hover:border-green-300">
                      +25
                    </button>
                    <button onclick="quantidadeRapida(50)"
                      class="quantidade-rapida-btn bg-gray-100 dark:bg-[#3F3F46] hover:bg-green-100 dark:hover:bg-green-800 text-gray-700 dark:text-gray-300 hover:text-green-700 dark:hover:text-green-300 px-6 py-3 rounded-xl font-medium transition-all duration-200 border-2 border-transparent hover:border-green-300">
                      +50
                    </button>
                    <button onclick="quantidadeRapida(100)"
                      class="quantidade-rapida-btn bg-gray-100 dark:bg-[#3F3F46] hover:bg-green-100 dark:hover:bg-green-800 text-gray-700 dark:text-gray-300 hover:text-green-700 dark:hover:text-green-300 px-6 py-3 rounded-xl font-medium transition-all duration-200 border-2 border-transparent hover:border-green-300">
                      +100
                    </button>
                    <button onclick="quantidadeRapida(250)"
                      class="quantidade-rapida-btn bg-gray-100 dark:bg-[#3F3F46] hover:bg-green-100 dark:hover:bg-green-800 text-gray-700 dark:text-gray-300 hover:text-green-700 dark:hover:text-green-300 px-6 py-3 rounded-xl font-medium transition-all duration-200 border-2 border-transparent hover:border-green-300">
                      +250
                    </button>
                  </div>
                </div>
              </div>

              <!-- Grid de pacotes melhorado -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pacote-roleta-grid">
                <?php foreach ($itens_roleta as $pacote): ?>
                  <div class="relative group">
                    <?php if (isset($pacote['des  taque']) && $pacote['destaque'] == '1'): ?>
                      <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-20">
                        <div
                          class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg flex items-center badge-popular">
                          <span class="mr-1">⭐</span>
                          <span>Mais Popular</span>
                        </div>
                      </div>
                    <?php endif; ?>

                    <div
                      class="bg-white dark:bg-[#27272A] border-2 border-gray-200 dark:border-gray-700 rounded-2xl p-6 text-center shadow-lg hover:shadow-2xl transition-all duration-300 h-full flex flex-col justify-between group-hover:border-green-300 dark:group-hover:border-green-500 pacote-roleta-card <?php echo isset($pacote['destaque']) && $pacote['destaque'] == '1' ? 'ring-2 ring-green-500 ring-opacity-30 shadow-green-500/20' : ''; ?>">
                      <!-- Ícone do pacote -->
                      <div class="mb-4">
                        <div
                          class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                          <span class="text-2xl">🎰</span>
                        </div>
                      </div>

                      <!-- Quantidade de giros -->
                      <div class="flex-1">
                        <div class="text-5xl font-bold text-gray-900 dark:text-white mb-2 leading-none">
                          <?php echo htmlspecialchars(isset($pacote['quantidade_giros']) ? $pacote['quantidade_giros'] : ''); ?>
                        </div>
                        <div class="text-gray-600 dark:text-gray-300 text-lg font-medium mb-6">
                          Giros
                        </div>
                      </div>

                      <!-- Preço e botão -->
                      <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-6">
                          R$
                          <?php echo number_format(isset($pacote['valor_pacote']) ? $pacote['valor_pacote'] : 0, 2, ',', '.'); ?>
                        </div>
                        <button type="button"
                          class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
                          onclick="selecionarPacoteRoleta('<?php echo htmlspecialchars(isset($pacote['codigo_pacote']) ? $pacote['codigo_pacote'] : ''); ?>', <?php echo isset($pacote['valor_pacote']) ? $pacote['valor_pacote'] : 0; ?>, <?php echo isset($pacote['quantidade_giros']) ? $pacote['quantidade_giros'] : 0; ?>)">
                          <span class="flex items-center justify-center">
                            <span class="mr-2">🛒</span>
                            Comprar
                          </span>
                        </button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Pacotes de Raspadinha -->
        <?php if ($campanhas[0]['habilitar_raspadinha'] == '1' && !empty($pacotes_raspadinha)): ?>
          <div class="mb-8 px-4 mt-2">
            <h3 class="text-2xl font-bold text-center mb-6 text-gray-800 dark:text-white">🎫 Pacotes de Raspadinha</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 max-w-7xl mx-auto">
              <?php foreach ($pacotes_raspadinha as $pacote): ?>
                <div class="relative">
                  <?php if (isset($pacote['destaque']) && $pacote['destaque'] == '1'): ?>
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-10">
                      <span class="bg-green-500 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg"
                        style="white-space: nowrap;">Mais Popular</span>
                    </div>
                  <?php endif; ?>
                  <div
                    class="bg-white dark:bg-[#27272A] border border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300 h-full flex flex-col justify-between <?php echo isset($pacote['destaque']) && $pacote['destaque'] == '1' ? 'ring-2 ring-green-500 ring-opacity-50' : ''; ?>">
                    <div class="flex-1">
                      <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                        <?php echo htmlspecialchars(isset($pacote['quantidade_raspadinhas']) ? $pacote['quantidade_raspadinhas'] : ''); ?>
                      </div>
                      <div class="text-gray-600 dark:text-gray-300 text-lg mb-4">
                        Raspadinhas
                      </div>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                      <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-4">
                        R$
                        <?php echo number_format(isset($pacote['valor_pacote']) ? $pacote['valor_pacote'] : 0, 2, ',', '.'); ?>
                      </div>
                      <button type="button"
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200 transform hover:scale-105"
                        onclick="selecionarPacoteRaspadinha('<?php echo htmlspecialchars(isset($pacote['codigo_pacote']) ? $pacote['codigo_pacote'] : ''); ?>', <?php echo isset($pacote['valor_pacote']) ? $pacote['valor_pacote'] : 0; ?>, <?php echo isset($pacote['quantidade_raspadinhas']) ? $pacote['quantidade_raspadinhas'] : 0; ?>)">
                        Comprar
                      </button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="mb-6 secao-compra">
          <label for="quantidade" class="block text-sm font-medium text-black dark:text-white secao-compra">
            Quantidade (Mín: <?php echo $compra_minima; ?>)
          </label>

          <!-- Custom Quantidade Seletor -->
          <script>
            // Fallback para Layouts que usam onclick inline antes das funções serem definidas
            (function () {
              const precoUnitarioBase = <?php echo $campanhas[0]['preco']; ?>;
              const compraMinimaBase = <?php echo $compra_minima; ?>;
              const compraMaximaBase = <?php echo $compra_maxima; ?>;

              if (typeof window.alterarQuantidade !== 'function') {
                window.alterarQuantidade = function (qtd, adicionar) {
                  const precoUnitario = precoUnitarioBase;
                  const compraMinima = compraMinimaBase;
                  const compraMaxima = compraMaximaBase;
                  let novaQuantidade;
                  const inputQtdBarra = document.getElementById('quantidade_barra');
                  if (!inputQtdBarra) return;
                  if (adicionar) {
                    const quantidadeAtual = parseInt(inputQtdBarra.value || compraMinima, 10);
                    novaQuantidade = quantidadeAtual + qtd;
                  } else {
                    novaQuantidade = qtd;
                  }
                  if (novaQuantidade < compraMinima) novaQuantidade = compraMinima;
                  if (novaQuantidade > compraMaxima) novaQuantidade = compraMaxima;
                  const valorTotal = precoUnitario * novaQuantidade;
                  inputQtdBarra.value = novaQuantidade;
                  const inputQtd = document.getElementById('quantidade');
                  const inputValor = document.getElementById('valor_total');
                  const btnComprar = document.getElementById('btnComprar');
                  if (inputQtd) inputQtd.value = novaQuantidade;
                  if (inputValor) inputValor.value = valorTotal;
                  if (btnComprar) btnComprar.innerHTML = 'Comprar por R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                  const mensagemMaximo = document.querySelector('.text-red-500');
                  if (mensagemMaximo) mensagemMaximo.style.display = novaQuantidade >= compraMaxima ? 'block' : 'none';
                }
              }

              if (typeof window.quantidadeRapida !== 'function') {
                window.quantidadeRapida = function (quantidade) {
                  const precoUnitario = precoUnitarioBase;
                  const compraMinima = compraMinimaBase;
                  const compraMaxima = compraMaximaBase;
                  let novaQuantidade = parseInt(quantidade, 10);
                  if (novaQuantidade < compraMinima) novaQuantidade = compraMinima;
                  if (novaQuantidade > compraMaxima) novaQuantidade = compraMaxima;
                  const valorTotal = precoUnitario * novaQuantidade;
                  const inputQtdBarra = document.getElementById('quantidade_barra');
                  const inputQtd = document.getElementById('quantidade');
                  const inputValor = document.getElementById('valor_total');
                  const btnComprar = document.getElementById('btnComprar');
                  if (inputQtdBarra) inputQtdBarra.value = novaQuantidade;
                  if (inputQtd) inputQtd.value = novaQuantidade;
                  if (inputValor) inputValor.value = valorTotal;
                  if (btnComprar) btnComprar.innerHTML = 'Comprar por R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
              }

              if (typeof window.alterarQuantidadePromocional !== 'function') {
                window.alterarQuantidadePromocional = function (qtd, precoPacote, adicionar, beneficioTipo, beneficioQuantidade) {
                  // Define quantidade de cotas do pedido de acordo com o pacote selecionado
                  const inputQtdBarra = document.getElementById('quantidade_barra');
                  const inputQtd = document.getElementById('quantidade');
                  const inputValor = document.getElementById('valor_total');
                  const btnComprar = document.getElementById('btnComprar');

                  const quantidadeFinal = adicionar ? (parseInt(inputQtdBarra?.value || 0, 10) + qtd) : qtd;
                  if (inputQtdBarra) inputQtdBarra.value = quantidadeFinal;
                  if (inputQtd) inputQtd.value = quantidadeFinal;

                  // Valor total deve ser o valor do pacote
                  if (inputValor) inputValor.value = precoPacote;
                  if (btnComprar) btnComprar.innerHTML = 'Comprar por R$ ' + Number(precoPacote).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                  // Zera campos de benefícios para evitar sujeira de seleção anterior
                  let girosInput = document.getElementById('quantidade_giros_roleta');
                  if (!girosInput) {
                    girosInput = document.createElement('input');
                    girosInput.type = 'hidden';
                    girosInput.id = 'quantidade_giros_roleta';
                    girosInput.name = 'quantidade_giros_roleta';
                    document.getElementById('formComprar').appendChild(girosInput);
                  }
                  girosInput.value = 0;

                  let raspadinhasInput = document.getElementById('quantidade_raspadinhas');
                  if (!raspadinhasInput) {
                    raspadinhasInput = document.createElement('input');
                    raspadinhasInput.type = 'hidden';
                    raspadinhasInput.id = 'quantidade_raspadinhas';
                    raspadinhasInput.name = 'quantidade_raspadinhas';
                    document.getElementById('formComprar').appendChild(raspadinhasInput);
                  }
                  raspadinhasInput.value = 0;

                  // Aplica benefício do pacote no campo correto
                  const qtdBeneficio = parseInt(beneficioQuantidade || 0, 10);
                  if (beneficioTipo === 'roleta' && qtdBeneficio > 0) {
                    girosInput.value = qtdBeneficio;
                  } else if (beneficioTipo === 'raspadinha' && qtdBeneficio > 0) {
                    raspadinhasInput.value = qtdBeneficio;
                  }
                }
              }
            })();
          </script>
          <div
            class="flex items-center justify-center gap-2 mt-4 mb-2 w-full px-4 bg-gray-100 dark:bg-[#27272A] p-2 rounded-2xl flex-col border border-gray-300 dark:border-[#000000]">

            <div class="flex items-center space-x-2">
              <button onclick="alterarQuantidade(-5,true)"
                class="bg-[#1BC863] hover:bg-[#e0b90f] text-white rounded-lg font-bold w-10 h-10 text-xl">-5</button>
              <button onclick="alterarQuantidade(-1,true)"
                class="bg-[#1BC863] hover:bg-[#e0b90f] text-white rounded-lg font-bold w-10 h-10 text-xl">-</button>

              <input type="number" id="quantidade_barra" value="<?php echo $compra_minima; ?>"
                class="flex-1 text-center bg-gray-100 dark:bg-[#3F3F46] text-ffffff dark:text-white rounded-lg border-none"
                readonly>

              <button onclick="alterarQuantidade(1,true)"
                class="bg-[#1BC863] hover:bg-[#e0b90f] text-white rounded-lg font-bold w-10 h-10 text-xl">+</button>
              <button onclick="alterarQuantidade(5,true)"
                class="bg-[#1BC863] hover:bg-[#e0b90f] text-white rounded-lg font-bold w-10 h-10 text-xl">+5</button>
            </div>
            <div style="display: none;" class="text-red-500 flex justify-between text-xs text-gray-400 mt-2">
              <span></span>
              <span>Máximo: <?php echo $compra_maxima; ?></span>
            </div>

          </div>
          <div class="flex justify-between text-xs text-gray-400 mt-2">
            <span></span>
            <span class="text-red-500" style="display: none;">Máximo: <?php echo $compra_maxima; ?></span>
          </div>
        </div>


        <!-- Comprar -->

        <form action="processamento_pagamento.php" method="POST" id="formComprar" onsubmit="return validarCompra(event)"
          class="secao-compra">
          <input type="hidden" name="campanha_id" value="<?php echo $id; ?>">
          <input type="hidden" name="valor_total" id="valor_total"
            value="<?php echo $campanhas[0]['preco'] * $compra_minima; ?>">
          <input type="hidden" name="cliente_id" id="cliente_id" value="">
          <input type="hidden" name="quantidade" id="quantidade" value="<?php echo $compra_minima; ?>">
          <input type="hidden" name="numeros_solicitados" id="numeros_solicitados" value="">
          <input type="hidden" name="nome_produto" value="<?php echo htmlspecialchars($campanhas[0]['nome']); ?>">
          <input type="hidden" name="codigo_afiliado" value="<?php echo $codigo_afiliado; ?>">


          <?php
          // Verifica se é um cliente (pode comprar) ou usuário do sistema (não pode comprar)
          $isCliente = isset($_SESSION['usuario']['cliente_id']);
          $isUsuarioSistema = isset($_SESSION['usuario']['usuario_id']);

          if (!isset($_SESSION['usuario']) || $isCliente) {
            ?>
            <div style="border: 2px solid #1BC863; padding: 3px;" class="rounded-xl">
              <button id="btnComprar" onclick="verificarLogin('comprar')"
                class="w-full bg-[#1BC863] hover:bg-[#43996d] text-white text-lg md:text-xl py-3 rounded-xl font-bold shadow transition">
                Comprar por R$
                <?php echo number_format($campanhas[0]['preco'] * $compra_minima, 2, ',', '.'); ?>
              </button>
            </div>
            <?php
          }
          // else
          // {
          ?>
          <!-- <button type="button"
            class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold">
            Não há números disponíveis para esta campanha.
          </button> -->
          <?php
          // }
          ?>

        </form>
        <?php
        // Verifica se é um cliente (pode ver títulos) ou usuário do sistema (não pode ver títulos)
        $isCliente = isset($_SESSION['usuario']['cliente_id']);
        $isUsuarioSistema = isset($_SESSION['usuario']['usuario_id']);

        if (!isset($_SESSION['usuario']) || $isCliente) {
          ?>
          <div class="w-full flex flex-col gap-2 mt-2 secao-compra">
            <button onclick="verificarLogin('titulos')"
              class="border border-[#1BC863] text-[#1BC863] rounded-lg font-semibold px-4 py-2 flex items-center justify-center gap-2 transition-all mb-1">
              <span class="iconify" data-icon="tabler:numbers"></span>
              Ver meus números1
            </button>
          </div>
          <?php
        }
        ?>

        <!-- cotas premiadas -->
        <?php
        if (!empty($campanhas[0]['mostrar_cotas_premiadas']) && !empty($campanhas[0]['cotas_premiadas'])):
          $premios_json = json_decode($campanhas[0]['premio_cotas_premiadas'], true);
          if (is_array($premios_json) && !empty($premios_json)): ?>
            <div class="w-full px-4 mb-2 secao-compra">
              <p class="text-sm font-medium text-black dark:text-white mb-2">🔥 Cotas premiadas - Achou ganhou!</p>
              <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-3 lg:grid-cols-3 gap-2">
                <?php
                foreach ($premios_json as $grupo) {
                  foreach ($grupo['cotas'] as $cota) {
                    $cota_formatada = formatarCotaComLargura($cota, $largura_cota);
                    echo "<div class='bg-blue-600 text-white p-2 rounded-lg text-center min-h-[64px]'>";
                    echo "<div class='flex items-center justify-center mb-1'>";
                    echo "<span class='text-green-400 mr-2'>★</span>";
                    echo "<span class='text-base font-bold'>" . $cota_formatada . "</span>";
                    echo "</div>";
                    echo "<div class='text-xs font-bold'> " . htmlspecialchars($grupo['premio']) . "</div>";
                    echo "</div>";
                  }
                }
                ?>
              </div>
              <p class="text-sm text-gray-400 mt-2">
                <?php echo $campanhas[0]['descricao_cotas_premiadas']; ?>
              </p>
            </div>
          <?php endif;
        endif; ?>


        <!-- top compradores -->
        <?php
        if ($campanhas[0]['habilitar_ranking'] == '1'):


          $filtro_periodo_top_ganhadores = json_decode($campanhas[0]['filtro_periodo_top_ganhadores'], true);
          if (!isset($filtro_periodo_top_ganhadores['filtro']))
            $filtro_periodo_top_ganhadores['filtro'] = 'ultimo_mes';

          switch ($filtro_periodo_top_ganhadores['filtro']) {
            case 'hoje':
              $data_inicio = date('Y-m-d H:i:s');
              $data_fim = date('Y-m-d H:i:s');
              break;

            case 'ontem':
              $data_inicio = date('Y-m-d H:i:s', strtotime('-1 day'));
              $data_fim = $data_inicio; // ontem apenas
              break;

            case 'ultimo_mes':
              $data_inicio = date('Y-m-d H:i:s', strtotime('-30 days'));
              $data_fim = date('Y-m-d H:i:s');
              break;

            case 'personalizado':

              $data_inicio = explode(' até ', $filtro_periodo_top_ganhadores['valor'])[0];
              $data_fim = explode(' até ', $filtro_periodo_top_ganhadores['valor'])[1];
              break;
          }

          // Buscar top compradores da campanha
          $top_compradores = listaClientesTopCompradores($conn, $id, $data_inicio, $data_fim, $campanhas[0]['quantidade_ranking']);
          if (!empty($top_compradores)): ?>
            <div class="w-full px-4 mb-2 secao-compra">
              <p class="text-sm font-medium text-black dark:text-white mb-2">🏆 Top Compradores</p>
              <div class="grid grid-cols-1 gap-2">
                <?php
                $posicao = 1;
                foreach ($top_compradores as $comprador):
                  $medalha = '';
                  if ($posicao == 1)
                    $medalha = '🥇';
                  else if ($posicao == 2)
                    $medalha = '🥈';
                  else if ($posicao == 3)
                    $medalha = '🥉';
                  ?>
                  <div class="bg-gray-100 dark:bg-gray-600 p-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span class="text-yellow-400 font-bold"><?php echo $medalha; ?>#<?php echo $posicao++; ?></span>
                      <span
                        class="text-black dark:text-white"><?php echo htmlspecialchars($comprador['cliente_nome']); ?></span>
                    </div>
                    <div class="flex items-center gap-4">
                      <div class="text-sm text-gray-400">
                        R$ <?php echo number_format($comprador['valor_total'], 2, ',', '.'); ?>
                      </div>
                      <div class="text-green-400">
                        <?php echo $comprador['total_comprado']; ?> cotas
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php if (!empty($campanhas[0]['descricao_top_compradores'])): ?>
                <p class="text-sm text-gray-400 mt-2">
                  <?php echo $campanhas[0]['descricao_top_compradores']; ?>
                </p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>


        </div>

        <!-- Sistema de Login/Cadastro Inline -->
        <div id="loginCadastroContainerLayout0" class="hidden login-container">
          <!-- Sistema de Login -->
          <div class="rounded-lg shadow-lg p-6 mb-6 bg-gray-100 dark:bg-[#3F3F46]">
            <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Digite seu número</h3>

            <!-- Formulário de Login -->
            <form id="formLoginInline" class="space-y-4 mb-6">
              <div class="relative">
                <label class="block text-gray-700 dark:text-gray-300 mb-2">Número de celular</label>
                <div class="relative">
                  <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                  <input type="tel" name="telefone" required
                    class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                    placeholder="(11) 99999-9999">
                </div>
              </div>

              <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold transition-colors">
                Entrar
              </button>
            </form>

            <div class="text-center">
              <button onclick="mostrarCadastro()"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                Primeira vez? Cadastre-se
              </button>
            </div>

            <!-- Resumo do Pedido -->
            <!-- <div class="mt-6 p-4 bg-green-600 rounded-lg">
              <div class="text-white text-center">
                <p class="font-bold">Valor do pedido: R$ <span id="valorPedidoLoginLayout0">0,00</span></p>
              </div>
            </div> -->

            <!-- Termos de Uso -->
            <div class="mt-4 p-3 bg-orange-500 rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <input type="checkbox" id="aceitarTermosLayout0" class="mr-2">
                  <label for="aceitarTermosLayout0" class="text-white text-sm">Aceito os termos de uso</label>
                </div>
                <button type="button" onclick="verTermosUso()"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors">
                  Ver Termos de Uso
                </button>
              </div>
            </div>

            <!-- Mensagem de Erro -->
            <div id="mensagemErroLayout0" class="hidden mt-4 p-3 bg-red-500 rounded-lg">
              <p class="text-white text-sm text-center">Para prosseguir você deve aceitar Termos de Uso.</p>
            </div>
          </div>



          <!-- Formulário de Cadastro Inline -->
          <div id="cadastroContainerLayout0" class="hidden w-full">
            <div class="rounded-lg shadow-lg p-6 mb-6 bg-gray-100 dark:bg-[#3F3F46]">
              <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Cadastro</h3>

              <form id="formCadastroInline" class="space-y-4">
                <!-- Campo Telefone - Sempre obrigatório -->
                <div class="relative">
                  <label class="block text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-phone mr-2"></i>Telefone
                  </label>
                  <div class="relative">
                    <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="tel" name="telefone" required
                      class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                      placeholder="(11) 99999-9999">
                  </div>
                </div>

                <?php if (empty($campos_obrigatorios) || in_array('nome', $campos_obrigatorios)): ?>
                  <div class="relative">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Nome Completo</label>
                    <div class="relative">
                      <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                      <input type="text" name="nome" <?php echo in_array('nome', $campos_obrigatorios) ? 'required' : ''; ?>
                        class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                        placeholder="Digite seu nome completo">
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (empty($campos_obrigatorios) || in_array('email', $campos_obrigatorios)): ?>
                  <div class="relative">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">E-mail</label>
                    <div class="relative">
                      <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                      <input type="email" name="email" <?php echo in_array('email', $campos_obrigatorios) ? 'required' : ''; ?>
                        class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                        placeholder="Digite seu e-mail">
                    </div>
                  </div>
                <?php endif; ?>

                <?php if (in_array('cpf', $campos_obrigatorios)): ?>
                  <div class="relative">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">CPF</label>
                    <div class="relative">
                      <i class="fas fa-id-card absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                      <input type="text" name="cpf" required
                        class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                        placeholder="Digite seu CPF">
                    </div>
                  </div>
                <?php endif; ?>

                <button type="submit"
                  class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold transition-colors">
                  Cadastrar
                </button>
              </form>

              <div class="text-center mt-4">
                <button onclick="voltarParaLogin()"
                  class="text-blue-500 hover:text-blue-600 text-sm font-medium transition-colors">
                  Já tenho cadastro, fazer login
                </button>
              </div>
            </div>
          </div>

          <!-- Botões de Navegação -->
          <div class="flex justify-between mt-4">
            <button onclick="voltarParaCompra()"
              class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
              <i class="fas fa-arrow-left mr-2"></i>
              Voltar
            </button>

          </div>
        </div>


      </section>
    </main>
  <?php } elseif ($campanhas[0]["layout"] == 1) { ?>
    <main class="container mx-auto px-0 py-8">
      <section class=" w-full md:w-4/5 lg:w-3/5 mx-auto relative -top-12" style=" margin-bottom: 0px;">

        <div
          class="bg-white dark:bg-[#27272A] rounded-2xl shadow-lg w-full p-0 md:p-4 flex flex-col items-center relative p-2">
          <!--border-[#2e3947] border-4-->
          <div class="relative w-full max-w-3xl mx-auto overflow-hidden rounded-lg">
            <div id="carousel" class="flex transition-transform duration-500 ease-in-out">
              <?php
              foreach ($imagens as $key => $imagem): ?>
                <div class="min-w-full">
                  <img src="<?php echo $imagem; ?>" alt="<?php echo $campanhas[0]['nome']; ?>" class="img-campanha-16-9">
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Botões de navegação -->
            <?php if (count($imagens) > 1): ?>
              <!-- <button id="prev"
              class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full shadow-md">
              &#10094;
            </button>
            <button id="next"
              class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-800 text-white p-2 rounded-full shadow-md">
              &#10095;
            </button> -->
            <?php endif; ?>

            <!-- Indicadores -->
            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
              <?php foreach ($imagens as $key => $imagem): ?>
                <button class="indicator w-3 h-3 bg-white rounded-full opacity-50"
                  data-index="<?php echo $key; ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>

          <script>
            const carousel = document.getElementById("carousel");
            const prev = document.getElementById("prev");
            const next = document.getElementById("next");
            const indicators = document.querySelectorAll(".indicator");
            let index = 0;

            function updateCarousel() {
              carousel.style.transform = `translateX(-${index * 100}%)`;
              indicators.forEach((dot, i) => {
                dot.classList.toggle("opacity-100", i === index);
              });
            }

            prev.addEventListener("click", () => {
              index = index > 0 ? index - 1 : indicators.length - 1;
              updateCarousel();
            });

            next.addEventListener("click", () => {
              index = index < indicators.length - 1 ? index + 1 : 0;
              updateCarousel();
            });

            indicators.forEach(dot => {
              dot.addEventListener("click", (e) => {
                index = parseInt(e.target.dataset.index);
                updateCarousel();
              });
            });
          </script>








          <div class="w-full px-4 py-3 bg-gray-100 dark:bg-[#3F3F46] ">
            <span class="bg-[#1BC863] text-black text-xs rounded px-3 py-1 font-bold inline-block mb-2">Adquira já!</span>
            <h1 class="text-[18px] md:text-[25px] font-bold mb-1">
              <span style="display: flex;align-items: center;" <img src="assets/css/estrela.svg" alt="estrela"
                style="fill: #22C55A;margin-right:5px;">
                <img src="assets/css/estrela.svg" alt="estrela" style="width: 30px;fill: #22C55A;margin-right:5px;">
                <?php
                echo isset($campanhas[0]["nome"]) ? $campanhas[0]["nome"] : '';
                ?>
            </h1>
            </span>
            <p class="text-white dark:text-[#b8b9ad] font-semibold text-base">
              <?php
              echo isset($campanhas[0]["subtitulo"]) ? $campanhas[0]["subtitulo"] : '-';
              ?>
            </p>


          </div>


          <div class="w-full secao-compra">
            <?php if ($campanhas[0]['habilitar_barra_progresso'] == '1' || $config['habilitar_compartilhamento'] == '1' || $config['habilitar_grupos'] == '1'): ?>
              <!-- Barra de Progresso -->


              <div class="flex justify-center sm:justify-start gap-1 sm:gap-2 mt-2 px-2 sm:px-0">
                <?php if (!$modo_exclusivo && $config['habilitar_compartilhamento'] == '1'): ?>
                  <button onclick="compartilharCampanha()"
                    class="flex-1 sm:flex-none sm:w-[35%] md:w-[30%] bg-[#1BC863] hover:bg-[#e0b90f] text-black py-1.5 rounded-lg flex items-center justify-center gap-1 sm:gap-2">
                    <i class="fas fa-share text-xs sm:text-sm"></i>
                    <span class="text-[11px] sm:text-sm whitespace-nowrap">Compartilhar</span>
                  </button>
                <?php endif; ?>

                <!-- Botão de Grupo -->
                <?php if ($config['habilitar_grupos'] == '1'): ?>
                  <a href="<?php echo $config['link_grupo']; ?>" target="_blank"
                    class="flex-1 sm:flex-none sm:w-[35%] md:w-[30%]">
                    <div
                      class="cursor-pointer bg-[#1BC863] hover:bg-[#e0b90f] rounded-lg py-1.5 px-2 flex items-center justify-center">
                      <div class="bg-black rounded-full p-0.5 sm:p-1 mr-1 sm:mr-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#1BC863]" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                      </div>
                      <span class="text-black font-medium text-[11px] sm:text-sm whitespace-nowrap">Entrar no Grupo</span>
                    </div>
                  </a>
                <?php endif; ?>
              </div>

              <?php if ($campanhas[0]['habilitar_barra_progresso'] == '1'): ?>
                <div class="mt-4 w-full">
                  <div class="w-full bg-gray-300 dark:bg-gray-500 rounded-full h-6 mb-2 relative overflow-hidden">
                    <div class="bg-[#1BC863] h-6 rounded-full progress-bar-animated relative"
                      style="width: <?php echo $porcentagem_vendida; ?>%"></div>
                    <div class="absolute inset-0 flex items-center justify-center text-white text-sm w-full">
                      <span><?php echo number_format($porcentagem_vendida, 1); ?>% vendido</span>
                      <?php if ($campanhas[0]['ativar_progresso_manual'] != '1'): ?>
                        <span class="ml-2"><?php echo $soma_todos_numeros_vendidos; ?>/<?php echo $quantidade_total; ?>
                          números</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

            <?php endif; ?>
          </div>
          <div class="flex flex-row items-center gap-2 justify-center mt-2 secao-compra">
            <span class="text-gray-600 dark:text-[#b8b9ad] font-medium">por apenas</span>
            <span class="bg-[#111217] rounded px-2 py-1 text-white font-bold">R$ <?php
            echo isset($campanhas[0]['preco']) ? $campanhas[0]['preco'] : "-";
            ?></span>
          </div>

          <!-- Chamada promocional -->
          <div class="w-full mt-2 text-center secao-compra">
            <span
              class="block font-semibold text-gray-700 dark:text-[#b8b9ad] bg-gray-100  dark:bg-[#3F3F46] rounded-2xl p-4">
              <?php
              echo isset($campanhas[0]["descricao"]) ? $campanhas[0]["descricao"] : '';
              ?>
            </span>
          </div>



          <!-- Notificação de Cotas em Dobro -->
          <?php if ($campanhas[0]['habilitar_cotas_em_dobro'] == '1'): ?>
            <div class="secao-compra my-4 w-full">
              <div style="display: flex;justify-content: center;padding: 10px;"
                class=" bg-yellow-600 text-white rounded-lg flex items-center gap-2">
                <span class="text-3xl" style="padding: 0px 5px 0px 10px;">🎉</span>
                <div>
                  <p class="font-bold">
                    <?php echo !empty($campanhas[0]['titulo_cotas_dobro']) ? $campanhas[0]['titulo_cotas_dobro'] : 'COTAS EM DOBRO ATIVADAS!'; ?>
                  </p>
                  <p class="text-md">
                    <?php echo !empty($campanhas[0]['subtitulo_cotas_dobro']) ? $campanhas[0]['subtitulo_cotas_dobro'] : 'Aproveite! Todas as cotas estão valendo em dobro.'; ?>
                  </p>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- Oferta Exclusiva -->
          <?php if ($modo_exclusivo): ?>
            <div class="secao-compra my-4 w-full">
              <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md">
                <p class="text-sm text-purple-800 dark:text-purple-200">
                  <span class="font-bold">⭐ Oferta Exclusiva!123</span><br>
                  Compre com desconto: Condição especial de pacotes por tempo LIMITADO! Não perca essa oportunidade de
                  aumentar suas chances de ganhar!
                </p>
              </div>
            </div>
          <?php endif; ?>
          <!-- Pacotes exclusivos -->
          <?php
          if ($campanhas[0]['habilita_pacote_promocional_exclusivo'] == '1' && $modo_exclusivo): ?>
            <div class="secao-compra my-4 w-full">
              <p class="text-sm font-medium text-black dark:text-white">Pacotes Exclusivos</p>
              <?php
              $pacotes_promocionais_exclusivos = $campanhas[0]['pacotes_exclusivos'];
              $pacotes_promocionais_exclusivos = json_decode($pacotes_promocionais_exclusivos, true);

              if (is_array($pacotes_promocionais_exclusivos) && !empty($pacotes_promocionais_exclusivos && $codigo_exclusivo != 0)) {
                $grid_cols = count($pacotes_promocionais_exclusivos) > 1 ? 'grid-cols-2' : 'grid-cols-1';
                echo '<div class="grid mt-2 text-center ' . $grid_cols . ' gap-2">';

                foreach ($pacotes_promocionais_exclusivos as $pacote) {
                  if ($codigo_exclusivo == $pacote['codigo_pacote']) {
                    $qtd = intval($pacote['quantidade_numeros']);
                    $valor_original = $qtd * $campanhas[0]['preco'];
                    $beneficio_tipo = isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '';
                    $beneficio_qtd = isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0;
                    $beneficio_html = '';
                    if (!empty($beneficio_tipo) && $beneficio_qtd > 0) {
                      $badge_start = "<div class='mt-2 flex justify-center'><span class='inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-yellow-300/20 dark:bg-yellow-500/20 text-yellow-100 dark:text-yellow-200 ring-1 ring-yellow-300/40 dark:ring-yellow-500/40'>";
                      $badge_end = "</span></div>";
                      if ($beneficio_tipo === 'roleta') {
                        $beneficio_html = $badge_start . "<i class='fa-solid fa-coins'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Giros' : 'Giro') . $badge_end;
                      } elseif ($beneficio_tipo === 'raspadinha') {
                        $beneficio_html = $badge_start . "<i class='fa-solid fa-ticket'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Raspadinhas' : 'Raspadinha') . $badge_end;
                      }
                    }
                    echo "<button onclick=\"alterarQuantidadePromocional($qtd, " . $pacote['valor_pacote'] . ", false, '" . ($beneficio_tipo ?? '') . "', " . ($beneficio_qtd ?? 0) . ")\" class='bg-purple-600 text-white py-2 rounded text-center'>
                            <span class='block text-lg font-bold'>$qtd números</span>
                            <span class='block text-red-300 text-sm mb-1 line-through'>R$ " . number_format($valor_original, 2, ',', '.') . "</span>
                            <span class='block text-green-300 text-lg font-bold'>R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . "</span>" .
                      $beneficio_html .
                      "</button>";
                  }
                }
                echo '</div>';
              } else {
                echo "<p class='text-sm text-gray-400'>Nenhum pacote exclusivo disponível no momento.</p>";
              }
              ?>
            </div>
          <?php endif; ?>




          <!-- Barra de promoções -->
          <div class="flex mt-2 items-center w-full secao-compra">
            <h1 class="text-1xl md:text-3xl font-bold mb-1">
              📣 Promoção
            </h1>
            <span class="flex justify-center items-center ml-2">Compre mais barato!</span>
          </div>


          <div
            class="w-full px-4 mt-3 flex flex-wrap gap-2 justify-center bg-gray-100 dark:bg-[#3F3F46] rounded-2xl p-2 secao-compra">
            <?php
            $pacotes_promocionais_origin = $campanhas[0]['pacote_promocional'];
            $pacotes_promocionais_origin = json_decode($pacotes_promocionais_origin, true);

            // var_dump($pacotes_promocionais_origin);die;
          
            foreach ($pacotes_promocionais_origin as $pacote) {
              $qtd = intval($pacote['quantidade_numeros']);
              $valor_original = $qtd * $campanhas[0]['preco'];

              // Adicionando novos pacotes ao array
              $pacotes_promocionais[] = [
                "valor_bilhete" => $pacote['valor_bilhete'],
                "quantidade_numeros" => $pacote['quantidade_numeros'],
                "valor_pacote" => $pacote['valor_pacote'],
                "beneficio_tipo" => isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '',
                "beneficio_quantidade" => isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0
              ];
            }


            if (is_array($pacotes_promocionais) && !empty($pacotes_promocionais)) {
              foreach ($pacotes_promocionais as $pacote) {
                $qtd = intval($pacote['quantidade_numeros']);
                $valor_original = $qtd * $campanhas[0]['preco'];
                $beneficio_tipo = isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '';
                $beneficio_qtd = isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0;
                $beneficio_html = '';
                if (!empty($beneficio_tipo) && $beneficio_qtd > 0) {
                  $beneficio_html = "<br><span class='inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-yellow-400/15 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-300 ring-1 ring-yellow-400/40 dark:ring-yellow-500/40'>" .
                    ($beneficio_tipo === 'roleta'
                      ? "<i class='fa-solid fa-coins'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Giros' : 'Giro')
                      : "<i class='fa-solid fa-ticket'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Raspadinhas' : 'Raspadinha'))
                    . "</span>";
                }

                echo "<span style='background: #1BC863!important;color:#ffffff!important;' class='bg-[#1BC863] text-black pacote-promocional' 
                onclick=\"alterarQuantidadePromocional
                ($qtd, '" . $pacote['valor_pacote'] . "', false, '" . ($beneficio_tipo ?? '') . "', " . ($beneficio_qtd ?? 0) . ")\">$qtd por R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . $beneficio_html . "</span>";
              }
            } else {
              echo "<p class='text-sm text-gray-400'>Nenhum pacote promocional disponível no momento.</p>";
            }
            ?>
          </div>

          <style>
            .text-base {
              font-size: 0.75rem;
              /* Ajuste o valor conforme necessário */
            }

            .pacote-promocional {
              text-align: center;
              background-color: #1BC863;
              color: #111217;
              font-size: 0.75rem;
              /* 14px */
              padding: 0.25rem 0.75rem;
              /* 4px 12px */
              border-radius: 0.5rem;
              /* arredondamento de 8px */
              font-weight: 500;
              cursor: pointer;
              width: 23%;
              /* Aproximadamente 1/4 da largura */
              box-sizing: border-box;
            }

            /* Responsividade */
            @media (max-width: 768px) {
              .pacote-promocional {
                width: 43%;
                /* Cada item ocupará a largura total em telas pequenas */
              }
            }
          </style>




















          <!-- Grid Seletores de Quantidade -->
          <div class="grid grid-cols-3 gap-1 w-full mt-4 secao-compra">

            <?php
            $incrementos = [25, 50, 100, 250, 500, 1000];
            foreach ($incrementos as $valor) {
              $isPopular = $valor === 50;
              $bgClass = "bg-[#111217]";
              $textColor = "text-white";
              $borderClass = "border border-[#343b41]";
              $hoverClass = "hover:bg-[#232a32]";
              $extraClass = $isPopular ? "relative" : "";

              $selectTextColor = "text-gray-300";

              echo "<button onclick=\"alterarQuantidade($valor, true)\" class='$bgClass $textColor py-4 rounded-xl $borderClass font-semibold $hoverClass transition borda-animada $extraClass'>
                      " . ($isPopular ? "<div  style='width: 75%;'   class='absolute -top-3 left-1/2 transform -translate-x-1/2 bg-[#1BC863] text-[#111217] text-[10px] font-bold px-2 py-[2px] rounded-md'>Mais popular</div>" : "") . "
                      +$valor<br />
                      <span class='text-xs $selectTextColor'>
                        SELECIONAR
                      </span>
                    </button>";
            }
            ?>



          </div>

          <!-- Pacotes de Roleta removidos: uso passa a ser pacotes gerais da campanha -->
          <?php if (false): ?>
            <div class="mb-8 px-4 mt-2">
              <h3 class="text-2xl font-bold text-center mb-6 text-gray-800 dark:text-white">🎰 Pacotes de Roleta</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <?php foreach ($itens_roleta as $pacote): ?>
                  <div class="relative">
                    <?php if (isset($pacote['destaque']) && $pacote['destaque'] == '1'): ?>
                      <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-10">
                        <span class="bg-green-500 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg"
                          style="white-space: nowrap;">Mais Popular</span>
                      </div>
                    <?php endif; ?>
                    <div
                      class="bg-white dark:bg-[#27272A] border border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300 h-full flex flex-col justify-between <?php echo isset($pacote['destaque']) && $pacote['destaque'] == '1' ? 'ring-2 ring-green-500 ring-opacity-50' : ''; ?>">
                      <div class="flex-1">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                          <?php echo htmlspecialchars(isset($pacote['quantidade_giros']) ? $pacote['quantidade_giros'] : ''); ?>
                        </div>
                        <div class="text-gray-600 dark:text-gray-300 text-lg mb-4">
                          Giros
                        </div>
                      </div>
                      <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-4">
                          R$
                          <?php echo number_format(isset($pacote['valor_pacote']) ? $pacote['valor_pacote'] : 0, 2, ',', '.'); ?>
                        </div>
                        <button type="button"
                          class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200 transform hover:scale-105"
                          onclick="selecionarPacoteRoleta('<?php echo htmlspecialchars(isset($pacote['codigo_pacote']) ? $pacote['codigo_pacote'] : ''); ?>', <?php echo isset($pacote['valor_pacote']) ? $pacote['valor_pacote'] : 0; ?>, <?php echo isset($pacote['quantidade_giros']) ? $pacote['quantidade_giros'] : 0; ?>)">
                          Comprar
                        </button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>






          <div style="display: flex;justify-content: start;width: 100%;" class="mt-4 secao-compra">
            <label for="quantidade" class="block text-sm font-medium text-black dark:text-white secao-compra">
              Quantidade (Mín: <?php echo $compra_minima; ?>)
            </label>
          </div>

          <!-- Custom Quantidade Seletor -->
          <div
            class="flex items-center justify-center gap-2 mt-4 mb-2 w-full px-4 bg-gray-100 dark:bg-[#27272A] p-2 rounded-2xl flex-col border border-gray-300 dark:border-[#000000] secao-compra">
            <div class="flex items-center space-x-2">
              <button onclick="alterarQuantidade(-5,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">-5</button>
              <button onclick="alterarQuantidade(-1,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">-</button>

              <input type="number" id="quantidade_barra" value="<?php echo $compra_minima; ?>"
                class="flex-1 text-center bg-gray-100 dark:bg-[#3F3F46] text-black dark:text-white rounded-lg border-none"
                readonly>

              <button onclick="alterarQuantidade(1,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">+</button>
              <button onclick="alterarQuantidade(5,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">+5</button>
            </div>


            <div style="display: none;" class="text-red-500 flex justify-between text-xs text-gray-400 mt-2">
              <span></span>
              <span>Máximo: <?php echo $compra_maxima; ?></span>
            </div>

          </div>
          <!-- Botão Quero Participar -->
          <div class="w-full px-4 mb-2 ">





            <form action="processamento_pagamento.php" method="POST" id="formComprar"
              onsubmit="return validarCompra(event)" class="secao-compra">
              <input type="hidden" name="campanha_id" value="<?php echo $id; ?>">
              <input type="hidden" name="valor_total" id="valor_total"
                value="<?php echo $campanhas[0]['preco'] * $compra_minima; ?>">
              <input type="hidden" name="cliente_id" id="cliente_id" value="">
              <input type="hidden" name="quantidade" id="quantidade" value="<?php echo $compra_minima; ?>">
              <input type="hidden" name="numeros_solicitados" id="numeros_solicitados" value="">
              <input type="hidden" name="nome_produto" value="<?php echo htmlspecialchars($campanhas[0]['nome']); ?>">
              <input type="hidden" name="codigo_afiliado" value="<?php echo $codigo_afiliado; ?>">
              <?php


              // Verifica se é um cliente (pode comprar) ou usuário do sistema (não pode comprar)
              $isCliente = isset($_SESSION['usuario']['cliente_id']);
              $isUsuarioSistema = isset($_SESSION['usuario']['usuario_id']);

              if (!isset($_SESSION['usuario']) || $isCliente) {

                ?>
                <div style="border: 2px solid #1BC863; padding: 3px;" class="rounded-xl mt-4">
                  <button id="btnComprar" onclick="verificarLogin('comprar')"
                    class="w-full bg-[#1BC863] hover:bg-[#43996d] text-white text-lg md:text-xl py-3 rounded-xl font-bold shadow transition">
                    Comprar por R$
                    <?php echo number_format($campanhas[0]['preco'] * $compra_minima, 2, ',', '.'); ?>
                  </button>
                </div>
                <?php
              }
              ?>

              <?php
              // Verifica se é um cliente (pode ver títulos) ou usuário do sistema (não pode ver títulos)
              $isCliente = isset($_SESSION['usuario']['cliente_id']);
              $isUsuarioSistema = isset($_SESSION['usuario']['usuario_id']);

              if (!isset($_SESSION['usuario']) || $isCliente) {
                ?>
                <!-- Botão "Ver meus números" e valor -->
                <div class="w-full flex flex-col gap-2 mt-2">
                  <button onclick="verificarLogin('titulos')"
                    class="border border-[#1BC863] text-[#1BC863] rounded-lg font-semibold px-4 py-2 flex items-center justify-center gap-2 transition-all mb-1">
                    <span class="iconify" data-icon="tabler:numbers"></span>
                    Ver meus números
                  </button>
                </div>
            </div>
            <?php
              } ?>


          <!-- cotas premiadas -->
          <?php
          if (!empty($campanhas[0]['mostrar_cotas_premiadas']) && !empty($campanhas[0]['cotas_premiadas']) && !empty($campanhas[0]['premio_cotas_premiadas'])):
            $premios_json = json_decode($campanhas[0]['premio_cotas_premiadas'], true);
            if (is_array($premios_json) && !empty($premios_json)): ?>
              <div class="w-full px-4 mb-2 secao-compra">
                <p class="text-sm font-medium text-black dark:text-white mb-2">🔥 Cotas premiadas - Achou ganhou!</p>
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-3">
                  <?php
                  foreach ($premios_json as $grupo) {
                    foreach ($grupo['cotas'] as $cota) {
                      $cota_formatada = formatarCotaComLargura($cota, $largura_cota);
                      echo "<div class='bg-blue-600 text-white p-3 rounded-lg text-center'>";
                      echo "<div class='flex items-center justify-center mb-2'>";
                      echo "<span class='text-green-400 mr-2'>★</span>";
                      echo "<span class='text-lg font-bold'>" . $cota_formatada . "</span>";
                      echo "</div>";
                      echo "<div class='text-sm font-bold'>" . htmlspecialchars($grupo['premio']) . "</div>";
                      echo "</div>";
                    }
                  }
                  ?>
                </div>
                <p class="text-sm text-gray-400 mt-2">
                  <?php echo $campanhas[0]['descricao_cotas_premiadas']; ?>
                </p>
              </div>
            <?php endif;
          elseif (!empty($campanhas[0]['mostrar_cotas_premiadas']) && !empty($campanhas[0]['cotas_premiadas'])): ?>
            <div class="w-full px-4 mb-2 secao-compra">
              <p class="text-sm font-medium text-black dark:text-white mb-2">🔥 Cotas premiadas - Achou ganhou!</p>
              <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-6 lg:grid-cols-6 gap-2 font-bold">
                <?php
                $cotas_premiadas = explode(',', $campanhas[0]['cotas_premiadas']);
                foreach ($cotas_premiadas as $cota) {
                  $cota_formatada = formatarCotaComLargura($cota, $largura_cota);
                  echo "<div class='bg-green-600 dark:text-black text-white text-center py-2 px-1 rounded-lg'>" . $cota_formatada . "</div>";
                }
                ?>
              </div>
              <p class="text-sm text-gray-400 mt-2">
                <?php echo $campanhas[0]['descricao_cotas_premiadas']; ?>
              </p>
            </div>
          <?php endif; ?>


          <!-- top compradores -->
          <?php
          if ($campanhas[0]['habilitar_ranking'] == '1'):


            $filtro_periodo_top_ganhadores = json_decode($campanhas[0]['filtro_periodo_top_ganhadores'], true);
            if (!isset($filtro_periodo_top_ganhadores['filtro']))
              $filtro_periodo_top_ganhadores['filtro'] = 'ultimo_mes';

            switch ($filtro_periodo_top_ganhadores['filtro']) {
              case 'hoje':
                $data_inicio = date('Y-m-d H:i:s');
                $data_fim = date('Y-m-d H:i:s');
                break;

              case 'ontem':
                $data_inicio = date('Y-m-d H:i:s', strtotime('-1 day'));
                $data_fim = $data_inicio; // ontem apenas
                break;

              case 'ultimo_mes':
                $data_inicio = date('Y-m-d H:i:s', strtotime('-30 days'));
                $data_fim = date('Y-m-d H:i:s');
                break;

              case 'personalizado':

                $data_inicio = explode(' até ', $filtro_periodo_top_ganhadores['valor'])[0];
                $data_fim = explode(' até ', $filtro_periodo_top_ganhadores['valor'])[1];
                break;
            }

            // Buscar top compradores da campanha
            $top_compradores = listaClientesTopCompradores($conn, $id, $data_inicio, $data_fim, $campanhas[0]['quantidade_ranking']);
            if (!empty($top_compradores)): ?>
              <div class="w-full px-4 mb-2 secao-compra">
                <p class="text-sm font-medium text-black dark:text-white mb-2">🏆 Top Compradores</p>
                <div class="grid grid-cols-1 gap-2">
                  <?php
                  $posicao = 1;
                  foreach ($top_compradores as $comprador):
                    $medalha = '';
                    if ($posicao == 1)
                      $medalha = '🥇';
                    else if ($posicao == 2)
                      $medalha = '🥈';
                    else if ($posicao == 3)
                      $medalha = '🥉';
                    ?>
                    <div class="bg-gray-300 dark:bg-gray-600 p-3 rounded-lg flex items-center justify-between">
                      <div class="flex items-center gap-2">
                        <span class="text-yellow-400 font-bold"><?php echo $medalha; ?>#<?php echo $posicao++; ?></span>
                        <span
                          class="text-black dark:text-white"><?php echo htmlspecialchars($comprador['cliente_nome']); ?></span>
                      </div>
                      <div class="flex items-center gap-4">
                        <div class="text-sm text-gray-400">
                          R$ <?php echo number_format($comprador['valor_total'], 2, ',', '.'); ?>
                        </div>
                        <div class="text-green-400">
                          <?php echo $comprador['total_comprado']; ?> cotas
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <?php if (!empty($campanhas[0]['descricao_top_compradores'])): ?>
                  <p class="text-sm text-gray-400 mt-2">
                    <?php echo $campanhas[0]['descricao_top_compradores']; ?>
                  </p>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          </form>

          <!-- Info Sorteio -->
          <!-- <div class="px-4 pb-2 text-center w-full">
                    <p class="text-sm text-gray-600 dark:text-[#b8b9ad]">Sorteio feito pela Loteria Federal. Todos os dados do veículo, como suas
          modificações serão divulgadas pelo Instagram.</p>
      </div> -->

          <!-- Sistema de Login/Cadastro Inline -->
          <div id="loginCadastroContainerLayout1" class="hidden w-full login-container">
            <!-- Sistema de Login -->
            <div class="rounded-lg shadow-lg p-6 mb-6 bg-gray-100 dark:bg-[#3F3F46]">
              <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Digite seu número</h3>

              <!-- Formulário de Login -->
              <form id="formLoginInline" class="space-y-4 mb-6">
                <div class="relative">
                  <label class="block text-gray-700 dark:text-gray-300 mb-2">Número de celular</label>
                  <div class="relative">
                    <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="tel" name="telefone" required
                      class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                      placeholder="(11) 99999-9999">
                  </div>
                </div>

                <button type="submit"
                  class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold transition-colors">
                  Entrar
                </button>
              </form>

              <div class="text-center">
                <button onclick="mostrarCadastro()"
                  class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                  Primeira vez? Cadastre-se
                </button>
              </div>

              <!-- Resumo do Pedido -->
              <!-- <div class="mt-6 p-4 bg-green-600 rounded-lg">
                <div class="text-white text-center">
                  <p class="font-bold">Valor do pedido: R$ <span id="valorPedidoLoginLayout1">0,00</span></p>
                </div>
              </div> -->

              <!-- Termos de Uso -->
              <div class="mt-4 p-3 bg-orange-500 rounded-lg">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <input type="checkbox" id="aceitarTermosLayout1" class="mr-2">
                    <label for="aceitarTermosLayout1" class="text-white text-sm">Aceito os termos de uso</label>
                  </div>
                  <button type="button" onclick="verTermosUso()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors">
                    Ver Termos de Uso
                  </button>
                </div>
              </div>

              <!-- Mensagem de Erro -->
              <div id="mensagemErroLayout1" class="hidden mt-4 p-3 bg-red-500 rounded-lg">
                <p class="text-white text-sm text-center">Para prosseguir você deve aceitar Termos de Uso.</p>
              </div>
            </div>



            <!-- Formulário de Cadastro Inline -->
            <div id="cadastroContainerLayout1" class="hidden w-full">
              <div class="rounded-lg shadow-lg p-6 mb-6 bg-gray-100 dark:bg-[#3F3F46]">
                <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Cadastro</h3>

                <form id="formCadastroInline" class="space-y-4">
                  <!-- Campo Telefone - Sempre obrigatório -->
                  <div class="relative">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">
                      <i class="fas fa-phone mr-2"></i>Telefone
                    </label>
                    <div class="relative">
                      <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                      <input type="tel" name="telefone" required
                        class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                        placeholder="(11) 99999-9999">
                    </div>
                  </div>

                  <?php if (empty($campos_obrigatorios) || in_array('nome', $campos_obrigatorios)): ?>
                    <div class="relative">
                      <label class="block text-gray-700 dark:text-gray-300 mb-2">Nome Completo</label>
                      <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="nome" <?php echo in_array('nome', $campos_obrigatorios) ? 'required' : ''; ?>
                          class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                          placeholder="Digite seu nome completo">
                      </div>
                    </div>
                  <?php endif; ?>

                  <?php if (empty($campos_obrigatorios) || in_array('email', $campos_obrigatorios)): ?>
                    <div class="relative">
                      <label class="block text-gray-700 dark:text-gray-300 mb-2">E-mail</label>
                      <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" <?php echo in_array('email', $campos_obrigatorios) ? 'required' : ''; ?>
                          class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                          placeholder="Digite seu e-mail">
                      </div>
                    </div>
                  <?php endif; ?>

                  <?php if (in_array('cpf', $campos_obrigatorios)): ?>
                    <div class="relative">
                      <label class="block text-gray-700 dark:text-gray-300 mb-2">CPF</label>
                      <div class="relative">
                        <i class="fas fa-id-card absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="cpf" required
                          class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                          placeholder="Digite seu CPF">
                      </div>
                    </div>
                  <?php endif; ?>

                  <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold transition-colors">
                    Cadastrar
                  </button>
                </form>

                <div class="text-center mt-4">
                  <button onclick="voltarParaLogin()"
                    class="text-blue-500 hover:text-blue-600 text-sm font-medium transition-colors">
                    Já tenho cadastro, fazer login
                  </button>
                </div>
              </div>
            </div>


            <!-- Botões de Navegação -->
            <div class="flex justify-between mt-4">
              <button onclick="voltarParaCompra()"
                class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
              </button>

            </div>
          </div>


        </div>
        </>

    </main>
    <?php
  } elseif ($campanhas[0]["layout"] == 2) { ?>

    <main class="container mx-auto px-0 py-8">
      <section class="w-full md:w-4/5 lg:w-3/5 mx-auto relative -top-12" style="margin-bottom: 0px;">
        <style>
          /* Layout 2 – ajustes visuais estilo Fideliz */
          .l2-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .2)
          }

          .l2-btn {
            background: #1BC863;
            color: #fff;
            border-radius: 10px
          }

          .l2-btn:hover {
            background: #43996d
          }

          .l2-desc {
            background: #3F3F46;
            color: #b8b9ad;
            border-radius: 10px;
            padding: 8px 10px;
            font-weight: 600
          }

          .l2-meta {
            color: #b8b9ad;
            font-weight: 600
          }

          input[type=range].l2-range {
            appearance: none;
            height: 12px;
            border-radius: 10px;
            outline: none;
            background: linear-gradient(#22c55e 0 0) no-repeat #263238;
            background-size: var(--_fill, 0%) 100%
          }

          input[type=range].l2-range::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            background: #0a0a0a;
            border-radius: 50%;
            border: 3px solid #22c55e;
            box-shadow: 0 0 0 2px #0a0a0a
          }

          input[type=range].l2-range::-moz-range-thumb {
            width: 20px;
            height: 20px;
            background: #0a0a0a;
            border: 3px solid #22c55e;
            border-radius: 50%
          }

          /* Pacotes - estilo referência */
          .l2-pack {
            background: #3F3F46;
            border: 2px solid #4B5563;
            border-radius: 10px;
            padding: 12px 10px;
            position: relative
          }

          .l2-pack .l2-qtd {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            line-height: 1
          }

          .l2-pack .l2-price {
            font-size: 15px;
            color: #E5E7EB;
            font-weight: 700;
            margin-top: 4px
          }

          .l2-pack .l2-divider {
            height: 1px;
            background: #9CA3AF;
            opacity: .6;
            margin: 8px 0
          }

          .l2-pack .l2-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #6B7280;
            color: #fff;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 800
          }

          .l2-pack .l2-pill .ico {
            font-size: 18px;
            line-height: 1
          }

          .l2-pop {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #22c55e;
            color: #fff;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 800
          }

          .l2-buybar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px
          }

          .l2-buybar .l2-buybtn {
            white-space: nowrap;
            padding: 10px 14px;
            border-radius: 12px;
            background: #1BC863;
            color: #fff;
            font-weight: 800
          }
        </style>
        <div class="bg-gray-100 dark:bg-[#27272A] rounded-2xl shadow-lg overflow-hidden l2-card">
          <!-- Capa/Galeria -->

          <div class="px-4 py-3 bg-white dark:bg-[#27272A] secao-compra">
            <h2 class="text-[15px] font-bold text-green-500 uppercase text-center mb-[6px]">
              <span class="flex items-center justify-start"><img src="assets/css/estrela.svg" alt="estrela"
                  style="width: 26px;margin-right:5px;"><?php echo $campanhas[0]['nome']; ?></span>
            </h2>


          </div>
          <div class="relative w-full overflow-hidden">
            <div id="carousel" class="flex transition-transform duration-500 ease-in-out">
              <?php foreach ($imagens as $key => $imagem): ?>
                <div class="min-w-full">
                  <img src="<?php echo $imagem; ?>" alt="<?php echo $campanhas[0]['nome']; ?>" class="img-campanha-16-9">
                </div>
              <?php endforeach; ?>
            </div>
            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
              <?php foreach ($imagens as $key => $imagem): ?>
                <button class="indicator w-3 h-3 bg-white rounded-full opacity-50"
                  data-index="<?php echo $key; ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Ações -->
          <div class="flex justify-start gap-2 px-4 pb-3 bg-white dark:bg-[#27272A] secao-compra mt-2">
            <?php if (!$modo_exclusivo && $config['habilitar_compartilhamento'] == '1'): ?>
              <button onclick="compartilharCampanha()"
                class="flex-1 sm:flex-none sm:w-[35%] md:w-[30%] l2-btn py-1.5 rounded-lg flex items-center justify-center gap-2">
                <i class="fas fa-share text-sm"></i><span class="text-[12px] sm:text-sm">Compartilhar</span>
              </button>
            <?php endif; ?>
            <?php if (!empty($campanhas[0]['url_regulamento'])): ?>
              <a href="<?php echo $campanhas[0]['url_regulamento']; ?>" target="_blank"
                class="flex-1 sm:flex-none sm:w-[35%] md:w-[30%]">
                <div
                  class="cursor-pointer bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-lg py-1.5 px-2 flex items-center justify-center">
                  <span class="text-black dark:text-white text-[12px] sm:text-sm font-medium">Regulamento</span>
                </div>
              </a>
            <?php endif; ?>
          </div>
          <?php if ($campanhas[0]['habilitar_barra_progresso'] == '1'): ?>
            <div class="mt-4 mx-4 secao-compra">
              <div class="w-full bg-gray-300 dark:bg-gray-500 rounded-full h-6 mb-2 relative overflow-hidden">
                <div class="bg-[#1BC863] h-6 rounded-full progress-bar-animated relative"
                  style="width: <?php echo $porcentagem_vendida; ?>%"></div>
                <div class="absolute inset-0 flex items-center justify-center text-white text-sm w-full">
                  <span><?php echo number_format($porcentagem_vendida, 1); ?>% vendido</span>
                  <?php if ($campanhas[0]['ativar_progresso_manual'] != '1'): ?>
                    <span class="ml-2"><?php echo $soma_todos_numeros_vendidos; ?>/<?php echo $quantidade_total; ?>
                      números</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
          <?php if (!empty($campanhas[0]['descricao'])): ?>
            <div class="mt-2 l2-desc text-center text-[12px] mx-4 bg-[#4B5563]  secao-compra">
              <?php echo $campanhas[0]['descricao']; ?>
            </div>
          <?php endif; ?>

         



          <!-- Quantidade + Slider -->
          <div class="px-4 pb-4 bg-gray-100 dark:bg-[#27272A] secao-compra">
              <!-- Custom Quantidade Seletor -->
          <div
            class="flex items-center justify-center gap-2 mt-4 mb-2 w-full px-4 bg-gray-100 dark:bg-[#27272A] p-2 rounded-2xl flex-col border border-gray-300 dark:border-[#000000] secao-compra">
            <div class="flex items-center space-x-2">
              <button onclick="alterarQuantidade(-5,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">-5</button>
              <button onclick="alterarQuantidade(-1,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">-</button>

              <input type="number" id="quantidade_barra" value="<?php echo $compra_minima; ?>"
                class="flex-1 text-center bg-gray-100 dark:bg-[#3F3F46] text-black dark:text-white rounded-lg border-none"
                readonly>

              <button onclick="alterarQuantidade(1,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">+</button>
              <button onclick="alterarQuantidade(5,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">+5</button>
            </div>


            <div style="display: none;" class="text-red-500 flex justify-between text-xs text-gray-400 mt-2">
              <span></span>
              <span>Máximo: <?php echo $compra_maxima; ?></span>
            </div>

          </div>
            <div class="my-6 flex items-center gap-2">
              <span class="text-xs text-gray-300"><?php echo $compra_minima; ?></span>
              <input id="quantidade_slider" type="range" min="<?php echo $compra_minima; ?>"
                max="<?php echo $compra_maxima; ?>" step="1" value="<?php echo $compra_minima; ?>"
                class="flex-1 l2-range">
              <span class="text-xs text-gray-300"><?php echo $compra_maxima; ?></span>
            </div>
            <div class="hidden text-red-500 text-right text-xs mt-1" id="msgMaximoLayout2">Máximo:
              <?php echo $compra_maxima; ?>
            </div>
          </div>

          <!-- Adição Rápida -->
          <?php if (!$modo_exclusivo && $campanhas[0]['habilitar_adicao_rapida'] == '1'): ?>
            <h3 class="text-lg font-bold text-green-500 uppercase text-center mb-[6px]"></h3>Adição Rápida</h3>
            <div class="px-4 pb-4 bg-gray-100 dark:bg-[#27272A] secao-compra">
              <div class="grid grid-cols-3 gap-2">
                <?php $incrementos = [50, 100, 250, 500, 1000];
                foreach ($incrementos as $valor): ?>
                  <button onclick="alterarQuantidade(<?php echo $valor; ?>, true)"
                    class="bg-green-600 hover:bg-green-700 text-white py-2 rounded">+<?php echo $valor; ?></button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <!-- Pacotes padrão -->
          <?php if ($campanhas[0]['habilitar_pacote_promocional'] == '1' && !isset($_REQUEST['cx'])): ?>
            <div class="px-4 pb-4 bg-gray-100 dark:bg-[#27272A] secao-compra">
              <?php
              $pacotes_promocionais = json_decode($campanhas[0]['pacote_promocional'], true);
              $grid_cols = (is_array($pacotes_promocionais) && count($pacotes_promocionais) > 1) ? 'grid-cols-2' : 'grid-cols-1';
              echo "<div class='grid {$grid_cols} gap-2'>";
              if (is_array($pacotes_promocionais) && !empty($pacotes_promocionais)) {
                foreach ($pacotes_promocionais as $pacote) {
                  $qtd = intval($pacote['quantidade_numeros']);
                  $valor_original = $qtd * $campanhas[0]['preco'];
                  $destaque = isset($pacote['destaque']) && $pacote['destaque'] == '1';
                  $beneficio_tipo = isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '';
                  $beneficio_qtd = isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0;
                  echo "<button onclick=\"alterarQuantidadePromocional($qtd, " . $pacote['valor_pacote'] . ", false, '" . ($beneficio_tipo) . "', " . ($beneficio_qtd) . ")\" class='relative l2-pack text-center'>";
                  if ($destaque)
                    echo "<span class='l2-pop'>Mais popular</span>";
                  echo "<div class='l2-qtd'>{$qtd}</div>";
                  echo "<div class='l2-price'>R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . "</div>";
                  echo "<div class='l2-divider'></div>";
                  if (!empty($beneficio_tipo) && $beneficio_qtd > 0) {
                    $label = $beneficio_tipo === 'roleta' ? ($beneficio_qtd . ' Giro' . ($beneficio_qtd > 1 ? 's' : '')) : ($beneficio_qtd . ' Raspadinh' . ($beneficio_qtd > 1 ? 'as' : 'a'));
                    echo "<span class='l2-pill'><span class='ico'>🎰</span> {$label}</span>";
                  } else {
                    echo "<span class='l2-pill'><span class='ico'>🎰</span> 0 Giros</span>";
                  }
                  echo "</button>";
                }
              } else {
                echo "<p class='text-sm text-gray-400'>Nenhum pacote promocional disponível no momento.</p>";
              }
              echo "</div>";
              ?>
            </div>
          <?php endif; ?>

          <!-- Comprar -->
          <div class="px-4 pb-4 bg-gray-100 dark:bg-[#27272A] secao-compra">
            <div class="flex justify-between text-xs l2-meta mb-2">
              <span>Preço Promocional: <span class="text-green-400">R$
                  <?php echo number_format($campanhas[0]['preco'], 2, ',', '.'); ?></span></span>
              <span>Giro(s) na Roleta: <span id="infoGirosLayout2">0</span></span>
            </div>
            <form action="processamento_pagamento.php" method="POST" id="formComprar"
              onsubmit="return validarCompra(event)" class="secao-compra">
              <input type="hidden" name="campanha_id" value="<?php echo $id; ?>">
              <input type="hidden" name="valor_total" id="valor_total"
                value="<?php echo $campanhas[0]['preco'] * $compra_minima; ?>">
              <input type="hidden" name="cliente_id" id="cliente_id" value="">
              <input type="hidden" name="quantidade" id="quantidade" value="<?php echo $compra_minima; ?>">
              <input type="hidden" name="numeros_solicitados" id="numeros_solicitados" value="">
              <input type="hidden" name="nome_produto" value="<?php echo htmlspecialchars($campanhas[0]['nome']); ?>">
              <input type="hidden" name="codigo_afiliado" value="<?php echo $codigo_afiliado; ?>">
              <div class="l2-buybar">
                <div class="flex-1"></div>
                <button id="btnComprar" onclick="verificarLogin('comprar')" class="l2-buybtn">
                  Comprar por R$ <?php echo number_format($campanhas[0]['preco'] * $compra_minima, 2, ',', '.'); ?>
                </button>
              </div>
            </form>
            <div class="w-full flex flex-col gap-2 mt-2 secao-compra">
              <button onclick="verificarLogin('titulos')"
                class="border border-[#1BC863] text-[#1BC863] rounded-lg font-semibold px-4 py-2 flex items-center justify-center gap-2 transition-all">
                <span class="iconify" data-icon="tabler:numbers"></span> Meus Títulos
              </button>
            </div>
          </div>

          <!-- Sistema de Login/Cadastro Inline (Layout 2) -->
          <div id="loginCadastroContainerLayout1" class="hidden w-full login-container">
            <!-- Sistema de Login -->
            <div class="rounded-lg shadow-lg p-6 mb-6 bg-gray-100 dark:bg-[#27272A]">
              <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Digite seu número</h3>
              <!-- Formulário de Login -->
              <form id="formLoginInline" class="space-y-4 mb-6">
                <div class="relative">
                  <label class="block text-gray-700 dark:text-gray-300 mb-2">Número de celular</label>
                  <div class="relative">
                    <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="tel" name="telefone" required
                      class="w-full bg-gray-200 dark:bg-[#27272A] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                      placeholder="(11) 99999-9999">
                  </div>
                </div>
                <button type="submit"
                  class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold transition-colors">Entrar</button>
              </form>
              <div class="text-center">
                <button onclick="mostrarCadastro()"
                  class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">Primeira
                  vez? Cadastre-se</button>
              </div>
              <!-- Resumo do Pedido -->
              <!-- <div class="mt-6 p-4 bg-green-600 rounded-lg">
                <div class="text-white text-center">
                  <p class="font-bold">Valor do pedido: R$ <span id="valorPedidoLoginLayout1">0,00</span></p>
                </div>
              </div> -->
              <!-- Termos de Uso -->
              <div class="mt-4 p-3 bg-orange-500 rounded-lg">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <input type="checkbox" id="aceitarTermosLayout1" class="mr-2">
                    <label for="aceitarTermosLayout1" class="text-white text-sm">Aceito os termos de uso</label>
                  </div>
                  <button type="button" onclick="verTermosUso()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors">Ver
                    Termos de Uso</button>
                </div>
              </div>
              <!-- Mensagem de Erro -->
              <div id="mensagemErroLayout1" class="hidden mt-4 p-3 bg-red-500 rounded-lg">
                <p class="text-white text-sm text-center">Para prosseguir você deve aceitar Termos de Uso.</p>
              </div>
            </div>

            <!-- Formulário de Cadastro Inline -->
            <div id="cadastroContainerLayout1" class="hidden w-full">
              <div class="rounded-lg shadow-lg p-6 mb-6 bg-gray-100 dark:bg-[#27272A]">
                <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Cadastro</h3>
                <form id="formCadastroInline" class="space-y-4">
                  <div class="relative">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2"><i
                        class="fas fa-phone mr-2"></i>Telefone</label>
                    <div class="relative">
                      <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                      <input type="tel" name="telefone" required
                        class="w-full bg-gray-200 dark:bg-[#27272A] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                        placeholder="(11) 99999-9999">
                    </div>
                  </div>
                  <?php if (empty($campos_obrigatorios) || in_array('nome', $campos_obrigatorios)): ?>
                    <div class="relative">
                      <label class="block text-gray-700 dark:text-gray-300 mb-2">Nome Completo</label>
                      <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="nome" <?php echo in_array('nome', $campos_obrigatorios) ? 'required' : ''; ?>
                          class="w-full bg-gray-200 dark:bg-[#27272A] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                          placeholder="Digite seu nome completo">
                      </div>
                    </div>
                  <?php endif; ?>
                  <?php if (empty($campos_obrigatorios) || in_array('email', $campos_obrigatorios)): ?>
                    <div class="relative">
                      <label class="block text-gray-700 dark:text-gray-300 mb-2">E-mail</label>
                      <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" <?php echo in_array('email', $campos_obrigatorios) ? 'required' : ''; ?>
                          class="w-full bg-gray-200 dark:bg-[#27272A] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                          placeholder="Digite seu e-mail">
                      </div>
                    </div>
                  <?php endif; ?>
                  <?php if (in_array('cpf', $campos_obrigatorios)): ?>
                    <div class="relative">
                      <label class="block text-gray-700 dark:text-gray-300 mb-2">CPF</label>
                      <div class="relative">
                        <i class="fas fa-id-card absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="cpf" required
                          class="w-full bg-gray-200 dark:bg-[#27272A] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                          placeholder="Digite seu CPF">
                      </div>
                    </div>
                  <?php endif; ?>
                  <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold transition-colors">Cadastrar</button>
                </form>
                <div class="text-center mt-4">
                  <button onclick="voltarParaLogin()"
                    class="text-blue-500 hover:text-blue-600 text-sm font-medium transition-colors">Já tenho cadastro,
                    fazer login</button>
                </div>
              </div>
            </div>

            <!-- Botões de Navegação -->
            <div class="flex justify-between mt-4">
              <button onclick="voltarParaCompra()"
                class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
              </button>
            </div>
          </div>

          <!-- Confirmação de Compra (sessão inline para todos os layouts) -->
          <section id="confirmacaoCompraContainer" class="hidden w-full">
            <div class="rounded-lg shadow-lg p-6 bg-gray-100 dark:bg-[#27272A]">
              <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Confirmação da compra</h3>

              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-2 text-gray-800 dark:text-gray-100 items-center">
                  <div class="font-semibold">Produto</div>
                  <div id="confNome" class="text-right pr-2"></div>
                  <div class="font-semibold">Quantidade</div>
                  <div id="confQtd" class="text-right pr-2"></div>
                  <div class="font-semibold">Preço unitário</div>
                  <div class="text-right pr-2">R$ <span id="confPreco"></span></div>
                  <div class="font-semibold">Benefícios</div>
                  <div id="confBeneficios" class="text-right pr-2">-</div>
                </div>

                <div class="p-4 bg-green-600 rounded-lg text-white text-center">
                  <p class="font-bold">Valor do pedido: R$ <span id="confTotal">0,00</span></p>
                </div>
              </div>

              <div class="mt-4 flex justify-between items-center">
                <button id="btnConfCancelar"
                  class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
                  <i class="fas fa-arrow-left mr-2"></i>
                  Voltar
                </button>
                <button id="btnConfConfirmar"
                  class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-bold transition-colors">
                  Confirmar e pagar
                </button>
              </div>
            </div>
          </section>




          <!-- Cotas premiadas -->
          <?php
          if (!empty($campanhas[0]['mostrar_cotas_premiadas']) && !empty($campanhas[0]['cotas_premiadas'])):
            $premios_json = json_decode($campanhas[0]['premio_cotas_premiadas'], true);
            if (is_array($premios_json) && !empty($premios_json)): ?>
              <div class="w-full px-4 pb-4 bg-gray-100 dark:bg-[#27272A] secao-compra">
                <p class="text-sm font-medium text-black dark:text-white mb-2">🔥 Cotas premiadas - Achou ganhou!</p>
                <div class="grid grid-cols-3 md:grid-cols-3 gap-2">
                  <?php
                  foreach ($premios_json as $grupo) {
                    foreach ($grupo['cotas'] as $cota) {
                      $cota_formatada = formatarCotaComLargura($cota, $largura_cota);
                      echo "<div class='bg-blue-600 text-white p-2 rounded-lg text-center min-h-[64px]'>";
                      echo "<div class='flex items-center justify-center mb-1'><span class='text-green-400 mr-2'>★</span><span class='text-base font-bold'>{$cota_formatada}</span></div>";
                      echo "<div class='text-xs font-bold'>" . htmlspecialchars($grupo['premio']) . "</div>";
                      echo "</div>";
                    }
                  }
                  ?>
                </div>
                <p class="text-sm text-gray-400 mt-2"><?php echo $campanhas[0]['descricao_cotas_premiadas']; ?></p>
              </div>
            <?php endif; endif; ?>

        </div>
      </section>
    </main>

    <script>
      (function () {
        const carousel = document.getElementById('carousel');
        const indicators = document.querySelectorAll('.indicator');
        let index = 0;
        function update() {
          if (!carousel) return;
          carousel.style.transform = `translateX(-${index * 100}%)`;
          indicators.forEach((dot, i) => dot.classList.toggle('opacity-100', i === index));
        }
        indicators.forEach(dot => { dot.addEventListener('click', e => { index = parseInt(e.target.dataset.index); update(); }); });
      })();

      (function () {
        const precoUnitario = <?php echo $campanhas[0]['preco']; ?>;
        const compraMinima = <?php echo $compra_minima; ?>;
        const compraMaxima = <?php echo $compra_maxima; ?>;
        const slider = document.getElementById('quantidade_slider');
        const inputQtdBarra = document.getElementById('quantidade_barra');
        const inputQtd = document.getElementById('quantidade');
        const inputValor = document.getElementById('valor_total');
        const btnComprar = document.getElementById('btnComprar');
        const infoGiros = document.getElementById('infoGirosLayout2');
        function atualizarTrack() {
          if (!slider) return;
          const min = parseInt(slider.min, 10);
          const max = parseInt(slider.max, 10);
          const val = parseInt(slider.value, 10);
          const fill = ((val - min) * 100) / (max - min);
          slider.style.setProperty('--_fill', fill + '%');
        }
        function aplicar(qtd) {
          let novaQuantidade = Math.max(compraMinima, Math.min(compraMaxima, parseInt(qtd || compraMinima, 10)));
          if (slider) slider.value = novaQuantidade;
          atualizarTrack();
          if (inputQtdBarra) inputQtdBarra.value = novaQuantidade;
          if (inputQtd) inputQtd.value = novaQuantidade;
          const valorTotal = precoUnitario * novaQuantidade;
          if (inputValor) inputValor.value = valorTotal;
          if (btnComprar) btnComprar.innerHTML = 'Comprar por R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          const msg = document.getElementById('msgMaximoLayout2');
          if (msg) msg.style.display = novaQuantidade >= compraMaxima ? 'block' : 'none';
          if (infoGiros) infoGiros.textContent = '0';
        }
        if (slider) { slider.addEventListener('input', e => aplicar(e.target.value)); atualizarTrack(); }
        if (typeof window.alterarQuantidade !== 'function') {
          window.alterarQuantidade = function (qtd, adicionar) {
            const base = parseInt(inputQtdBarra?.value || compraMinima, 10);
            aplicar(adicionar ? base + parseInt(qtd, 10) : qtd);
          }
        }
        if (typeof window.quantidadeRapida !== 'function') {
          window.quantidadeRapida = function (qtd) { aplicar(qtd); }
        }
        if (typeof window.alterarQuantidadePromocional !== 'function') {
          window.alterarQuantidadePromocional = function (qtd, precoPacote, adicionar, beneficioTipo, beneficioQuantidade) {
            const quantidadeFinal = adicionar ? (parseInt(inputQtdBarra?.value || 0, 10) + parseInt(qtd, 10)) : parseInt(qtd, 10);
            if (slider) slider.value = quantidadeFinal;
            if (inputQtdBarra) inputQtdBarra.value = quantidadeFinal;
            if (inputQtd) inputQtd.value = quantidadeFinal;
            if (inputValor) inputValor.value = precoPacote;
            if (btnComprar) btnComprar.innerHTML = 'Comprar por R$ ' + Number(precoPacote).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            let girosInput = document.getElementById('quantidade_giros_roleta');
            if (!girosInput) { girosInput = document.createElement('input'); girosInput.type = 'hidden'; girosInput.id = 'quantidade_giros_roleta'; girosInput.name = 'quantidade_giros_roleta'; document.getElementById('formComprar').appendChild(girosInput); }
            girosInput.value = 0;
            let raspadinhasInput = document.getElementById('quantidade_raspadinhas');
            if (!raspadinhasInput) { raspadinhasInput = document.createElement('input'); raspadinhasInput.type = 'hidden'; raspadinhasInput.id = 'quantidade_raspadinhas'; raspadinhasInput.name = 'quantidade_raspadinhas'; document.getElementById('formComprar').appendChild(raspadinhasInput); }
            raspadinhasInput.value = 0;
            const qtdBeneficio = parseInt(beneficioQuantidade || 0, 10);
            if (beneficioTipo === 'roleta' && qtdBeneficio > 0) { girosInput.value = qtdBeneficio; if (infoGiros) infoGiros.textContent = String(qtdBeneficio); }
            else if (beneficioTipo === 'raspadinha' && qtdBeneficio > 0) { raspadinhasInput.value = qtdBeneficio; if (infoGiros) infoGiros.textContent = '0'; }
          }
        }
        aplicar(inputQtdBarra?.value);
      })();
    </script>

  <?php } elseif ($campanhas[0]["layout"] == 3) { ?>
    <!-- Main Content -->
    <main class="container mx-auto px-0 py-8">
      <section class=" w-full md:w-4/5 lg:w-3/5 mx-auto relative -top-12" style=" margin-bottom: 0px;">

        <div class="bg-white dark:bg-[#27272A] rounded-2xl shadow-lg w-full p-0 flex flex-col items-center relative">
          <div class="relative w-full max-w-3xl mx-auto overflow-hidden rounded-lg">
            <div id="carousel" class="flex transition-transform duration-500 ease-in-out">
              <?php
              foreach ($imagens as $key => $imagem): ?>
                <div class="min-w-full">
                  <img src="<?php echo $imagem; ?>" alt="<?php echo $campanhas[0]['nome']; ?>" class="img-campanha-16-9">
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Indicadores -->
            <div class="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-2">
              <?php foreach ($imagens as $key => $imagem): ?>
                <button class="indicator w-3 h-3 bg-white rounded-full opacity-50"
                  data-index="<?php echo $key; ?>"></button>
              <?php endforeach; ?>
            </div>

            <!-- Overlay de título/sub sobre imagem (estilo referência) -->
            <style>
              .l3-hero-overlay {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                padding: 10px 14px;
                background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, .6) 55%, rgba(0, 0, 0, .85) 100%)
              }

              .l3-hero-badge {
                display: inline-block;
                background: #FACC15;
                color: #111217;
                font-weight: 700;
                font-size: 11px;
                border-radius: 4px;
                padding: 2px 8px;
                margin-bottom: 6px
              }

              .l3-hero-title {
                color: #fff;
                font-weight: 800;
                font-size: 16px;
                line-height: 1.2;
                margin: 0
              }

              .l3-hero-sub {
                color: #b8b9ad;
                font-size: 11px;
                margin-top: 2px
              }
            </style>
            <div class="l3-hero-overlay">
              <span class="l3-hero-badge">MAIS UMA RECORDE AQUI 🙏🏼</span>
              <h2 class="l3-hero-title"><?php echo isset($campanhas[0]['nome']) ? $campanhas[0]['nome'] : ''; ?></h2>
              <div class="l3-hero-sub"><?php echo isset($campanhas[0]['subtitulo']) ? $campanhas[0]['subtitulo'] : '-'; ?>
              </div>
            </div>
          </div>

          <script>
            const carousel = document.getElementById("carousel");
            const prev = document.getElementById("prev");
            const next = document.getElementById("next");
            const indicators = document.querySelectorAll(".indicator");
            let index = 0;

            function updateCarousel() {
              carousel.style.transform = `translateX(-${index * 100}%)`;
              indicators.forEach((dot, i) => {
                dot.classList.toggle("opacity-100", i === index);
              });
            }

            prev.addEventListener("click", () => {
              index = index > 0 ? index - 1 : indicators.length - 1;
              updateCarousel();
            });

            next.addEventListener("click", () => {
              index = index < indicators.length - 1 ? index + 1 : 0;
              updateCarousel();
            });

            indicators.forEach(dot => {
              dot.addEventListener("click", (e) => {
                index = parseInt(e.target.dataset.index);
                updateCarousel();
              });
            });
          </script>

          <!-- Bloco de progresso/valor estilo referência -->
          <div class="w-full ">
            <?php
            // Verifica se é um cliente (pode ver títulos) ou usuário do sistema (não pode ver títulos)
            $isCliente = isset($_SESSION['usuario']['cliente_id']);
            $isUsuarioSistema = isset($_SESSION['usuario']['usuario_id']);

            if (!isset($_SESSION['usuario']) || $isCliente) {
              ?>
              <!-- Botão "Ver meus números" e valor -->
              <div class="w-full flex flex-col gap-2 secao-compra">
                <button onclick="verificarLogin('titulos')"
                  class="bg-black text-white font-semibold px-4 py-2 flex items-center justify-center ">
                  <span class="iconify" data-icon="tabler:numbers"></span>
                  Ver meus números
                </button>
              </div>
            </div>
            <?php
            } ?>


          <div class="w-full bg-[#0f1216] h-6 relative secao-compra">
            <div class="bg-[#FACC15] h-6" style="width: <?php echo $porcentagem_vendida; ?>%"></div>
            <div class="absolute inset-0 flex items-center justify-center text-white text-xs font-bold"
              style="text-shadow: 0 1px 2px rgba(0,0,0,.6)">
              <?php echo number_format($porcentagem_vendida, 1); ?>%
            </div>
          </div>


        </div>


        <div class="bg-white dark:bg-[#27272A] shadow-lg w-full p-0 md:p-4 flex flex-col items-center relative p-2">
          <!--border-[#2e3947] border-4-->


          <div class="flex justify-start w-full secao-compra">
            <?php if (!$modo_exclusivo && $config['habilitar_compartilhamento'] == '1'): ?>
              <button onclick="compartilharCampanha()"
                class="flex-1 sm:flex-none sm:w-[35%] md:w-[30%] bg-[#FACC15] hover:bg-[#e0b90f] text-black py-1.5 rounded-lg flex items-center justify-center gap-1 sm:gap-2 mr-2 mb-4">
                <i class="fas fa-share text-xs sm:text-sm"></i>
                <span class="text-[11px] sm:text-sm whitespace-nowrap">Compartilhar</span>
              </button>
            <?php endif; ?>

            <!-- Botão de Grupo -->
            <?php if ($config['habilitar_grupos'] == '1'): ?>
              <a href="<?php echo $config['link_grupo']; ?>" target="_blank"
                class="flex-1 sm:flex-none sm:w-[35%] md:w-[30%]">
                <div
                  class="cursor-pointer bg-[#FACC15] hover:bg-[#e0b90f] rounded-lg py-1.5 px-2 flex items-center justify-center">
                  <div class="bg-black rounded-full p-0.5 sm:p-1 mr-1 sm:mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-[#FACC15]" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                  </div>
                  <span class="text-black font-medium text-[11px] sm:text-sm whitespace-nowrap">Entrar no Grupo</span>
                </div>
              </a>
            <?php endif; ?>
          </div>

          <div class="flex flex-row items-center gap-2 justify-center mb-4 secao-compra">
            <span class="text-gray-600 dark:text-[#b8b9ad] font-medium">por apenas</span>
            <span class="bg-[#111217] rounded px-2 py-1 text-white font-bold">R$ <?php
            echo isset($campanhas[0]['preco']) ? $campanhas[0]['preco'] : "-";
            ?></span>
          </div>

          <!-- Chamada promocional -->
          <div class="w-full mt-2 text-center secao-compra">
            <span
              class="block font-semibold text-gray-700 dark:text-[#b8b9ad] bg-gray-100  dark:bg-[#3F3F46] rounded-2xl p-4">
              <?php
              echo isset($campanhas[0]["descricao"]) ? $campanhas[0]["descricao"] : '';
              ?>
            </span>
          </div>

          <!-- Notificação de Cotas em Dobro -->
          <?php if ($campanhas[0]['habilitar_cotas_em_dobro'] == '1'): ?>
            <div class="secao-compra my-4 w-full">
              <div style="display: flex;justify-content: center;padding: 10px;"
                class=" bg-yellow-600 text-white rounded-lg flex items-center gap-2">
                <span class="text-3xl" style="padding: 0px 5px 0px 10px;">🎉</span>
                <div>
                  <p class="font-bold">
                    <?php echo !empty($campanhas[0]['titulo_cotas_dobro']) ? $campanhas[0]['titulo_cotas_dobro'] : 'COTAS EM DOBRO ATIVADAS!'; ?>
                  </p>
                  <p class="text-md">
                    <?php echo !empty($campanhas[0]['subtitulo_cotas_dobro']) ? $campanhas[0]['subtitulo_cotas_dobro'] : 'Aproveite! Todas as cotas estão valendo em dobro.'; ?>
                  </p>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <!-- Oferta Exclusiva -->
          <?php if ($modo_exclusivo): ?>
            <div class="secao-compra my-4 w-full">
              <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md">
                <p class="text-sm text-purple-800 dark:text-purple-200">
                  <span class="font-bold">⭐ Oferta Exclusiva!123</span><br>
                  Compre com desconto: Condição especial de pacotes por tempo LIMITADO! Não perca essa oportunidade de
                  aumentar suas chances de ganhar!
                </p>
              </div>
            </div>
          <?php endif; ?>
          <!-- Pacotes exclusivos -->
          <?php
          if ($campanhas[0]['habilita_pacote_promocional_exclusivo'] == '1' && $modo_exclusivo): ?>
            <div class="secao-compra my-4 w-full">
              <p class="text-sm font-medium text-black dark:text-white">Pacotes Exclusivos</p>
              <?php
              $pacotes_promocionais_exclusivos = $campanhas[0]['pacotes_exclusivos'];
              $pacotes_promocionais_exclusivos = json_decode($pacotes_promocionais_exclusivos, true);

              if (is_array($pacotes_promocionais_exclusivos) && !empty($pacotes_promocionais_exclusivos && $codigo_exclusivo != 0)) {
                $grid_cols = count($pacotes_promocionais_exclusivos) > 1 ? 'grid-cols-2' : 'grid-cols-1';
                echo '<div class="grid mt-2 text-center ' . $grid_cols . ' gap-2">';

                foreach ($pacotes_promocionais_exclusivos as $pacote) {
                  if ($codigo_exclusivo == $pacote['codigo_pacote']) {
                    $qtd = intval($pacote['quantidade_numeros']);
                    $valor_original = $qtd * $campanhas[0]['preco'];
                    $beneficio_tipo = isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '';
                    $beneficio_qtd = isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0;
                    $beneficio_html = '';
                    if (!empty($beneficio_tipo) && $beneficio_qtd > 0) {
                      $badge_start = "<div class='mt-2 flex justify-center'><span class='inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-yellow-300/20 dark:bg-yellow-500/20 text-yellow-100 dark:text-yellow-200 ring-1 ring-yellow-300/40 dark:ring-yellow-500/40'>";
                      $badge_end = "</span></div>";
                      if ($beneficio_tipo === 'roleta') {
                        $beneficio_html = $badge_start . "<i class='fa-solid fa-coins'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Giros' : 'Giro') . $badge_end;
                      } elseif ($beneficio_tipo === 'raspadinha') {
                        $beneficio_html = $badge_start . "<i class='fa-solid fa-ticket'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Raspadinhas' : 'Raspadinha') . $badge_end;
                      }
                    }
                    echo "<button onclick=\"alterarQuantidadePromocional($qtd, " . $pacote['valor_pacote'] . ", false, '" . ($beneficio_tipo ?? '') . "', " . ($beneficio_qtd ?? 0) . ")\" class='bg-purple-600 text-white py-2 rounded text-center'>
                            <span class='block text-lg font-bold'>$qtd números</span>
                            <span class='block text-red-300 text-sm mb-1 line-through'>R$ " . number_format($valor_original, 2, ',', '.') . "</span>
                            <span class='block text-green-300 text-lg font-bold'>R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . "</span>" .
                      $beneficio_html .
                      "</button>";
                  }
                }
                echo '</div>';
              } else {
                echo "<p class='text-sm text-gray-400'>Nenhum pacote exclusivo disponível no momento.</p>";
              }
              ?>
            </div>
          <?php endif; ?>




          <!-- Barra de promoções -->
          <div class="flex mt-2 items-center w-full secao-compra">
            <h1 class="text-1xl md:text-3xl font-bold mb-1">
              📣 Promoção
            </h1>
            <span class="flex justify-center items-center ml-2">Compre mais barato!</span>
          </div>


          <div
            class="w-full px-4 mt-3 flex flex-wrap gap-2 justify-center bg-gray-100 dark:bg-[#3F3F46] rounded-2xl p-2 secao-compra">
            <?php
            $pacotes_promocionais_origin = $campanhas[0]['pacote_promocional'];
            $pacotes_promocionais_origin = json_decode($pacotes_promocionais_origin, true);

            // var_dump($pacotes_promocionais_origin);die;
          
            foreach ($pacotes_promocionais_origin as $pacote) {
              $qtd = intval($pacote['quantidade_numeros']);
              $valor_original = $qtd * $campanhas[0]['preco'];

              // Adicionando novos pacotes ao array
              $pacotes_promocionais[] = [
                "valor_bilhete" => $pacote['valor_bilhete'],
                "quantidade_numeros" => $pacote['quantidade_numeros'],
                "valor_pacote" => $pacote['valor_pacote'],
                "beneficio_tipo" => isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '',
                "beneficio_quantidade" => isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0
              ];
            }


            if (is_array($pacotes_promocionais) && !empty($pacotes_promocionais)) {
              foreach ($pacotes_promocionais as $pacote) {
                $qtd = intval($pacote['quantidade_numeros']);
                $valor_original = $qtd * $campanhas[0]['preco'];
                $beneficio_tipo = isset($pacote['beneficio_tipo']) ? $pacote['beneficio_tipo'] : '';
                $beneficio_qtd = isset($pacote['beneficio_quantidade']) ? (int) $pacote['beneficio_quantidade'] : 0;
                $beneficio_html = '';
                if (!empty($beneficio_tipo) && $beneficio_qtd > 0) {
                  $beneficio_html = "<br><span class='inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-yellow-400/15 dark:bg-yellow-500/20 text-yellow-600 dark:text-yellow-300 ring-1 ring-yellow-400/40 dark:ring-yellow-500/40'>" .
                    ($beneficio_tipo === 'roleta'
                      ? "<i class='fa-solid fa-coins'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Giros' : 'Giro')
                      : "<i class='fa-solid fa-ticket'></i> {$beneficio_qtd} " . ($beneficio_qtd > 1 ? 'Raspadinhas' : 'Raspadinha'))
                    . "</span>";
                }

                echo "<span style='background: #FACC15!important;color:#111217!important;' class='bg-[#FACC15] text-black pacote-promocional' 
                onclick=\"alterarQuantidadePromocional
                ($qtd, '" . $pacote['valor_pacote'] . "', false, '" . ($beneficio_tipo ?? '') . "', " . ($beneficio_qtd ?? 0) . ")\">$qtd por R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . $beneficio_html . "</span>";
              }
            } else {
              echo "<p class='text-sm text-gray-400'>Nenhum pacote promocional disponível no momento.</p>";
            }
            ?>
          </div>

          <style>
            .text-base {
              font-size: 0.75rem;
              /* Ajuste o valor conforme necessário */
            }

            .pacote-promocional {
              text-align: center;
              background-color: #FACC15;
              color: #111217;
              font-size: 0.75rem;
              /* 14px */
              padding: 0.25rem 0.75rem;
              /* 4px 12px */
              border-radius: 0.5rem;
              /* arredondamento de 8px */
              font-weight: 500;
              cursor: pointer;
              width: 23%;
              /* Aproximadamente 1/4 da largura */
              box-sizing: border-box;
            }

            /* Responsividade */
            @media (max-width: 768px) {
              .pacote-promocional {
                width: 43%;
                /* Cada item ocupará a largura total em telas pequenas */
              }
            }
          </style>




















          <!-- Seletores de Quantidade centralizados 3 em cima / 2 embaixo -->
          <div class="w-full mt-4 secao-compra">
            <div class="flex justify-center gap-2">
              <?php
              $linha1 = [5, 10, 100];
              foreach ($linha1 as $valor) {
                $isPopular = ($valor === 10);
                $bgClass = $isPopular ? "bg-[#FFFF8D]" : "bg-[#111217]";
                $textColor = $isPopular ? "text-[#111217]" : "text-white";
                $borderClass = "border border-[#343b41]";
                $hoverClass = $isPopular ? "hover:bg-[#FACC15]" : "hover:bg-[#232a32]";
                $extraClass = $isPopular ? "relative" : "";
                $selectTextColor = $isPopular ? "text-[#111217]" : "text-gray-300";
                echo "<button onclick=\"alterarQuantidade($valor, true)\" class='$bgClass $textColor py-4 rounded-xl $borderClass font-semibold $hoverClass transition borda-animada-amarela w-1/3 $extraClass'>"
                  . ($isPopular ? "<div style='width:75%;' class='absolute -top-3 left-1/2 transform -translate-x-1/2 bg-[#FACC15] text-[#111217] text-[10px] font-bold px-2 py-[2px] rounded-md'>Mais popular</div>" : "") .
                  "+$valor<br /><span class='text-xs $selectTextColor'>SELECIONAR</span></button>";
              }
              ?>
            </div>
            <div class="flex justify-center gap-2 mt-2">
              <?php
              $linha2 = [500, 1000];
              foreach ($linha2 as $valor) {
                $bgClass = "bg-[#111217]";
                echo "<button onclick=\"alterarQuantidade($valor, true)\" class='$bgClass text-white py-4 rounded-xl border border-[#343b41] font-semibold hover:bg-[#232a32] transition borda-animada-amarela w-1/3'>+${valor}<br /><span class='text-xs text-gray-300'>SELECIONAR</span></button>";
              }
              ?>
            </div>
          </div>

          <!-- Pacotes de Roleta removidos: uso passa a ser pacotes gerais da campanha -->
          <?php if (false): ?>
            <div class="mb-8 px-4 mt-2">
              <h3 class="text-2xl font-bold text-center mb-6 text-gray-800 dark:text-white">🎰 Pacotes de Roleta</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 max-w-7xl mx-auto">
                <?php foreach ($itens_roleta as $pacote): ?>
                  <div class="relative">
                    <?php if (isset($pacote['destaque']) && $pacote['destaque'] == '1'): ?>
                      <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-10">
                        <span class="bg-green-500 text-white px-4 py-1 rounded-full text-xs font-bold shadow-lg"
                          style="white-space: nowrap;">Mais Popular</span>
                      </div>
                    <?php endif; ?>
                    <div
                      class="bg-white dark:bg-[#27272A] border border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300 h-full flex flex-col justify-between <?php echo isset($pacote['destaque']) && $pacote['destaque'] == '1' ? 'ring-2 ring-green-500 ring-opacity-50' : ''; ?>">
                      <div class="flex-1">
                        <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
                          <?php echo htmlspecialchars(isset($pacote['quantidade_giros']) ? $pacote['quantidade_giros'] : ''); ?>
                        </div>
                        <div class="text-gray-600 dark:text-gray-300 text-lg mb-4">
                          Giros
                        </div>
                      </div>
                      <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400 mb-4">
                          R$
                          <?php echo number_format(isset($pacote['valor_pacote']) ? $pacote['valor_pacote'] : 0, 2, ',', '.'); ?>
                        </div>
                        <button type="button"
                          class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-200 transform hover:scale-105"
                          onclick="selecionarPacoteRoleta('<?php echo htmlspecialchars(isset($pacote['codigo_pacote']) ? $pacote['codigo_pacote'] : ''); ?>', <?php echo isset($pacote['valor_pacote']) ? $pacote['valor_pacote'] : 0; ?>, <?php echo isset($pacote['quantidade_giros']) ? $pacote['quantidade_giros'] : 0; ?>)">
                          Comprar
                        </button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>




          <div style="display: flex;justify-content: start;width: 100%;" class="mt-4 secao-compra">
            <label for="quantidade" class="block text-sm font-medium text-black dark:text-white secao-compra">
              Quantidade (Mín: <?php echo $compra_minima; ?>)
            </label>
          </div>

          <!-- Botão Quero Participar (novo layout com seletor ao lado) -->
          <div class="w-full px-4 mb-2 ">





            <form action="processamento_pagamento.php" method="POST" id="formComprar"
              onsubmit="return validarCompra(event)" class="secao-compra">
              <input type="hidden" name="campanha_id" value="<?php echo $id; ?>">
              <input type="hidden" name="valor_total" id="valor_total"
                value="<?php echo $campanhas[0]['preco'] * $compra_minima; ?>">
              <input type="hidden" name="cliente_id" id="cliente_id" value="">
              <input type="hidden" name="quantidade" id="quantidade" value="<?php echo $compra_minima; ?>">
              <input type="hidden" name="numeros_solicitados" id="numeros_solicitados" value="">
              <input type="hidden" name="nome_produto" value="<?php echo htmlspecialchars($campanhas[0]['nome']); ?>">
              <input type="hidden" name="codigo_afiliado" value="<?php echo $codigo_afiliado; ?>">
              <?php


              // Verifica se é um cliente (pode comprar) ou usuário do sistema (não pode comprar)
              $isCliente = isset($_SESSION['usuario']['cliente_id']);
              $isUsuarioSistema = isset($_SESSION['usuario']['usuario_id']);

              if (!isset($_SESSION['usuario']) || $isCliente) {

                ?>
                <style>
                  .l3-qty {
                    background: #232a32;
                    border: 1px solid #343b41;
                    border-radius: 12px;
                    position: relative;
                    padding: 6px;
                    overflow: visible
                  }

                  .l3-qty-field {
                    background: #2b3139;
                    border: 1px solid #3a424c;
                    border-radius: 10px;
                    height: 100%;
                  }

                  .l3-qty-input {
                    background: #2b3139;
                    color: #fff;
                    border: none;
                    height: 100%;
                    width: 100%;
                    text-align: center;
                    border-radius: 10px
                  }

                  .l3-qty-btn {
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 28px;
                    height: 28px;
                    border-radius: 999px;
                    border: 1px solid #9ca3af;
                    color: #e5e7eb;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #232a32;
                    z-index: 10;
                    box-shadow: 0 0 0 3px #232a32
                  }

                  .l3-qty-btn.left {
                    left: 10px
                  }

                  .l3-qty-btn.right {
                    right: 10px
                  }

                  .l3-buy {
                    background: #FACC15;
                    color: #111217;
                    border-radius: 12px;
                    padding: 10px 12px;
                    height: 56px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center
                  }

                  .l3-check {
                    width: 20px;
                    height: 20px;
                    border-radius: 999px;
                    border: 2px solid #111217;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center
                  }

                  .l3-price {
                    line-height: 1;
                    font-weight: 700;
                    margin-left: 28px
                  }
                </style>
                <div class="grid grid-cols-2 gap-2 items-stretch mt-4">
                  <!-- seletor quantidade (estilo caixa escura com ícones circulares) -->
                  <div class="l3-qty flex items-center justify-center">
                    <button type="button" class="l3-qty-btn left" onclick="alterarQuantidade(-1,true)"><i
                        class="fa-solid fa-minus"></i></button>
                    <div class="l3-qty-field" style="width: 70%;text-align: center;">
                      <input type="number" id="quantidade_barra" value="<?php echo $compra_minima; ?>" class="l3-qty-input"
                        readonly>
                    </div>
                    <button type="button" class="l3-qty-btn right" onclick="alterarQuantidade(1,true)"><i
                        class="fa-solid fa-plus"></i></button>
                  </div>

                  <!-- botão comprar -->
                  <div style="border: 2px solid #FACC15; padding: 3px;" class="rounded-xl">
                    <button id="btnComprar" onclick="verificarLogin('comprar')"
                      class="l3-buy w-full text-left flex flex-row" style="flex-direction: row; justify-content: start;">

                      <div class="flex items-center gap-2 text-sm mr-2">
                        <span class="l3-check">
                          <i class="fa-solid fa-check"></i>
                        </span>
                      </div>

                      <div class="block">
                        <span>
                          Quero participar
                        </span>

                        <div id="btnPreco" class="l3-price text-base" style="font-size: 1rem;">R$
                          <?php echo number_format($campanhas[0]['preco'] * $compra_minima, 2, ',', '.'); ?>
                        </div>
                      </div>






                    </button>
                  </div>
                </div>
                <?php
              }
              ?>




              <!-- cotas premiadas -->
              <?php
              if (!empty($campanhas[0]['mostrar_cotas_premiadas']) && !empty($campanhas[0]['cotas_premiadas']) && !empty($campanhas[0]['premio_cotas_premiadas'])):
                $premios_json = json_decode($campanhas[0]['premio_cotas_premiadas'], true);
                if (is_array($premios_json) && !empty($premios_json)): ?>
                  <div class="w-full my-4 secao-compra">
                    <?php
                    // Contador de premiados (comprados) vs total
                    $tp_total = 0;
                    $tp_ganhos = 0;
                    foreach ($premios_json as $g) {
                      if (!empty($g['cotas']) && is_array($g['cotas'])) {
                        foreach ($g['cotas'] as $c) {
                          $tp_total++;
                          $pc = listaPedidos($conn, null, null, $id, null, null, null, null, $c);
                          if (!empty($pc)) {
                            $tp_ganhos++;
                          }
                        }
                      }
                    }
                    ?>
                    <div class="flex items-center justify-between mb-2">
                      <div class="flex items-center gap-2">
                        <p class="text-[17px] font-medium text-black dark:text-white">🏆 Títulos Premiados</p>
                        <span class="text-[11px] text-gray-400">instantâneos</span>
                      </div>
                      <span
                        class="px-2 py-[2px] rounded-md border border-[#FACC15] text-[#FACC15] text-[17px] font-bold"><?php echo $tp_ganhos; ?>/<?php echo $tp_total; ?></span>
                    </div>
                    <div class="space-y-1">
                      <?php
                      foreach ($premios_json as $grupo) {
                        $premioTexto = isset($grupo['premio']) ? $grupo['premio'] : '';
                        foreach ($grupo['cotas'] as $cota) {
                          $cota_formatada = formatarCotaComLargura($cota, $largura_cota);
                          // Detecta pelo banco se a cota já foi comprada nesta campanha
                          $pedidoCota = listaPedidos($conn, null, null, $id, null, null, null, null, $cota);
                          $temGanhador = !empty($pedidoCota);
                          $nomeGanhador = $temGanhador ? ($pedidoCota[0]['cliente_nome'] ?? 'Comprado') : 'Disponível';
                          echo "<div class='flex items-center gap-1 " . ($temGanhador ? 'bg-[#FACC15]' : 'bg-[#111217]') . " rounded-lg'>";

                          // número da cota à esquerda (pílula cinza, largura fixa)
                          echo "<span class=\"min-w-[80px] " . ($temGanhador ? 'bg-white text-black' : 'bg-[#919EAB] text-white') . " ml-2 text-center px-3 py-1 rounded text-xs font-bold \">{$cota_formatada}</span>";
                          // prêmio no meio: amarelo quando tem ganhador, cinza escuro quando disponível
                          $classePremio = $temGanhador ? " text-[#111217]" : " text-gray-200";
                          $iconePremio = $temGanhador ? "<i class=\\\"fa-solid fa-clock ml-1 opacity-70\\\"></i>" : "";
                          echo "<div class=\"flex-1 text-center px-3 py-1 rounded {$classePremio} text-lg font-semibold\">" . htmlspecialchars($premioTexto) . " {$iconePremio}</div>";
                          // status à direita: amarelo com troféu se tem ganhador, cinza com 'Disponível' caso contrário
                          if ($temGanhador) {
                            echo "<span class=\"px-3 py-1 rounded text-xs font-semibold flex items-center gap-1 bg-[#FACC15] text-[#111217]\"><i class=\"fa-solid fa-trophy\"></i> " . htmlspecialchars($nomeGanhador) . "</span>";
                          } else {
                            echo "<span class=\"px-3 py-1 rounded text-xs font-semibold flex items-center gap-1 text-white\"> Disponível</span>";
                          }
                          echo "</div>";
                        }
                      }
                      ?>
                    </div>
                    <?php if (!empty($campanhas[0]['descricao_cotas_premiadas'])): ?>
                      <p class="text-xs text-gray-400 mt-2">
                        <?php echo $campanhas[0]['descricao_cotas_premiadas']; ?>
                      </p>
                    <?php endif; ?>
                  </div>
                <?php endif;
              endif;






              if ($campanhas[0]['habilitar_ranking'] == '1'):


                $filtro_periodo_top_ganhadores = json_decode($campanhas[0]['filtro_periodo_top_ganhadores'], true);
                if (!isset($filtro_periodo_top_ganhadores['filtro']))
                  $filtro_periodo_top_ganhadores['filtro'] = 'ultimo_mes';

                switch ($filtro_periodo_top_ganhadores['filtro']) {
                  case 'hoje':
                    $data_inicio = date('Y-m-d H:i:s');
                    $data_fim = date('Y-m-d H:i:s');
                    break;

                  case 'ontem':
                    $data_inicio = date('Y-m-d H:i:s', strtotime('-1 day'));
                    $data_fim = $data_inicio; // ontem apenas
                    break;

                  case 'ultimo_mes':
                    $data_inicio = date('Y-m-d H:i:s', strtotime('-30 days'));
                    $data_fim = date('Y-m-d H:i:s');
                    break;

                  case 'personalizado':

                    $data_inicio = explode(' até ', $filtro_periodo_top_ganhadores['valor'])[0];
                    $data_fim = explode(' até ', $filtro_periodo_top_ganhadores['valor'])[1];
                    break;
                }

                // Buscar top compradores da campanha
                $top_compradores = listaClientesTopCompradores($conn, $id, $data_inicio, $data_fim, $campanhas[0]['quantidade_ranking']);
                if (!empty($top_compradores)): ?>
                  <div class="w-full px-4 mb-2 secao-compra">
                    <p class="text-sm font-medium text-black dark:text-white mb-2">🏆 Top Compradores</p>
                    <div class="grid grid-cols-1 gap-2">
                      <?php
                      $posicao = 1;
                      foreach ($top_compradores as $comprador):
                        $medalha = '';
                        if ($posicao == 1)
                          $medalha = '🥇';
                        else if ($posicao == 2)
                          $medalha = '🥈';
                        else if ($posicao == 3)
                          $medalha = '🥉';
                        ?>
                        <div class="bg-gray-300 dark:bg-gray-600 p-3 rounded-lg flex items-center justify-between">
                          <div class="flex items-center gap-2">
                            <span class="text-yellow-400 font-bold"><?php echo $medalha; ?>#<?php echo $posicao++; ?></span>
                            <span
                              class="text-black dark:text-white"><?php echo htmlspecialchars($comprador['cliente_nome']); ?></span>
                          </div>
                          <div class="flex items-center gap-4">
                            <div class="text-sm text-gray-400">
                              R$ <?php echo number_format($comprador['valor_total'], 2, ',', '.'); ?>
                            </div>
                            <div class="text-green-400">
                              <?php echo $comprador['total_comprado']; ?> cotas
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <?php if (!empty($campanhas[0]['descricao_top_compradores'])): ?>
                      <p class="text-sm text-gray-400 mt-2">
                        <?php echo $campanhas[0]['descricao_top_compradores']; ?>
                      </p>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              <?php endif; ?>

            </form>

            <!-- Info Sorteio -->
            <!-- <div class="px-4 pb-2 text-center w-full">
                    <p class="text-sm text-gray-600 dark:text-[#b8b9ad]">Sorteio feito pela Loteria Federal. Todos os dados do veículo, como suas
          modificações serão divulgadas pelo Instagram.</p>
      </div> -->

            <!-- Sistema de Login/Cadastro Inline -->
            <div id="loginCadastroContainerLayout1" class="hidden w-full login-container">
              <!-- Sistema de Login -->
              <div class="rounded-lg shadow-lg p-6 mb-6 bg-gray-100 dark:bg-[#3F3F46]">
                <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Digite seu número</h3>

                <!-- Formulário de Login -->
                <form id="formLoginInline" class="space-y-4 mb-6">
                  <div class="relative">
                    <label class="block text-gray-700 dark:text-gray-300 mb-2">Número de celular</label>
                    <div class="relative">
                      <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                      <input type="tel" name="telefone" required
                        class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                        placeholder="(11) 99999-9999">
                    </div>
                  </div>

                  <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold transition-colors">
                    Entrar
                  </button>
                </form>

                <div class="text-center">
                  <button onclick="mostrarCadastro()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                    Primeira vez? Cadastre-se
                  </button>
                </div>

                <!-- Resumo do Pedido -->
                <!-- <div class="mt-6 p-4 bg-green-600 rounded-lg">
                <div class="text-white text-center">
                  <p class="font-bold">Valor do pedido: R$ <span id="valorPedidoLoginLayout1">0,00</span></p>
                </div>
              </div> -->

                <!-- Termos de Uso -->
                <div class="mt-4 p-3 bg-orange-500 rounded-lg">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <input type="checkbox" id="aceitarTermosLayout1" class="mr-2">
                      <label for="aceitarTermosLayout1" class="text-white text-sm">Aceito os termos de uso</label>
                    </div>
                    <button type="button" onclick="verTermosUso()"
                      class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors">
                      Ver Termos de Uso
                    </button>
                  </div>
                </div>

                <!-- Mensagem de Erro -->
                <div id="mensagemErroLayout1" class="hidden mt-4 p-3 bg-red-500 rounded-lg">
                  <p class="text-white text-sm text-center">Para prosseguir você deve aceitar Termos de Uso.</p>
                </div>
              </div>



              <!-- Formulário de Cadastro Inline -->
              <div id="cadastroContainerLayout1" class="hidden w-full">
                <div class="rounded-lg shadow-lg p-6 mb-6 bg-gray-100 dark:bg-[#3F3F46]">
                  <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Cadastro</h3>

                  <form id="formCadastroInline" class="space-y-4">
                    <!-- Campo Telefone - Sempre obrigatório -->
                    <div class="relative">
                      <label class="block text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-phone mr-2"></i>Telefone
                      </label>
                      <div class="relative">
                        <i class="fas fa-phone absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="tel" name="telefone" required
                          class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                          placeholder="(11) 99999-9999">
                      </div>
                    </div>

                    <?php if (empty($campos_obrigatorios) || in_array('nome', $campos_obrigatorios)): ?>
                      <div class="relative">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">Nome Completo</label>
                        <div class="relative">
                          <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                          <input type="text" name="nome" <?php echo in_array('nome', $campos_obrigatorios) ? 'required' : ''; ?>
                            class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                            placeholder="Digite seu nome completo">
                        </div>
                      </div>
                    <?php endif; ?>

                    <?php if (empty($campos_obrigatorios) || in_array('email', $campos_obrigatorios)): ?>
                      <div class="relative">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">E-mail</label>
                        <div class="relative">
                          <i class="fas fa-envelope absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                          <input type="email" name="email" <?php echo in_array('email', $campos_obrigatorios) ? 'required' : ''; ?>
                            class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                            placeholder="Digite seu e-mail">
                        </div>
                      </div>
                    <?php endif; ?>

                    <?php if (in_array('cpf', $campos_obrigatorios)): ?>
                      <div class="relative">
                        <label class="block text-gray-700 dark:text-gray-300 mb-2">CPF</label>
                        <div class="relative">
                          <i class="fas fa-id-card absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                          <input type="text" name="cpf" required
                            class="w-full bg-gray-200 dark:bg-[#3F3F46] border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-3 text-gray-800 dark:text-white"
                            placeholder="Digite seu CPF">
                        </div>
                      </div>
                    <?php endif; ?>

                    <button type="submit"
                      class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold transition-colors">
                      Cadastrar
                    </button>
                  </form>

                  <div class="text-center mt-4">
                    <button onclick="voltarParaLogin()"
                      class="text-blue-500 hover:text-blue-600 text-sm font-medium transition-colors">
                      Já tenho cadastro, fazer login
                    </button>
                  </div>
                </div>
              </div>


              <!-- Botões de Navegação -->
              <div class="flex justify-between mt-4">
                <button onclick="voltarParaCompra()"
                  class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors">
                  <i class="fas fa-arrow-left mr-2"></i>
                  Voltar
                </button>

              </div>
            </div>


          </div>
          </>

    </main>
    <?php
  }













































  require_once('footer.php') ?>

</body>

<?php if ($campanhas[0]["layout"] == 0): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const carousel = document.getElementById("carousel");
      const indicators = document.querySelectorAll(".indicator");
      if (!carousel || indicators.length <= 1) return;

      let index = 0;
      let intervalId = null;
      let startX = 0;
      let isDragging = false;
      let dragThreshold = 50;

      function updateCarousel() {
        carousel.style.transform = `translateX(-${index * 100}%)`;
        indicators.forEach((dot, i) => {
          dot.classList.toggle("opacity-100", i === index);
        });
      }

      function nextSlide() {
        index = (index + 1) % indicators.length;
        updateCarousel();
      }

      function prevSlide() {
        index = (index - 1 + indicators.length) % indicators.length;
        updateCarousel();
      }

      indicators.forEach(dot => {
        dot.addEventListener("click", (e) => {
          index = parseInt(e.target.dataset.index);
          updateCarousel();
          restartAutoplay();
        });
      });

      function startAutoplay() {
        stopAutoplay();
        intervalId = setInterval(nextSlide, 5000);
      }
      function stopAutoplay() {
        if (intervalId) clearInterval(intervalId);
      }
      function restartAutoplay() {
        stopAutoplay();
        startAutoplay();
      }

      // Mouse drag
      carousel.addEventListener('mousedown', (e) => {
        isDragging = true;
        startX = e.pageX;
        stopAutoplay();
      });
      document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
      });
      document.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        let diff = e.pageX - startX;
        if (diff > dragThreshold) {
          prevSlide();
        } else if (diff < -dragThreshold) {
          nextSlide();
        }
        isDragging = false;
        restartAutoplay();
      });

      // Touch drag
      carousel.addEventListener('touchstart', (e) => {
        isDragging = true;
        startX = e.touches[0].clientX;
        stopAutoplay();
      });
      carousel.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        let diff = e.changedTouches[0].clientX - startX;
        if (diff > dragThreshold) {
          prevSlide();
        } else if (diff < -dragThreshold) {
          nextSlide();
        }
        isDragging = false;
        restartAutoplay();
      });

      // Inicialização
      updateCarousel();
      startAutoplay();
    });


  </script>
<?php endif; ?>

<style>
  /* Melhorias para o modo claro */
  .dark .pacote-promocional {
    background-color: #343b41 !important;
  }

  .pacote-promocional {
    background-color: #1BC863 !important;
    color: white !important;
  }

  /* Melhor contraste para textos no modo claro */
  .dark .text-gray-600 {
    color: #b8b9ad;
  }

  /* Melhor contraste para bordas no modo claro */
  .border-gray-300 {
    border-color: #d1d5db;
  }

  /* Melhor contraste para backgrounds no modo claro */
  .bg-gray-100 {
    background-color: #f3f4f6;
  }

  /* Melhor contraste para inputs no modo claro */
  .bg-gray-100.dark\:bg-gray-700 {
    background-color: #f3f4f6;
  }

  .dark .bg-gray-100.dark\:bg-gray-700 {
    background-color: #374151;
  }

  /* Melhor contraste para botões no modo claro */
  .bg-gray-600 {
    background-color: #3F3F46;
  }

  .dark .bg-gray-600 {
    background-color: #374151;
  }

  /* Melhor contraste para textos em botões */
  .bg-gray-600 .text-white {
    color: white;
  }

  /* Melhor contraste para bordas de botões */
  .border-\[#343b41\] {
    border-color: #6b7280;
  }

  .dark .border-\[#343b41\] {
    border-color: #343b41;
  }

  /* Melhor contraste para hover states */
  .hover\:bg-\[#232a32\]:hover {
    background-color: #3F3F46;
  }

  .dark .hover\:bg-\[#232a32\]:hover {
    background-color: #232a32;
  }
</style>
<script>
  const precoBase = <?php echo $campanhas[0]['preco']; ?>;
  const compraMinima = <?php echo $compra_minima; ?>;
  const compraMaxima = <?php echo $compra_maxima; ?>;
  let acaoAposLogin = 'comprar';

  // Define clienteId baseado na sessão
  <?php
  $clienteId = null;
  if (isset($_SESSION['usuario']['cliente_id'])) {
    $clienteId = $_SESSION['usuario']['cliente_id'];
  }
  ?>
  const clienteId = <?php echo $clienteId ? $clienteId : 'null'; ?>;

  function verificarLogin(acao = 'comprar') {
    <?php
    $isCliente = isset($_SESSION['usuario']['cliente_id']);
    $isUsuarioSistema = isset($_SESSION['usuario']['usuario_id']);
    ?>

    const isCliente = <?php echo $isCliente ? 'true' : 'false'; ?>;
    const isUsuarioSistema = <?php echo $isUsuarioSistema ? 'true' : 'false'; ?>;

    if (isCliente && clienteId && clienteId !== false && clienteId !== "false") {
      // Cliente logado: abre confirmação antes de enviar
      if (acao == 'comprar') {
        abrirConfirmacaoCompra();
        return;
      } else if (acao == 'titulos') {
        window.location.href = 'meus_titulos.php?cliente_id=' + clienteId;
        return;
      }
    } else if (isUsuarioSistema) {
      // Se for um usuário do sistema (admin/afiliado), não pode comprar nem ver títulos
      alert('Usuários do sistema não podem comprar cotas ou visualizar títulos.');
      return;
    }

    // Caso não esteja logado, mostra o sistema de login inline
    mostrarSistemaLogin(acao);
  }

  // Função para resetar completamente o estado do sistema
  function resetarEstadoSistema() {
    const secoesCompra = document.querySelectorAll('.secao-compra');
    const confirmacao = document.getElementById('confirmacaoCompraContainer');
    const layout0 = document.getElementById('loginCadastroContainerLayout0');
    const layout1 = document.getElementById('loginCadastroContainerLayout1');
    const cadastroContainerLayout0 = document.getElementById('cadastroContainerLayout0');
    const cadastroContainerLayout1 = document.getElementById('cadastroContainerLayout1');

    // Reseta seções de compra
    secoesCompra.forEach(secao => {
      if (confirmacao && (secao === confirmacao || confirmacao.contains(secao))) {
        // deixa confirmação oculta por padrão ao resetar
        secao.classList.add('hidden');
        secao.classList.remove('slide-out', 'slide-in');
      } else {
        secao.classList.remove('hidden', 'slide-out');
      }
      secao.style.transform = 'translateX(0)';
      secao.style.opacity = '1';
      secao.style.transition = '';
    });

    // Esconde containers de login
    if (layout0) {
      layout0.classList.add('hidden');
      layout0.classList.remove('slide-in', 'slide-out');
    }
    if (layout1) {
      layout1.classList.add('hidden');
      layout1.classList.remove('slide-in', 'slide-out');
    }

    // Esconde containers de cadastro
    if (cadastroContainerLayout0) {
      cadastroContainerLayout0.classList.add('hidden');
      cadastroContainerLayout0.classList.remove('form-fade-in');
    }
    if (cadastroContainerLayout1) {
      cadastroContainerLayout1.classList.add('hidden');
      cadastroContainerLayout1.classList.remove('form-fade-in');
    }

    // Limpa estado
    acaoAposLogin = '';
  }

  // Função para detectar qual layout está sendo usado
  function detectarLayout() {
    const layout0 = document.getElementById('loginCadastroContainerLayout0');
    const layout1 = document.getElementById('loginCadastroContainerLayout1');

    // Se apenas um layout existe, usa ele
    if (layout0 && !layout1) {
      return { container: layout0, type: 'layout0' };
    } else if (layout1 && !layout0) {
      return { container: layout1, type: 'layout1' };
    }

    // Se ambos existem, verifica qual está visível
    if (layout0 && layout1) {
      if (!layout0.classList.contains('hidden')) {
        return { container: layout0, type: 'layout0' };
      } else if (!layout1.classList.contains('hidden')) {
        return { container: layout1, type: 'layout1' };
      }
    }

    // Fallback: retorna o primeiro que existir
    if (layout0) {
      return { container: layout0, type: 'layout0' };
    } else if (layout1) {
      return { container: layout1, type: 'layout1' };
    }

    return null;
  }

  // Função para mostrar o sistema de login
  function mostrarSistemaLogin(acao = 'comprar') {
    // Reseta o estado do sistema primeiro para garantir funcionamento correto
    resetarEstadoSistema();

    // Pequeno delay para garantir que o reset seja aplicado
    setTimeout(() => {
      // Encontra a seção de compra (botões, formulários, etc.)
      const secoesCompra = document.querySelectorAll('.secao-compra');

      // Detecta qual layout está sendo usado
      const layoutInfo = detectarLayout();
      const loginContainer = layoutInfo ? layoutInfo.container : null;
      const layoutType = layoutInfo ? layoutInfo.type : 'layout0';

      // Garante que a confirmação esteja oculta ao entrar no fluxo de login
      const confirmacao = document.getElementById('confirmacaoCompraContainer');
      if (confirmacao) {
        confirmacao.classList.add('hidden');
        confirmacao.style.opacity = '';
        confirmacao.style.transform = '';
        confirmacao.style.transition = '';
      }

      // Determina qual layout está ativo
      const isLayout0 = layoutType === 'layout0';
      const isLayout1 = layoutType === 'layout1';

      // Limpa qualquer estado residual primeiro
      acaoAposLogin = acao;

      // Limpa formulários e containers de cadastro
      const cadastroContainerLayout0 = document.getElementById('cadastroContainerLayout0');
      const cadastroContainerLayout1 = document.getElementById('cadastroContainerLayout1');
      const cadastroContainer = cadastroContainerLayout0 || cadastroContainerLayout1;

      if (cadastroContainer) {
        cadastroContainer.classList.add('hidden');
        cadastroContainer.classList.remove('form-fade-in');
      }

      // Garante que o formulário de login esteja visível
      const loginForm = document.getElementById('formLoginInline');
      if (loginForm && loginForm.parentElement) {
        loginForm.parentElement.classList.remove('hidden');
      }

      // Atualiza o valor do pedido
      const quantidade = parseInt(document.getElementById('quantidade_barra').value);
      const precoUnitario = <?php echo $campanhas[0]['preco']; ?>;
      const valorTotal = quantidade * precoUnitario;

      // Usa o ID correto baseado no layout
      const valorPedidoLoginId = isLayout0 ? 'valorPedidoLoginLayout0' : 'valorPedidoLoginLayout1';
      const valorPedidoLogin = document.getElementById(valorPedidoLoginId);
      if (valorPedidoLogin) {
        valorPedidoLogin.textContent = valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }

      // Reseta o estado das seções de compra primeiro
      secoesCompra.forEach(secao => {
        secao.classList.remove('slide-out');
        secao.style.transform = '';
        secao.style.opacity = '';
      });

      // Animação de slide out das seções de compra
      secoesCompra.forEach(secao => {
        secao.classList.add('slide-out');
      });

      setTimeout(() => {
        // Esconde as seções de compra
        secoesCompra.forEach(secao => {
          secao.classList.add('hidden');
        });

        // Mostra o sistema de login com animação garantida
        if (loginContainer) {
          // torna visível
          loginContainer.classList.remove('hidden');
          // reseta estados de classe
          loginContainer.classList.remove('slide-out');
          loginContainer.classList.remove('slide-in');
          // estado inicial fora da tela
          loginContainer.style.transform = 'translateX(50%)';
          loginContainer.style.opacity = '0';
          // força reflow para permitir transição
          void loginContainer.offsetWidth;
          // estado final animado (usa a transition definida em .login-container)
          loginContainer.style.transform = 'translateX(0)';
          loginContainer.style.opacity = '1';
          loginContainer.classList.add('slide-in');
          // limpa estilos inline após a animação
          setTimeout(() => {
            loginContainer.style.transform = '';
            loginContainer.style.opacity = '';
          }, 500);
        }
      }, 500);
    }, 50); // Fecha o setTimeout do reset
  }

  // Função para voltar para a compra
  function voltarParaCompra() {
    const secoesCompra = document.querySelectorAll('.secao-compra');
    const confirmacao = document.getElementById('confirmacaoCompraContainer');

    // Detecta qual layout está sendo usado
    const layoutInfo = detectarLayout();
    const loginContainer = layoutInfo ? layoutInfo.container : null;

    const cadastroContainerLayout0 = document.getElementById('cadastroContainerLayout0');
    const cadastroContainerLayout1 = document.getElementById('cadastroContainerLayout1');
    const cadastroContainer = cadastroContainerLayout0 || cadastroContainerLayout1;

    // Esconde o cadastro se estiver visível
    if (cadastroContainer && !cadastroContainer.classList.contains('hidden')) {
      cadastroContainer.classList.add('hidden');
      cadastroContainer.classList.remove('form-fade-in');
    }

    // Esconde o formulário de login se estiver visível
    const loginForm = document.getElementById('formLoginInline');
    if (loginForm && loginForm.parentElement) {
      setTimeout(() => {
        loginForm.parentElement.classList.add('hidden');
      }, 500);
    }

    // Animação de slide out do container de login
    if (loginContainer) {
      loginContainer.classList.remove('slide-in');
      loginContainer.classList.add('slide-out');
    }

    setTimeout(() => {
      // Oculta a confirmação (se estiver aberta)
      if (confirmacao) {
        confirmacao.classList.add('hidden');
      }
      // Deixa o container visível enquanto anima a saída, só oculta após concluir
      if (loginContainer) {
        loginContainer.classList.remove('hidden');
        loginContainer.classList.remove('slide-in');
        loginContainer.classList.add('slide-out');
      }

      // Mostra as seções de compra com ANIMAÇÃO de slide-in à esquerda
      secoesCompra.forEach(secao => {
        secao.classList.remove('hidden', 'slide-out');
        // estado inicial para animar
        secao.style.transform = 'translateX(-50%)';
        secao.style.opacity = '0';
        // gatilho de reflow para permitir a transição
        void secao.offsetWidth;
        // estado final (anima graças ao CSS de .secao-compra)
        secao.style.transform = 'translateX(0)';
        secao.style.opacity = '1';
      });

      // Após a duração da transição, aí sim oculta o container de login
      setTimeout(() => {
        if (loginContainer) {
          loginContainer.classList.add('hidden');
          loginContainer.classList.remove('slide-out');
          loginContainer.style.transform = '';
          loginContainer.style.opacity = '';
        }
      }, 500);

      // Limpa qualquer estado residual
      acaoAposLogin = '';
    }, 0);
  }

  // Função para mostrar o formulário de cadastro
  function mostrarCadastro() {
    const loginForm = document.getElementById('formLoginInline').parentElement;

    // Detecta qual layout está sendo usado
    const cadastroContainerLayout0 = document.getElementById('cadastroContainerLayout0');
    const cadastroContainerLayout1 = document.getElementById('cadastroContainerLayout1');
    const cadastroContainer = cadastroContainerLayout0 || cadastroContainerLayout1;

    // Animação de fade out do formulário de login
    loginForm.style.opacity = '0';
    loginForm.style.transform = 'translateY(-10px)';

    setTimeout(() => {
      loginForm.classList.add('hidden');
      loginForm.style.opacity = '';
      loginForm.style.transform = '';

      // Mostra o formulário de cadastro com animação
      cadastroContainer.classList.remove('hidden');
      cadastroContainer.classList.add('form-fade-in');
    }, 200);
  }

  // Função para voltar para o login
  function voltarParaLogin() {
    const loginForm = document.getElementById('formLoginInline').parentElement;

    // Detecta qual layout está sendo usado
    const cadastroContainerLayout0 = document.getElementById('cadastroContainerLayout0');
    const cadastroContainerLayout1 = document.getElementById('cadastroContainerLayout1');
    const cadastroContainer = cadastroContainerLayout0 || cadastroContainerLayout1;

    // Animação de fade out do formulário de cadastro
    cadastroContainer.style.opacity = '0';
    cadastroContainer.style.transform = 'translateY(10px)';

    setTimeout(() => {
      cadastroContainer.classList.add('hidden');
      cadastroContainer.classList.remove('form-fade-in');
      cadastroContainer.style.opacity = '';
      cadastroContainer.style.transform = '';

      // Mostra o formulário de login com animação de slide left
      loginForm.classList.remove('hidden');
      loginForm.style.opacity = '0';
      loginForm.style.transform = 'translateX(-50px)';
      loginForm.style.transition = 'opacity 0.3s ease-in-out, transform 0.3s ease-in-out';

      setTimeout(() => {
        loginForm.style.opacity = '1';
        loginForm.style.transform = 'translateX(0)';
      }, 50);
    }, 200);
  }

  // Função para continuar com login
  function continuarComLogin() {
    // Simplifica a detecção do layout - sempre usa Layout0 por padrão
    let aceitarTermos = document.getElementById('aceitarTermosLayout0');
    let mensagemErro = document.getElementById('mensagemErroLayout0');

    // Se não encontrar no Layout0, tenta Layout1
    if (!aceitarTermos) {
      const aceitarTermos1 = document.getElementById('aceitarTermosLayout1');
      const mensagemErro1 = document.getElementById('mensagemErroLayout1');
      if (aceitarTermos1) {
        aceitarTermos = aceitarTermos1;
        mensagemErro = mensagemErro1;
      }
    }

    if (!aceitarTermos || !aceitarTermos.checked) {
      if (mensagemErro) {
        mensagemErro.classList.remove('hidden');
        // Rola para a mensagem e aplica animação de atenção
        try { mensagemErro.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { }
        mensagemErro.classList.remove('attention-shake');
        // reflow para reiniciar animação
        void mensagemErro.offsetWidth;
        mensagemErro.classList.add('attention-shake');
        mensagemErro.addEventListener('animationend', function handler() {
          mensagemErro.classList.remove('attention-shake');
          mensagemErro.removeEventListener('animationend', handler);
        });
      }
      return;
    }

    // Submete o formulário de login
    document.getElementById('formLoginInline').dispatchEvent(new Event('submit'));
  }

  // Função para ver termos de uso
  function verTermosUso() {
    alert('Termos de uso serão exibidos aqui.');
  }




  function alterarQuantidade(qtd, adicionar = false) {
    const precoUnitario = <?php echo $campanhas[0]['preco']; ?>;
    const compraMinima = <?php echo $compra_minima; ?>;
    const compraMaxima = <?php echo $compra_maxima; ?>;

    let novaQuantidade;
    if (adicionar) {
      // Se adicionar for true, soma à quantidade atual
      const quantidadeAtual = parseInt(document.getElementById('quantidade_barra').value);
      novaQuantidade = quantidadeAtual + qtd;
    } else {
      // Se adicionar for false, usa o valor direto
      novaQuantidade = qtd;
    }
    // Validar limites
    if (novaQuantidade < compraMinima) novaQuantidade = compraMinima;
    if (novaQuantidade > compraMaxima) novaQuantidade = compraMaxima;

    const valorTotal = precoUnitario * novaQuantidade;

    document.getElementById('quantidade_barra').value = novaQuantidade;
    document.getElementById('quantidade').value = novaQuantidade;
    document.getElementById('valor_total').value = valorTotal;
    const elPrecoAlterar = document.getElementById('btnPreco');
    if (elPrecoAlterar) {
      elPrecoAlterar.textContent = 'R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Mostrar/ocultar mensagem de máximo
    const mensagemMaximo = document.querySelector('.text-red-500');
    if (novaQuantidade >= compraMaxima) {
      mensagemMaximo.style.display = 'block';
    } else {
      mensagemMaximo.style.display = 'none';
    }
  }

  // Função para os botões de quantidade rápida
  function quantidadeRapida(quantidade) {
    const precoUnitario = <?php echo $campanhas[0]['preco']; ?>;
    const compraMinima = <?php echo $compra_minima; ?>;
    const compraMaxima = <?php echo $compra_maxima; ?>;

    // Definir quantidade diretamente
    let novaQuantidade = parseInt(quantidade);

    // Validar limites
    if (novaQuantidade < compraMinima) novaQuantidade = compraMinima;
    if (novaQuantidade > compraMaxima) novaQuantidade = compraMaxima;

    const valorTotal = precoUnitario * novaQuantidade;

    document.getElementById('quantidade_barra').value = novaQuantidade;
    document.getElementById('quantidade').value = novaQuantidade;
    document.getElementById('valor_total').value = valorTotal;
    const elPrecoRapida = document.getElementById('btnPreco');
    if (elPrecoRapida) {
      elPrecoRapida.textContent = 'R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Mostrar/ocultar mensagem de máximo
    const mensagemMaximo = document.querySelector('.text-red-500');
    if (novaQuantidade >= compraMaxima) {
      mensagemMaximo.style.display = 'block';
    } else {
      mensagemMaximo.style.display = 'none';
    }

    // Adicionar efeito visual ao botão clicado
    const botoes = document.querySelectorAll('.quantidade-rapida-btn');
    botoes.forEach(btn => {
      btn.classList.remove('bg-green-500', 'text-white', 'border-green-500', 'active');
      btn.classList.add('bg-gray-100', 'dark:bg-[#3F3F46]', 'text-gray-700', 'dark:text-gray-300');
    });

    const botaoClicado = event.target;
    botaoClicado.classList.remove('bg-gray-100', 'dark:bg-[#3F3F46]', 'text-gray-700', 'dark:text-gray-300');
    botaoClicado.classList.add('bg-green-500', 'text-white', 'border-green-500', 'active');
  }

  function alterarQuantidadePromocional(qtd, precoPacote, adicionar = false, beneficioTipo = '', beneficioQuantidade = 0) {
    const compraMinima = <?php echo $compra_minima; ?>;
    const compraMaxima = <?php echo $compra_maxima; ?>;

    // Define quantidade de cotas conforme pacote selecionado
    let novaQuantidade;
    if (adicionar) {
      const quantidadeAtual = parseInt(document.getElementById('quantidade_barra').value || '0', 10);
      novaQuantidade = quantidadeAtual + qtd;
    } else {
      novaQuantidade = qtd;
    }

    if (novaQuantidade < compraMinima) novaQuantidade = compraMinima;
    if (novaQuantidade > compraMaxima) novaQuantidade = compraMaxima;

    // Atualiza campos básicos
    const inputQtdBarra = document.getElementById('quantidade_barra');
    const inputQtd = document.getElementById('quantidade');
    const inputValor = document.getElementById('valor_total');
    const btnComprar = document.getElementById('btnComprar');
    if (inputQtdBarra) inputQtdBarra.value = novaQuantidade;
    if (inputQtd) inputQtd.value = novaQuantidade;
    if (inputValor) inputValor.value = precoPacote;
    const elPrecoPromo = document.getElementById('btnPreco');
    if (elPrecoPromo) {
      elPrecoPromo.textContent = 'R$ ' + Number(precoPacote).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Garante inputs de benefício que o backend espera
    let girosInput = document.getElementById('quantidade_giros_roleta');
    if (!girosInput) {
      girosInput = document.createElement('input');
      girosInput.type = 'hidden';
      girosInput.id = 'quantidade_giros_roleta';
      girosInput.name = 'quantidade_giros_roleta';
      document.getElementById('formComprar').appendChild(girosInput);
    }
    let raspadinhasInput = document.getElementById('quantidade_raspadinhas');
    if (!raspadinhasInput) {
      raspadinhasInput = document.createElement('input');
      raspadinhasInput.type = 'hidden';
      raspadinhasInput.id = 'quantidade_raspadinhas';
      raspadinhasInput.name = 'quantidade_raspadinhas';
      document.getElementById('formComprar').appendChild(raspadinhasInput);
    }

    // Zera ambos e aplica somente o benefício do pacote
    girosInput.value = 0;
    raspadinhasInput.value = 0;
    const qtdBeneficio = parseInt(beneficioQuantidade || 0, 10);
    if (beneficioTipo === 'roleta' && qtdBeneficio > 0) {
      girosInput.value = qtdBeneficio;
    } else if (beneficioTipo === 'raspadinha' && qtdBeneficio > 0) {
      raspadinhasInput.value = qtdBeneficio;
    }

    // Mostrar/ocultar mensagem de máximo
    const mensagemMaximo = document.querySelector('.text-red-500');
    if (novaQuantidade >= compraMaxima) {
      mensagemMaximo.style.display = 'block';
    } else {
      mensagemMaximo.style.display = 'none';
    }

    // Fluxo: se logado vai para confirmação, senão mostra login
    if (clienteId) {
      const form = document.getElementById('formComprar');
      if (form && form.cliente_id) form.cliente_id.value = clienteId;
      abrirConfirmacaoCompra();
    } else {
      mostrarSistemaLogin('comprar');
    }

  }

  // Garante disponibilidade no escopo global (Layout 0 usa onclick inline)
  if (typeof window !== 'undefined') {
    window.alterarQuantidade = alterarQuantidade;
    window.alterarQuantidadePromocional = alterarQuantidadePromocional;
    window.quantidadeRapida = quantidadeRapida;
  }

  function selecionarPacoteRoleta(codigo, valor, quantidadeGiros) {
    // Armazenar informações do pacote selecionado
    window.pacoteRoletaSelecionado = {
      codigo: codigo,
      valor: valor,
      quantidadeGiros: quantidadeGiros
    };

    // Atualizar o formulário com os dados do pacote
    document.getElementById('valor_total').value = valor;
    document.getElementById('quantidade').value = 1; // Quantidade sempre 1 para pacotes
    const elPrecoRoleta = document.getElementById('btnPreco');
    if (elPrecoRoleta) {
      elPrecoRoleta.textContent = 'R$ ' + valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Adicionar campo hidden para o código do pacote
    let codigoInput = document.getElementById('codigo_pacote_roleta');
    if (!codigoInput) {
      codigoInput = document.createElement('input');
      codigoInput.type = 'hidden';
      codigoInput.id = 'codigo_pacote_roleta';
      codigoInput.name = 'codigo_pacote_roleta';
      document.getElementById('formComprar').appendChild(codigoInput);
    }
    codigoInput.value = codigo;

    // Adicionar campo hidden para quantidade de giros
    let girosInput = document.getElementById('quantidade_giros_roleta');
    if (!girosInput) {
      girosInput = document.createElement('input');
      girosInput.type = 'hidden';
      girosInput.id = 'quantidade_giros_roleta';
      girosInput.name = 'quantidade_giros_roleta';
      document.getElementById('formComprar').appendChild(girosInput);
    }
    girosInput.value = quantidadeGiros;

    // Fluxo: se logado vai para confirmação, senão mostra login
    if (clienteId) {
      const form = document.getElementById('formComprar');
      if (form && form.cliente_id) form.cliente_id.value = clienteId;
      abrirConfirmacaoCompra();
    } else {
      mostrarSistemaLogin('comprar');
    }
  }

  function selecionarPacoteRaspadinha(codigo, valor, quantidadeRaspadinhas) {
    // Armazenar informações do pacote selecionado
    window.pacoteRaspadinhaSelecionado = {
      codigo: codigo,
      valor: valor,
      quantidadeRaspadinhas: quantidadeRaspadinhas
    };

    // Atualizar o formulário com os dados do pacote
    document.getElementById('valor_total').value = valor;
    document.getElementById('quantidade').value = 1; // Quantidade sempre 1 para pacotes
    const elPrecoRasp = document.getElementById('btnPreco');
    if (elPrecoRasp) {
      elPrecoRasp.textContent = 'R$ ' + valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Adicionar campo hidden para o código do pacote
    let codigoInput = document.getElementById('codigo_pacote_raspadinha');
    if (!codigoInput) {
      codigoInput = document.createElement('input');
      codigoInput.type = 'hidden';
      codigoInput.id = 'codigo_pacote_raspadinha';
      codigoInput.name = 'codigo_pacote_raspadinha';
      document.getElementById('formComprar').appendChild(codigoInput);
    }
    codigoInput.value = codigo;

    // Adicionar campo hidden para quantidade de raspadinhas
    let raspadinhasInput = document.getElementById('quantidade_raspadinhas');
    if (!raspadinhasInput) {
      raspadinhasInput = document.createElement('input');
      raspadinhasInput.type = 'hidden';
      raspadinhasInput.id = 'quantidade_raspadinhas';
      raspadinhasInput.name = 'quantidade_raspadinhas';
      document.getElementById('formComprar').appendChild(raspadinhasInput);
    }
    raspadinhasInput.value = quantidadeRaspadinhas;

    // Fluxo: se logado vai para confirmação, senão mostra login
    if (clienteId) {
      const form = document.getElementById('formComprar');
      if (form && form.cliente_id) form.cliente_id.value = clienteId;
      abrirConfirmacaoCompra();
    } else {
      mostrarSistemaLogin('comprar');
    }
  }

  function definirQuantidade(valor) {
    if (valor >= compraMinima && valor <= compraMaxima) {
      const quantidadeInput = document.getElementById('quantidade_barra');
      quantidadeInput.value = valor;
      atualizarBotaoComprar(valor);
      atualizarLinkComprar(valor);
    }
  }

  function atualizarBotaoComprar(quantidade) {
    const botaoComprar = document.querySelector('button.bg-green-600.w-full');
    const precoTotal = quantidade * precoBase;
    botaoComprar.textContent = `Comprar por R$ ${precoTotal.toFixed(2).replace('.', ',')}`;
  }

  function atualizarLinkComprar(quantidade) {
    const linkComprar = document.getElementById('linkComprar');
    const baseUrl = linkComprar.href.split('&quantidade=')[0];
    linkComprar.href = `${baseUrl}&quantidade=${quantidade}`;
  }

  function compartilharCampanha() {

    const mensagem = `${window.location.href}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(mensagem)}`;

    window.open(whatsappUrl, '_blank');
  }

  document.addEventListener('DOMContentLoaded', () => {
    // Inicializar o carrossel
    inicializarCarrossel();

    // Inicializar o sistema de login
    inicializarSistemaLogin();
  });

  // Função para inicializar o sistema de login
  function inicializarSistemaLogin() {
    // Adiciona máscara para telefone
    const telefoneInputs = document.querySelectorAll('input[name="telefone"]');
    telefoneInputs.forEach(input => {
      input.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value;
      });
    });
  }

  let slideAtual = 0;
  let slides = [];
  let indicadores = [];
  let temporizador = null;

  function inicializarCarrossel() {
    slides = document.querySelectorAll('.carousel-slide');
    indicadores = document.querySelectorAll('.carousel-indicator');

    if (slides.length <= 1) return; // Não inicializa se tiver apenas 1 ou nenhuma imagem

    // Garantir que o primeiro slide esteja visível
    slides[0].classList.add('active');
    indicadores[0].classList.add('active');

    // Iniciar autoplay
    iniciarAutoplay();
  }

  function moverSlide(direcao) {
    if (slides.length <= 1) return;

    // Parar autoplay temporariamente
    pararAutoplay();

    // Remover classes ativas do slide atual
    slides[slideAtual].classList.remove('active');
    indicadores[slideAtual].classList.remove('active');

    // Calcular próximo slide
    const totalSlides = slides.length;
    slideAtual = (slideAtual + direcao + totalSlides) % totalSlides;

    // Ativar novo slide
    slides[slideAtual].classList.add('active');
    indicadores[slideAtual].classList.add('active');

    // Reiniciar autoplay
    iniciarAutoplay();
  }

  function irParaSlide(index) {
    if (index === slideAtual || slides.length <= 1) return;

    // Parar autoplay temporariamente
    pararAutoplay();

    // Remover classes ativas do slide atual
    slides[slideAtual].classList.remove('active');
    indicadores[slideAtual].classList.remove('active');

    // Atualizar índice e ativar novo slide
    slideAtual = index;
    slides[slideAtual].classList.add('active');
    indicadores[slideAtual].classList.add('active');

    // Reiniciar autoplay
    iniciarAutoplay();
  }

  function iniciarAutoplay() {
    if (slides.length <= 1) return;

    pararAutoplay();
    temporizador = setInterval(() => {
      moverSlide(1);
    }, 5000);
  }

  function pararAutoplay() {
    if (temporizador) {
      clearInterval(temporizador);
      temporizador = null;
    }
  }



  // Formulário de Login Inline
  document.getElementById('formLoginInline').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    // Simplifica a detecção do layout - sempre usa Layout0 por padrão
    let aceitarTermos = document.getElementById('aceitarTermosLayout0');
    let mensagemErro = document.getElementById('mensagemErroLayout0');

    // Se não encontrar no Layout0, tenta Layout1
    if (!aceitarTermos) {
      const aceitarTermos1 = document.getElementById('aceitarTermosLayout1');
      const mensagemErro1 = document.getElementById('mensagemErroLayout1');
      if (aceitarTermos1) {
        aceitarTermos = aceitarTermos1;
        mensagemErro = mensagemErro1;
      }
    }

    // Verifica se aceitou os termos
    if (!aceitarTermos || !aceitarTermos.checked) {
      if (mensagemErro) {
        mensagemErro.classList.remove('hidden');
        try { mensagemErro.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { }
        mensagemErro.classList.remove('attention-shake');
        void mensagemErro.offsetWidth;
        mensagemErro.classList.add('attention-shake');
        mensagemErro.addEventListener('animationend', function handler() {
          mensagemErro.classList.remove('attention-shake');
          mensagemErro.removeEventListener('animationend', handler);
        });
      }
      return;
    }

    // Esconde mensagem de erro se estiver visível
    mensagemErro.classList.add('hidden');

    // Mostra loading no botão
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Entrando...';
    submitBtn.disabled = true;

    fetch('ajax_login.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Atualiza o clienteId após sucesso
          document.getElementById('cliente_id').value = data.cliente_id;

          // Se for cliente, segue para confirmação de compra
          if (data.isCliente) {
            // esconde o container de login e mostra a confirmação
            const layoutInfo = detectarLayout();
            const loginContainer = layoutInfo ? layoutInfo.container : null;
            if (loginContainer) loginContainer.classList.add('hidden');
            // Garante que a área principal volte a aparecer antes de abrir a confirmação
            const secoesCompra = document.querySelectorAll('.secao-compra');
            secoesCompra.forEach(secao => secao.classList.remove('hidden'));
            abrirConfirmacaoCompra();
          } else {
            alert('Usuários do sistema não podem comprar cotas.');
            voltarParaCompra();
          }
        } else if (data.need_register) {
          // Se for necessário o cadastro, mostra o formulário de cadastro
          mostrarCadastro();
        } else {
          alert(data.message || 'Ocorreu um erro ao processar sua solicitação. Tente novamente.');
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
      })
      .finally(() => {
        // Restaura o botão
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      });
  });



  // Formulário de Cadastro Inline
  document.getElementById('formCadastroInline').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    // Mostra loading no botão
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Cadastrando...';
    submitBtn.disabled = true;

    fetch('ajax_cadastro.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('cliente_id').value = data.cliente_id;
          // esconde container de cadastro e mostra confirmação
          const layoutInfo = detectarLayout();
          const loginContainer = layoutInfo ? layoutInfo.container : null;
          if (loginContainer) loginContainer.classList.add('hidden');
          const secoesCompra = document.querySelectorAll('.secao-compra');
          secoesCompra.forEach(secao => secao.classList.remove('hidden'));
          abrirConfirmacaoCompra();
        } else {
          alert(data.message || 'Ocorreu um erro ao processar sua solicitação. Tente novamente.');
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
      })
      .finally(() => {
        // Restaura o botão
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      });
  });

  // Event listener para o checkbox de termos
  document.addEventListener('DOMContentLoaded', function () {
    // Adiciona listeners para ambos os checkboxes
    const aceitarTermosLayout0 = document.getElementById('aceitarTermosLayout0');
    const aceitarTermosLayout1 = document.getElementById('aceitarTermosLayout1');
    const mensagemErroLayout0 = document.getElementById('mensagemErroLayout0');
    const mensagemErroLayout1 = document.getElementById('mensagemErroLayout1');

    if (aceitarTermosLayout0 && mensagemErroLayout0) {
      aceitarTermosLayout0.addEventListener('change', function () {
        if (this.checked) {
          mensagemErroLayout0.classList.add('hidden');
        }
      });
    }

    if (aceitarTermosLayout1 && mensagemErroLayout1) {
      aceitarTermosLayout1.addEventListener('change', function () {
        if (this.checked) {
          mensagemErroLayout1.classList.add('hidden');
        }
      });
    }
  });



  function validarCompra(event) {
    event.preventDefault();

    const quantidade = parseInt(document.getElementById('quantidade_barra').value);
    const campanha_id = document.querySelector('input[name="campanha_id"]').value;
    const cliente_id = document.getElementById('cliente_id').value;
    const codigo_afiliado = document.querySelector('input[name="codigo_afiliado"]').value;

    if (!cliente_id) {
      // Mostra o sistema de login inline
      // Sempre oculta a confirmação antes de abrir o login
      try {
        const confirmacao = document.getElementById('confirmacaoCompraContainer');
        if (confirmacao) confirmacao.classList.add('hidden');
      } catch (e) { }
      mostrarSistemaLogin('comprar');
      return false;
    }
    // Atualiza os valores e abre confirmação
    document.getElementById('numeros_solicitados').value = JSON.stringify(numeros_solicitados);
    document.getElementById('quantidade').value = quantidade;
    document.getElementById('valor_total').value = quantidade * <?php echo $campanhas[0]['preco']; ?>;
    abrirConfirmacaoCompra();
    return false;
  }

  // Confirmação de compra (todos layouts)
  function abrirConfirmacaoCompra() {
    const nome = document.querySelector('input[name="nome_produto"]').value;
    const qtd = parseInt(document.getElementById('quantidade').value || document.getElementById('quantidade_barra').value, 10);
    const precoUnit = parseFloat(<?php echo $campanhas[0]['preco']; ?>);
    const total = parseFloat(document.getElementById('valor_total').value || (qtd * precoUnit));
    // Garante cliente_id no formulário quando já está logado
    try {
      const form = document.getElementById('formComprar');
      if (form && form.cliente_id && !form.cliente_id.value && (typeof clienteId !== 'undefined') && clienteId) {
        form.cliente_id.value = clienteId;
      }
    } catch (e) { }
    let beneficios = [];
    const giros = document.getElementById('quantidade_giros_roleta');
    const rasp = document.getElementById('quantidade_raspadinhas');
    if (giros && parseInt(giros.value || '0', 10) > 0) beneficios.push(`${giros.value} Giro(s)`);
    if (rasp && parseInt(rasp.value || '0', 10) > 0) beneficios.push(`${rasp.value} Raspadinha(s)`);
    // Garante que a seção de confirmação exista para todos os layouts e com tamanho padrão
    (function ensureConfirmacao() {
      if (!document.getElementById('confirmacaoCompraContainer')) {
        const wrapper = document.createElement('section');
        wrapper.id = 'confirmacaoCompraContainer';
        wrapper.className = 'hidden w-full';
        wrapper.innerHTML = `
          <div class="rounded-lg shadow-lg p-6 bg-gray-100 dark:bg-[#3F3F46] secao-compra">
            <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Confirmação da compra</h3>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-2 text-gray-800 dark:text-gray-100 items-center">
                <div class="font-semibold">Produto</div>
                <div id="confNome" class="text-right pr-2"></div>
                <div class="font-semibold">Quantidade</div>
                <div id="confQtd" class="text-right pr-2"></div>
                <div class="font-semibold">Preço unitário</div>
                <div class="text-right pr-2">R$ <span id="confPreco"></span></div>
                <div class="font-semibold">Benefícios</div>
                <div id="confBeneficios" class="text-right pr-2">-</div>
              </div>
              <div class="p-4 bg-green-600 rounded-lg text-white text-center">
                <p class="font-bold">Valor do pedido: R$ <span id="confTotal">0,00</span></p>
              </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
              <button id="btnConfCancelar" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg flex items-center transition-colors"><i class="fas fa-arrow-left mr-2"></i>Voltar</button>
              <button id="btnConfConfirmar" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-bold transition-colors">Confirmar e pagar</button>
            </div>
          </div>`;
        // Insere logo após a área de compra para manter o alinhamento
        const form = document.getElementById('formComprar');
        if (form && form.parentElement) {
          const parent = form.parentElement;
          if (parent.nextSibling) {
            parent.parentNode.insertBefore(wrapper, parent.nextSibling);
          } else {
            parent.parentNode.appendChild(wrapper);
          }
        } else {
          // Fallback: antes do footer
          const footer = document.querySelector('footer');
          if (footer && footer.parentElement) {
            footer.parentElement.insertBefore(wrapper, footer);
          } else {
            document.body.appendChild(wrapper);
          }
        }
      }
    })();

    // Preenche os campos do resumo
    const confNome = document.getElementById('confNome');
    const confQtd = document.getElementById('confQtd');
    const confPreco = document.getElementById('confPreco');
    const confTotal = document.getElementById('confTotal');
    const confBenef = document.getElementById('confBeneficios');
    if (confNome) confNome.textContent = nome;
    if (confQtd) confQtd.textContent = String(qtd);
    if (confPreco) confPreco.textContent = precoUnit.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (confTotal) confTotal.textContent = Number(total).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (confBenef) confBenef.textContent = beneficios.length ? beneficios.join(' + ') : '-';
    const sessao = document.getElementById('confirmacaoCompraContainer');
    if (!sessao) return;
    // Esconde seções de compra, exceto os elementos que pertencem à própria seção de confirmação
    const secoesCompra = document.querySelectorAll('.secao-compra');
    secoesCompra.forEach(secao => {
      if (!sessao.contains(secao)) {
        secao.classList.add('hidden');
      } else {
        secao.classList.remove('hidden');
        secao.classList.remove('slide-out');
        secao.classList.remove('slide-in');
      }
    });
    // Mostra o container da confirmação com transição
    sessao.classList.remove('hidden');
    sessao.classList.remove('slide-out');
    sessao.classList.remove('slide-in');
    // estado inicial
    sessao.style.opacity = '0';
    sessao.style.transform = 'translateY(16px)';
    sessao.style.transition = 'opacity .35s ease, transform .35s ease';
    void sessao.offsetWidth; // reflow
    // estado final
    sessao.style.opacity = '1';
    sessao.style.transform = 'translateY(0)';
    setTimeout(() => {
      sessao.style.opacity = '';
      sessao.style.transform = '';
      sessao.style.transition = '';
      try { sessao.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { }
    }, 400);
  }

  function fecharConfirmacaoCompra() {
    const sessao = document.getElementById('confirmacaoCompraContainer');
    if (sessao) {
      sessao.style.opacity = '1';
      sessao.style.transform = 'translateY(0)';
      sessao.style.transition = 'opacity .25s ease, transform .25s ease';
      void sessao.offsetWidth;
      sessao.style.opacity = '0';
      sessao.style.transform = 'translateY(16px)';
      setTimeout(() => {
        sessao.classList.add('hidden');
        sessao.style.opacity = '';
        sessao.style.transform = '';
        sessao.style.transition = '';
        const secoesCompra = document.querySelectorAll('.secao-compra');
        secoesCompra.forEach(secao => {
          secao.classList.remove('hidden', 'slide-out');
          secao.style.transform = '';
          secao.style.opacity = '';
          secao.style.transition = '';
        });
        try { document.getElementById('btnComprar')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { }
      }, 260);
    } else {
      const secoesCompra = document.querySelectorAll('.secao-compra');
      secoesCompra.forEach(secao => {
        secao.classList.remove('hidden', 'slide-out');
        secao.style.transform = '';
        secao.style.opacity = '';
        secao.style.transition = '';
      });
    }
  }

  document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'btnConfCancelar') {
      e.preventDefault();
      fecharConfirmacaoCompra();
    }
    if (e.target && e.target.id === 'btnConfConfirmar') {
      e.preventDefault();
      const form = document.getElementById('formComprar');
      if (!form.cliente_id.value) {
        fecharConfirmacaoCompra();
        mostrarSistemaLogin('comprar');
        return;
      }
      form.submit();
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    // Função para trocar as imagens automaticamente
    function iniciarCarrosselAutomatico() {
      const carousel = document.getElementById("carousel");
      const indicators = document.querySelectorAll(".indicator");
      let currentIndex = 0;
      // Função para atualizar o carrossel
      function atualizarCarrossel() {
        carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
        indicators.forEach((dot, i) => {
          dot.classList.toggle("opacity-100", i === currentIndex);
        });
      }

      // Função para avançar para o próximo slide
      function proximoSlide() {
        currentIndex = (currentIndex + 1) % indicators.length;
        atualizarCarrossel();
      }

      // Iniciar o intervalo de 5 segundos
      setInterval(proximoSlide, 5000);
    }

    // Iniciar o carrossel automático
    iniciarCarrosselAutomatico();
  });

  // Adicione estas variáveis para controle do arrastar/deslizar
  let startX = 0;
  let isDragging = false;
  let currentTranslate = 0;
  let prevTranslate = 0;

  // Função para atualizar o carrossel manualmente
  function updateCarouselManual(idx) {
    index = idx;
    carousel.style.transform = `translateX(-${index * 100}%)`;
    indicators.forEach((dot, i) => {
      dot.classList.toggle("opacity-100", i === index);
    });
  }

  // Eventos de mouse (desktop)
  carousel.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.pageX;
    prevTranslate = -index * carousel.offsetWidth;
    carousel.style.transition = 'none';
  });
  document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
  });
  document.addEventListener('mouseup', (e) => {
    if (!isDragging) return;
    let diff = e.pageX - startX;
    if (diff > 50) {
      // Arrastou para a direita
      index = (index - 1 + indicators.length) % indicators.length;
    } else if (diff < -50) {
      // Arrastou para a esquerda
      index = (index + 1) % indicators.length;
    }
    carousel.style.transform = `translateX(-${index * 100}%)`;
    indicators.forEach((dot, i) => {
      dot.classList.toggle("opacity-100", i === index);
    });
    isDragging = false;
  });

  // Eventos de toque (mobile)
  carousel.addEventListener('touchstart', (e) => {
    isDragging = true;
    startX = e.touches[0].clientX;
    prevTranslate = -index * carousel.offsetWidth;
    carousel.style.transition = 'none';
  });
  carousel.addEventListener('touchend', (e) => {
    if (!isDragging) return;
    let diff = e.changedTouches[0].clientX - startX;
    if (diff > 50) {
      // Arrastou para a direita
      index = (index - 1 + indicators.length) % indicators.length;
    } else if (diff < -50) {
      // Arrastou para a esquerda
      index = (index + 1) % indicators.length;
    }
    carousel.style.transform = `translateX(-${index * 100}%)`;
    indicators.forEach((dot, i) => {
      dot.classList.toggle("opacity-100", i === index);
    });
    isDragging = false;
  });

  // Carrossel manual + autoplay apenas para layout == 1
  document.addEventListener('DOMContentLoaded', function () {
    // Só ativa se existir o carrossel do layout 1
    const carousel = document.getElementById("carousel");
    const indicators = document.querySelectorAll(".indicator");
    if (!carousel || indicators.length <= 1) return;

    let index = 0;
    let intervalId = null;
    let startX = 0;
    let isDragging = false;
    let dragThreshold = 50;

    function updateCarousel() {
      carousel.style.transform = `translateX(-${index * 100}%)`;
      indicators.forEach((dot, i) => {
        dot.classList.toggle("opacity-100", i === index);
      });
    }

    function nextSlide() {
      index = (index + 1) % indicators.length;
      updateCarousel();
    }

    function prevSlide() {
      index = (index - 1 + indicators.length) % indicators.length;
      updateCarousel();
    }

    indicators.forEach(dot => {
      dot.addEventListener("click", (e) => {
        index = parseInt(e.target.dataset.index);
        updateCarousel();
        restartAutoplay();
      });
    });

    function startAutoplay() {
      stopAutoplay();
      intervalId = setInterval(nextSlide, 5000);
    }
    function stopAutoplay() {
      if (intervalId) clearInterval(intervalId);
    }
    function restartAutoplay() {
      stopAutoplay();
      startAutoplay();
    }

    // Mouse drag
    carousel.addEventListener('mousedown', (e) => {
      isDragging = true;
      startX = e.pageX;
      stopAutoplay();
    });
    document.addEventListener('mousemove', (e) => {
      if (!isDragging) return;
    });
    document.addEventListener('mouseup', (e) => {
      if (!isDragging) return;
      let diff = e.pageX - startX;
      if (diff > dragThreshold) {
        prevSlide();
      } else if (diff < -dragThreshold) {
        nextSlide();
      }
      isDragging = false;
      restartAutoplay();
    });

    // Touch drag
    carousel.addEventListener('touchstart', (e) => {
      isDragging = true;
      startX = e.touches[0].clientX;
      stopAutoplay();
    });
    carousel.addEventListener('touchend', (e) => {
      if (!isDragging) return;
      let diff = e.changedTouches[0].clientX - startX;
      if (diff > dragThreshold) {
        prevSlide();
      } else if (diff < -dragThreshold) {
        nextSlide();
      }
      isDragging = false;
      restartAutoplay();
    });

    // Inicialização
    updateCarousel();
    startAutoplay();
  });
</script>

</html>