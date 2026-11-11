<?php
/**
 * Vidraria Fácil - Componente Global de MODAL (Tailwind CSS)
 * Regra: "sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL"
 */
?>

<!-- Estrutura do Modal Global -->
<div id="modal-global-overlay" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden transition-opacity duration-200">
    <div id="modal-global-card" class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all scale-95 opacity-0">
        <!-- Cabeçalho com Ícone e Título -->
        <div class="p-6 pb-4 flex items-start gap-4">
            <div id="modal-global-icon-wrapper" class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 bg-sky-100 text-sky-600 dark:bg-sky-950 dark:text-sky-400">
                <i id="modal-global-icon" class="fa-solid fa-circle-info text-2xl"></i>
            </div>
            <div class="flex-1">
                <h3 id="modal-global-title" class="text-lg font-bold text-slate-800 dark:text-slate-100 leading-snug">
                    Título da Mensagem
                </h3>
                <p id="modal-global-message" class="text-sm text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
                    Conteúdo detalhado da notificação.
                </p>
            </div>
        </div>

        <!-- Rodapé de Ações -->
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
            <button id="modal-btn-cancelar" type="button" class="hidden px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-800 transition">
                Cancelar
            </button>
            <button id="modal-btn-confirmar" type="button" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 shadow-md shadow-sky-600/20 transition">
                Entendido
            </button>
        </div>
    </div>
</div>

<script>
/**
 * Gerenciador de Modal do Vidraria Fácil
 */
(function() {
    const overlay = document.getElementById('modal-global-overlay');
    const card = document.getElementById('modal-global-card');
    const titleEl = document.getElementById('modal-global-title');
    const msgEl = document.getElementById('modal-global-message');
    const iconWrapper = document.getElementById('modal-global-icon-wrapper');
    const iconEl = document.getElementById('modal-global-icon');
    const btnCancel = document.getElementById('modal-btn-cancelar');
    const btnConfirm = document.getElementById('modal-btn-confirmar');

    let confirmCallback = null;
    let cancelCallback = null;

    const temas = {
        'info': {
            bg: 'bg-sky-100 text-sky-600 dark:bg-sky-950 dark:text-sky-400',
            icon: 'fa-circle-info',
            btn: 'bg-sky-600 hover:bg-sky-700 shadow-sky-600/20'
        },
        'sucesso': {
            bg: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400',
            icon: 'fa-circle-check',
            btn: 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20'
        },
        'aviso': {
            bg: 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400',
            icon: 'fa-triangle-exclamation',
            btn: 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20'
        },
        'perigo': {
            bg: 'bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400',
            icon: 'fa-triangle-exclamation',
            btn: 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20'
        }
    };

    function fecharModal() {
        card.classList.remove('scale-100', 'opacity-100');
        card.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            overlay.classList.add('hidden');
        }, 150);
    }

    btnCancel.onclick = function() {
        fecharModal();
        if (cancelCallback) cancelCallback();
    };

    btnConfirm.onclick = function() {
        fecharModal();
        if (confirmCallback) confirmCallback();
    };

    overlay.onclick = function(e) {
        if (e.target === overlay) {
            fecharModal();
            if (cancelCallback) cancelCallback();
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !overlay.classList.contains('hidden')) {
            fecharModal();
            if (cancelCallback) cancelCallback();
        }
    });

    /**
     * Abre o modal customizado
     */
    window.abrirModal = function(opcoes) {
        const {
            titulo = 'Aviso',
            mensagem = '',
            tipo = 'info',
            mostrarCancelar = false,
            textoCancelar = 'Cancelar',
            textoConfirmar = 'OK',
            onConfirm = null,
            onCancel = null
        } = opcoes;

        const tema = temas[tipo] || temas['info'];

        titleEl.textContent = titulo;
        msgEl.textContent = mensagem;

        // Ícone e Cores
        iconWrapper.className = `w-12 h-12 rounded-xl flex items-center justify-center shrink-0 ${tema.bg}`;
        iconEl.className = `fa-solid ${tema.icon} text-2xl`;

        btnConfirm.className = `px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-md transition ${tema.btn}`;
        btnConfirm.textContent = textoConfirmar;

        if (mostrarCancelar) {
            btnCancel.classList.remove('hidden');
            btnCancel.textContent = textoCancelar;
        } else {
            btnCancel.classList.add('hidden');
        }

        confirmCallback = onConfirm;
        cancelCallback = onCancel;

        overlay.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        }, 10);
    };

    /**
     * Helpers rápidos para alert e confirm nativos
     */
    window.alertaModal = function(titulo, mensagem, tipo = 'info') {
        window.abrirModal({
            titulo,
            mensagem,
            tipo,
            mostrarCancelar: false,
            textoConfirmar: 'Entendido'
        });
    };

    window.confirmarModal = function(titulo, mensagem, onConfirm, textoConfirmar = 'Sim, continuar', tipo = 'aviso') {
        window.abrirModal({
            titulo,
            mensagem,
            tipo,
            mostrarCancelar: true,
            textoCancelar: 'Cancelar',
            textoConfirmar,
            onConfirm
        });
    };

    // Substituição das chamadas nativas do navegador
    window.alert = function(msg) {
        window.alertaModal('Atenção', msg, 'info');
    };
})();
</script>
