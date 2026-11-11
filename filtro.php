<?php
/**
 * Vidraria Fácil - Caso de Uso 2: Filtrar por Atributos Físicos
 * Conforme slides 3 e 5 da apresentação do projeto
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

// Carrega todas as vidrarias inicialmente
$stmt = $bd->query("SELECT * FROM vidrarias ORDER BY nome ASC");
$todasVidrarias = $stmt->fetchAll();

$tituloPagina = "Filtrar por Atributos";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">

    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <i class="fa-solid fa-filter text-emerald-600"></i> Filtrar por Atributos Físicos
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Selecione características visuais conhecidas (fundo, gargalo, função) para refinar e encontrar a vidraria
        </p>
    </div>

    <!-- Painel de Controles e Filtros -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm mb-8">
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Filtro 1: Formato do Fundo -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-shapes text-sky-500 mr-1"></i> Formato do Fundo
                </label>
                <select id="filtro-fundo" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">Todos os formatos</option>
                    <option value="Redondo">Fundo Redondo (esférico)</option>
                    <option value="Plano">Fundo Plano (estável em bancada)</option>
                    <option value="Cônico">Fundo Cônico / Afunilado</option>
                    <option value="Côncavo">Fundo Côncavo (disco)</option>
                    <option value="Esférico">Esférico em pera</option>
                </select>
            </div>

            <!-- Filtro 2: Tipo de Gargalo -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-wine-bottle text-emerald-500 mr-1"></i> Tipo de Gargalo / Boca
                </label>
                <select id="filtro-gargalo" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Todos os gargalos</option>
                    <option value="Estreito">Gargalo Estreito</option>
                    <option value="Largo">Boca Larga / Bico vertedor</option>
                    <option value="Esmerilhado">Gargalo Esmerilhado (juntas cônicas)</option>
                    <option value="Sem gargalo">Sem gargalo (disco ou placa)</option>
                </select>
            </div>

            <!-- Filtro 3: Categoria de Uso -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-microscope text-indigo-500 mr-1"></i> Finalidade / Categoria
                </label>
                <select id="filtro-categoria" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Todas as categorias</option>
                    <option value="Aquecimento">Aquecimento e Destilação</option>
                    <option value="Reação">Reações e Misturas</option>
                    <option value="Medição">Medição Volumétrica</option>
                    <option value="Separação">Separação e Filtração</option>
                    <option value="Armazenamento">Armazenamento / Biologia</option>
                </select>
            </div>

            <!-- Filtro 4: Resistência ao Calor -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-fire text-amber-500 mr-1"></i> Resistência Térmica
                </label>
                <select id="filtro-aquecimento" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    <option value="">Qualquer tolerância</option>
                    <option value="1">Resiste ao fogo / chama direta</option>
                    <option value="0">Não aquecer (instrumentos calibrados)</option>
                </select>
            </div>

        </div>

        <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
            <span id="contador-resultados" class="text-xs font-bold text-slate-600">
                Mostrando <?= count($todasVidrarias) ?> vidrarias encontradas
            </span>
            <button type="button" onclick="limparFiltros()" class="px-3 py-1.5 text-xs font-semibold text-slate-500 hover:text-rose-600 transition">
                <i class="fa-solid fa-rotate-left mr-1"></i> Limpar Filtros
            </button>
        </div>

    </div>

    <!-- Grid de Resultados Filtrados -->
    <div id="grid-vidrarias" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($todasVidrarias as $v): ?>
            <div class="card-vidraria bg-white rounded-3xl p-5 border border-slate-200 shadow-sm hover:shadow-xl hover:border-emerald-300 transition flex flex-col justify-between"
                 data-fundo="<?= h($v['formato_fundo']) ?>"
                 data-gargalo="<?= h($v['tipo_gargalo']) ?>"
                 data-categoria="<?= h($v['categoria']) ?>"
                 data-aquecimento="<?= $v['resiste_aquecimento'] ?>"
                 data-graduada="<?= $v['graduada'] ?>">
                
                <div>
                    <!-- Badge e Categoria -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 rounded-lg">
                            <?= h($v['categoria']) ?>
                        </span>
                        <?php if ($v['resiste_aquecimento']): ?>
                            <span class="px-2 py-0.5 text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-md">
                                <i class="fa-solid fa-fire-burner text-[9px] mr-1"></i> Aquecível
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200 rounded-md">
                                <i class="fa-solid fa-snowflake text-[9px] mr-1"></i> Não Aquecer
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Imagem da Vidraria -->
                    <div class="h-44 w-full bg-slate-50 rounded-2xl p-4 flex items-center justify-center mb-4 border border-slate-100 overflow-hidden">
                        <?php if (!empty($v['foto_url'])): ?>
                            <img src="<?= BASE_URL ?>/<?= $v['foto_url'] ?>" alt="<?= h($v['nome']) ?>" class="max-h-full max-w-full object-contain"
                                 onerror="this.src='https://cdn-icons-png.flaticon.com/512/862/862856.png'">
                        <?php else: ?>
                            <i class="fa-solid fa-flask text-5xl text-emerald-500"></i>
                        <?php endif; ?>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 leading-snug">
                        <?= h($v['nome']) ?>
                    </h3>
                    <p class="text-[11px] text-slate-400 font-mono"><?= h($v['nome_en']) ?></p>

                    <!-- Características Físicas -->
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px]">
                            Fundo: <?= h($v['formato_fundo']) ?>
                        </span>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px]">
                            Gargalo: <?= h($v['tipo_gargalo']) ?>
                        </span>
                        <?php if ($v['graduada']): ?>
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-[10px] font-medium">
                                Graduada
                            </span>
                        <?php endif; ?>
                    </div>

                    <p class="text-xs text-slate-600 mt-3 line-clamp-2 leading-relaxed">
                        <?= h($v['funcao_principal']) ?>
                    </p>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a href="<?= BASE_URL ?>/detalhes.php?id=<?= $v['id'] ?>" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md flex items-center justify-center gap-1.5 transition">
                        <span>Ver Ficha e Segurança</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

            </div>
        <?php endforeach; ?>
    </div>

    <!-- Mensagem de Nenhum Resultado -->
    <div id="sem-resultados" class="hidden text-center py-16 bg-white rounded-3xl border border-dashed border-slate-200">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
            <i class="fa-solid fa-filter-circle-xmark"></i>
        </div>
        <h3 class="text-base font-bold text-slate-700">Nenhuma vidraria corresponde aos filtros</h3>
        <p class="text-xs text-slate-400 mt-1">Tente remover algumas características selecionadas para ampliar os resultados.</p>
        <button type="button" onclick="limparFiltros()" class="mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
            Redefinir Filtros
        </button>
    </div>

</main>

<script>
const selFundo = document.getElementById('filtro-fundo');
const selGargalo = document.getElementById('filtro-gargalo');
const selCategoria = document.getElementById('filtro-categoria');
const selAquecimento = document.getElementById('filtro-aquecimento');
const cards = document.querySelectorAll('.card-vidraria');
const contador = document.getElementById('contador-resultados');
const msgVazio = document.getElementById('sem-resultados');

function aplicarFiltros() {
    const fundo = selFundo.value.toLowerCase();
    const gargalo = selGargalo.value.toLowerCase();
    const categoria = selCategoria.value.toLowerCase();
    const aquecimento = selAquecimento.value;

    let visiveis = 0;

    cards.forEach(card => {
        const cFundo = (card.dataset.fundo || '').toLowerCase();
        const cGargalo = (card.dataset.gargalo || '').toLowerCase();
        const cCategoria = (card.dataset.categoria || '').toLowerCase();
        const cAquecimento = card.dataset.aquecimento;

        let ok = true;
        if (fundo && cFundo !== fundo) ok = false;
        if (gargalo && cGargalo !== gargalo) ok = false;
        if (categoria && cCategoria !== categoria) ok = false;
        if (aquecimento !== '' && cAquecimento !== aquecimento) ok = false;

        if (ok) {
            card.classList.remove('hidden');
            visiveis++;
        } else {
            card.classList.add('hidden');
        }
    });

    contador.textContent = `Mostrando ${visiveis} vidraria(s) encontrada(s)`;
    if (visiveis === 0) {
        msgVazio.classList.remove('hidden');
    } else {
        msgVazio.classList.add('hidden');
    }
}

function limparFiltros() {
    selFundo.value = '';
    selGargalo.value = '';
    selCategoria.value = '';
    selAquecimento.value = '';
    aplicarFiltros();
}

selFundo.addEventListener('change', aplicarFiltros);
selGargalo.addEventListener('change', aplicarFiltros);
selCategoria.addEventListener('change', aplicarFiltros);
selAquecimento.addEventListener('change', aplicarFiltros);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
