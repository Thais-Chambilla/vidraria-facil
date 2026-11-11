<?php
/**
 * Vidraria Fácil - Histórico de Pesquisas do Usuário
 * Regra CORE:
 * - Usuário Gratuito: histórico reduzido (configurado pelo ADM)
 * - Usuário Premium: histórico completo de todo o período
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$usuario = exigir_login();
$limites = obter_limites_plano($bd, $usuario);

$isPremium = $limites['is_premium'];
$diasLimite = $limites['dias_historico'];
$itensLimite = $limites['itens_historico'];

// Construção da consulta respeitando estritamente o plano do usuário
if ($isPremium) {
    // Premium: histórico completo e irrestrito
    $sql = "
        SELECT h.*, v.nome as vidraria_nome, v.categoria as vidraria_categoria, v.foto_url as vidraria_foto
        FROM pesquisas_historico h
        LEFT JOIN vidrarias v ON h.vidraria_id = v.id
        WHERE h.usuario_id = ?
        ORDER BY h.data_pesquisa DESC
    ";
    $stmt = $bd->prepare($sql);
    $stmt->execute([$usuario['id']]);
} else {
    // Gratuito: restrição de dias e quantidade máxima conforme regra de negócio
    $driver = $bd->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'mysql') {
        $sql = "
            SELECT h.*, v.nome as vidraria_nome, v.categoria as vidraria_categoria, v.foto_url as vidraria_foto
            FROM pesquisas_historico h
            LEFT JOIN vidrarias v ON h.vidraria_id = v.id
            WHERE h.usuario_id = ? 
              AND h.data_pesquisa >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY h.data_pesquisa DESC
            LIMIT ?
        ";
    } else {
        // SQLite
        $sql = "
            SELECT h.*, v.nome as vidraria_nome, v.categoria as vidraria_categoria, v.foto_url as vidraria_foto
            FROM pesquisas_historico h
            LEFT JOIN vidrarias v ON h.vidraria_id = v.id
            WHERE h.usuario_id = ? 
              AND h.data_pesquisa >= datetime('now', '-' || ? || ' days')
            ORDER BY h.data_pesquisa DESC
            LIMIT ?
        ";
    }
    $stmt = $bd->prepare($sql);
    $stmt->execute([$usuario['id'], $diasLimite, $itensLimite]);
}

$historico = $stmt->fetchAll();

$tituloPagina = "Meu Histórico";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-sky-600"></i> Meu Histórico de Detecções
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Registro de todas as vidrarias identificadas por você via foto e inteligência artificial
            </p>
        </div>

        <a href="<?= BASE_URL ?>/camera.php" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
            <i class="fa-solid fa-camera"></i>
            <span>Nova Detecção</span>
        </a>
    </div>

    <!-- Banner Explicativo do Plano (Regra de Negócio: Free limitado vs Premium completo) -->
    <?php if ($isPremium): ?>
        <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-base shrink-0">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div>
                    <h4 class="font-bold text-amber-900">Histórico Completo Premium Ativo</h4>
                    <p class="text-amber-700 mt-0.5">Você tem acesso vitalício a todas as consultas realizadas no laboratório desde a criação da sua conta.</p>
                </div>
            </div>
            <span class="px-2.5 py-1 bg-amber-200/60 text-amber-900 font-bold rounded-lg text-[10px] uppercase tracking-wider hidden sm:inline-block">
                Sem Limites
            </span>
        </div>
    <?php else: ?>
        <div class="mb-6 p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold text-base shrink-0">
                    <i class="fa-solid fa-info"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sky-950">Visualização Parcial (Plano Gratuito)</h4>
                    <p class="text-sky-800 mt-0.5">
                        Exibindo no máximo as últimas <strong><?= $itensLimite ?> consultas</strong> dos últimos <strong><?= $diasLimite ?> dias</strong> conforme regras do plano.
                    </p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/planos.php" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-bold rounded-xl text-xs shadow-sm hover:opacity-95 transition shrink-0">
                <i class="fa-solid fa-crown mr-1"></i> Desbloquear Histórico Completo
            </a>
        </div>
    <?php endif; ?>

    <!-- Lista de Itens do Histórico -->
    <?php if (empty($historico)): ?>
        <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-200">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                <i class="fa-solid fa-camera-rotate"></i>
            </div>
            <h3 class="text-base font-bold text-slate-700">Nenhuma identificação registrada ainda</h3>
            <p class="text-xs text-slate-400 mt-1">Abra a câmera do celular ou carregue uma foto para fazer sua primeira leitura.</p>
            <a href="<?= BASE_URL ?>/camera.php" class="mt-4 inline-block px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition">
                Escanear Vidraria Agora
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($historico as $item): ?>
                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                    
                    <div class="flex items-center gap-4">
                        <!-- Foto Capturada -->
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-900 overflow-hidden shrink-0 border border-slate-100 flex items-center justify-center">
                            <?php if (!empty($item['imagem_path']) && file_exists(__DIR__ . '/' . $item['imagem_path'])): ?>
                                <img src="<?= BASE_URL ?>/<?= $item['imagem_path'] ?>" alt="Captura" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fa-solid fa-flask text-2xl text-slate-400"></i>
                            <?php endif; ?>
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-extrabold text-slate-900">
                                    <?= h($item['resultado_identificado']) ?>
                                </h3>
                                <?php if ($item['confianca_ia']): ?>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded-md">
                                        <?= $item['confianca_ia'] ?>%
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="text-xs text-slate-400 mt-1 flex items-center gap-3">
                                <span><i class="fa-regular fa-calendar mr-1"></i> <?= date('d/m/Y H:i', strtotime($item['data_pesquisa'])) ?></span>
                                <span><i class="fa-solid fa-tag mr-1"></i> <?= ucfirst($item['metodo_busca']) ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="w-full sm:w-auto flex items-center gap-2 justify-end pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                        <?php if ($item['vidraria_id']): ?>
                            <a href="<?= BASE_URL ?>/detalhes.php?id=<?= $item['vidraria_id'] ?>" class="px-4 py-2 bg-sky-50 text-sky-700 hover:bg-sky-100 font-bold text-xs rounded-xl transition">
                                Ver Ficha Técnica
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
