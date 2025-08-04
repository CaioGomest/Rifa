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
  $config = listaInformacoes($conn);
  $campos_obrigatorios = explode(',', $config['campos_obrigatorios']);
  $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
  $campanhas = listaCampanhas($conn, $id, NULL, 1);
  $numeros_disponiveis = obterNumerosDisponiveis($conn, $id);

  if ($campanhas[0]['cotas_premiadas'])
    $cotas_premiadas = explode(',', $campanhas[0]['cotas_premiadas']);
  else
    $cotas_premiadas = [];

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

<body class="bg-gray-100 text-black dark:bg-gray-900 dark:text-white transition-all">

  <?php
  if ($campanhas[0]["layout"] == 0) {
    ?>
    <!-- Main Content -->
    <main class="container mx-auto px-0 py-8">
      <section class="mb-10 w-full md:w-4/5 lg:w-3/5 mx-auto relative -top-12" style="margin-bottom: 0px;">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-lg shadow-lg p-[18px]">
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

          </script>


          <!-- Barra de Progresso -->
          <?php if ($campanhas[0]['habilitar_barra_progresso'] == '1'): ?>
            <div class="mt-4">
              <div class="w-full bg-gray-300 dark:bg-gray-700 rounded-full h-6 mb-2 relative overflow-hidden">
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
          <div class="flex justify-center sm:justify-start gap-1 sm:gap-2 mb-6 mt-2 px-2 sm:px-0">
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
          <div class="mb-6">
            <p class="bg-white dark:bg-gray-700 p-2 rounded-lg text-[15px] font-semibold mb-2">
              <?php echo $campanhas[0]['descricao']; ?>
            </p>
            <?php if (!empty($campanhas[0]['subtitulo'])): ?>
              <p class="text-sm text-gray-400 mt-2"><?php echo $campanhas[0]['subtitulo']; ?></p>
            <?php endif; ?>
          </div>

          <!-- Notificação de Cotas em Dobro -->
          <?php if ($campanhas[0]['habilitar_cotas_em_dobro'] == '1'): ?>
            <div class="mb-6">
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
            <div class="mb-6">
              <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md">
                <p class="text-sm text-purple-800 dark:text-purple-200">
                  <span class="font-bold">⭐ Oferta Exclusiva!</span><br>
                  Compre com desconto: Condição especial de pacotes por tempo LIMITADO! Não perca essa oportunidade de
                  aumentar suas chances de ganhar!
                </p>
              </div>
            </div>
          <?php endif; ?>

          <!-- Adição Rápida -->
          <?php if (!$modo_exclusivo && $campanhas[0]['habilitar_adicao_rapida'] == '1'): ?>
            <div class="mb-6">
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
            <div class="mb-6">
              <p class="text-sm font-medium text-black dark:text-white">Pacotes padrão</p>

              <?php
              $pacotes_promocionais = $campanhas[0]['pacote_promocional'];
              $pacotes_promocionais = json_decode($pacotes_promocionais, true);
              echo "<div class='grid " . (count($pacotes_promocionais) <= 1 ? 'grid-cols-1' : 'grid-cols-2') . " gap-2 mt-2'>";
              if (is_array($pacotes_promocionais) && !empty($pacotes_promocionais)) {
                foreach ($pacotes_promocionais as $pacote) {
                  $qtd = intval($pacote['quantidade_numeros']);
                  $valor_original = $qtd * $campanhas[0]['preco'];
                  echo "<button onclick='alterarQuantidadePromocional($qtd, " . $pacote['valor_pacote'] . ")' class='bg-white dark:bg-gray-700 text-center p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-all'>
                        <div class='text-black dark:text-white text-lg font-bold mb-1'>$qtd números</div>
                        <div class='text-red-500 dark:text-red-600 text-sm mb-1 line-through'>R$ " . number_format($valor_original, 2, ',', '.') . "</div>
                        <div class='text-green-500 dark:text-green-600 text-xl font-bold'>R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . "</div>
                   
                        </button>";
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
          <div class="mb-6">
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
                  echo "<button onclick='alterarQuantidadePromocional($qtd, " . $pacote['valor_pacote'] . ")' class='bg-purple-600 text-white py-2 rounded text-center'>
                            <span class='block text-lg font-bold'>$qtd números</span>
                            <span class='block text-red-300 text-sm mb-1 line-through'>R$ " . number_format($valor_original, 2, ',', '.') . "</span>
                            <span class='block text-green-300 text-lg font-bold'>R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . "</span>
                          </button>";
                }
              }
              echo '</div>';
            } else {
              echo "<p class='text-sm text-gray-400'>Nenhum pacote exclusivo disponível no momento.</p>";
            }
            ?>
          </div>
        <?php endif; ?>


        <div class="mb-6">
          <label for="quantidade" class="block text-sm font-medium text-black dark:text-white">
            Quantidade (Mín: <?php echo $compra_minima; ?>)
          </label>

          <!-- Custom Quantidade Seletor -->
          <div
            class="flex items-center justify-center gap-2 mt-4 mb-2 w-full px-4 bg-gray-100 dark:bg-gray-800 p-2 rounded-2xl flex-col border border-gray-300 dark:border-[#000000]">

            <div class="flex items-center space-x-2">
              <button onclick="alterarQuantidade(-5,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">-5</button>
              <button onclick="alterarQuantidade(-1,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">-</button>

              <input type="number" id="quantidade_barra" value="<?php echo $compra_minima; ?>"
                class="flex-1 text-center bg-gray-100 dark:bg-gray-700 text-black dark:text-white rounded-lg border-none"
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
          <div class="flex justify-between text-xs text-gray-400 mt-2">
            <span></span>
            <span class="text-red-500" style="display: none;">Máximo: <?php echo $compra_maxima; ?></span>
          </div>
        </div>


        <!-- Comprar -->

        <form action="processamento_pagamento.php" method="POST" id="formComprar" onsubmit="return validarCompra(event)">
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
          
          if(!isset($_SESSION['usuario']) || $isCliente)
          {
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
        
        if(!isset($_SESSION['usuario']) || $isCliente)
        {
        ?>
        <div class="w-full flex flex-col gap-2 mt-2">
          <button onclick="verificarLogin('titulos')"
            class="border border-[#1BC863] text-[#1BC863] rounded-lg font-semibold px-4 py-2 flex items-center justify-center gap-2 transition-all mb-1">
            <span class="iconify" data-icon="tabler:numbers"></span>
            Ver meus números
          </button>
        </div>
        <?php
        }  
        ?>

        <!-- cotas -->
        <?php

        if (!empty($campanhas[0]['mostrar_cotas_premiadas']) && !empty($campanhas[0]['cotas_premiadas'])): ?>
          <div class="mt-4">
            <p class="text-sm font-medium text-black dark:text-white mb-2">🔥 Cotas premiadas - Achou ganhou!</p>
            <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-6 lg:grid-cols-6 gap-2 font-bold">
              <?php
              $cotas_premiadas = explode(',', $campanhas[0]['cotas_premiadas']);
              foreach ($cotas_premiadas as $cota) {
                echo
                  "<div class='bg-green-600 text-black dark:text-white text-center py-2 px-1 rounded-lg'>" . trim($cota) . "</div>";
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
            <div class="mt-4">
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
      </section>
    </main>
  <?php } else { ?>
    <!-- Main Content -->
    <main class="container mx-auto px-0 py-8">
      <section class=" w-full md:w-4/5 lg:w-3/5 mx-auto relative -top-12" style=" margin-bottom: 0px;">

        <div
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg w-full p-0 md:p-4 flex flex-col items-center relative p-2">
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








          <div class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-700 ">
            <span class="bg-[#1BC863] text-white text-xs rounded px-3 py-1 font-bold inline-block mb-2">Adquira já!</span>
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


          <div class="w-full">
            <?php if ($campanhas[0]['habilitar_barra_progresso'] == '1' || $config['habilitar_compartilhamento'] == '1' || $config['habilitar_grupos'] == '1'): ?>
              <!-- Barra de Progresso -->
              <?php if ($campanhas[0]['habilitar_barra_progresso'] == '1'): ?>
                <div class="mt-4">
                  <div class="w-full bg-gray-300 dark:bg-gray-500 rounded-full h-6 mb-2 relative overflow-hidden">
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

              <div class="flex justify-center sm:justify-start gap-1 sm:gap-2 mt-2 px-2 sm:px-0">
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
            <?php endif; ?>
          </div>
          <div class="flex flex-row items-center gap-2 justify-center mt-2">
            <span class="text-gray-600 dark:text-[#b8b9ad] font-medium">por apenas</span>
            <span class="bg-[#343b41] rounded px-2 py-1 text-white font-bold">R$ <?php
            echo isset($campanhas[0]['preco']) ? $campanhas[0]['preco'] : "-";
            ?></span>
          </div>

          <!-- Chamada promocional -->
          <div class="w-full mt-2 text-center">
            <span class="block font-semibold text-gray-700 dark:text-[#b8b9ad] bg-gray-100  dark:bg-gray-700 rounded-2xl p-4">
              <?php
              echo isset($campanhas[0]["descricao"]) ? $campanhas[0]["descricao"] : '';
              ?>
            </span>
          </div>

          <!-- Notificação de Cotas em Dobro -->
          <?php if ($campanhas[0]['habilitar_cotas_em_dobro'] == '1'): ?>
            <div class="my-4 w-full">
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
            <div class="my-4 w-full">
              <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-md">
                <p class="text-sm text-purple-800 dark:text-purple-200">
                  <span class="font-bold">⭐ Oferta Exclusiva!</span><br>
                  Compre com desconto: Condição especial de pacotes por tempo LIMITADO! Não perca essa oportunidade de
                  aumentar suas chances de ganhar!
                </p>
              </div>
            </div>
          <?php endif; ?>





          <!-- Barra de promoções -->
          <div class="flex mt-2 items-center w-full">
            <h1 class="text-1xl md:text-3xl font-bold mb-1">
              📣 Promoção
            </h1>
            <span class="flex justify-center items-center ml-2">Compre mais barato!</span>
          </div>


          <div class="w-full px-4 mt-3 flex flex-wrap gap-2 justify-center bg-gray-100 dark:bg-gray-700 rounded-2xl p-2">
            <?php
            $pacotes_promocionais_origin = $campanhas[0]['pacote_promocional'];
            $pacotes_promocionais_origin = json_decode($pacotes_promocionais_origin, true);
            
            // var_dump($pacotes_promocionais_origin);die;

            foreach ($pacotes_promocionais_origin as $pacote)
            {
                $qtd = intval($pacote['quantidade_numeros']);
                $valor_original = $qtd * $campanhas[0]['preco'];
            
                // Adicionando novos pacotes ao array
                $pacotes_promocionais[] = [
                    "valor_bilhete" => $pacote['valor_bilhete'],
                    "quantidade_numeros" => $pacote['quantidade_numeros'],
                    "valor_pacote" => $pacote['valor_pacote']
                ];
            }
            

            if (is_array($pacotes_promocionais) && !empty($pacotes_promocionais)) {
              foreach ($pacotes_promocionais as $pacote) {
                $qtd = intval($pacote['quantidade_numeros']);
                $valor_original = $qtd * $campanhas[0]['preco'];

                echo "<span style='background: #1BC863!important;' class='bg-[#1BC863] pacote-promocional' 
                onclick=\"alterarQuantidadePromocional
                ($qtd, '" . $pacote['valor_pacote'] . "')\">$qtd por R$ " . number_format($pacote['valor_pacote'], 2, ',', '.') . "</span>";
              }
            } else {
              echo "<p class='text-sm text-gray-400'>Nenhum pacote promocional disponível no momento.</p>";
            }
            ?>
          </div>

          <style>
          .text-base {
              font-size: 0.75rem; /* Ajuste o valor conforme necessário */
            }
            .pacote-promocional {
              text-align: center;
              background-color: #343b41;
              color: white;
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
          <div class="grid grid-cols-3 gap-1 w-full mt-4">

            <?php
            $incrementos = [25, 50, 100, 250, 500, 1000];
            foreach ($incrementos as $valor) {
              $isPopular = $valor === 50;
              $bgClass = $isPopular ? "bg-[#1BC863]" : "bg-gray-600 dark:bg-gray-700";
              $textColor = $isPopular ? "text-white" : "text-white";
              $borderClass = "border border-[#343b41]";
              $hoverClass = $isPopular ? "" : "hover:bg-[#232a32]";
              $extraClass = $isPopular ? "relative" : "";

              $selectTextColor = $isPopular ? "text-white" : "text-gray-600 dark:text-[#b8b9ad]";

              echo "<button onclick=\"alterarQuantidade($valor, true)\" class='$bgClass $textColor py-4 rounded-xl $borderClass font-semibold $hoverClass transition borda-animada $extraClass'>
                      " . ($isPopular ? "<div  style='width: 75%;'   class='absolute -top-3 left-1/2 transform -translate-x-1/2 bg-[#148f4a] text-white text-[10px] font-bold px-2 py-[2px] rounded-md'>Mais popular</div>" : "") . "
                      +$valor<br />
                      <span class='text-xs $selectTextColor'>
                        SELECIONAR
                      </span>
                    </button>";
            }
            ?>



          </div>

          
          <div style="display: flex;justify-content: start;width: 100%;" class="mt-4">
            <label for="quantidade" class="block text-sm font-medium text-black dark:text-white">
              Quantidade (Mín: <?php echo $compra_minima; ?>)
            </label>
          </div>

          <!-- Custom Quantidade Seletor -->
          <div
            class="flex items-center justify-center gap-2 mt-4 mb-2 w-full px-4 bg-gray-100 dark:bg-gray-800 p-2 rounded-2xl flex-col border border-gray-300 dark:border-[#000000]">
            <div class="flex items-center space-x-2">
              <button onclick="alterarQuantidade(-5,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">-5</button>
              <button onclick="alterarQuantidade(-1,true)"
                class="bg-[#1BC863] hover:bg-[#43996d] text-white rounded-lg font-bold w-10 h-10 text-xl">-</button>

              <input type="number" id="quantidade_barra" value="<?php echo $compra_minima; ?>"
                class="flex-1 text-center bg-gray-100 dark:bg-gray-700 text-black dark:text-white rounded-lg border-none"
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
          <div class="w-full px-4 mb-2">





            <form action="processamento_pagamento.php" method="POST" id="formComprar"
              onsubmit="return validarCompra(event)">
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
              
              if(!isset($_SESSION['usuario']) || $isCliente)
              {
                
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
        
        if(!isset($_SESSION['usuario']) || $isCliente)
        {
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
          }  ?>


              <!-- cotas -->
              <?php

              if (!empty($campanhas[0]['mostrar_cotas_premiadas']) && !empty($campanhas[0]['cotas_premiadas'])): ?>
                <div class="mt-4">
                  <p class="text-sm font-medium text-black dark:text-white mb-2">🔥 Cotas premiadas - Achou ganhou!</p>
                  <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-6 lg:grid-cols-6 gap-2 font-bold">
                    <?php
                    $cotas_premiadas = explode(',', $campanhas[0]['cotas_premiadas']);
                    foreach ($cotas_premiadas as $cota) {
                      echo
                        "<div class='bg-green-600 text-black dark:text-white text-center py-2 px-1 rounded-lg'>" . trim($cota) . "</div>";
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
                  <div class="w-full px-4 mb-2">
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
        </div>
        </>

    </main>
    <?php
  } ?>

  <!-- Modal Verificação por Telefone -->
  <div id="modalTelefone" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="m-4 bg-white dark:bg-gray-800  p-8 rounded-lg w-96 relative">
      <button onclick="fecharModal('modalTelefone')" class="absolute top-4 right-4 text-gray-400 hover:text-white">
        <i class="fas fa-times"></i>
      </button>

      <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Verificar Cliente</h3>

      <form id="formTelefone" class="space-y-4">
        <div>
          <label class="block text-gray-700 dark:text-gray-300 mb-2">Seu Telefone</label>
          <input type="tel" name="telefone" required
            class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-800 dark:text-white"
            placeholder="Digite seu número de telefone">
        </div>

        <button type="submit"
          class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold">
          Verificar
        </button>
      </form>
    </div>
  </div>

  <!-- Modal Cadastro -->
  <div id="modalCadastro" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="m-4 bg-white dark:bg-gray-800  p-8 rounded-lg w-96 relative">
      <button onclick="fecharModal('modalCadastro')" class="absolute top-4 right-4 text-gray-400 hover:text-white">
        <i class="fas fa-times"></i>
      </button>

      <h3 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">Cadastro</h3>

      <form id="formCadastro" class="space-y-4">
        <input type="hidden" name="telefone" id="telefone_cadastro">

        <?php if (empty($campos_obrigatorios) || in_array('nome', $campos_obrigatorios)): ?>
          <div>
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Seu Nome</label>
            <input type="text" name="nome" <?php echo in_array('nome', $campos_obrigatorios) ? 'required' : ''; ?>
              class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-800 dark:text-white"
              placeholder="Digite seu nome completo">
          </div>
        <?php endif; ?>

        <?php if (empty($campos_obrigatorios) || in_array('email', $campos_obrigatorios)): ?>
          <div>
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Seu E-mail</label>
            <input type="email" name="email" <?php echo in_array('email', $campos_obrigatorios) ? 'required' : ''; ?>
              class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-800 dark:text-white"
              placeholder="Digite seu e-mail">
          </div>
        <?php endif; ?>

        <?php if (in_array('cpf', $campos_obrigatorios)): ?>
          <div>
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Seu CPF</label>
            <input type="text" name="cpf" required
              class="w-full bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-800 dark:text-white"
              placeholder="Digite seu CPF">
          </div>
        <?php endif; ?>

        <button type="submit"
          class="bg-green-600 hover:bg-green-700 text-white w-full py-3 rounded-lg text-lg font-bold">
          Cadastrar
        </button>
      </form>
    </div>
  </div>

  <?php require_once('footer.php') ?>

</body>

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
      // Se for um cliente logado, pode comprar e ver títulos
      if (acao == 'comprar') {
        document.getElementById('formComprar').cliente_id.value = clienteId;
        document.getElementById('formComprar').submit();
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

    // Caso não esteja logado, redireciona para a página de login
    const campanhaId = new URLSearchParams(window.location.search).get('id');
    window.location.href = 'login.php?acao=' + acao + (campanhaId ? '&campanha_id=' + campanhaId : '');
  }


  function fecharModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.getElementById(modalId).classList.remove('flex');
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
    document.getElementById('btnComprar').innerHTML = 'Comprar por R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Mostrar/ocultar mensagem de máximo
    const mensagemMaximo = document.querySelector('.text-red-500');
    if (novaQuantidade >= compraMaxima) {
      mensagemMaximo.style.display = 'block';
    } else {
      mensagemMaximo.style.display = 'none';
    }
  }

  function alterarQuantidadePromocional(qtd, precoUnitario, adicionar = false) {
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
    if (novaQuantidade > compraMaxima) {
      novaQuantidade = compraMaxima;
      // Ajustar o preço proporcionalmente
      precoUnitario = (precoUnitario * compraMaxima) / qtd;
    }

    const valorTotal = precoUnitario;

    document.getElementById('quantidade_barra').value = novaQuantidade;
    document.getElementById('quantidade').value = novaQuantidade;
    document.getElementById('valor_total').value = valorTotal;
    document.getElementById('btnComprar').innerHTML = 'Comprar por R$ ' + valorTotal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Mostrar/ocultar mensagem de máximo
    const mensagemMaximo = document.querySelector('.text-red-500');
    if (novaQuantidade >= compraMaxima) {
      mensagemMaximo.style.display = 'block';
    } else {
      mensagemMaximo.style.display = 'none';
    }

    
    // Redirecionar diretamente para o checkout
    if (clienteId) {
      // Se clienteId existir, já é um cliente logado, então podemos submeter diretamente
      document.getElementById('formComprar').cliente_id.value = clienteId;
      document.getElementById('formComprar').submit();
    } else {
      // Se não estiver logado, abre o modal de telefone
      abrirModalTelefone('comprar');
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
  });

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

  // Formulário de Verificação por Telefone
  document.getElementById('formTelefone').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('verificar_cliente_telefone.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Atualiza o clienteId após sucesso
          document.getElementById('cliente_id').value = data.cliente_id;
          fecharModal('modalTelefone'); // Fecha o modal de telefone

          // Realiza a ação pós-login (compra ou títulos)
          if (acaoAposLogin == 'comprar') {
            document.getElementById('formComprar').submit(); // Se for comprar, submete o formulário
          } else if (acaoAposLogin == 'titulos') {
            window.location.href = 'meus_titulos.php?cliente_id=' + data.cliente_id; // Se for ver títulos, redireciona
          }
        } else if (data.need_register) {
          // Se for necessário o cadastro, abre o modal de cadastro
          document.getElementById('telefone_cadastro').value = formData.get('telefone');
          fecharModal('modalTelefone');
          document.getElementById('modalCadastro').classList.remove('hidden');
          document.getElementById('modalCadastro').classList.add('flex');
        } else {
          alert(data.message || 'Ocorreu um erro ao processar sua solicitação. Tente novamente.');
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
      });
  });

  // Formulário de Cadastro
  document.getElementById('formCadastro').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('verificar_cliente.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('cliente_id').value = data.cliente_id;
          fecharModal('modalCadastro');

          if (acaoAposLogin == 'comprar') {
            document.getElementById('formComprar').submit();
          } else if (acaoAposLogin == 'titulos') {
            window.location.href = 'meus_titulos.php?cliente_id=' + data.cliente_id;
          }
        } else {
          alert(data.message || 'Ocorreu um erro ao processar sua solicitação. Tente novamente. (2)');
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        alert('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
      });
  });

  function validarCompra(event) {
    event.preventDefault();

    const quantidade = parseInt(document.getElementById('quantidade_barra').value);
    const campanha_id = document.querySelector('input[name="campanha_id"]').value;
    const cliente_id = document.getElementById('cliente_id').value;
    const codigo_afiliado = document.querySelector('input[name="codigo_afiliado"]').value;

    if (!cliente_id) {
      // alert('Por favor, faça login antes de comprar.');
      abrirModalTelefone();
      return false;
    }
    // Atualizar os valores do formulário antes de enviar
    document.getElementById('numeros_solicitados').value = JSON.stringify(numeros_solicitados);
    document.getElementById('quantidade').value = quantidade;
    document.getElementById('valor_total').value = quantidade * <?php echo $campanhas[0]['preco']; ?>;

    // Enviar para validação
    fetch('validar_compra.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        campanha_id: campanha_id,
        numeros_solicitados: numeros_solicitados,
        cliente_id: cliente_id,
        quantidade: quantidade,
        codigo_afiliado: codigo_afiliado
      })
    })
      .then(response => response.json())
      .then(data => {
        if (data.sucesso) {
          document.getElementById('formComprar').submit();
        } else {
          alert(data.mensagem);
        }
      })
      .catch(error => {
        console.error('Erro:', error);
        // alert('Ocorreu um erro ao validar sua compra. Por favor, tente novamente.');
      });

    return false;
  }

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
    background-color: #4b5563;
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
    background-color: #4b5563;
  }
  
  .dark .hover\:bg-\[#232a32\]:hover {
    background-color: #232a32;
  }
</style>

</html>