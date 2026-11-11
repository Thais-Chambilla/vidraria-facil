<?php
/**
 * Vidraria Fácil - Perfil do Usuário e Gerenciamento de Cota
 */

require_once __DIR__ . '/bd.php';
require_once __DIR__ . '/includes/funcoes.php';

$usuario = exigir_login();
$limites = obter_limites_plano($bd, $usuario);

$mensagemSucesso = null;
$mensagemErro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'atualizar_dados') {
        $novoNome = trim($_POST['nome'] ?? '');
        if (empty($novoNome)) {
            $mensagemErro = "O nome não pode estar em branco.";
        } else {
            $stmt = $bd->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
            $stmt->execute([$novoNome, $usuario['id']]);

            $_SESSION['usuario']['nome'] = $novoNome;
            $usuario['nome'] = $novoNome;

            registrar_log($bd, $usuario['id'], $novoNome, 'PERFIL_ATUALIZADO', 'usuarios', $usuario['id'], "Nome atualizado para: {$novoNome}");
            definir_toast('sucesso', 'Perfil Atualizado', 'Seus dados foram atualizados com sucesso.');
            header("Location: " . BASE_URL . "/perfil.php");
            exit;
        }
    } elseif ($acao === 'alterar_senha') {
        $senhaAtual = trim($_POST['senha_atual'] ?? '');
        $novaSenha = trim($_POST['nova_senha'] ?? '');
        $confirmaSenha = trim($_POST['confirma_senha'] ?? '');

        // Busca senha atual do banco
        $stmtS = $bd->prepare("SELECT senha FROM usuarios WHERE id = ?");
        $stmtS->execute([$usuario['id']]);
        $rowS = $stmtS->fetch();

        if (!$rowS || !password_verify($senhaAtual, $rowS['senha'])) {
            $mensagemErro = "A senha atual informada está incorreta.";
        } elseif (strlen($novaSenha) < 6) {
            $mensagemErro = "A nova senha deve ter no mínimo 6 caracteres.";
        } elseif ($novaSenha !== $confirmaSenha) {
            $mensagemErro = "As novas senhas não coincidem.";
        } else {
            $novoHash = password_hash($novaSenha, PASSWORD_BCRYPT);
            $stmtUp = $bd->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmtUp->execute([$novoHash, $usuario['id']]);

            registrar_log($bd, $usuario['id'], $usuario['nome'], 'SENHA_ALTERADA', 'usuarios', $usuario['id'], 'Senha redefinida com novo hash Bcrypt');
            definir_toast('sucesso', 'Senha Alterada', 'Sua senha foi redefinida com segurança.');
            header("Location: " . BASE_URL . "/perfil.php");
            exit;
        }
    }
}

$tituloPagina = "Meu Perfil";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2">
            <i class="fa-solid fa-id-card text-sky-600"></i> Meu Perfil & Cota
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Gerencie suas informações de acesso e consulte seu consumo de pesquisas</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Card 1: Resumo do Plano e Cota (Regras de Negócio) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-md <?= $limites['is_premium'] ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' ?>">
                    Status da Assinatura
                </span>

                <div class="mt-4 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl <?= $limites['is_premium'] ? 'bg-amber-500' : 'bg-sky-600' ?> text-white flex items-center justify-center text-xl shadow-md">
                        <i class="fa-solid <?= $limites['is_premium'] ? 'fa-crown' : 'fa-user' ?>"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 capitalize">
                            Plano <?= h($usuario['plano']) ?>
                        </h3>
                        <p class="text-[11px] text-slate-400">Tipo de Conta: <?= ucfirst($usuario['tipo']) ?></p>
                    </div>
                </div>

                <!-- Barra de Consumo Diário -->
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                        <span class="text-slate-600">Pesquisas Hoje:</span>
                        <?php if ($limites['is_premium']): ?>
                            <span class="text-emerald-600">Ilimitadas</span>
                        <?php else: ?>
                            <span class="text-sky-600"><?= $limites['pesquisas_feitas_hoje'] ?> / <?= $limites['limite_pesquisas'] ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!$limites['is_premium']): ?>
                        <?php
                            $porcentagem = min(100, round(($limites['pesquisas_feitas_hoje'] / max(1, $limites['limite_pesquisas'])) * 100));
                        ?>
                        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-sky-500 to-cyan-400 rounded-full transition-all" style="width: <?= $porcentagem ?>%;"></div>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2">
                            Restam <strong><?= $limites['pesquisas_restantes'] ?></strong> pesquisas para hoje.
                        </p>
                    <?php else: ?>
                        <div class="p-2.5 bg-amber-50 rounded-xl text-[11px] text-amber-800 font-medium">
                            <i class="fa-solid fa-sparkles text-amber-500 mr-1"></i> Acesso livre a todas as funcionalidades.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                    <p><i class="fa-solid fa-clock mr-1 text-slate-400"></i> Histórico: <strong><?= $limites['is_premium'] ? 'Vitalício Ilimitado' : "Últimos {$limites['dias_historico']} dias" ?></strong></p>
                </div>
            </div>

            <div class="mt-6">
                <?php if (!$limites['is_premium']): ?>
                    <a href="<?= BASE_URL ?>/planos.php" class="block w-full py-2.5 px-4 bg-gradient-to-r from-amber-500 to-yellow-500 text-white font-bold text-xs rounded-xl text-center shadow-md shadow-amber-500/20 hover:opacity-95 transition">
                        <i class="fa-solid fa-crown mr-1"></i> Upgrade para Premium
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 2: Dados Cadastrais e Redefinição de Senha -->
        <div class="md:col-span-2 space-y-6">
            
            <?php if ($mensagemErro): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 mr-1"></i> <?= h($mensagemErro) ?>
                </div>
            <?php endif; ?>

            <!-- Atualizar Nome -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Informações Pessoais</h3>
                
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="acao" value="atualizar_dados">

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nome Completo</label>
                        <input type="text" name="nome" value="<?= h($usuario['nome']) ?>" required
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">E-mail Cadastrado</label>
                        <input type="email" value="<?= h($usuario['email']) ?>" disabled
                               class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-400 cursor-not-allowed">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">O e-mail é utilizado como identificador exclusivo de acesso.</span>
                    </div>

                    <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Salvar Alterações
                    </button>
                </form>
            </div>

            <!-- Alterar Senha (Bcrypt) -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4">Segurança & Senha (Bcrypt)</h3>
                
                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="acao" value="alterar_senha">

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Senha Atual</label>
                        <input type="password" name="senha_atual" required placeholder="••••••••"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nova Senha</label>
                            <input type="password" name="nova_senha" required placeholder="Mínimo 6 dígitos"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Confirmar Nova Senha</label>
                            <input type="password" name="confirma_senha" required placeholder="Repita a nova senha"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Atualizar Senha
                    </button>
                </form>
            </div>

        </div>

    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
