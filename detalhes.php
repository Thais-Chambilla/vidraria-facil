<?php
/**
 * Vidraria Fácil - Caso de Uso 3: Exibir Detalhes Técnicos e de Segurança
 * Conforme slides 6 e 9 da apresentação
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $bd->prepare("SELECT * FROM vidrarias WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$vidraria = $stmt->fetch();

if (!$vidraria) {
    definir_toast('erro', 'Vidraria Não Encontrada', 'O instrumento selecionado não existe no catálogo.');
    header("Location: " . BASE_URL . "/catalogo.php");
    exit;
}

$tituloPagina = $vidraria['nome'];
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <!-- Navegação Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6 font-medium">
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-sky-600 transition">Início</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <a href="<?= BASE_URL ?>/catalogo.php" class="hover:text-sky-600 transition">Catálogo</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-slate-700 font-bold"><?= h($vidraria['nome']) ?></span>
    </nav>

    <!-- Card Principal de Detalhes -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        
        <!-- Topo com Foto e Identificação Primária -->
        <div class="p-6 sm:p-10 bg-gradient-to-b from-slate-50 to-white border-b border-slate-100 flex flex-col md:flex-row items-center gap-8">
            
            <!-- Imagem em Destaque -->
            <div class="w-full md:w-72 shrink-0">
                <div class="relative bg-white rounded-3xl p-6 border-2 border-slate-100 shadow-md flex items-center justify-center">
                    <?php if (!empty($vidraria['foto_url'])): ?>
                        <img src="<?= BASE_URL ?>/<?= $vidraria['foto_url'] ?>" alt="<?= h($vidraria['nome']) ?>"
                             class="w-56 h-56 object-contain"
                             onerror="this.src='https://cdn-icons-png.flaticon.com/512/862/862856.png'">
                    <?php else: ?>
                        <i class="fa-solid fa-flask-vial text-7xl text-sky-500"></i>
                    <?php endif; ?>

                    <span class="absolute top-3 right-3 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-sky-100 text-sky-800 rounded-lg">
                        <?= h($vidraria['categoria']) ?>
                    </span>
                </div>
            </div>

            <!-- Dados Principais e Badges Anatômicos -->
            <div class="flex-1 text-center md:text-left space-y-3">
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                    <?= h($vidraria['nome']) ?>
                </h1>
                
                <p class="text-xs sm:text-sm text-slate-500 font-mono">
                    <?= h($vidraria['nome_en']) ?> &bull; <?= h($vidraria['material_comum']) ?>
                </p>

                <!-- Grid de Atributos Físicos -->
                <div class="pt-2 flex flex-wrap justify-center md:justify-start gap-2">
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl flex items-center gap-1.5">
                        <i class="fa-solid fa-shapes text-sky-500"></i> Fundo: <?= h($vidraria['formato_fundo']) ?>
                    </span>
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl flex items-center gap-1.5">
                        <i class="fa-solid fa-wine-bottle text-emerald-500"></i> Gargalo: <?= h($vidraria['tipo_gargalo']) ?>
                    </span>
                    <?php if ($vidraria['graduada']): ?>
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold rounded-xl flex items-center gap-1.5">
                            <i class="fa-solid fa-ruler-vertical text-emerald-600"></i> Peça Graduada
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-slate-100 text-slate-500 text-xs font-semibold rounded-xl">
                            Não Graduada
                        </span>
                    <?php endif; ?>

                    <?php if ($vidraria['resiste_aquecimento']): ?>
                        <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold rounded-xl flex items-center gap-1.5">
                            <i class="fa-solid fa-fire text-amber-600"></i> Suporta Aquecimento
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 bg-rose-50 text-rose-800 border border-rose-200 text-xs font-semibold rounded-xl flex items-center gap-1.5">
                            <i class="fa-solid fa-ban text-rose-500"></i> Proibido Aquecer
                        </span>
                    <?php endif; ?>
                </div>

                <div class="pt-4 flex flex-wrap gap-3 justify-center md:justify-start">
                    <a href="<?= BASE_URL ?>/camera.php" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                        <i class="fa-solid fa-camera"></i>
                        <span>Escanear com a Câmera</span>
                    </a>
                    <a href="<?= BASE_URL ?>/catalogo.php" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                        Ver Outras Vidrarias
                    </a>
                </div>
            </div>

        </div>

        <!-- Seções Informativas e Normas de Segurança -->
        <div class="p-6 sm:p-10 space-y-6">

            <!-- 1. Para que Serve (Função Principal) -->
            <div class="p-5 bg-sky-50/70 rounded-3xl border border-sky-100">
                <h3 class="text-sm font-extrabold text-sky-950 uppercase tracking-wider flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-circle-question text-sky-600 text-base"></i>
                    Para que serve (Função Principal):
                </h3>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-normal">
                    <?= nl2br(h($vidraria['funcao_principal'])) ?>
                </p>
            </div>

            <!-- 2. Aplicações Práticas no Laboratório -->
            <div class="p-5 bg-slate-50 rounded-3xl border border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-flask text-emerald-600 text-base"></i>
                    Aplicações Práticas no Laboratório:
                </h3>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-normal">
                    <?= nl2br(h($vidraria['aplicacoes'])) ?>
                </p>
            </div>

            <!-- 3. Normas de Segurança Primária e Prevenção de Acidentes (Slide 9 e 2) -->
            <div class="p-5 sm:p-6 bg-amber-50/90 rounded-3xl border-2 border-amber-300 shadow-sm">
                <h3 class="text-sm font-black text-amber-950 uppercase tracking-wider flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg"></i>
                    Normas de Segurança Primária & Prevenção de Riscos:
                </h3>
                <p class="text-xs sm:text-sm text-amber-950 leading-relaxed font-medium">
                    <?= nl2br(h($vidraria['dicas_seguranca'])) ?>
                </p>
            </div>

            <!-- 4. Cuidados no Manuseio e Limpeza -->
            <div class="p-5 bg-slate-50 rounded-3xl border border-slate-100">
                <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-hand-holding-hand text-indigo-600 text-base"></i>
                    Cuidados de Manuseio e Higienização:
                </h3>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed font-normal">
                    <?= nl2br(h($vidraria['cuidados_manuseio'])) ?>
                </p>
            </div>

        </div>

    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
