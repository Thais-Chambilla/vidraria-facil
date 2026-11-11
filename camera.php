<?php
/**
 * Vidraria Fácil - Caso de Uso 1: Escanear Vidraria (Câmera & IA)
 * Conforme slides 4 e 8 e requisitos.md
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$usuarioAtual = usuario_logado();
$limites = $usuarioAtual ? obter_limites_plano($bd, $usuarioAtual) : null;

$tituloPagina = "Escanear com Câmera";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 max-w-4xl mx-auto px-4 sm:px-6 py-6 w-full">

    <!-- Cabeçalho da Tela -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-camera text-sky-600"></i> Escanear Vidraria
            </h1>
            <p class="text-xs text-slate-500">Aponte a câmera ou carregue uma imagem para identificação com IA</p>
        </div>

        <?php if ($limites): ?>
            <div class="text-right">
                <?php if ($limites['is_premium']): ?>
                    <span class="px-2.5 py-1 text-[11px] font-bold bg-amber-100 text-amber-800 rounded-lg inline-flex items-center gap-1">
                        <i class="fa-solid fa-crown text-amber-500"></i> Buscas Ilimitadas
                    </span>
                <?php else: ?>
                    <span class="px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-700 rounded-lg inline-block border border-slate-200">
                        Cota: <strong class="text-sky-600"><?= $limites['pesquisas_restantes'] ?></strong> / <?= $limites['limite_pesquisas'] ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Alerta caso não esteja logado -->
    <?php if (!$usuarioAtual): ?>
        <div class="mb-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg"></i>
                <span>Você pode testar a identificação, mas crie uma conta para salvar suas detecções no histórico.</span>
            </div>
            <a href="<?= BASE_URL ?>/auth/login.php" class="px-3 py-1.5 bg-amber-600 text-white font-bold rounded-xl shrink-0 hover:bg-amber-700 transition">
                Entrar
            </a>
        </div>
    <?php endif; ?>

    <!-- Container Principal do Viewfinder da Câmera -->
    <div class="relative bg-slate-900 rounded-3xl overflow-hidden shadow-2xl aspect-[4/3] sm:aspect-[16/10] flex items-center justify-center border-4 border-slate-800">
        
        <!-- Elemento de Vídeo da Câmera -->
        <video id="video-stream" autoplay playsinline muted class="w-full h-full object-cover"></video>
        
        <!-- Canvas Oculto para Captura de Frame -->
        <canvas id="camera-canvas" class="hidden"></canvas>

        <!-- Preview da Imagem Capturada ou Selecionada -->
        <img id="image-preview" class="w-full h-full object-contain hidden bg-slate-950" alt="Preview da Vidraria">

        <!-- Mira / Retícula de Centralização Visual de Laboratório -->
        <div id="camera-reticle" class="absolute inset-0 pointer-events-none flex items-center justify-center p-8">
            <div class="relative w-64 h-64 sm:w-80 sm:h-80 border-2 border-dashed border-sky-400/70 rounded-3xl flex items-center justify-center shadow-[0_0_50px_rgba(14,165,233,0.15)]">
                <!-- Cantoneiras Tecnológicas -->
                <div class="absolute -top-2 -left-2 w-6 h-6 border-t-4 border-l-4 border-cyan-400 rounded-tl-xl"></div>
                <div class="absolute -top-2 -right-2 w-6 h-6 border-t-4 border-r-4 border-cyan-400 rounded-tr-xl"></div>
                <div class="absolute -bottom-2 -left-2 w-6 h-6 border-b-4 border-l-4 border-cyan-400 rounded-bl-xl"></div>
                <div class="absolute -bottom-2 -right-2 w-6 h-6 border-b-4 border-r-4 border-cyan-400 rounded-br-xl"></div>

                <div class="text-center px-4">
                    <span class="inline-block px-3 py-1 bg-slate-900/80 backdrop-blur-md text-[11px] font-semibold text-cyan-300 rounded-full border border-cyan-500/30">
                        <i class="fa-solid fa-arrows-to-eye mr-1"></i> Posicione a vidraria aqui
                    </span>
                </div>
            </div>
        </div>

        <!-- Animação de Varredura / Radar durante Análise IA -->
        <div id="scanner-radar" class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm hidden flex-col items-center justify-center p-6 text-center z-20">
            <div class="relative w-24 h-24 mb-4">
                <div class="absolute inset-0 rounded-full border-4 border-sky-500/20 animate-ping"></div>
                <div class="w-full h-full rounded-full border-4 border-t-sky-500 border-r-transparent border-b-cyan-400 border-l-transparent animate-spin flex items-center justify-center">
                    <i class="fa-solid fa-microchip text-sky-400 text-3xl"></i>
                </div>
            </div>
            <h3 class="text-lg font-bold text-white tracking-wide">Analisando contornos com IA...</h3>
            <p class="text-xs text-sky-200 mt-1 max-w-xs">Identificando anatomia, curvatura do gargalo e formato do fundo via Hugging Face Vision</p>
        </div>

        <!-- Controles Sobrepostos no Topo da Câmera -->
        <div class="absolute top-4 left-4 right-4 flex items-center justify-between z-10 pointer-events-auto">
            <span id="camera-status-badge" class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-900/70 backdrop-blur-md text-slate-200 border border-slate-700/60 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Câmera Ativa
            </span>

            <div class="flex items-center gap-2">
                <button id="btn-switch-camera" type="button" title="Alternar Câmera Frontal/Traseira" class="w-10 h-10 rounded-full bg-slate-900/70 backdrop-blur-md text-white hover:bg-slate-800 border border-slate-700 flex items-center justify-center transition active:scale-95">
                    <i class="fa-solid fa-camera-rotate"></i>
                </button>
            </div>
        </div>

    </div>

    <!-- Barra Inferior de Comandos de Captura e Upload -->
    <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-4">
        
        <!-- Input invisível de Upload de Arquivo / Galeria -->
        <input type="file" id="file-input" accept="image/*" class="hidden">

        <!-- Botão Galeria / Arquivo -->
        <button id="btn-choose-file" type="button" class="w-full sm:w-auto px-5 py-3.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-2xl shadow-sm flex items-center justify-center gap-2 transition active:scale-95">
            <i class="fa-solid fa-images text-sky-600 text-base"></i>
            <span>Escolher da Galeria</span>
        </button>

        <!-- Botão Principal de Disparo / Foto -->
        <button id="btn-capture" type="button" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-sky-600 to-cyan-500 hover:from-sky-700 hover:to-cyan-600 text-white font-black text-sm rounded-2xl shadow-xl shadow-sky-500/30 flex items-center justify-center gap-3 transition transform active:scale-95">
            <div class="w-4 h-4 rounded-full bg-white flex items-center justify-center">
                <span class="w-2 h-2 rounded-full bg-sky-600"></span>
            </div>
            <span>Tirar Foto e Identificar</span>
        </button>

        <!-- Botão de Reiniciar Câmera (quando preview exibido) -->
        <button id="btn-retake" type="button" class="w-full sm:w-auto px-5 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl hidden flex items-center justify-center gap-2 transition">
            <i class="fa-solid fa-rotate-left"></i>
            <span>Tirar Outra Foto</span>
        </button>

    </div>

    <!-- Painel de Resultado da Identificação (Conforme Slide 9 da Apresentação) -->
    <div id="result-card" class="mt-8 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xl hidden transition-all">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-[11px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 rounded-lg flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Vidraria Reconhecida
                </span>
                <span id="res-confianca" class="px-2.5 py-1 text-[11px] font-bold bg-sky-100 text-sky-800 rounded-lg">
                    Confiança: 98.4%
                </span>
            </div>
            <span id="res-motor" class="text-[10px] text-slate-400 font-medium">Hugging Face Vision AI</span>
        </div>

        <div class="flex flex-col md:flex-row gap-6 mt-6 items-start">
            <!-- Imagem da Vidraria Identificada -->
            <div class="w-full md:w-48 shrink-0 text-center">
                <img id="res-foto" src="" alt="Vidraria" class="w-36 h-36 object-contain rounded-2xl bg-slate-50 p-2 mx-auto border border-slate-100 shadow-inner">
                <span id="res-categoria" class="inline-block mt-2 px-2.5 py-0.5 bg-slate-100 text-slate-600 font-semibold text-[10px] rounded-md">
                    Aquecimento
                </span>
            </div>

            <!-- Ficha Rápida -->
            <div class="flex-1 space-y-3">
                <h3 id="res-nome" class="text-2xl font-black text-slate-900 leading-tight">
                    Balão de Fundo Redondo
                </h3>
                <p id="res-nome-en" class="text-xs text-slate-400 font-mono -mt-2">round-bottom flask &bull; Borossilicato 3.3</p>

                <!-- Bloco Para que serve -->
                <div class="p-4 bg-sky-50/70 rounded-2xl border border-sky-100">
                    <h4 class="text-xs font-bold text-sky-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-sky-600"></i> Para que serve:
                    </h4>
                    <p id="res-funcao" class="text-xs text-slate-700 mt-1.5 leading-relaxed">
                        Usado para aquecimento uniforme de líquidos, misturas e reações químicas. Elemento essencial para processos de destilação e refluxo.
                    </p>
                </div>

                <!-- Bloco Dica de Segurança -->
                <div class="p-4 bg-amber-50/80 rounded-2xl border border-amber-200">
                    <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-shield-halved text-amber-600"></i> Dica de Segurança & Prevenção:
                    </h4>
                    <p id="res-seguranca" class="text-xs text-amber-950 mt-1.5 leading-relaxed">
                        Verificar trincas antes de aquecer e utilizar obrigatoriamente suportes, garras de fixação e mantas aquecedoras reguladas.
                    </p>
                </div>

                <div class="pt-2 flex flex-wrap gap-3">
                    <a id="res-link-detalhes" href="#" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                        <span>Ver Ficha Técnica Completa</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                    <button type="button" onclick="reiniciarCamera()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                        Nova Leitura
                    </button>
                </div>
            </div>
        </div>
    </div>

</main>

<script>
// Variáveis de Controle da Câmera
let streamAtual = null;
let modoCamera = 'environment'; // 'environment' (traseira) ou 'user' (frontal)
let dadosImagemCapturada = null;

const video = document.getElementById('video-stream');
const canvas = document.getElementById('camera-canvas');
const imagePreview = document.getElementById('image-preview');
const reticle = document.getElementById('camera-reticle');
const radar = document.getElementById('scanner-radar');
const statusBadge = document.getElementById('camera-status-badge');
const btnCapture = document.getElementById('btn-capture');
const btnRetake = document.getElementById('btn-retake');
const btnSwitch = document.getElementById('btn-switch-camera');
const btnChooseFile = document.getElementById('btn-choose-file');
const fileInput = document.getElementById('file-input');
const resultCard = document.getElementById('result-card');

/**
 * Inicializa a câmera com navigator.mediaDevices.getUserMedia
 */
async function iniciarCamera() {
    try {
        if (streamAtual) {
            streamAtual.getTracks().forEach(track => track.stop());
        }

        const constraints = {
            video: {
                facingMode: { ideal: modoCamera },
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };

        streamAtual = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = streamAtual;
        video.classList.remove('hidden');
        imagePreview.classList.add('hidden');
        reticle.classList.remove('hidden');
        btnCapture.classList.remove('hidden');
        btnRetake.classList.add('hidden');
        statusBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Câmera Ativa';

    } catch (err) {
        console.warn("Câmera não disponível ou permissão negada:", err);
        statusBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-400"></span> Modo Arquivo / Galeria';
        mostrarToast('aviso', 'Câmera Indisponível', 'Habilite o acesso à câmera no navegador ou utilize a opção "Escolher da Galeria".');
    }
}

/**
 * Alterna entre câmera frontal e traseira
 */
btnSwitch.addEventListener('click', () => {
    modoCamera = (modoCamera === 'environment') ? 'user' : 'environment';
    iniciarCamera();
});

/**
 * Disparo da Foto e Captura via Canvas
 */
btnCapture.addEventListener('click', () => {
    if (!video.videoWidth) {
        // Se a câmera não estiver ativa, abre o seletor de arquivos
        fileInput.click();
        return;
    }

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    dadosImagemCapturada = canvas.toDataURL('image/jpeg', 0.90);
    
    // Mostra preview estático
    imagePreview.src = dadosImagemCapturada;
    imagePreview.classList.remove('hidden');
    video.classList.add('hidden');
    reticle.classList.add('hidden');
    btnCapture.classList.add('hidden');
    btnRetake.classList.remove('hidden');

    // Envia para o motor de IA
    enviarParaIdentificacao(dadosImagemCapturada);
});

/**
 * Seleção de imagem via galeria / arquivo
 */
btnChooseFile.addEventListener('click', () => fileInput.click());

fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(evt) {
        dadosImagemCapturada = evt.target.result;
        imagePreview.src = dadosImagemCapturada;
        imagePreview.classList.remove('hidden');
        video.classList.add('hidden');
        reticle.classList.add('hidden');
        btnCapture.classList.add('hidden');
        btnRetake.classList.remove('hidden');

        enviarParaIdentificacao(dadosImagemCapturada, file.name);
    };
    reader.readAsDataURL(file);
});

/**
 * Reinicia o fluxo para nova captura
 */
window.reiniciarCamera = function() {
    resultCard.classList.add('hidden');
    dadosImagemCapturada = null;
    iniciarCamera();
};
btnRetake.addEventListener('click', reiniciarCamera);

/**
 * Envia imagem para a API AJAX de Identificação
 */
async function enviarParaIdentificacao(base64Data, nomeArquivo = null) {
    radar.classList.remove('hidden');
    resultCard.classList.add('hidden');

    try {
        const formData = new FormData();
        formData.append('imagem_base64', base64Data);
        if (nomeArquivo) formData.append('nome_arquivo', nomeArquivo);

        const resp = await fetch('<?= BASE_URL ?>/api/identificar.php', {
            method: 'POST',
            body: formData
        });

        const data = await resp.json();

        radar.classList.add('hidden');

        if (!data.sucesso) {
            // Se o limite do plano gratuito foi atingido
            if (data.limite_atingido) {
                abrirModal({
                    titulo: 'Limite Diário Atingido',
                    mensagem: `${data.mensagem}\n\nFaça upgrade para o Plano Premium para ter pesquisas ilimitadas e histórico completo!`,
                    tipo: 'aviso',
                    mostrarCancelar: true,
                    textoCancelar: 'Fechar',
                    textoConfirmar: 'Ver Planos',
                    onConfirm: () => {
                        window.location.href = '<?= BASE_URL ?>/planos.php';
                    }
                });
                return;
            }

            if (data.requer_login) {
                abrirModal({
                    titulo: 'Login Obrigatório',
                    mensagem: data.mensagem,
                    tipo: 'info',
                    mostrarCancelar: true,
                    textoCancelar: 'Continuar como visitante',
                    textoConfirmar: 'Fazer Login',
                    onConfirm: () => {
                        window.location.href = '<?= BASE_URL ?>/auth/login.php?retorno=' + encodeURIComponent(window.location.href);
                    }
                });
                return;
            }

            mostrarToast('erro', 'Falha na Detecção', data.mensagem || 'Não foi possível reconhecer a vidraria.');
            return;
        }

        // Preenche o Card de Resultado
        const v = data.vidraria;
        document.getElementById('res-nome').textContent = v.nome;
        document.getElementById('res-nome-en').textContent = (v.nome_en || '') + ' • ' + (v.material_comum || 'Borossilicato 3.3');
        document.getElementById('res-categoria').textContent = v.categoria;
        document.getElementById('res-confianca').textContent = `Confiança: ${data.confianca}%`;
        document.getElementById('res-motor').textContent = data.motor || 'IA Vision';
        document.getElementById('res-funcao').textContent = v.funcao_principal;
        document.getElementById('res-seguranca').textContent = v.dicas_seguranca;
        document.getElementById('res-link-detalhes').href = `<?= BASE_URL ?>/detalhes.php?id=${v.id}`;
        
        const imgFoto = document.getElementById('res-foto');
        imgFoto.src = v.foto_url ? `<?= BASE_URL ?>/${v.foto_url}` : '<?= BASE_URL ?>/assets/img/image41.jpeg';

        resultCard.classList.remove('hidden');
        mostrarToast('sucesso', 'Reconhecimento Concluído!', `${v.nome} identificada com ${data.confianca}% de precisão.`);

        // Rola suavemente até o resultado
        resultCard.scrollIntoView({ behavior: 'smooth' });

    } catch (e) {
        radar.classList.add('hidden');
        mostrarToast('erro', 'Erro de Comunicação', 'Houve uma falha ao enviar a foto para processamento.');
        console.error(e);
    }
}

// Inicializa a câmera automaticamente ao carregar a página
document.addEventListener('DOMContentLoaded', iniciarCamera);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
