<?php
/**
 * Vidraria Fácil - Funções Utilitárias e Regras de Negócio (CORE)
 */

require_once __DIR__ . '/../bd.php';

/**
 * Registra logs de auditoria no banco de dados para qualquer ação ou alteração
 */
function registrar_log(
    PDO $bd,
    ?int $usuarioId,
    ?string $usuarioNome,
    string $acao,
    ?string $tabelaAfetada = null,
    ?int $registroId = null,
    ?string $detalhes = null
): void {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido', 0, 250);

        if (!$usuarioNome && $usuarioId) {
            $stmtUser = $bd->prepare("SELECT nome FROM usuarios WHERE id = ?");
            $stmtUser->execute([$usuarioId]);
            $u = $stmtUser->fetch();
            $usuarioNome = $u ? $u['nome'] : 'Usuário #' . $usuarioId;
        }

        $stmt = $bd->prepare("
            INSERT INTO logs_auditoria (usuario_id, usuario_nome, acao, tabela_afetada, registro_id, detalhes, ip_address, user_agent, data_hora)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))
        ");
        
        // Em MySQL, datetime('now') gera erro de sintaxe, ajustamos dinamicamente se for MySQL
        $driver = $bd->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $stmt = $bd->prepare("
                INSERT INTO logs_auditoria (usuario_id, usuario_nome, acao, tabela_afetada, registro_id, detalhes, ip_address, user_agent, data_hora)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
        }

        $stmt->execute([
            $usuarioId,
            $usuarioNome,
            strtoupper(trim($acao)),
            $tabelaAfetada,
            $registroId,
            $detalhes,
            $ip,
            $userAgent
        ]);
    } catch (Exception $e) {
        // Falhas de log não devem derrubar a aplicação, mas podem ser registradas no error_log
        error_log("Erro ao gravar log de auditoria: " . $e->getMessage());
    }
}

/**
 * Retorna os dados do usuário autenticado na sessão atual ou null
 */
function usuario_logado(): ?array {
    return $_SESSION['usuario'] ?? null;
}

/**
 * Exige autenticação prévia para prosseguir
 */
function exigir_login(): array {
    $usuario = usuario_logado();
    if (!$usuario) {
        definir_toast('aviso', 'Acesso Restrito', 'Por favor, realize login para acessar esta funcionalidade.');
        $urlRetorno = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header("Location: " . BASE_URL . "/auth/login.php?retorno=" . $urlRetorno);
        exit;
    }
    return $usuario;
}

/**
 * Exige perfil de Administrador
 */
function exigir_admin(): array {
    $usuario = exigir_login();
    if (($usuario['tipo'] ?? '') !== 'admin') {
        definir_toast('erro', 'Acesso Negado', 'Esta área é exclusiva para administradores.');
        header("Location: " . BASE_URL . "/index.php");
        exit;
    }
    return $usuario;
}

/**
 * Obtém valor de configuração armazenado na base de dados
 */
function obter_configuracao(PDO $bd, string $chave, $padrao = null) {
    try {
        $stmt = $bd->prepare("SELECT valor, tipo FROM configuracoes WHERE chave = ? LIMIT 1");
        $stmt->execute([$chave]);
        $row = $stmt->fetch();
        if (!$row) return $padrao;

        if ($row['tipo'] === 'number' || is_numeric($row['valor'])) {
            return (float)$row['valor'];
        }
        return $row['valor'];
    } catch (Exception $e) {
        return $padrao;
    }
}

/**
 * Salva configuração no banco e registra log de auditoria
 */
function salvar_configuracao(PDO $bd, string $chave, string $valor, ?int $usuarioId = null): bool {
    try {
        $stmt = $bd->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
        $stmt->execute([$valor, $chave]);

        registrar_log(
            $bd,
            $usuarioId,
            $_SESSION['usuario']['nome'] ?? 'ADM',
            'CONFIG_ALTERADA',
            'configuracoes',
            null,
            "Chave '{$chave}' alterada para: {$valor}"
        );
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Calcula limites do plano para o usuário (CORE: Free limitado vs Premium ilimitado)
 */
function obter_limites_plano(PDO $bd, array $usuario): array {
    $hoje = date('Y-m-d');
    $usuarioId = $usuario['id'];

    // Sincroniza dados mais recentes do banco
    $stmt = $bd->prepare("SELECT plano, pesquisas_hoje, data_ultimo_reset FROM usuarios WHERE id = ?");
    $stmt->execute([$usuarioId]);
    $uAtual = $stmt->fetch();

    if ($uAtual) {
        // Reseta o contador se virou o dia
        if ($uAtual['data_ultimo_reset'] !== $hoje) {
            $update = $bd->prepare("UPDATE usuarios SET pesquisas_hoje = 0, data_ultimo_reset = ? WHERE id = ?");
            $update->execute([$hoje, $usuarioId]);
            $uAtual['pesquisas_hoje'] = 0;
            $uAtual['data_ultimo_reset'] = $hoje;
        }
        $plano = $uAtual['plano'];
        $pesquisasHoje = (int)$uAtual['pesquisas_hoje'];
    } else {
        $plano = $usuario['plano'] ?? 'gratuito';
        $pesquisasHoje = 0;
    }

    $isPremium = ($plano === 'premium' || ($usuario['tipo'] ?? '') === 'admin');
    $limiteDiario = (int)obter_configuracao($bd, 'limite_pesquisas_gratuito', 5);
    $diasHistorico = (int)obter_configuracao($bd, 'dias_historico_gratuito', 7);
    $itensHistorico = (int)obter_configuracao($bd, 'itens_historico_gratuito', 5);

    if ($isPremium) {
        return [
            'plano'                => 'premium',
            'is_premium'           => true,
            'limite_pesquisas'     => 999999,
            'pesquisas_feitas_hoje'=> $pesquisasHoje,
            'pesquisas_restantes'  => 999999,
            'pode_pesquisar'       => true,
            'dias_historico'       => 999999,
            'itens_historico'      => 999999,
            'mensagem_plano'       => 'Plano Premium Ativo: Buscas e histórico ilimitados!'
        ];
    }

    $restantes = max(0, $limiteDiario - $pesquisasHoje);
    $podePesquisar = ($restantes > 0);

    return [
        'plano'                => 'gratuito',
        'is_premium'           => false,
        'limite_pesquisas'     => $limiteDiario,
        'pesquisas_feitas_hoje'=> $pesquisasHoje,
        'pesquisas_restantes'  => $restantes,
        'pode_pesquisar'       => $podePesquisar,
        'dias_historico'       => $diasHistorico,
        'itens_historico'      => $itensHistorico,
        'mensagem_plano'       => "Plano Gratuito: {$restantes} de {$limiteDiario} buscas restantes hoje."
    ];
}

/**
 * Incrementa o contador de pesquisas do usuário após busca realizada
 */
function consumir_pesquisa_usuario(PDO $bd, int $usuarioId): bool {
    $hoje = date('Y-m-d');
    $stmt = $bd->prepare("
        UPDATE usuarios 
        SET pesquisas_hoje = CASE WHEN data_ultimo_reset = ? THEN pesquisas_hoje + 1 ELSE 1 END,
            data_ultimo_reset = ?
        WHERE id = ?
    ");
    return $stmt->execute([$hoje, $hoje, $usuarioId]);
}

/**
 * Sistema de Mensagens Toast (Sucesso, Erro, Aviso, Info)
 */
function definir_toast(string $tipo, string $titulo, string $mensagem): void {
    $_SESSION['toast'] = [
        'tipo'     => $tipo, // 'sucesso', 'erro', 'aviso', 'info'
        'titulo'   => $titulo,
        'mensagem' => $mensagem
    ];
}

function obter_toast(): ?array {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        unset($_SESSION['toast']);
        return $toast;
    }
    return null;
}

/**
 * Sanitização básica de strings para exibição segura
 */
function h(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
