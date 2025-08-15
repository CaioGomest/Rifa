// Funções para preview e remoção de imagens
function previewImagem(input, previewId) {
    const preview = document.getElementById(previewId);
    const container = input.closest('.flex-col');
    
    // Remove qualquer imagem existente no preview
    const imagemExistente = preview.querySelector('img');
    if (imagemExistente) {
        imagemExistente.remove();
    }

    // Remove quaisquer imagens já exibidas (salvas ou previews anteriores)
    const imagensAtuais = container.querySelectorAll('.imagemAtual');
    if (imagensAtuais && imagensAtuais.length) {
        imagensAtuais.forEach(el => el.remove());
    }

    // Verifica se existe um arquivo e atualiza o preview
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            // Remove o preview anterior, se existir
            preview.innerHTML = '';  // Limpa o conteúdo do preview
            preview.classList.remove('hidden');  // Torna o preview visível

            // Cria um novo preview da imagem
            const div = document.createElement('div');
            div.className = 'relative inline-block w-fit group imagemAtual';  // wrapper do preview
            const removerFn = (previewId === 'preview-capa') ? 'removerImagemCapa' : 'removerImagemPrincipal';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="max-w-[200px] rounded-lg shadow-md">
                <button type="button" onclick="${removerFn}(this)" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            preview.appendChild(div);

            // Se já existir uma imagem renderizada anteriormente como "imagemAtual" nessa seção, remova-a
            const atual = container.querySelector('.imagemAtual');
            if (atual && atual !== div) {
                atual.remove();
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}


function previewGaleria(input) {
    const preview = document.getElementById('preview-galeria');
    preview.innerHTML = '';

    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="w-full h-40 object-cover rounded-lg shadow-md">
                    <button type="button" onclick="this.parentElement.remove()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                `;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }
}

function removerImagemPrincipal(button) {
    if (confirm('Tem certeza que deseja remover esta imagem?')) {
        const container = button.closest('.relative');
        if (container) container.remove();
        const input = document.getElementById('imagem_principal');
        if (input) input.value = '';
        const flag = document.getElementById('remover_imagem_principal');
        if (flag) flag.value = '1';
    }
}

function removerImagemCapa(button) {
    if (confirm('Tem certeza que deseja remover esta imagem?')) {
        const container = button.closest('.relative');
        if (container) container.remove();
        const input = document.getElementById('imagem_capa');
        if (input) input.value = '';
        const flag = document.getElementById('remover_imagem_capa');
        if (flag) flag.value = '1';
    }
}

function removerImagemGaleria(button, indexOrPath) {
   
        const container = button.closest('.relative');
        container.remove();

        // Atualiza o campo hidden com as imagens restantes
        const galeriaAtual = document.getElementById('galeria_imagens_atual');
        if (galeriaAtual) {
            const imagens = galeriaAtual.value ? galeriaAtual.value.split(',') : [];
            if (typeof indexOrPath === 'number') {
                imagens.splice(indexOrPath, 1);
            } else if (typeof indexOrPath === 'string' && indexOrPath.length > 0) {
                const idx = imagens.indexOf(indexOrPath);
                if (idx > -1) imagens.splice(idx, 1);
            }
            galeriaAtual.value = imagens.filter(Boolean).join(',');
        }

        // Limpa o input de upload para evitar conflito com imagens removidas
        const inputGal = document.getElementById('galeria');
        if (inputGal) inputGal.value = '';
} 