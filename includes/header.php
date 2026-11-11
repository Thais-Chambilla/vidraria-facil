<?php
/**
 * Vidraria Fácil - Cabeçalho Global Responsivo (Mobile-First)
 */
require_once __DIR__ . '/../bd.php';
require_once __DIR__ . '/funcoes.php';

$usuarioAtual = usuario_logado();
$limites = $usuarioAtual ? obter_limites_plano($bd, $usuarioAtual) : null;
$paginaAtual = basename($_SERVER['PHP_SELF'] ?? 'index.php');
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50 text-slate-800 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($tituloPagina) ? h($tituloPagina) . ' | ' : '' ?>Vidraria Fácil - Reconhecimento de Vidrarias</title>
    
    <!-- Meta tags para PWA / Mobile Web App -->
    <meta name="theme-color" content="#0284c7">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN com configuração personalizada -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        lab: {
                            50: '#ecfdf5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        /* Efeito de vidro laboratorial */
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .safe-bottom {
            padding-bottom: env(safe-area-inset-bottom, 1rem);
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-slate-50 text-slate-800 pb-20 md:pb-0">

    <!-- Barra de Navegação Superior (Desktop & Mobile) -->
    <header class="sticky top-0 z-40 glass-panel border-b border-slate-200/80 shadow-sm transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Marca -->
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-cyan-400 flex items-center justify-center text-white shadow-md shadow-sky-500/20 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-flask-vial text-lg"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-lg text-slate-900 tracking-tight flex items-center gap-1.5">
                            Vidraria Fácil
                            <span class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] uppercase font-bold tracking-wider bg-sky-100 text-sky-800 rounded-md">PTIC</span>
                        </span>
                        <p class="text-[11px] text-slate-500 hidden sm:block -mt-1 font-medium">Identificação Inteligente de Vidrarias</p>
                    </div>
                </a>

                <!-- Links Desktop -->
                <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
                    <a href="<?= BASE_URL ?>/index.php" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition <?= $paginaAtual === 'index.php' ? 'text-sky-600 font-semibold bg-sky-50/80' : '' ?>">
                        <i class="fa-solid fa-house mr-1.5 opacity-70"></i> Início
                    </a>
                    <a href="<?= BASE_URL ?>/camera.php" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition <?= $paginaAtual === 'camera.php' ? 'text-sky-600 font-semibold bg-sky-50/80' : '' ?>">
                        <i class="fa-solid fa-camera mr-1.5 opacity-70"></i> Escanear Câmera
                    </a>
                    <a href="<?= BASE_URL ?>/filtro.php" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition <?= $paginaAtual === 'filtro.php' ? 'text-sky-600 font-semibold bg-sky-50/80' : '' ?>">
                        <i class="fa-solid fa-filter mr-1.5 opacity-70"></i> Filtro de Atributos
                    </a>
                    <a href="<?= BASE_URL ?>/catalogo.php" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition <?= $paginaAtual === 'catalogo.php' ? 'text-sky-600 font-semibold bg-sky-50/80' : '' ?>">
                        <i class="fa-solid fa-book-open mr-1.5 opacity-70"></i> Catálogo
                    </a>
                    <a href="<?= BASE_URL ?>/historico.php" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition <?= $paginaAtual === 'historico.php' ? 'text-sky-600 font-semibold bg-sky-50/80' : '' ?>">
                        <i class="fa-solid fa-clock-rotate-left mr-1.5 opacity-70"></i> Histórico
                    </a>
                    <a href="<?= BASE_URL ?>/planos.php" class="px-3 py-2 rounded-lg hover:text-sky-600 hover:bg-sky-50 transition <?= $paginaAtual === 'planos.php' ? 'text-sky-600 font-semibold bg-sky-50/80' : '' ?>">
                        <i class="fa-solid fa-crown mr-1.5 text-amber-500"></i> Planos
                    </a>
                </nav>

                <!-- Área de Usuário / Status do Plano -->
                <div class="flex items-center gap-3">
                    <?php if ($usuarioAtual): ?>
                        
                        <!-- Badge do Plano (Free com contador ou Premium) -->
                        <?php if ($limites['is_premium']): ?>
                            <a href="<?= BASE_URL ?>/planos.php" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-amber-500 to-yellow-400 text-white shadow-sm hover:opacity-95 transition">
                                <i class="fa-solid fa-crown text-[10px]"></i>
                                Premium Vitalício
                            </a>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/planos.php" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition border border-slate-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Plano Free: <strong class="text-sky-600 font-bold"><?= $limites['pesquisas_restantes'] ?></strong> restantes
                            </a>
                        <?php endif; ?>

                        <!-- Menu de Perfil / ADM -->
                        <div class="relative group">
                            <button type="button" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 text-slate-700 transition">
                                <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm">
                                    <?= strtoupper(substr($usuarioAtual['nome'], 0, 1)) ?>
                                </div>
                                <span class="hidden md:inline-block text-xs font-medium max-w-[120px] truncate text-left">
                                    <span class="block font-semibold"><?= h($usuarioAtual['nome']) ?></span>
                                    <span class="block text-[10px] text-slate-400 capitalize"><?= $usuarioAtual['tipo'] === 'admin' ? 'Administrador' : $usuarioAtual['plano'] ?></span>
                                </span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-0.5"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-1 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 hidden group-hover:block hover:block z-50">
                                <div class="px-4 py-2 border-b border-slate-100 mb-1">
                                    <p class="text-xs font-semibold text-slate-900 truncate"><?= h($usuarioAtual['nome']) ?></p>
                                    <p class="text-[11px] text-slate-400 truncate"><?= h($usuarioAtual['email']) ?></p>
                                </div>

                                <a href="<?= BASE_URL ?>/perfil.php" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-600">
                                    <i class="fa-regular fa-user opacity-70"></i> Meu Perfil & Cota
                                </a>
                                <a href="<?= BASE_URL ?>/historico.php" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-600">
                                    <i class="fa-solid fa-clock-rotate-left opacity-70"></i> Meu Histórico
                                </a>

                                <?php if (($usuarioAtual['tipo'] ?? '') === 'admin'): ?>
                                    <div class="border-t border-slate-100 my-1 pt-1">
                                        <p class="px-4 py-1 text-[10px] font-bold text-sky-600 uppercase tracking-wider">Painel Administrativo</p>
                                        <a href="<?= BASE_URL ?>/admin/index.php" class="flex items-center gap-2.5 px-4 py-1.5 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-600">
                                            <i class="fa-solid fa-gauge-high opacity-70"></i> Painel Geral (KPIs)
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/usuarios.php" class="flex items-center gap-2.5 px-4 py-1.5 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-600">
                                            <i class="fa-solid fa-users opacity-70"></i> Gerenciar Usuários
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/vidrarias.php" class="flex items-center gap-2.5 px-4 py-1.5 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-600">
                                            <i class="fa-solid fa-vial opacity-70"></i> Catálogo de Vidrarias
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/configuracoes.php" class="flex items-center gap-2.5 px-4 py-1.5 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-600">
                                            <i class="fa-solid fa-sliders opacity-70"></i> Limites & Hugging Face
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/auditoria.php" class="flex items-center gap-2.5 px-4 py-1.5 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-600">
                                            <i class="fa-solid fa-shield-halved opacity-70"></i> Logs de Auditoria
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <div class="border-t border-slate-100 mt-1 pt-1">
                                    <a href="<?= BASE_URL ?>/auth/logout.php" class="flex items-center gap-2.5 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50">
                                        <i class="fa-solid fa-arrow-right-from-bracket opacity-70"></i> Encerrar Sessão
                                    </a>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/auth/login.php" class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:text-sky-600 transition">
                                Entrar
                            </a>
                            <a href="<?= BASE_URL ?>/auth/cadastro.php" class="px-4 py-1.5 text-xs font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-md shadow-sky-600/20 transition">
                                Cadastrar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </header>

    <!-- Componentes Globais de Toast e Modal -->
    <?php require_once __DIR__ . '/toast.php'; ?>
    <?php require_once __DIR__ . '/modal.php'; ?>
