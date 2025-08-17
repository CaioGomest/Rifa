<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
// Postback: salvar imagens via PHP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


	$upload_dir = __DIR__ . '/../uploads/campanhas/';
	if (!file_exists($upload_dir)) { @mkdir($upload_dir, 0777, true); }

	$caminho_atual = $_POST['caminho_imagem_atual'] ?? '';
	$imagem_capa_atual = $_POST['imagem_capa_atual'] ?? '';
	$remover_imagem_principal = isset($_POST['remover_imagem_principal']) && $_POST['remover_imagem_principal'] === '1';
	$remover_imagem_capa = isset($_POST['remover_imagem_capa']) && $_POST['remover_imagem_capa'] === '1';

	if ($remover_imagem_principal) { $caminho_atual = ''; }
	if ($remover_imagem_capa) { $imagem_capa_atual = ''; }

	$deve_subir_principal = (isset($_FILES['imagem_principal']) && $_FILES['imagem_principal']['error'] === UPLOAD_ERR_OK);
	$deve_subir_capa = (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK);

	// Imagem principal: define valor somente quando removido ou enviado novo; caso contrário, mantém NULL para não atualizar
	if ($remover_imagem_principal) {
		$caminho_imagem = '';
	} elseif ($deve_subir_principal) {
		$novo_principal = editarImagemPrincipal('imagem_principal', $caminho_atual, $upload_dir);
		$caminho_imagem = ($novo_principal === $caminho_atual) ? NULL : $novo_principal;
	} else {
		$caminho_imagem = NULL;
	}

	// Imagem de capa: define valor somente quando removido ou enviado novo; caso contrário, mantém NULL para não atualizar
	if ($remover_imagem_capa) {
		$imagem_capa = '';
	} elseif ($deve_subir_capa) {
		$nova_capa = editarImagemPrincipal('imagem_capa', $imagem_capa_atual, $upload_dir);
		$imagem_capa = ($nova_capa === $imagem_capa_atual) ? NULL : $nova_capa;
	} else {
		$imagem_capa = NULL;
	}

	$deve_subir_galeria = (isset($_FILES['galeria']) && is_array($_FILES['galeria']['name']) && array_filter($_FILES['galeria']['name']));
	$galeria_atual_post_str = $_POST['galeria_imagens_atual'] ?? '';
	$galeria_atual = $galeria_atual_post_str !== '' ? explode(',', $galeria_atual_post_str) : [];

	if (!$deve_subir_galeria) {
		$galeria_imagens = (trim($galeria_atual_post_str) === trim($campanha['galeria_imagens'] ?? ''))
			? NULL
			: implode(',', array_filter($galeria_atual));
	} else {
		$galeria_imagens = editarGaleriaImagens('galeria', $galeria_atual, $upload_dir);
	}

	$resultado = editaCampanha(
		$conn, $id,
		null, null, null,
		$caminho_imagem,
		$galeria_imagens,
		// 6..14
		null, null, null, null, null, null, null, null, null,
		// 15..23
		null, null, null, null, null, null, null, null, null,
		// 24..31
		null, null, null, null, null, null, null, null,
		// 32..39
		null, null, null, null, null, null, null, null,
		// 40..44
		null, null, null, null, null,
		$imagem_capa,
		// 46..54
		null, null, null, null, null, null, null, null, null
	);
	if ($resultado === true) { $mensagem_sucesso = 'Imagens salvas com sucesso!'; }
	else { $mensagem_erro = is_string($resultado) ? $resultado : 'Erro ao salvar imagens.'; }
	$campanha = listaCampanhas($conn, $id); $campanha = ($campanha && is_array($campanha)) ? $campanha[0] : $campanha;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><title>Editar Campanha - Imagens</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
	<div class="flex h-screen">
		<?php require("sidebar.php"); ?>
		<main class="flex-1 p-6 overflow-y-auto max-h-screen">
			<header class="flex justify-between items-center mb-6">
				<h1 class="text-2xl font-bold">Editar Campanha - Imagens</h1>
				<a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
			</header>
			<div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
				<div class="flex justify-between items-center mb-4">
					<h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
				</div>
				<?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
				<?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
				<form method="POST" enctype="multipart/form-data" class="space-y-4">
					<h3 class="text-lg font-semibold mb-6">Imagens da Campanha</h3>
					<div class="space-y-6">
						<div>
							<label class="block mb-2 font-medium">Imagem de capa (listagem)</label>
							<div class="flex flex-col space-y-4">
								<div class="flex items-center justify-center w-full">
									<label for="imagem_capa" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-[#3F3F46] hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600" onclick="document.getElementById('remover_imagem_capa').value='0'">
										<div class="flex flex-col items-center justify-center pt-5 pb-6">
											<svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
												<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
											</svg>
											<p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
											<p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF (MAX. 2MB)</p>
										</div>
										<input type="file" name="imagem_capa" id="imagem_capa" accept="image/*" class="hidden" onchange="previewImagem(this, 'preview-capa')" />
									</label>
								</div>
								<?php if(!empty($campanha['imagem_capa'])): ?>
								<div class="relative inline-block w-fit group imagemAtual">
									<img src="../<?= $campanha['imagem_capa']?>" alt="Imagem de capa" class="max-w-[200px] rounded-lg shadow-md">
									<button type="button" onclick="removerImagemCapa(this)" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
										<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
									</button>
								</div>
								<?php endif; ?>
								<div id="preview-capa" class="hidden mt-4"><img src="" alt="Preview" class="max-w-[200px] rounded-lg shadow-md"></div>
								<input type="hidden" name="imagem_capa_atual" value="<?= $campanha['imagem_capa'] ?? '' ?>">
								<input type="hidden" id="remover_imagem_capa" name="remover_imagem_capa" value="0">
							</div>
						</div>
						<div>
							<label class="block mb-2 font-medium">Imagem principal</label>
							<div class="flex flex-col space-y-4">
								<div class="flex items-center justify-center w-full">
									<label for="imagem_principal" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-[#3F3F46] hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600" onclick="document.getElementById('remover_imagem_principal').value='0'">
										<div class="flex flex-col items-center justify-center pt-5 pb-6">
											<svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
												<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
											</svg>
											<p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
											<p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF (MAX. 2MB)</p>
										</div>
										<input type="file" name="imagem_principal" id="imagem_principal" accept="image/*" class="hidden" onchange="previewImagem(this, 'preview-principal')" />
									</label>
								</div>
								<?php if(!empty($campanha['caminho_imagem'])): ?>
								<div class="relative inline-block w-fit group imagemAtual">
									<img src="../<?= $campanha['caminho_imagem']?>" alt="Imagem atual" class="max-w-[200px] rounded-lg shadow-md">
									<button type="button" onclick="removerImagemPrincipal(this)" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
										<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
									</button>
								</div>
								<?php endif; ?>
								<div id="preview-principal" class="hidden mt-4"><img src="" alt="Preview" class="max-w-[200px] rounded-lg shadow-md"></div>
								<input type="hidden" name="caminho_imagem_atual" value="<?= $campanha['caminho_imagem'] ?? '' ?>">
								<input type="hidden" id="remover_imagem_principal" name="remover_imagem_principal" value="0">
							</div>
						</div>
						<div>
							<label class="block mb-2 font-medium">Galeria de imagens</label>
							<div class="flex items-center justify-center w-full mb-4">
								<label for="galeria" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-[#3F3F46] hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600">
									<div class="flex flex-col items-center justify-center pt-5 pb-6">
										<svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
											<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
										</svg>
										<p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Clique para enviar</span> ou arraste e solte</p>
										<p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG ou GIF (MAX. 2MB)</p>
									</div>
									<input type="file" name="galeria[]" id="galeria" accept="image/*" multiple class="hidden" onchange="previewGaleria(this)" />
								</label>
							</div>
							<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="galeria-atual">
								<?php if(!empty($campanha['galeria_imagens'])): foreach(explode(',', $campanha['galeria_imagens']) as $img): ?>
									<div class="relative group">
										<img src="../<?= $img ?>" alt="Imagem da galeria" class="w-full h-40 object-cover rounded-lg shadow-md">
										<button type="button" onclick="removerImagemGaleria(this, '<?= $img ?>')" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
											<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
										</button>
									</div>
								<?php endforeach; endif; ?>
							</div>
							<input type="hidden" id="galeria_imagens_atual" name="galeria_imagens_atual" value="<?= $campanha['galeria_imagens'] ?? '' ?>">
							<div id="preview-galeria" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4"></div>
						</div>
						<button type="submit" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700 mt-4">Salvar</button>
					</div>
				</form>
			</div>
		</main>
	</div>
	
	<script src="../js/funcoes_imagens.js"></script>
</body>
</html>


