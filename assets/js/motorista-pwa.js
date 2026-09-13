/* =========================================================================
   Hidrossolo — PWA da Área do Motorista
   Instalação, detecção de conexão e fila de lançamentos offline.
   ========================================================================= */
(function () {
    'use strict';

    var QUEUE_KEY = 'hidrossolo_offline_queue';
    var DISMISS_KEY = 'hidrossolo_pwa_dismissed';

    /* ---------------------------------------------------------------------
     * Fila offline (localStorage)
     * ------------------------------------------------------------------- */
    function readQueue() {
        try {
            return JSON.parse(localStorage.getItem(QUEUE_KEY)) || [];
        } catch (e) {
            return [];
        }
    }

    function writeQueue(queue) {
        try {
            localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
        } catch (e) {
            // armazenamento cheio ou indisponível
        }
        updateBanners();
    }

    function updateBanners() {
        var pendentes = readQueue().length;

        document.body.classList.toggle('is-offline', !navigator.onLine);
        document.body.classList.toggle('has-pending', pendentes > 0);

        document.querySelectorAll('[data-pending-count]').forEach(function (el) {
            el.textContent = String(pendentes);
        });
    }

    function serialize(form) {
        var data = new FormData(form);
        var pares = [];

        data.forEach(function (valor, chave) {
            pares.push([chave, valor]);
        });

        return pares;
    }

    function enqueue(form) {
        var queue = readQueue();

        queue.push({
            url: form.getAttribute('action') || window.location.pathname,
            data: serialize(form),
            at: Date.now()
        });

        writeQueue(queue);
    }

    function toFormData(pares) {
        var body = new FormData();

        pares.forEach(function (par) {
            body.append(par[0], par[1]);
        });

        return body;
    }

    function flush() {
        var queue = readQueue();

        if (!queue.length || !navigator.onLine) {
            updateBanners();
            return;
        }

        var item = queue[0];

        fetch(item.url, {
            method: 'POST',
            body: toFormData(item.data),
            credentials: 'same-origin',
            redirect: 'follow'
        }).then(function (response) {
            if (!response.ok && !response.redirected) {
                throw new Error('HTTP ' + response.status);
            }

            queue.shift();
            writeQueue(queue);

            if (queue.length) {
                flush();
            } else {
                avisar('Lançamentos pendentes enviados com sucesso!', 'success');
            }
        }).catch(function () {
            // mantém na fila para tentar novamente depois
            updateBanners();
        });
    }

    /* ---------------------------------------------------------------------
     * Aviso flutuante
     * ------------------------------------------------------------------- */
    function avisar(mensagem, tipo) {
        var node = document.createElement('div');
        node.textContent = mensagem;
        node.style.cssText = 'position:fixed;left:50%;bottom:22px;transform:translateX(-50%);' +
            'z-index:1200;padding:.75rem 1.15rem;border-radius:12px;font-size:.85rem;max-width:92vw;' +
            'text-align:center;box-shadow:0 12px 30px rgba(2,6,23,.35);color:#fff;' +
            'background:' + (tipo === 'error' ? '#991b1b' : '#065f46') + ';';

        document.body.appendChild(node);

        window.setTimeout(function () {
            node.style.transition = 'opacity .3s ease';
            node.style.opacity = '0';
            window.setTimeout(function () { node.remove(); }, 320);
        }, 3400);
    }

    /* ---------------------------------------------------------------------
     * Intercepta envio sem conexão
     * ------------------------------------------------------------------- */
    document.addEventListener('submit', function (event) {
        var form = event.target.closest('form[data-offline-queue]');

        if (!form || navigator.onLine) {
            return; // com internet, o envio segue normal
        }

        event.preventDefault();
        enqueue(form);
        form.reset();

        avisar('Sem conexão: o lançamento foi salvo no aparelho e será enviado quando a internet voltar.', 'error');
    }, true);

    window.addEventListener('online', function () {
        updateBanners();
        avisar('Conexão restabelecida. Enviando lançamentos...', 'success');
        flush();
    });

    window.addEventListener('offline', updateBanners);

    /* ---------------------------------------------------------------------
     * Instalação (PWA)
     * ------------------------------------------------------------------- */
    var deferredPrompt = null;
    var installBar = document.querySelector('[data-pwa-install]');

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;

        if (installBar && localStorage.getItem(DISMISS_KEY) !== '1') {
            installBar.style.display = 'flex';
        }
    });

    var installBtn = document.querySelector('[data-pwa-install-btn]');

    if (installBtn) {
        installBtn.addEventListener('click', function () {
            if (!deferredPrompt) {
                avisar('Para instalar: abra o menu do navegador e escolha "Adicionar à tela inicial".', 'success');
                return;
            }

            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function () {
                deferredPrompt = null;
                if (installBar) installBar.style.display = 'none';
            });
        });
    }

    var dismissBtn = document.querySelector('[data-pwa-install-dismiss]');

    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () {
            localStorage.setItem(DISMISS_KEY, '1');
            if (installBar) installBar.style.display = 'none';
        });
    }

    window.addEventListener('appinstalled', function () {
        localStorage.setItem(DISMISS_KEY, '1');
        if (installBar) installBar.style.display = 'none';
        avisar('Aplicativo instalado! Use o ícone na tela inicial.', 'success');
    });

    /* ---------------------------------------------------------------------
     * Service Worker
     * ------------------------------------------------------------------- */
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .catch(function () { /* ambiente sem suporte/HTTPS */ });

            // Envia fila pendente assim que houver conexão
            if (navigator.onLine) flush();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateBanners();
        if (navigator.onLine) flush();
    });
})();
