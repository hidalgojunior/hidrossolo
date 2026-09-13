/* =========================================================================
   Hidrossolo — Service Worker da Área do Motorista (PWA)
   Escopo: /  |  Atua apenas em /motorista e /assets
   ========================================================================= */
'use strict';

var CACHE = 'hidrossolo-driver-v2';
var OFFLINE_URL = '/offline.html';

var PRECACHE = [
    OFFLINE_URL,
    '/motorista',
    '/motorista/abastecimentos',
    '/motorista/abastecimentos/novo',
    '/motorista/manutencoes',
    '/motorista/manutencoes/nova',
    '/assets/css/app.css',
    '/assets/css/motorista.css',
    '/assets/js/ui.js',
    '/assets/js/motorista-pwa.js',
    '/assets/pwa/icon-192.png',
    '/assets/pwa/icon-512.png',
    '/manifest.webmanifest'
];

/* -------------------------------------------------------------------------
   Instalação: pré-cache do app shell
   ------------------------------------------------------------------------- */
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE).then(function (cache) {
            // addAll falha por completo se um item falhar; usamos add individual
            return Promise.all(PRECACHE.map(function (url) {
                return cache.add(new Request(url, { credentials: 'same-origin' })).catch(function () { /* ignora */ });
            }));
        }).then(function () { return self.skipWaiting(); })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(keys.map(function (key) {
                if (key !== CACHE && key.indexOf('hidrossolo-driver-') === 0) {
                    return caches.delete(key);
                }
            }));
        }).then(function () { return self.clients.claim(); })
    );
});

/* -------------------------------------------------------------------------
   Estratégia de busca
   ------------------------------------------------------------------------- */
function isAsset(url) {
    return /\.(css|js|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|webmanifest)$/i.test(url.pathname);
}

self.addEventListener('fetch', function (event) {
    var request = event.request;
    var url = new URL(request.url);

    // Só lidamos com o próprio site
    if (url.origin !== self.location.origin) {
        return;
    }

    // Somente a área do motorista e os assets estáticos
    var naArea = url.pathname === '/motorista' || url.pathname.indexOf('/motorista/') === 0;
    var noAsset = url.pathname.indexOf('/assets/') === 0 || url.pathname.indexOf('/manifest.webmanifest') === 0;

    if (!naArea && !noAsset) {
        return; // deixa o navegador cuidar (site público e admin nunca são cacheados)
    }

    // POST/PUT/DELETE passam direto (a fila offline é tratada na página)
    if (request.method !== 'GET') {
        return;
    }

    // Assets: cache primeiro, atualizando em segundo plano
    if (isAsset(url)) {
        event.respondWith(
            caches.match(request).then(function (cached) {
                var network = fetch(request).then(function (response) {
                    if (response && response.ok) {
                        var copy = response.clone();
                        caches.open(CACHE).then(function (cache) { cache.put(request, copy); });
                    }
                    return response;
                }).catch(function () { return cached; });

                return cached || network;
            })
        );
        return;
    }

    // Navegação: rede primeiro, cache como reserva, offline.html como último recurso
    event.respondWith(
        fetch(request).then(function (response) {
            if (response && response.ok) {
                var copy = response.clone();
                caches.open(CACHE).then(function (cache) { cache.put(request, copy); });
            }
            return response;
        }).catch(function () {
            return caches.match(request).then(function (cached) {
                return cached || caches.match(OFFLINE_URL);
            });
        })
    );
});

/* -------------------------------------------------------------------------
   Mensagens da página
   ------------------------------------------------------------------------- */
self.addEventListener('message', function (event) {
    if (event.data === 'skip-waiting') {
        self.skipWaiting();
    }
});
