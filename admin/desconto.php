<?php
require_once("header.php");
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID inválido');
$campanha = listaCampanhas($conn, $id);
if (!$campanha || !is_array($campanha)) die('Campanha não encontrada');
$campanha = $campanha[0];
// Postback: salvar descontos/pacotes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$habilitar_adicao_rapida = isset($_POST['habilitar_adicao_rapida']) ? 1 : 0;
	$habilitar_pacote_promocional = isset($_POST['habilitar_pacote_promocional']) ? 1 : 0;
	$pacote_promocional = $_POST['pacote_promocional'] ?? '[]';
	$pacotes_exclusivos = $_POST['pacotes_exclusivos'] ?? '[]';
	$resultado = editaCampanha(
		$conn, $id,
		null,null,null,
		null,null,
		null,
		null,
		null,
		null,null,
		null,null,
		$habilitar_adicao_rapida,
		null,
		$habilitar_pacote_promocional,
		$pacote_promocional,
		null,
		1,
		$pacotes_exclusivos,
		null,null,null,null,
		null,null,null,
		null,null,null,null,null,null,null,null,null,null,null,null,
		null,null
	);
	if ($resultado === true) { $mensagem_sucesso = 'Descontos salvos com sucesso!'; }
	else { $mensagem_erro = is_string($resultado) ? $resultado : 'Erro ao salvar.'; }
	$campanha = listaCampanhas($conn, $id); $campanha = $campanha[0];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><title>Editar Campanha - Desconto</title></head>
<body class="bg-gray-100 text-gray-900 dark:bg-[#18181B] dark:text-white">
	<div class="flex h-screen">
		<?php require("sidebar.php"); ?>
		<main class="flex-1 p-6 overflow-y-auto max-h-screen">
			<header class="flex justify-between items-center mb-6">
				<h1 class="text-2xl font-bold">Editar Campanha - Desconto</h1>
				<a href="../campanhas.php" class="bg-gray-200 dark:bg-[#3F3F46] px-3 py-2 rounded">Voltar</a>
			</header>
			<div class="bg-white dark:bg-[#27272A] p-6 rounded-lg shadow-xl">
				<div class="flex justify-between items-center mb-4">
					<h2 id="modalCampoTitulo" class="text-xl font-bold">Editar Campo</h2>
				</div>
				<?php if (!empty($mensagem_sucesso)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_sucesso)."','success');</script>"; endif; ?>
				<?php if (!empty($mensagem_erro)): require_once('../assets/template_alerta.php'); echo "<script>showCustomAlert('".addslashes($mensagem_erro)."','error');</script>"; endif; ?>
				<form method="POST" class="space-y-4">
					<h3 class="text-lg font-semibold mb-4">Descontos e Pacotes</h3>
					<div class="space-y-4">
						<div>
							<label for="habilitar_adicao_rapida" class="block mb-2 font-medium">Habilitar Adição Rápida</label>
							<label class="toggle-switch">
								<input type="checkbox" id="habilitar_adicao_rapida" name="habilitar_adicao_rapida" value="1" <?= (int)$campanha['habilitar_adicao_rapida']==1?'checked':'' ?>>
								<div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
							</label>
						</div>
						<div>
							<label for="habilitar_pacote_promocional" class="block mb-2 font-medium">Habilitar Pacote Promocional</label>
							<label class="toggle-switch">
								<input type="checkbox" id="habilitar_pacote_promocional" name="habilitar_pacote_promocional" class="habilitar_pacote_promocional" value="1" <?= (int)$campanha['habilitar_pacote_promocional']==1?'checked':'' ?>>
								<div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
							</label>
						</div>
						<div class="mb-4">
							<h3 class="text-lg font-semibold mb-2">Pacote Exclusivo</h3>
							<label class="toggle-switch">
								<input type="checkbox" id="habilita_pacote_promocional_exclusivo" name="habilita_pacote_promocional_exclusivo" value="1" <?= (int)($campanha['habilita_pacote_promocional_exclusivo'] ?? 0)==1?'checked':'' ?> >
								<div class="toggle-switch-background"><div class="toggle-switch-handle"></div></div>
							</label>
						</div>
						<div class="mb-4 pacote_promocional">
							<h3 class="text-lg font-semibold mb-2">Pacote Promocional</h3>
							<div id="descontos-container" class="space-y-3"></div>
							<button type="button" onclick="adicionarDescontoPromocional('normal')" class="mt-4 w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Adicionar Novo Pacote Normal</button>
							<input type="hidden" name="pacote_promocional" id="pacote_promocional_input">
						</div>
						<div class="mb-4" id="pacote_exclusivo">
							<h3 class="text-lg font-semibold mb-2">Pacotes Exclusivos</h3>
							<div id="descontos-exclusivos-container" class="space-y-3"></div>
							<button type="button" onclick="adicionarDescontoExclusivo('exclusivo')" class="mt-4 w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Adicionar Novo Pacote Exclusivo</button>
							<input type="hidden" name="pacotes_exclusivos" id="pacotes_exclusivos_input">
						</div>
						<button type="submit" class="w-full bg-purple-600 text-white p-2 rounded hover:bg-purple-700">Salvar</button>
					</div>
				</form>
			</div>
		</main>
	</div>
	<script src="js/campanhas_editar.js"></script>
	<script>
		// Preenche pacotes existentes e serializa antes de enviar
		(function(){
			try{
				const containerNormal = document.getElementById('descontos-container');
				const containerExclusivo = document.getElementById('descontos-exclusivos-container');
				let n = [] , e = [];
				<?php $pp = $campanha['pacote_promocional'] ?? '[]'; $pe = $campanha['pacotes_exclusivos'] ?? '[]'; ?>
				n = JSON.parse('<?= json_encode(json_decode($pp, true)) ?>');
				e = JSON.parse('<?= json_encode(json_decode($pe, true)) ?>');
				if (Array.isArray(n) && n.length) n.forEach(p=>adicionarPacoteExistentePromocional(p,'normal')); else adicionarDescontoPromocional('normal');
				if (Array.isArray(e) && e.length) e.forEach(p=>adicionarPacoteExistenteExclusivo(p,'exclusivo')); else adicionarDescontoExclusivo('exclusivo');
			}catch(err){ console.error(err); }
			validaDescontoPromocial(5); validaDescontoExclusivo(5);
			document.querySelector('form').addEventListener('submit', function(){
				const collect = (containerSel, tipo) => Array.from(document.querySelectorAll(containerSel + ' > div')).map(container => {
					const valorBilhete = parseFloat((container.querySelector(`input[name="valor_bilhete_${tipo}[]"]`)||{}).value||0) || 0;
					const quantidade = parseInt((container.querySelector(`input[name="quantidade_desconto_${tipo}[]"]`)||{}).value||0) || 0;
					const valorPacote = parseFloat((container.querySelector(`input[name="valor_desconto_${tipo}[]"]`)||{}).value||0) || 0;
					const beneficioTipo = (container.querySelector(`select[name="beneficio_tipo_${tipo}[]"]`)||{}).value||'';
					const beneficioQtd = parseInt((container.querySelector(`input[name="beneficio_quantidade_${tipo}[]"]`)||{}).value||0) || 0;
					const codigo = (container.querySelector(`input[name="codigo_desconto_${tipo}[]"]`)||{}).value||'';
					const obj = { valor_bilhete: valorBilhete, quantidade_numeros: quantidade, valor_pacote: valorPacote };
					if (tipo==='exclusivo') { obj.codigo_pacote = codigo; obj.beneficio_tipo = beneficioTipo; obj.beneficio_quantidade = beneficioQtd; }
					else { obj.beneficio_tipo = beneficioTipo; obj.beneficio_quantidade = beneficioQtd; }
					return obj;
				});
				document.getElementById('pacote_promocional_input').value = JSON.stringify(collect('#descontos-container','normal'));
				document.getElementById('pacotes_exclusivos_input').value = JSON.stringify(collect('#descontos-exclusivos-container','exclusivo'));
			});
		})();
	</script>
</body>
</html>


