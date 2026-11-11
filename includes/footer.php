<?php
/**
 * Vidraria Fácil - Rodapé Global e Barra de Ações Mobile (Mobile-First)
 */
?>

    <!-- Rodapé Desktop -->
    <footer class="mt-auto border-t border-slate-200 bg-white/80 py-8 px-4 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center font-bold">
                    <i class="fa-solid fa-flask-vial"></i>
                </div>
                <div>
                    <p class="font-bold text-slate-700">Vidraria Fácil &bull; PTIC</p>
                    <p class="text-[11px] text-slate-400">Projeto de Identificação Inteligente de Vidrarias de Laboratório</p>
                </div>
            </div>

            <!-- Aviso de Segurança em Laboratório -->
            <div class="text-center md:text-right max-w-md">
                <p class="text-[11px] text-amber-700 font-medium bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200 inline-block">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Atenção: Sempre utilize EPI completo (jaleco, óculos e luvas) ao manusear vidrarias em laboratório.
                </p>
                <p class="text-[10px] text-slate-400 mt-1">
                    Grupo PTIC: Nicolas Sanches, Thais, Laís e Kauanny &bull; PHP &bull; MySQL PDO &bull; Tailwind CSS &bull; Hugging Face
                </p>
            </div>
        </div>
    </footer>

    <!-- Barra de Navegação Flutuante Inferior para Dispositivos Móveis (Mobile-First) -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 safe-bottom shadow-lg">
        <div class="grid grid-cols-5 h-16 items-center px-1">
            
            <a href="<?= BASE_URL ?>/index.php" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-sky-600 <?= $paginaAtual === 'index.php' ? 'text-sky-600 font-bold' : '' ?>">
                <i class="fa-solid fa-house text-lg"></i>
                <span class="text-[10px] mt-0.5">Início</span>
            </a>

            <a href="<?= BASE_URL ?>/filtro.php" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-sky-600 <?= $paginaAtual === 'filtro.php' ? 'text-sky-600 font-bold' : '' ?>">
                <i class="fa-solid fa-filter text-lg"></i>
                <span class="text-[10px] mt-0.5">Filtros</span>
            </a>

            <!-- Botão Central Destacado da Câmera -->
            <a href="<?= BASE_URL ?>/camera.php" class="flex flex-col items-center justify-center -mt-5 group">
                <div class="w-13 h-13 p-3 rounded-full bg-gradient-to-tr from-sky-600 to-cyan-400 text-white shadow-lg shadow-sky-500/30 flex items-center justify-center transform group-active:scale-95 transition">
                    <i class="fa-solid fa-camera text-xl"></i>
                </div>
                <span class="text-[10px] font-bold text-sky-700 mt-1">Escanear</span>
            </a>

            <a href="<?= BASE_URL ?>/catalogo.php" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-sky-600 <?= $paginaAtual === 'catalogo.php' ? 'text-sky-600 font-bold' : '' ?>">
                <i class="fa-solid fa-book-open text-lg"></i>
                <span class="text-[10px] mt-0.5">Catálogo</span>
            </a>

            <a href="<?= BASE_URL ?>/historico.php" class="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-sky-600 <?= $paginaAtual === 'historico.php' ? 'text-sky-600 font-bold' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                <span class="text-[10px] mt-0.5">Histórico</span>
            </a>

        </div>
    </div>

</body>
</html>
