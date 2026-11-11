<?php
/**
 * Vidraria Fácil - API de Identificação por Imagem (Câmera / Upload)
 * Integração: Hugging Face Inference API, verificação de limites do plano e gravação no histórico
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/ia_huggingface.php';

// Resposta padrão
$resposta = [
    'sucesso'  => false,
    'mensagem' => '',
    'dados'    => null
];

try {
    $usuario = usuario_logado();

    // Se o usuário não estiver logado, permite como visitante com limite de 1 teste ou exige login
    if (!$usuario) {
        http_response_code(401);
        echo json_encode([
            'sucesso'        => false,
            'requer_login'   => true,
            'mensagem'       => 'Você precisa estar logado para escanear vidrarias e salvar no seu histórico.'
        ]);
        exit;
    }

    // Validação de Regra de Negócio: Limite de buscas para usuários do Plano Gratuito
    $limites = obter_limites_plano($bd, $usuario);
    if (!$limites['pode_pesquisar']) {
        http_response_code(403);
        echo json_encode([
            'sucesso'          => false,
            'limite_atingido'  => true,
            'mensagem'         => "Você atingiu seu limite diário de {$limites['limite_pesquisas']} pesquisas gratuitas.",
            'dica_upgrade'     => 'Faça upgrade para o Plano Premium e tenha pesquisas e histórico ilimitados!'
        ]);
        exit;
    }

    $imagemData = null;
    $nomeArquivoOriginal = null;
    $metodoBusca = 'camera';

    // 1. Imagem enviada via Câmera em Base64 (JSON ou POST)
    if (!empty($_POST['imagem_base64'])) {
        $imagemData = $_POST['imagem_base64'];
        $metodoBusca = 'camera';
    } 
    // 2. Imagem enviada via Upload de Arquivo
    elseif (!empty($_FILES['imagem_arquivo']['tmp_name'])) {
        $arquivo = $_FILES['imagem_arquivo'];
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Falha no upload do arquivo (código {$arquivo['error']}).");
        }
        $imagemData = file_get_contents($arquivo['tmp_name']);
        $nomeArquivoOriginal = $arquivo['name'];
        $metodoBusca = 'upload';
    } else {
        throw new Exception("Nenhuma imagem foi recebida pelo servidor.");
    }

    // Salva cópia da captura na pasta uploads/capturas/ para histórico visual
    $uploadDir = __DIR__ . '/../uploads/capturas/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }
    
    $ext = 'jpg';
    $nomeSalvo = 'scan_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $caminhoSalvo = $uploadDir . $nomeSalvo;
    $caminhoRelativo = 'uploads/capturas/' . $nomeSalvo;

    if (strpos($imagemData, 'data:image') === 0) {
        $raw = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imagemData));
        file_put_contents($caminhoSalvo, $raw);
    } else {
        file_put_contents($caminhoSalvo, $imagemData);
    }

    // Processamento da Inteligência Artificial (Hugging Face / Motor Vision)
    $motorIA = new MotorIAHuggingFace($bd);
    $resultadoIA = $motorIA->identificarVidraria($imagemData, $nomeArquivoOriginal);

    if (!$resultadoIA['sucesso'] || empty($resultadoIA['vidraria'])) {
        throw new Exception($resultadoIA['mensagem'] ?? 'Não foi possível reconhecer a vidraria com precisão suficiente.');
    }

    $vidraria = $resultadoIA['vidraria'];
    $confianca = (float)($resultadoIA['confianca'] ?? 95.0);

    // Consome cota do usuário se plano gratuito
    consumir_pesquisa_usuario($bd, $usuario['id']);

    // Grava registro no Histórico do usuário (respeitando foreign keys PDO)
    $stmtHist = $bd->prepare("
        INSERT INTO pesquisas_historico (
            usuario_id, vidraria_id, metodo_busca, imagem_path, confianca_ia, resultado_identificado, detalhes_busca, data_pesquisa
        ) VALUES (?, ?, ?, ?, ?, ?, ?, " . ($bd->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? 'NOW()' : "datetime('now', 'localtime')") . ")
    ");
    $stmtHist->execute([
        $usuario['id'],
        $vidraria['id'],
        $metodoBusca,
        $caminhoRelativo,
        $confianca,
        $vidraria['nome'],
        json_encode([
            'motor'        => $resultadoIA['motor'] ?? 'Vision AI',
            'alternativas' => $resultadoIA['alternativas'] ?? []
        ], JSON_UNESCAPED_UNICODE)
    ]);

    $historicoId = (int)$bd->lastInsertId();

    // Auditoria obrigatória
    registrar_log(
        $bd,
        $usuario['id'],
        $usuario['nome'],
        'DETECCAO_IMAGEM',
        'pesquisas_historico',
        $historicoId,
        "Vidraria '{$vidraria['nome']}' identificada via {$metodoBusca} com {$confianca}% de confiança"
    );

    // Recalcula limites após o consumo
    $limitesAtualizados = obter_limites_plano($bd, $usuario);

    echo json_encode([
        'sucesso'            => true,
        'mensagem'           => 'Vidraria identificada com sucesso!',
        'vidraria'           => $vidraria,
        'confianca'          => $confianca,
        'motor'              => $resultadoIA['motor'] ?? 'IA',
        'imagem_url'         => BASE_URL . '/' . $caminhoRelativo,
        'alternativas'       => $resultadoIA['alternativas'] ?? [],
        'historico_id'       => $historicoId,
        'limites_plano'      => $limitesAtualizados
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
