<?php
/**
 * Vidraria Fácil - API de Consulta e Filtragem Dinâmica de Vidrarias
 * Suporte ao Caso de Uso 2: Filtrar Atributos
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/../includes/funcoes.php';

try {
    $termo = trim($_GET['termo'] ?? '');
    $categoria = trim($_GET['categoria'] ?? '');
    $formatoFundo = trim($_GET['formato_fundo'] ?? '');
    $tipoGargalo = trim($_GET['tipo_gargalo'] ?? '');
    $graduada = isset($_GET['graduada']) && $_GET['graduada'] !== '' ? (int)$_GET['graduada'] : null;
    $resisteAquecimento = isset($_GET['aquecimento']) && $_GET['aquecimento'] !== '' ? (int)$_GET['aquecimento'] : null;

    $sql = "SELECT * FROM vidrarias WHERE 1=1";
    $params = [];

    if (!empty($termo)) {
        $sql .= " AND (nome LIKE ? OR funcao_principal LIKE ? OR tags_reconhecimento LIKE ?)";
        $termoLike = "%{$termo}%";
        $params[] = $termoLike;
        $params[] = $termoLike;
        $params[] = $termoLike;
    }

    if (!empty($categoria)) {
        $sql .= " AND categoria = ?";
        $params[] = $categoria;
    }

    if (!empty($formatoFundo)) {
        $sql .= " AND formato_fundo = ?";
        $params[] = $formatoFundo;
    }

    if (!empty($tipoGargalo)) {
        $sql .= " AND tipo_gargalo = ?";
        $params[] = $tipoGargalo;
    }

    if ($graduada !== null) {
        $sql .= " AND graduada = ?";
        $params[] = $graduada;
    }

    if ($resisteAquecimento !== null) {
        $sql .= " AND resiste_aquecimento = ?";
        $params[] = $resisteAquecimento;
    }

    $sql .= " ORDER BY nome ASC";

    $stmt = $bd->prepare($sql);
    $stmt->execute($params);
    $vidrarias = $stmt->fetchAll();

    echo json_encode([
        'sucesso'   => true,
        'total'     => count($vidrarias),
        'vidrarias' => $vidrarias
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
