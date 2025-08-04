<?php
require_once('header.php');
require 'conexao.php';
$campanhas = listaCampanhas($conn, NULL, NULL, 1, NULL, "data_criacao DESC", NULL, NULL, NULL, 0, 0);
$destaque = null;

// Se houver campanhas
if (!empty($campanhas)) {
  $destaques = array_filter($campanhas, function ($campanha) {
    return $campanha['campanha_destaque'] == 1;
  });

  if (count($destaques) > 0) {
    // Ordena por data_criacao DESC para pegar o mais recente
    usort($destaques, function ($a, $b) {
      return strtotime($b['data_criacao']) - strtotime($a['data_criacao']);
    });
    $destaque = $destaques[0];
    // Remove o destaque da lista geral
    $campanhas = array_filter($campanhas, function ($campanha) use ($destaque) {
      return $campanha['id'] != $destaque['id'];
    });
  } else {
    // Nenhum com destaque = 1, então usa o primeiro como destaque
    $destaque = array_shift($campanhas);
  }
}
?>

<?php

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
    fbq('track', 'PageView');
  </script>

  <noscript>
    <img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id=<?php echo $config['pixel_facebook']; ?>&ev=PageView&noscript=1" />
  </noscript>
  <!-- Fim do Pixel -->
<?php endif; ?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
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
</head>

<body class="bg-gray-100 text-black dark:bg-gray-900 dark:text-white transition-all">
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-40"></div>
  <main class="container mx-auto px-0 py-8">
    <!-- Destaques -->

    <section class=" w-full md:w-4/5 lg:w-3/5 mx-auto relative -top-12">

      <?php if (!empty($destaque)): ?>
        <div class="dark:bg-gray-800 bg-white rounded-t-lg shadow-lg p-2">
          <a href="campanha.php?id=<?php echo $destaque['id']; ?>">
            <h2 class="bg-gray-200 dark:bg-gray-700 p-2 rounded-lg text-lg font-semibold mb-4">
              <span class="flex">
                <img src="assets/css/estrela.svg" alt="estrela" style="fill: #22C55A;margin-right:5px;">
                Destaques
              </span>
            </h2>

            <div class="hover-effect bg-gray-300 dark:bg-gray-700 rounded-lg overflow-hidden shadow borda-animada">
              <div class="p-4 ">
                <div class="bg-gray-400 dark:bg-gray-600 rounded-lg flex items-center justify-center">
                  <?php if (!empty($destaque['caminho_imagem'])): ?>
                    <img src="<?php echo $destaque['caminho_imagem']; ?>" alt="<?php echo $destaque['nome']; ?>" class="img-campanha-16-9">
                  <?php else: ?>
                    <span class="dark:text-white text-black text-2xl">Imagem não disponível</span>
                  <?php endif; ?>
                </div>
                <div class="mt-4">
                  <h3 class="text-xl font-bold dark:text-white text-black"><?php echo $destaque['nome']; ?></h3>
                  <p class="text-sm dark:text-white text-black mt-1"><?php echo $destaque['descricao']; ?></p>
                  <p class="text-lg font-bold dark:text-white text-black mt-4 flex justify-between items-center">
                    R$ <?php echo $destaque['preco']; ?>
                    <button
                      class="mt-4 bg-green-500 text-white px-5 py-2 rounded-md hover:bg-green-600 dark:bg-green-400 dark:hover:bg-green-500 blink-button">
                      Comprar
                    </button>
                    
                    <style>
                      .blink-button {
                        animation: blink-animation 1.8s infinite;
                      }
                    
                      @keyframes blink-animation {
                        0% {
                          opacity: 1;
                        }
                        50% {
                          opacity: 0;
                        }
                        100% {
                          opacity: 1;
                        }
                      }
                </style>

                  </p>
                </div>
              </div>
            </div>
          </a>
        </div>

        <?php if (!empty($campanhas)): ?>
          <div class="dark:bg-gray-800 bg-white rounded-b-lg shadow-lg p-2 pt-0">
            <?php foreach ($campanhas as $campanha): ?>
              <a href="campanha.php?id=<?php echo $campanha['id']; ?>">
                <div class="cursor-pointer hover-effect flex items-center bg-gray-300 dark:bg-gray-700 rounded-lg p-4 mb-4 borda-animada">
                  <div class="w-20 h-20 rounded-full bg-gray-400 dark:bg-gray-600 flex items-center justify-center">
                    <?php if (!empty($campanha['caminho_imagem'])): ?>
                      <img src="<?php echo $campanha['caminho_imagem']; ?>" alt="<?php echo $campanha['nome']; ?>"
                        class="w-full h-full object-cover rounded-full">
                    <?php else: ?>
                      <span class="dark:text-white text-black text-sm">Sem imagem</span>
                    <?php endif; ?>
                  </div>
                  <div class="ml-4">
                    <h3 class="text-lg font-bold"><?php echo $campanha['nome']; ?></h3>
                    <p class="text-sm dark:text-white text-black"><?php echo $campanha['descricao']; ?></p>
                    <p class="text-sm dark:text-white text-black">R$ <?php echo $campanha['preco']; ?></p>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      <?php else: ?>
        <div class="dark:bg-gray-800 bg-white rounded-lg shadow-lg p-8 text-center">
          <div class="flex flex-col items-center justify-center space-y-4">
            <div class="w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
              <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold dark:text-white text-black">Nenhuma campanha disponível</h3>
            <p class="text-gray-400 dark:text-gray-600">No momento não temos campanhas ativas. Novas campanhas serão
              adicionadas em breve!</p>
            <p class="text-gray-400 dark:text-gray-600">Fique de olho em nossas redes sociais para novidades.</p>
          </div>
        </div>
      <?php endif; ?>


      <!-- Compartilhar e Grupo -->
      <?php if ($config['habilitar_fale_conosco'] == '1'): ?>
        <div class="dark:bg-gray-800 bg-white rounded-lg shadow-lg p-2 mt-10">
          <div class="flex justify-center">
            <a href="<?= $config['link_fale_conosco'] ?>" target="_blank"
                              class="flex-1 sm:flex-none bg-gray-300 dark:bg-gray-700 text-white py-1.5 rounded-lg flex items-center justify-center gap-1 sm:gap-2 p-4">
              <div class="icone font-lg bg-dark rounded p-2 bg-opacity-10"></div>
              <i class="fas fa-share text-xs sm:text-sm"></i>
              <span class="text-[11px] sm:text-sm whitespace-nowrap">Dúvidas? Fale conosco</span>
            </a>
          </div>
        </div>
      <?php endif; ?>


      <?php
      $perguntas = $config['perguntas_frequentes'];
      $perguntas = preg_replace('/\s+/', ' ', $perguntas);
      $perguntas = array_filter(explode('/', $perguntas), function ($item) {
        return trim($item) !== '';
      });

      if (!empty($perguntas)){
        ?>
        <!-- Perguntas Frequentes -->
        <div class="dark:bg-gray-800 bg-white rounded-b-lg shadow-lg p-2 mt-10">
          <h2 class="bg-gray-300 dark:bg-gray-700 p-2 rounded-lg text-black dark:text-white text-lg mb-4">🙋🏼 Perguntas
            frequentes</h2>
          <div class="space-y-4">
            <?php
            // Agrupa perguntas e respostas
            $faq = [];
            for ($i = 0; $i < count($perguntas); $i += 2) {
              if (isset($perguntas[$i]) && trim($perguntas[$i]) !== '') {
                $faq[] = [
                  'pergunta' => trim($perguntas[$i]),
                  'resposta' => isset($perguntas[$i + 1]) ? trim($perguntas[$i + 1]) : ''
                ];
              }
            }
            foreach ($faq as $item):
              ?>
                              <div class="cursor-pointer hover-effect bg-gray-300 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-bold dark:text-white text-black mb-2"><?php echo $item['pergunta']; ?></h3>
                <p class="dark:text-white text-black"><?php echo $item['resposta']; ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php
      }

      ?>
    </section>
  </main>
  <?php require_once('footer.php'); ?>

  <script>
    function compartilhar() {
      if (navigator.share) {
        navigator.share({
          title: 'Rifa Online',
          text: 'Corre que está acabando! Venha participar da nossa rifa online!',
          url: window.location.href
        })
          .then(() => console.log('Compartilhado com sucesso!'))
          .catch((error) => console.log('Erro ao compartilhar:', error));
      } else {
        // Fallback para navegadores que não suportam a API de compartilhamento
        const tempInput = document.createElement('input');
        tempInput.value = window.location.href;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        alert('Link copiado para a área de transferência!');
      }
    }
  </script>
</body>

</html>