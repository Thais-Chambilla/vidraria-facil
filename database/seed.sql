-- =============================================================================
-- PROJETO VIDRARIA FÁCIL - DADOS INICIAIS (SEED)
-- Catálogo enriquecido de vidrarias de laboratório, configurações e contas demo
-- =============================================================================

USE `vidraria_facil`;

-- -----------------------------------------------------------------------------
-- Configurações do Sistema
-- -----------------------------------------------------------------------------
INSERT INTO `configuracoes` (`chave`, `valor`, `descricao`, `tipo`) VALUES
('limite_pesquisas_gratuito', '5', 'Quantidade diária de pesquisas por foto para usuários gratuitos', 'number'),
('dias_historico_gratuito', '7', 'Limite de dias para visualização do histórico no plano gratuito', 'number'),
('itens_historico_gratuito', '5', 'Quantidade máxima de registros exibidos no histórico gratuito', 'number'),
('huggingface_api_token', '', 'Token da API Hugging Face (Inference API)', 'string'),
('huggingface_model', 'google/vit-base-patch16-224', 'Modelo Hugging Face utilizado para reconhecimento de imagens', 'string'),
('hf_confidence_threshold', '0.40', 'Índice mínimo de confiança da IA (0.00 a 1.00)', 'number')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);

-- -----------------------------------------------------------------------------
-- Usuários Iniciais (Senhas padrão com hash bcrypt: admin123, aluno123, premium123)
-- -----------------------------------------------------------------------------
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `plano`, `status`, `pesquisas_hoje`, `data_ultimo_reset`) VALUES
(1, 'Administrador do Sistema', 'admin@vidrariafacil.com', '$2y$10$V04oRk2i1V2U85LzU0K0eeFomVjGfZu5m9sYy6f2c/q.9Yg4f9Gce', 'admin', 'premium', 'ativo', 0, CURRENT_DATE),
(2, 'Aluno Laboratório (Free)', 'aluno@vidrariafacil.com', '$2y$10$V04oRk2i1V2U85LzU0K0eeFomVjGfZu5m9sYy6f2c/q.9Yg4f9Gce', 'comum', 'gratuito', 'ativo', 0, CURRENT_DATE),
(3, 'Dra. Helena Pesquisadora', 'pesquisador@vidrariafacil.com', '$2y$10$V04oRk2i1V2U85LzU0K0eeFomVjGfZu5m9sYy6f2c/q.9Yg4f9Gce', 'comum', 'premium', 'ativo', 0, CURRENT_DATE)
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

-- -----------------------------------------------------------------------------
-- Catálogo Completo de Vidrarias de Laboratório
-- -----------------------------------------------------------------------------
INSERT INTO `vidrarias` (
    `id`, `nome`, `nome_en`, `categoria`, `formato_fundo`, `tipo_gargalo`, 
    `graduada`, `resiste_aquecimento`, `material_comum`, `funcao_principal`, 
    `aplicacoes`, `dicas_seguranca`, `cuidados_manuseio`, `foto_url`, `tags_reconhecimento`
) VALUES
(
    1,
    'Balão de Fundo Redondo',
    'round-bottom flask',
    'Aquecimento',
    'Redondo',
    'Estreito',
    0,
    1,
    'Vidro Borossilicato 3.3',
    'Usado para aquecimento uniforme de líquidos, misturas e reações químicas em geral.',
    'Essencial para processos de destilação simples e fracionada, sistemas de refluxo, sínteses orgânicas e evaporação rotativa.',
    'Verificar trincas ou microfissuras antes de submeter ao fogo. Utilizar obrigatoriamente suportes universais com garras e mantas aquecedoras reguladas (nunca apoiar diretamente em superfícies planas).',
    'Nunca apoie sobre a bancada sem anel de cortiça ou borracha. Use luvas térmicas ou pinça metálica ao retirar do aquecimento.',
    'assets/img/image41.jpeg',
    'balao, fundo redondo, balao de vidro, destilacao, refluxo, aquecimento, round bottom flask, flask'
),
(
    2,
    'Balão de Fundo Plano',
    'flat-bottom flask',
    'Aquecimento',
    'Plano',
    'Estreito',
    0,
    1,
    'Vidro Borossilicato 3.3',
    'Utilizado para aquecimento brando de soluções e dissolução de reagentes que possam ficar em repouso sobre a bancada.',
    'Reações com aquecimento moderado sob tela de amianto, armazenamento temporário de líquidos sintetizados e titulações específicas.',
    'Não recomendado para altas pressões ou vácuo severo devido ao fundo plano. Sempre utilize tela de amianto ou cerâmica entre a chama e o balão.',
    'Apoiar com cuidado sobre superfícies planas para evitar choque mecânico na junção do fundo com o corpo.',
    'assets/img/image22.png',
    'balao, fundo plano, flat bottom, flask, aquecimento brando, solucao'
),
(
    3,
    'Béquer (Copo de Becker)',
    'beaker',
    'Reação',
    'Plano',
    'Largo',
    1,
    1,
    'Vidro Borossilicato 3.3',
    'Recipiente multiuso para dissolução de substâncias, misturas, titulações e aquecimento moderado de líquidos.',
    'Preparo de soluções, banho-maria, pesagem preliminar de sólidos brutos e precipitações químicas.',
    'Sua graduação é meramente APROXIMADA e nunca deve ser usada para medições volumétricas de precisão. Em caso de aquecimento, use tela refratária.',
    'Manusear utilizando pinças para béquer quando aquecido. Evitar mudanças bruscas de temperatura (choque térmico).',
    'assets/img/image28.png',
    'bequer, becker, copo graduado, beaker, copo de vidro, bico vertedor, misturas'
),
(
    4,
    'Erlenmeyer',
    'conical flask / erlenmeyer',
    'Reação',
    'Plano',
    'Estreito',
    1,
    1,
    'Vidro Borossilicato 3.3',
    'Desenvolvido para agitação vigorosa manual de líquidos sem risco de respingos externos.',
    'Amplamente empregado em titulações ácido-base, recristalizações, culturas microbiológicas e aquecimento de líquidos.',
    'Assim como o béquer, as marcações volumétricas são aproximadas. Ao fechar com rolha durante agitação com solventes voláteis, alivie a pressão interna com frequência.',
    'Segure pelo gargalo ao agitar em círculos. Em caso de aquecimento na chama, mantenha sob tela de amianto.',
    'assets/img/image29.png',
    'erlenmeyer, frasco conico, conical flask, titulacao, agitacao, boca estreita'
),
(
    5,
    'Proveta Graduada',
    'graduated cylinder',
    'Medição',
    'Plano',
    'Largo',
    1,
    0,
    'Vidro Borossilicato ou Soda-cal',
    'Destinada à medição de volumes de líquidos com precisão média a moderada.',
    'Transferência rápida de volumes de solventes, ácidos ou bases diluídas que não exijam rigor de pipeta volumétrica.',
    'NUNCA deve ser aquecida diretamente na chama nem levada à estufa quente, pois a dilatação térmica descalibra permanentemente a graduação.',
    'A leitura deve ser realizada na altura dos olhos, alinhando a base do menisco à linha de graduação desejada.',
    'assets/img/image21.png',
    'proveta, proveta graduada, cilindro graduado, graduated cylinder, medicao de volume'
),
(
    6,
    'Pipeta Volumétrica',
    'volumetric pipette',
    'Medição',
    'Cônico',
    'Estreito',
    0,
    0,
    'Vidro Borossilicato Calibrado',
    'Instrumento de alta precisão para transferir um volume único e rigorosamente fixo de líquido (ex: 5mL, 10mL, 25mL).',
    'Preparo de padrões analíticos, curvas de calibração em espectrofotometria e alíquotas de amostras em titulação.',
    'JAMAIS pipetar com a boca! O uso de pera de borracha (pipetador manual) ou pipetador de cremalheira é obrigatório por normas de biossegurança.',
    'Deixe escorrer livremente e nunca force a saída da última gota com sopro, a não ser que a pipeta seja do tipo blow-out devidamente marcada.',
    'assets/img/image23.png',
    'pipeta volumetrica, pipeta fixa, bulbo central, volumetric pipette, medicao rigorosa'
),
(
    7,
    'Pipeta Graduada',
    'graduated pipette',
    'Medição',
    'Cônico',
    'Estreito',
    1,
    0,
    'Vidro Borossilicato Calibrado',
    'Permite medir e transferir volumes variáveis de líquidos com boa precisão analítica.',
    'Adição controlada de reagentes em volumes fracionados durante reações e preparo de soluções seriadas.',
    'Nunca utilize vidros trincados na ponta da pipeta, pois o menisco sofrerá erro sistemático e há alto risco de corte.',
    'Sempre esvazie lentamente em contato com a parede inclinada do recipiente receptor.',
    'assets/img/image24.png',
    'pipeta graduada, graduated pipette, escala milimetrada, transferencia liquidos'
),
(
    8,
    'Bureta',
    'burette',
    'Medição',
    'Cônico',
    'Estreito',
    1,
    0,
    'Vidro Borossilicato de Precisão',
    'Tubo cilíndrico graduado de alta precisão dotado de torneira esmerilhada ou de PTFE para escoamento controlado.',
    'Item indispensável em volumetrias e análises titulométricas (ácido-base, redox, complexometria e precipitação).',
    'Certifique-se de que não haja bolhas de ar presas abaixo da torneira antes de iniciar a titulação. Lave a torneira imediatamente após usar hidróxido de sódio concentrado para não emperrar.',
    'Fixe sempre no suporte universal com garra dupla própria para buretas para garantir estabilidade e verticalidade perfeita.',
    'assets/img/image25.png',
    'bureta, burette, torneira de vidro, torneira teflon, titulacao precisa, escoamento'
),
(
    9,
    'Balão Volumétrico',
    'volumetric flask',
    'Medição',
    'Plano',
    'Estreito',
    0,
    0,
    'Vidro Borossilicato Classe A',
    'Frasco de vidro com fundo plano, gargalo longo e delgado contendo um traço de aferição para preparo de soluções de concentração exata.',
    'Preparo de soluções padronizadas em laboratórios de controle de qualidade, fármacos e química analítica quantitativa.',
    'Proibido aquecer ou secar em estufas acima de 50°C para não alterar a capacidade volumétrica calibrada.',
    'Dissolva previamente o soluto em um béquer antes de transferir quantitativamente para o balão volumétrico. Homogeneíze invertendo o balão fechado várias vezes.',
    'assets/img/image26.png',
    'balao volumetrico, volumetric flask, traco de afericao, tampa esmerilhada, solucao padrao'
),
(
    10,
    'Tubo de Ensaio',
    'test tube',
    'Reação',
    'Redondo',
    'Largo',
    0,
    1,
    'Vidro Borossilicato (Neutro ou 3.3)',
    'Tubo de vidro fino com fundo semiesférico para ensaios em pequena escala e testes qualitativos rápidos.',
    'Testes de precipitação, reações colorimétricas rápidas, ensaios de solubilidade e testes biológicos.',
    'Ao aquecer na chama do bico de Bunsen, utilize pinça de madeira e aponte a boca do tubo para uma direção onde NÃO haja pessoas.',
    'Aqueça movimentando suavemente o tubo na zona intermediária da chama para evitar projeção repentina do líquido (ebulição violenta).',
    'assets/img/image27.png',
    'tubo de ensaio, test tube, tubo de vidro, reacoes pequenas, pinça de madeira'
),
(
    11,
    'Placa de Petri',
    'petri dish',
    'Armazenamento',
    'Plano',
    'Largo',
    0,
    1,
    'Vidro Borossilicato ou Soda-cal',
    'Recipiente cilíndrico e raso composto por base e tampa utilizado para isolamento e cultivo de microrganismos ou secagem de amostras.',
    'Cultura de fungos e bactérias em meios sólidos de ágar, observação em microscópios estereoscópicos e cristalização lenta.',
    'Em caso de manipulação de micro-organismos patogênicos, autoclave a placa antes de qualquer descarte ou lavagem.',
    'Evite manusear a tampa com dedos desprotegidos para não contaminar a amostra ou o meio de cultura estéril.',
    'assets/img/image3.png',
    'placa de petri, petri dish, placa microbiologia, cultura agar, tampa de vidro'
),
(
    12,
    'Funil de Vidro (Analítico)',
    'glass funnel',
    'Separação',
    'Cônico',
    'Estreito',
    0,
    0,
    'Vidro Borossilicato ou Comum',
    'Vidraria cônica com haste alongada para transferência segura de líquidos entre recipientes e sustentação de papel de filtro.',
    'Filtração simples por gravidade de suspensões e transferência limpa para frascos de boca estreita.',
    'O papel de filtro deve ser dobrado corretamente e adaptado sem vazamentos de líquido pelas laterais.',
    'A ponta biselada da haste do funil deve tocar a parede interna do béquer receptor para evitar respingos de filtrado.',
    'assets/img/image4.png',
    'funil de vidro, glass funnel, haste longa, haste curta, filtracao por gravidade'
),
(
    13,
    'Kitassato (Frasco de Büchner)',
    'buchner flask / filter flask',
    'Separação',
    'Plano',
    'Largo',
    1,
    0,
    'Vidro Borossilicato de Parede Grossa',
    'Semelhante ao Erlenmeyer, mas com paredes espessas e uma saída lateral para conexão com trompa d’água ou bomba de vácuo.',
    'Filtrações a vácuo rápidas em conjunto com o funil de Büchner com placa porosa ou porcelana.',
    'Nunca utilize Erlenmeyers comuns para filtração a vácuo! Somente o Kitassato suporta a pressão negativa sem implodir.',
    'Interponha sempre um frasco de segurança (armadilha de vácuo / trap) entre o Kitassato e a bomba de vácuo para proteger o motor contra aspiração acidental de líquidos.',
    'assets/img/image5.png',
    'kitassato, buchner flask, filtracao a vacuo, saida lateral, parede reforcada'
),
(
    14,
    'Condensador de Liebig / Serpentina',
    'condenser',
    'Separação',
    'Cilíndrico',
    'Esmerilhado',
    0,
    1,
    'Vidro Borossilicato 3.3',
    'Dispositivo de vidro com tubo interno e camisa externa para circulação de líquido refrigerante (geralmente água fria).',
    'Condensação de vapores em processos de destilação simples, fracionada e em aparelhos de refluxo Soxhlet.',
    'A entrada de água de refrigeração deve ser SEMPRE conectada na extremidade inferior e a saída na superior (fluxo contra-corrente).',
    'Garanta que as conexões de mangueira estejam firmes para não causar alagamentos ou desligamento brusco do resfriamento com a caldeira ligada.',
    'assets/img/image6.png',
    'condensador, condensador liebig, serpentina, destilacao, camisa de agua, vapor'
),
(
    15,
    'Funil de Separação / Decantação',
    'separatory funnel',
    'Separação',
    'Esférico',
    'Esmerilhado',
    1,
    0,
    'Vidro Borossilicato 3.3',
    'Formato em pera com torneira inferior e tampa superior esmerilhada para separação de misturas de líquidos imiscíveis.',
    'Extração líquido-líquido em química orgânica e separação de fases aquosas e orgânicas.',
    'Ao agitar, abra periodicamente a torneira invertida para despressurizar os vapores de solventes inflamáveis (ex: éter, diclorometano).',
    'Mantenha a torneira lubrificada com silicone ou utilize torneiras de PTFE (Teflon) que dispensam graxa e não contaminam a amostra.',
    'assets/img/image7.png',
    'funil de separacao, funil de bromo, decantacao, extracao liquido liquido, torneira'
),
(
    16,
    'Vidro de Relógio',
    'watch glass',
    'Armazenamento',
    'Côncavo',
    'Sem gargalo',
    0,
    0,
    'Vidro Soda-cal ou Borossilicato',
    'Peça circular e ligeiramente côncava que lembra o mostrador de um relógio antigo.',
    'Pesagem de sólidos não higroscópicos, cobertura protetora para béqueres durante aquecimento brando e evaporação lenta.',
    'Não deve ser submetido diretamente à chama do bico de Bunsen.',
    'Higienize com papel macio para não arranhar a superfície côncava.',
    'assets/img/image8.png',
    'vidro de relogio, watch glass, tampa de bequer, pesagem de solidos, superficie concava'
)
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`);

-- -----------------------------------------------------------------------------
-- Logs Iniciais de Auditoria
-- -----------------------------------------------------------------------------
INSERT INTO `logs_auditoria` (`usuario_id`, `usuario_nome`, `acao`, `tabela_afetada`, `registro_id`, `detalhes`, `ip_address`, `user_agent`) VALUES
(1, 'Administrador do Sistema', 'SISTEMA_INICIALIZADO', 'configuracoes', NULL, 'Base de dados inicializada com catálogo completo e regras de negócio padrão', '127.0.0.1', 'VidrariaFacil Installer'),
(1, 'Administrador do Sistema', 'CONFIGURACAO_ATUALIZADA', 'configuracoes', NULL, 'Limites do plano gratuito estabelecidos em 5 pesquisas diárias e 7 dias de histórico', '127.0.0.1', 'VidrariaFacil Installer');
