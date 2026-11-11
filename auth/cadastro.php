<?php
/**
 * Vidraria Fácil - Cadastro de Novos Usuários
 * Regra: Senhas criptografadas com Bcrypt, plano gratuito automático e log de auditoria
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

$erro = null;

if (usuario_logado()) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $senha = trim($_POST['senha'] ?? '');
    $senhaConfirma = trim($_POST['senha_confirma'] ?? '');

    if (empty($nome) || !$email || empty($senha)) {
        $erro = "Por favor, preencha todos os campos obrigatórios.";
    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve conter no mínimo 6 caracteres.";
    } elseif ($senha !== $senhaConfirma) {
        $erro = "As senhas digitadas não coincidem.";
    } else {
        // Verifica se email já existe
        $stmtCheck = $bd->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $erro = "Este endereço de e-mail já está cadastrado no sistema.";
        } else {
            // Hash seguro com Bcrypt conforme requisito CORE
            $hashSenha = password_hash($senha, PASSWORD_BCRYPT);

            // Inserção com Prepared Statement
            $stmtInsert = $bd->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo, plano, status, pesquisas_hoje, data_ultimo_reset)
                VALUES (?, ?, ?, 'comum', 'gratuito', 'ativo', 0, CURRENT_DATE)
            ");
            $sucesso = $stmtInsert->execute([$nome, $email, $hashSenha]);

            if ($sucesso) {
                $novoId = (int)$bd->lastInsertId();

                // Auditoria da criação de usuário
                registrar_log($bd, $novoId, $nome, 'CADASTRO_NOVO_USUARIO', 'usuarios', $novoId, 'Novo usuário cadastrado no plano Gratuito');

                // Autologin
                $_SESSION['usuario'] = [
                    'id'    => $novoId,
                    'nome'  => $nome,
                    'email' => $email,
                    'tipo'  => 'comum',
                    'plano' => 'gratuito'
                ];

                definir_toast('sucesso', 'Conta Criada!', "Seja bem-vindo ao Vidraria Fácil, {$nome}! Seu plano Gratuito já está ativo.");
                header("Location: " . BASE_URL . "/index.php");
                exit;
            } else {
                $erro = "Ocorreu um erro ao registrar sua conta. Tente novamente.";
            }
        }
    }
}

$tituloPagina = "Criar Conta";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 flex items-center justify-center p-4 py-12">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 sm:p-10">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Crie sua conta</h1>
            <p class="text-xs text-slate-500 mt-1">Comece grátis a identificar vidrarias com visão computacional</p>
        </div>

        <?php if ($erro): ?>
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-base"></i>
                <div class="font-medium"><?= h($erro) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nome Completo</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="nome" required placeholder="Ex: Lucas Silva" value="<?= h($_POST['nome'] ?? '') ?>"
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">E-mail</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required placeholder="seu.email@exemplo.com" value="<?= h($_POST['email'] ?? '') ?>"
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Senha (mínimo 6 dígitos)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="senha" required placeholder="••••••••"
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Confirmar Senha</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-shield-check"></i>
                    </span>
                    <input type="password" name="senha_confirma" required placeholder="••••••••"
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                </div>
            </div>

            <div class="p-3 bg-sky-50 rounded-xl border border-sky-100 flex items-center gap-2 text-[11px] text-sky-800">
                <i class="fa-solid fa-gift text-sky-600 text-sm"></i>
                <span>Você terá acesso imediato com cota diária de pesquisas gratuitas.</span>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-sky-600 to-cyan-500 hover:from-sky-700 hover:to-cyan-600 text-white font-bold text-sm rounded-2xl shadow-lg shadow-sky-500/25 active:scale-[0.99] transition">
                <i class="fa-solid fa-user-check mr-2"></i> Criar Minha Conta Gratuita
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-xs text-slate-500">
                Já tem cadastro? 
                <a href="<?= BASE_URL ?>/auth/login.php" class="font-bold text-sky-600 hover:underline">Faça login aqui</a>
            </p>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
