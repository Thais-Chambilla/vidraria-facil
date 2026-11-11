<?php
/**
 * Vidraria Fácil - Catálogo Completo de Vidrarias de Laboratório
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$termo = trim($_GET['q'] ?? '');
$categoria = trim($_GET['cat'] ?? '');

$sql = "SELECT * FROM vidrarias WHERE 1=1";
$params = [];

if (!empty($termo)) {
    $sql .= " AND (nome LIKE ? OR nome_en LIKE ? OR funcao_principal LIKE ? OR tags_reconhecimento LIKE ?)";
    $tLike = "%{$termo}%";
    $params = array_merge($params, [$tLike, $tLike, $tLike, $tLike]);
}

if (!empty($categoria)) {
    $sql .= " AND categoria = ?";
    $params[] = $categoria;
}

$sql .= " ORDER BY nome ASC";
$stmt = $bd->prepare($sql);
$stmt->execute($params);
$vidrarias = $stmt->fetchAll();

// Categorias para as abas
$categorias = ['Todas', 'Aquecimento', 'Reação', 'Medição', 'Separação', 'Armazenamento'];

$tituloPagina = "Catálogo de Vidrarias";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-book-open text-sky-600"></i> Catálogo Laboratorial
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Fichas técnicas, recomendações de segurança e manuseio de vidrarias químicas
            </p>
        </div>

        <!-- Barra de Busca -->
        <form method="GET" action="" class="relative max-w-md w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input type="text" name="q" placeholder="Buscar por nome ou função..." value="<?= h($termo) ?>"
                   class="w-full pl-10 pr-20 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition shadow-sm">
            <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 px-3 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl transition">
                Buscar
            </button>
        </form>
    </div>

    <!-- Abas de Filtro por Categoria -->
    <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 scrollbar-none">
        <?php foreach ($categorias as $cat): ?>
            <?php
                $isAtivo = ($cat === 'Todas' && empty($categoria)) || ($cat === $categoria);
                $link = ($cat === 'Todas') ? BASE_URL . '/catalogo.php' : BASE_URL . '/catalogo.php?cat=' . urlencode($cat);
                if (!empty($termo)) $link .= ($cat === 'Todas' ? '?' : '&') . 'q=' . urlencode($termo);
            ?>
            <a href="<?= $link ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $isAtivo ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                <?= $cat ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Grid de Vidrarias -->
    <?php if (empty($vidrarias)): ?>
        <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-200">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">Nenhum resultado encontrado</h3>
            <p class="text-xs text-slate-400 mt-1">Não encontramos vidrarias para os termos pesquisados.</p>
            <a href="<?= BASE_URL ?>/catalogo.php" class="mt-4 inline-block px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Limpar Busca
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($vidrarias as $v): ?>
                <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm hover:shadow-xl hover:border-sky-300 transition flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 rounded-md">
                                <?= h($v['categoria']) ?>
                            </span>
                            <?php if ($v['resiste_aquecimento']): ?>
                                <span class="text-amber-500 text-xs" title="Resiste ao aquecimento">
                                    <i class="fa-solid fa-fire"></i>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="h-40 w-full bg-slate-50 rounded-2xl p-4 flex items-center justify-center mb-4 border border-slate-100 overflow-hidden group-hover:scale-105 transition-transform">
                            <?php if (!empty($v['foto_url'])): ?>
                                <img src="<?= BASE_URL ?>/<?= $v['foto_url'] ?>" alt="<?= h($v['nome']) ?>" class="max-h-full max-w-full object-contain"
                                     onerror="this.src='https://cdn-icons-png.flaticon.com/512/862/862856.png'">
                            <?php else: ?>
                                <i class="fa-solid fa-flask text-5xl text-sky-500"></i>
                            <?php endif; ?>
                        </div>

                        <h3 class="text-base font-extrabold text-slate-900 group-hover:text-sky-600 transition leading-snug">
                            <?= h($v['nome']) ?>
                        </h3>
                        <p class="text-[11px] text-slate-400 font-mono"><?= h($v['nome_en']) ?></p>

                        <p class="text-xs text-slate-600 mt-2 line-clamp-2 leading-relaxed">
                            <?= h($v['funcao_principal']) ?>
                        </p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <a href="<?= BASE_URL ?>/detalhes.php?id=<?= $v['id'] ?>" class="w-full py-2 bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white font-bold text-xs rounded-xl text-center transition flex items-center justify-center gap-1.5">
                            <span>Ver Detalhes</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
