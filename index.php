<?php
/**
 * Vidraria Fácil - Página Inicial (Dashboard Mobile-First)
 * Conforme especificações do projeto PTIC e requisitos.md
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$usuarioAtual = usuario_logado();
$limites = $usuarioAtual ? obter_limites_plano($bd, $usuarioAtual) : null;

// Busca algumas vidrarias em destaque para o catálogo rápido
$stmtDestaques = $bd->query("SELECT * FROM vidrarias ORDER BY id ASC LIMIT 6");
$destaques = $stmtDestaques->fetchAll();

$tituloPagina = "Início";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">

    <!-- Banner de Status do Plano / Boas-vindas -->
    <?php if ($usuarioAtual): ?>
        <div class="mb-6 p-4 sm:p-5 rounded-3xl <?= $limites['is_premium'] ? 'bg-gradient-to-r from-amber-500/10 via-yellow-500/10 to-amber-500/10 border border-amber-200' : 'bg-gradient-to-r from-sky-500/10 via-cyan-500/10 to-blue-500/10 border border-sky-200' ?> flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl <?= $limites['is_premium'] ? 'bg-amber-500 text-white' : 'bg-sky-600 text-white' ?> flex items-center justify-center text-xl shrink-0 shadow-md">
                    <i class="fa-solid <?= $limites['is_premium'] ? 'fa-crown' : 'fa-user-graduate' ?>"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Olá, <?= h($usuarioAtual['nome']) ?>!
                    </h2>
                    <p class="text-xs text-slate-600 mt-0.5">
                        <?php if ($limites['is_premium']): ?>
                            <span class="text-amber-700 font-semibold">Conta Premium:</span> Acesso total, pesquisas ilimitadas e histórico vitalício.
                        <?php else: ?>
                            <span class="text-sky-700 font-semibold">Plano Gratuito:</span> Você tem <strong><?= $limites['pesquisas_restantes'] ?> de <?= $limites['limite_pesquisas'] ?></strong> pesquisas disponíveis hoje.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if (!$limites['is_premium']): ?>
                <a href="<?= BASE_URL ?>/planos.php" class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white font-bold text-xs rounded-xl shadow-md shadow-amber-500/20 text-center transition">
                    <i class="fa-solid fa-crown mr-1.5"></i> Fazer Upgrade Premium
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Seção Hero: Busca Central e Apresentação -->
    <div class="text-center py-6 sm:py-10 max-w-2xl mx-auto">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 mb-4">
            <i class="fa-solid fa-wand-magic-sparkles text-sky-600"></i> Inteligência Artificial & Visão Computacional
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
            Identifique vidrarias de laboratório <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-cyan-500">em segundos</span>
        </h1>
        <p class="text-sm text-slate-600 mt-3 leading-relaxed">
            Aponte a câmera do seu celular ou filtre por características físicas para descobrir utilidades práticas e normas de segurança química.
        </p>

        <!-- Barra de Busca Rápida -->
        <form action="<?= BASE_URL ?>/catalogo.php" method="GET" class="mt-6 relative max-w-xl mx-auto">
            <div class="relative flex items-center">
                <span class="absolute left-4 text-slate-400 text-base">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="q" placeholder="Buscar por nome, função ou características (ex: béquer, aquecimento)..."
                       class="w-full pl-12 pr-28 py-4 bg-white border border-slate-200 shadow-xl shadow-slate-200/50 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition">
                <button type="submit" class="absolute right-2 px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                    Buscar
                </button>
            </div>
        </form>
    </div>

    <!-- Módulos de Atalhos Rápidos (Conforme Slide 7 da Apresentação) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 my-8">
        
        <!-- Card 1: Escanear Câmera -->
        <a href="<?= BASE_URL ?>/camera.php" class="group p-6 bg-white rounded-3xl border border-slate-100 shadow-md hover:shadow-xl hover:border-sky-200 transition-all flex flex-col justify-between">
            <div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-600 to-cyan-400 text-white flex items-center justify-center text-2xl shadow-lg shadow-sky-500/30 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <h3 class="font-extrabold text-base text-slate-900 mt-5 group-hover:text-sky-600 transition-colors">
                    Escanear Vidraria
                </h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Identificação visual instantânea por foto utilizando a câmera do dispositivo e IA.
                </p>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs font-bold text-sky-600">
                <span>Abrir Câmera</span>
                <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </a>

        <!-- Card 2: Filtrar Atributos -->
        <a href="<?= BASE_URL ?>/filtro.php" class="group p-6 bg-white rounded-3xl border border-slate-100 shadow-md hover:shadow-xl hover:border-emerald-200 transition-all flex flex-col justify-between">
            <div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center text-2xl shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-filter"></i>
                </div>
                <h3 class="font-extrabold text-base text-slate-900 mt-5 group-hover:text-emerald-600 transition-colors">
                    Filtrar por Atributos
                </h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Busca guiada por formato de fundo, gargalo, resistência térmica e graduação.
                </p>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs font-bold text-emerald-600">
                <span>Aplicar Filtros</span>
                <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </a>

        <!-- Card 3: Catálogo Laboratorial -->
        <a href="<?= BASE_URL ?>/catalogo.php" class="group p-6 bg-white rounded-3xl border border-slate-100 shadow-md hover:shadow-xl hover:border-indigo-200 transition-all flex flex-col justify-between">
            <div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-violet-400 text-white flex items-center justify-center text-2xl shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
                <h3 class="font-extrabold text-base text-slate-900 mt-5 group-hover:text-indigo-600 transition-colors">
                    Catálogo Completo
                </h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Consulte todas as fichas técnicas com nomes científicos, fotos e normas de prevenção.
                </p>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs font-bold text-indigo-600">
                <span>Ver Catálogo</span>
                <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </a>

        <!-- Card 4: Histórico de Consultas -->
        <a href="<?= BASE_URL ?>/historico.php" class="group p-6 bg-white rounded-3xl border border-slate-100 shadow-md hover:shadow-xl hover:border-cyan-200 transition-all flex flex-col justify-between">
            <div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-sky-400 text-white flex items-center justify-center text-2xl shadow-lg shadow-cyan-500/30 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <h3 class="font-extrabold text-base text-slate-900 mt-5 group-hover:text-cyan-600 transition-colors">
                    Histórico Pessoal
                </h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Acompanhe suas detecções anteriores e consulte fotos e relatórios armazenados.
                </p>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-50 flex items-center text-xs font-bold text-cyan-600">
                <span>Ver Histórico</span>
                <i class="fa-solid fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </a>

    </div>

    <!-- Seção de Destaque do Projeto: Exemplo Balão de Fundo Redondo (Slide 9 da Apresentação) -->
    <div class="my-10 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-lg">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <div class="w-full md:w-1/3 text-center">
                <div class="relative inline-block">
                    <img src="<?= BASE_URL ?>/assets/img/image41.jpeg" alt="Balão de Fundo Redondo"
                         class="w-48 h-48 sm:w-56 sm:h-56 object-cover rounded-3xl shadow-md border-4 border-sky-50 mx-auto"
                         onerror="this.src='https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=400&auto=format&fit=crop'">
                    <span class="absolute bottom-2 right-2 px-2.5 py-1 bg-emerald-500 text-white text-[11px] font-bold rounded-lg shadow">
                        <i class="fa-solid fa-check mr-1"></i> Identificado
                    </span>
                </div>
            </div>

            <div class="w-full md:w-2/3">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider bg-sky-100 text-sky-800 rounded-md">Exemplo de Leitura</span>
                    <span class="px-2.5 py-1 text-[11px] font-semibold bg-emerald-100 text-emerald-800 rounded-md">Confiança: 98.4%</span>
                </div>
                <h3 class="text-2xl font-black text-slate-900">Balão de Fundo Redondo</h3>
                <p class="text-xs text-slate-400 font-mono mt-0.5">round-bottom flask &bull; Vidro Borossilicato 3.3</p>

                <div class="mt-4 space-y-3">
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-question text-sky-500"></i> Para que serve:
                        </h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Usado para aquecimento uniforme de líquidos, misturas e reações químicas. Elemento essencial para processos de destilação e refluxo.
                        </p>
                    </div>

                    <div class="p-3 bg-amber-50 rounded-2xl border border-amber-100">
                        <h4 class="text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-amber-600"></i> Dica de Segurança:
                        </h4>
                        <p class="text-xs text-amber-900 mt-1 leading-relaxed">
                            Verificar trincas antes de aquecer e utilizar obrigatoriamente suportes, garras de fixação e mantas aquecedoras reguladas.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex gap-3">
                    <a href="<?= BASE_URL ?>/detalhes.php?id=1" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Ver Ficha Técnica Completa
                    </a>
                    <a href="<?= BASE_URL ?>/camera.php" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Escanear Outra Peça
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Catálogo Rápido de Vidrarias Mais Comuns -->
    <div class="my-10">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Vidrarias Populares de Laboratório</h2>
                <p class="text-xs text-slate-500 mt-0.5">Instrumentos químicos fundamentais cadastrados no sistema</p>
            </div>
            <a href="<?= BASE_URL ?>/catalogo.php" class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                Ver todas (<?= count($destaques) ?>+) <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <?php foreach ($destaques as $v): ?>
                <a href="<?= BASE_URL ?>/detalhes.php?id=<?= $v['id'] ?>" class="group p-3 bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-sky-300 transition flex flex-col items-center text-center">
                    <div class="w-20 h-20 rounded-xl bg-slate-50 flex items-center justify-center p-2 mb-2 group-hover:scale-105 transition-transform overflow-hidden">
                        <?php if (!empty($v['foto_url'])): ?>
                            <img src="<?= BASE_URL ?>/<?= $v['foto_url'] ?>" alt="<?= h($v['nome']) ?>" class="w-full h-full object-contain"
                                 onerror="this.src='https://cdn-icons-png.flaticon.com/512/862/862856.png'">
                        <?php else: ?>
                            <i class="fa-solid fa-flask text-3xl text-sky-500"></i>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-sky-600 transition line-clamp-1">
                        <?= h($v['nome']) ?>
                    </h4>
                    <span class="text-[10px] text-slate-400 mt-0.5"><?= h($v['categoria']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
