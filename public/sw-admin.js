/* =========================================================================
   Hidrossolo — Service Worker do Painel do Gestor (PWA)
   Escopo: /admin

   Regras de segurança de dados:
   - Somente arquivos estáticos (/assets) e os ícones/manifest são cacheados.
   - NENHUMA página administrativa é gravada em cache: o painel mostra dados
     de negócio (financeiro, clientes, agenda) que não devem ficar
     disponíveis em um aparelho sem sessão válida.
   - Sem rede, a navegação cai na página offline.
   ========================================================================= */
'use strict';

var CACHE = 'hidrossolo-admin-v3';
var OFFLINE_URL = '/offline.html';

var PRECACHE = [
    OFFLINE_URL,
    '/assets/css/app.css',
    '/assets/css/media.css',
    '/assets/js/ui.js',
    '/assets/js/admin-pwa.js',
    '/assets/pwa/admin-192.png',
    '/assets/pwa/admin-512.png',
    '/manifest-admin.webmanifest'
];

self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE).then(function (cache) {
            return Promise.all(PRECACHE.map(function (url) {
                return cache.add(new Request(url, { credentials: 'same-origin' })).catch(function () {
                    /* item indisponível: segue sem ele */
                });
            }));
        }).then(function () { return self.skipWaiting(); })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (chaves) {
            return Promise.all(chaves.map(function (chave) {
                if (chave !== CACHE && chave.indexOf('hidrossolo-admin') === 0) {
                    return caches.delete(chave);
                }

                return Promise.resolve();
            }));
        }).then(function () { return self.clients.claim(); })
    );
});

self.addEventListener('fetch', function (event) {
    var requisicao = event.request;

    // Só métodos de leitura e mesma origem
    if (requisicao.method !== 'GET') {
        return;
    }

    var url = new URL(requisicao.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    // Estáticos: cache primeiro, atualizando em segundo plano
    if (url.pathname.indexOf('/assets/') === 0 || url.pathname === OFFLINE_URL) {
        event.respondWith(
            caches.open(CACHE).then(function (cache) {
                return cache.match(requisicao).then(function (resposta) {
                    var rede = fetch(requisicao).then(function (novaResposta) {
                        if (novaResposta && novaResposta.status === 200) {
                            cache.put(requisicao, novaResposta.clone());
                        }

                        return novaResposta;
                    }).catch(function () { return resposta; });

                    return resposta || rede;
                });
            })
        );

        return;
    }

    // Navegação no painel: sempre rede; sem rede, página offline
    if (requisicao.mode === 'navigate') {
        event.respondWith(
            fetch(requisicao).catch(function () {
                return caches.match(new Request(OFFLINE_URL, { credentials: 'same-origin' }))
                    .then(function (offline) {
                        return offline || new Response('Você está sem conexão.', {
                            status: 503,
                            headers: { 'Content-Type': 'text/plain; charset=utf-8' }
                        });
                    });
            })
        );
    }
});

self.addEventListener('message', function (event) {
    if (event.data === 'skip-waiting') {
        self.skipWaiting();
    }
});
