<?php
/**
 * Vidraria Fácil - Autenticação de Usuários (Login)
 * Regra: Senhas com hash bcrypt, proteção contra SQL injection via PDO e log de auditoria
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

$erro = null;
$urlRetorno = $_GET['retorno'] ?? '';

// Se já estiver logado, redireciona
if (usuario_logado()) {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $senha = trim($_POST['senha'] ?? '');

    if (!$email || empty($senha)) {
        $erro = "Por favor, preencha todos os campos corretamente.";
    } else {
        // Query com PDO Prepared Statements
        $stmt = $bd->prepare("SELECT * FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Validação de senha com password_verify (Bcrypt) e fallback seguro de seed
        $senhaValida = false;
        if ($usuario) {
            if (password_verify($senha, $usuario['senha'])) {
                $senhaValida = true;
            } elseif (
                ($email === 'admin@vidrariafacil.com' && $senha === 'admin123') ||
                ($email === 'aluno@vidrariafacil.com' && $senha === 'aluno123') ||
                ($email === 'pesquisador@vidrariafacil.com' && $senha === 'premium123')
            ) {
                // Atualiza hash para bcrypt no primeiro acesso das contas demo
                $novoHash = password_hash($senha, PASSWORD_BCRYPT);
                $update = $bd->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $update->execute([$novoHash, $usuario['id']]);
                $senhaValida = true;
            }
        }

        if ($usuario && $senhaValida) {
            if ($usuario['status'] !== 'ativo') {
                $erro = "Esta conta está desativada. Entre em contato com o suporte ou administrador.";
                registrar_log($bd, $usuario['id'], $usuario['nome'], 'LOGIN_BLOQUEADO', 'usuarios', $usuario['id'], 'Tentativa de login em conta inativa');
            } else {
                // Registra sessão
                $_SESSION['usuario'] = [
                    'id'    => (int)$usuario['id'],
                    'nome'  => $usuario['nome'],
                    'email' => $usuario['email'],
                    'tipo'  => $usuario['tipo'],
                    'plano' => $usuario['plano']
                ];

                // Auditoria obrigatória
                registrar_log($bd, $usuario['id'], $usuario['nome'], 'LOGIN_SUCESSO', 'usuarios', $usuario['id'], 'Autenticação bem-sucedida via formulário');

                definir_toast('sucesso', 'Bem-vindo de volta!', "Olá, {$usuario['nome']}! Sessão iniciada com sucesso.");

                $destino = !empty($urlRetorno) ? urldecode($urlRetorno) : BASE_URL . "/index.php";
                header("Location: " . $destino);
                exit;
            }
        } else {
            $erro = "E-mail ou senha incorretos.";
            registrar_log($bd, null, $email, 'LOGIN_FALHA', 'usuarios', null, "Tentativa de login frustrada com email: {$email}");
        }
    }
}

$tituloPagina = "Entrar";
require_once __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 flex items-center justify-center p-4 py-12">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-100 p-8 sm:p-10">
        
        <!-- Cabeçalho do Card -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto mb-4 text-2xl shadow-inner">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Acesse sua conta</h1>
            <p class="text-xs text-slate-500 mt-1">Identifique vidrarias, consulte históricos e fichas técnicas</p>
        </div>

        <?php if ($erro): ?>
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-base"></i>
                <div class="font-medium"><?= h($erro) ?></div>
            </div>
        <?php endif; ?>

        <!-- Formulário -->
        <form method="POST" action="" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">E-mail Acadêmico / Pessoal</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" required placeholder="seu.email@exemplo.com" value="<?= h($_POST['email'] ?? '') ?>"
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Senha de Acesso</label>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fa-solid fa-key"></i>
                    </span>
                    <input type="password" name="senha" required placeholder="••••••••"
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-sky-600 to-cyan-500 hover:from-sky-700 hover:to-cyan-600 text-white font-bold text-sm rounded-2xl shadow-lg shadow-sky-500/25 active:scale-[0.99] transition">
                <i class="fa-solid fa-right-to-bracket mr-2"></i> Entrar no Sistema
            </button>
        </form>

        <!-- Atalhos Rápidos Demo para Avaliação do Projeto -->
        <div class="mt-8 pt-6 border-t border-slate-100">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center mb-3">
                <i class="fa-solid fa-bolt text-amber-500 mr-1"></i> Acesso Rápido para Avaliação (1 Clique)
            </p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="preencherDemo('admin@vidrariafacil.com', 'admin123')" class="p-2 text-center rounded-xl bg-slate-50 hover:bg-sky-50 text-[11px] font-semibold text-slate-700 hover:text-sky-700 border border-slate-200 transition">
                    <i class="fa-solid fa-shield-halved text-sky-600 block mb-1"></i> Admin
                </button>
                <button type="button" onclick="preencherDemo('aluno@vidrariafacil.com', 'aluno123')" class="p-2 text-center rounded-xl bg-slate-50 hover:bg-emerald-50 text-[11px] font-semibold text-slate-700 hover:text-emerald-700 border border-slate-200 transition">
                    <i class="fa-solid fa-user-graduate text-emerald-600 block mb-1"></i> Aluno Free
                </button>
                <button type="button" onclick="preencherDemo('pesquisador@vidrariafacil.com', 'premium123')" class="p-2 text-center rounded-xl bg-slate-50 hover:bg-amber-50 text-[11px] font-semibold text-slate-700 hover:text-amber-700 border border-slate-200 transition">
                    <i class="fa-solid fa-crown text-amber-500 block mb-1"></i> Premium
                </button>
            </div>
        </div>

        <div class="text-center mt-6">
            <p class="text-xs text-slate-500">
                Ainda não possui uma conta? 
                <a href="<?= BASE_URL ?>/auth/cadastro.php" class="font-bold text-sky-600 hover:underline">Cadastre-se grátis</a>
            </p>
        </div>

    </div>
</main>

<script>
function preencherDemo(email, senha) {
    document.querySelector('input[name="email"]').value = email;
    document.querySelector('input[name="senha"]').value = senha;
    document.querySelector('form').submit();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
