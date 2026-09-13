/* =========================================================================
   Hidrossolo — PWA do Painel do Gestor
   Registra o service worker e oferece a instalação do app do administrador,
   para que agenda, contas a pagar/receber e os lançamentos da frota fiquem
   acessíveis pelo celular.
   ========================================================================= */
(function () {
    'use strict';

    var CHAVE_DISPENSA = 'hidrossolo_admin_pwa_dispensado';
    var promptInstalacao = null;

    /* ---------------------------------------------------------------------
       Service worker (escopo /admin)
       --------------------------------------------------------------------- */
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw-admin.js', { scope: '/admin/' }).catch(function () {
                /* ambiente sem suporte/HTTPS: a instalação fica indisponível */
            });
        });
    }

    /* ---------------------------------------------------------------------
       Já está instalado?
       --------------------------------------------------------------------- */
    function jaInstalado() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.matchMedia('(display-mode: minimal-ui)').matches
            || window.navigator.standalone === true;
    }

    function ehIOS() {
        return /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    }

    /* ---------------------------------------------------------------------
       Barra de instalação
       --------------------------------------------------------------------- */
    function barra() {
        return document.querySelector('[data-admin-pwa-install]');
    }

    function mostrarBarra() {
        var el = barra();

        if (!el) {
            return;
        }

        try {
            if (jaInstalado() || window.localStorage.getItem(CHAVE_DISPENSA) === '1') {
                return;
            }
        } catch (e) {
            /* localStorage indisponível: mostra mesmo assim */
        }

        el.style.display = '';

        window.setTimeout(function () {
            if (el.style.display !== 'none') {
                esconder(el, false);
            }
        }, 20000);
    }

    function esconder(el, definitivo) {
        el.style.display = 'none';
        document.documentElement.style.setProperty('--admin-pwa-h', '0px');

        if (definitivo) {
            try {
                window.localStorage.setItem(CHAVE_DISPENSA, '1');
            } catch (e) {
                /* ignora */
            }
        }
    }

    function ajustarAltura() {
        var el = barra();

        if (!el || el.style.display === 'none') {
            document.documentElement.style.setProperty('--admin-pwa-h', '0px');
            return;
        }

        document.documentElement.style.setProperty('--admin-pwa-h', el.offsetHeight + 'px');
    }

    window.addEventListener('beforeinstallprompt', function (evento) {
        evento.preventDefault();
        promptInstalacao = evento;
        mostrarBarra();
    });

    window.addEventListener('appinstalled', function () {
        var el = barra();

        if (el) {
            esconder(el, true);
        }
    });

    document.addEventListener('click', function (evento) {
        var botaoInstalar = evento.target.closest('[data-admin-pwa-install-btn]');
        var botaoFechar = evento.target.closest('[data-admin-pwa-install-dismiss]');

        if (botaoFechar) {
            var el = barra();

            if (el) {
                esconder(el, true);
            }

            return;
        }

        if (!botaoInstalar) {
            return;
        }

        var el = barra();

        if (promptInstalacao) {
            promptInstalacao.prompt();

            promptInstalacao.userChoice.then(function (escolha) {
                if (escolha && escolha.outcome === 'accepted') {
                    if (el) {
                        esconder(el, true);
                    }
                }

                promptInstalacao = null;
            });

            return;
        }

        // iPhone/iPad não dispara beforeinstallprompt: orienta o usuário
        alert(
            'No iPhone/iPad:\n\n' +
            '1. Toque no botão Compartilhar (□ com seta);\n' +
            '2. Escolha "Adicionar à Tela de Início";\n' +
            '3. Confirme em "Adicionar".'
        );
    });

    document.addEventListener('DOMContentLoaded', function () {
        ajustarAltura();

        // iOS não suporta beforeinstallprompt: mostra a barra com instruções
        if (ehIOS()) {
            mostrarBarra();
        }

        window.addEventListener('resize', ajustarAltura);
    });
})();
