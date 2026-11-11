<?php
/**
 * Vidraria Fácil - Planos de Acesso (Gratuito vs Premium)
 * Regra CORE:
 * - Gratuito: pesquisas e histórico limitados
 * - Premium: buscas mais amplas e histórico completo
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$usuarioAtual = usuario_logado();
$limites = $usuarioAtual ? obter_limites_plano($bd, $usuarioAtual) : null;
$limiteAdmin = (int)obter_configuracao($bd, 'limite_pesquisas_gratuito', 5);
$diasAdmin = (int)obter_configuracao($bd, 'dias_historico_gratuito', 7);

$tituloPagina = "Planos de Acesso";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">

    <div class="text-center max-w-xl mx-auto mb-10">
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-800">
            Planos & Benefícios
        </span>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight mt-3">
            Escolha o plano ideal para seus estudos ou laboratório
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-2">
            Identifique vidrarias instantaneamente e mantenha seu histórico sempre acessível
        </p>
    </div>

    <!-- Cards Comparativos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">
        
        <!-- Plano Gratuito -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-md flex flex-col justify-between relative">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-lg uppercase">
                        Plano Estudante
                    </span>
                    <span class="text-xs font-semibold text-slate-400">100% Grátis</span>
                </div>

                <h3 class="text-2xl font-black text-slate-900">Gratuito</h3>
                <p class="text-xs text-slate-500 mt-1">Ideal para consultas esporádicas e aulas práticas</p>

                <div class="my-6">
                    <span class="text-4xl font-black text-slate-900">R$ 0</span>
                    <span class="text-xs text-slate-400 font-medium">/ para sempre</span>
                </div>

                <!-- Lista de Recursos -->
                <ul class="space-y-3.5 text-xs text-slate-600">
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-check text-emerald-500"></i>
                        <span>Até <strong><?= $limiteAdmin ?> pesquisas por dia</strong> com câmera</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-check text-emerald-500"></i>
                        <span>Histórico das últimas consultas (máx. <strong><?= $diasAdmin ?> dias</strong>)</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-check text-emerald-500"></i>
                        <span>Acesso ao catálogo com mais de 15 vidrarias</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-check text-emerald-500"></i>
                        <span>Filtro por atributos anatômicos (fundo, gargalo)</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-slate-400 line-through">
                        <i class="fa-solid fa-xmark text-slate-300"></i>
                        <span>Histórico vitalício ilimitado</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-slate-400 line-through">
                        <i class="fa-solid fa-xmark text-slate-300"></i>
                        <span>Buscas ilimitadas por dia</span>
                    </li>
                </ul>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-100">
                <?php if ($usuarioAtual && !$limites['is_premium']): ?>
                    <button type="button" disabled class="w-full py-3 bg-slate-100 text-slate-500 font-bold text-xs rounded-2xl cursor-default text-center">
                        Seu Plano Atual
                    </button>
                <?php elseif (!$usuarioAtual): ?>
                    <a href="<?= BASE_URL ?>/auth/cadastro.php" class="block w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-2xl text-center transition">
                        Começar Grátis
                    </a>
                <?php else: ?>
                    <span class="block text-center text-xs text-slate-400 font-medium">Plano básico incluído</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Plano Premium (Destaque) -->
        <div class="bg-gradient-to-b from-amber-500/10 via-white to-white rounded-3xl p-8 border-2 border-amber-400 shadow-xl flex flex-col justify-between relative">
            
            <div class="absolute -top-3.5 right-8 px-3.5 py-1 bg-gradient-to-r from-amber-500 to-yellow-400 text-white font-black text-[10px] uppercase tracking-wider rounded-full shadow-md">
                Recomendado para Laboratórios
            </div>

            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg uppercase">
                        Plano Pesquisador
                    </span>
                    <i class="fa-solid fa-crown text-amber-500 text-lg"></i>
                </div>

                <h3 class="text-2xl font-black text-slate-900">Premium Vitalício</h3>
                <p class="text-xs text-slate-500 mt-1">Buscas ilimitadas e histórico completo de todo o período</p>

                <div class="my-6">
                    <span class="text-4xl font-black text-slate-900">R$ 19,90</span>
                    <span class="text-xs text-slate-400 font-medium">/ pagamento único de ativação</span>
                </div>

                <!-- Lista de Recursos Premium -->
                <ul class="space-y-3.5 text-xs text-slate-700">
                    <li class="flex items-center gap-2.5 font-semibold text-slate-900">
                        <i class="fa-solid fa-circle-check text-amber-500 text-sm"></i>
                        <span><strong>Buscas ILIMITADAS</strong> via câmera e upload</span>
                    </li>
                    <li class="flex items-center gap-2.5 font-semibold text-slate-900">
                        <i class="fa-solid fa-circle-check text-amber-500 text-sm"></i>
                        <span><strong>Histórico COMPLETO</strong> de todo o período</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-check text-amber-500 text-sm"></i>
                        <span>Fichas técnicas expandidas com riscos e biossegurança</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-check text-amber-500 text-sm"></i>
                        <span>Reconhecimento prioritário no motor de IA</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-check text-amber-500 text-sm"></i>
                        <span>Exportação de relatórios para auditoria laboratorial</span>
                    </li>
                </ul>
            </div>

            <div class="mt-8 pt-6 border-t border-amber-100">
                <?php if ($usuarioAtual && $limites['is_premium']): ?>
                    <button type="button" disabled class="w-full py-3.5 bg-emerald-500 text-white font-black text-xs rounded-2xl cursor-default text-center shadow-md flex items-center justify-center gap-2">
                        <i class="fa-solid fa-crown"></i>
                        <span>Você já é Premium!</span>
                    </button>
                <?php elseif ($usuarioAtual): ?>
                    <form method="POST" action="<?= BASE_URL ?>/api/upgrade_plano.php">
                        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white font-black text-xs rounded-2xl text-center shadow-lg shadow-amber-500/25 transition flex items-center justify-center gap-2 active:scale-95">
                            <i class="fa-solid fa-crown"></i>
                            <span>Ativar Plano Premium Agora (1 Clique Demo)</span>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/cadastro.php" class="block w-full py-3.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white font-black text-xs rounded-2xl text-center shadow-lg shadow-amber-500/25 transition">
                        Criar Conta e Ativar Premium
                    </a>
                <?php endif; ?>
            </div>

        </div>

    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
