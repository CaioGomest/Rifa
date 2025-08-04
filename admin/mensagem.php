<?php
$mensagem = null;

if (isset($_REQUEST['mensagem']))
{
    $mensagem = array(
        'mensagem' => $_REQUEST['mensagem'],
        'tipo' => isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : 'sucesso'
    );
}
if (isset($mensagem)): ?>
    <div id="mensagem-alerta"
        class="mb-4 p-4 rounded-md text-center <?php echo $mensagem['tipo'] === 'sucesso' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?> transition-all duration-500 opacity-100">
        <?php echo $mensagem['mensagem']; ?>
    </div>
<?php endif; ?>
<script>
    function esconderMensagem()
    {
        const mensagem = document.getElementById('mensagem-alerta');
        if (mensagem)
        {
            mensagem.style.opacity = '0';
            setTimeout(() => {
                mensagem.parentNode.removeChild(mensagem);
            }, 500);
        }
    }

    document.addEventListener('DOMContentLoaded', function ()
    {
        const mensagem = document.getElementById('mensagem-alerta');
        if (mensagem)
        {
            setTimeout(esconderMensagem, 5000);
        }
    });
</script>