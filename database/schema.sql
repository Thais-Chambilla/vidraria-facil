-- =============================================================================
-- PROJETO VIDRARIA FÁCIL - ESTRUTURA DO BANCO DE DADOS (MySQL)
-- Requisitos: PDO, senhas com bcrypt, histórico com restrições de plano,
-- auditoria completa de alterações, e configurações dinâmicas de limites.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `vidraria_facil` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `vidraria_facil`;

-- -----------------------------------------------------------------------------
-- 1. Tabela: configuracoes (Parâmetros globais gerenciados pelo ADM)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracoes` (
    `chave` VARCHAR(60) NOT NULL PRIMARY KEY,
    `valor` TEXT NOT NULL,
    `descricao` VARCHAR(255) NULL,
    `tipo` VARCHAR(20) DEFAULT 'string',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Tabela: usuarios
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt',
    `tipo` ENUM('comum', 'admin') NOT NULL DEFAULT 'comum',
    `plano` ENUM('gratuito', 'premium') NOT NULL DEFAULT 'gratuito',
    `status` ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    `pesquisas_hoje` INT NOT NULL DEFAULT 0,
    `data_ultimo_reset` DATE DEFAULT (CURRENT_DATE),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_plano` (`plano`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Tabela: vidrarias (Catálogo de instrumentos de laboratório)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `vidrarias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(120) NOT NULL,
    `nome_en` VARCHAR(120) NULL COMMENT 'Mapeamento com classes do Hugging Face / IA',
    `categoria` VARCHAR(60) NOT NULL COMMENT 'Medição, Aquecimento, Reação, Separação, Armazenamento',
    `formato_fundo` VARCHAR(40) NOT NULL COMMENT 'Redondo, Plano, Cônico, Esférico',
    `tipo_gargalo` VARCHAR(40) NOT NULL COMMENT 'Estreito, Largo, Esmerilhado, Sem gargalo, Duplo',
    `graduada` TINYINT(1) NOT NULL DEFAULT 0,
    `resiste_aquecimento` TINYINT(1) NOT NULL DEFAULT 0,
    `material_comum` VARCHAR(80) DEFAULT 'Vidro Borossilicato 3.3',
    `funcao_principal` TEXT NOT NULL,
    `aplicacoes` TEXT NOT NULL,
    `dicas_seguranca` TEXT NOT NULL,
    `cuidados_manuseio` TEXT NOT NULL,
    `foto_url` VARCHAR(255) NULL,
    `icone_svg` TEXT NULL,
    `tags_reconhecimento` TEXT NULL COMMENT 'Tags e sinônimos para matching do motor de IA',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_nome` (`nome`),
    INDEX `idx_categoria` (`categoria`),
    INDEX `idx_formato_fundo` (`formato_fundo`),
    INDEX `idx_tipo_gargalo` (`tipo_gargalo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Tabela: pesquisas_historico (Histórico de detecções e consultas)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pesquisas_historico` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `vidraria_id` INT NULL,
    `metodo_busca` ENUM('camera', 'upload', 'filtro', 'busca_texto') NOT NULL,
    `imagem_path` VARCHAR(255) NULL,
    `confianca_ia` DECIMAL(5,2) NULL,
    `resultado_identificado` VARCHAR(150) NOT NULL,
    `detalhes_busca` TEXT NULL,
    `data_pesquisa` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`vidraria_id`) REFERENCES `vidrarias`(`id`) ON DELETE SET NULL,
    INDEX `idx_usuario_data` (`usuario_id`, `data_pesquisa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. Tabela: logs_auditoria (Auditoria obrigatória para qualquer alteração)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `usuario_nome` VARCHAR(100) NULL,
    `acao` VARCHAR(60) NOT NULL COMMENT 'LOGIN, LOGOUT, CADASTRO, DETECCAO_FOTO, ALTERAR_PLANO, etc.',
    `tabela_afetada` VARCHAR(60) NULL,
    `registro_id` INT NULL,
    `detalhes` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `data_hora` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_data_hora` (`data_hora`),
    INDEX `idx_usuario_log` (`usuario_id`),
    INDEX `idx_acao` (`acao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
