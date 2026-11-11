<?php
/**
 * Vidraria Fácil - Configurações Gerais da Aplicação
 */

if (session_status() === PHP_SESSION_NONE) {
    // Configurações de sessão segura
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Configurações da Aplicação
define('APP_NAME', 'Vidraria Fácil');
define('APP_TAGLINE', 'Identificação Inteligente de Vidrarias de Laboratório');
define('APP_VERSION', '1.0.0');

// Detecção de URL Base
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = preg_replace('/(\/(auth|admin|api|includes|views))$/', '', $scriptDir);
$baseUrl = rtrim($protocol . $host . $basePath, '/');
define('BASE_URL', $baseUrl);

// Configurações do Banco de Dados MySQL (Padrão XAMPP/WAMP/Servidor Local)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'vidraria_facil');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Hugging Face Defaults (Podem ser sobrescritos pelo painel ADM)
define('DEFAULT_HF_MODEL', 'google/vit-base-patch16-224');
define('DEFAULT_HF_TOKEN', '');
