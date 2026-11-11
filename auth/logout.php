<?php
/**
 * Vidraria Fácil - Encerramento Seguro de Sessão (Logout)
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

$usuario = usuario_logado();

if ($usuario) {
    registrar_log($bd, $usuario['id'], $usuario['nome'], 'LOGOUT', 'usuarios', $usuario['id'], 'Sessão encerrada pelo usuário');
}

// Limpa todas as variáveis de sessão
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Reinicia sessão temporária apenas para exibir o toast informativo
session_start();
definir_toast('info', 'Sessão Encerrada', 'Você saiu da sua conta com segurança.');

header("Location: " . BASE_URL . "/auth/login.php");
exit;
