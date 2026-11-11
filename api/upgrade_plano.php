<?php
/**
 * Vidraria Fácil - API / Ação para Upgrade de Plano para Premium
 * Regra: Altera o plano do usuário e registra log de auditoria
 */

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

$usuario = exigir_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoPlano = 'premium';

    $stmt = $bd->prepare("UPDATE usuarios SET plano = ? WHERE id = ?");
    $sucesso = $stmt->execute([$novoPlano, $usuario['id']]);

    if ($sucesso) {
        // Atualiza a sessão
        $_SESSION['usuario']['plano'] = $novoPlano;

        // Auditoria
        registrar_log(
            $bd,
            $usuario['id'],
            $usuario['nome'],
            'UPGRADE_PLANO_PREMIUM',
            'usuarios',
            $usuario['id'],
            'Usuário realizou adesão ao Plano Premium com sucesso'
        );

        definir_toast('sucesso', 'Parabéns!', 'Você agora é um usuário Premium! Desfrute de pesquisas e histórico ilimitados.');
    } else {
        definir_toast('erro', 'Falha no Upgrade', 'Não foi possível atualizar seu plano no momento.');
    }
}

header("Location: " . BASE_URL . "/planos.php");
exit;
