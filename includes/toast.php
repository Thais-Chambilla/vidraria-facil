<?php
/**
 * Vidraria Fácil - Componente de Notificações TOAST (Tailwind CSS)
 * Substitui alertas intrusivos por notificações modernas e fluidas
 */

$toast = obter_toast();
?>

<!-- Container Global para Toasts -->
<div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full px-4 pointer-events-none sm:px-0">
    <?php if ($toast): ?>
        <?php
            $tipo = $toast['tipo'] ?? 'info';
            $titulo = $toast['titulo'] ?? '';
            $msg = $toast['mensagem'] ?? '';

            $cores = [
                'sucesso' => 'bg-emerald-50 border-emerald-500 text-emerald-900 dark:bg-emerald-950 dark:border-emerald-600 dark:text-emerald-100',
                'erro'    => 'bg-rose-50 border-rose-500 text-rose-900 dark:bg-rose-950 dark:border-rose-600 dark:text-rose-100',
                'aviso'   => 'bg-amber-50 border-amber-500 text-amber-900 dark:bg-amber-950 dark:border-amber-600 dark:text-amber-100',
                'info'    => 'bg-sky-50 border-sky-500 text-sky-900 dark:bg-sky-950 dark:border-sky-600 dark:text-sky-100'
            ];

            $icones = [
                'sucesso' => 'fa-check-circle text-emerald-500',
                'erro'    => 'fa-circle-xmark text-rose-500',
                'aviso'   => 'fa-triangle-exclamation text-amber-500',
                'info'    => 'fa-circle-info text-sky-500'
            ];

            $estiloCor = $cores[$tipo] ?? $cores['info'];
            $estiloIcone = $icones[$tipo] ?? $icones['info'];
        ?>
        <div class="toast-item pointer-events-auto border-l-4 shadow-xl rounded-xl p-4 transition-all duration-300 transform translate-y-0 opacity-100 flex items-start gap-3 <?= $estiloCor ?>" role="alert">
            <i class="fa-solid <?= $estiloIcone ?> text-xl mt-0.5"></i>
            <div class="flex-1">
                <h4 class="font-bold text-sm leading-tight"><?= h($titulo) ?></h4>
                <p class="text-xs mt-1 leading-relaxed opacity-90"><?= h($msg) ?></p>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="opacity-60 hover:opacity-100 text-gray-500 p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<script>
/**
 * Função global em JavaScript para disparo de TOASTS dinâmicos no frontend
 */
window.mostrarToast = function(tipo, titulo, mensagem) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const cores = {
        'sucesso': 'bg-emerald-50 border-emerald-500 text-emerald-900 dark:bg-emerald-950 dark:border-emerald-600 dark:text-emerald-100',
        'erro': 'bg-rose-50 border-rose-500 text-rose-900 dark:bg-rose-950 dark:border-rose-600 dark:text-rose-100',
        'aviso': 'bg-amber-50 border-amber-500 text-amber-900 dark:bg-amber-950 dark:border-amber-600 dark:text-amber-100',
        'info': 'bg-sky-50 border-sky-500 text-sky-900 dark:bg-sky-950 dark:border-sky-600 dark:text-sky-100'
    };

    const icones = {
        'sucesso': 'fa-check-circle text-emerald-500',
        'erro': 'fa-circle-xmark text-rose-500',
        'aviso': 'fa-triangle-exclamation text-amber-500',
        'info': 'fa-circle-info text-sky-500'
    };

    const cor = cores[tipo] || cores['info'];
    const icone = icones[tipo] || icones['info'];

    const toast = document.createElement('div');
    toast.className = `toast-item pointer-events-auto border-l-4 shadow-xl rounded-xl p-4 transition-all duration-300 transform translate-y-0 opacity-100 flex items-start gap-3 ${cor}`;
    toast.innerHTML = `
        <i class="fa-solid ${icone} text-xl mt-0.5"></i>
        <div class="flex-1">
            <h4 class="font-bold text-sm leading-tight">${titulo}</h4>
            <p class="text-xs mt-1 leading-relaxed opacity-90">${mensagem}</p>
        </div>
        <button type="button" class="opacity-60 hover:opacity-100 text-gray-500 p-1">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    `;

    toast.querySelector('button').onclick = () => toast.remove();
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-x-full');
        setTimeout(() => toast.remove(), 350);
    }, 4500);
};

// Auto-dismiss para toasts PHP carregados no DOM
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast-item').forEach(toast => {
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => toast.remove(), 350);
        }, 4500);
    });
});
</script>
