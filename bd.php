<?php
/**
 * Vidraria Fácil - Conexão Segura com Banco de Dados via PDO
 * Requisitos:
 * - Uso obrigatório de PDO para evitar SQL Injections
 * - Configuração com Prepared Statements
 * - Resiliência e tratamento seguro de exceções
 */

require_once __DIR__ . '/config/config.php';

function obterConexaoBD(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $opcoesPdo = [
        PDO::ATTR_ERRMODE            => PDO_ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO_FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    try {
        // Tenta conexão primária com MySQL via PDO
        $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", DB_HOST, DB_PORT, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoesPdo);
        return $pdo;
    } catch (PDOException $eMySQL) {
        // Se o erro for de banco inexistente, tenta criá-lo no MySQL
        if (strpos($eMySQL->getMessage(), 'Unknown database') !== false || $eMySQL->getCode() == 1049) {
            try {
                $dsnSemBanco = sprintf("mysql:host=%s;port=%s;charset=utf8mb4", DB_HOST, DB_PORT);
                $pdoAdmin = new PDO($dsnSemBanco, DB_USER, DB_PASS, $opcoesPdo);
                $pdoAdmin->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Reconecta agora com o banco criado
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoesPdo);
                executarInstalacaoInicial($pdo);
                return $pdo;
            } catch (Exception $eCriacao) {
                // Segue para fallback
            }
        }

        // Fallback seguro com SQLite caso MySQL não esteja rodando localmente
        $sqlitePath = __DIR__ . '/database/vidraria_facil.sqlite';
        try {
            $dsnSqlite = "sqlite:" . $sqlitePath;
            $pdo = new PDO($dsnSqlite, null, null, [
                PDO::ATTR_ERRMODE            => PDO_ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO_FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ]);
            
            // Cria tabelas e insere dados iniciais no SQLite se arquivo for recente/vazio
            executarInstalacaoSqliteSeNecessario($pdo);
            return $pdo;
        } catch (PDOException $eSqlite) {
            die("Erro fatal de banco de dados: Não foi possível conectar ao MySQL (" . htmlspecialchars($eMySQL->getMessage()) . ") nem ao fallback local.");
        }
    }
}

/**
 * Executa scripts de schema e seed no MySQL quando banco recém-criado
 */
function executarInstalacaoInicial(PDO $pdo): void {
    $schemaFile = __DIR__ . '/database/schema.sql';
    $seedFile = __DIR__ . '/database/seed.sql';

    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
    }
    if (file_exists($seedFile)) {
        $sql = file_get_contents($seedFile);
        $pdo->exec($sql);
    }
}

/**
 * Cria a estrutura equivalente no SQLite caso o MySQL não esteja ativo
 */
function executarInstalacaoSqliteSeNecessario(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS configuracoes (
            chave TEXT PRIMARY KEY,
            valor TEXT NOT NULL,
            descricao TEXT,
            tipo TEXT DEFAULT 'string',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            senha TEXT NOT NULL,
            tipo TEXT DEFAULT 'comum',
            plano TEXT DEFAULT 'gratuito',
            status TEXT DEFAULT 'ativo',
            pesquisas_hoje INTEGER DEFAULT 0,
            data_ultimo_reset DATE DEFAULT CURRENT_DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS vidrarias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            nome_en TEXT,
            categoria TEXT NOT NULL,
            formato_fundo TEXT NOT NULL,
            tipo_gargalo TEXT NOT NULL,
            graduada INTEGER DEFAULT 0,
            resiste_aquecimento INTEGER DEFAULT 0,
            material_comum TEXT DEFAULT 'Vidro Borossilicato 3.3',
            funcao_principal TEXT NOT NULL,
            aplicacoes TEXT NOT NULL,
            dicas_seguranca TEXT NOT NULL,
            cuidados_manuseio TEXT NOT NULL,
            foto_url TEXT,
            icone_svg TEXT,
            tags_reconhecimento TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS pesquisas_historico (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            vidraria_id INTEGER,
            metodo_busca TEXT NOT NULL,
            imagem_path TEXT,
            confianca_ia REAL,
            resultado_identificado TEXT NOT NULL,
            detalhes_busca TEXT,
            data_pesquisa DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (vidraria_id) REFERENCES vidrarias(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS logs_auditoria (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER,
            usuario_nome TEXT,
            acao TEXT NOT NULL,
            tabela_afetada TEXT,
            registro_id INTEGER,
            detalhes TEXT,
            ip_address TEXT,
            user_agent TEXT,
            data_hora DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Verifica se já existem vidrarias cadastradas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM vidrarias");
    if ($stmt->fetch()['total'] == 0) {
        // Popula configurações
        $pdo->exec("
            INSERT OR IGNORE INTO configuracoes (chave, valor, descricao, tipo) VALUES
            ('limite_pesquisas_gratuito', '5', 'Quantidade diária de pesquisas por foto para usuários gratuitos', 'number'),
            ('dias_historico_gratuito', '7', 'Limite de dias para visualização do histórico no plano gratuito', 'number'),
            ('itens_historico_gratuito', '5', 'Quantidade máxima de registros exibidos no histórico gratuito', 'number'),
            ('huggingface_api_token', '', 'Token da API Hugging Face (Inference API)', 'string'),
            ('huggingface_model', 'google/vit-base-patch16-224', 'Modelo Hugging Face para reconhecimento de imagens', 'string'),
            ('hf_confidence_threshold', '0.40', 'Índice mínimo de confiança da IA', 'number');
        ");

        // Insere usuários iniciais (senha com hash bcrypt)
        $hashBcryptPadrao = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->exec("
            INSERT OR IGNORE INTO usuarios (id, nome, email, senha, tipo, plano, status, pesquisas_hoje) VALUES
            (1, 'Administrador do Sistema', 'admin@vidrariafacil.com', '{$hashBcryptPadrao}', 'admin', 'premium', 'ativo', 0),
            (2, 'Aluno Laboratório (Free)', 'aluno@vidrariafacil.com', '{$hashBcryptPadrao}', 'comum', 'gratuito', 'ativo', 0),
            (3, 'Dra. Helena Pesquisadora', 'pesquisador@vidrariafacil.com', '{$hashBcryptPadrao}', 'comum', 'premium', 'ativo', 0);
        ");

        // Importa dados de vidrarias
        $schemaVidrarias = [
            [
                'Balão de Fundo Redondo', 'round-bottom flask', 'Aquecimento', 'Redondo', 'Estreito', 0, 1,
                'Vidro Borossilicato 3.3',
                'Usado para aquecimento uniforme de líquidos, misturas e reações químicas em geral.',
                'Essencial para processos de destilação simples e fracionada, refluxo, sínteses orgânicas e evaporação.',
                'Verificar trincas antes de aquecer e utilizar obrigatoriamente suportes com garras e mantas aquecedoras.',
                'Nunca apoiar na bancada sem anel de cortiça ou borracha. Use luvas térmicas ao retirar do calor.',
                'assets/img/image41.jpeg',
                'balao, fundo redondo, balao de vidro, destilacao, refluxo, aquecimento, round bottom flask, flask'
            ],
            [
                'Balão de Fundo Plano', 'flat-bottom flask', 'Aquecimento', 'Plano', 'Estreito', 0, 1,
                'Vidro Borossilicato 3.3',
                'Utilizado para aquecimento brando de soluções e dissolução de reagentes que possam ficar em repouso.',
                'Reações moderadas sob tela de amianto, armazenamento temporário e titulações.',
                'Não recomendado para altas pressões ou vácuo. Sempre utilize tela refratária de amianto/cerâmica.',
                'Apoiar com suavidade na bancada para não gerar choque mecânico.',
                'assets/img/image22.png',
                'balao, fundo plano, flat bottom, flask, aquecimento brando'
            ],
            [
                'Béquer (Copo de Becker)', 'beaker', 'Reação', 'Plano', 'Largo', 1, 1,
                'Vidro Borossilicato 3.3',
                'Recipiente multiuso para dissolução de substâncias, misturas, titulações e aquecimento de líquidos.',
                'Preparo de soluções, banho-maria, pesagem preliminar de sólidos e precipitações químicas.',
                'Sua graduação é APROXIMADA e nunca deve ser usada para medições volumétricas de precisão analítica.',
                'Manusear com pinças para béquer quando aquecido. Evitar choque térmico brusco.',
                'assets/img/image28.png',
                'bequer, becker, copo graduado, beaker, copo de vidro, bico vertedor, misturas'
            ],
            [
                'Erlenmeyer', 'conical flask / erlenmeyer', 'Reação', 'Plano', 'Estreito', 1, 1,
                'Vidro Borossilicato 3.3',
                'Desenvolvido para agitação vigorosa manual de líquidos sem risco de respingos externos.',
                'Titulações ácido-base, recristalizações, culturas microbiológicas e aquecimento de líquidos.',
                'Marcações aproximadas. Ao fechar com rolha em agitações com solventes voláteis, alivie a pressão com frequência.',
                'Segure pelo gargalo ao agitar em movimentos circulares.',
                'assets/img/image29.png',
                'erlenmeyer, frasco conico, conical flask, titulacao, agitacao, boca estreita'
            ],
            [
                'Proveta Graduada', 'graduated cylinder', 'Medição', 'Plano', 'Largo', 1, 0,
                'Vidro Borossilicato ou Soda-cal',
                'Destinada à medição de volumes de líquidos com precisão intermediária.',
                'Transferência rápida de volumes de solventes, ácidos ou bases que não exigem rigor de pipeta volumétrica.',
                'NUNCA deve ser aquecida diretamente na chama nem levada à estufa quente para não descalibrar a escala.',
                'A leitura deve ser realizada na altura dos olhos, alinhando a base do menisco à linha de graduação.',
                'assets/img/image21.png',
                'proveta, proveta graduada, cilindro graduado, graduated cylinder, medicao de volume'
            ],
            [
                'Pipeta Volumétrica', 'volumetric pipette', 'Medição', 'Cônico', 'Estreito', 0, 0,
                'Vidro Borossilicato Calibrado',
                'Instrumento de alta precisão para transferir um volume único e rigorosamente fixo de líquido.',
                'Preparo de padrões analíticos, curvas de calibração e alíquotas em titulações.',
                'JAMAIS pipetar com a boca! O uso de pera de sucção ou pipetador de cremalheira é obrigatório.',
                'Deixe escorrer livremente pela gravidade e nunca assopre para expulsar a gota restante.',
                'assets/img/image23.png',
                'pipeta volumetrica, pipeta fixa, bulbo central, volumetric pipette, medicao rigorosa'
            ],
            [
                'Pipeta Graduada', 'graduated pipette', 'Medição', 'Cônico', 'Estreito', 1, 0,
                'Vidro Borossilicato Calibrado',
                'Permite medir e transferir volumes variáveis de líquidos com boa precisão analítica.',
                'Adição controlada de reagentes em volumes fracionados durante reações e preparo de soluções.',
                'Nunca use pipetas com a ponta quebrada ou trincada. Utilize sempre pera de sucção.',
                'Esvazie encostando a ponta suavemente na parede interna do frasco receptor.',
                'assets/img/image24.png',
                'pipeta graduada, graduated pipette, escala milimetrada, transferencia liquidos'
            ],
            [
                'Bureta', 'burette', 'Medição', 'Cônico', 'Estreito', 1, 0,
                'Vidro Borossilicato de Precisão',
                'Tubo cilíndrico graduado de alta precisão dotado de torneira para escoamento controlado.',
                'Item indispensável em análises titulométricas (ácido-base, redox e complexometria).',
                'Elimine qualquer bolha de ar abaixo da torneira antes de titular. Lave a torneira após o uso.',
                'Fixe firmemente em suporte universal com garras duplas específicas.',
                'assets/img/image25.png',
                'bureta, burette, torneira de vidro, torneira teflon, titulacao precisa, escoamento'
            ],
            [
                'Balão Volumétrico', 'volumetric flask', 'Medição', 'Plano', 'Estreito', 0, 0,
                'Vidro Borossilicato Classe A',
                'Frasco com fundo plano, gargalo longo e delgado com traço de aferição para preparo de soluções de concentração exata.',
                'Preparo de soluções padronizadas analíticas e padrões laboratoriais.',
                'Proibido aquecer diretamente para não deformar a dilatação térmica calibrada.',
                'Homogeneíze invertendo o balão tapado com rolha esmerilhada por 10 vezes consecutivas.',
                'assets/img/image26.png',
                'balao volumetrico, volumetric flask, traco de afericao, tampa esmerilhada, solucao padrao'
            ],
            [
                'Tubo de Ensaio', 'test tube', 'Reação', 'Redondo', 'Largo', 0, 1,
                'Vidro Borossilicato (Neutro)',
                'Tubo de vidro com fundo arredondado para ensaios em pequena escala e testes qualitativos rápidos.',
                'Testes de solubilidade, precipitações, reações colorimétricas e aquecimento de pequenas amostras.',
                'Ao aquecer na chama, use pinça de madeira e aponte a boca para onde não haja pessoas.',
                'Movimente na chama para não causar ebulição explosiva localizada.',
                'assets/img/image27.png',
                'tubo de ensaio, test tube, tubo de vidro, reacoes pequenas, pinça de madeira'
            ],
            [
                'Placa de Petri', 'petri dish', 'Armazenamento', 'Plano', 'Largo', 0, 1,
                'Vidro Borossilicato ou Soda-cal',
                'Recipiente cilíndrico raso com base e tampa para cultivo de microrganismos ou secagem de amostras.',
                'Microbiologia, cultivo em ágar, germinação e cristalização lenta.',
                'Autoclave adequadamente após o uso microbiológico para descarte seguro.',
                'Manipule com técnica asséptica perto da chama ou câmara de fluxo laminar.',
                'assets/img/image3.png',
                'placa de petri, petri dish, placa microbiologia, cultura agar, tampa de vidro'
            ],
            [
                'Funil de Vidro (Analítico)', 'glass funnel', 'Separação', 'Cônico', 'Estreito', 0, 0,
                'Vidro Borossilicato',
                'Vidraria cônica para transferência segura de líquidos e sustentação de papel de filtro.',
                'Filtração simples por gravidade e envase em recipientes de boca estreita.',
                'Dobre o papel de filtro corretamente para aderir às paredes sem canalizar o líquido.',
                'Apoie a haste biselada na parede receptora para escoamento uniforme.',
                'assets/img/image4.png',
                'funil de vidro, glass funnel, haste longa, haste curta, filtracao por gravidade'
            ],
            [
                'Kitassato (Frasco de Büchner)', 'buchner flask / filter flask', 'Separação', 'Plano', 'Largo', 1, 0,
                'Vidro Borossilicato Parede Grossa',
                'Semelhante ao Erlenmeyer com saída lateral para acoplamento de vácuo.',
                'Filtração a vácuo com funil de Büchner em alta velocidade.',
                'Nunca use Erlenmeyer comum para vácuo; utilize sempre Kitassato reforçado.',
                'Use armadilha de segurança para vácuo (trap) para proteger a bomba de sucção.',
                'assets/img/image5.png',
                'kitassato, buchner flask, filtracao a vacuo, saida lateral, parede reforcada'
            ],
            [
                'Condensador de Liebig / Serpentina', 'condenser', 'Separação', 'Cilíndrico', 'Esmerilhado', 0, 1,
                'Vidro Borossilicato 3.3',
                'Tubo duplo com camisa de resfriamento para condensação de vapores.',
                'Destilações, purificação de solventes e sistemas de refluxo orgânico.',
                'Entrada de água fria SEMPRE na parte inferior e saída na superior.',
                'Assegure presilhas nas mangueiras para evitar desconexão com a caldeira quente.',
                'assets/img/image6.png',
                'condensador, condensador liebig, serpentina, destilacao, camisa de agua, vapor'
            ],
            [
                'Funil de Separação / Decantação', 'separatory funnel', 'Separação', 'Esférico', 'Esmerilhado', 1, 0,
                'Vidro Borossilicato 3.3',
                'Formato de pera com torneira para separação de misturas de líquidos imiscíveis.',
                'Extração líquido-líquido em química orgânica e separação de fases aquosa/orgânica.',
                'Abra a torneira invertida periodicamente durante agitação para aliviar a pressão interna de vapores.',
                'Certifique-se de que a torneira esteja bem ajustada e vedada antes de encher.',
                'assets/img/image7.png',
                'funil de separacao, funil de bromo, decantacao, extracao liquido liquido, torneira'
            ],
            [
                'Vidro de Relógio', 'watch glass', 'Armazenamento', 'Côncavo', 'Sem gargalo', 0, 0,
                'Vidro Soda-cal ou Borossilicato',
                'Disco de vidro côncavo para pesagem preliminar de sólidos e cobertura de béqueres.',
                'Pesagem não higroscópica, secagem lenta e tampa para prevenir contaminação.',
                'Não expor à chama direta de bico de Bunsen.',
                'Lavar delicadamente com esponja macia para evitar riscos ópticos.',
                'assets/img/image8.png',
                'vidro de relogio, watch glass, tampa de bequer, pesagem de solidos'
            ]
        ];

        $insertStmt = $pdo->prepare("
            INSERT INTO vidrarias (
                nome, nome_en, categoria, formato_fundo, tipo_gargalo,
                graduada, resiste_aquecimento, material_comum, funcao_principal,
                aplicacoes, dicas_seguranca, cuidados_manuseio, foto_url, tags_reconhecimento
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($schemaVidrarias as $v) {
            $insertStmt->execute($v);
        }

        // Log inicial
        $pdo->exec("
            INSERT INTO logs_auditoria (usuario_id, usuario_nome, acao, tabela_afetada, detalhes, ip_address)
            VALUES (1, 'Sistema', 'SISTEMA_INICIALIZADO', 'configuracoes', 'Banco de dados SQLite inicializado automaticamente com catálogo de vidrarias', '127.0.0.1')
        ");
    }
}

// Instanciação inicial global da conexão
$bd = obterConexaoBD();
